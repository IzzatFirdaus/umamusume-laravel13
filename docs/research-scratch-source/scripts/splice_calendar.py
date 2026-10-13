import re
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
    body = "\n".join(lines[start:]).rstrip()
    blocks[label] = body

text = doc.read_text(encoding="utf-8")
for label, body in blocks.items():
    marker = f"<!--SPICE:{label}-->"
    if marker in text:
        text = text.replace(marker, body)
        print("spliced", label, len(body.splitlines()), "lines")

left = re.findall(r"<!--SPICE:(.*?)-->", text)
if left:
    raise SystemExit(f"UNRESOLVED MARKERS: {left}")
doc.write_text(text, encoding="utf-8")
print("wrote", doc, len(text.splitlines()), "lines")
