"""Self-test for screenshots.py against a throwaway corpus.

Runs the real subcommand handlers with the module globals redirected to a
tempfile corpus, so the real registry is never touched. Covers:

  1. Inventory generation and SHA-256 exact-duplicate detection.
  2. Near-duplicate cluster links from clusters.json.
  3. sid stability and review-state preservation across re-runs.
  4. Rename recovery: content carried under a new filename inherits the record.
  5. Note reconciliation: exact, probable, and unresolved match classes.
  6. documented_unverified promotion from a note reference.
  7. `verified` refused without --note.
  8. `skipped` refused without --reason.
  9. Invalid state in review_history fails validation.
 10. A clean tree passes validation.

Usage:  python screenshots_selftest.py
Exit:   0 when every assertion holds, 1 otherwise.
"""

import collections
import hashlib
import json
import os
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
    """A minimal valid 24-bit PNG with a deterministic fill keyed on seed."""
    def chunk(tag, data):
        return (struct.pack(">I", len(data)) + tag + data
                + struct.pack(">I", zlib.crc32(tag + data) & 0xffffffff))
    ihdr = struct.pack(">IIBBBBB", w, h, 8, 2, 0, 0, 0)
    rows = b""
    for y in range(h):
        rows += b"\x00" + bytes([(seed + y * w) & 0xff]) * (w * 3)
    with open(path, "wb") as fh:
        fh.write(b"\x89PNG\r\n\x1a\n" + chunk(b"IHDR", ihdr)
                 + chunk(b"IDAT", zlib.compress(rows)) + chunk(b"IEND", b""))


def _redirect_map(tmp, reg, src, legacy):
    """Where each S.<KEY> points while the selftest runs.

    Every key in KEYS must appear here. A key that is missing silently keeps
    pointing at the live registry, so a test that writes it would mutate real
    research data instead of the throwaway copy.
    """
    return {
        "ROOT": tmp, "REG": reg, "SRC": src,
        "INV": os.path.join(reg, "inventory.jsonl"),
        "NOTES": os.path.join(reg, "notes-index.jsonl"),
        "EVIDENCE": os.path.join(reg, "evidence.jsonl"),
        "REFS_INDEX": os.path.join(reg, "refs-index.jsonl"),
        "DISPOSITIONS": os.path.join(reg, "dispositions.jsonl"),
        "BATCHES": os.path.join(reg, "batches"),
        "CLUSTERS": os.path.join(reg, "clusters.json"),
        "SIGNATURES": os.path.join(reg, "signatures.json"),
        "LEGACY": legacy, "EXTRAS": legacy,
        "CAPTURE_MANIFEST": os.path.join(reg, "capture", "manifest.jsonl"),
    }


def _keys_missing_redirect(keys, redirects):
    """KEYS entries with no matching redirect into the throwaway tree."""
    return sorted(set(keys) - set(redirects))


def _assert_keys_redirected():
    """Fail fast when a KEYS entry has no redirect into the throwaway tree."""
    missing = _keys_missing_redirect(KEYS, _REDIRECT_KEYS)
    if missing:
        raise AssertionError(
            "selftest KEYS missing redirects: %s; a test writing them would "
            "mutate the live registry" % missing)


_REDIRECT_KEYS = ("ROOT", "REG", "SRC", "INV", "NOTES", "EVIDENCE", "REFS_INDEX",
                  "DISPOSITIONS", "BATCHES", "CLUSTERS", "SIGNATURES", "LEGACY",
                  "EXTRAS", "CAPTURE_MANIFEST")


