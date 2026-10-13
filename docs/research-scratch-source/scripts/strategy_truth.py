import json
from pathlib import Path

J = Path("D:/Projects/umamusume-laravel13/research-scratch/data/json")
fac = json.loads((J / "factors.json").read_text(encoding="utf-8"))
print("== every factor whose JP name is one of the four strategies ==")
JP = {"逃げ": 1, "先行": 1, "差し": 1, "追込": 1}
rows = []
for cat, lst in fac.items():
    for f in lst:
        if f.get("name_ja") in JP:
            rows.append((cat, f.get("id"), f.get("name_ja"), f.get("name_en"), f.get("name_en_gl")))
for r in sorted(rows, key=lambda x: str(x[2])):
    print("  cat=%-8s id=%-4s jp=%-4s name_en=%-16s name_en_gl=%s" % r)

print()
print("== pink factors (aptitudes), full list ==")
for f in fac.get("pink", []):
    print("  id=%-4s jp=%-8s en=%-14s en_gl=%s" % (f.get("id"), f.get("name_ja"), f.get("name_en"), f.get("name_en_gl")))

print()
print("== card-level availability ==")
cards = json.loads((J / "character-cards.json").read_text(encoding="utf-8"))
chars = {c["char_id"]: c for c in json.loads((J / "characters.json").read_text(encoding="utf-8"))}
debut = {}
for c in sorted(cards, key=lambda c: (c.get("release") or "9999", c["card_id"])):
    debut.setdefault(c["char_id"], c)
nondebut = [c for c in cards if c is not debut[c["char_id"]]]
d_en = sum(1 for c in debut.values() if c.get("release_en"))
nd_en = sum(1 for c in nondebut if c.get("release_en"))
print("  debut cards: %d, with release_en: %d, without: %d" % (len(debut), d_en, len(debut) - d_en))
print("  variant cards: %d, with release_en: %d, without: %d" % (len(nondebut), nd_en, len(nondebut) - nd_en))
mis = [c for c in nondebut if not c.get("release_en") and chars[c["char_id"]].get("playable_en")]
print("  MIS-TAG CLASS, variant without release_en but unit playable_en: %d" % len(mis))
mis2 = [c for c in debut.values() if not c.get("release_en") and chars[c["char_id"]].get("playable_en")]
print("  same class among debut cards: %d" % len(mis2))
print("  total cards with release_en: %d" % sum(1 for c in cards if c.get("release_en")))
