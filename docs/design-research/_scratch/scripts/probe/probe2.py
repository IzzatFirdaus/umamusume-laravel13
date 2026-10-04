"""Second probe pass with coordinates read off frames inspected at full resolution."""
import json
import os

from PIL import Image

SRC = r"docs/game-screenshots"
OUT = r"docs/design-research/_scratch"

PROBES = [
    ("Screenshot 2026-07-17 234521.png", "log-and-skill-learn", [
        ((1310, 40), "log-header-green-face"),
        ((1075, 40), "log-header-lattice-dark"),
        ((1140, 40), "log-header-lattice-light"),
        ((1600, 40), "log-header-green-right"),
        ((1400, 160), "log-card-white"),
        ((1400, 300), "log-card-white2"),
        ((1300, 571), "phase-bar-green"),
        ((1240, 571), "phase-bar-green-left"),
        ((500, 460), "skill-card-selected-gold"),
        ((300, 430), "skill-card-selected-gold-left"),
        ((500, 590), "skill-card-idle-lavender"),
        ((500, 730), "skill-card-idle-lavender2"),
        ((740, 417), "hint-badge-orange"),
        ((740, 470), "cost-number-pill"),
        ((799, 470), "cost-plus-green"),
        ((686, 470), "cost-minus-grey"),
        ((533, 917), "confirm-green-face"),
        ((533, 895), "confirm-green-top"),
        ((533, 940), "confirm-green-bottom"),
        ((768, 917), "reset-white"),
        ((585, 353), "skillpoints-label-green"),
        ((700, 353), "skillpoints-value-orange-text"),
        ((1845, 360), "nav-rail-active-tile"),
        ((1845, 100), "nav-rail-idle"),
        ((215, 1036), "back-btn-white"),
        ((545, 1054), "skip-green"),
    ]),
    ("Screenshot 2026-07-17 232345.png", "umamusume-details-modal", [
        ((700, 48), "modal-header-green"),
        ((300, 48), "modal-header-lattice"),
        ((400, 267), "stat-band-green"),
        ((743, 537), "segment-active-green"),
        ((392, 537), "segment-idle-white"),
        ((450, 351), "aptitude-pill-white"),
        ((519, 351), "aptitude-grade-A"),
        ((775, 351), "aptitude-grade-G"),
        ((605, 389), "aptitude-grade-B-pink"),
        ((555, 389), "aptitude-grade-A-orange"),
        ((400, 604), "skill-chip-lavender"),
        ((400, 596), "unique-skill-chip-pink"),
        ((545, 1002), "close-btn-white"),
        ((545, 990), "close-btn-gloss"),
        ((600, 170), "potential-lvl-pill"),
        ((400, 130), "modal-body-bg"),
    ]),
    ("Screenshot 2026-07-14 194819.png", "training-hud", [
        ((60, 40), "turn-card-numeral-blue"),
        ((35, 12), "turn-card-tab-blue"),
        ((250, 19), "date-strip-bg"),
        ((100, 19), "goal-label-pill"),
        ((400, 40), "goal-value-brown-text"),
        ((648, 74), "details-btn"),
        ((100, 130), "energy-label-pill"),
        ((200, 130), "energy-fill-1"),
        ((240, 130), "energy-fill-2"),
        ((280, 130), "energy-fill-3"),
        ((330, 130), "energy-fill-4"),
        ((390, 130), "energy-track-empty"),
        ((560, 130), "mood-good-orange"),
        ((60, 170), "lvl-ribbon-pale"),
        ((150, 207), "discipline-banner-green"),
        ((400, 745), "stat-band-bluegrey"),
        ((640, 745), "skillpts-band-teal"),
        ((400, 780), "stat-body"),
        ((96, 735), "grade-badge-D"),
        ((243, 735), "grade-badge-F"),
        ((400, 838), "failure-pill"),
        ((100, 950), "btn-ring-green"),
        ((100, 905), "btn-inner-white"),
        ((283, 905), "btn-active-icon"),
    ]),
]

out = {}
for fname, label, probes in PROBES:
    p = os.path.join(SRC, fname)
    rows = []
    with Image.open(p) as im:
        im = im.convert("RGB")
        w, h = im.size
        for (x, y), name in probes:
            if x >= w or y >= h:
                rows.append((name, "OFF-FRAME"))
                continue
            block = [im.getpixel((max(0, min(x + dx, w - 1)), max(0, min(y + dy, h - 1)))) for dx in (-2, 0, 2) for dy in (-2, 0, 2)]
            avg = tuple(round(sum(c[i] for c in block) / len(block)) for i in range(3))
            rows.append((name, "#{:02X}{:02X}{:02X}".format(*avg)))
    out[label] = {"file": fname, "size": [w, h], "rows": rows}
    print(f"\n=== {label} ({fname} {w}x{h}) ===")
    for n, hx in rows:
        print(f"  {n:<32} {hx}")

with open(os.path.join(OUT, "colorprobes2.json"), "w", encoding="utf-8") as fh:
    json.dump(out, fh, indent=1)
