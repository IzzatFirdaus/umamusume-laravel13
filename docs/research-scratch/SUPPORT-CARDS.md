# Support Cards

## Provenance

This document consolidates the following source files verbatim (no summarization, no deduplication):

- `docs/design-research/support-cards-mechanics-2026-10-01.md` (176 lines)
- `docs/design-research/support-cards-module-plan-2026-10-01.md` (207 lines)
- `o8-per-card-state-proposal.md` (130 lines, 9 headings), folded in 2026-10-04 (Round 11) as the section
  `## o8-per-card-state-proposal.md` at the end of this file. It had been sitting standalone inside
  `docs/research-scratch/`, which §File discipline of `INDEX.md` does not allow, so this fold corrects a
  standing violation as well as consolidating a source. Chosen home because the four proposed
  `deck_slots` columns are this master's own open question about where per-card state belongs. Status is
  **proposal only**, stopped on a PRD-absence premise, so the owner ruling it waits on is still open and
  this section is where it continues.

---

## support-cards-mechanics-2026-10-01.md

### Support card mechanics, sourced record (2026-10-01)

**Type of document:** factual record, no recommendations, no plan. The mechanic is described from the sources; the app is described from the code; the two are compared; the questions a plan would have to answer are listed. Nothing here decides them.

**Sources, and the role each plays.** Where they disagree, the export wins for values, the live corpus wins for meaning, and the deprecated PDFs are evidence of one Trainer's record-keeping, not of the mechanic.

| Tag      | Source                                                                                                                                                                                                                           | Role                                                                                                                                                                                                                           |
| -------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `[B]`    | `database/seeders/data/support-cards.88dea522.json` (559 records) and `support_effects.ca447e53.json` (35 records), the GameTora export pulled 2026-09-27, both git-tracked                                                      | The game's own data. Authoritative for values                                                                                                                                                                                  |
| `[R]`    | `docs/UMAMUSUME_REFERENCE.md` §1.4 (`:417-579`), the live corpus                                                                                                                                                                 | Authoritative for what the mechanic means                                                                                                                                                                                      |
| `[P]`    | `docs/deprecated/Umamusume Progress Tracker.pdf`, card list pages and friend-card list (extracted 2026-10-01 with xpdf `pdftotext` to a temp dir outside the repo; the PDF was not modified)                                     | Historical record of one Trainer's card notes, Game8/Sportskeeda-derived, vintage unstated per card                                                                                                                            |
| `[PB]`   | `docs/deprecated/Uma Musume Career Tracker System Documentation.pdf`                                                                                                                                                             | Searched in full: **zero support-card content**. Its data model is trainee/run/stat/skill only, and every "card" hit is UI-layout vocabulary ("card-based layouts", `x-skill-card`). It contributes nothing to this mechanic   |
| `[A]`    | `docs/adr/0005-support-card-entities.md`, `docs/adr/0014-support-card-entities.md`                                                                                                                                               | What the app is allowed to model. ADR-0014 is the authority                                                                                                                                                                    |
| `[S]`    | `database/migrations/2026_09_30_142618_create_support_cards_and_support_effects_tables.php`, `2026_09_30_151945_correct_support_card_schema_and_constraints.php`, `app/Models/`, `app/Actions/`, `app/Http/`, `config/uma.php`   | What the app actually holds and reads                                                                                                                                                                                          |

**Method.** Every `[P]` claim about a card's effects was checked against `[B]` before it stands: 19 card records were compared, per effect, against the export's anchor vectors (levels 1, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50; cap = last anchor that is not `-1`). The comparison script ran against the tracked seed body, so the figures below are reproducible. Tier labels are noted where the sources carry them and are not tabulated, per ADR-0014's hold (`docs/adr/0014-support-card-entities.md:147-158`). A fresh-context reviewer was not spawned for this pass (dispatched-agent context); the doubt step was performed as direct cross-checks of every deprecated claim against the export.

---

#### 1. What a support card is, as a mechanic

A support card is a training companion equipped before a career run, not during it. A deck holds six slots; two copies of the same card cannot be equipped together `[R]` `:477`. Six is also the friend-labelled position, and the label belongs to the slot: any card may occupy it `[R]` `:477`, `[A]` ADR-0005 correction 1.

Cards come in seven types: speed, stamina, power, guts, intelligence (which the client calls Wit), friend (Pal) and group. Group is a type in its own right, not a flag `[R]` `:423-433`. Pal cards differ in effect profile, not just flavour: none of the 11 JP SSR friend cards carries Friendship Bonus (id 1) while all 296 non-friend, non-group SSR cards do `[R]` `:437`. Rarity is R, SR, SSR, exported as 1, 2, 3 `[R]` `:445`.

What a card does, per the corpus:

- **At career start.** Initial-stat effects (ids 9 to 13) and Initial Friendship Gauge (id 14) apply once, when the run begins `[R]` `:548`.
- **During training.** A card raises the yield of the training sessions it appears in: stat bonuses (ids 3 to 7), Training Effectiveness (8), Mood Effect (2), Friendship Bonus (1), Failure Protection (27), Energy Cost Reduction (28), Skill Point Bonus (30) `[R]` `:549-554`. Placement at a facility is governed by Specialty Priority (19) `[R]` `:459,551`.
- **Friendship training.** Each card carries a friendship gauge, 0 to 100, which fills as the card is used and turns orange at 80. Past 80, the card can trigger friendship training at the facility of its own type. The training formula multiplies its terms, and two qualifying cards' Friendship Bonuses multiply rather than add (1.25 x 1.30 = 1.625) `[R]` `:457`.
- **Card events.** Each card owns an event chain (135 per-character, 11 Pal, 5 group chains in the export) with its own effects: Event Recovery (25), Event Effectiveness (26) `[R]` `:465,553`.
- **Skill hints.** A card's `hints` block names a hinted skill pool (`hint_skills`) and modifiers (`hint_others`). Hint Frequency (18) raises how often hint events occur; Hint Levels (17) raises the level granted. Hint level discounts the hinted skill's point cost by 10% per level, to 30% at level 3 `[R]` `:465-467`.
- **Race days.** Race Bonus (15) and Fan Bonus (16) apply per race `[R]` `:552`.
- **Level.** A card's effect count and values grow with its card level: the export stores each effect as anchors at levels 1, 5, 10 ... 50, and any intermediate value is the floor of the line between the bracketing anchors `[R]` `:506-522`. Level caps are 30/25/20 unbroken, +5 per break `[R]` `:449`.
- **Unique Perk.** A second, independent axis: the client's card panel prints a `Unique Perk` heading with two effect names and its own level, and the export does not carry the perk's values at all `[R]` `:524-526`.
- **Scenario Link.** A deck-editor badge shown when the card's character is on the running scenario's linked list. It is a join, not a card property `[R]` `:530`.

The deprecated PDF adds one mechanic the corpus's §1.4 does not describe: its friend-card table is organised around a **"Unique Value When Borrowed"** column, presuming that slot 6 can hold a borrowed card. The corpus covers rental only for Legacy ancestors (§1.5.4), not support cards. The mechanic is plausible and unverified in this repo's sources; nothing here asserts it.

---

#### 2. The effect columns the docs describe

**A correction to the framing first.** The dispatch named "the seven effect columns" and listed eight names. The PDF has no such columns: its main list is one row per card with four text columns, Card Name, Rarity+Type, Unique Effect, Support Effects, Event Skills & Hints, Notes (`[P]` pages 1 to 9; corroborated by `docs/deprecated/REVIEW-2026-09-30.md` §3C), and its friend table's columns are Card Name, Rarity+Type, Unique Value When Borrowed, Typical Use Cases / Notes, Tier (`[P]` page 10). The eight names in the dispatch are **effect names inside the prose "Support Effects" column**, not columns. The live structure is the export's: a 35-row effect dictionary, and per card a list of `[effect_id, v1 ... v11]` anchor vectors `[B]` `support_effects.json`, `support-cards.json`; `[R]` `:506,542`.

