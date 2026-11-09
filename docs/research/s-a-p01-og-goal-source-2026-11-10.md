# `S-A-P01-OG` — Our Grand Concert's `goal_source`, resolved

**Slice:** `S-A-P01-OG-1`. Research pass, then the config decision it licenses.
**Base:** `6493326a`. No code change beyond a comment; `config/scenarios.php` needs no value change.

**Outcome:** `goal_source` for `our_grand_concert` **is `trainee_goals`**, and it is already set at
`config/scenarios.php:1090`. The open presumption was carried in a *code comment*
(`app/Services/Career/TeamPanelBuilder.php:222-227`), which still says OGC "declares no
`goal_source`" and cites a question `P-01 §7 decision 7` that has since been answered. The
substance was settled by `S-A-P01.4`; what remained stale was the prose pointing at it.

---

## 1. The question

`P-01 §7 decision 7` (open at the time) asked whether Our Grand Concert's career-goal ladder is the
trainee's own. `TeamPanelBuilder::traineeGoalRows()` reads one config key:

```php
$goalSource = config('scenarios.scenarios.'.$run->scenarioKey().'.goal_source', 'trainee_goals');
```

`null` disables the panel; any string enables it; an absent key defaults to `'trainee_goals'`. So
the question resolves to: which value should `scenarios.our_grand_concert.goal_source` hold?

## 2. Evidence

### 2.1 The scenario's own document

`docs/scenarios/07-grand-concert.md:92-95`:

> "A race career underneath. Unlike Trackblazer, this scenario "returns the standard trainee career
> features of race goals and career events", including New Year events, the hot spring lottery and
> per-character hidden events…"

And at `:242`, with the `S-A-P01.4` dated note already in place:

> "The scenario has character race goals, which is what the panel is for… **Dated note 2026-11-08
> (S-A-P01.4):** the decision named here has since been made, and it went the other way from what
> this cell expected — the character goal ladder *is* composed for this scenario, and what stays off
> is the race *calendar*. The ladder's grain is the character rather than the scenario (`ADR-0026`:
> "Trainee grain, no run scope"…), so `trainee_goals` describes the trainee under any scenario that
> asks for it."

### 2.2 The reference corpus

`docs/UMAMUSUME_REFERENCE.md` §2.8 (`### 2.8. Our Grand Concert closed open: the primary read, one
refutation, four naming rows`, `:1619`) records the 2026-10-05 primary read that discharged the
extraction suspension. Its own summary of what that read licenses is at `:1671-1675`:

> "…`facility_level_source` now says `repetition` from GameTora's own sentence, and `documented` is
> now true of the file it claims to describe. Whether the scenario gains a resource chip, a live
> marker or a goal-race calendar is a design decision about surfaces, and it is the owner's…"

That sentence is about **surfaces** (a calendar, a chip, a marker), not about whether goal *races*
exist. The existence question is answered in `docs/scenarios/09-global-race-calendar.md:643`, the
calendar master's own forward correction:

> "Game8 prints that the scenario "returns the standard trainee career features of race goals and
> career events", so goal races exist here in the URA sense rather than being unknown. **That is
> still not a fixture list**, and this file adds none. The goals are the character's own, per
> trainee rather than per scenario, which is the shape `01-ura-finale.md` already documents…"

So the corpus holds two distinct facts: goal races exist here, and their grain is the trainee.

### 2.3 `ADR-0026` — the grain decision

`docs/adr/0026-per-trainee-career-objectives.md:71`:

> "**Trainee grain, no run scope.** `trainee_goals` keys on `umamusume_id`, not on
> `character_card_id` and not on…"

