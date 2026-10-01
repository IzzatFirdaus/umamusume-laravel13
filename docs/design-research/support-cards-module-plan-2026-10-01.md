# Support card module, sourced development plan (2026-10-01)

**Status: DRAFT.** Committed at `c811b5e` for reading; the commit does not promote it out of draft, and §7 below is uncommitted. Goes to the owner before any development slice opens.

This file replaced an earlier ten-section draft at the same path (restructured 2026-10-01). The draft's content is preserved in the sections below; nothing was dropped.

**Role:** game-mechanics agent. This is a sourced plan, not an implementation and not an ADR.
**Base:** `docs/design-research/support-cards-mechanics-2026-10-01.md` (the record), read once.
**Fence held:** one file written, this one. No view, controller, model, migration, request, route, config, test, or ADR was edited. No commit, no push, no migration, no test run, no network fetch, and the shared database was not opened.
**Verified against:** HEAD `4643eb7` at close. HEAD was `20364ae` when the first pass over this material opened; the plan's citations are line-anchored and were re-confirmed after the move.
**PLAN.md checked:** no support-card or deck slice is scheduled there. `grep -i 'support.card|deck' PLAN.md` returns nothing across all 644 lines.

---

## 1. Method

**Sources consulted, by tier.** Tier numbers are the dispatch's. Read the highest tier that answers; lower tiers were descended to only where the higher one was silent.

| Tier | Source | Locator | Date |
|---|---|---|---|
| 1 | GameTora export, the game's own data | `database/seeders/data/support-cards.88dea522.json` (559 records), `support_effects.ca447e53.json` (35 records), both tracked | pulled 2026-09-27 |
| 1 | Client frames (deck editor, six card detail panels) | `docs/game-screenshots/`, as recorded in the corpus | captured 2026-07-15 |
| 2 | GameTora card page, read at three selector levels | `gametora.com/umamusume/supports/30003-tokai-teio` | read 2026-09-27 |
| 2 | Game8 tier list and friendship/hint guides | as cited in `UMAMUSUME_REFERENCE.md` §1.4 | 2026-09-23 and later |
| 2 | GameWith ranking and deck guide | as cited in §1.4 | 2026-09-26 and earlier |
| 3 | Cygames Global news (uncap crystals, Books of Hints) | `umamusume.com/news/1064/` | 2026-09-23 |
| 4 | Deprecated PDFs | `docs/deprecated/` | not used (no higher tier disagrees with the export on anything here) |
| Product truth | `PRD.md` (US-12, FR-A-8, A-9, FR-C-6, §6.9), `docs/adr/0005`, `docs/adr/0014`, `docs/design-research/DESIGN.md` §6.5b and §8.4, `KNOWN-ISSUES.md` KI-43, `PLAN.md` | repo | read 2026-10-01 |

**Tooling.** File reads, `grep`, `ls`, `git ls-files`/`rev-parse`. No network request was made. The database was fingerprinted but not opened, because a peer is writing it (see §4).

**Ponytail discipline applied.** The record was read once. ADR-0005 and ADR-0014 were each read once. The 35-effect dictionary was not re-derived. The deprecated PDFs were not read. Nothing beyond the highest answering tier was consulted per decision.

---

## 2. §6-1 answer

**Undecided by source. The plan stops at the gate.**

The question: what is a support card for in this app beyond being recorded on a run? Three candidates exist. The sources exclude one and leave two open, and no source in this repository chooses between the two that remain.

**Candidate 3, run-math input, is excluded by product truth.** `PRD.md:109-110` (FR-A-8) closes with "Reference data only, on A-5's precedent: no card tier, no strength score, no training-yield calculation". `ADR-0014`'s "Not designed here" paragraph names the same exclusion from the other side: "Effect-value computation at runtime (which card was on which tile in which turn), friendship-trigger arithmetic, hint-level accumulation". The app's run math is deterministic over Trainer-entered turn entries, and it holds no per-run card level and no per-turn gauge state to feed a card term, while `PRD.md:37` (US-12) forbids storing a level. A plan step that made cards an input to run math would contradict a written requirement, not merely lack one.

