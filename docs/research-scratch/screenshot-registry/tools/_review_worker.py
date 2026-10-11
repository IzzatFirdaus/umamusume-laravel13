"""Worker for the cross-process concurrency test.

Usage:
    python _review_worker.py <inventory.jsonl> <ref> <observer>

Patches the screenshots module to point at the given inventory and runs a
single `review` to verified.  Used only by screenshots_concurrency_test.py.
"""
import os
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, HERE)
import screenshots as S

inv = os.path.abspath(sys.argv[1])
S.INV = inv
S.REG = os.path.dirname(inv)
S.BATCHES = os.path.join(S.REG, "batches")
os.makedirs(S.BATCHES, exist_ok=True)

args = S.argparse.Namespace(
    ids=sys.argv[2], state="verified", batch="CTEST", observer=sys.argv[3],
    confidence="high", note="proc note %s" % sys.argv[3], reason=None,
    next_action=None, scenario=None, category=None, questions=None,
)
sys.exit(S.cmd_review(args))
