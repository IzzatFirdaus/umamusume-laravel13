import os
import sys

sys.path.insert(0, os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), "tools"))
import doc_census as dc  # noqa: E402

dead = dc.dead_links()
for target in ["docs/PLAN-DOC-SYNC-2026-10-02.md", "docs/PLAN-UI-UX-2026-10-02.md",
               "docs/research-scratch/PROCESS-PLANS.md"]:
    hits = [d for d in dead if d[0] == target]
    print(f"citing {target}: {len(hits)} dead refs")
    for h in hits[:6]:
        print(f"    :{h[1]} -> {h[2]} [{h[3]}]")

print(f"total census: {len(dead)}")