The eight named effects, each with its mechanic, its static-or-dynamic reading, and who carries it:

| Effect (dictionary id)                              | Mechanic                                                                                                                                                                                 | Static or dynamic                                                                                                                  | Carried by                                                                     |
| --------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------ |
| Friendship Bonus (1)                                | Multiplies stat gain when the card joins a friendship training session; qualifying cards multiply together `[R]` `:457`                                                                  | Fixed value per card level (`calc: mult`); applied repeatedly; the gauge that gates it accumulates, the bonus does not `[B]`       | All three                                                                      |
| Mood Effect (2)                                     | Amplifies the mood term when training together `[B]` id 2                                                                                                                                | Fixed value; the mood it scales with changes turn to turn, so the output varies `[R]` `:549`                                       | All three                                                                      |
| Hint Frequency (18)                                 | Raises the probability that hint events occur `[B]` id 18                                                                                                                                | Fixed percent; shifts a per-turn probability. No accumulation of its own; the hint *levels* it feeds accumulate `[R]` `:465-467`   | All three                                                                      |
| Specialty Priority (19)                             | Raises how often the card is placed at its preferred facility (`calc: add`) `[B]` id 19                                                                                                  | Fixed value; shifts a per-turn placement probability `[R]` `:459,551`                                                              | All three                                                                      |
| Initial Friendship (14, Initial Friendship Gauge)   | Adds to the friendship gauge when the run starts `[B]` id 14                                                                                                                             | One-shot at career start; static. On 305 of 312 JP SSR stat cards `[R]` `:548`                                                     | All three                                                                      |
| Race Bonus (15)                                     | Raises stat gain from race results `[B]` id 15                                                                                                                                           | Fixed percent, applied per race day `[R]` `:552`                                                                                   | All three                                                                      |
| Fan Bonus (16)                                      | Raises fan gain from race results `[B]` id 16                                                                                                                                            | Fixed percent, applied per race day `[R]` `:552`                                                                                   | All three                                                                      |
| "Unique Effect"                                     | Not a levelled effect. The PDF's column names the Unique Perk's two effects, sometimes with values; the export's `effects` arrays do not carry perk effects or values `[R]` `:524-526`   | Separate level axis of its own; nothing in this repo states what raises it or what its values are `[R]` `:526`                     | PDF only, for values. The corpus names the axis and proves the export silent   |

**The full dictionary.** 35 records, ids 1 to 33, 41 and 9991 `[B]`. Grouped by when they pay (grouping and coverage counts cited from `[R]` `:546-556`, which counted the 312 JP SSR records):

| When it pays                          | Effects (id)                                                                                                                                        | `calc`         |
| ------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------- | -------------- |
| At career start                       | Initial Speed (9), Stamina (10), Power (11), Guts (12), Wit (13), Initial Friendship Gauge (14)                                                     | flat           |
| Every shared training, multiplier     | Friendship Bonus (1)                                                                                                                                | `mult`         |
| Every shared training, flat percent   | Mood Effect (2), Training Effectiveness (8)                                                                                                         | flat           |
| Specific discipline's gain            | Speed (3), Stamina (4), Power (5), Guts (6), Wit (7) Bonus, Skill Point Bonus (30)                                                                  | flat           |
| Card's own tile presence              | Specialty Priority (19)                                                                                                                             | `add`          |
| Race days                             | Race Bonus (15), Fan Bonus (16)                                                                                                                     | flat percent   |
| Card's own events                     | Event Recovery (25), Event Effectiveness (26)                                                                                                       | flat percent   |
| Failure and Energy spend              | Failure Protection (27) `mult`, Energy Cost Reduction (28) `mult`, Wit Friendship Recovery (31)                                                     | mixed          |
| Hint output                           | Hint Levels (17) (`symbol: level`), Hint Frequency (18)                                                                                             | flat           |
| Carried by **zero** of 559 cards      | Max Speed to Max Wit (20-24, the export marks them `inactive`), Minigame Effectiveness (29, `inactive`), Hint Quantity Bonus (33), id 41, id 9991   | none           |

Three load-bearing dictionary facts, from `[R]` `:560-575`: only four effects declare `calc` and they are exactly the four that combine multiplicatively; the five "Max <stat>" effects exist in the dictionary and no card carries them, so no shipped card raises a stat ceiling; and ids 32, 33, 41 and 9991 have missing or sentence-form names, so any UI rendering effect names from the dictionary needs a visible unverified marker for those.

---

#### 3. What the app models

Three tables. `2026_09_30_142618` created them; `2026_09_30_151945` rebuilt all three in raw SQL and is the shape in force. Line references are to the final migration unless noted.

**`support_cards`** (`2026_09_30_151945:219-245`). 18 columns.

| Column                    | Type                        | Source field                                           | Reader                                                                                                                                         |
| ------------------------- | --------------------------- | ------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------- |
| id                        | autoincrement               | none                                                   | surrogate                                                                                                                                      |
| support_id                | int, unique                 | `support_id`                                           | upsert key `StoreSupportCards:103`; API order `SupportCardController:26`; display fallback `SupportCard:87`                                    |
| char_id                   | int, nullable, **no FK**    | `char_id` verbatim, including the 9000 block           | API emission only (`SupportCardResource`); no query or view logic reads it                                                                     |
| char_name                 | varchar, nullable           | `char_name`                                            | `displayName():87`, `isScenarioLink():146`, deck panel sort `deck-panel.blade.php:17`                                                          |
| name_ja                   | varchar, nullable           | `name_jp`                                              | display fallback `SupportCard:87`                                                                                                              |
| title_en                  | varchar, nullable           | `title_en`                                             | `displayName():89`, panel sort                                                                                                                 |
| title_ja                  | varchar, nullable           | `title_ja`                                             | API emission only                                                                                                                              |
| rarity                    | int, CHECK 1/2/3            | `rarity`                                               | `rarityWord():98-105` in the panel; API                                                                                                        |
| type                      | varchar, CHECK 7 keys       | `type`                                                 | `typeLabel():111-118`, panel sort, `SupportCard::TYPES:57` shared with parser and CHECK                                                        |
| release_jp                | date, nullable              | `release`                                              | consumed by the `release_status` generated column; API                                                                                         |
| release_global            | date, nullable              | `release_en`                                           | deck picker filter `TrainingRunController:307` (`whereNotNull`); generated column; API                                                         |
| release_status            | generated, stored           | derived                                                | API emission only                                                                                                                              |
| effects                   | text (JSON), default `[]`   | `effects` anchor vectors, verbatim, `-1` = no anchor   | written by the parser (`GametoraSupportCardParser:159-183`), emitted raw by the API; **no view, query, or engine computes anything from it**   |
| source_url                | varchar                     | pinned URL                                             | provenance; API emission only                                                                                                                  |
| fetched_at                | datetime                    | fetch time                                             | provenance; API emission only                                                                                                                  |
| is_manual                 | bool, default false         | none (Trainer corrections)                             | the fetch engine's stop sign `StoreSupportCards:63-66` (FR-B-4)                                                                                |
| created_at / updated_at   | timestamps                  | none                                                   | framework                                                                                                                                      |

Model: `app/Models/SupportCard.php`. Casts `effects` to array and `rarity` to `CardRarity`. View-boundary vocabulary: `displayName()`, `rarityWord()` (R/SR/SSR), `typeLabel()` (Wit, Pal), `isScenarioLink($scenarioKey)` (derived against `config/scenarios.php`, never stored, per ADR-0014:160-165).

