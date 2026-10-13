"""Re-sync every generated table in the doc from calendar-tables.md.

Each doc section is matched by heading; the first contiguous pipe-table inside it is
replaced by the regenerated block. Prose around the tables is left untouched.
"""

import re
import sys
from pathlib import Path

root = Path("D:/Projects/umamusume-laravel13")
src = (root / "research-scratch/calendar-tables.md").read_text(encoding="utf-8")
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

doc.write_text(text, encoding="utf-8")
print("wrote", doc.name, len(text.splitlines()), "lines")
