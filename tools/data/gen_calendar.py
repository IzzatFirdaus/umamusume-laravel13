"""Generate the Global career race-calendar tables from the GameTora export.

Reads the cached export in research-scratch/data/json/, joins race_instances to
racetracks_extended and to the Global payout table (en/race-fans), and prints markdown table
blocks carrying `@@Label — n rows` markers. That output is the `## calendar-tables.md` section of
`docs/research-scratch/RACE-AND-SLICE-RESEARCH.md`; replace that section with a fresh run, then
run `python tools/resync_doc.py` to push the blocks into `docs/scenarios/09-global-race-calendar.md`.
"""

import json
import sys
from pathlib import Path

# The blocks land in an LF master, and on Windows a redirected print translates \n to \r\n.
sys.stdout.reconfigure(newline="\n")

ROOT = Path(__file__).resolve().parent.parent
J = ROOT / "research-scratch/data/json"
if not J.is_dir():
    sys.exit(f"no cached export at {J}: it is gitignored scratch, refill it before regenerating")
load = lambda n: json.loads((J / n).read_text(encoding="utf-8"))

instances = load("race_instances.json")
races = {r["id"]: r for r in load("races.json")}
tracks = {t["id"]: t for t in load("racetracks_extended.json")}
payout_en = {p["id"]: {f["order"]: f["fans"] for f in p["fans"]}
             for p in load("en__race-fans.json")}

MONTHS = ["January", "February", "March", "April", "May", "June",
          "July", "August", "September", "October", "November", "December"]
YEARS = {1: "Junior", 2: "Classic", 3: "Senior"}
GRADE = {100: "G1", 200: "G2", 300: "G3", 400: "OP", 700: "Pre-OP", 800: "Maiden", 900: "Debut"}
SURFACE = {1: "Turf", 2: "Dirt"}
HEADER = ("| Turn | Slot | Race | Tier | Distance | Surface | Track | Fans to enter "
          "| Fans for 1st | Notes |")
RULE = "|" + "---|" * 10


def band(d):
    if d == 99999:
        return "flexes"
    return "Sprint" if d <= 1400 else "Mile" if d <= 1800 else "Medium" if d <= 2400 else "Long"


def cols(r):
    d = r["details"]
    monthly = r["month"] != 99999
    turn = str((r["month"] - 1) * 2 + r["half"]) if monthly else "-"
    slot = (f"{'Early' if r['half'] == 1 else 'Late'} {MONTHS[r['month'] - 1]}"
            if monthly else "after Senior Dec")
    dist = "-" if d["distance"] == 99999 else f"{d['distance']:,} m"
    src = races.get(d.get("id"), {})
    notes = []
    if d.get("unreleased_servers") and "en" in d["unreleased_servers"]:
        notes.append("**not on Global**")
    marker = src.get("did_not_exist") or r.get("did_not_exist")
    if marker:
        notes.append(f"added by `{marker}`")
    if d["distance"] == 99999:
        notes.append("distance/surface flex with your most-run types")
    gain = r.get("fans_gain")
    first = payout_en.get(gain, {}).get(1) if gain else None
    if gain and first is None:
        notes.append(f"payout curve `{gain}` absent from `en/race-fans`")
    if r.get("special_race"):
        notes.append("mandatory" if d["grade"] == 900 else "conditional")
    payout = "⚠️" if (gain and first is None) else \
             ("-" if first is None else f"{first:,}")
    return [turn, slot, d["name_en"], GRADE.get(d["grade"], f"code {d['grade']}"),
            dist, SURFACE.get(d["terrain"], "flexes"),
            tracks.get(d["track"], {}).get("name_en", "-"),
            "-" if r.get("fans_needed") is None else f"{r['fans_needed']:,}",
            payout, "; ".join(notes)]


def key(c, r):
    turn = 99 if r["month"] == 99999 else (r["month"] - 1) * 2 + r["half"]
    return (r["year"], turn, -r["details"]["grade"], c[2])


