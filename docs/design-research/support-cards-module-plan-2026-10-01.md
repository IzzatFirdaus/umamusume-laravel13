# Support card module, sourced development plan (2026-10-01)

**Status: DRAFT. Not committed.** Goes to the owner for reading before any development slice opens.

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

No fetch is proposed here, and none was made.
