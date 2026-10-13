import json, collections, sys

D = "research-scratch/data/json/"
races = {r["id"]: r for r in json.load(open(D + "races.json", encoding="utf-8"))}
ura = json.load(open(D + "ura-races.json", encoding="utf-8"))

print("=" * 70)
print("SPECIAL ROWS (no instance)")
for u in ura:
    if "instance" not in u:
        det = u.get("details", {})
        print(f"  id={u['id']!r} tid={u['tid']!r} y{u['year']} m{u['month']} h{u['half']} "
              f"special_race={u.get('special_race')} dne={u.get('did_not_exist')}")
        print(f"    details keys: {sorted(det.keys())}")
        print(f"    name_en={det.get('name_en')!r} grade={det.get('grade')} "
              f"distance={det.get('distance')} terrain={det.get('terrain')} "
              f"fans_needed={u.get('fans_needed')}")

print("=" * 70)
print("GRADE ENUM CHECK")
GN = {100: "?100", 200: "?200", 300: "?300", 400: "?400", 700: "?700"}
for g in sorted({r["grade"] for r in races.values()}):
    names = sorted(r["name_en"] for r in races.values() if r["grade"] == g)
    print(f"  grade {g}: {len(names)} -> {names[:14]}")

print("=" * 70)
print("SERVER-EXCLUSION FLAGS")
for r in sorted(races.values(), key=lambda x: x["id"]):
    if "unreleased_servers" in r or "did_not_exist" in r:
        print(f"  {r['id']} {r['name_en']:<34} grade={r['grade']} "
              f"ura={r['list_ura']} unrel={r.get('unreleased_servers')} dne={r.get('did_not_exist')}")

print("=" * 70)
print("URA SLOT SPINE (year/month/half -> n races)")
slots = collections.Counter((u["year"], u["month"], u["half"]) for u in ura)
for y in (1, 2, 3):
    row = {m: sum(1 for (yy, mm, hh), n in slots.items() if yy == y and mm == m for _ in range(1))
           for m in range(1, 13)}
    cnt = collections.Counter()
    for (yy, mm, hh), n in slots.items():
        if yy == y:
            cnt[mm] += n
    print(f"  year {y}: " + " ".join(f"m{m:02d}={cnt[m]:2d}" for m in range(1, 13)))

print("=" * 70)
print("fans_needed / fans_gain ranges by year")
for y in (1, 2, 3, 4):
    us = [u for u in ura if u["year"] == y and "fans_needed" in u]
    if us:
        fn = sorted(u["fans_needed"] for u in us)
        fg = sorted(u["fans_gain"] for u in us)
        print(f"  year {y}: n={len(us)} fans_needed {fn[0]}..{fn[-1]}  fans_gain {fg[0]}..{fg[-1]}")

print("=" * 70)
print("Junior year (year 1) full list, in slot order")
j = sorted((u for u in ura if u["year"] == 1), key=lambda u: (u["month"], u["half"], str(u["tid"])))
for u in j:
    r = races.get(u["instance"])
    print(f"  m{u['month']:02d} h{u['half']} tid={str(u['tid']):<4} "
          f"{'JOIN-FAIL' if r is None else r['name_en']:<34} "
          f"grade={r['grade'] if r else '?':<4} {r['distance'] if r else '?':>6} "
          f"terrain={r['terrain'] if r else '?'} fans_needed={u.get('fans_needed')} gain={u.get('fans_gain')}")