Status: **Accepted** (`ADR-0026`'s contract block: "accepted — the owner signed FR-A-10 as worded
and ratified this text on 2026-10-13"), and `code: built`. This is the binding decision the
config key is expressing, and it is scenario-agnostic by construction: the ladder is not a scenario
fact to be granted per scenario, it is a property of the trainee that any scenario may ask for.

### 2.4 Web corroboration (the dispatch's `S-A-P01.OG` instruction)

Two independent English sources, both read this pass:

- **Game8, "Grand Live (Grand Concert) Scenario Guide"** (`archives/607337`, page updated
  2026-07-26): *"Unlike in Trackblazer (MANT), Grand Live (Grand Concert) returns the standard
  trainee career features of race goals and career events. This includes the familiar New Year
  events, the hot spring lottery, and hidden events for each Umamusume."* — and note the phrase
  **"for each Umamusume"**, which is the per-character grain stated on the publisher side.
- **uma.guide, "Grand Concert Guide"** (`uma.guide/guides/grand-concert`, last updated 2026-07-20):
  the page documents caps, deck shape, Performance Points, concerts, scenario sparks and the
  unique-skill fan thresholds, and **says nothing about goals** — a silent source, not a
  contradicting one. Its unique-skill table is explicitly labelled "the same as the URA Finale
  Scenario", which is the same structural claim from the other direction.

Neither source contradicts the corpus, and Game8 states the per-character shape directly.

## 3. Decision

**`goal_source` for `our_grand_concert` is `'trainee_goals'`.**

- `config/scenarios.php:1090` already carries it, under a comment citing
  `07-grand-concert.md:242`, `ADR-0026` and `09-global-race-calendar.md:641`. **No config value
  changes.** The dispatch's C.2 "if yes → no config change, close presumption in comment citing
  research artifact" branch is the one that fires.
- The presumption that stayed open was in `TeamPanelBuilder.php:222-227`, which asserted OGC
  "declares no `goal_source` while P-01 §7 decision 7 is open" and pointed at
  `config/scenarios.php:1118-1119` for the unresolved sentence. Both halves are now false: the key
  is declared, and the question is answered. Corrected in place below.

### Why not `null`

`null` would suppress the ladder for this scenario. The corpus says goal races exist here "in the
URA sense", Game8 says the scenario "returns the standard trainee career features of race goals",
and `ADR-0026` makes the ladder a trainee property — so suppressing it would hide stored,
per-trainee rows that the source says apply. That is a data-loss-shaped rendering choice, not a
conservative one.

### Why the default does not decide it

`TeamPanelBuilder:228` defaults an **absent** key to `'trainee_goals'`. That default is right but it
is not evidence: it cannot distinguish "the scenario was never asked" from "the scenario answered
yes". Declaring the key explicitly is what makes the answer auditable, which is why the config
comment names three sources rather than relying on the fallback.

## 4. The edit this licenses

One comment in `app/Services/Career/TeamPanelBuilder.php`, replacing the stale
"declares no `goal_source`" paragraph with the resolved state. No behavior change: the value the
expression reads is unchanged, and the payload is byte-identical.

## 5. What stays open

- **The goal-race calendar** remains off for this scenario, and that is a surface decision, not a
  data one (`09-global-race-calendar.md:643` "not a fixture list"; `UMAMUSUME_REFERENCE.md:1673-1675`
  "a design decision about surfaces, and it is the owner's"). This slice does not touch it.
- **D-241 / gate G-41 wording** still names this scenario as the "undescribed scenario" acceptance
  case while the scenario is documented (`config/scenarios.php:1118-1128` records the same
  tension). Out of scope; recorded, not resolved.

## 6. Sources, with the read that produced them

| Claim | Source | Type |
|---|---|---|
| Goal races exist in OGC, URA sense | `docs/scenarios/07-grand-concert.md:92-95`; `docs/scenarios/09-global-race-calendar.md:643` | repo corpus |
| Ladder grain is the trainee, not the scenario | `docs/adr/0026-…md:71` (Accepted) | ADR |
| Same, publisher-side | Game8 `archives/607337` — "race goals and career events … for each Umamusume" | web, read 2026-11-10 |
| Unique-skill thresholds "same as URA Finale" | uma.guide `guides/grand-concert` | web, read 2026-11-10 |
| Calendar stays off | `docs/scenarios/09-global-race-calendar.md:643`; `UMAMUSUME_REFERENCE.md:1673-1675` | repo corpus |
| The key is already set | `config/scenarios.php:1090` | code |
