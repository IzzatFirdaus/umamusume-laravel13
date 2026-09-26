"""Probe exact pixel colours in named UI regions of key screenshots, plus dominant-colour summaries."""
import json
import os
from collections import Counter

from PIL import Image

SRC = r"docs/game-screenshots"
OUT = r"docs/design-research/_scratch"

# (image, region label, [(x, y), ...]) -- coordinates read off the frames inspected visually
PROBES = [
    ("Screenshot 2026-07-14 194819.png", "training-screen-green-theme", [
        ((150, 207), "discipline-banner-fill(Dirt)"),
        ((100, 905), "training-btn-ring(Speed)"),
        ((100, 963), "training-btn-labelband"),
        ((283, 905), "training-btn-active-core"),
        ((400, 745), "stat-panel-header-band"),
        ((640, 745), "skillpts-header-band"),
        ((400, 780), "stat-panel-body"),
        ((78, 776), "grade-badge-Eplus"),
        ((232, 776), "grade-badge-F"),
        ((560, 130), "mood-pill-GOOD"),
        ((196, 130), "energy-gauge-fill-cyan"),
        ((245, 130), "energy-gauge-fill-green"),
        ((300, 130), "energy-gauge-fill-olive"),
        ((355, 130), "energy-gauge-track"),
        ((40, 40), "turn-card-blue"),
        ((250, 19), "date-pill-bg"),
        ((100, 19), "goal-pill-bg"),
        ((648, 74), "details-btn-bg"),
        ((400, 686), "gain-cloud-white"),
        ((200, 700), "gain-number-orange"),
        ((400, 838), "failure-pill-blue"),
        ((60, 170), "lvl-ribbon-pale"),
        ((720, 210), "bond-gauge-green"),
        ((720, 282), "bond-gauge-blue"),
    ]),
    ("Screenshot 2026-07-17 230755.png", "training-screen-blue-theme-and-race-calendar", [
        ((100, 180), "discipline-banner-blue-freestyle"),
        ((100, 160), "lvl-ribbon-pale-blue"),
        ((245, 895), "training-btn-ring-blue"),
        ((1310, 39), "section-capsule-green"),
        ((1372, 152), "tab-active-green"),
        ((1180, 152), "tab-inactive-white"),
        ((1100, 152), "tabbar-track"),
        ((1440, 248), "datecell-current-paleyellow"),
        ((1170, 248), "datecell-disabled-grey"),
        ((1250, 372), "datecell-white"),
        ((1170, 294), "plus-icon-green"),
        ((1125, 497), "race-thumb-scheduled-pink"),
        ((1382, 983), "reset-btn"),
        ((222, 1041), "back-btn-white"),
        ((545, 1054), "skip-btn-green"),
        ((703, 1054), "quick-btn-white"),
        ((1847, 98), "rightrail-label"),
    ]),
    ("Screenshot 2026-07-14 124128.png", "event-choices-panel", [
        ((1200, 162), "choice-preview-selected-green"),
        ((1200, 361), "choice-preview-selected-green2"),
        ((1200, 240), "choice-preview-unselected-white"),
        ((1199, 66), "stat-header-band"),
        ((1240, 105), "stat-body-white"),
        ((1560, 66), "skillpts-teal"),
        ((150, 36), "event-tag-cyan"),
        ((300, 76), "event-title-ribbon-blue"),
        ((400, 624), "choice-btn-white-fill"),
        ((680, 624), "choice-btn-arrow-grad"),
        ((400, 803), "choice-btn-gold-arrow"),
        ((145, 624), "choice-icon-green-circle"),
        ((145, 803), "choice-icon-gold-circle"),
    ]),
    ("Screenshot 2026-07-14 202142.png", "training-screen-junior", [
        ((150, 199), "discipline-banner-green"),
        ((400, 700), "stat-panel-header"),
        ((640, 700), "skillpts-header"),
        ((96, 735), "grade-D-blue"),
        ((243, 735), "grade-F-purple"),
        ((560, 121), "mood-GREAT-pink"),
        ((100, 160), "lvl-ribbon-grey"),
    ]),
]

result = {}
for fname, label, probes in PROBES:
    path = os.path.join(SRC, fname)
    with Image.open(path) as im:
        im = im.convert("RGB")
        w, h = im.size
        rows = []
        for (x, y), name in probes:
            if x >= w or y >= h:
                rows.append({"name": name, "hex": None, "note": f"out of frame {w}x{h}"})
                continue
            # average a 3x3 block to dodge single-pixel noise / text antialiasing
            block = [im.getpixel((min(x + dx, w - 1), min(y + dy, h - 1))) for dx in (-1, 0, 1) for dy in (-1, 0, 1)]
            avg = tuple(round(sum(c[i] for c in block) / len(block)) for i in range(3))
            rows.append({"name": name, "at": [x, y], "hex": "#{:02X}{:02X}{:02X}".format(*avg)})
    result[label] = {"file": fname, "size": [w, h], "probes": rows}

# dominant palette per key frame
for fname, label, _ in PROBES:
    path = os.path.join(SRC, fname)
    with Image.open(path) as im:
        q = im.convert("RGB").resize((300, int(300 * im.size[1] / im.size[0])), Image.LANCZOS).quantize(10)
        pal = q.getpalette()
        counts = Counter(q.getdata())
        top = []
        for idx, n in counts.most_common(10):
            r, g, b = pal[idx * 3 : idx * 3 + 3]
            top.append({"hex": f"#{r:02X}{g:02X}{b:02X}", "pct": round(100 * n / sum(counts.values()), 1)})
    result[label]["dominant"] = top

with open(os.path.join(OUT, "colorprobes.json"), "w", encoding="utf-8") as fh:
    json.dump(result, fh, indent=1)

for label, d in result.items():
    print(f"\n=== {label}  ({d['file']}) ===")
    for p in d.get("probes", []):
        print(f"  {p['name']:<38} {p.get('hex')}")
    print("  dominant:", "  ".join(f"{t['hex']} {t['pct']}%" for t in d["dominant"]))
