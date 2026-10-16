"""Validate UI contract files against the screenshot registry.

A contract is only as good as its evidence: every component that carries
observed values must point at a frame the registry has verified.  This
validator is read-only and exits non-zero with a line-per-error report.
"""
import argparse
import json
import os
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, HERE)
import screenshots as S  # noqa: E402

REQUIRED_TOP = ("screen_type", "components", "observed_interactions",
                "unresolved_questions", "evidence")
REQUIRED_COMPONENT = ("name", "label", "states", "values", "units")


def validate_contract(path, by_ref, by_sid):
    errors = []
    try:
        with open(path, encoding="utf-8") as f:
            c = json.load(f)
    except (OSError, ValueError) as exc:
        return ["%s: cannot read contract: %s" % (path, exc)]

    for k in REQUIRED_TOP:
        if k not in c:
            errors.append("%s: missing top-level key %r" % (path, k))

    comps = c.get("components", [])
    if not isinstance(comps, list):
        errors.append("%s: components must be a list" % path)
        comps = []
    for i, comp in enumerate(comps):
        for k in REQUIRED_COMPONENT:
            if k not in comp:
                errors.append("%s: components[%d] missing %r" % (path, i, k))

    ev = c.get("evidence", [])
    if not isinstance(ev, list):
        errors.append("%s: evidence must be a list" % path)
        ev = []
    ev_fields = set()
    for i, e in enumerate(ev):
        ref = e.get("ref")
        sid = e.get("sid")
        if ref not in by_ref:
            errors.append("%s: evidence[%d].ref %r is not an inventory frame" % (path, i, ref))
        elif by_ref[ref].get("state") != "verified":
            errors.append("%s: evidence[%d].ref %s is not verified (%s)"
                          % (path, i, ref, by_ref[ref].get("state")))
        if sid not in by_sid:
            errors.append("%s: evidence[%d].sid %r is not an inventory frame" % (path, i, sid))
        elif by_sid[sid].get("state") != "verified":
            errors.append("%s: evidence[%d].sid %s is not verified (%s)"
                          % (path, i, sid, by_sid[sid].get("state")))
        if e.get("field"):
            ev_fields.add(e["field"])

    for i, comp in enumerate(comps):
        if comp.get("values") and comp.get("name") not in ev_fields:
            errors.append("%s: components[%d] %r has values but no evidence entry"
                          % (path, i, comp.get("name")))
    return errors


def main():
    ap = argparse.ArgumentParser(description="Validate UI contract files.")
    ap.add_argument("--path", required=True, help="a contract file or a directory of them")
    args = ap.parse_args()

    inv = S.read_jsonl(S.INV)
    by_ref = {r["ref"]: r for r in inv}
    by_sid = {r["sid"]: r for r in inv if r.get("sid")}

    paths = []
    if os.path.isdir(args.path):
        for name in sorted(os.listdir(args.path)):
            if name.endswith(".json"):
                paths.append(os.path.join(args.path, name))
    else:
        paths.append(args.path)

    errors = []
    for p in paths:
        errors.extend(validate_contract(p, by_ref, by_sid))

    if errors:
        for e in errors:
            print("FAIL  %s" % e)
        print("validate_contracts: %d error(s) in %d file(s)" % (len(errors), len(paths)))
        return 1
    print("validate_contracts: %d file(s) OK" % len(paths))
    return 0


if __name__ == "__main__":
    sys.exit(main())
