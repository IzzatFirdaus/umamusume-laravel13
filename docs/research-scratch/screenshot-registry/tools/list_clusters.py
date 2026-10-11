"""List frames by cluster representative."""
import json, os

REG = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
inv = [json.loads(l) for l in open(os.path.join(REG, "inventory.jsonl"), encoding="utf-8") if l.strip()]

# Group by cluster_rep
clusters = {}
for r in inv:
    rep = r.get("cluster_rep")
    if rep:
        clusters.setdefault(rep, []).append(r["ref"])

# Sort each list
for rep in clusters:
    clusters[rep].sort()

# Print some clusters
for rep, refs in [("Screenshot 2026-07-18 001811.png", "725 rep"),
                  ("Screenshot 2026-07-17 230755.png", "707 rep"),
                  ("Screenshot 2026-07-17 230902.png", "708 rep"),
                  ("Screenshot 2026-07-14 202142.png", "80 rep"),
                  ("Screenshot 2026-07-14 194819.png", "66 rep")]:
    if rep in clusters:
        print(f"{refs} ({rep}): {len(clusters[rep])} frames")
        print(f"  First 5: {clusters[rep][:5]}")
        print(f"  Last 5:  {clusters[rep][-5:]}")
        print()
    else:
        print(f"{refs}: not found")