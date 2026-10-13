import re
from pathlib import Path

L = Path("assembled-body.v2.md").read_text(encoding="utf-8").splitlines()
pats = [
    "is the current Champions Meeting round",
    r"The current \[Global\] Champions Meeting",
    "Failure Rate Down",
    "Failure Protection",
    "above 100",
    r"cycle twice",
    r"cycle once per year",
    r"137 future character banners",
    r"Rebuild the tables from the exports",
    "Confirmed as 2025",
    "Story event record id 1057",
    r"ids 1001, 110",
]
for p in pats:
    rx = re.compile(p)
    hits = [(i + 1, l) for i, l in enumerate(L) if rx.search(l)]
    print("### %r -> %d hit(s)" % (p, len(hits)))
    for n, l in hits[:2]:
        print("   %d: %s" % (n, l[:340]))
