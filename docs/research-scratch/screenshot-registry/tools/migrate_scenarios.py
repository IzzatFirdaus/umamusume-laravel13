"""Normalize scenario spellings in inventory.jsonl to their canonical slug.

Two string forms for one scenario (`URA Finale` and `ura_finale`) made every
scenario query depend on a text fallback. This migration rewrites the stored
value through ``canonical_scenario`` and appends one audit entry per changed
record to ``review-log.jsonl``, so the correction is traceable rather than
silent. Records already carrying the canonical form are left untouched, which
makes the migration idempotent.

Usage:
    python tools/migrate_scenarios.py --apply
    python tools/migrate_scenarios.py --check
"""
import argparse
import datetime
import json
import os
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, HERE)
import screenshots as S  # noqa: E402


def _atomic_write(path, rows, key):
    tmp = "%s.tmp.migrate-%d" % (path, os.getpid())
    with open(tmp, "w", encoding="utf-8", newline="\n") as fh:
        for r in rows:
            fh.write(json.dumps(r, ensure_ascii=False, sort_keys=True) + "\n")
    os.replace(tmp, path)


def _migrate_file(path, label, id_key, apply_changes):
    """Normalize `scenario` in one JSONL file. Returns the change list."""
    if not os.path.exists(path):
        return []
    rows = S.read_jsonl(path)
    changed, out = [], []
    for r in rows:
        before = r.get("scenario")
        after = S.canonical_scenario(before)
        if after != before:
            r = dict(r)
            r["scenario"] = after
            changed.append({"file": label, "eid": r.get(id_key),
                            "ref": r.get("ref"), "sid": r.get("sid"),
                            "before": before, "after": after})
        out.append(r)

    if changed and apply_changes:
        _atomic_write(path, out, id_key if id_key == "filename" else "eid")
    return changed


def run(apply_changes):
    changed = []
    changed += _migrate_file(S.INV, "inventory.jsonl", "filename", apply_changes)
    changed += _migrate_file(S.EVIDENCE, "evidence.jsonl", "eid", apply_changes)

    if not changed:
        print("migrate_scenarios: no non-canonical scenario values found")
        return 0

    for c in changed:
        who = c["eid"] or c["ref"]
        print("  %s %s: %r -> %r" % (c["file"], who, c["before"], c["after"]))
    print("migrate_scenarios: %d record(s) to normalize" % len(changed))

    if not apply_changes:
        print("migrate_scenarios: dry run only; pass --apply to write")
        return 0

    with S.registry_lock():
        stamp = datetime.datetime.now(datetime.timezone.utc).strftime(
            "%Y-%m-%dT%H:%M:%SZ")
        with open(os.path.join(S.REG, "review-log.jsonl"), "a",
                  encoding="utf-8", newline="\n") as fh:
            for c in changed:
                fh.write(json.dumps({
                    "event": "scenario_normalized",
                    "date": S.today(),
                    "timestamp": stamp,
                    "file": c["file"],
                    "eid": c["eid"],
                    "ref": c["ref"],
                    "sid": c["sid"],
                    "before": c["before"],
                    "after": c["after"],
                    "by": "migrate_scenarios",
                }, ensure_ascii=False, sort_keys=True) + "\n")
    print("migrate_scenarios: applied %d normalization(s)" % len(changed))
    return 0


def main():
    ap = argparse.ArgumentParser(description=__doc__)
    g = ap.add_mutually_exclusive_group()
    g.add_argument("--apply", action="store_true", help="write the normalization")
    g.add_argument("--check", action="store_true", help="dry run (default)")
    args = ap.parse_args()
    return run(args.apply)


if __name__ == "__main__":
    sys.exit(main())