def emit(label, flt, with_year=False):
    picked = [r for r in instances if flt(r)]
    rows = sorted(((cols(r), r) for r in picked), key=lambda x: key(x[0], x[1]))
    print(f"\n@@{label} — {len(rows)} rows\n")
    if with_year:
        print("| Year | Turn | Slot | Race | Tier | Distance | Surface | Track "
              "| Fans to enter | Fans for 1st | Notes |")
        print("|" + "---|" * 11)
        for c, r in rows:
            print("| " + " | ".join([YEARS[r["year"]]] + c) + " |")
        return
    print(HEADER)
    print(RULE)
    for c, _ in rows:
        print("| " + " | ".join(c) + " |")


for year in (1, 2, 3):
    emit(f"{YEARS[year]} Year", lambda r, y=year: r["year"] == y)

emit("Special races", lambda r: not r.get("instance"))
emit("G1 index", lambda r: r["details"]["grade"] == 100 and r.get("instance"), with_year=True)

print("\n@@Counts — slots\n")
print("| Year | G1 | G2 | G3 | OP | Pre-OP | Maiden | Debut | total |")
print("|---|---|---|---|---|---|---|---|---|")
for label in ("Junior", "Classic", "Senior", "Finale"):
    sel = [r for r in instances if YEARS.get(r["year"], "Finale") == label]
    g = [GRADE.get(r["details"]["grade"], "?") for r in sel]
    print("| %s | %s | %d |" % (label, " | ".join(str(g.count(x)) for x in
          ["G1", "G2", "G3", "OP", "Pre-OP", "Maiden", "Debut"]), len(sel)))

print("\n@@Unique races, base pool vs additions\n")
print("| Tier | Sprint | Mile | Medium | Long | flexes | total |")
print("|---|---|---|---|---|---|---|")
for label, drop in (("base pool", True), ("with `did_not_exist` additions", False)):
    for code in (100, 200, 300, 400, 700):
        u = set()
        for r in instances:
            d = r["details"]
            if d["grade"] != code:
                continue
            if d.get("unreleased_servers") and "en" in d["unreleased_servers"]:
                continue
            src = races.get(d.get("id"), {})
            if drop and (src.get("did_not_exist") or r.get("did_not_exist")):
                continue
            u.add((d["name_en"], band(d["distance"])))
        if not u:
            continue
        cells = [str(sum(1 for _, b in u if b == x))
                 for x in ("Sprint", "Mile", "Medium", "Long", "flexes")]
        print("| %s %s | %s | %d |" % (GRADE[code], label[:4] and "", " | ".join(cells), len(u)))
        break_after = None
print()
for label, drop in (("base pool", True), ("with additions", False)):
    line = []
    for code in (100, 200, 300):
        u = {r["details"]["name_en"] for r in instances
             if r["details"]["grade"] == code
             and not (r["details"].get("unreleased_servers") and "en" in r["details"]["unreleased_servers"])
             and not (drop and (races.get(r["details"].get("id"), {}).get("did_not_exist")
                                or r.get("did_not_exist")))}
        line.append(f"{GRADE[code]}: {len(u)}")
    print(f"  {label:<16} " + "  ".join(line))

print("\n@@Global exclusions\n")
print("| Race | Tier | Distance | Track | In a career slot? | Flag |")
print("|---|---|---|---|---|---|")
for r in sorted(races.values(), key=lambda x: x["id"]):
    if r.get("unreleased_servers") or r.get("did_not_exist"):
        t = tracks.get(r["track"], {}).get("name_en", "-")
        slot = "yes: " + ",".join(r["list_ura"]) if r["list_ura"] else "no"
        flag = ("`unreleased_servers: ['en']`" if r.get("unreleased_servers")
                else f"`did_not_exist: '{r['did_not_exist']}'`")
        print(f"| {r['name_en']} | {GRADE.get(r['grade'], r['grade'])} | {r['distance']:,} m "
              f"| {t} | {slot} | {flag} |")
