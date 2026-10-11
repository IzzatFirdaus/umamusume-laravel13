"""Concurrency regression test for the registry lock and atomic writes.

Phases:
  1. Sequential  — 4 `cmd_review` calls; all records survive with history.
  2. Threads     — 4 in-process `cmd_review` calls on distinct frames.
  3. Processes   — 4 subprocess `cmd_review` calls (the original incident).
  4. Evidence    — 4 concurrent `cmd_evidence` calls; no lost back-links and
                   unique eids (exercises the INV read-modify-write).
  5. Batch       — 4 concurrent `cmd_batch` result appends to one manifest;
                   no lost results.

Each phase asserts no exceptions, no malformed JSONL, no lost updates, and
matching review history.  Runs only against a throwaway corpus.

Run with:
    python tools/screenshots_concurrency_test.py
"""
import json
import os
import shutil
import sys
import tempfile
import threading

HERE = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, HERE)
import screenshots as S

N_WRITERS = 4
BASELINE_RECORDS = 12


def _build_rows(tmpdir):
    """Build a fresh list of BASELINE_RECORDS inventory rows."""
    src_dir = os.path.join(tmpdir, "src")
    os.makedirs(src_dir, exist_ok=True)
    rows = []
    for i in range(BASELINE_RECORDS):
        ref = "2026-01-01 10%04d" % i
        fname = "Screenshot %s.png" % ref
        fpath = os.path.join(src_dir, fname)
        with open(fpath, "wb") as fh:
            fh.write(b"fake-png-%d" % i)
        rec = S.new_record(ref)
        rec["sid"] = "deadbeef%04d" % i
        rec["sha256"] = "x" * 64
        rec["state"] = "not_reviewed"
        rec["path"] = "src/" + fname
        rec["review_history"] = []
        rec["observations"] = []
        rows.append(rec)
    return rows


def _make_corpus(tmpdir):
    """Write a fresh inventory and return its path."""
    inv_path = os.path.join(tmpdir, "inventory.jsonl")
    S.write_jsonl(inv_path, _build_rows(tmpdir), "filename")
    return inv_path


def _make_args(ids, observer, note, batch="CTEST"):
    return S.argparse.Namespace(
        ids=ids, state="verified", batch=batch, observer=observer,
        confidence="high", note=note, reason=None, next_action=None,
        scenario=None, category=None, questions=None,
    )


