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
        "LEGACY", "EXTRAS", "EVIDENCE", "REFS_INDEX", "DISPOSITIONS")


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


def run():
    tmp = tempfile.mkdtemp(prefix="screenshots-selftest-")
    src = os.path.join(tmp, "game-screenshots")
    reg = os.path.join(tmp, "registry")
    legacy = os.path.join(tmp, "notes")
    os.makedirs(src)
    os.makedirs(reg)
    os.makedirs(legacy)

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
                 "LEGACY": legacy, "EXTRAS": legacy}.items():
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
                r["evidence"] = [{"claim": "x", "supports": ["no-such-frame"], "kind": "observed"}]
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
            return {"schema_version": 1, "eid": eid, "claim": "c", "kind": "observed",
                    "status": status, "supports": supports,
                    "independent_supports": independent or [], "confidence": "high",
                    "by": "x", "date": S.today()}

        a = rec("2026-01-01 400000", "s-a")
        a["cluster_rep"] = "Screenshot 2026-01-01 400000.png"
        b = rec("2026-01-01 400001", "s-b")
        b["cluster_rep"] = "Screenshot 2026-01-01 400001.png"
        c = rec("2026-01-01 400002", "s-c")
        c["cluster_rep"] = "Screenshot 2026-01-01 400000.png"
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
    finally:
        for k, v in saved.items():
            setattr(S, k, v)
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
