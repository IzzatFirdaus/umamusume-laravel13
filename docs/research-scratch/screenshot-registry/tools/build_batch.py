"""Deterministic batch planner for the screenshot registry.

Selects a reviewable candidate set for the next batch by composing explicit,
ordered priorities over the current inventory. It is read-only: it never
mutates review state and never writes into the registry unless `--out` is
given, and even then it only writes a candidate manifest.

Priorities (applied in order, deduplicated):
  1. `documented_unverified` frames          -> validate legacy notes.
  2. A representative from each large cluster -> characterise big groups.
  3. No-note full-desktop frames in a cluster
     with no reviewed member                  -> fresh UI.
  4. Time-spread singletons                   -> avoid one run segment.
  5. Explicit `--include` refs                -> answer a specific question.

A perceptual cluster is a candidate grouping, not proof of duplicates, so the
builder caps how many frames it takes from any one cluster (`--per-cluster`).

Usage:
    python tools/build_batch.py --limit 25
    python tools/build_batch.py --limit 25 --out batches/B004-candidates.json
    python tools/build_batch.py --include "2026-07-18 001811,2026-07-15 012428"
"""
import argparse
import collections
import json
import os
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, HERE)
import screenshots as S  # noqa: E402

LARGE_CLUSTER = 10
FULL_W, FULL_H = 1920, 1080


def _captured(ref):
    """The capture timestamp is the ref itself (YYYY-MM-DD HHMMSS)."""
    return ref


def _no_note(r):
    return not r.get("note_refs")


def _is_full_desktop(r):
    return r.get("width") == FULL_W and r.get("height") == FULL_H


def _cluster_has_reviewed(inv, rep):
    for r in inv:
        if r.get("cluster_rep") == rep and r.get("state") in ("verified", "partial"):
            return True
    return False


def _spread(refs, n):
    """Pick up to n refs spread evenly across a sorted list."""
    refs = sorted(refs)
    if n >= len(refs):
        return refs
    if n <= 0:
        return []
    step = len(refs) / float(n)
    return [refs[int(i * step)] for i in range(n)]