def run():
    tmp = tempfile.mkdtemp(prefix="screenshots-selftest-")
    src = os.path.join(tmp, "game-screenshots")
    reg = os.path.join(tmp, "registry")
    legacy = os.path.join(tmp, "notes")
    os.makedirs(src)
    os.makedirs(reg)
    os.makedirs(legacy)

    _assert_keys_redirected()
    live_digests = {}
    for k in KEYS:
        path = getattr(S, k)
        if isinstance(path, str) and os.path.isfile(path):
            live_digests[path] = hashlib.sha256(open(path, "rb").read()).hexdigest()
    saved = {k: getattr(S, k) for k in KEYS}
    for k, v in _redirect_map(tmp, reg, src, legacy).items():
        setattr(S, k, v)

    failures = []

    def expect(cond, msg):
        if not cond:
            failures.append(msg)

    try:
        # 1. Corpus: three distinct contents plus one exact duplicate.
        make_png(os.path.join(src, "Screenshot 2026-01-01 100000.png"), 1920, 1080, 1)
        make_png(os.path.join(src, "Screenshot 2026-01-01 100001.png"), 1920, 1080, 2)
        make_png(os.path.join(src, "Screenshot 2026-01-01 100002.png"), 640, 360, 3)
        import shutil
        shutil.copyfile(os.path.join(src, "Screenshot 2026-01-01 100000.png"),
                        os.path.join(src, "Screenshot 2026-01-01 100003.png"))

        clusters = [
            {"rep": "Screenshot 2026-01-01 100000.png", "count": 3, "dim": [1920, 1080],
             "members": ["Screenshot 2026-01-01 100000.png",
                         "Screenshot 2026-01-01 100001.png",
                         "Screenshot 2026-01-01 100003.png"]},
            {"rep": "Screenshot 2026-01-01 100002.png", "count": 1, "dim": [640, 360],
             "members": ["Screenshot 2026-01-01 100002.png"]},
        ]
        with open(S.CLUSTERS, "w", encoding="utf-8") as fh:
            json.dump(clusters, fh)
        with open(S.SIGNATURES, "w", encoding="utf-8") as fh:
            json.dump([{"name": c["members"][0]} for c in clusters], fh)

        a = types.SimpleNamespace()
        expect(S.cmd_inventory(a) == 0, "inventory should exit 0")
        inv1 = S.read_jsonl(S.INV)
        expect(len(inv1) == 4, "expected 4 inventory records, got %d" % len(inv1))
        by_ref = {r["ref"]: r for r in inv1}
        dup = [r for r in inv1 if r.get("exact_duplicate_count", 1) > 1]
        expect(len(dup) == 2, "expected 2 frames in the exact-duplicate group, got %d" % len(dup))
        expect(by_ref["2026-01-01 100000"]["sha256"] == by_ref["2026-01-01 100003"]["sha256"],
               "duplicate group must share a sha")
        expect(by_ref["2026-01-01 100000"]["exact_duplicate_count"] == 2,
               "duplicate count must be 2 for a two-file group")
        expect(by_ref["2026-01-01 100000"]["exact_duplicate_of"] is None,
               "the first sibling has no exact_duplicate_of")
        expect(by_ref["2026-01-01 100003"]["exact_duplicate_of"] == "2026-01-01 100000",
               "the sibling points at the first sibling")
        expect(by_ref["2026-01-01 100001"]["near_duplicate_cluster"] ==
               ["Screenshot 2026-01-01 100000.png", "Screenshot 2026-01-01 100003.png"],
               "near-duplicate cluster must carry its siblings")
        expect(by_ref["2026-01-01 100001"]["cluster_size"] == 3,
               "cluster_size must be 3")
        expect(by_ref["2026-01-01 100000"]["dhash_matches_cluster"] is None,
               "a cluster representative has no dhash_matches_cluster verdict")
        expect(by_ref["2026-01-01 100001"]["dhash_matches_cluster"] is True,
               "a member whose rep is in the same cluster reports True")
        expect(by_ref["2026-01-01 100000"]["width"] == 1920,
               "PNG IHDR width must be read")
        expect(by_ref["2026-01-01 100002"]["width"] == 640,
               "PNG IHDR width must be read for the small frame")

        # 2. A review decision survives an inventory re-run.
        a2 = types.SimpleNamespace(ids="2026-01-01 100001", state="verified", observer="selftest",
                                   confidence="high", note="verified by selftest", batch="B0",
                                   scenario=None, category=None, questions=None,
                                   reason=None, next_action=None)
        expect(S.cmd_review(a2) == 0, "review should exit 0")
        before = {r["ref"]: r for r in S.read_jsonl(S.INV)}["2026-01-01 100001"]
        expect(S.cmd_inventory(a) == 0, "second inventory run should exit 0")
        after = {r["ref"]: r for r in S.read_jsonl(S.INV)}["2026-01-01 100001"]
        expect(before["sid"] == after["sid"], "sid must be stable across re-runs")
        expect(after["state"] == "verified", "verified state must survive re-inventory")
        expect(len(after["review_history"]) == 1,
               "review_history must survive re-inventory, got %d" % len(after["review_history"]))
        expect(len(after["observations"]) == 1,
               "observations must survive re-inventory, got %d" % len(after["observations"]))
        expect(after["inventory_run"] >= after["first_seen"],
               "inventory_run must not regress first_seen")

        # 3. Rename recovery, on a frame with a unique hash.
        os.remove(os.path.join(src, "Screenshot 2026-01-01 100001.png"))
        make_png(os.path.join(src, "Screenshot 2026-01-01 110000.png"), 1920, 1080, 2)
        os.remove(os.path.join(src, "Screenshot 2026-01-01 100000.png"))
        os.remove(os.path.join(src, "Screenshot 2026-01-01 100003.png"))
        expect(S.cmd_inventory(a) == 0, "rename inventory should exit 0")
        inv3 = {r["ref"]: r for r in S.read_jsonl(S.INV)}
        expect("2026-01-01 100001" not in inv3, "the renamed-away ref must be dropped")
        expect("2026-01-01 110000" in inv3, "the new ref must be present")
        r = inv3.get("2026-01-01 110000")
        if r:
            expect(r["state"] == "verified", "rename must carry forward the review state")
            expect(len(r["review_history"]) == 1, "rename must carry forward the history")
            expect(r["first_seen"] == before["first_seen"], "rename must preserve first_seen")
            expect(r["sid"] == before["sid"], "rename must preserve the sid")

        # 4. Note reconciliation.
        note_a = os.path.join(S.LEGACY, "00-test.md")
        with open(note_a, "w", encoding="utf-8") as fh:
            fh.write("# Notes\n\n"
                     "## Screenshot 2026-01-01 100002\n\n"
                     "Frame: `docs/game-screenshots/Screenshot 2026-01-01 100002.png`, 640x360. "
                     "Read 2026-01-01 by the selftest pass.\n\n"
                     "1. **Category:** menu\n"
                     "2. **Screen name:** \"Settings\"\n\n"
                     "## Screenshot 2026-01-01 100099\n\n"
                     "Frame: `docs/game-screenshots/Screenshot 2026-01-01 100099.png`, 640x360. "
                     "Read 2026-01-01 by the selftest pass.\n\n"
                     "1. **Category:** menu\n"
                     "2. **Screen name:** \"Settings\"\n")
        a3 = types.SimpleNamespace()
        expect(S.cmd_notes(a3) == 0, "notes should exit 0")
        ni = collections.defaultdict(list)
        for n in S.read_jsonl(S.NOTES):
            ni[n["ref"]].append(n)
        expect(ni.get("2026-01-01 100002", [{}])[0].get("match") == "exact",
               "100002 should match exactly, got %r"
               % ni.get("2026-01-01 100002", [{}])[0].get("match"))
        expect(ni.get("2026-01-01 100002", [{}])[0].get("category") == "menu",
               "category should be extracted")
        expect(any(m.get("match") == "unresolved" for m in ni.get("2026-01-01 100099", [{}])),
               "100099 should be unresolved")
        expect(bool(ni.get("2026-01-01 100099", [{}])[0].get("match_reason")),
               "100099 should explain why it is unresolved")

        # 5. documented_unverified promotion.
        st = {r["ref"]: r["state"] for r in S.read_jsonl(S.INV)}
        expect(st.get("2026-01-01 100002") == "documented_unverified",
               "100002 should be promoted to documented_unverified, got %r"
               % st.get("2026-01-01 100002"))
        expect(st.get("2026-01-01 110000") == "verified",
               "verified state must not be downgraded by the notes pass")

        # 6. A note that names a wrong filename is unresolved, not exact.
        with open(note_a, "w", encoding="utf-8") as fh:
            fh.write("## Screenshot 2026-01-01 100002\n\n"
                     "Frame: `docs/game-screenshots/Screenshot 2026-01-01 100002.png`, 640x360.\n\n"
                     "1. **Category:** menu\n")
            fh.write("## Screenshot 2026-01-01 110000\n\n"
                     "Frame: `docs/game-screenshots/Screenshot 1999-01-01 110000.png`, 1920x1080.\n\n"
                     "1. **Category:** training\n")
        expect(S.cmd_notes(a3) == 0, "second notes run should exit 0")
        ni2 = collections.defaultdict(list)
        for n in S.read_jsonl(S.NOTES):
            ni2[n["ref"]].append(n)
        expect(any(m.get("match") == "unresolved" for m in ni2.get("2026-01-01 110000", [{}])),
               "a note naming a different filename must be unresolved")

        # 7. Refusals.
        def refused(ids, state, note=None, reason=None):
            return S.cmd_review(types.SimpleNamespace(
                ids=ids, state=state, observer="selftest", confidence=None, note=note,
                batch=None, scenario=None, category=None, questions=None,
                reason=reason, next_action=None))

        expect(refused("2026-01-01 100002", "verified") == 2,
               "verified without --note must exit 2")
        expect(refused("2026-01-01 100002", "skipped") == 2,
               "skipped without --reason must exit 2")
        expect(refused("no-such-frame", "skipped", reason="x") == 2,
               "an unknown id must exit 2")

        # 8. A skipped reason is recorded and validation passes.
        expect(refused("2026-01-01 100002", "skipped", reason="unreadable crop") == 0,
               "skipped with --reason must exit 0")
        sk = {r["ref"]: r for r in S.read_jsonl(S.INV)}["2026-01-01 100002"]
        expect(sk["state"] == "skipped", "state must be skipped")
        expect(sk["skipped_reason"] == "unreadable crop", "skipped_reason must be recorded")
        expect(len(sk["review_history"]) == 1, "review_history must record the skip")

        # 9. Invalid state in review_history fails validation.
        rows = S.read_jsonl(S.INV)
        for r in rows:
            if r["ref"] == "2026-01-01 100002":
                r["review_history"] = [{"state": "bogus", "by": "selftest", "date": S.today()}]
        S.write_jsonl(S.INV, rows, "filename")
        a6 = types.SimpleNamespace()
        expect(S.cmd_validate(a6) == 1, "an invalid state must fail validation")

        # 10. A skipped frame without a reason fails validation.
        rows = S.read_jsonl(S.INV)
        for r in rows:
            if r["ref"] == "2026-01-01 100002":
                r["review_history"] = []
                r["state"] = "skipped"
                r["skipped_reason"] = None
        S.write_jsonl(S.INV, rows, "filename")
        expect(S.cmd_validate(a6) == 1, "a skipped frame without a reason must fail validation")

        # 11. Evidence citing an unknown frame fails validation.
        rows = S.read_jsonl(S.INV)
        for r in rows:
            if r["ref"] == "2026-01-01 100002":
                r["state"] = "documented_unverified"
                r["evidence"] = [{"claim": "x", "supports": ["no-such-frame"], "status": "observed"}]
        S.write_jsonl(S.INV, rows, "filename")
        expect(S.cmd_validate(a6) == 1, "evidence citing an unknown frame must fail validation")

        # 12. Verified state without review_history fails validation.
        rows = S.read_jsonl(S.INV)
        for r in rows:
            if r["ref"] == "2026-01-01 100002":
                r["state"] = "verified"
                r["evidence"] = []
        S.write_jsonl(S.INV, rows, "filename")
        expect(S.cmd_validate(a6) == 1, "verified without history must fail validation")

        # 13. Clean up the tampered record: validation must pass.
        rows = S.read_jsonl(S.INV)
        for r in rows:
            if r["ref"] == "2026-01-01 100002":
                r["state"] = "documented_unverified"
        S.write_jsonl(S.INV, rows, "filename")
        expect(S.cmd_validate(a6) == 0, "a clean tree must pass validation")

        # 14. Coverage counts match the underlying records.
        st2 = collections.Counter(r["state"] for r in S.read_jsonl(S.INV))
        expect(sum(st2.values()) == 2, "coverage counts must sum to the record count")
        expect(st2.get("documented_unverified") == 1,
               "expected 1 documented_unverified, got %d" % st2.get("documented_unverified", 0))
        expect(st2.get("verified") == 1,
               "expected 1 verified, got %d" % st2.get("verified", 0))

        # 15. Citation dispositions: each kind validates; an undocumented
        #     unresolved citation fails; a typo must point at a real frame.
        def cite(ref, in_inv):
            return {"schema_version": 1, "ref": ref, "sid": None, "citations": 1,
                    "docs": ["x.md"], "in_inventory": in_inv,
                    "state": None, "first_citation": {"doc": "x.md", "in_inventory": in_inv,
                                                      "line": 1, "context": ""}}

        base_refs = [cite("2026-01-01 100002", True)]
        S.write_jsonl(S.REFS_INDEX, base_refs + [
            cite("2026-01-01 999998", False),
            cite("2026-01-01 999997", False),
            cite("2026-01-01 999996", False),
        ], "ref")
        S._dispose("2026-01-01 999998", "missing_no_copy", None, "gone", [], "selftest")
        S._dispose("2026-01-01 999997", "explicitly_missing", None, "marked missing", [], "selftest")
        S._dispose("2026-01-01 999996", "filename_typo", "2026-01-01 100002", "typo", [], "selftest")
        expect(S.cmd_validate(a6) == 0,
               "all three disposition kinds must validate cleanly")

        # An undocumented unresolved citation must fail validation.
        S.write_jsonl(S.REFS_INDEX, base_refs + [
            cite("2026-01-01 999998", False),
            cite("2026-01-01 999997", False),
            cite("2026-01-01 999996", False),
            cite("2026-01-01 999995", False),
        ], "ref")
        expect(S.cmd_validate(a6) == 1,
               "an undocumented unresolved citation must fail validation")

        # A filename_typo resolving to a non-frame must fail validation.
        S.write_jsonl(S.REFS_INDEX, base_refs + [
            cite("2026-01-01 999998", False),
            cite("2026-01-01 999997", False),
            cite("2026-01-01 999996", False),
        ], "ref")
        S._dispose("2026-01-01 999996", "filename_typo", "2026-01-01 888888", "bad typo", [], "selftest")
        expect(S.cmd_validate(a6) == 1,
               "a filename_typo resolving to a non-frame must fail validation")

        # Restore a clean disposition set and confirm the tree validates.
        S._dispose("2026-01-01 999996", "filename_typo", "2026-01-01 100002", "typo", [], "selftest")
        expect(S.cmd_validate(a6) == 0, "a clean disposition set must validate")

        # 16. Batch summaries are deterministic, idempotent, and validated.
        ba = types.SimpleNamespace(id="B0", ids="2026-01-01 100002,2026-01-01 110000",
                                   question="q", why="w", verify=None, next=None,
                                   state=None, observer="selftest", confidence=None,
                                   note=None, reason=None, show=False)
        expect(S.cmd_batch(ba) == 0, "batch definition should exit 0")
        s1 = S.compute_batch_summary("B0")
        expect(s1["unique_frame_count"] == 2, "batch must report 2 unique frames")
        expect(s1["duplicate_ids"] == [], "a clean batch has no duplicate ids")
        expect(s1["states"].get("verified") == 1, "batch summary must count 1 verified")
        expect(s1["states"].get("documented_unverified") == 1,
               "batch summary must count 1 documented_unverified")
        sb = types.SimpleNamespace(id="B0", check=False, dry_run=False)
        S.cmd_batch_summary(sb)
        s2 = S.compute_batch_summary("B0")
        expect(s1 == s2, "batch summary must be deterministic across runs")
        expect(S.cmd_validate(a6) == 0, "a consistent batch summary must validate")

        # A duplicate id in the manifest must fail validation.
        bpath = os.path.join(S.BATCHES, "B0.json")
        m = json.load(open(bpath, encoding="utf-8"))
        m["ids"] = ["2026-01-01 100002", "2026-01-01 100002"]
        with open(bpath, "w", encoding="utf-8") as fh:
            json.dump(m, fh)
        expect(S.cmd_validate(a6) == 1, "a duplicate batch id must fail validation")

        # A stale summary must fail validation.
        m["ids"] = ["2026-01-01 100002", "2026-01-01 110000"]
        m["summary"] = {"batch": "B0", "frame_count": 99}
        with open(bpath, "w", encoding="utf-8") as fh:
            json.dump(m, fh)
        expect(S.cmd_validate(a6) == 1, "a stale batch summary must fail validation")

        # 17. Duplicate ref, verified-without-observation, and malformed JSONL
        #     each fail validation on a minimal, otherwise-clean registry.
        import shutil as _sh
        S.write_jsonl(S.REFS_INDEX, [], "ref")
        with open(S.DISPOSITIONS, "w", encoding="utf-8") as fh:
            fh.write("")
        _sh.rmtree(S.BATCHES, ignore_errors=True)
        os.makedirs(S.BATCHES, exist_ok=True)

        real = "game-screenshots/Screenshot 2026-01-01 100002.png"

        def rec(ref, sid, state="not_reviewed"):
            return {"schema_version": 1, "ref": ref, "sid": sid, "sha256": "y" * 64,
                    "state": state, "path": real, "review_history": [], "observations": []}

        S.write_jsonl(S.INV, [rec("2026-01-01 200000", "aaa"),
                              rec("2026-01-01 200000", "bbb")], "filename")
        expect(S.cmd_validate(a6) == 1, "a duplicate ref must fail validation")

        r0 = rec("2026-01-01 200000", "aaa", state="verified")
        r0["review_history"] = [{"state": "verified", "by": "x", "date": S.today()}]
        r0["observations"] = []
        S.write_jsonl(S.INV, [r0, rec("2026-01-01 200001", "bbb")], "filename")
        expect(S.cmd_validate(a6) == 1, "verified without an observation must fail validation")

        with open(S.INV, "a", encoding="utf-8") as fh:
            fh.write("{ this is not valid json\n")
        expect(S.cmd_validate(a6) == 1, "malformed inventory JSONL must fail validation")

        # 18. Batch planner is deterministic, caps per-cluster selection, and
        #     never mutates review state.
        import importlib.util
        spec = importlib.util.spec_from_file_location(
            "build_batch", os.path.join(HERE, "build_batch.py"))
        bb = importlib.util.module_from_spec(spec)
        spec.loader.exec_module(bb)
        syn = [{"ref": "2026-02-01 10%04d" % i, "sid": "s%d" % i,
                "state": "not_reviewed", "width": 1920, "height": 1080,
                "cluster_rep": "Screenshot 2026-02-01 100000.png", "cluster_size": 6,
                "note_refs": [], "note_files": []} for i in range(6)]
        m1 = bb.build_candidates(syn, limit=10, per_cluster=2)
        m2 = bb.build_candidates(syn, limit=10, per_cluster=2)
        expect(m1 == m2, "batch planner must be deterministic")
        expect(m1["counts"]["total"] == 2, "per-cluster cap must limit selection")
        expect(all(r["state"] == "not_reviewed" for r in syn),
               "the batch planner must not mutate review state")

        # 19. The review aid is read-only, and a non-legible outcome is never
        #     mapped to `verified`.
        spec2 = importlib.util.spec_from_file_location(
            "review_images", os.path.join(HERE, "review_images.py"))
        ri = importlib.util.module_from_spec(spec2)
        spec2.loader.exec_module(ri)
        for oc in ("unreadable", "insufficient_resolution", "obscured", "ambiguous"):
            expect(ri.outcome_state(oc) != "verified", "outcome %s must not be verified" % oc)
        expect(ri.outcome_state("legible") == "verified", "legible must map to verified")

        S.write_jsonl(S.INV, [rec("2026-01-01 100002", "aaa")], "filename")
        inv_before = S.read_jsonl(S.INV)
        png = os.path.join(src, "Screenshot 2026-01-01 100002.png")
        before_bytes = open(png, "rb").read()
        out_crop = os.path.join(reg, "scratch", "crop.png")
        ri.crop(png, (0, 0, 64, 64), out_crop)
        expect(os.path.exists(out_crop), "crop must write an output file")
        expect(open(png, "rb").read() == before_bytes, "crop must not alter the original bytes")
        expect(S.read_jsonl(S.INV) == inv_before, "the review aid must not change review state")

        # 20. Structured observation fields: old, fully structured, and
        #     partially populated records validate; a wrong type fails.
        logp = os.path.join(reg, "review-log.jsonl")
        with open(logp, "w", encoding="utf-8", newline="\n") as fh:
            fh.write(json.dumps({"date": S.today(), "ref": "2026-01-01 300000",
                                 "sid": "sid-old", "state": "verified", "by": "x",
                                 "batch": None, "confidence": None, "note": "n",
                                 "reason": None}) + "\n")
        others = [rec("2026-01-01 100002", "sid-100002"),
                  rec("2026-01-01 110000", "sid-110000")]

        def obs_rec(observation):
            r = rec("2026-01-01 300000", "sid-old", state="verified")
            r["review_history"] = [{"state": "verified", "by": "x", "date": S.today()}]
            r["observations"] = [observation]
            return r

        def set_inv(observation):
            S.write_jsonl(S.INV, [obs_rec(observation)] + others, "filename")

        set_inv({"date": S.today(), "by": "x", "text": "old-style observation"})
        expect(S.cmd_validate(a6) == 0, "an old-style observation must validate")

        full = {"date": S.today(), "by": "x", "text": "full",
                "screen_type": "training", "scenario": "Unity Cup", "career_phase": "Junior Yr",
                "visible_mechanics": ["mood", "failure-rate"],
                "direct_values": {"Skill Pts": 148, "Failure": "9%"},
                "limitations": ["discount chip not legible"],
                "follow_up_question": "confirm the Lv3 tier"}
        set_inv(full)
        expect(S.cmd_validate(a6) == 0, "a fully structured observation must validate")

        bad = dict(full)
        bad["visible_mechanics"] = "mood"  # wrong type: must be a list of strings
        set_inv(bad)
        expect(S.cmd_validate(a6) == 1, "a mis-typed structured field must fail validation")

        # 21. Evidence traceability: a confirmed claim needs >=2 independent
        #     capture contexts; independent_supports must be a subset.
        _sh.rmtree(S.BATCHES, ignore_errors=True)
        os.makedirs(S.BATCHES, exist_ok=True)
        S.write_jsonl(S.REFS_INDEX, [], "ref")
        with open(S.DISPOSITIONS, "w", encoding="utf-8") as fh:
            fh.write("")
        with open(logp, "w", encoding="utf-8"):
            pass

        def evrec(eid, supports, status, independent=None):
            return {"schema_version": 1, "eid": eid, "claim": "c",
                    "status": status, "supports": supports,
                    "independent_supports": independent or [], "confidence": "high",
                    "by": "x", "date": S.today()}

        a = rec("2026-01-01 400000", "s-a")
        a["cluster_rep"] = "Screenshot 2026-01-01 400000.png"
        a["observations"] = [{"screen_type": "training", "text": "training screen", "by": "x"}]
        b = rec("2026-01-01 400001", "s-b")
        b["cluster_rep"] = "Screenshot 2026-01-01 400001.png"
        b["observations"] = [{"screen_type": "skill-selection", "text": "skill screen", "by": "x"}]
        c = rec("2026-01-01 400002", "s-c")
        c["cluster_rep"] = "Screenshot 2026-01-01 400000.png"
        c["observations"] = [{"screen_type": "training", "text": "training screen", "by": "x"}]
        S.write_jsonl(S.INV, [a, b, c, rec("2026-01-01 100002", "sid-100002"),
                              rec("2026-01-01 110000", "sid-110000")], "filename")

        with open(S.EVIDENCE, "w", encoding="utf-8", newline="\n") as fh:
            fh.write(json.dumps(evrec("e1", ["2026-01-01 400000", "2026-01-01 400002"],
                                      "confirmed")) + "\n")
        expect(S.cmd_validate(a6) == 1,
               "a confirmed claim supported only by one cluster must fail validation")

        with open(S.EVIDENCE, "w", encoding="utf-8", newline="\n") as fh:
            fh.write(json.dumps(evrec("e1", ["2026-01-01 400000", "2026-01-01 400001"],
                                      "confirmed")) + "\n")
        expect(S.cmd_validate(a6) == 0,
               "a confirmed claim across two clusters must validate")

        with open(S.EVIDENCE, "w", encoding="utf-8", newline="\n") as fh:
            fh.write(json.dumps(evrec("e1", ["2026-01-01 400000"], "observed",
                                      independent=["2026-01-01 400001"])) + "\n")
        expect(S.cmd_validate(a6) == 1,
               "independent_supports must be a subset of supports")

        # 22. The evidence browser reports scenario gaps as capture_required
        #     and never mutates the registry.
        import io
        import contextlib
        inv_before = S.read_jsonl(S.INV)
        br = types.SimpleNamespace(state=None, screen_type=None, mechanic=None,
                                   scenario=None, note_coverage=None, confidence=None,
                                   status=None, json=True)
        buf = io.StringIO()
        with contextlib.redirect_stdout(buf):
            rc = S.cmd_browse(br)
        expect(rc == 0, "browse must exit 0")
        expect("capture_required" in buf.getvalue(), "browse JSON must report capture_required")
        expect("Trackblazer" in buf.getvalue(), "an uncaptured scenario must be listed")
        expect(S.read_jsonl(S.INV) == inv_before, "browse must not mutate the registry")

        # 23. A full schedule/table claim may not be `observed` on a single
        #     supporting frame (Slice 4.1R regression).
        sched = "Mood tiers are five at +20/+10/0/-10/-20 percent."
        with open(S.EVIDENCE, "w", encoding="utf-8", newline="\n") as fh:
            r = evrec("e1", ["2026-01-01 400000"], "observed")
            r["claim"] = sched
            fh.write(json.dumps(r) + "\n")
        expect(S.cmd_validate(a6) == 1,
               "a full-schedule claim observed on one frame must fail validation")

        with open(S.EVIDENCE, "w", encoding="utf-8", newline="\n") as fh:
            r = evrec("e1", ["2026-01-01 400000", "2026-01-01 400001"], "observed")
            r["claim"] = sched
            fh.write(json.dumps(r) + "\n")
        expect(S.cmd_validate(a6) == 0,
               "a full-schedule claim observed on two frames must validate")

        with open(S.EVIDENCE, "w", encoding="utf-8", newline="\n") as fh:
            r = evrec("e1", ["2026-01-01 400000"], "inferred")
            r["claim"] = sched
            fh.write(json.dumps(r) + "\n")
        expect(S.cmd_validate(a6) == 0,
               "a full-schedule claim inferred on one frame must validate")

        # 24. UI contract validation: every evidence ref/sid resolves to a
        #     verified frame; an unresolved sid fails with a named error.
        spec3 = importlib.util.spec_from_file_location(
            "validate_contracts", os.path.join(HERE, "validate_contracts.py"))
        vc = importlib.util.module_from_spec(spec3)
        spec3.loader.exec_module(vc)
        by_ref = {"2026-01-01 400000": {"ref": "2026-01-01 400000", "sid": "s-a",
                                        "state": "verified"}}
        by_sid = {"s-a": by_ref["2026-01-01 400000"]}
        fixc = os.path.join(HERE, "fixtures", "contracts")
        okc = vc.validate_contract(os.path.join(fixc, "valid.json"), by_ref, by_sid)
        expect(okc == [], "a valid contract fixture must validate: %s" % okc)
        badc = vc.validate_contract(os.path.join(fixc, "invalid_unresolved_ref.json"),
                                    by_ref, by_sid)
        expect(any("sid" in m for m in badc),
               "an unresolved sid must fail with a named error: %s" % badc)

        # 25. Evidence packets are deterministic and match the fixture
        #     expectation; generation is read-only.
        import subprocess
        fixr = os.path.join(HERE, "fixtures", "packets", "registry")
        expd = os.path.join(HERE, "fixtures", "packets", "expected")
        d1 = os.path.join(reg, "pkt1")
        d2 = os.path.join(reg, "pkt2")
        for d in (d1, d2):
            rc = subprocess.call([sys.executable, os.path.join(HERE, "evidence_packet.py"),
                                  "--topic", "hint-discount", "--registry", fixr, "--out", d])
            expect(rc == 0, "evidence_packet.py must exit 0")
        md1 = open(os.path.join(d1, "packet.md"), encoding="utf-8").read()
        md2 = open(os.path.join(d2, "packet.md"), encoding="utf-8").read()
        mj1 = open(os.path.join(d1, "manifest.json"), encoding="utf-8").read()
        mj2 = open(os.path.join(d2, "manifest.json"), encoding="utf-8").read()
        expect(md1 == md2 and mj1 == mj2, "two packet builds must be byte-identical")
        expect(md1 == open(os.path.join(expd, "packet.md"), encoding="utf-8").read(),
               "packet.md must match the fixture expectation")
        expect(mj1 == open(os.path.join(expd, "manifest.json"), encoding="utf-8").read(),
               "manifest.json must match the fixture expectation")

        # 26. Capture intake: valid import appends one line; corrupt exits
        #     non-zero and appends nothing; same-stem different bytes preserve
        #     both; exact-byte duplicate never overwrites.
        spec5 = importlib.util.spec_from_file_location(
            "import_captures", os.path.join(HERE, "import_captures.py"))
        ic = importlib.util.module_from_spec(spec5)
        spec5.loader.exec_module(ic)
        capdir = os.path.join(reg, "capture")
        corpusdir = os.path.join(reg, "corpus")
        fixcap = os.path.join(HERE, "fixtures", "captures")
        man = os.path.join(capdir, "manifest.jsonl")

        def mlines():
            if not os.path.exists(man):
                return []
            return [l for l in open(man, encoding="utf-8").read().splitlines() if l.strip()]

        rc, _e, _m = ic.import_capture(os.path.join(fixcap, "valid.png"), "trackblazer",
                                       "scenario_overview", "ctx",
                                       capture_timestamp="20260101T000000",
                                       capture_dir=capdir, corpus_dir=corpusdir)
        expect(rc == 0 and len(mlines()) == 1, "a valid import must append exactly one line")

        rc2, _e, _m = ic.import_capture(os.path.join(fixcap, "corrupt.png"), "trackblazer",
                                        "scenario_overview", "ctx",
                                        capture_timestamp="20260101T000001",
                                        capture_dir=capdir, corpus_dir=corpusdir)
        expect(rc2 != 0 and len(mlines()) == 1,
               "a corrupt import must exit non-zero and append nothing")

        for n in ("a", "b"):
            ic.import_capture(os.path.join(fixcap, "dup_stem_diff_bytes", n + ".png"),
                              "trackblazer", "scenario_overview", "ctx",
                              capture_timestamp="20260101T000002",
                              capture_dir=capdir, corpus_dir=corpusdir)
        dupfiles = sorted(f for f in os.listdir(capdir) if f.startswith("20260101T000002"))
        expect(len(dupfiles) == 2,
               "same-stem different-bytes captures must both be preserved")
        expect(len(mlines()) == 3, "each distinct import appends one manifest line")

        rc4, e4, _m = ic.import_capture(os.path.join(fixcap, "exact_dup", "a.png"),
                                        "trackblazer", "scenario_overview", "ctx",
                                        capture_timestamp="20260101T000003",
                                        capture_dir=capdir, corpus_dir=corpusdir)
        rc5, e5, _m = ic.import_capture(os.path.join(fixcap, "exact_dup", "b.png"),
                                        "trackblazer", "scenario_overview", "ctx",
                                        capture_timestamp="20260101T000003",
                                        capture_dir=capdir, corpus_dir=corpusdir)
        expect(rc4 == 0 and rc5 == 0, "an exact-byte duplicate import must exit 0")
        expect(e4["sha256"] == e5["sha256"], "exact-byte duplicates share a SHA-256")
        written = [f for f in os.listdir(capdir) if f.startswith("20260101T000003")]
        expect(len(written) == 1, "an exact-byte duplicate must not write a second file")
        for line in mlines():
            json.loads(line)  # the manifest must stay valid JSONL

        # 27. The end-to-end lifecycle test passes on a throwaway registry.
        rc_e2e = subprocess.call(
            [sys.executable, os.path.join(HERE, "screenshots_e2e_test.py")],
            stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
        expect(rc_e2e == 0, "the end-to-end lifecycle test must exit 0")

        # 28. The research entry point: read-only subcommands change nothing; a
        #     --write-gated subcommand without --write writes nothing; check exits 0.
        rp = os.path.join(HERE, "research.py")
        real_root = os.path.abspath(os.path.join(HERE, "..", "..", "..", ".."))
        reg_rel = os.path.join("docs", "research-scratch", "screenshot-registry")

        def git_status():
            return subprocess.run(["git", "status", "--porcelain", reg_rel],
                                  capture_output=True, text=True, cwd=real_root).stdout

        before = git_status()
        expect(subprocess.call([sys.executable, rp, "plan-batch"],
                               stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL) == 0,
               "research plan-batch must exit 0")
        expect(subprocess.call([sys.executable, rp, "summarize-evidence"],
                               stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL) == 0,
               "research summarize-evidence must exit 0")
        expect(git_status() == before,
               "read-only research subcommands must not change the registry")

        rc_bp = subprocess.call([sys.executable, rp, "build-packet", "--topic", "zz-no-write"],
                                stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
        expect(rc_bp != 0, "build-packet without --write must exit non-zero")
        expect(not os.path.exists(os.path.join(real_root, reg_rel, "packets", "zz-no-write")),
               "build-packet without --write must write nothing")
        expect(git_status() == before,
               "the failed build-packet must not change the registry")

        expect(subprocess.call([sys.executable, rp, "check", "--skip-selftest"],
                               stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL) == 0,
               "research check --skip-selftest must exit 0")

        # 29. coverage-gap reads targets.json and reports an undersampled cluster.
        os.makedirs(S.REG, exist_ok=True)
        rows = [rec("2026-01-01 50000%d" % i, "sid-%d" % i) for i in range(4)]
        for r in rows:
            r["cluster_index"] = 7
        S.write_jsonl(S.INV, rows, "filename")
        open(S.EVIDENCE, "w").close()
        with open(os.path.join(S.REG, "targets.json"), "w", encoding="utf-8") as fh:
            fh.write(open(os.path.join(HERE, "fixtures", "targets", "undersampled.json"),
                          encoding="utf-8").read())
        buf = io.StringIO()
        with contextlib.redirect_stdout(buf):
            rc_gap = S.cmd_coverage_gap(types.SimpleNamespace())
        out = buf.getvalue()
        expect(rc_gap == 1, "coverage-gap must exit 1 while a gap remains")
        expect('"cluster_id": 7' in out, "coverage-gap must list the undersampled cluster")

        # 30. coverage-gap ignores a capture blocker a resolved entry supersedes.
        os.makedirs(os.path.join(S.REG, "capture"), exist_ok=True)
        shutil.copy(os.path.join(HERE, "fixtures", "capture", "resolved_entry.jsonl"),
                    S.CAPTURE_MANIFEST)
        buf = io.StringIO()
        with contextlib.redirect_stdout(buf):
            rc_gap2 = S.cmd_coverage_gap(types.SimpleNamespace())
        out2 = buf.getvalue()
        expect(rc_gap2 == 1, "coverage-gap must still exit 1 with a scenario gap")
        expect('"unity_cup"' not in out2.split('"capture_blockers_active"')[1].split("]")[0],
               "coverage-gap must not list unity_cup as an active blocker")
        expect('"unity_cup"' in out2.split('"capture_blockers_resolved"')[1].split("]")[0],
               "coverage-gap must list unity_cup as resolved")
        expect('"trackblazer"' in out2.split('"capture_blockers_active"')[1].split("]")[0],
               "coverage-gap must keep an unresolvable blocker active")

        # 31. A cited frame must carry a screen_type (Slice 10.3 invariant).
        def validate_out():
            buf = io.StringIO()
            with contextlib.redirect_stdout(buf):
                S.cmd_validate(a6)
            return buf.getvalue()

        typed = rec("2026-01-01 600000", "sid-600000", state="verified")
        typed["observations"] = [{"screen_type": "training", "text": "training screen", "by": "x"}]
        untyped = rec("2026-01-01 600001", "sid-600001", state="verified")
        untyped["observations"] = [{"text": "no screen type recorded", "by": "x"}]
        S.write_jsonl(S.INV, [typed, untyped], "filename")

        with open(S.EVIDENCE, "w", encoding="utf-8", newline="\n") as fh:
            fh.write(json.dumps(evrec("e1", ["2026-01-01 600000"], "observed")) + "\n")
        expect("no screen_type" not in validate_out(),
               "an observed claim on a typed frame must not raise the screen_type invariant")

        with open(S.EVIDENCE, "w", encoding="utf-8", newline="\n") as fh:
            fh.write(json.dumps(evrec("e1", ["2026-01-01 600001"], "observed")) + "\n")
        out_untyped = validate_out()
        expect("2026-01-01 600001" in out_untyped and "no screen_type" in out_untyped,
               "an observed claim on an untyped frame must raise the screen_type invariant")

        with open(S.EVIDENCE, "w", encoding="utf-8", newline="\n") as fh:
            fh.write(json.dumps(evrec("e1", ["2026-01-01 600000"], "inferred")) + "\n")
        cdir = os.path.join(S.REG, "contracts")
        os.makedirs(cdir, exist_ok=True)
        contract = {"screen_type": "training", "components": [],
                    "observed_interactions": [], "unresolved_questions": [],
                    "evidence": [{"field": "stat_row", "ref": "2026-01-01 600000",
                                  "sid": "sid-600000"}]}
        with open(os.path.join(cdir, "training-screen.json"), "w", encoding="utf-8") as fh:
            json.dump(contract, fh)
        expect("no screen_type" not in validate_out(),
               "a contract citing a typed verified frame must not raise the invariant")

        contract["evidence"] = [{"field": "stat_row", "ref": "2026-01-01 600001",
                                 "sid": "sid-600001"}]
        with open(os.path.join(cdir, "training-screen.json"), "w", encoding="utf-8") as fh:
            json.dump(contract, fh)
        out_bad_contract = validate_out()
        expect("2026-01-01 600001" in out_bad_contract
               and "no screen_type" in out_bad_contract,
               "a contract citing an untyped frame must raise the screen_type invariant")

        # 32. Scenario spellings collapse to one canonical slug, and the
        #     migration normalizes without touching already-canonical records.
        import importlib.util as _ilu
        _spec = _ilu.spec_from_file_location(
            "migrate_scenarios", os.path.join(HERE, "migrate_scenarios.py"))
        ms = _ilu.module_from_spec(_spec)
        _spec.loader.exec_module(ms)

        variants = [json.loads(l) for l in open(
            os.path.join(HERE, "fixtures", "scenarios", "variants.jsonl"),
            encoding="utf-8") if l.strip()]
        with open(os.path.join(S.REG, "targets.json"), "w", encoding="utf-8") as fh:
            json.dump({"screen_types": {"required": [], "min_verified_per_type": 0},
                       "scenarios": {"required": ["ura_finale"],
                                     "min_verified_per_scenario": 1},
                       "scenario_aliases": {
                           "ura_finale": ["URA Finale", "ura finale", "URAFinale",
                                          "Ura Finale"],
                           "unity_cup": ["Unity-Cup", "Unity Cup", "UnityCup"]}},
                      fh)
        canon = {S.canonical_scenario(r.get("scenario")) for r in variants
                 if r.get("scenario")}
        expect(canon == {"ura_finale", "unity_cup", "trackblazer"},
               "every scenario variant must collapse to one canonical slug: %s" % sorted(canon))
        expect(S.canonical_scenario("") == "",
               "an empty scenario must stay empty")
        expect(S.canonical_scenario(None) is None,
               "a null scenario must stay null")

        seeded = [rec(r["ref"], r["sid"]) for r in variants]
        for r, v in zip(seeded, variants):
            r["scenario"] = v.get("scenario")
        S.write_jsonl(S.INV, seeded, "filename")
        before_rows = S.read_jsonl(S.INV)

        rc = ms.run(False)
        expect(rc == 0, "a dry-run migration must exit 0")
        expect(S.read_jsonl(S.INV) == before_rows,
               "a dry-run migration must not touch the inventory")

        rc = ms.run(True)
        expect(rc == 0, "an applying migration must exit 0")
        after_rows = S.read_jsonl(S.INV)
        expect(all(r.get("scenario") == S.canonical_scenario(r.get("scenario"))
                   for r in after_rows),
               "every scenario value must be canonical after migration")
        untouched = {r["ref"]: r.get("scenario") for r in before_rows
                     if r.get("scenario") in (None, "ura_finale", "unity_cup",
                                              "trackblazer")}
        for r in after_rows:
            if r["ref"] in untouched:
                expect(r.get("scenario") == untouched[r["ref"]],
                       "an already-canonical record must not be rewritten: %s" % r["ref"])
        expect(sum(1 for r in after_rows
                   if r.get("scenario") == "ura_finale") == 5,
               "all five URA Finale spellings must land on ura_finale")

        expect("non-canonical scenario" not in validate_out(),
               "canonical scenario values must not raise the invariant")
        for r in after_rows:
            if r.get("scenario") == "ura_finale":
                r["scenario"] = "URA Finale"
        S.write_jsonl(S.INV, after_rows, "filename")
        out_variant = validate_out()
        expect("non-canonical scenario" in out_variant,
               "a variant scenario spelling must fail validation")

        # 33. A KEYS entry with no redirect is caught before any test writes.
        cases = json.load(open(os.path.join(HERE, "fixtures", "selftest",
                                            "missing_redirect.json"),
                               encoding="utf-8"))["cases"]
        for c in cases:
            got = _keys_missing_redirect(c["keys"], c["redirects"])
            expect(got == c["expect_missing"],
                   "redirect guard must report %s for case %r, got %s"
                   % (c["expect_missing"], c["name"], got))

        real_keys, real_redirects = KEYS, _REDIRECT_KEYS
        expect(_keys_missing_redirect(real_keys, real_redirects) == [],
               "the real selftest must have no missing redirects")
        try:
            globals()["KEYS"] = list(real_keys) + ["A_KEY_NOBODY_REDIRECTS"]
            _assert_keys_redirected()
            expect(False, "the guard must raise when a KEYS entry has no redirect")
        except AssertionError as exc:
            expect("A_KEY_NOBODY_REDIRECTS" in str(exc),
                   "the guard must name the un-redirected key: %s" % exc)
        finally:
            globals()["KEYS"] = real_keys
        expect(_keys_missing_redirect(KEYS, _REDIRECT_KEYS) == [],
               "KEYS must be restored after the guard test")

        # 34. `unclassified` requires a follow_up naming the ambiguity.
        unc_cases = json.load(open(os.path.join(HERE, "fixtures", "validate",
                                                "unclassified.json"),
                                   encoding="utf-8"))["cases"]
        for c in unc_cases:
            row = rec(c["ref"], c["sid"], state=c["state"])
            row["review_history"] = [{"state": "verified", "by": "x", "date": S.today()}]
            obs = {"screen_type": c["screen_type"], "text": "backfill", "by": "x"}
            if c["follow_up_question"] is not None:
                obs["follow_up_question"] = c["follow_up_question"]
            row["observations"] = [obs]
            S.write_jsonl(S.INV, [row], "filename")
            open(S.EVIDENCE, "w").close()
            out_u = validate_out()
            raised = "unclassified without a follow_up" in out_u
            expect(raised == c["expect_fail"],
                   "case %r must %s: %s" % (c["name"],
                                            "fail" if c["expect_fail"] else "pass",
                                            out_u.strip().splitlines()[-1] if out_u.strip() else ""))

        # 35. evidence-edit corrects under the lock with an audit entry, and a
        #     surgical edit that skips the command is caught by validate.
        fix = json.load(open(os.path.join(HERE, "fixtures", "evidence_edit",
                                          "cases.json"), encoding="utf-8"))
        base = fix["base_claim"]

        def seed_claims(claim):
            open(S.EVIDENCE, "w", encoding="utf-8", newline="\n").write(
                json.dumps(claim, sort_keys=True) + "\n")

        def seed_frames():
            rows = []
            for ref, sid, crep in (("2026-01-01 400000", "s-a", "Screenshot 2026-01-01 400000.png"),
                                   ("2026-01-01 400001", "s-b", "Screenshot 2026-01-01 400001.png")):
                r = rec(ref, sid, state="verified")
                r["cluster_rep"] = crep
                r["observations"] = [{"screen_type": "training", "text": "t", "by": "x"}]
                rows.append(r)
            S.write_jsonl(S.INV, rows, "filename")

        def audit_rows():
            return [json.loads(l) for l in
                    open(os.path.join(S.REG, "review-log.jsonl"), encoding="utf-8")
                    if l.strip()]

        for c in fix["cases"]:
            seed_claims(base)
            seed_frames()
            open(os.path.join(S.REG, "review-log.jsonl"), "w").close()
            ns = types.SimpleNamespace(
                eid=base["eid"], reason=c["reason"], observer="agent",
                claim=c["edit"].get("--claim"),
                status=c["edit"].get("--status"),
                confidence=c["edit"].get("--confidence"),
                supports=c["edit"].get("--supports"),
                independent=c["edit"].get("--independent"),
                contradicts=c["edit"].get("--contradicts"),
                limits=c["edit"].get("--limits"),
                open=c["edit"].get("--open"),
                scenario=c["edit"].get("--scenario"))
            if c["name"] == "surgical_edit_diverges_from_audit":
                rc = S.cmd_evidence_edit(ns)
                expect(rc == c["expect_rc"],
                       "evidence-edit case %r must exit %s, got %s"
                       % (c["name"], c["expect_rc"], rc))
                rows = S.read_jsonl(S.EVIDENCE)
                rows[0]["limits"] = ["overwritten after the audit row was written"]
                S.write_jsonl(S.EVIDENCE, rows, "eid")
            else:
                rc = S.cmd_evidence_edit(ns)
                expect(rc == c["expect_rc"],
                       "evidence-edit case %r must exit %s, got %s"
                       % (c["name"], c["expect_rc"], rc))
            edits = [r for r in audit_rows() if r.get("event") == "evidence_edited"]
            expect(bool(edits) == c["expect_audit"],
                   "evidence-edit case %r audit presence must be %s"
                   % (c["name"], c["expect_audit"]))
            if c["expect_audit"]:
                expect(edits[0].get("reason") == c["reason"],
                       "evidence-edit case %r must record its reason" % c["name"])
                expect("before" in edits[0] and "after" in edits[0],
                       "evidence-edit case %r must record before and after" % c["name"])
            out_e = validate_out()
            expect(("evidence_edited" in out_e) == c["expect_validate_fail"],
                   "evidence-edit case %r validate must %s: %s"
                   % (c["name"], "fail" if c["expect_validate_fail"] else "pass",
                      out_e.strip().splitlines()[-1] if out_e.strip() else ""))

        # 36. Derived artifacts are validated too: a packet, HANDOFF counts,
        #     and both capture-manifest invariants each fail on their fixture.
        import glob as _glob
        for fx in sorted(_glob.glob(os.path.join(HERE, "fixtures", "validate",
                                                 "derived", "*.json"))):
            spec = json.load(open(fx, encoding="utf-8"))
            name = os.path.basename(fx)
            if "frames" in spec:
                rows = []
                for f in spec["frames"]:
                    r = rec(f["ref"], f["sid"], state=f.get("state", "verified"))
                    r["review_history"] = [{"state": "verified", "by": "x",
                                            "date": S.today()}]
                    r["observations"] = f.get("observations", [])
                    rows.append(r)
                S.write_jsonl(S.INV, rows, "filename")
            open(S.EVIDENCE, "w").close()
            open(os.path.join(S.REG, "review-log.jsonl"), "w").close()
            if "targets" in spec:
                with open(os.path.join(S.REG, "targets.json"), "w",
                          encoding="utf-8") as fh:
                    json.dump(spec["targets"], fh)
            if "capture_manifest" in spec:
                os.makedirs(os.path.join(S.REG, "capture"), exist_ok=True)
                with open(S.CAPTURE_MANIFEST, "w", encoding="utf-8",
                          newline="\n") as fh:
                    for e in spec["capture_manifest"]:
                        fh.write(json.dumps(e, sort_keys=True) + "\n")
            if "manifest" in spec and "topic" in spec:
                pdir = os.path.join(S.REG, "packets", spec["topic"])
                os.makedirs(pdir, exist_ok=True)
                with open(os.path.join(pdir, "manifest.json"), "w",
                          encoding="utf-8") as fh:
                    json.dump(spec["manifest"], fh)
            if "handoff" in spec:
                with open(os.path.join(S.REG, "HANDOFF.md"), "w",
                          encoding="utf-8") as fh:
                    json.dump(spec["handoff"], fh)
            out_d = validate_out()
            expect(spec["expect_message"] in out_d,
                   "fixture %r must raise %r: %s"
                   % (name, spec["expect_message"],
                      out_d.strip().splitlines()[-1] if out_d.strip() else ""))
            if os.path.exists(os.path.join(S.REG, "HANDOFF.md")):
                os.remove(os.path.join(S.REG, "HANDOFF.md"))

        # 37. A screen type must come from required, additional, or the
        #     vocabulary already in use; free text is rejected.
        vocab = json.load(open(os.path.join(HERE, "fixtures", "vocabulary",
                                            "additional_types.json"),
                               encoding="utf-8"))
        with open(os.path.join(S.REG, "targets.json"), "w", encoding="utf-8") as fh:
            json.dump(vocab["targets"], fh)
        for c in vocab["cases"]:
            row = rec(c["ref"], c["sid"], state="verified")
            row["review_history"] = [{"state": "verified", "by": "x",
                                      "date": S.today()}]
            row["observations"] = [{"screen_type": c["screen_type"],
                                    "text": "backfill", "by": "x"}]
            S.write_jsonl(S.INV, [row], "filename")
            open(S.EVIDENCE, "w").close()
            open(os.path.join(S.REG, "review-log.jsonl"), "w").close()
            out_v = validate_out()
            raised = "screen_type" in out_v and "not in the" in out_v
            expect(raised == c["expect_fail"],
                   "vocabulary case %r must %s: %s"
                   % (c["name"], "fail" if c["expect_fail"] else "pass",
                      out_v.strip().splitlines()[-1] if out_v.strip() else ""))

        # 38. cluster-split reassigns an over-collapsed cluster by screen
        #     type, audits each reassignment, survives a re-inventory, and
        #     refuses an assignment it cannot justify.
        cs = json.load(open(os.path.join(HERE, "fixtures", "clusters",
                                         "two_type_cluster.json"),
                            encoding="utf-8"))
        with open(os.path.join(S.REG, "targets.json"), "w", encoding="utf-8") as fh:
            json.dump(cs["targets"], fh)
        for f in cs["frames"]:
            make_png(os.path.join(src, "Screenshot %s.png" % f["ref"]),
                     1920, 1080, int(f["ref"][-6:]))
        with open(S.CLUSTERS, "w", encoding="utf-8") as fh:
            json.dump([{"rep": "Screenshot %s.png" % cs["frames"][0]["ref"],
                        "count": len(cs["frames"]), "dim": [1920, 1080],
                        "members": ["Screenshot %s.png" % f["ref"]
                                    for f in cs["frames"]]}], fh)
        expect(S.cmd_inventory(a) == 0, "split-case inventory should exit 0")
        cs_inv = {r["ref"]: r for r in S.read_jsonl(S.INV)}
        for f in cs["frames"]:
            expect(cs_inv[f["ref"]]["cluster_index"] == cs["expect"]["source_cluster_id"],
                   "fixture frame %s must start in the source cluster" % f["ref"])
        for f in cs["frames"]:
            if f["screen_type"]:
                expect(S.cmd_review(types.SimpleNamespace(
                    ids=f["ref"], state="verified", observer="selftest",
                    confidence="high", note="typed by selftest", batch=None,
                    scenario=None, category=None, questions=None, reason=None,
                    next_action=None, screen_type=f["screen_type"],
                    career_phase=None, mechanics=None, values=None,
                    limitations=None, follow_up=None)) == 0,
                    "review of %s should exit 0" % f["ref"])
        assign_path = os.path.join(reg, "assignments.json")

        def split(assignments, reason="selftest split"):
            with open(assign_path, "w", encoding="utf-8") as fh:
                json.dump(assignments, fh)
            return S.cmd_cluster_split(types.SimpleNamespace(
                cluster=cs["expect"]["source_cluster_id"], assignments=assign_path,
                observer="selftest", reason=reason))

        for c in cs["reject_cases"]:
            rc = split(c["assignments"])
            expect(rc == c["expect_rc"],
                   "split reject case %r must exit %s, got %s"
                   % (c["name"], c["expect_rc"], rc))
            rows_after = {r["ref"]: r for r in S.read_jsonl(S.INV)}
            expect(all(rows_after[f["ref"]]["cluster_index"]
                       == cs["expect"]["source_cluster_id"] for f in cs["frames"]),
                   "a rejected split must not move any frame: %s" % c["name"])

        expect(split(cs["assignments"], "selftest: two-type cluster") == 0,
               "the split itself should exit 0")
        after = {r["ref"]: r for r in S.read_jsonl(S.INV)}
        learn_refs = [f["ref"] for f in cs["frames"] if f["screen_type"] == "learn"]
        learn_cids = {after[r]["cluster_index"] for r in learn_refs}
        expect(len(learn_cids) == 1
               and cs["expect"]["source_cluster_id"] not in learn_cids,
               "both learn frames must share one new cluster, got %s" % learn_cids)
        learn_members = [r for r in after.values() if r["cluster_index"] in learn_cids]
        expect(len(learn_members) == cs["expect"]["learn_members"],
               "the learn cluster must hold %d frames, got %d"
               % (cs["expect"]["learn_members"], len(learn_members)))
        expect(all(r["cluster_size"] == cs["expect"]["learn_members"]
                   for r in learn_members),
               "cluster_size must be recomputed after the split")
        expect(all(r["cluster_rep"] in {x["filename"] for x in learn_members}
                   for r in learn_members),
               "the learn rep must be a member of the learn cluster")
        for r in learn_members:
            expect(r["near_duplicate_cluster"] ==
                   sorted(x["filename"] for x in learn_members
                          if x["filename"] != r["filename"]),
                   "near_duplicate_cluster must name the other learn members")
        train_rows = [after[f["ref"]] for f in cs["frames"]
                      if f["screen_type"] == "training"]
        expect(len({r["cluster_index"] for r in train_rows}) == 1,
               "the training frame must have its own cluster")
        expect(train_rows[0]["cluster_size"] == cs["expect"]["training_members"],
               "the training cluster must hold %d frame(s)"
               % cs["expect"]["training_members"])
        expect(train_rows[0]["near_duplicate_cluster"] == [],
               "a singleton cluster has no near-duplicate neighbours")
        res = after[cs["expect"]["residual_ref"]]
        expect(res["cluster_index"] == cs["expect"]["source_cluster_id"],
               "the untyped frame must stay in the source cluster")
        expect(res["cluster_size"] == cs["expect"]["residual_members"],
               "the residual cluster must be recomputed")
        expect(res["cluster_rep"] == res["filename"],
               "the residual rep must be its only member")
        events = [json.loads(l) for l in
                  open(os.path.join(S.REG, "review-log.jsonl"), encoding="utf-8")
                  if l.strip()]
        splits = [e for e in events if e.get("event") == "cluster_split"]
        expect(len(splits) == cs["expect"]["split_events"],
               "one cluster_split event per reassigned frame, got %d" % len(splits))
        expect(all(e.get("by") for e in splits),
               "every cluster_split event must name an observer")
        expect(all(e.get("from_cluster") == cs["expect"]["source_cluster_id"]
                   and e.get("to_cluster") is not None for e in splits),
               "every cluster_split event must record from and to clusters")
        expect({e["ref"] for e in splits} ==
               {f["ref"] for f in cs["frames"] if f["screen_type"]},
               "the split events must name exactly the reassigned frames")
        expect(all(e.get("sid") == after[e["ref"]].get("sid") for e in splits),
               "a cluster_split event must carry the frame's sid")

        # The split must survive a re-inventory: review-log is the durable
        # record, so a fresh derivation from clusters.json replays it.
        expect(S.cmd_inventory(a) == 0, "re-inventory after a split should exit 0")
        replayed = {r["ref"]: r for r in S.read_jsonl(S.INV)}
        for f in cs["frames"]:
            expect(replayed[f["ref"]]["cluster_index"]
                   == after[f["ref"]]["cluster_index"],
                   "the split must survive a re-inventory: %s" % f["ref"])
            expect(replayed[f["ref"]]["cluster_size"]
                   == after[f["ref"]]["cluster_size"],
                   "cluster_size must survive a re-inventory: %s" % f["ref"])
    finally:
        for k, v in saved.items():
            setattr(S, k, v)
        for path, digest in live_digests.items():
            if not os.path.exists(path):
                continue
            if hashlib.sha256(open(path, "rb").read()).hexdigest() != digest:
                print("FAIL  live registry file was modified by the selftest: %s" % path)
                failures.append("selftest mutated a live registry file: %s" % path)
        import shutil
        shutil.rmtree(tmp, ignore_errors=True)

    if failures:
        for f in failures:
            print("FAIL  %s" % f)
        print("selftest: %d assertion failures" % len(failures))
        return 1
    print("selftest: all assertions held on the throwaway corpus")
    return 0


if __name__ == "__main__":
    sys.exit(run())
