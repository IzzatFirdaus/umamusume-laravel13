import os
import sys

# The census lives in the repository's `tools/`, four levels up from this file:
# docs/research-scratch-source/scripts/ -> docs/research-scratch-source/ -> docs/ -> the root.
# The path this used to insert, `docs/research-scratch-source/tools`, does not exist, so the
# import raised ModuleNotFoundError and the script never ran at all.
REPO = os.path.dirname(os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__)))))
sys.path.insert(0, os.path.join(REPO, "tools"))
import doc_census as dc  # noqa: E402

# `dead_links()` returns the dead references and the unreadable files. This read the pair as
# the list of references, so every lookup below missed and the summary printed the tuple's
# length, which is 2.
dead, _unreadable = dc.dead_links()
sha = dc.git("rev-parse", "--short", dc.DEFAULT_REF).strip()
print(f"measured at {dc.DEFAULT_REF} {sha} (the committed tree)")
for target in ["docs/PLAN-DOC-SYNC-2026-10-02.md", "docs/PLAN-UI-UX-2026-10-02.md",
               "docs/research-scratch/PROCESS-PLANS.md"]:
    hits = [d for d in dead if d[0] == target]
    print(f"citing {target}: {len(hits)} dead refs")
    for h in hits[:6]:
        print(f"    :{h[1]} -> {h[2]} [{h[3]}]")

print(f"total census: {len(dead)}")