**`support_effects`** (`2026_09_30_151945:191-203`). 9 columns: id, effect_id (unique), name_en (NOT NULL, falls back to `name_en_eon`), name_ja, calc (CHECK `mult`/`add` or null, null on 31 of 35 records), symbol (`percent`/`none`/`level` or absent), description_en, source_url, fetched_at. Written by `StoreSupportEffects`. **No runtime code reads this table**: no controller, no view, no join, and the API resource for cards does not resolve anchors against it. Model: `app/Models/SupportEffect.php`.

**`deck_slots`** (`2026_09_30_151945:265-277`). 6 columns: id, training_run_id (FK cascade), support_card_id (FK restrict), slot_position (CHECK 1 to 6, unique per run), created_at, updated_at. Every field is read: `StoreDeckRequest:53-83` (key-restricted array, duplicate rejection), `TrainingRunController:735-748` (delete-then-insert in a transaction), `deck-panel.blade.php:10,36-53` (display, Scenario Link badge, position 6 labelled "Friends"), `TrainingRunResource:43-49` (deck on the run API). Model: `app/Models/DeckSlot.php` (saving-hook range guard `:86-95`, `isFriendSlot():97`).

**Import path.** `config/uma.php:278-313` declares `gametora-support-cards` and `gametora-support-effects` (manifest-resolved, pinned fallback, seed files the two tracked JSON bodies). `GametoraSupportCardParser` reads 11 export keys per record and **drops the rest, including `hints` (the skill-hint pool) and `event_skills`**, on the floor that no column holds them. `uma:import:support-cards` imports both from the committed bodies with zero network.

**API.** Read-only: `GET /api/v1/support-cards`, `/{id}` (`SupportCardController:22-42`). Writes go through the web UI only, which keeps `ApiV1ValidationEnvelopeTest`'s pinned premise true (ADR-0014:172-174).

Count: 33 columns across the three tables; 26 substantive after excluding three surrogate ids and four timestamps. 12 have behavioural readers (screen, query, guard, or derivation), 6 are API-emitted with no behavioural reader, 8 (the whole dictionary table) are written and never read.

---

#### 4. The gap

**Modelled and used.** The deck as identity: run, card, position, with duplicate rejection, the Global-release picker filter, the friend-slot label, the Scenario Link badge (derived from `char_name`), and the run API's `deck` array. On `support_cards`: `support_id`, `char_name`, `name_ja`, `title_en`, `rarity`, `type`, `release_global` (picker), `release_jp` (via the generated column), `is_manual` (stop sign).

**Modelled and unused.** The entire `support_effects` table (8 substantive fields, written, never read). On `support_cards`: `char_id`, `title_ja`, `release_status`, `effects`, `source_url`, `fetched_at` reach the API and nothing else. The `effects` anchor vectors are the mechanic's core data and no code computes a single effect value from them.

**Required by the mechanic and not modelled.** Ten things the game does that the schema does not carry:

1. **Levelled effect values.** The floor-interpolation rule (`[R]` `:520-522`) is unimplemented; "what does this card give at level 35" is answerable from stored anchors by hand only.
2. **The effect-name join.** Nothing resolves an anchor's effect id against the dictionary, so a screen cannot print "Friendship Bonus" from data.
3. **Card-to-skill hints.** The export carries each card's `hint_skills` pool and `hint_others` modifiers `[B]`; the parser drops both; `run_skills` records acquired/skipped but no hint provenance or hint level.
4. **Card event chains.** `event_skills` per card and the 151 event chains `[R]` `:465` are not imported; Event Recovery and Event Effectiveness (25, 26) have nothing to attach to.
5. **Friendship gauge state.** The 0-to-100 gauge, the 80 trigger, and per-turn accumulation exist nowhere; `turn_entries` records stats, mood, energy, fans, not card gauges.
6. **Per-run card level and breaks.** The deck records card identity only. Effect values depend on card level (`[R]` `:522`), so a deck without levels cannot reproduce the yield a Trainer saw. Distinct from collection state (cut by decision); this is per-run run-state, which no decision has addressed.
7. **The Unique Perk.** Two effect names and a level per card, values uncarried by any source (`[R]` `:524-526`).
8. **Borrowed-friend status for slot 6.** The `[P]` friend table presumes borrowing; nothing models whether slot 6's card was the Trainer's own.
9. **Scenario Link bonus magnitude.** The badge is derived; what the link grants has no source and no column (`[A]` ADR-0005 re-verification, point 2).
10. **Card tier labels.** Held, not cut: no current Global source (ADR-0014:147-158). The `[P]` main list carries tier words inside its Notes column and the friend table carries a dedicated Tier column (S+/S/A/B); locations noted, values not tabulated here.

Collection tracking (levels, breaks, perk level owned by the Trainer) is also mechanic-shaped but is **excluded by decision** (ADR-0014:22), not missing; the `[P]` "2ND ACC" list of 22 owned card titles is that same feature in the deprecated record.

---

#### 5. Contradictions between sources

One line each; `[P]` versus `[B]` unless named. Verification basis: 19 card records compared effect-by-effect against the export anchors.

1. **Tazuna Hayakawa [Tracen Reception]:** `[P]` claims Initial Power +60, Initial Guts +35, Initial Intelligence +30 as support effects; the export row carries Initial **Speed** (id 9, cap 30), Initial Friendship Gauge (14, cap 30), Training Effectiveness (8, cap 10) and the event/failure effects, and none of the three claimed initial-stat effects exists in the row.
2. **Nice Nature [Messing Around]:** `[P]` claims Hint Lv2, Hint Freq +50%, Specialty +60, Initial Wit +20; the export row carries none of ids 13, 17, 18 or 19.
3. **Eishin Flash [5:00 a.m. Right on Schedule]:** `[P]` claims Training +15% against an export cap of 5, and Race Bonus +5% though the row carries no id 15 at all.
4. **Race Bonus 10 vs 5:** `[P]` claims Race Bonus +10% for both Hishi Amazon [Reach the Top!] and Air Groove [Nothing Escapes the Vice Prez]; the export caps both at 5.
5. **Perk-pattern strays:** `[P]` values with no export row (Special Week [The Brightest Star in Japan!]: Initial Guts +20, Guts Bonus +1, a second Friendship +35; Mihono Bourbon: Friendship +10%; Fuji Kiseki: Initial Wit +20; Gold City [Run(my)way]: Initial Speed +20). `[R]` `:524-526` reads these shapes as Unique Perk effects the export does not carry, so they are either perk values (unverifiable here) or transcription errors; `[P]` does not say which.
6. **Level of the `[P]` figures is unstated, and mixed:** El Condor Pasa's full set matches level-50 caps, while Matikanetannhauser's Initial Guts +25 is the level-30 anchor and its Race/Fan figures are the level-45/50 caps. The same document's numbers are therefore not one snapshot at any level.
7. **Title drift:** `[P]` "Reach to the Top!" vs export "[Reach the Top!]", "Trainer's Teamwork" vs "[Trainers' Teamwork]", "The Brightest Star in Japan" vs "[The Brightest Star in Japan!]", and the Winning Ticket entry interleaves a stray "Haru Urara [Urara's Day Off!]" header above it. Two lookups failed on exact spelling before correction.
8. **Borrow mechanic:** `[P]`'s friend table presumes slot 6 holds a borrowed card; `[R]` §1.4 does not describe support-card borrowing (its rental coverage is Legacy ancestors, §1.5.4). PDF-only claim.

Corroboration worth stating: Winning Ticket's `[P]` entry is written as level-30-to-cap **ranges**, and every range endpoint matches the export; Daiwa Scarlet, Matikanefukukitaru, Seiun Sky and El Condor Pasa match value-for-value at cap. The `[P]` card list is a usable historical record where it stays levelled; its "Unique Effect" column and initial-stat claims are where it stops agreeing with the game's data.

---

#### 6. Decisions a plan would need

