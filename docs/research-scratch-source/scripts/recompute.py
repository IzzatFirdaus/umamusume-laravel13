import json
from collections import Counter
from pathlib import Path

J = Path("D:/Projects/umamusume-laravel13/research-scratch/data/json")
tracks = json.loads((J / "racetracks.json").read_text(encoding="utf-8"))
courses = [c for t in tracks for c in t.get("courses", [])]
print("racetracks:", len(tracks), "courses:", len(courses))

zero = [c["id"] for c in courses if not c.get("corners")]
print("courses with 0 corner segments:", len(zero), zero, "distances:", [c.get("distance") for c in courses if not c.get("corners")])

exact, within, other = 0, 0, 0
for c in courses:
    L = c["length"]
    fr = [p["end"] / L for p in c["phases"][:3]]
    tgt = [1 / 6, 2 / 3, 5 / 6]
    if all(abs(a - b) < 1e-9 for a, b in zip(fr, tgt)):
        exact += 1
    elif all(abs(a - b) < 0.001 for a, b in zip(fr, tgt)):
        within += 1
    else:
        other += 1
print("phase split exact:", exact, "within 0.1%:", within, "beyond:", other, "sum:", exact + within + other)

loc = Counter()
for c in courses:
    for t in (c.get("spurtStart") or {}).get("location", []):
        loc[t] += 1
print("spurtStart location tokens:", loc.most_common())

ph = Counter()
for c in courses:
    m = (c.get("spurtStart") or {}).get("meters")
    pid = None
    for p in c["phases"]:
        if p["start"] <= m < p["end"] or (m == c["length"] and p["end"] == m):
            pid = p["id"]
    ph[pid] += 1
print("spurtStart falls in phase:", ph.most_common())

groups = {
    "final_corner": lambda s: "final_corner" in s,
    "corner_no_other": lambda s: "corner" in s and len(s) == 1,
    "straight_no_other": lambda s: "straight" in s and len(s) == 1,
    "slope_words": lambda s: bool({"uphill", "downhill"} & set(s)),
    "final_straight": lambda s: "final_straight" in s,
}
counts = {k: sum(1 for c in courses if f((c.get("spurtStart") or {}).get("location", []))) for k, f in groups.items()}
print("group counts:", counts)
union = sum(1 for c in courses if any(f((c.get("spurtStart") or {}).get("location", [])) for f in groups.values()))
print("courses covered by at least one group:", union)

sk = json.loads((J / "skills.json").read_text(encoding="utf-8"))


def cond_text(s):
    parts = []
    for g in s.get("condition_groups", []) or []:
        parts.append(str(g.get("precondition", "")) + " " + str(g.get("condition", "")))
    return " ".join(parts)


pinned = [s for s in sk if "running_style==4" in cond_text(s)]
last = [s for s in pinned if "is_lastspurt" in cond_text(s)]
occ = sum(cond_text(s).count("is_lastspurt") for s in pinned)
print("skills pinned to running_style==4:", len(pinned), "of which mention is_lastspurt:", len(last), "occurrences:", occ)
