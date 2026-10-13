import re
from pathlib import Path

t = Path("assembled-body.v2.md").read_text(encoding="utf-8")
md = set(re.findall(r"\]\((https?://[^)\s]+)", t))
bt = set(re.findall(r"`(https?://[^`\s]+)`", t))
dom = lambda u: re.sub(r"^https?://", "", u).split("/")[0]  # noqa: E731
only = bt - md
print("markdown link targets:", len(md))
print("backtick urls:", len(bt))
print("backtick-only, not linked:", len(only))
print("domains backtick-only:", sorted({dom(u) for u in only}))
print("domains markdown:", sorted({dom(u) for u in md}))
for u in sorted(only)[:10]:
    print("   ", u)
