"""Ground truth for the review's disputed items, straight from the export."""

import json
import urllib.request
from pathlib import Path

D = Path("D:/Projects/umamusume-laravel13/research-scratch/data")
J = D / "json"


def load(n):
    return json.loads((J / n).read_text(encoding="utf-8"))


print("== strategy client labels (factors.json) ==")
fac = load("factors.json")
for f in fac:
    if f.get("id") in (11, 12, 21, 22, 23, 24, 31, 32, 33, 34):
        print("  id", f["id"], "| en_gl:", f.get("name_en_gl"), "| en:", f.get("name_en"), "| jp:", f.get("name_ja") or f.get("name_jp"))

print()
print("== card-level availability ==")
cards = load("character-cards.json")
chars = {c["char_id"]: c for c in load("characters.json")}
debut = {}
for c in sorted(cards, key=lambda c: (c.get("release") or "9999", c["card_id"])):
    debut.setdefault(c["char_id"], c)
nondebut = [c for c in cards if c is not debut[c["char_id"]]]
print("  debut cards:", len(debut), "with release_en:", sum(1 for c in debut.values() if c.get("release_en")))
print("  variant cards:", len(nondebut), "with release_en:", sum(1 for c in nondebut if c.get("release_en")))
print("  variant cards WITHOUT release_en whose unit IS playable_en (the mis-tag class):",
      sum(1 for c in nondebut if not c.get("release_en") and chars[c["char_id"]].get("playable_en")))
print("  variant cards without release_en at all:", sum(1 for c in nondebut if not c.get("release_en")))
print("  total cards with release_en:", sum(1 for c in cards if c.get("release_en")))

print()
print("== en/events/legend-race (was claimed 15 rows) ==")
m = json.loads((D / "manifest.json").read_text(encoding="utf-8"))
key = "en/events/legend-race"
if key in m:
    url = "https://gametora.com/data/umamusume/%s.%s.json" % (key, m[key])
    raw = urllib.request.urlopen(urllib.request.Request(url, headers={"User-Agent": "Mozilla/5.0"}), timeout=60).read()
    (J / "en__events__legend-race.json").write_bytes(raw)
    lr = json.loads(raw.decode("utf-8"))
    print("  fetched", len(raw), "bytes, rows:", len(lr))
    for r in lr[-3:]:
        print("   ", {k: r.get(k) for k in ("id", "name", "name_en", "start", "end")})
else:
    print("  key absent from manifest")

print()
print("== 1.2 course-factor recomputations ==")
tracks = load("racetracks.json")
courses = [c for c in tracks if c.get("phases")] if isinstance(tracks, list) else []
print("  racetracks entries:", len(tracks))
