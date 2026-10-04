"""Build a labelled contact sheet from a filter expression over clusters.json."""
import json
import os
import sys

from PIL import Image, ImageDraw

SRC = r"docs/game-screenshots"
OUT = r"docs/design-research/_scratch"

reps = json.load(open(os.path.join(OUT, "clusters.json"), encoding="utf-8"))
mode = sys.argv[1]

if mode == "wide":
    sel = [r for r in reps if r["dim"][0] >= 1200]
elif mode == "mid":
    sel = [r for r in reps if 780 <= r["dim"][0] < 1200]
elif mode == "crop":
    sel = [r for r in reps if r["dim"][0] < 780]
elif mode == "bigclusters":
    sel = sorted(reps, key=lambda r: -r["count"])[:int(sys.argv[2])]

CELL_W, CELL_H, LABEL, COLS = 316, 196, 24, 6
ROWS = 8
PER = COLS * ROWS
n_sheets = (len(sel) + PER - 1) // PER
print(f"{mode}: {len(sel)} reps -> {n_sheets} sheets")

for s in range(n_sheets):
    chunk = sel[s * PER : (s + 1) * PER]
    img = Image.new("RGB", (COLS * CELL_W, ROWS * (CELL_H + LABEL)), (18, 18, 24))
    d = ImageDraw.Draw(img)
    for k, r in enumerate(chunk):
        path = os.path.join(SRC, r["rep"])
        with Image.open(path) as im:
            im = im.convert("RGB")
            im.thumbnail((CELL_W - 8, CELL_H - 8), Image.LANCZOS)
        x = (k % COLS) * CELL_W
        y = (k // COLS) * (CELL_H + LABEL)
        img.paste(im, (x + (CELL_W - im.size[0]) // 2, y + (CELL_H - im.size[1]) // 2))
        n = s * PER + k
        d.rectangle([x, y + CELL_H, x + CELL_W - 3, y + CELL_H + LABEL - 3], outline=(120, 120, 150))
        d.text((x + 5, y + CELL_H + 5), f"n{n} x{r['count']} {r['rep'][22:28]}", fill=(235, 235, 250))
    img.save(os.path.join(OUT, f"cs_{mode}_{s + 1}of{n_sheets}.png"))
    print("wrote", f"cs_{mode}_{s + 1}of{n_sheets}.png", img.size)