Not the plan's content; the questions. Each names the side of the gap it answers.

1. **What is a support card for in this app beyond being recorded on a run?** (app side, scope) Catalogue browsing, deck-effect preview, or an input to run math, are three different slices. Every other question below is cheaper once this is answered, and it is the one the plan cannot start without.
2. **Does the app ever compute an effect value, or only store anchors?** (app side) If compute: where the floor rule lives, and the truncation-not-rounding test it needs (`[R]` `:520-522`).
3. **Does any screen join the dictionary?** (app side) If yes, ids 32/33/41/9991 need the visible unverified-marker treatment (`[R]` `:572-575`).
4. **Does the deck gain per-run level and break count?** (mechanic-app boundary) Mechanic: effect values depend on card level. App: ADR-0014 kept *collection* out; per-run state is a third thing neither reference data nor collection, and needs its own ruling.
5. **Is the friendship gauge ever tracked per turn?** (mechanic side) Would touch `turn_entries` or `deck_slots`; the 80-threshold trigger is the part run math cannot explain without it.
6. **Are card hints imported and does the card-to-skill join get built?** (mechanic side, data exists) `hints.hint_skills` is on disk and currently dropped at the parser.
7. **Does slot 6 record borrowed versus owned?** (mechanic side, source gap) Needs a source before it needs a column; `[P]` alone is not one.
8. **Do tier labels ship if a current source appears?** (app side) Held by source availability per ADR-0014:147-158, not by R75.
9. **Is the Scenario Link bonus magnitude pursued?** (mechanic side) Needs a new source; the badge stays presence-only otherwise.
10. **Does the collection half ever ship?** (app side, explicitly cut for now) ADR-0014:22 leaves it out; the `[P]` second-account list shows the Trainer kept one.
11. **Does the read-only API premise survive any of this?** (app side) A computed or joined card payload is still a read; a deck write endpoint is not, per the envelope test pin (ADR-0014:172-174).

---

**Scope note.** The mechanic is broader than the deprecated documents suggested: `[P]` describes eight effect names per card in prose, while the game's own dictionary carries 35 effects, a per-card hint pool, event chains, and a second (perk) value axis the export does not even carry. This record covers what the named sources hold and stops there; the group-card membership set, the JP post-2025 stage model (`[R]` `:451`), and deck-building heuristics (type spread, the four-type rule, `[R]` `:479-481`) are noted where met and not developed.

---

## support-cards-module-plan-2026-10-01.md

### Support card module, sourced development plan (2026-10-01)

**Status: DRAFT.** Committed at `c811b5e` for reading; the commit does not promote it out of draft, and §7 below is uncommitted. Goes to the owner before any development slice opens.

This file replaced an earlier ten-section draft at the same path (restructured 2026-10-01). The draft's content is preserved in the sections below; nothing was dropped.

**Role:** game-mechanics agent. This is a sourced plan, not an implementation and not an ADR.
**Base:** `docs/design-research/support-cards-mechanics-2026-10-01.md` (the record), read once.
**Fence held:** one file written, this one. No view, controller, model, migration, request, route, config, test, or ADR was edited. No commit, no push, no migration, no test run, no network fetch, and the shared database was not opened.
**Verified against:** HEAD `4643eb7` at close. HEAD was `20364ae` when the first pass over this material opened; the plan's citations are line-anchored and were re-confirmed after the move.
**PLAN.md checked:** no support-card or deck slice is scheduled there. `grep -i 'support.card|deck' PLAN.md` returns nothing across all 644 lines.

---

#### 1. Method

**Sources consulted, by tier.** Tier numbers are the dispatch's. Read the highest tier that answers; lower tiers were descended to only where the higher one was silent.

| Tier            | Source                                                                                                                                                               | Locator                                                                                                                         | Date                                                                   |
| --------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------- |
| 1               | GameTora export, the game's own data                                                                                                                                 | `database/seeders/data/support-cards.88dea522.json` (559 records), `support_effects.ca447e53.json` (35 records), both tracked   | pulled 2026-09-27                                                      |
| 1               | Client frames (deck editor, six card detail panels)                                                                                                                  | `docs/game-screenshots/`, as recorded in the corpus                                                                             | captured 2026-07-15                                                    |
| 2               | GameTora card page, read at three selector levels                                                                                                                    | `gametora.com/umamusume/supports/30003-tokai-teio`                                                                              | read 2026-09-27                                                        |
| 2               | Game8 tier list and friendship/hint guides                                                                                                                           | as cited in `UMAMUSUME_REFERENCE.md` §1.4                                                                                       | 2026-09-23 and later                                                   |
| 2               | GameWith ranking and deck guide                                                                                                                                      | as cited in §1.4                                                                                                                | 2026-09-26 and earlier                                                 |
| 3               | Cygames Global news (uncap crystals, Books of Hints)                                                                                                                 | `umamusume.com/news/1064/`                                                                                                      | 2026-09-23                                                             |
| 4               | Deprecated PDFs                                                                                                                                                      | `docs/deprecated/`                                                                                                              | not used (no higher tier disagrees with the export on anything here)   |
| Product truth   | `PRD.md` (US-12, FR-A-8, A-9, FR-C-6, §6.9), `docs/adr/0005`, `docs/adr/0014`, `docs/design-research/DESIGN.md` §6.5b and §8.4, `KNOWN-ISSUES.md` KI-43, `PLAN.md`   | repo                                                                                                                            | read 2026-10-01                                                        |

**Tooling.** File reads, `grep`, `ls`, `git ls-files`/`rev-parse`. No network request was made. The database was fingerprinted but not opened, because a peer is writing it (see §4).

**Ponytail discipline applied.** The record was read once. ADR-0005 and ADR-0014 were each read once. The 35-effect dictionary was not re-derived. The deprecated PDFs were not read. Nothing beyond the highest answering tier was consulted per decision.

---

#### 2. §6-1 answer

**Undecided by source. The plan stops at the gate.**

The question: what is a support card for in this app beyond being recorded on a run? Three candidates exist. The sources exclude one and leave two open, and no source in this repository chooses between the two that remain.

**Candidate 3, run-math input, is excluded by product truth.** `PRD.md:109-110` (FR-A-8) closes with "Reference data only, on A-5's precedent: no card tier, no strength score, no training-yield calculation". `ADR-0014`'s "Not designed here" paragraph names the same exclusion from the other side: "Effect-value computation at runtime (which card was on which tile in which turn), friendship-trigger arithmetic, hint-level accumulation". The app's run math is deterministic over Trainer-entered turn entries, and it holds no per-run card level and no per-turn gauge state to feed a card term, while `PRD.md:37` (US-12) forbids storing a level. A plan step that made cards an input to run math would contradict a written requirement, not merely lack one.

**Candidate 1, catalogue browsing, has no requirement.** No FR names a support-card browse screen. `PRD.md:44` (A-3) scopes the catalogue index to Umamusume names and aliases; `PRD.md:144` (D-2) scopes search to skills. What ships is the deck picker plus a read-only JSON API (`ADR-0014:172-174`). `DESIGN.md:1413` (Screen D) is the skill search, not a card surface.

**Candidate 2, deck-effect preview, is constrained but unauthorized and unevaluable.** The rule exists and is verified: `UMAMUSUME_REFERENCE.md:520-522` gives the floor-interpolation rule and states that "the tool can therefore reproduce any `[Global]` effect value from the export alone, provided it stores the anchors and applies the floor rule". `PRD.md:103` (A-8 iii) says an intermediate value "is interpolated on read". `DESIGN.md:739-742` permits a displayed magnitude only with the anchors printed beside it, per D-256. But no requirement authorizes the surface, and there is no level to evaluate at: no source states a card level for a run, and US-12 forbids storing one. The design package's own rail paragraph (`DESIGN.md:690-699`) rules the rail's data source as Trainer-entered and forbids a magnitude, level, rarity or growth rate on a rail chip, and its premise ("`ADR-0005` is still PROPOSED") is stale against ADR-0014.

