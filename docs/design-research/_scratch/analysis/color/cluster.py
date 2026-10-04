"""Cluster near-duplicate screenshots with dHash, then build labelled contact sheets of representatives."""
import json
import os
from collections import defaultdict

from PIL import Image, ImageDraw

SRC = r"docs/game-screenshots"
OUT = r"docs/design-research/_scratch"

files = sorted(f for f in os.listdir(SRC) if f.lower().endswith(".png"))


def dhash(path, size=8):
    with Image.open(path) as im:
        g = im.convert("L").resize((size + 1, size), Image.LANCZOS)
    px = list(g.getdata())
    bits = 0
    for y in range(size):
        for x in range(size):
            left = px[y * (size + 1) + x]
            right = px[y * (size + 1) + x + 1]
            bits = (bits << 1) | (1 if left > right else 0)
    return bits


def ham(a, b):
    return (a ^ b).bit_count()


hashes = []
for i, name in enumerate(files):
    hashes.append((i, name, dhash(os.path.join(SRC, name))))
    if i % 300 == 0:
        print(f"hashed {i}/{len(files)}", flush=True)

# union-find over consecutive-window similarity; screens repeat in time-clusters
parent = list(range(len(hashes)))


def find(x):
    while parent[x] != x:
        parent[x] = parent[parent[x]]
        x = parent[x]
    return x


def union(a, b):
    ra, rb = find(a), find(b)
    if ra != rb:
        parent[rb] = ra


THRESHOLD = 6
# compare each image against the last member of its own cluster plus a rolling window
recent = []
for idx, (i, name, h) in enumerate(hashes):
    for j, hj in recent:
        if ham(h, hj) <= THRESHOLD:
            union(i, j)
    recent.append((i, h))
    if len(recent) > 60:
        recent.pop(0)

clusters = defaultdict(list)
for i, name, h in hashes:
    clusters[find(i)].append((i, name, h))

reps = []
for root, members in clusters.items():
    members.sort(key=lambda m: m[0])
    # representative = largest file (usually the sharpest full frame) with its index recorded
    best = max(members, key=lambda m: os.path.getsize(os.path.join(SRC, m[1])))
    with Image.open(os.path.join(SRC, best[1])) as im:
        w, hgt = im.size
    reps.append(
        {
            "rep": best[1],
            "rep_index": best[0],
            "count": len(members),
            "first": members[0][1],
            "dim": [w, hgt],
            "members": [m[1] for m in members],
        }
    )

reps.sort(key=lambda r: r["rep_index"])
with open(os.path.join(OUT, "clusters.json"), "w", encoding="utf-8") as fh:
    json.dump(reps, fh, indent=1)

print(f"DISTINCT CLUSTERS: {len(reps)} from {len(files)} images")
big = [r for r in reps if r["dim"] == [1920, 1080]]
print(f"full-hd reps: {len(big)}")

# contact sheets of representatives, 40 per sheet (5 columns x 8 rows)
CELL_W, CELL_H, LABEL, COLS, PER_SHEET = 300, 190, 22, 5, 8
ROWS = PER_SHEET


def tile(target, r, k):
    path = os.path.join(SRC, r["rep"])
    with Image.open(path) as im:
        im = im.convert("RGB")
        im.thumbnail((CELL_W - 6, CELL_H - 6), Image.LANCZOS)
        x = (k % COLS) * CELL_W
        y = (k // COLS) * (CELL_H + LABEL)
        ox = x + (CELL_W - 6 - im.size[0]) // 2
        oy = y + (CELL_H - 6 - im.size[1]) // 2
        target.paste(im, (ox, oy))
        d = ImageDraw.Draw(target)
        d.rectangle([x, y + CELL_H, x + CELL_W - 2, y + CELL_H + LABEL - 2], outline=(90, 90, 110))
        d.text((x + 4, y + CELL_H + 4), f"#{k}  x{r['count']}  {r['rep'][22:28]}", fill=(220, 220, 240))


n_sheets = (len(reps) + COLS * ROWS - 1) // (COLS * ROWS)
for s in range(n_sheets):
    chunk = reps[s * COLS * ROWS : (s + 1) * COLS * ROWS]
    img = Image.new("RGB", (COLS * CELL_W, ROWS * (CELL_H + LABEL)), (20, 20, 26))
    for k, r in enumerate(chunk):
        tile(img, r, k)
    img.save(os.path.join(OUT, f"sheet_{s + 1}of{n_sheets}.png"))
    print(f"sheet {s + 1}/{n_sheets}: reps {s * COLS * ROWS}..{s * COLS * ROWS + len(chunk) - 1}")

