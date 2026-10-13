"""Scenario spine table straight from scenarios.json (tier B export)."""

import json
from datetime import datetime, timedelta, timezone
from pathlib import Path

J = Path("D:/Projects/umamusume-laravel13/research-scratch/data/json")
OUT = Path("D:/Projects/umamusume-laravel13/research-scratch/scenario_spine.md")
JST = timezone(timedelta(hours=9))
UTC = timezone.utc

d = json.loads(J.joinpath("scenarios.json").read_text(encoding="utf-8"))
rows = []
for s in sorted(d, key=lambda x: x["order"]):
    jp = datetime.fromtimestamp(s["start_ja"], JST).strftime("%Y-%m-%d") if s.get("start_ja") else "none"
    en = datetime.fromtimestamp(s["start_en"], UTC).strftime("%Y-%m-%d") if s.get("start_en") else "not listed"
    hc = s["hard_caps"]
    st = s["stats"]
    rows.append("| %d | %d | %s | %s | %s | %d / %d / %d / %d / %d | %s | %s | %s | %s | %d | %d |" % (
        s["order"], s["id"], s["name_ja"], s.get("name_en") or "-", s.get("name_en_old") or "-",
        hc[0], hc[1], hc[2], hc[3], hc[4],
        "[" + ", ".join(str(v) for v in st) + "]", hc[5], jp, en,
        s.get("special_program") or 0, len(s.get("scenario_linked_characters") or []),
    ))

head = (
    "| Order | id | JP name | Export EN | Older EN rendering | Hard cap Sp/St/Pw/Gu/Wi | `stats` (same order) | cap 6th slot | `[JP]` start (JST) | `[Global]` start (UTC) | `special_program` | linked chars |\n"
    "|---|---|---|---|---|---|---|---|---|---|---|---|\n"
)
capnote = (
    "\nTwo cap regimes exist across the fourteen: orders 1 to 12 carry 2000 on every stat, orders 13 and 14 "
    "(Beyond Dreams and トレセン軒) carry 2500. The sixth slot is 9999 under the old regime and 99999 under the new, "
    "which is a sentinel rather than a stat. Read from `scenarios.json`, fetched 2026-09-27.\n"
)
OUT.write_text(head + "\n".join(rows) + "\n" + capnote, encoding="utf-8")
print(OUT.name, "rows", len(rows))
print("distinct cap sets:", sorted({tuple(json.loads((J/'scenarios.json').read_text(encoding='utf-8'))[i]['hard_caps'][:5]) for i in range(14)}))
