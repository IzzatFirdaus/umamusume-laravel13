"""Single safe entry point for a research session.

Read-only by default.  Subcommands that write anything into the registry tree
require an explicit ``--write`` and print the target before writing.  Mutating
paths acquire the registry lock.

Exit codes: 0 success, 1 validation/consistency failure, 2 usage error,
3 lock contention.
"""
import argparse
import os
import subprocess
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, HERE)
import screenshots as S  # noqa: E402


def _py(script, *rest):
    return [sys.executable, os.path.join(HERE, script)] + list(rest)


def _run(argv):
    return subprocess.call(argv)


def cmd_check(args):
    steps = [_py("screenshots.py", "validate")]
    if not args.skip_selftest:
        steps.append(_py("screenshots.py", "selftest"))
    steps.append(_py("screenshots_concurrency_test.py"))
    steps.append(_py("screenshots_e2e_test.py"))
    steps.append(_py("validate_contracts.py", "--path", os.path.join(S.REG, "contracts")))
    for step in steps:
        rc = _run(step)
        if rc != 0:
            print("research check: FAILED at %s (exit %d)" % (os.path.basename(step[1]), rc),
                  file=sys.stderr)
            return 1
    print("research check: all gates passed")
    return 0


def cmd_plan_batch(args):
    argv = _py("build_batch.py", "--limit", str(args.size), "--dry-run")
    if args.out:
        if not args.write:
            print("research plan-batch: --out requires --write", file=sys.stderr)
            return 2
        print("research plan-batch: will write %s" % args.out)
        argv += ["--out", args.out]
        try:
            with S.registry_lock():
                return _run(argv)
        except TimeoutError:
            print("research plan-batch: registry lock busy", file=sys.stderr)
            return 3
    return _run(argv)


def cmd_summarize_evidence(args):
    argv = _py("screenshots.py", "evidence-query")
    if args.status:
        argv += ["--status", args.status]
    return _run(argv)


def cmd_build_packet(args):
    if not args.write:
        print("research build-packet: requires --write", file=sys.stderr)
        return 2
    out = args.out or os.path.join(S.REG, "packets", args.topic)
    print("research build-packet: will write %s" % out)
    argv = _py("evidence_packet.py", "--topic", args.topic, "--out", out)
    try:
        with S.registry_lock():
            return _run(argv)
    except TimeoutError:
        print("research build-packet: registry lock busy", file=sys.stderr)
        return 3


def main():
    ap = argparse.ArgumentParser(description="Safe research entry point.")
    sub = ap.add_subparsers(dest="cmd", required=True)

    c = sub.add_parser("check", help="run the registry gates")
    c.add_argument("--skip-selftest", action="store_true",
                   help="omit the selftest step (used when invoked from the selftest)")
    c.set_defaults(fn=cmd_check)

    p = sub.add_parser("plan-batch", help="plan a candidate batch (read-only)")
    p.add_argument("--size", type=int, default=25)
    p.add_argument("--out", help="write the candidate manifest here (needs --write)")
    p.add_argument("--write", action="store_true")
    p.set_defaults(fn=cmd_plan_batch)

    e = sub.add_parser("summarize-evidence", help="list evidence claims")
    e.add_argument("--status", choices=["inferred", "observed", "confirmed"])
    e.set_defaults(fn=cmd_summarize_evidence)

    b = sub.add_parser("build-packet", help="build an evidence packet (needs --write)")
    b.add_argument("--topic", required=True)
    b.add_argument("--out")
    b.add_argument("--write", action="store_true")
    b.set_defaults(fn=cmd_build_packet)

    args = ap.parse_args()
    return args.fn(args)


if __name__ == "__main__":
    sys.exit(main())
