"""Native-resolution review aid for the screenshot registry.

Read-only. Prints a review brief for one frame (ref, sid, dimensions, path,
state, legacy-note excerpt, nearby cluster members), and can write a zoomed
crop of a region to a scratch directory so a value can be read without
touching the original image.

Explicit outcomes: a frame that is unreadable, too low resolution, obscured,
or ambiguous must never be recorded as `verified`. ``outcome_state`` encodes
that rule and the CLI prints the exact `review` command to run.

Usage:
    python tools/review_images.py --ref "2026-07-18 001811"
    python tools/review_images.py --ref "..." --crop 1180,540,640,360 --out scratch/x.png
    python tools/review_images.py --ref "..." --outcome obscured
    python tools/review_images.py --batch B004
"""
import argparse
import json
import os
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, HERE)
import screenshots as S  # noqa: E402

SCRATCH = os.path.join(S.REG, "scratch")

# outcome -> (review state, reason). A non-legible outcome is never `verified`.
OUTCOMES = {
    "legible": ("verified", None),
    "unreadable": ("skipped", "unreadable file"),
    "insufficient_resolution": ("partial", "value not legible at native resolution"),
    "obscured": ("partial", "UI obscured by an overlay"),
    "ambiguous": ("partial", "text ambiguous"),
}


def outcome_state(outcome):
    """Map a review outcome to a registry state, enforcing 'never verified'."""
    if outcome not in OUTCOMES:
        raise ValueError("unknown outcome %r" % outcome)
    state = OUTCOMES[outcome][0]
    if outcome != "legible" and state == "verified":
        raise AssertionError("a non-legible outcome must never map to verified")
    return state


def cluster_members(inv, rep):
    return sorted(r["ref"] for r in inv if r.get("cluster_rep") == rep)


def brief(inv_row, notes_by_ref, inv):
    """A read-only review brief for one frame."""
    ref = inv_row["ref"]
    rep = inv_row.get("cluster_rep") or ref
    note_refs = inv_row.get("note_refs", [])
    excerpt = None
    for n in notes_by_ref.get(ref, []):
        if n.get("screen") or n.get("observation"):
            excerpt = (n.get("screen") or n.get("observation"))
            break
    return {
        "ref": ref,
        "sid": inv_row.get("sid"),
        "state": inv_row.get("state"),
        "path": inv_row.get("path"),
        "width": inv_row.get("width"),
        "height": inv_row.get("height"),
        "readable": inv_row.get("readable"),
        "cluster_rep": rep,
        "cluster_size": inv_row.get("cluster_size"),
        "cluster_members": [m for m in cluster_members(inv, rep) if m != ref],
        "note_files": inv_row.get("note_files", []),
        "legacy_note_excerpt": excerpt,
        "outcomes": sorted(OUTCOMES),
    }


def crop(src_path, box, out_path):
    """Write a crop of src_path to out_path. Never writes the original."""
    from PIL import Image

    os.makedirs(os.path.dirname(os.path.abspath(out_path)), exist_ok=True)
    with Image.open(src_path) as im:
        c = im.crop(box)
        c.save(out_path)
    return out_path


def _print_brief(b):
    print("ref:            %s" % b["ref"])
    print("sid:            %s" % b["sid"])
    print("state:          %s" % b["state"])
    print("dimensions:     %sx%s" % (b["width"], b["height"]))
    print("readable:       %s" % b["readable"])
    print("path:           %s" % b["path"])
    print("cluster:        %s (size %s)" % (b["cluster_rep"], b["cluster_size"]))
    if b["cluster_members"]:
        print("cluster members: %s" % ", ".join(b["cluster_members"][:8]))
    if b["note_files"]:
        print("note files:     %s" % ", ".join(b["note_files"]))
    if b["legacy_note_excerpt"]:
        print("legacy note:    %s" % b["legacy_note_excerpt"])
    print("outcomes:       %s" % ", ".join(b["outcomes"]))


def main(argv=None):
    p = argparse.ArgumentParser(prog="review_images.py", description=__doc__,
                                formatter_class=argparse.RawDescriptionHelpFormatter)
    p.add_argument("--ref", help="a single frame ref")
    p.add_argument("--batch", help="review every frame in a batch manifest")
    p.add_argument("--crop", help="crop box x,y,w,h (with --ref)")
    p.add_argument("--out", help="output path for --crop (default: scratch/<ref>.png)")
    p.add_argument("--outcome", choices=sorted(OUTCOMES),
                   help="print the review command for this outcome")
    p.add_argument("--json", action="store_true", help="emit the brief as JSON")
    args = p.parse_args(argv)

    inv = S.read_jsonl(S.INV)
    if not inv:
        print("error: no inventory; run `inventory` first", file=sys.stderr)
        return 2
    by_ref = {r["ref"]: r for r in inv}
    notes_by_ref = {}
    for n in S.read_jsonl(S.NOTES):
        notes_by_ref.setdefault(n["ref"], []).append(n)

    refs = []
    if args.ref:
        refs = [args.ref]
    elif args.batch:
        _, mpath = S._batch_paths(args.batch)
        manifest = json.load(open(os.path.join(S.BATCHES, "%s.json" % args.batch), encoding="utf-8"))
        refs = manifest.get("ids", [])

    if not refs:
        print("error: give --ref or --batch", file=sys.stderr)
        return 2

    for ref in refs:
        row = by_ref.get(ref)
        if not row:
            print("error: unknown ref %s" % ref, file=sys.stderr)
            return 2
        b = brief(row, notes_by_ref, inv)
        if args.json:
            print(json.dumps(b, indent=1, ensure_ascii=False, sort_keys=True))
        else:
            _print_brief(b)
            if args.outcome:
                state = outcome_state(args.outcome)
                print("recommended:    review --ids \"%s\" --state %s --observer <you>%s"
                      % (ref, state,
                         " --reason \"%s\"" % OUTCOMES[args.outcome][1] if state == "skipped" else ""))
        if args.crop:
            box = tuple(int(x) for x in args.crop.split(","))
            out = args.out or os.path.join(SCRATCH, "%s.crop.png" % ref)
            ap = os.path.join(S.ROOT, row["path"])
            crop(ap, box, out)
            print("wrote crop:     %s" % S.rel(out))
    return 0


if __name__ == "__main__":
    sys.exit(main())