**Candidate 1, catalogue browsing, has no requirement.** No FR names a support-card browse screen. `PRD.md:44` (A-3) scopes the catalogue index to Umamusume names and aliases; `PRD.md:144` (D-2) scopes search to skills. What ships is the deck picker plus a read-only JSON API (`ADR-0014:172-174`). `DESIGN.md:1413` (Screen D) is the skill search, not a card surface.

**Candidate 2, deck-effect preview, is constrained but unauthorized and unevaluable.** The rule exists and is verified: `UMAMUSUME_REFERENCE.md:520-522` gives the floor-interpolation rule and states that "the tool can therefore reproduce any `[Global]` effect value from the export alone, provided it stores the anchors and applies the floor rule". `PRD.md:103` (A-8 iii) says an intermediate value "is interpolated on read". `DESIGN.md:739-742` permits a displayed magnitude only with the anchors printed beside it, per D-256. But no requirement authorizes the surface, and there is no level to evaluate at: no source states a card level for a run, and US-12 forbids storing one. The design package's own rail paragraph (`DESIGN.md:690-699`) rules the rail's data source as Trainer-entered and forbids a magnitude, level, rarity or growth rate on a rail chip, and its premise ("`ADR-0005` is still PROPOSED") is stale against ADR-0014.

Candidate 2 has two shapes, and the plan above names only the larger one. **Preview at cap** reads each `support_cards.effects` anchor vector, takes the last anchor that is not `-1`, resolves the effect id against `support_effects`, and prints the value. No stored level, no per-run input, no US-12 collision; the floor-interpolation rule is only needed for intermediate levels, not for cap. The GameTora page shows a level selector (minus 5, minus 1, plus 1, plus 5) with "Unlocked at level N" annotations; its readings were taken at levels 30, 35 and 40, not at cap. The cap-preview is a smaller surface than the GameTora page shows; it is the subset that needs no level input. **Preview at arbitrary level** is the same computation with a Trainer-supplied level input, and that input is where US-12's prohibition applies. The first shape is the smaller change that makes the shipped schema do something; the second is where the level question actually lives.

Per the dispatch, the plan presents all three with their evidence and stops. It does not pick.

Superseded in part by §7. §7 answers §6-1 with tier-1 and tier-2 sources and selects candidate 2 in its per-card cap shape; §7 is the current position. §2's three-candidate framing stands as the record of the state before §7 was written.

---

## 3. Decision table

The record's §6, eleven decisions.

| # | Decision | Verdict | Source (tier, locator, date) | One-line reasoning |
|---|---|---|---|---|
| 1 | What a card is for beyond the run record | **unsourced** | PRD A-8 `:109-110`; ADR-0014 "Not designed here"; DESIGN.md §6.5b | Excludes run-math input; does not choose between browsing and preview. Owner's call |
| 2 | Compute an effect value, or store anchors only | **partial** | PRD A-8 (iii) `:103`; corpus §1.4.7 `:520-522`; DESIGN.md `:739-742` | The rule and its display constraint are sourced; the display is not authorized, and no level is held |
| 3 | Join the dictionary in any screen | **partial** | corpus §1.4.8 `:572-575`; record §3 (dictionary imported, unread) | The unverified-marker rule is sourced (D-20); no surface is authorized |
| 4 | Deck gains per-run level and breaks | **unsourced** | PRD US-12 `:37`; §6.9 `:172` | Currently forbidden: "no card level, limit break or Unique Perk state is stored anywhere". Needs a PRD amendment |
| 5 | Track the friendship gauge per turn | **unsourced** | corpus §1.4.3 `:457-459`; ADR-0014 "Not designed here" | The mechanic is described; no source publishes per-turn gauge state, and none is recorded |
| 6 | Import card hints; build the card-to-skill join | **partial** | Tier 1 export `hints.hint_skills`, `hint_others`, `event_skills`; corpus §1.4.4 `:465` | The data is on disk and the parser drops it by design; the scope is unsourced |
| 7 | Record borrowed versus owned in slot 6 | **unsourced** | record §5 item 8 | No repo source describes support-card borrowing; the claim is deprecated-PDF-only and uncorroborated |
| 8 | Ship tier labels if a current source appears | **partial** | PRD §6.9 `:172`; ADR-0014 `:147-158` | Held for want of a current Global source, not for want of authorization; the source is missing |
| 9 | Pursue the Scenario Link bonus magnitude | **unsourced** | ADR-0005 re-verification, point 2 | No source publishes link bonus magnitudes |
| 10 | Ship the collection half | **sourced** (negative) | PRD §6.9 `:172`; ADR-0014 `:22` | Cut, and no user story replaced it |
| 11 | Does the read-only API premise survive | **sourced** | ADR-0014 `:172-174`; `ApiV1ValidationEnvelopeTest` pin | A computed or joined read payload is still a read; a deck write endpoint would break the pin |

