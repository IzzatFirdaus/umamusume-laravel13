"""Build a B002 candidate list from the current registry state.

The selection rule is:
  1. Every not_reviewed, full-desktop, no-note frame is in scope.
  2. From each distinct cluster size, take the first frame by ref order.
  3. From the singleton frames, take a fixed spread across the time axis.
  4. Also take the representatives of every large cluster (size >= 10)
     so that large groups get one confirmed read each.

The output is a single line of space-separated refs, ready to feed into
`screenshots.py batch --id B002 --ids "..."`.
"""
import json
import os
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, HERE)
import screenshots as S

inv = [json.loads(l) for l in open(S.INV, encoding="utf-8") if l.strip()]
nr = [r for r in inv
      if r["state"] == "not_reviewed"
      and r.get("width") == 1920 and r.get("height") == 1080
      and not r.get("note_refs")]

# one rep per distinct cluster size, in order of size descending
seen_sizes, by_cluster = set(), {}
for r in nr:
    key = (r.get("cluster_size") or 0, r.get("cluster_rep") or "")
    if key not in by_cluster:
        by_cluster[key] = r
cluster_reps = sorted(by_cluster.values(),
                      key=lambda r: (-(r.get("cluster_size") or 0), r["ref"]))
picks = []
for r in cluster_reps:
    sz = r.get("cluster_size") or 0
    if sz in seen_sizes:
        continue
    seen_sizes.add(sz)
    picks.append(r["ref"])

# large clusters get their rep explicitly, even if a smaller rep was already picked
for r in cluster_reps:
    if (r.get("cluster_size") or 0) >= 10 and r["ref"] not in picks:
        picks.append(r["ref"])

# singleton spread across the time axis
singletons = sorted([r["ref"] for r in nr if (r.get("cluster_size") or 0) == 1],
                    key=lambda ref: ref)
step = max(1, len(singletons) // 8)
for ref in singletons[::step]:
    if ref not in picks:
        picks.append(ref)

# deduplicate and order
final = sorted(set(picks))
print(" ".join(final))
print("---total---", len(final), file=sys.stderr)
