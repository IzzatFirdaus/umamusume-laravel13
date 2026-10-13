"""Per-section gate metrics for the self-audit, plus the lore sweep.

Splits on section boundaries so a weak section cannot hide behind a strong one.
"""

import re
import sys
from collections import Counter
from pathlib import Path

S = Path("D:/Projects/umamusume-laravel13/research-scratch")
target = Path(sys.argv[1]) if len(sys.argv) > 1 else S / "assembled-body.v2.md"
if not target.is_absolute():
    target = S / target
text = target.read_text(encoding="utf-8")
lines = text.splitlines()

BOUNDS = [
    ("Section 1: mechanics", r"^## Section 1"),
    ("Section 2: roster", r"^## Section 2"),
    ("Section 3: live ops", r"^## Section 3"),
    ("Section 4: gacha", r"^## Section 4"),
    ("Conflict log", r"^## Source Conflict Log"),
]

starts = []
for name, pat in BOUNDS:
    idx = next((i for i, ln in enumerate(lines) if re.match(pat, ln)), None)
    if idx is None:
        raise SystemExit("missing boundary: %s" % name)
    starts.append((name, idx))
starts.sort(key=lambda t: t[1])

def slice_of(i):
    a = starts[i][1]
    b = starts[i + 1][1] if i + 1 < len(starts) else len(lines)
    return "\n".join(lines[a:b])

OFFICIAL = ("umamusume.jp", "umamusume.com")
rows_out = []
for i, (name, _) in enumerate(starts):
    body = slice_of(i)
    links = re.findall(r"\]\((https?://[^)\s]+)", body) + re.findall(r"`https?://[^`]+`", body)
    domains = {re.sub(r"^https?://", "", l.strip("()")).split("/")[0] for l in links}
    rows_out.append({
        "section": name,
        "lines": body.count("\n") + 1,
        "links": len(links),
        "domains": len(domains),
        "official_S": sum(1 for l in links if any(o in l for o in OFFICIAL)),
        "stale": body.count("⚠️ STALE"),
        "unverified": body.count("❌ UNVERIFIED"),
        "speculation": body.count("[SPECULATION]"),
        "table_rows": sum(1 for ln in body.splitlines() if ln.startswith("| ") and not ln.startswith("|---")),
    })

for r in rows_out:
    print(r)

print()
print("TOTAL distinct link domains:", len({d for l in re.findall(r"\]\((https?://[^)\s]+)", text) for d in [re.sub(r"^https?://", "", l).split("/")[0]]}))
print("top domains:", Counter(re.sub(r"^https?://", "", l).split("/")[0] for l in re.findall(r"\]\((https?://[^)\s]+)", text)).most_common(12))

BANNED = re.compile(r"\b(horses?|sire|sires|dam|dams|mare|mares|foal|foals|racehorse|stallion|filly|mare|breeding|🏇)\b", re.I)
print()
print("banned tokens with word boundaries:", len(BANNED.findall(text)))
for m in BANNED.finditer(text):
    ln = text[:m.start()].count("\n") + 1
    seg = lines[ln - 1]
    ctx = seg[max(0, m.start() - seg.find(m.group(0)) - 40):][:140]
    print("  line %d: %r in ...%s" % (ln, m.group(0), ctx[-110:]))

SUB = re.compile(r"(horses?|sire|dam|mare|foal|racehorse|breeding)", re.I)
hits = []
for i, ln in enumerate(lines, 1):
    for m in SUB.finditer(ln):
        hits.append((i, m.group(0), ln[max(0, m.start() - 30):m.start() + 30]))
print()
print("substring hits (need adjudication):", len(hits))
words = Counter(h[1].lower() for h in hits)
print("  by token:", dict(words))
for h in hits[:14]:
    print("   line %d %r context %r" % (h[0], h[1], h[2]))

print()
print("em dash lines:", [i for i, ln in enumerate(lines, 1) if "—" in ln])
print("hedging phrases:", {p: len(re.findall(p, text, re.I)) for p in [
    r"it is worth noting", r"it should be mentioned", r"importantly,", r"seamless", r"crucially",
    r"delve", r"leverag", r"robust", r"comprehensive", r"TODO", r"TBD", r"data pending"]})
print("bare-domain citations (no path):", len(re.findall(r"\]\(https?://[^/)]+\)", text)))