Counts: 2 sourced, 4 partial, 5 unsourced.

---

## 4. Plan

**Scope as shipped.** The module is reference data plus a run's deck. The reference half is `support_cards` (559 rows) and `support_effects` (the 35-row dictionary), engine-owned, provenance-bearing, `is_manual`-protected (PRD FR-A-8, ADR-0014). The run half is `deck_slots`: the six cards a Trainer equipped, position-keyed, friend-labelled at six, duplicates refused, Scenario Link derived on read (PRD US-12, FR-C-6, A-9). A read-only JSON API exposes the catalogue (`ADR-0014:172-174`). That is the whole of the authorized module, and it is shipped.

**Phase count: zero implementation phases.** Every candidate below is blocked by §6-1 or by a missing source, and no support-card slice is scheduled in `PLAN.md`. What exists is prerequisite work, each item sourced so the owner can order it.

| # | Prerequisite | Closes | Owner | Source that justifies it |
|---|---|---|---|---|
| P-1 | The owner's §6-1 ruling | Everything. No phase can be ordered before it | Owner | The dispatch's gate; §2 above |
| P-2 | Correct `DESIGN.md` §6.5b's rail ruling (`:690-699`) against ADR-0014 | Only if the answer is preview or the rail | Docs Writer, owner approval | The paragraph's premise ("`ADR-0005` is still PROPOSED") is stale, and its "never fetched" ruling contradicts the shipped table |
| P-3 | A PRD amendment if a stored per-run level is wanted | Decision 4 | Owner, Architect | US-12's acceptance text currently forbids storing one |
| P-4 | KI-43's deck-panel weight fix, an independent in-module defect | Nothing in this plan | Separate slice | `KNOWN-ISSUES.md` KI-43, filed 2026-10-01, open |

**Cost of each candidate, as evidence for the owner's choice.** Each need cites its source; none is a plan step.

| Candidate | What it would need | Schema change | Blocked by |
|---|---|---|---|
| Catalogue browsing | A route and screen; either the shipped read-only API or a new read controller; a search surface modelled on Screen D (`DESIGN.md:1413`) | None | No PRD requirement (`PRD.md:44` scopes the index to Umamusume) |
| Deck-effect preview | An effect-value service applying the floor rule (`corpus §1.4.7:520-522`, PRD A-8 iii); a dictionary join (`support_effects`, imported and unread); an unverified-marker rule for ids 32, 33, 41, 9991 (`corpus §1.4.8:572-575`); a level input the app does not store and cannot derive (US-12); a display slot with anchors shown beside the figure (`DESIGN.md:739-742`) | None if the level stays transient; a column if it is persisted, which US-12 forbids | No requirement, and no level source |
| Run-math input | Per-run card level and breaks; per-turn gauge state; a training-yield computation | Yes, several | PRD A-8 forbids the yield calculation; US-12 forbids the stored level; no gauge source |

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

