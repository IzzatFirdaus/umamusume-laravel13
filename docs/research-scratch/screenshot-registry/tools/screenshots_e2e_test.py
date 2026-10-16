"""End-to-end smoke test on a throwaway registry.

Runs the full lifecycle — inventory, batch planning, review, evidence, batch
summary, coverage, packet, validation — entirely under a temp directory.  The
real registry and the 1,159-frame corpus are never touched.
"""
import hashlib
import importlib.util
import json
import os
import shutil
import struct
import sys
import tempfile
import types
import zlib

HERE = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, HERE)
import screenshots as S  # noqa: E402

KEYS = ("ROOT", "REG", "SRC", "INV", "NOTES", "BATCHES", "CLUSTERS", "SIGNATURES",
        "LEGACY", "EXTRAS", "EVIDENCE", "REFS_INDEX", "DISPOSITIONS",
        "CAPTURE_MANIFEST")


def make_png(path, w, h, seed):
    def chunk(tag, data):
        return (struct.pack(">I", len(data)) + tag + data
                + struct.pack(">I", zlib.crc32(tag + data) & 0xffffffff))
    ihdr = struct.pack(">IIBBBBB", w, h, 8, 2, 0, 0, 0)
    rows = b"".join(b"\x00" + bytes([(seed + y * w) & 0xff]) * (w * 3) for y in range(h))
    with open(path, "wb") as fh:
        fh.write(b"\x89PNG\r\n\x1a\n" + chunk(b"IHDR", ihdr)
                 + chunk(b"IDAT", zlib.compress(rows)) + chunk(b"IEND", b""))


def sha256_of(path):
    h = hashlib.sha256()
    with open(path, "rb") as fh:
        for c in iter(lambda: fh.read(65536), b""):
            h.update(c)
    return h.hexdigest()


def _load(name):
    spec = importlib.util.spec_from_file_location(name, os.path.join(HERE, name + ".py"))
    mod = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(mod)
    return mod


