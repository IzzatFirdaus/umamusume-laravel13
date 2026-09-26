"""Triage the screenshot corpus: dimensions, color signature, and nearest-neighbour grouping."""
import json
import os
import sys
from collections import defaultdict

from PIL import Image

SRC = r"docs/game-screenshots"
OUT = r"docs/design-research/_scratch"

files = sorted(f for f in os.listdir(SRC) if f.lower().endswith(".png"))
records = []
for i, name in enumerate(files):
    path = os.path.join(SRC, name)
    try:
        with Image.open(path) as im:
            w, h = im.size
            small = im.convert("RGB").resize((16, 9), Image.LANCZOS)
            px = list(small.getdata())
            n = len(px)
            mean = tuple(round(sum(c[j] for c in px) / n) for j in range(3))
            lum = round(sum(0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2] for c in px) / n)
            # saturation-ish spread: how colourful is the frame
            spread = round(sum(max(c) - min(c) for c in px) / n)
            # coarse 4x4 grid of per-cell mean luminance -> 16 numbers
            grid = im.convert("L").resize((4, 4), Image.LANCZOS)
            cells = list(grid.getdata())
    except Exception as exc:  # noqa: BLE001
        records.append({"i": i, "name": name, "error": str(exc)})
        continue
    records.append(
        {
            "i": i,
            "name": name,
            "w": w,
            "h": h,
            "mean": mean,
            "lum": lum,
            "sat": spread,
            "cells": cells,
        }
    )
    if i % 200 == 0:
        print(f"{i}/{len(files)}", file=sys.stderr, flush=True)

with open(os.path.join(OUT, "signatures.json"), "w", encoding="utf-8") as fh:
    json.dump(records, fh)

dims = defaultdict(int)
for r in records:
    dims[(r.get("w"), r.get("h"))] += 1
print("DIMENSIONS:", dict(dims))

lums = [r["lum"] for r in records if "lum" in r]
lums.sort()
print(f"LUMINANCE min/med/max: {lums[0]} {lums[len(lums) // 2]} {lums[-1]}")


def bucket_key(r):
    if "lum" not in r:
        return None
    mr, mg, mb = r["mean"]
    total = mr + mg + mb or 1
    dom = max((mr, mg, mb))
    if dom == mb and mb > mr * 1.15:
        hue = "blue"
    elif dom == mr and mr > mb * 1.15:
        hue = "red"
    elif dom == mg and mg > mr * 1.15:
        hue = "green"
    else:
        hue = "neutral"
    dark = "d" if r["lum"] < 90 else ("m" if r["lum"] < 160 else "l")
    return f"{dark}{hue}"


groups = defaultdict(list)
for r in records:
    k = bucket_key(r)
    if k:
        groups[k].append(r)

for k, v in sorted(groups.items(), key=lambda kv: -len(kv[1])):
    print(f"BUCKET {k}: {len(v)}")
