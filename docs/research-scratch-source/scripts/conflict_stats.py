import re
from collections import Counter
from pathlib import Path

t = Path("D:/Projects/umamusume-laravel13/research-scratch/assembled-body.v2.md").read_text(encoding="utf-8")
log = t[t.index("## Source Conflict Log"):]
rows = [ln for ln in log.splitlines() if re.match(r"^\| \d+ \|", ln)]
print("conflict rows:", len(rows))
print("by origin:", Counter(r.split("|")[2].strip() for r in rows).most_common())
print("by confidence:", Counter(r.split("|")[-2].strip().upper() for r in rows).most_common())
print()
print("rows not resolved to HIGH:")
for r in rows:
    conf = r.split("|")[-2].strip().upper()
    if not conf.startswith("HIGH"):
        cells = [c.strip() for c in r.strip("|").split("|")]
        print("  #%s [%s/%s] %s" % (cells[0], cells[1], conf or "?", cells[2][:100]))

body = t[: t.index("## Source Conflict Log")]
print()
print("unverified markers in body sections:", body.count("❌ UNVERIFIED"))
stale = re.findall(r"⚠️ STALE[^|\n]{0,70}", body)
print("stale sample attributions:", Counter(re.sub(r"[^a-zA-Z.]", "", s.split(":")[-1]).lower()[:12] for s in stale).most_common(8))
