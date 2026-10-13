"""Replace the G1 index table in the doc with the regenerated, year-columned block."""

import re
from pathlib import Path

root = Path("D:/Projects/umamusume-laravel13")
src = (root / "research-scratch/calendar-tables.md").read_text(encoding="utf-8")
doc = root / "docs/scenarios/09-global-race-calendar.md"

chunk = src.split("@@G1 index")[1].split("@@")[0]
lines = chunk.split("\n")
start = next(i for i, ln in enumerate(lines) if ln.startswith("|"))
new_table = "\n".join(lines[start:]).split("\n\n")[0].rstrip()
new_rows = len([l for l in new_table.splitlines() if l.startswith("| ")]) - 1

text = doc.read_text(encoding="utf-8")
pat = re.compile(r"(## G1 index\n\n.*?\n\n)(\|.*?)(?=\n## Slot counts)", re.S)
m = pat.search(text)
if not m:
    raise SystemExit("G1 index section not matched")
old_rows = len([l for l in m.group(2).splitlines() if l.startswith("| ")]) - 1
text = text[:m.start(2)] + new_table + text[m.end(2):]
doc.write_text(text, encoding="utf-8")
print(f"G1 index: {old_rows} data rows -> {new_rows} data rows")
