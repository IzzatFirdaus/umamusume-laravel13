"""Isolate UI accent colours: cluster only vivid pixels (high saturation), per key frame."""
import colorsys
import json
import os
from collections import Counter, defaultdict

from PIL import Image

SRC = r"docs/game-screenshots"
OUT = r"docs/design-research/_scratch"

FRAMES = [
    ("Screenshot 2026-07-14 194819.png", "training-green"),
    ("Screenshot 2026-07-14 202142.png", "training-green-junior"),
    ("Screenshot 2026-07-17 230755.png", "training-blue-plus-calendar"),
    ("Screenshot 2026-07-14 124128.png", "event-choices"),
    ("Screenshot 2026-07-14 124926.png", "event-choices-2"),
    ("Screenshot 2026-07-18 001811.png", "most-repeated-frame"),
    ("Screenshot 2026-07-14 025344.png", "guide-dialog"),
    ("Screenshot 2026-07-17 235229.png", "team-showdown"),
    ("Screenshot 2026-07-18 134106.png", "photo-album"),
    ("Screenshot 2026-07-15 005040.png", "result-pits-modal"),
]

report = {}
for fname, label in FRAMES:
    path = os.path.join(SRC, fname)
    if not os.path.exists(path):
        report[label] = {"file": fname, "missing": True}
        continue
    with Image.open(path) as im:
        im = im.convert("RGB")
        im.thumbnail((700, 700), Image.LANCZOS)
        px = list(im.getdata())
    vivid = []
    for r, g, b in px:
        h, s, v = colorsys.rgb_to_hsv(r / 255, g / 255, b / 255)
        if s > 0.42 and 0.35 < v <= 1.0:
            vivid.append((round(h * 360 / 15) * 15 % 360, r, g, b))
    buckets = defaultdict(list)
    for hue, r, g, b in vivid:
        buckets[hue].append((r, g, b))
    out = []
    for hue, cols in sorted(buckets.items(), key=lambda kv: -len(kv[1])):
        if len(cols) < max(12, len(vivid) // 120):
            continue
        avg = tuple(round(sum(c[i] for c in cols) / len(cols)) for i in range(3))
        # keep the most saturated member as the representative (cluster mean desaturates)
        best = max(cols, key=lambda c: colorsys.rgb_to_hsv(*[v / 255 for v in c])[1])
        out.append(
            {
                "hue": hue,
                "share_pct": round(100 * len(cols) / len(px), 2),
                "mean_hex": "#{:02X}{:02X}{:02X}".format(*avg),
                "peak_hex": "#{:02X}{:02X}{:02X}".format(*best),
            }
        )
    # neutrals: near-white panel fills and dark text
    whites = [(r, g, b) for r, g, b in px if colorsys.rgb_to_hsv(r / 255, g / 255, b / 255)[1] < 0.09 and min(r, g, b) > 225]
    darks = [(r, g, b) for r, g, b in px if colorsys.rgb_to_hsv(r / 255, g / 255, b / 255)[2] < 0.42]
    def mean(cols):
        if not cols:
            return None
        a = tuple(round(sum(c[i] for c in cols) / len(cols)) for i in range(3))
        return "#{:02X}{:02X}{:02X}".format(*a)
    # darkest brown-ish cluster = UI text colour
    browns = [(r, g, b) for r, g, b in px if 0.15 < colorsys.rgb_to_hsv(r / 255, g / 255, b / 255)[1] < 0.55 and 0.18 < colorsys.rgb_to_hsv(r / 255, g / 255, b / 255)[2] < 0.52]
    report[label] = {
        "file": fname,
        "size": list(im.size),
        "accents": out[:8],
        "panel_white_mean": mean(whites),
        "panel_white_share_pct": round(100 * len(whites) / len(px), 1),
        "text_brown_mean": mean(browns),
        "text_brown_share_pct": round(100 * len(browns) / len(px), 1),
    }

with open(os.path.join(OUT, "accents.json"), "w", encoding="utf-8") as fh:
    json.dump(report, fh, indent=1)

for label, d in report.items():
    if d.get("missing"):
        print(f"{label}: MISSING {d['file']}")
        continue
    print(f"\n{label}  [{d['file']}] {d['size'][0]}x{d['size'][1]}")
    print(f"  panel white {d['panel_white_mean']} ({d['panel_white_share_pct']}%)   text brown {d['text_brown_mean']} ({d['text_brown_share_pct']}%)")
    for a in d["accents"]:
        print(f"  hue {a['hue']:>3}  {a['share_pct']:>5}%  mean {a['mean_hex']}  peak {a['peak_hex']}")
