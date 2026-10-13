"""Point the 27 local-export citations at the public dataset URLs they came from.

Writes v2 rather than editing in place, so an in-flight review's line numbers
stay valid.
"""

import json
import re
from pathlib import Path

S = Path("D:/Projects/umamusume-laravel13/research-scratch")
src = (S / "assembled-body.md").read_text(encoding="utf-8")
manifest = json.loads((S / "data/manifest.json").read_text(encoding="utf-8"))

# file name in the local export -> dataset key in the manifest
KEYS = {
    "skills.json": "skills",
    "support_effects.json": "support_effects",
    "character-cards.json": "character-cards",
    "characters.json": "characters",
    "races.json": "races",
    "racetracks.json": "racetracks",
    "scenarios.json": "scenarios",
    "ura-races.json": "ura-races",
    "items.json": "items",
    "factors.json": "factors",
    "support-cards.json": "support-cards",
    "status-effects.json": "status-effects",
    "characters_extended.json": "characters_extended",
    "character_profiles.json": "character_profiles",
    "events__champions-meeting.json": "events/champions-meeting",
    "en__events__champions-meeting.json": "en/events/champions-meeting",
    "events__legend-race.json": "events/legend-race",
    "events__story-events.json": "events/story-events",
    "storyevents.json": "storyevents",
    "en__storyevents.json": "en/storyevents",
    "training_events__char.json": "training_events/char",
    "training_events__friend.json": "training_events/friend",
    "training_events__group.json": "training_events/group",
    "training_events__ssr.json": "training_events/ssr",
    "training_events__sr.json": "training_events/sr",
    "training_events__shared.json": "training_events/shared",
    "training_events__scenario.json": "training_events/scenario",
    "training_events__char_card.json": "training_events/char_card",
    "gacha__char-standard.json": "gacha/char-standard",
    "gacha__support-standard.json": "gacha/support-standard",
    "gacha__special.json": "gacha/special",
    "gacha__char_banners.json": "gacha/char_banners",
    "gacha__support_banners.json": "gacha/support_banners",
    "en__gacha__char-standard.json": "en/gacha/char-standard",
    "en__gacha__support-standard.json": "en/gacha/support-standard",
    "en__gacha__special.json": "en/gacha/special",
    "en__foresight__predicted_releases.json": "en/foresight/predicted_releases",
    "en__foresight__timeline.json": "en/foresight/timeline",
    "nicknames.json": "nicknames",
    "trophies.json": "trophies",
    "support-card-sources.json": "support-card-sources",
    "character-card-sources.json": "character-card-sources",
}

BASE = "https://gametora.com/data/umamusume"
rx = re.compile(r"\]\((D:/Projects/umamusume-laravel13/research-scratch/)?data/json/([A-Za-z0-9_./-]+)\)")

seen = set()
missing = []


def repl(m):
    fname = m.group(2)
    key = KEYS.get(fname)
    if key is None or key not in manifest:
        missing.append(fname)
        return m.group(0)
    seen.add(fname)
    return "](%s/%s.%s.json)" % (BASE, key, manifest[key])

out = rx.sub(repl, src)

before, after = src.count("\n"), out.count("\n")
print("links rewritten:", len(seen))
print("unresolved file names:", sorted(set(missing)))
print("line count before/after:", before, after)
assert before == after, "rewrite changed line count, review line numbers would shift"
assert not missing, "some cited export file has no manifest key"
(S / "assembled-body.v2.md").write_text(out, encoding="utf-8")
print("remaining local links:", len(re.findall(r"\]\(D:/Projects/", out)))
print("wrote assembled-body.v2.md")