Candidate 2 has two shapes, and the plan above names only the larger one. **Preview at cap** reads each `support_cards.effects` anchor vector, takes the last anchor that is not `-1`, resolves the effect id against `support_effects`, and prints the value. No stored level, no per-run input, no US-12 collision; the floor-interpolation rule is only needed for intermediate levels, not for cap. The GameTora page shows a level selector (minus 5, minus 1, plus 1, plus 5) with "Unlocked at level N" annotations; its readings were taken at levels 30, 35 and 40, not at cap. The cap-preview is a smaller surface than the GameTora page shows; it is the subset that needs no level input. **Preview at arbitrary level** is the same computation with a Trainer-supplied level input, and that input is where US-12's prohibition applies. The first shape is the smaller change that makes the shipped schema do something; the second is where the level question actually lives.

Per the dispatch, the plan presents all three with their evidence and stops. It does not pick.

Superseded in part by §7. §7 answers §6-1 with tier-1 and tier-2 sources and selects candidate 2 in its per-card cap shape; §7 is the current position. §2's three-candidate framing stands as the record of the state before §7 was written.

---

#### 3. Decision table

The record's §6, eleven decisions.

| #     | Decision                                          | Verdict                  | Source (tier, locator, date)                                                             | One-line reasoning                                                                                                 |
| ----- | ------------------------------------------------- | ------------------------ | ---------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------ |
| 1     | What a card is for beyond the run record          | **unsourced**            | PRD A-8 `:109-110`; ADR-0014 "Not designed here"; DESIGN.md §6.5b                        | Excludes run-math input; does not choose between browsing and preview. Owner's call                                |
| 2     | Compute an effect value, or store anchors only    | **partial**              | PRD A-8 (iii) `:103`; corpus §1.4.7 `:520-522`; DESIGN.md `:739-742`                     | The rule and its display constraint are sourced; the display is not authorized, and no level is held               |
| 3     | Join the dictionary in any screen                 | **partial**              | corpus §1.4.8 `:572-575`; record §3 (dictionary imported, unread)                        | The unverified-marker rule is sourced (D-20); no surface is authorized                                             |
| 4     | Deck gains per-run level and breaks               | **unsourced**            | PRD US-12 `:37`; §6.9 `:172`                                                             | Currently forbidden: "no card level, limit break or Unique Perk state is stored anywhere". Needs a PRD amendment   |
| 5     | Track the friendship gauge per turn               | **unsourced**            | corpus §1.4.3 `:457-459`; ADR-0014 "Not designed here"                                   | The mechanic is described; no source publishes per-turn gauge state, and none is recorded                          |
| 6     | Import card hints; build the card-to-skill join   | **partial**              | Tier 1 export `hints.hint_skills`, `hint_others`, `event_skills`; corpus §1.4.4 `:465`   | The data is on disk and the parser drops it by design; the scope is unsourced                                      |
| 7     | Record borrowed versus owned in slot 6            | **unsourced**            | record §5 item 8                                                                         | No repo source describes support-card borrowing; the claim is deprecated-PDF-only and uncorroborated               |
| 8     | Ship tier labels if a current source appears      | **partial**              | PRD §6.9 `:172`; ADR-0014 `:147-158`                                                     | Held for want of a current Global source, not for want of authorization; the source is missing                     |
| 9     | Pursue the Scenario Link bonus magnitude          | **unsourced**            | ADR-0005 re-verification, point 2                                                        | No source publishes link bonus magnitudes                                                                          |
| 10    | Ship the collection half                          | **sourced** (negative)   | PRD §6.9 `:172`; ADR-0014 `:22`                                                          | Cut, and no user story replaced it                                                                                 |
| 11    | Does the read-only API premise survive            | **sourced**              | ADR-0014 `:172-174`; `ApiV1ValidationEnvelopeTest` pin                                   | A computed or joined read payload is still a read; a deck write endpoint would break the pin                       |

Counts: 2 sourced, 4 partial, 5 unsourced.

---

#### 4. Plan

**Scope as shipped.** The module is reference data plus a run's deck. The reference half is `support_cards` (559 rows) and `support_effects` (the 35-row dictionary), engine-owned, provenance-bearing, `is_manual`-protected (PRD FR-A-8, ADR-0014). The run half is `deck_slots`: the six cards a Trainer equipped, position-keyed, friend-labelled at six, duplicates refused, Scenario Link derived on read (PRD US-12, FR-C-6, A-9). A read-only JSON API exposes the catalogue (`ADR-0014:172-174`). That is the whole of the authorized module, and it is shipped.

**Phase count: zero implementation phases.** Every candidate below is blocked by §6-1 or by a missing source, and no support-card slice is scheduled in `PLAN.md`. What exists is prerequisite work, each item sourced so the owner can order it.

| #     | Prerequisite                                                            | Closes                                          | Owner                         | Source that justifies it                                                                                                          |
| ----- | ----------------------------------------------------------------------- | ----------------------------------------------- | ----------------------------- | --------------------------------------------------------------------------------------------------------------------------------- |
| P-1   | The owner's §6-1 ruling                                                 | Everything. No phase can be ordered before it   | Owner                         | The dispatch's gate; §2 above                                                                                                     |
| P-2   | Correct `DESIGN.md` §6.5b's rail ruling (`:690-699`) against ADR-0014   | Only if the answer is preview or the rail       | Docs Writer, owner approval   | The paragraph's premise ("`ADR-0005` is still PROPOSED") is stale, and its "never fetched" ruling contradicts the shipped table   |
| P-3   | A PRD amendment if a stored per-run level is wanted                     | Decision 4                                      | Owner, Architect              | US-12's acceptance text currently forbids storing one                                                                             |
| P-4   | KI-43's deck-panel weight fix, an independent in-module defect          | Nothing in this plan                            | Separate slice                | `KNOWN-ISSUES.md` KI-43, filed 2026-10-01, open                                                                                   |

**Cost of each candidate, as evidence for the owner's choice.** Each need cites its source; none is a plan step.

| Candidate             | What it would need                                                                                                                                                                                                                                                                                                                                                            | Schema change                                                                         | Blocked by                                                                               |
| --------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------- |
| Catalogue browsing    | A route and screen; either the shipped read-only API or a new read controller; a search surface modelled on Screen D (`DESIGN.md:1413`)                                                                                                                                                                                                                                       | None                                                                                  | No PRD requirement (`PRD.md:44` scopes the index to Umamusume)                           |
| Deck-effect preview   | An effect-value service applying the floor rule (`corpus §1.4.7:520-522`, PRD A-8 iii); a dictionary join (`support_effects`, imported and unread); an unverified-marker rule for ids 32, 33, 41, 9991 (`corpus §1.4.8:572-575`); a level input the app does not store and cannot derive (US-12); a display slot with anchors shown beside the figure (`DESIGN.md:739-742`)   | None if the level stays transient; a column if it is persisted, which US-12 forbids   | No requirement, and no level source                                                      |
| Run-math input        | Per-run card level and breaks; per-turn gauge state; a training-yield computation                                                                                                                                                                                                                                                                                             | Yes, several                                                                          | PRD A-8 forbids the yield calculation; US-12 forbids the stored level; no gauge source   |

One constraint binds all three: the API is read-only, and `ApiV1ValidationEnvelopeTest` pins that the 422 branch is unreachable because the API has no write (`ADR-0014:172-174`). A candidate needing a write endpoint is a scope change to name, not to assume.

**If the owner answers "no further work", the plan is complete and empty.** The module ships as reference data plus the deck record, and the only open in-module item is KI-43, which is its own slice.

**Risk and pivot points.**

