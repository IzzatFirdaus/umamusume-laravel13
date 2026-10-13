"""Round two: the patch that missed, plus scrubbing every machine-local path.

The deliverable must stand alone, so no citation may point into a working
directory on the machine that built it.
"""

import re
from pathlib import Path

S = Path("D:/Projects/umamusume-laravel13/research-scratch")
t = (S / "assembled-body.v3.md").read_text(encoding="utf-8")
MANIFEST = "https://gametora.com/data/manifests/umamusume.json"

EXP = "`GameTora data export` ([manifest]" + "(" + MANIFEST + ")" + ")"

FIXES = [
    ("repeating the Aries to Pisces cycle once per year",
     "restarting the zodiac series each year without completing it, 19 records over two partial cycles", 1),
    ("`D:\\Projects\\umamusume-laravel13\\research-scratch\\data\\json\\`", EXP, 0),
    ("GameTora export pulled 2026-09-27 into " + "`D:" , "GameTora data export pulled 2026-09-27, " , 0),
    ("Local export figures read from ", "Export figures read from " + EXP + ": ", 1),
    ("`research-scratch/data/json/scenarios.json`", "`scenarios` from the data export", 1),
    ("`research-scratch/data/json/events__champions-meeting.json`", "`events/champions-meeting` from the data export", 1),
    ("`research-scratch/draft-sec3-4-liveops.md` conflict row 5:",
     "the live-ops research pass, its row 5:", 1),
    ("D:\\Projects\\umamusume-laravel13\\research-scratch\\data\\json\\characters.json",
     "[characters dataset]" + "(https://gametora.com/data/umamusume/characters.731ee674.json)", 1),
    ("D:\\Projects\\umamusume-laravel13\\research-scratch\\data\\json\\character-cards.json",
     "[character-cards dataset]" + "(https://gametora.com/data/umamusume/character-cards.679f7c2e.json)", 1),
    ("(tier B local export under test)", "(tier B data export under test)", 1),
]

log = []
for old, new, want in FIXES:
    n = t.count(old)
    if n:
        t = t.replace(old, new)
    log.append((old[:52], n, want))

# any absolute path fragment left anywhere, in any slash style
t = re.sub(r"`?D:[/\\\\](?:[^ `\)|]+?)research-scratch[/\\\\]?", "", t)
t = re.sub(r"research-scratch/", "", t)
t = t.replace("local export `", "data export `").replace("[B] local `", "[B] `")
t = t.replace("the local `events__story-events.json` record", "the `events/story-events` record")
t = t.replace("local `en__foresight__timeline.json`", "`en/foresight/timeline`")
t = t.replace("`en__foresight__timeline.json`", "`en/foresight/timeline`")
t = t.replace("`en__events__champions-meeting.json`", "`en/events/champions-meeting`")
t = t.replace("`gacha__char_banners.json`", "`gacha/char_banners`")

(S / "assembled-body.v3.md").write_text(t, encoding="utf-8")
for o, n, w in log:
    flag = "" if (n == w or (w == 0 and n >= 0)) else "   <-- expected"
    print("  %-54s %d%s" % (o, n, flag))
print("lines:", t.count("\n") + 1)
print("remaining 'research-scratch':", t.count("research-scratch"))
print("remaining 'local export':", t.count("local export"))
print("remaining 'D:/':", t.count("D:/"), "D:\\\\:", t.count("D:\\"))
for i, ln in enumerate(t.splitlines(), 1):
    if "research-scratch" in ln or "D:\\" in ln or "D:/" in ln:
        print("  LEFTOVER %d: %s" % (i, ln[:180]))