def main():
    real_inv = S.INV
    real_log = os.path.join(S.REG, "review-log.jsonl")
    real_inv_sha = sha256_of(real_inv) if os.path.exists(real_inv) else None
    real_log_sha = sha256_of(real_log) if os.path.exists(real_log) else None

    tmp = tempfile.mkdtemp(prefix="screenshot-registry-e2e-")
    src = os.path.join(tmp, "game-screenshots")
    reg = os.path.join(tmp, "registry")
    os.makedirs(src)
    os.makedirs(reg)

    saved = {k: getattr(S, k) for k in KEYS}
    for k, v in {"ROOT": tmp, "REG": reg, "SRC": src,
                 "INV": os.path.join(reg, "inventory.jsonl"),
                 "NOTES": os.path.join(reg, "notes-index.jsonl"),
                 "EVIDENCE": os.path.join(reg, "evidence.jsonl"),
                 "REFS_INDEX": os.path.join(reg, "refs-index.jsonl"),
                 "DISPOSITIONS": os.path.join(reg, "dispositions.jsonl"),
                 "BATCHES": os.path.join(reg, "batches"),
                 "CLUSTERS": os.path.join(reg, "clusters.json"),
                 "SIGNATURES": os.path.join(reg, "signatures.json"),
                  "LEGACY": os.path.join(tmp, "notes"),
                  "EXTRAS": os.path.join(tmp, "notes"),
                  "CAPTURE_MANIFEST": os.path.join(reg, "capture",
                                                   "manifest.jsonl")}.items():
        setattr(S, k, v)
    os.makedirs(S.LEGACY, exist_ok=True)

    failures = []

    def expect(cond, msg):
        if not cond:
            failures.append(msg)

    try:
        # 1. Corpus + clusters.
        make_png(os.path.join(src, "Screenshot 2026-01-01 100000.png"), 1920, 1080, 1)
        make_png(os.path.join(src, "Screenshot 2026-01-01 100001.png"), 1920, 1080, 2)
        make_png(os.path.join(src, "Screenshot 2026-01-01 100002.png"), 640, 360, 3)
        clusters = [
            {"rep": "Screenshot 2026-01-01 100000.png", "count": 2, "dim": [1920, 1080],
             "members": ["Screenshot 2026-01-01 100000.png", "Screenshot 2026-01-01 100001.png"]},
            {"rep": "Screenshot 2026-01-01 100002.png", "count": 1, "dim": [640, 360],
             "members": ["Screenshot 2026-01-01 100002.png"]},
        ]
        with open(S.CLUSTERS, "w", encoding="utf-8") as fh:
            json.dump(clusters, fh)
        with open(S.SIGNATURES, "w", encoding="utf-8") as fh:
            json.dump([{"name": c["members"][0]} for c in clusters], fh)

        a = types.SimpleNamespace()
        expect(S.cmd_inventory(a) == 0, "inventory should exit 0")
        expect(len(S.read_jsonl(S.INV)) == 3, "expected 3 inventory records")

        # 2. Batch planner (read-only).
        bb = _load("build_batch")
        cand = bb.build_candidates(S.read_jsonl(S.INV), limit=2, per_cluster=1)
        expect(cand["counts"]["total"] >= 1, "batch planner must select at least one frame")

        # 3. Review two frames.
        rv = types.SimpleNamespace(ids="2026-01-01 100000,2026-01-01 100001",
                                   state="verified", batch="E2E", observer="e2e",
                                   confidence="high", note="e2e note", reason=None,
                                   next_action=None, scenario=None, category=None,
                                   questions=None, screen_type="training",
                                   career_phase=None, mechanics=None, values=None,
                                   limitations=None, follow_up=None)
        expect(S.cmd_review(rv) == 0, "review should exit 0")
        log = S.read_jsonl(os.path.join(reg, "review-log.jsonl"))
        expect(len(log) == 2, "review-log must grow by exactly 2")

        # 4. Evidence claim.
        ea = types.SimpleNamespace(id="e2e", claim="e2e claim",
                                   supports="2026-01-01 100000", kind=None,
                                   status="observed", scenario=None, topic=None,
                                   confidence="high", observer="e2e", batch="E2E",
                                   limits=None, contradicts=None, open=None,
                                   independent="2026-01-01 100000", next_question=None)
        expect(S.cmd_evidence(ea) == 0, "evidence should exit 0")
        expect(len(S.read_jsonl(S.EVIDENCE)) == 1, "evidence must grow by exactly 1")

        # 5. Batch definition + deterministic summary.
        ba = types.SimpleNamespace(id="E2E", ids="2026-01-01 100000,2026-01-01 100001",
                                   question="q", why="w", verify=None, next=None,
                                   state=None, observer="e2e", confidence=None,
                                   note=None, reason=None, show=False)
        expect(S.cmd_batch(ba) == 0, "batch should exit 0")
        expect(S.cmd_batch_summary(types.SimpleNamespace(id="E2E", check=False,
                                                         dry_run=False)) == 0,
               "batch-summary should exit 0")

        # 6. Coverage report.
        expect(S.cmd_report(a) == 0, "report should exit 0")
        cov_path = os.path.join(reg, "COVERAGE.md")
        expect(os.path.exists(cov_path), "COVERAGE.md must be written")

        # 7. Evidence packet.
        ep = _load("evidence_packet")
        pkt = ep.build_packet("e2e", reg)
        expect(len(pkt["claims"]) == 1, "packet must find the e2e claim")

        # 8. Validation.
        expect(S.cmd_validate(a) == 0, "validate must pass on the temp registry")

        # 9. Determinism: the coverage report is byte-stable.
        cov1 = open(cov_path, encoding="utf-8").read()
        expect(S.cmd_report(a) == 0, "second report should exit 0")
        cov2 = open(cov_path, encoding="utf-8").read()
        expect(cov1 == cov2, "coverage report must be deterministic")
    finally:
        for k, v in saved.items():
            setattr(S, k, v)
        shutil.rmtree(tmp, ignore_errors=True)

    # 10. The real registry is untouched.
    if real_inv_sha is not None:
        expect(sha256_of(real_inv) == real_inv_sha, "real inventory.jsonl must be unchanged")
    if real_log_sha is not None:
        expect(sha256_of(real_log) == real_log_sha, "real review-log.jsonl must be unchanged")

    if failures:
        for f in failures:
            print("FAIL  %s" % f)
        print("screenshots_e2e_test: %d failure(s)" % len(failures))
        return 1
    print("screenshots_e2e_test: PASS")
    return 0


if __name__ == "__main__":
    sys.exit(main())