## 5. Out of scope, one line each

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

## 6. Source gaps

| Open item | Source type that would close it | Where it would come from |
|---|---|---|
| §6-1, the module's purpose | An owner ruling, not a fetch | The owner. No repository source answers it |
| Per-run card level and breaks | An owner ruling plus a PRD amendment | The owner and Architect |
| Borrowed-versus-owned | A client capture, or a maintained third-party reference describing the friend slot | Not obtainable from this repository today; the only claim on disk is the deprecated PDF |
| Unique Perk values | A Game8 card page or an in-client capture | Not in the export (`corpus §1.4.7:524-526`); not obtainable offline |
| Unique Perk values, upgrade costs, stat-gain line | Tier 2, GameTora per-card page | `gametora.com/umamusume/supports/{support_id}-{slug}`, keyed on `support_cards.support_id`. The export publishes `url_name` (e.g. `10001-special-week`); it is **not** stored: `grep url_name` returns zero across `GametoraSupportCardParser.php`, `SupportCard.php`, `SupportCardResource.php`, and the final migration, which has no slug column. A per-card link is therefore constructible from the export body today, and a later slice wanting it in the UI would need `url_name` stored or read at import. Verified against page 30028 (Kitasan Black), 2026-10-01. |
| Card tier labels | A current Global tier source | Game8's list is 2026-09-23 and MLB-scored, GameWith is JP, uma.guide unconfirmed (`ADR-0014:147-158`) |
| Scenario Link bonus magnitude | Any source publishing link bonus magnitudes | None found in any pass to date |
| Per-turn gauge state | A client capture, or Trainer entry | No source publishes gauge trajectories |
| Client-surface claims | The frames themselves, or a re-run of the measurements they supported | The frames did not render in this pass; the claims rest on `DESIGN.md:725-729` and `UMAMUSUME_REFERENCE.md:524-528`, both of which measured the frames. Closing this needs the frames viewable or their measurements re-verified. |

No fetch is proposed here, and none was made.

---

## 7. Sourced slice brief

This section refines §2's candidate 2 by sourcing it against the game's own surfaces. It does not rewrite §2; §2's "unauthorized" reading stands until the owner rules. §4's "zero implementation phases" stands too: the slice below opens only on the owner's §6-1 ruling.

§7 answers §6-1; §2's "unsourced" verdict predates this section.

### 7.1 Task 1 answer: per-card effect preview is the candidate the game's own surfaces support

The client shows a card's effects **per card**, on a card detail panel, at the card's own level, and it shows **composition counts** for a deck rather than any effect aggregate. So candidate 2 is the one the game's behaviour supports, refined to its per-card shape; a deck-total surface is not game-supported, candidate 1 has no tier-1 support and no requirement, and candidate 3 is excluded by PRD A-8. Because the client's panel reads the card's own level and the app holds no level (US-12 forbids storing one), the shape the app's data permits is the level-free one: each effect's value at the highest anchor the source publishes.