- §6-1 answered "run-math input": collides with A-8's "no training-yield calculation" and needs a PRD amendment; the plan would be rewritten, not extended.
- §6-1 answered "preview": the level input is the pivot. Persisting a level collides with US-12's "stored anywhere"; a transient per-view level avoids that but adds a control with no source behind its value.
- Two readings of `DESIGN.md` §6.5b: the rail paragraph (`:690-699`) can be read as forbidding any catalog-driven support surface, or as a stale ruling overtaken by ADR-0014. P-2 settles which.
- KI-43 compounds: the deck panel is already 80 to 89 percent of the run page's markup, so any candidate adding a picker or a preview worsens it unless the picker is reworked first.
- Shared database churn: a peer rebuilt `database/database.sqlite` before and during this pass (0 bytes at 2026-10-01 19:54, 438272 bytes at 21:32). Schema claims here come from the migration files, not the database, so they hold; any future schema proposal must be rebased on the peer's migrations, and the consolidation records `support_cards = 0` on the shared file at its last read.

**What this plan does not do.** No code, view, controller, model, migration, request, route, or config is written or edited. No test is authored. No ADR is written; P-3 names the amendment a future slice would need. No fetch is made, and no source outside §1 is used. No phase is authorized: the plan describes the work and its blockers, it does not open the work.

---

#### 5. Out of scope, one line each

- Effect-value computation and display (decision 2): blocked on §6-1 and on a level source.
- A dictionary join on any screen (decision 3): blocked on §6-1.
- Per-run card level and breaks (decision 4): forbidden by US-12 today.
- Per-turn friendship-gauge tracking (decision 5): no data source.
- Card hints and the card-to-skill join (decision 6): data available, scope unauthorized.
- Borrowed-versus-owned in slot 6 (decision 7): no source in the repo.
- Tier labels (decision 8): held for want of a current Global source.
- Scenario Link bonus magnitude (decision 9): no source publishes it.
- The collection half (decision 10): cut by §6.9 and ADR-0014.
- Card event chains and `event_skills` (record §4 item 4): same shape as decision 6.
- The Unique Perk axis (record §4 item 7): values are in no source here.
- Card tier data from the deprecated PDFs (record §5): tier 4, not tabulated, not used.
- KI-43, the deck panel's markup weight: a real open defect, deferred to its own slice.

---

#### 6. Source gaps

| Open item                                           | Source type that would close it                                                      | Where it would come from                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      |
| --------------------------------------------------- | ------------------------------------------------------------------------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| §6-1, the module's purpose                          | An owner ruling, not a fetch                                                         | The owner. No repository source answers it                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    |
| Per-run card level and breaks                       | An owner ruling plus a PRD amendment                                                 | The owner and Architect                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       |
| Borrowed-versus-owned                               | A client capture, or a maintained third-party reference describing the friend slot   | Not obtainable from this repository today; the only claim on disk is the deprecated PDF                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       |
| Unique Perk values                                  | A Game8 card page or an in-client capture                                            | Not in the export (`corpus §1.4.7:524-526`); not obtainable offline                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           |
| Unique Perk values, upgrade costs, stat-gain line   | Tier 2, GameTora per-card page                                                       | `gametora.com/umamusume/supports/{support_id}-{slug}`, keyed on `support_cards.support_id`. The export publishes `url_name` (e.g. `10001-special-week`); it is **not** stored: `grep url_name` returns zero across `GametoraSupportCardParser.php`, `SupportCard.php`, `SupportCardResource.php`, and the final migration, which has no slug column. A per-card link is therefore constructible from the export body today, and a later slice wanting it in the UI would need `url_name` stored or read at import. Verified against page 30028 (Kitasan Black), 2026-10-01.   |
| Card tier labels                                    | A current Global tier source                                                         | Game8's list is 2026-09-23 and MLB-scored, GameWith is JP, uma.guide unconfirmed (`ADR-0014:147-158`)                                                                                                                                                                                                                                                                                                                                                                                                                                                                         |
| Scenario Link bonus magnitude                       | Any source publishing link bonus magnitudes                                          | None found in any pass to date                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                |
| Per-turn gauge state                                | A client capture, or Trainer entry                                                   | No source publishes gauge trajectories                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        |
| Client-surface claims                               | The frames themselves, or a re-run of the measurements they supported                | The frames did not render in this pass; the claims rest on `DESIGN.md:725-729` and `UMAMUSUME_REFERENCE.md:524-528`, both of which measured the frames. Closing this needs the frames viewable or their measurements re-verified.                                                                                                                                                                                                                                                                                                                                             |

No fetch is proposed here, and none was made.

---

#### 7. Sourced slice brief

This section refines §2's candidate 2 by sourcing it against the game's own surfaces. It does not rewrite §2; §2's "unauthorized" reading stands until the owner rules. §4's "zero implementation phases" stands too: the slice below opens only on the owner's §6-1 ruling.

§7 answers §6-1; §2's "unsourced" verdict predates this section.

##### 7.1 Task 1 answer: per-card effect preview is the candidate the game's own surfaces support

The client shows a card's effects **per card**, on a card detail panel, at the card's own level, and it shows **composition counts** for a deck rather than any effect aggregate. So candidate 2 is the one the game's behaviour supports, refined to its per-card shape; a deck-total surface is not game-supported, candidate 1 has no tier-1 support and no requirement, and candidate 3 is excluded by PRD A-8. Because the client's panel reads the card's own level and the app holds no level (US-12 forbids storing one), the shape the app's data permits is the level-free one: each effect's value at the highest anchor the source publishes.

