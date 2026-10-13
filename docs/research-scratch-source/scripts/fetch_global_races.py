"""Fetch the Global-side race keys from GameTora, resolving through the live manifest."""

import json
import urllib.request
from pathlib import Path

D = Path("D:/Projects/umamusume-laravel13/research-scratch/data")
J = D / "json"
UA = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
                  "(KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36",
    "Accept": "application/json",
}


def get(url):
    return urllib.request.urlopen(urllib.request.Request(url, headers=UA), timeout=60).read()


live = get("https://gametora.com/data/manifests/umamusume.json")
(J / "manifest.live.json").write_bytes(live)
lm = json.loads(live)
cached = json.loads((D / "manifest.json").read_text(encoding="utf-8"))

print("manifest keys live=%d cached=%d" % (len(lm), len(cached)))
print("keys only in live:", sorted(set(lm) - set(cached)))
drift = {k: (cached[k], lm[k]) for k in cached if k in lm and cached[k] != lm[k]}
print("drifted keys (%d):" % len(drift))
for k, (a, b) in sorted(drift.items()):
    print("  %-34s %s -> %s" % (k, a, b))

WANT = [
    "en/race-fans", "en/race-rewards",
    "race-fans", "race_instances", "races_extended", "racetracks_extended",
    "ura-race-rewards", "db-files/single_mode_rank", "en/db-files/single_mode_rank",
    "career-rivals",
]
for key in WANT:
    if key not in lm:
        print("MISSING from live manifest:", key)
        continue
    out = J / (key.replace("/", "__") + ".json")
    url = "https://gametora.com/data/umamusume/%s.%s.json" % (key, lm[key])
    raw = get(url)
    out.write_bytes(raw)
    rows = json.loads(raw)
    n = len(rows) if isinstance(rows, list) else len(rows.keys())
    print("OK %-34s %8d bytes  rows=%-6d %s" % (key, len(raw), n, url))