| Question | What the client does | Source (tier, locator, date) |
|---|---|---|
| Fixed level or picked level? | The card detail panel shows the card at its own level (`Lvl N / MAX`, `0 SP to next level`) with the Unique Perk on its own level badge. The Trainer does not pick. A reference site does pick: GameTora's per-card page has a level selector (minus 5, minus 1, plus 1, plus 5) and prints "Unlocked at level N" | Tier 1: `DESIGN.md:725-729` (measured from frames `155215` to `155337`, captured 2026-07-15); tier 1: `UMAMUSUME_REFERENCE.md:524-528`; tier 2: GameTora page, read 2026-09-27 (`UMAMUSUME_REFERENCE.md:508-518`) |
| Deck totals or per card? | Per card. The deck screen carries per-slot corners (rarity, type, four break diamonds, `Lvl N`) and a type legend row whose `xN` counts appear only when non-zero. No effect aggregate anywhere | Tier 1: `DESIGN.md:701-723` (measured from frame `Screenshot 2026-07-15 155016.png`) |
| What the deck adds during a run, or only static effects? | During a run the client shows per-card live state on the training HUD rail (bond gauge, an orange chevron when friendship training is available, a flame on the card's own tile), not effect values | Tier 1: `DESIGN.md:688` |
| A surface the app's deck panel mirrors? | The app's six-slot panel mirrors the client's deck editor. The client's per-card surface is the card detail panel, and the app has no card detail surface | Tier 1: `DESIGN.md:701-714`; `resources/views/components/deck-panel.blade.php` |

Corroboration and one limit. The export (tier 1, pulled 2026-09-27) is the data the preview reads, and its anchor structure is already decoded and verified against the client at three levels on five effects (`UMAMUSUME_REFERENCE.md:506-522`). The limit: the frames themselves were not rendered in this pass, because the image read returned no content, so the two rows above rest on the two repository documents that measured those frames, not on my own viewing of them.

### 7.2 The slice

**Scope.** On the run screen's Support deck panel, each equipped card gains a line listing its effects at cap: for every anchor row, take the last value that is not `-1`, resolve the effect id against `support_effects`, and print the value with its unit. The line names its basis (the cap), and an effect id with no dictionary row prints a visible unverified marker rather than a blank. Nothing is stored, no level is asked for, and the picker is untouched.

**Files to add or change.**

| File | Change | Purpose |
|---|---|---|
| `app/Services/SupportCardEffects.php` | add | `atCap(SupportCard $card, array $names): list<array{id: int, name: string|null, value: int, level: int}>`. Static, matching `app/Services/ScenarioCaps.php`'s shape and docblock style. The cap is the last anchor that is not `-1`; `level` is the anchor's level from the fixed ladder (1, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50) so the view can name the basis |
| `app/Http/Controllers/TrainingRunController.php` | change `showData()` | Load the 35-row dictionary once (`SupportEffect::query()->pluck('name_en', 'effect_id')->all()`) and pass it beside `deckCards` (`:307`), so six slots cost no extra query |
| `resources/views/components/deck-panel.blade.php` | change the equipped list (`:36-53`) | One effect line per equipped card, from the service. The picker (`:59-87`) is not touched, because KI-43 already makes it 80 to 89 percent of the page |
| `tests/Unit/SupportCardEffectsTest.php` | add | see below |
| `tests/Feature/RunDeckPreviewTest.php` | add | see below |

**Schema changes.** None. No migration, no column, no index. The slice reads `support_cards.effects` and `support_effects` as they stand.

**Tests.**

- `tests/Unit/SupportCardEffectsTest.php` pins: the cap is the last non-`-1` anchor (fixture Kitasan Black `30028`, whose export anchors resolve to Friendship Bonus 25, Mood Effect 30, Power Bonus 1, Training Effectiveness 10, Initial Friendship Gauge 35, Race Bonus 5, Fan Bonus 15, Hint Levels 2, Hint Frequency 30, Specialty Priority 80); an effect whose anchors are all `-1` is omitted; an anchor id with no dictionary row yields `name: null` so the view can mark it; the returned `level` matches the anchor's position on the ladder.
- `tests/Feature/RunDeckPreviewTest.php` pins: an equipped card renders its effect line on the run screen; the line names its cap basis; a run with no deck renders no effect line (the existing named-absence state is unchanged); a card carrying an id with no dictionary row renders the unverified marker rather than a blank.

**PRD or ADR dependencies.** No ADR: no schema change, so none is needed. One PRD question is a prerequisite for the owner: FR-A-8 already contemplates a Global-facing surface offering the stored rows (`PRD.md:106-108`, "a Global-facing surface offers only rows carrying a Global release date"), which may cover a read-only effect display under "Reference data only"; if it does not, one FR line is owed. Either way the owner's §6-1 ruling (plan §4, P-1) comes first. Neither document is written here.

**What the slice does not do.** Level-preview (needs a Trainer-supplied level; US-12 forbids storing one). Deck-aggregate totals (not game-supported; the client shows composition counts). Catalogue browsing (no requirement; tier-1 support is a collection inventory the app cut). Run-math input (excluded by PRD A-8). The card-to-skill hint join, the friendship gauge, tier labels, the Unique Perk values, and KI-43's picker rework.

**Sources the slice's behaviour rests on.** Tier 1: the GameTora export bodies, tracked, pulled 2026-09-27 (`support-cards.88dea522.json`, `support_effects.ca447e53.json`); the client frames of 2026-07-15 as measured in `DESIGN.md` §6.5b and `UMAMUSUME_REFERENCE.md` §1.4. Tier 2: the GameTora per-card page, read 2026-09-27; Game8's card list, 2026-09-23. The anchor rule and its verification: `UMAMUSUME_REFERENCE.md:506-522`. The display constraints the slice must honour: `DESIGN.md:739-742` (a displayed value names its own rule, D-256) and `UMAMUSUME_REFERENCE.md:572-575` with D-20 (a visible unverified marker rather than an invented label). Cap values are stated anchors, not interpolations, so D-256's "print the anchors it interpolated between" is satisfied by naming the basis rather than by printing two bracketing anchors.

---

## 7.4 Correction, 2026-10-01, landed by the implementation pass

The §7 slice is built. Three of its own claims did not survive contact with the tree, and each is corrected here rather than by editing the prose above.

1. **The marker rule was aimed at the wrong ids.** §7 says the surface needs an unverified marker "for ids 32, 33, 41, 9991". That is true of the export body and false of the shipped table: `GametoraSupportEffectParser.php:24-30` documents the `name_en` to `name_en_eon` fallback, and the imported dictionary holds `Initial Skill Points Up` (32) and `All Stats Bonus` (41). Measured against the committed bodies, 33, 41 and 9991 are carried by zero cards, 26 effect ids are used by at least one card, and every one of those 26 has a dictionary row, so the orphan count is zero. The marker keys on the absent-row case, which is empty today, and is kept as the defensive branch for a source that later ships an id the dictionary does not name. `UMAMUSUME_REFERENCE.md` §1.4.8's predicted blank cell for the 14 SSR cards carrying id 32 does not occur, and what those cards print is the publisher's alternate English rendering rather than a client `name_en` string. Whether that rendering is acceptable display copy is a ruling §7 did not make and the slice did not need: the row is stored, the column is NOT NULL, and the parser's reason is on the record.
2. **The dictionary load moved.** §7's file table puts it in `TrainingRunController::showData()`. The implementation fence named four files and the controller was not one of them, so the single load landed in the component's own `@php` block, once per panel rather than once per card. §7's file table is superseded on that point; the N+1 it was written to avoid is still avoided.
3. **The cap is not usually level 50.** Counting the last anchor that is not `-1` across the 5,114 anchor rows in `support-cards.88dea522.json`: level 45 on 1,724 rows, level 50 on 1,391, level 40 on 957, level 35 on 818, level 30 on 200, the remainder at 25 and below. So the basis label reads `highest stated anchor` and prints no level, because naming one would be wrong for a large share of the figures.

Evidence for the counts: a read of the two committed bodies with `node`, 2026-10-01, no network and no database write. The shipped panel was measured in a browser on a run-unique scratch database before and after: 289,411 to 292,976 bytes of panel, page share 88.6 to 88.8 percent, three equipped cards rendering seven and eight chips each, zero `[Unverified]` markers against the real dictionary, console clean. KI-43 is untouched and still open; the added 3,565 bytes sit on top of a page that was already 88.6 percent deck, and the picker that causes that is what §7 defers.