def build_candidates(inv, limit=25, per_cluster=2, include=None, only_clusters=None):
    """Return a deterministic candidate manifest for the next batch.

    ``inv`` is the inventory record list. The result is a dict with a
    ``selected`` list (each carrying a ``reason``) and per-selector counts.

    ``only_clusters`` restricts selection to members of those cluster IDs;
    ``None`` selects from the whole inventory.
    """
    include = list(include or [])
    reviewed = {"verified", "partial"}
    open_states = {"not_reviewed", "documented_unverified"}
    if only_clusters is not None:
        only_clusters = {int(c) for c in only_clusters}
        inv = [r for r in inv if r.get("cluster_index") in only_clusters]

    by_ref = {r["ref"]: r for r in inv}
    selected = []
    seen = set()
    per_cluster_count = collections.Counter()
    reasons = {}

    def take(ref, reason):
        r = by_ref.get(ref)
        if not r or ref in seen:
            return False
        if r.get("state") not in open_states:
            return False
        rep = r.get("cluster_rep") or r["ref"]
        if per_cluster_count[rep] >= per_cluster:
            return False
        seen.add(ref)
        per_cluster_count[rep] += 1
        reasons[ref] = reason
        selected.append(r)
        return True

    # 1. documented_unverified: validate the legacy note.
    du = sorted(r["ref"] for r in inv if r.get("state") == "documented_unverified")
    for ref in du:
        if len(selected) >= limit:
            break
        take(ref, "documented_unverified: validate the legacy note")

    # 2. One representative per large cluster that is not yet reviewed.
    #    When clusters are named explicitly, every named cluster qualifies,
    #    so a caller can target clusters below LARGE_CLUSTER.
    min_size = 1 if only_clusters is not None else LARGE_CLUSTER
    big_reps = {}
    for r in inv:
        rep = r.get("cluster_rep")
        if not rep:
            continue
        size = r.get("cluster_size") or 0
        if size >= min_size and not _cluster_has_reviewed(inv, rep):
            big_reps.setdefault(rep, r)
    for rep in sorted(big_reps):
        if len(selected) >= limit:
            break
        take(big_reps[rep]["ref"], "large cluster %s representative (size %d)"
             % (rep, big_reps[rep].get("cluster_size")))

    # 3. No-note full-desktop frames in a cluster with no reviewed member.
    cand3 = sorted(r["ref"] for r in inv
                   if r.get("state") == "not_reviewed" and _no_note(r) and _is_full_desktop(r)
                   and not _cluster_has_reviewed(inv, r.get("cluster_rep") or r["ref"]))
    for ref in cand3:
        if len(selected) >= limit:
            break
        take(ref, "no-note full-desktop frame; cluster has no reviewed member")

    # 4. Time-spread singletons.
    singles = [r["ref"] for r in inv
               if r.get("state") == "not_reviewed" and (r.get("cluster_size") or 1) == 1]
    need = max(0, limit - len(selected))
    for ref in _spread(singles, need):
        if len(selected) >= limit:
            break
        take(ref, "time-spread singleton")

    # 5. Explicit includes (answer a named question).
    for ref in include:
        if len(selected) >= limit:
            break
        take(ref, "explicit include")

    # Deterministic ordering: by capture time.
    selected.sort(key=lambda r: r["ref"])

    manifest = {
        "target": limit,
        "per_cluster_cap": per_cluster,
        "selected": [
            {
                "ref": r["ref"],
                "sid": r.get("sid"),
                "state": r.get("state"),
                "width": r.get("width"),
                "height": r.get("height"),
                "cluster_rep": r.get("cluster_rep"),
                "cluster_size": r.get("cluster_size"),
                "note_files": r.get("note_files", []),
                "captured": _captured(r["ref"]),
                "reason": reasons[r["ref"]],
            }
            for r in selected
        ],
    }
    manifest["counts"] = {
        "total": len(selected),
        "documented_unverified": sum(1 for r in selected if r.get("state") == "documented_unverified"),
        "distinct_clusters": len({(r.get("cluster_rep") or r["ref"]) for r in selected}),
    }
    return manifest


def main(argv=None):
    p = argparse.ArgumentParser(prog="build_batch.py", description=__doc__,
                                formatter_class=argparse.RawDescriptionHelpFormatter)
    p.add_argument("--limit", type=int, default=25, help="target number of frames")
    p.add_argument("--per-cluster", type=int, default=2, help="max frames from one cluster")
    p.add_argument("--include", help="comma-separated refs to force into the batch")
    p.add_argument("--only-clusters", help="file with one cluster ID per; restrict selection")
    p.add_argument("--out", help="write the manifest here (default: stdout)")
    p.add_argument("--dry-run", action="store_true", help="print only; never write")
    args = p.parse_args(argv)

    inv = S.read_jsonl(S.INV)
    if not inv:
        print("error: no inventory; run `inventory` first", file=sys.stderr)
        return 2
    include = [x.strip() for x in (args.include or "").split(",") if x.strip()]
    only_clusters = None
    if args.only_clusters:
        with open(args.only_clusters, encoding="utf-8") as fh:
            only_clusters = [x.strip() for x in fh if x.strip()]
    manifest = build_candidates(inv, limit=args.limit, per_cluster=args.per_cluster,
                                include=include, only_clusters=only_clusters)
    text = json.dumps(manifest, indent=1, ensure_ascii=False, sort_keys=True) + "\n"
    if args.out and not args.dry_run:
        os.makedirs(os.path.dirname(os.path.abspath(args.out)), exist_ok=True)
        S.write_text_atomic(args.out, text)
        print("wrote %s (%d frames)" % (S.rel(args.out), manifest["counts"]["total"]))
    else:
        sys.stdout.write(text)
    return 0


if __name__ == "__main__":
    sys.exit(main())
