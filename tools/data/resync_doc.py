"""Re-sync every generated race-calendar table in the scenario guide.

The generated tables are the `## calendar-tables.md` section of
`docs/research-scratch/RACE-AND-SLICE-RESEARCH.md`, the carrier Round 11 folded in from the
standalone `calendar-tables.md`. `gen_calendar.py` prints that section's blocks; this script pushes
seven of them into their headings in `docs/scenarios/09-global-race-calendar.md`. The eighth,
`Unique races, base pool vs additions`, has no heading in the scenario guide and is not pushed.

Each doc section is matched by heading; the first contiguous pipe-table inside it is
replaced by the regenerated block. Prose around the tables is left untouched.
"""

import re
import sys
from pathlib import Path

root = Path(__file__).resolve().parent.parent
ANCHOR = re.compile(r"^## calendar-tables\.md$", re.M)

master_text = (root / "docs/research-scratch/RACE-AND-SLICE-RESEARCH.md").read_text(encoding="utf-8")
# Match the heading line, not the first mention: this master's own provenance names the anchor inline.
hit = ANCHOR.search(master_text)
if hit is None:
    sys.exit("no calendar-tables heading in the race master")
nxt = master_text.find("\n## ", hit.end())
# Slice to the section only: the master's prose also names the `@@` markers, and a whole-file split
# on `@@` would read that sentence as a block.
src = master_text[hit.start():] if nxt == -1 else master_text[hit.start():nxt]
doc = root / "docs/scenarios/09-global-race-calendar.md"

blocks = {}
for chunk in src.split("@@")[1:]:
    label = chunk.split(" — ")[0].split("\n")[0].strip()
    lines = chunk.split("\n")
    start = next((i for i, ln in enumerate(lines) if ln.startswith("|")), None)
    if start is None:
        continue
    body = []
    for ln in lines[start:]:
        if ln.strip() == "" and body:
            break
        body.append(ln)
    blocks[label] = "\n".join(body)

SECTIONS = [
    ("## Mandatory and conditional races", "Special races"),
    ("## Junior Year", "Junior Year"),
    ("## Classic Year", "Classic Year"),
    ("## Senior Year", "Senior Year"),
    ("## G1 index", "G1 index"),
    ("## Slot counts", "Counts"),
    ("## Appendix: every flagged race row in the export", "Global exclusions"),
]

text = doc.read_text(encoding="utf-8")
for heading, label in SECTIONS:
    if label not in blocks:
        sys.exit(f"no generated block for {label}")
    h = text.index(heading)
    nxt = text.find("\n## ", h + len(heading))
    end = len(text) if nxt == -1 else nxt + 1
    section = text[h:end]
    new = blocks[label]
    if new in section:
        print(f"  unchanged  {label}")
        continue
    m = re.search(r"^\|.*(?:\n\|.*)*", section, re.M)
    if not m:
        sys.exit(f"no table under {heading}")
    old_rows = len(m.group(0).rstrip().split("\n")) - 2
    section = section[:m.start()] + new + "\n" + section[m.end():]
    text = text[:h] + section + text[end:]
    print(f"  replaced   {label}: {old_rows} -> {len(new.splitlines()) - 2} data rows")

doc.write_text(text, encoding="utf-8", newline="\n")
print("wrote", doc.name, len(text.splitlines()), "lines")
