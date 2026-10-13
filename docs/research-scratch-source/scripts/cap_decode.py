"""Test the hypothesis: scenarios.json `stats` is a per-stat cap bonus over a 1200 base.

Triggered by the user's Game8 read: URA 1400 on all five; Unity Cup 1300 x4 and 1800 Wit.
"""

import json
from pathlib import Path

d = json.loads(Path("D:/Projects/umamusume-laravel13/research-scratch/data/json/scenarios.json").read_text(encoding="utf-8"))
BASE = 1200
print("order | scenario | stats bonus | base+bonus (Sp,St,Pw,Gu,Wi) | hard_caps[0]")
for s in sorted(d, key=lambda x: x["order"]):
    b = s["stats"]
    print("%5d | %-38s | %-28s | %-26s | %d" % (
        s["order"], s["name_ja"][:38], str(b),
        str([BASE + v for v in b]), s["hard_caps"][0]))

ura = next(s for s in d if s["id"] == 1)
uni = next(s for s in d if s["id"] == 2)
tb = next(s for s in d if s["id"] == 4)
bd = next(s for s in d if s["id"] == 13)
print()
print("URA claim (1400 all five):", [BASE + v for v in ura["stats"]])
print("Unity claim (1300x4, Wit 1800):", [BASE + v for v in uni["stats"]])
print("Trackblazer derived:", [BASE + v for v in tb["stats"]], "-> user said 'standard base caps apply'")
print("Beyond Dreams derived Speed:", BASE + bd["stats"][0], "-> matches the 2100 figure in conflict row 3")