| Question                                                   | What the client does                                                                                                                                                                                                                                                                                                | Source (tier, locator, date)                                                                                                                                                                                        |
| ---------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Fixed level or picked level?                               | The card detail panel shows the card at its own level (`Lvl N / MAX`, `0 SP to next level`) with the Unique Perk on its own level badge. The Trainer does not pick. A reference site does pick: GameTora's per-card page has a level selector (minus 5, minus 1, plus 1, plus 5) and prints "Unlocked at level N"   | Tier 1: `DESIGN.md:725-729` (measured from frames `155215` to `155337`, captured 2026-07-15); tier 1: `UMAMUSUME_REFERENCE.md:524-528`; tier 2: GameTora page, read 2026-09-27 (`UMAMUSUME_REFERENCE.md:508-518`)   |
| Deck totals or per card?                                   | Per card. The deck screen carries per-slot corners (rarity, type, four break diamonds, `Lvl N`) and a type legend row whose `xN` counts appear only when non-zero. No effect aggregate anywhere                                                                                                                     | Tier 1: `DESIGN.md:701-723` (measured from frame `Screenshot 2026-07-15 155016.png`)                                                                                                                                |
| What the deck adds during a run, or only static effects?   | During a run the client shows per-card live state on the training HUD rail (bond gauge, an orange chevron when friendship training is available, a flame on the card's own tile), not effect values                                                                                                                 | Tier 1: `DESIGN.md:688`                                                                                                                                                                                             |
| A surface the app's deck panel mirrors?                    | The app's six-slot panel mirrors the client's deck editor. The client's per-card surface is the card detail panel, and the app has no card detail surface                                                                                                                                                           | Tier 1: `DESIGN.md:701-714`; `resources/views/components/deck-panel.blade.php`                                                                                                                                      |

Corroboration and one limit. The export (tier 1, pulled 2026-09-27) is the data the preview reads, and its anchor structure is already decoded and verified against the client at three levels on five effects (`UMAMUSUME_REFERENCE.md:506-522`). The limit: the frames themselves were not rendered in this pass, because the image read returned no content, so the two rows above rest on the two repository documents that measured those frames, not on my own viewing of them.

##### 7.2 The slice

**Scope.** On the run screen's Support deck panel, each equipped card gains a line listing its effects at cap: for every anchor row, take the last value that is not `-1`, resolve the effect id against `support_effects`, and print the value with its unit. The line names its basis (the cap), and an effect id with no dictionary row prints a visible unverified marker rather than a blank. Nothing is stored, no level is asked for, and the picker is untouched.

**Files to add or change.**

| File                                              | Change                              | Purpose                                                                                                                                                                    |                                                                                                                                                                                                                                                                                       |
| ------------------------------------------------- | ----------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `app/Services/SupportCardEffects.php`             | add                                 | `atCap(SupportCard $card, array $names): list<array{id: int, name: string                                                                                                  | null, value: int, level: int}>`. Static, matching`app/Services/ScenarioCaps.php`'s shape and docblock style. The cap is the last anchor that is not`-1`;`level` is the anchor's level from the fixed ladder (1, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50) so the view can name the basis |
| `app/Http/Controllers/TrainingRunController.php`  | change `showData()`                 | Load the 35-row dictionary once (`SupportEffect::query()->pluck('name_en', 'effect_id')->all()`) and pass it beside `deckCards` (`:307`), so six slots cost no extra query |                                                                                                                                                                                                                                                                                       |
| `resources/views/components/deck-panel.blade.php` | change the equipped list (`:36-53`) | One effect line per equipped card, from the service. The picker (`:59-87`) is not touched, because KI-43 already makes it 80 to 89 percent of the page                     |                                                                                                                                                                                                                                                                                       |
| `tests/Unit/SupportCardEffectsTest.php`           | add                                 | see below                                                                                                                                                                  |                                                                                                                                                                                                                                                                                       |
| `tests/Feature/RunDeckPreviewTest.php`            | add                                 | see below                                                                                                                                                                  |                                                                                                                                                                                                                                                                                       |

**Schema changes.** None. No migration, no column, no index. The slice reads `support_cards.effects` and `support_effects` as they stand.

**Tests.**

- `tests/Unit/SupportCardEffectsTest.php` pins: the cap is the last non-`-1` anchor (fixture Kitasan Black `30028`, whose export anchors resolve to Friendship Bonus 25, Mood Effect 30, Power Bonus 1, Training Effectiveness 10, Initial Friendship Gauge 35, Race Bonus 5, Fan Bonus 15, Hint Levels 2, Hint Frequency 30, Specialty Priority 80); an effect whose anchors are all `-1` is omitted; an anchor id with no dictionary row yields `name: null` so the view can mark it; the returned `level` matches the anchor's position on the ladder.
- `tests/Feature/RunDeckPreviewTest.php` pins: an equipped card renders its effect line on the run screen; the line names its cap basis; a run with no deck renders no effect line (the existing named-absence state is unchanged); a card carrying an id with no dictionary row renders the unverified marker rather than a blank.

**PRD or ADR dependencies.** No ADR: no schema change, so none is needed. One PRD question is a prerequisite for the owner: FR-A-8 already contemplates a Global-facing surface offering the stored rows (`PRD.md:106-108`, "a Global-facing surface offers only rows carrying a Global release date"), which may cover a read-only effect display under "Reference data only"; if it does not, one FR line is owed. Either way the owner's §6-1 ruling (plan §4, P-1) comes first. Neither document is written here.

**What the slice does not do.** Level-preview (needs a Trainer-supplied level; US-12 forbids storing one). Deck-aggregate totals (not game-supported; the client shows composition counts). Catalogue browsing (no requirement; tier-1 support is a collection inventory the app cut). Run-math input (excluded by PRD A-8). The card-to-skill hint join, the friendship gauge, tier labels, the Unique Perk values, and KI-43's picker rework.

**Sources the slice's behaviour rests on.** Tier 1: the GameTora export bodies, tracked, pulled 2026-09-27 (`support-cards.88dea522.json`, `support_effects.ca447e53.json`); the client frames of 2026-07-15 as measured in `DESIGN.md` §6.5b and `UMAMUSUME_REFERENCE.md` §1.4. Tier 2: the GameTora per-card page, read 2026-09-27; Game8's card list, 2026-09-23. The anchor rule and its verification: `UMAMUSUME_REFERENCE.md:506-522`. The display constraints the slice must honour: `DESIGN.md:739-742` (a displayed value names its own rule, D-256) and `UMAMUSUME_REFERENCE.md:572-575` with D-20 (a visible unverified marker rather than an invented label). Cap values are stated anchors, not interpolations, so D-256's "print the anchors it interpolated between" is satisfied by naming the basis rather than by printing two bracketing anchors.

---

##### 7.4 Correction, 2026-10-01, landed by the implementation pass

The §7 slice is built. Three of its own claims did not survive contact with the tree, and each is corrected here rather than by editing the prose above.

1. **The marker rule was aimed at the wrong ids.** §7 says the surface needs an unverified marker "for ids 32, 33, 41, 9991". That is true of the export body and false of the shipped table: `GametoraSupportEffectParser.php:24-30` documents the `name_en` to `name_en_eon` fallback, and the imported dictionary holds `Initial Skill Points Up` (32) and `All Stats Bonus` (41). Measured against the committed bodies, 33, 41 and 9991 are carried by zero cards, 26 effect ids are used by at least one card, and every one of those 26 has a dictionary row, so the orphan count is zero. The marker keys on the absent-row case, which is empty today, and is kept as the defensive branch for a source that later ships an id the dictionary does not name. `UMAMUSUME_REFERENCE.md` §1.4.8's predicted blank cell for the 14 SSR cards carrying id 32 does not occur, and what those cards print is the publisher's alternate English rendering rather than a client `name_en` string. Whether that rendering is acceptable display copy is a ruling §7 did not make and the slice did not need: the row is stored, the column is NOT NULL, and the parser's reason is on the record.
2. **The dictionary load moved.** §7's file table assigns the dictionary load to `TrainingRunController::showData()`. The implementation pass was fenced to four files without the controller, so the load lives in `deck-panel.blade.php` instead. The shipped location is the component; the plan's table is superseded on that point. The N+1 the table was written to avoid is still avoided, because the component loads the dictionary once per render rather than once per slot.
3. **The cap is not usually level 50.** Counting the last anchor that is not `-1` across the 5,114 anchor rows in `support-cards.88dea522.json`: level 45 on 1,724 rows, level 50 on 1,391, level 40 on 957, level 35 on 818, level 30 on 200, the remainder at 25 and below. So the basis label reads `highest stated anchor` and prints no level, because naming one would be wrong for a large share of the figures.

Evidence for the counts: a read of the two committed bodies with `node`, 2026-10-01, no network and no database write. The shipped panel was measured in a browser on a run-unique scratch database before and after: 289,411 to 292,976 bytes of panel, page share 88.6 to 88.8 percent, three equipped cards rendering seven and eight chips each, zero `[Unverified]` markers against the real dictionary, console clean. KI-43 is untouched and still open; the added 3,565 bytes sit on top of a page that was already 88.6 percent deck, and the picker that causes that is what §7 defers.

---

## o8-per-card-state-proposal.md

## Proposal: per-card deck state for runs (O-8 2b(d))

**Status.** Proposal only. Stopped at Dispatch C Stage 1 on a PRD-absence premise. Files moved:
none yet. Schema moved: none yet. Migration target only stated here for the owner's review.

### Problem

A logged run currently records only the six support cards by their identity, on
`deck_slots` (`training_run_id`, `support_card_id`, `slot_position` 1..6). Anything else the client
prints about each card at deck-save time is not captured: card level, the limit-break count, the
bond value, the hint level. `docs/research-scratch/AUDIT-AND-VERIFICATION.md`, section `## UIX-AUDIT-TRAINING-RUNS.md` O-8 names this gap: a run that
survives to be re-read cannot answer "what level was that card at when this run happened".
`docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` §1.4 lists four values per card for the documented
run, and the tool cannot reproduce them.

### Why this proposal exists

`PRD.md` §6.9 partial lift (line 172) cuts the half §6.9 was really about: "no collection: no
`user_support_cards`, no card levels, limit breaks or Unique Perk states, because that is
uma-tracker's abandoned promise and no user story replaced it." US-12 (line 37) repeats the same
body in plainer English: "no card level, limit break or Unique Perk state is stored anywhere
(`ADR-0014`: identity, not collection)." `ADR-0014` records the decision in two places: the
§"Decision" table puts `UserSupportCard: the Trainer's collection: level, breaks, perk level` on
the **no** row with the reason "Collection tracking is still the feature §6.9 cut. The deck
records card identity, not ownership state"; §"Not designed here" lists "hint-level
accumulation" among the off-scope items. The forward plan in `docs/research-scratch/AUDIT-AND-VERIFICATION.md`, section `## UIX-AUDIT-TRAINING-RUNS.md`
backlog item 3 says "Same gate" referring to item 2's note that a schema proposal with a PRD
citation must precede code.

No PRD section authorizes the change. No owner pre-approval is on the record in the ADRs I can
read. ADR-0014's "Same gate" reference in the backlog is the existing reading of what is and is
not in scope. Three things follow from this.

1. The four values proposed here would have to be added through a PRD amendment plus an ADR
   that supersedes ADR-0014's decision table entry, before any migration or model change.
2. The amendment is a deliberate reversal of the partial-lift language, which cut per-card state
   on the grounds that uma-tracker's abandoned PRD promised it without delivering. The ownerturned
   question is "does this tool want the half §6.9 cut, on a per-run basis, today?" It is not a
   technical question.
3. The proposal below is the smallest readable answer to that question, in case the owner wants it.

### Source of the four values

`docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` §1.4 records the four values per card for the run-7
fixture: card level, limit break count, bond value, hint level. These are the values the client
prints at deck-save time in the run header and which a Trainer reads off to log a run. They are
display-only state on the client: the run math does not depend on them. The deck panel in the
tool would render them as a tile if they were captured, so a Trainer reading a logged run sees
the same four numbers a logged run was started with.

### Proposed schema

Four nullable columns on `deck_slots`, alongside the existing three identity columns:

| Column          | Type                     | Default   | Why these                                                                                                            |
| --------------- | ------------------------ | --------- | -------------------------------------------------------------------------------------------------------------------- |
| `card_level`    | unsigned small integer   | null      | in identity range 1..50 across the catalogue; unsigned small avoids the tinyint ceiling                              |
| `limit_break`   | unsigned tiny integer    | null      | 0..4 in the client's ladder; tinyint fits without an unreachable cap                                                 |
| `bond`          | unsigned tiny integer    | null      | 0..10 in the ladder, sometimes higher with bond-up events; tinyint with a controller-side 0..20 check stays honest   |
| `hint_level`    | unsigned tiny integer    | null      | 0..5 on the client's hint ladder; tinyint with a 0..5 controller-side check                                          |

Nullable on every column so an existing row is untouched and a fresh slot fill that leaves the
four blank is just an untrained deck slot. The four values are tied to a `(run, slot_position)`,
not to the card on the catalogue, so the same card in two runs may carry different values.

Naming convention check: the catalogue uses `type`, `rarity`, `char_id` (ADR-0014 correction 1).
The deck slot uses `slot_position` and `support_card_id`. Adding `card_level` keeps the noun
form `card_*` next to the existing `slot_position`. The other three follow with no
abbreviation since the dispatch's column list named them in full. No rename proposed.

### User-facing entry point

The locked-tile view the dispatch's Stage 3 adds to `deck-panel.blade.php` is one place the four
values could ride, with an inline edit disclosure per tile. The other is the existing picker
form: extend the per-slot select so the four small inputs sit beside the chosen card's name.
The picker path stays a `<input type="number">` per value with `min` and `max` constraints
matching the column comment, and accepted only on save. The tile path would be the same four
fields rendered invisibly inside an edit disclosure so the locked view stays a view by default.

Either path satisfies the dispatch's Stage 4 contract. The picker path is the smaller change
because the locked view does not yet exist; the tile path is the cleaner UX once a Trainer has
saved the deck once and is just adjusting values.

### What this proposal is not

- It does **not** model card effects, unique perks, or hint pools: none are captured. ADR-0014
  correction 3 stands: the perk's values are absent from the export and from every source here.
- It does **not** capture friend-training bond arithmetic, hint discounting, or any run-time
  computation. None are captured.
- It does **not** backfill existing rows. Every row that predates this proposal has null on the
  four columns and is left untouched.
- It does **not** import per-card state from any external source. The four values would be
  Trainer-entered only, like the deck itself.
- It does **not** promote hard-coded level, break or hint numbers into copy on any surface. The
  values are stored against a slot; the tile and the picker render what the row holds; nothing
  in `config('uma')` or `config('scenarios.php')` changes.
- It does **not** lift the §6.9 partial-lift language. The partial lift covers the catalogue and
  the deck identity, and that language stays. The proposal only adds per-run state on a single
  table that already exists.

### Alternatives considered

| Alternative                                   | Cost                                                            | Why not                                                                                                                                                                                                                                                                                       |
| --------------------------------------------- | --------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Keep the deck card-only                       | Zero                                                            | The audit's O-8 finding already names the gap, and the run report cannot reproduce the four values without it.                                                                                                                                                                                |
| A new `deck_card_state` table, normalised     | A second table, a foreign key, a join                           | The values belong on `deck_slots` already: a row exists iff the slot is equipped, and the four values are properties of that equipping, not parallel facts. A second table would let a Trainer edit the four values without touching `deck_slots`, but no surface needs that freedom today.   |
| Capture the four as JSON on `deck_slots`      | One column, no migration of the others, the values are nested   | The catalogue is integer-typed for the four values; a JSON column would push type discipline into the application, and no current use case asks for arbitrary keys. The proposal's four integer columns are the same shape the catalogue would carry.                                         |
| OnDeckLoad, ask GameWith for current values   | Runtime fetch on the run page                                   | The four values are per-run, not per-current-state. The run report captures what the deck was *at run start*, and a fetch against today's catalogue will give today's values, not those. The dispatch's audit finding is that the tool cannot reproduce what was true at deck-save time.      |
| Wait for OQ-5 to resolve, then redo           | Zero                                                            | OQ-5 is about skill eligibility, not card run state. The two are independent.                                                                                                                                                                                                                 |

### What the owner rules on

- **Whether the section 6.9 partial-lift's "no collection" clause extends to per-run per-card
  values.** The clause's plain text covers `user_support_cards`, but ADR-0014's reading extended it
  to `level`, `limit_break`, and `unique_perk` because those are collection facts. The proposal
  asks for the opposite reading on a per-run basis: card level at run start is a per-run fact, not
  a collection fact.
- **Whether a user story replaces umamusume-tracker's abandoned promise.** The PRD US-12
  body cites ADR-0014 with the reason "no user story replaced it", and the proposal names the run
  report as that user story. The owner can accept the proposal and amend §6.9 and US-12, decline
  and keep the partial lift as written, or accept only some of the four values.
- **Column conventions.** The proposal's `card_level`, `limit_break`, `bond`, `hint_level` are
  proposed; the owner may prefer `card_lvl`, `lb`, `bond_pts`, `hint_lvl` to match an existing
  convention. None of those names is currently used in `deck_slots` or `support_cards`, so the
  choice is open.

On approval, the owner should land the PRD amendment (`§6.9` partial lift text and
`US-12` body) plus an ADR that supersedes ADR-0014's decision-table entry for `UserSupportCard`
or reads "no UPPER-cut does not extend to per-run per-card values" by amendment. Re-issue
Dispatch C from Stage 2 against that ruling.
