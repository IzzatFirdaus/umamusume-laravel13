# ADR-0009: Seeding `scenario_slots` — three sources, what each unlocks, what stays dark

<!-- ADR-CONTRACT
  decision: accepted in part (owner ruling) — Option A (committed client export) for URA Finale only; Options B and C remain unimplemented
  code: built — ScenarioSlotSeeder reading committed JSON (R55, f0ae288); source_key migration (R54, c0a743f)
  superseded-by: —
  amended-by: 0003 (its R3 is narrowed: URA Finale seeding is no longer refused)
  open: the schema key question (one race per month and half) is unanswered, so no slice should treat "pick a source" as the next actionable step; the tier label set is undecided
-->

Status: **RULED IN PART.** Slice 11 implemented Option A (committed client export) for URA Finale only:
the `source_key` migration (R54, `c0a743f`), `ScenarioSlotSeeder` reading committed JSON (R55, `f0ae288`),
and tier-label correction nulling unsourced grade codes (R65, `2d0c1dc`). Options B (live fetch) and C
(bulk seeding from wiki) remain unimplemented. **This narrows `ADR-0003` Amendment R3**, which had refused
a seeder for want of a source; the 2026-09-28 reason held for the other three scenarios and for the
fetch-engine path, both of which still await an owner decision.

## Problem

`scenario_slots` was empty in every real database, and the stub seeder that would have held the rows was
deleted because `ADR-0003` R3 ruled slot rows fetch-engine work with no sourced data to fetch. Everything
downstream that reads slots was therefore rendering an absence: `StoreRaceEntryRequest` requires a
`scenario_slot_id` that exists and belongs to the run's scenario, `TrainingRun::epithetProgress()` matches
prerequisites against `completedRaceTitles()` by title, `KI-10`'s ratio half needs to know which race a
placed finish came from, and `KI-17` is the same shape from the other side.

R3's stated blocker was the source, not the schema. A calendar with the three things R3 named now exists in
this repository, and a client export that predates it is on disk. So the question moved: not *whether a
source exists*, but *which source, through which mechanism, and into which schema*.

## The blocker that is not about sources

Whatever the answer, the table cannot hold a real career calendar as keyed. `scenario_slots` has
`unique (scenario_key, month, half, kind)`, and the client and the calendar both put **several races in the
same month and half** — `docs/scenarios/09-global-race-calendar.md` lists three slots for Early August of
Junior Year, all `goal_race`. One of the three would insert; the other two would violate the key.

So seeding is a schema decision before it is a data decision, and the schema is the Architect's, cited to a
PRD requirement. Three shapes were considered, none adopted here:

1. Add a discriminator to the key (`race_id`/`source_id`, or the export's `instance`). Preserves the
   invariant the key was written for and makes the calendar's own shape expressible.
2. Move the calendar's grain: one slot per (scenario, month, half, kind) with a child table for the races
   in it. Larger change, cleaner model, and it collides with a standing instruction not to add tables.
3. Keep the key and seed one race per slot, which is what the fixtures do today by deriving month and half
   from order. This is **not a faithful calendar**; it is a sample that fits the constraint, and it should
   not be mistaken for option 1 or 2 working.

A second schema question is already open: `tier` is a nullable free-text column with no enum behind it, and
`[Global]`'s client prints one label where the export's `gradeName` for code 400 reads `OP/L (Open/Listed)`.
A seeder that picks a tier set picks a contract, so the tier question is decided *before* any import, not
inside one.

And `is_mandatory` cannot mean what the column suggests: only the Junior Make Debut and the scenario final
are mandatory for every trainee, while Goal races are per-character and four captured client panels show
four different Goal sets. Seeding `is_mandatory = false` on a Goal race is accurate; seeding the Goal banner
itself would need a per-character table that no source names.

## What is common to all three sources

- None of them is available without the schema decision above, so no slice should treat "pick a source" as
  the next actionable step. Picking the key is first.
- Whichever is chosen, provenance lands per row: `source_url`, `snapshot_path`, `fetched_at`,
  `source_timezone`, `is_manual`. A row whose date cannot be stated is not seeded.
- `KI-17` is not unlocked by any source. It is a missing link between `race_entries` and turns.
- A populated calendar makes the guide text and the seeded rows two authorities. D-228 decides by date: the
  newer dated source wins, and the app should record which one it used rather than reconciling them silently
  at read time.

## Decision

None taken on the source. The owner's three questions are, in the order that unblocks the rest:

1. Which key shape carries more than one race per month and half, and does that need a migration now or a
   later slice?
2. Fetch engine or seeder for the chosen source, given that R3's reason for the fetch engine was the
   absence of a source rather than a rejection of seeders?
3. Which tier label set `scenario_slots.tier` commits to, and whether `tier` and `slot_label` should both
   exist while both can hold `G1`.

**This ADR may be cited as evidence that the source question is answered and the schema question is not.
It may not be cited as permission to seed.**

## Links

- `ADR-0003` R3 (the rule this ADR qualifies) and R2 (the nullable `scenario_slot_id`), `ADR-0004` (the
  provenance pattern)
- `docs/scenarios/09-global-race-calendar.md`; `config/scenarios.php` (`grade_objectives`)
- `tests/Feature/Schema/ScenarioSlotMigrationTest.php`, `ScenarioSlotSeeder`
- KI-10, KI-11, KI-15, KI-17; D-20, D-227, D-228, D-270
