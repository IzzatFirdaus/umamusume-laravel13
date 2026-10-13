# ADR-0026: Each trainee's career objectives are sourced reference data, read at trainee grain

<!-- ADR-CONTRACT
  decision: accepted — the owner signed FR-A-10 as worded and ratified this text on 2026-10-13 ("Sign FR-A-10 as worded. Flip ADR-0026 from Proposed to Accepted."). The four rulings it records (fetch source over seeder body; US-10 plus the drafted FR line; widest display reach; store everything and decode on display) were the owner's, asked before any code was written.
  code: built — migration 2026_10_13_120000_create_trainee_goals_table (verified by a fresh `migrate:fresh --seed` on a scratch file; the dev database is migrated last, under the owner's item-2 authorization, and this line is updated when it is); TraineeGoal + TraineeGoalType + TraineeGoalFactory; GametoraUraObjectivesParser; StoreTraineeGoals; PipelineRunner's seventh is_a() branch; config/uma.php gametora-ura-objectives; tests/Feature/{TraineeGoalTest,TraineeGoalsFetchTest,TraineeGoalsPanelTest,GametoraUraObjectivesParserTest}.php
  superseded-by: —
  amended-by: 0003 (lands its R3 rule that reference data arrives through the fetch engine, at a new grain), 0009 (takes none of its slot decision back: is_mandatory stays the scenario-wide fact), 0004 (does not reopen its cap reference data)
  open: cond_value 2 and 4 and cond_type 2 and 12 stay undecoded (22 stored rows, filed as KI-100); the debut carries no client pennant although the export makes it objective #1 for every character (kept as the source states it, alerts deduped, recorded in TraineeGoal's docblock); the 24-turn calendar grid this first promised a mark on is no longer rendered by any surface
-->

Status: **Accepted** — the owner's ruling of 2026-10-13 signed `PRD.md` **FR-A-10** as worded and ratified
this text, and directed the ADR index be regenerated rather than hand-edited. Drafted the same day as
**Proposed**, on `ADR-0025`'s precedent: the rulings were never in question, only the wording that records
them, and an agent does not record an owner ruling on inference.

## Context

Three documents told the reader that per-character objectives have no source. `docs/scenarios/09-global-race-calendar.md:733`
printed "❌ **Per-character Goal races are not in the export.** … **no GameTora key carries them**";
`docs/research-scratch/RACE-AND-SLICE-RESEARCH.md:268` printed "**none of it is in the export**, which is why
only four characters have any Goal data at all"; and the Cockpit's own absence copy told a Trainer that "a
schema proposal with a PRD citation is what unblocks this module". All three were load-bearing. `KI-34` was
reserved for exactly this entry and never filed, and `docs/research-scratch/DESIGN-AND-MECHANICS-REQUESTS.md:112-116`
names the draft it was reserved for as "the **`ura-objectives` finding**".

That finding is now measured, and the claims are wrong. `ura-objectives` is a key in the publisher's own
manifest (`https://gametora.com/data/manifests/umamusume.json`), and the document behind it answers `200` with
**135 characters and 1,701 objectives**, each carrying a race row inline, a required placing, a deadline turn
and a ladder order. Against this tool's own roster: **all 67 trainees have a ladder, 829 objectives, and 1,219
of the 1,222 race objectives on the document match a `race_catalog_slots` row this database already holds**, at
a turn that agrees with the stored turn on every one of them. The three that miss carry the source's own
`unreleased_servers: ["en"]` marker, so the source states that they are not Global races rather than leaving
that to be inferred.

Two independent readings confirm the decode rather than one. The `[Global]` client captures in
`docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md:186-191` state Rice Shower's four Senior objectives with month and
half, and the export's ladder for `char_id 1030` lands on the same turns, the same races and the same placings.
A published client readout of Haru Urara's ladder states "Have at least 5000 fans … Turn: 37 … Classic Class …
Early July", and the export's `cond_type 3, cond_value 5000` at turn 37 is that row — which also settles the
turn arithmetic `(year − 1) × 24 + (month − 1) × 2 + half` that `RaceCatalogSlot` and
`TrainingRun::TURNS_PER_YEAR` already run on. A Tier B dataset confirmed by Tier S client captures is what
`docs/scenarios/09-global-race-calendar.md:654` requires before a claim becomes app data, and that test passes
for the fan and placing figures the captures cover.

What is not settled is a minority of the codes. Across the whole document `cond_value` 2 (31 rows) and 4 (1)
name a placing no capture states, and `cond_type` 2 (10 rows) and 12 (1) name a condition this repository has
no reading for. Of the 829 rows the roster actually stores, **22 carry no readable requirement**: 17 placings
of figure 2 and 5 conditions of type 2. `AGENTS.md` §5 ("Never invent a client string") decides those: store
them, read them as unread.

## Decision

**One declared fetch source, not a seeder body and not Trainer entry.** `gametora-ura-objectives` joins
`config('uma.sources')` with a parser, a store action and a seventh `PipelineRunner` branch, so the rows carry
real `source_url` / `snapshot_path` / `fetched_at` / `source_timezone` (`ADR-0003` Amendment R3) and refresh
through `uma:fetch` and `uma:reparse` like every other reference table. `ADR-0009:44`'s "standing instruction
not to add tables" is read for what it is: `ARCHITECTURE-ESSENTIALS.md:63` scopes it to the repo #4 planner
consolidation, and seven reference tables have landed since under their own ADRs. The host is already
allowlisted, so no new origin is introduced and the SSRF floor is unchanged; `gametora.com/robots.txt` read on
2026-10-13 does not disallow `/data/` and publishes no crawl-delay, and the entry says plainly that the approval
that host carries was given without that check.

**Trainee grain, no run scope.** `trainee_goals` keys on `umamusume_id`, not on `character_card_id` and not on
`training_run_id`. `RACE-AND-SLICE-RESEARCH.md:250` sketched a nullable `training_run_id`; the sourced ladder
does not need it, because a run's progress against an objective is **derived** from the `race_entries` and
`turn_entries` the Trainer already records. Adding the column would make one fact owned by two writers and
would need a write path this feature does not ask for. The sketch gets a dated correction, not a silent edit.

**Store everything, decode on display.** Every objective row the source states for a trainee this tool holds is
written, keeping `source_cond_id`, `source_cond_type`, `source_cond_value`, `source_cond_value_2`,
`source_target_type`, `source_race_type` and `source_race_choice` raw, and the source's own
`unreleased_servers` becomes `global_unreleased`. `TraineeGoal::requirementPhrase()` answers with the client's
own words only for the figures a capture confirms — `Place 1st`, `Place top 3`, `Place top 5`, `Participate`,
and the fan form — and returns null for the rest. A row with a known kind and an unread figure keeps its kind
(`Placement`, with `required_placement` 2 or 4); it is not demoted to a guessed sixth kind.

**The race a Trainer reads is the calendar's, not the document's.** `race_catalog_slots.title` is already the
Global-vetted client string and is what every surface prints; the export's `name_en` is stored only where no
slot row carries the race. That keeps §5's vocabulary gate on the display path without touching dataset keys.

**The scenario's final rounds are stored but not re-printed as trainee goals.** 201 of the 829 rows are the
three post-December rounds at `target_type 3`, one triple per trainee, and they are the scenario-wide
obligation `race_catalog_slots.is_mandatory` already renders. They land as data and stay out of the ladder the
Cockpit prints, so a Trainer is not shown the same obligation twice.

**The Goal mark moves to the surfaces that still render.** `TrainingRun.php:746-749` promises "the banner
returns when `trainee_goals` exists to drive it", and `RACE-AND-SLICE-RESEARCH.md:279-286` repeats that the
component still renders a `goal` state. `SCREEN_SPEC.md:2092` records that `calendarCells()` — the method that
fed it — is no longer called from any live surface, and the only caller left in the tree is its own test. The
mark therefore goes on the race strip, the race decision screen and the race drawer, which is where the
`[Global]` captures put the goal line in the first place (`screenshot-notes/00:19`, `00:41`, `00:62`), and the
two claims are corrected forward rather than honoured into a dead grid.

## Consequences

- **A promotion invalidates the catalog cache.** `StoreTraineeGoals` writes through `PipelineRunner`, which
  bumps `catalog:version` on a non-zero write, because the trainee's ladder is rendered on a catalog detail
  page that caches behind that counter. A write that left it alone would serve a stale ladder until the TTL
  expired. The first write seeds the counter rather than moving it, which is the existing card branch's idiom,
  and `TraineeGoalsFetchTest` pins both halves.
- **Alternates are branches, not merged obligations.** 35 objectives on the roster sit at a shared ladder
  `order` with different `source_race_choice` values (one character is offered Arima Kinen *or* Nikkei Shinshun
  Hai). Both rows survive, the unique key carries the branch, and an achieved count reads one obligation at that
  position. Merging them would state a single demand the client does not make.
- **Twenty-two stored rows stay unread and say so.** Measured on the scratch seed: 17 objectives whose placing
  figure is 2, and 5 objectives of condition type 2. Across the whole 1,701-row document the same classes count
  31 plus 1 figure-4 plus 10 condition-type-2 plus 1 condition-type-12, of which the roster holds only part.
  The surface prints a named not-decoded state carrying the source's own figures, and the question is filed
  rather than answered.
- **`ADR-0015`'s single owner holds.** Due-and-unmet arithmetic for a goal runs through
  `RaceCatalogSlot::isAtOrBeforeTurn()`, the same reader the mandatory-race strip already calls. No second
  copy of that arithmetic is written, and a goal whose calendar row records no turn can be current and can
  never be missed.
- **`PRD.md` §6.11 is untouched.** Nothing here predicts an outcome, prices a chance or recommends an entry.
  The module states what the source says each trainee is asked for, and what this run has recorded.
- **`race_entries.objective_index` is not this.** That column already means the Trackblazer Grade Point period
  (`RaceEntry.php:30`, D-270, `MAX_OBJECTIVE_INDEX` = 4). This decision names nothing after it and re-points
  nothing to it, and the collision is written down because the word "objective" invites exactly that mistake.
- **`KI-34` is filed.** The reserved number's subject is the documentation defect: three tracked files asserted
  a published key did not exist. Closure of the register entry stays the owner's to confirm.
