"""Crop the scenario-chip region from a stratified sample of frames and tile them.

The turn chip sits top-left in the training HUD; the scenario chip sits just below it.
Reading those labels across many frames is the cheapest decisive test of which
scenario(s) the corpus actually contains.
"""
import os
from PIL import Image, ImageDraw

SRC = r"docs/game-screenshots"
OUT = r"docs/design-research/_scratch"

files = sorted(f for f in os.listdir(SRC) if f.lower().endswith(".png"))
# stratified: every Nth frame so the whole four-day capture span is covered
step = max(1, len(files) // 96)
sample = files[::step][:96]

CROP_W, CROP_H = 300, 120
COLS, ROWS = 6, 16
sheet = Image.new("RGB", (COLS * CROP_W, ROWS * (CROP_H + 20)), (24, 24, 30))
d = ImageDraw.Draw(sheet)

for i, name in enumerate(sample):
    try:
        with Image.open(os.path.join(SRC, name)) as im:
            im = im.convert("RGB")
            w, h = im.size
            # top-left band: turn chip plus the scenario chip beneath it
            box = (0, int(h * 0.015), int(w * 0.52), int(h * 0.175))
            crop = im.crop(box)
            crop.thumbnail((CROP_W - 6, CROP_H - 6), Image.LANCZOS)
    except Exception:
        continue
    x = (i % COLS) * CROP_W
    y = (i // COLS) * (CROP_H + 20)
    sheet.paste(crop, (x + 3, y + 3))
    d.text((x + 4, y + CROP_H - 2), name[22:28], fill=(230, 230, 250))

sheet.save(os.path.join(OUT, "scenario_chips.png"))
print(f"sampled {len(sample)} of {len(files)} frames -> {sheet.size}")
print("wrote", os.path.join(OUT, "scenario_chips.png"))