def main():
    tmp = tempfile.mkdtemp(prefix="screenshot-reg-test-")
    inv_path = _make_corpus(tmp)

    # Patch module-level paths to point at our throwaway corpus.
    S.INV = inv_path
    S.REG = tmp
    S.EVIDENCE = os.path.join(tmp, "evidence.jsonl")
    S.BATCHES = os.path.join(tmp, "batches")
    S.CLUSTERS = os.path.join(tmp, "nonexistent-clusters.json")
    os.makedirs(S.BATCHES, exist_ok=True)

    # ----------------------------------------------------------------
    # Phase 1: sequential
    # ----------------------------------------------------------------
    for i in range(N_WRITERS):
        args = _make_args(
            ids="2026-01-01 10000%d" % i,
            observer="writer-%d" % i,
            note="seq note %d" % i,
        )
        rc = S.cmd_review(args)
        assert rc == 0, "sequential cmd_review returned %d" % rc
    print("Phase 1: %d sequential review calls OK" % N_WRITERS)

    inv = S.read_jsonl(inv_path)
    assert len(inv) == BASELINE_RECORDS, \
        "inventory has %d records, expected %d" % (len(inv), BASELINE_RECORDS)
    verified = [r for r in inv if r["state"] == "verified"]
    assert len(verified) == N_WRITERS, \
        "expected %d verified, got %d" % (N_WRITERS, len(verified))
    for r in verified:
        assert len(r["review_history"]) == 1, \
            "verified frame %s has %d review_history entries" % (r["ref"], len(r["review_history"]))
        assert len(r["observations"]) == 1, \
            "verified frame %s has %d observations" % (r["ref"], len(r["observations"]))
    print("Phase 1: JSONL valid, %d verified, %d not_reviewed"
          % (len(verified), BASELINE_RECORDS - N_WRITERS))

    # ----------------------------------------------------------------
    # Phase 2: concurrent threads (in-process, simulates the race)
    # ----------------------------------------------------------------
    S.write_jsonl(inv_path, _build_rows(tmp), "filename")
    # Clear review-log for a clean count
    review_log_path = os.path.join(tmp, "review-log.jsonl")
    if os.path.exists(review_log_path):
        os.remove(review_log_path)

    errors = []

    def do_review(i):
        try:
            args = _make_args(
                ids="2026-01-01 10000%d" % i,
                observer="thread-%d" % i,
                note="concurrent note %d" % i,
            )
            rc = S.cmd_review(args)
            if rc != 0:
                errors.append("thread %d: cmd_review returned %d" % (i, rc))
        except Exception as e:
            errors.append("thread %d: %r" % (i, e))

    threads = [threading.Thread(target=do_review, args=(i,)) for i in range(N_WRITERS)]
    for t in threads:
        t.start()
    for t in threads:
        t.join()

    assert not errors, "concurrent errors: %s" % errors
    inv = S.read_jsonl(inv_path)
    assert len(inv) == BASELINE_RECORDS, \
        "after concurrent: %d records, expected %d" % (len(inv), BASELINE_RECORDS)
    verified = [r for r in inv if r["state"] == "verified"]
    assert len(verified) == N_WRITERS, \
        "after concurrent: expected %d verified, got %d" % (N_WRITERS, len(verified))
    for r in verified:
        assert len(r["review_history"]) == 1, \
            "frame %s: %d review_history entries" % (r["ref"], len(r["review_history"]))
    print("Phase 2: all %d threads completed without corruption" % N_WRITERS)

    # ----------------------------------------------------------------
    # Phase 3: cross-process concurrency (the original incident's shape)
    # ----------------------------------------------------------------
    S.write_jsonl(inv_path, _build_rows(tmp), "filename")
    if os.path.exists(review_log_path):
        os.remove(review_log_path)

    import subprocess
    worker = os.path.join(HERE, "_review_worker.py")
    procs = []
    for i in range(N_WRITERS):
        p = subprocess.Popen(
            [sys.executable, worker, inv_path, "2026-01-01 10000%d" % i, "proc-%d" % i],
            stdout=subprocess.PIPE, stderr=subprocess.PIPE,
        )
        procs.append(p)
    proc_errors = []
    for i, p in enumerate(procs):
        out, err = p.communicate()
        if p.returncode != 0:
            proc_errors.append("proc %d rc=%d stderr=%s" % (i, p.returncode, err.decode("utf-8", "replace")[:300]))
    assert not proc_errors, "cross-process errors: %s" % proc_errors
    inv = S.read_jsonl(inv_path)
    assert len(inv) == BASELINE_RECORDS, \
        "after cross-process: %d records, expected %d" % (len(inv), BASELINE_RECORDS)
    verified = [r for r in inv if r["state"] == "verified"]
    assert len(verified) == N_WRITERS, \
        "after cross-process: expected %d verified, got %d" % (N_WRITERS, len(verified))
    for r in verified:
        assert len(r["review_history"]) == 1, \
            "frame %s: %d review_history entries" % (r["ref"], len(r["review_history"]))
    print("Phase 3: all %d processes completed without corruption" % N_WRITERS)

    # ----------------------------------------------------------------
    # Phase 4: concurrent evidence additions (read-modify-write of INV)
    # ----------------------------------------------------------------
    S.write_jsonl(inv_path, _build_rows(tmp), "filename")
    if os.path.exists(S.EVIDENCE):
        os.remove(S.EVIDENCE)
    if os.path.exists(review_log_path):
        os.remove(review_log_path)

    ev_errors = []

    def do_evidence(i):
        try:
            args = S.argparse.Namespace(
                id="claim-%d" % i, claim="claim %d" % i,
                supports="2026-01-01 10000%d" % i, kind="observed",
                scenario=None, topic=None, confidence="high",
                observer="ev-%d" % i, batch="CTEST", limits=None,
                contradicts=None, open=None,
            )
            rc = S.cmd_evidence(args)
            if rc != 0:
                ev_errors.append("ev %d: rc=%d" % (i, rc))
        except Exception as e:
            ev_errors.append("ev %d: %r" % (i, e))

    threads = [threading.Thread(target=do_evidence, args=(i,)) for i in range(N_WRITERS)]
    for t in threads:
        t.start()
    for t in threads:
        t.join()
    assert not ev_errors, "evidence concurrency errors: %s" % ev_errors
    ev = S.read_jsonl(S.EVIDENCE)
    assert len(ev) == N_WRITERS, "expected %d evidence claims, got %d" % (N_WRITERS, len(ev))
    eids = sorted(e["eid"] for e in ev)
    assert len(set(eids)) == N_WRITERS, "evidence eids must be unique: %s" % eids
    inv = S.read_jsonl(inv_path)
    backlinks = sum(len(r.get("evidence", [])) for r in inv)
    assert backlinks == N_WRITERS, \
        "expected %d back-links after concurrent evidence, got %d" % (N_WRITERS, backlinks)
    print("Phase 4: all %d evidence additions survived with unique eids" % N_WRITERS)

    # ----------------------------------------------------------------
    # Phase 5: concurrent batch result appends to one manifest
    # ----------------------------------------------------------------
    bid = "CTEST"
    bargs = S.argparse.Namespace(
        id=bid, ids=",".join("2026-01-01 10000%d" % i for i in range(N_WRITERS)),
        question="q", why="w", verify=None, next=None, state=None,
        observer="init", confidence=None, note=None, reason=None, show=False)
    S.cmd_batch(bargs)

    b_errors = []

    def do_batch_result(i):
        try:
            args = S.argparse.Namespace(
                id=bid, ids=",".join("2026-01-01 10000%d" % j for j in range(N_WRITERS)),
                question=None, why=None, verify=None, next=None,
                state="verified", observer="b-%d" % i, confidence="high",
                note="result %d" % i, reason=None, show=False)
            rc = S.cmd_batch(args)
            if rc != 0:
                b_errors.append("batch %d: rc=%d" % (i, rc))
        except Exception as e:
            b_errors.append("batch %d: %r" % (i, e))

    threads = [threading.Thread(target=do_batch_result, args=(i,)) for i in range(N_WRITERS)]
    for t in threads:
        t.start()
    for t in threads:
        t.join()
    assert not b_errors, "batch concurrency errors: %s" % b_errors
    manifest = json.load(open(os.path.join(S.BATCHES, "%s.json" % bid), encoding="utf-8"))
    assert len(manifest["results"]) == N_WRITERS, \
        "expected %d batch results after concurrent append, got %d" % (N_WRITERS, len(manifest["results"]))
    print("Phase 5: all %d batch result appends survived" % N_WRITERS)

    shutil.rmtree(tmp)
    print("concurrency regression test: PASS")
    return 0


if __name__ == "__main__":
    sys.exit(main())
