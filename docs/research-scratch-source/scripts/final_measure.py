import re
from pathlib import Path

t = Path("D:/Projects/umamusume-laravel13/UMAMUSUME_REFERENCE.md").read_text(encoding="utf-8")
lines = t.splitlines()
BANNED = re.compile(r"\b(horses?|sire|sires|dam|dams|mare|mares|foal|foals|racehorse|stallion|filly|breeding)\b|🏇", re.I)
print("word-boundary banned hits:", len(BANNED.findall(t)))
in_policy = 0
other = []
for i, ln in enumerate(lines, 1):
    for m in BANNED.finditer(ln):
        if i in (28, 30):
            in_policy += 1
        else:
            other.append((i, m.group(0)))
print("  inside the lore policy lines 28/30:", in_policy)
print("  elsewhere:", len(other), other[:8])

print()
print("em dash occurrences:", t.count("—"))
for i, ln in enumerate(lines, 1):
    if "—" in ln:
        kind = "table row" if ln.startswith("| ") else ("policy text" if i == 30 else "OTHER")
        print("  line %d [%s] x%d" % (i, kind, ln.count("—")))

FILLER = [r"it is worth noting", r"it should be mentioned", r"seamless", r"crucially", r"delve",
          r"leverag", r"robust", r"comprehensive", r"TODO", r"TBD", r"data pending"]
print()
for p in FILLER:
    hits = [i for i, ln in enumerate(lines, 1) if re.search(p, ln, re.I)]
    if hits:
        outside = [i for i in hits if i > 1480]
        print("  %-24s hits %s  (in 5.5/5.6 audit enumeration: %s)" % (p, hits, outside))

print()
print("section tag counts:", {k: t.count(k) for k in ["[JP]", "[Global]", "[Both]", "[JP-Only]"]})
print("audit section claims '0 for' phrases:", len(re.findall(r"0 for ", t)))
