# Handoff — race read path moves to `race_catalog_slots`

**Date:** 2026-09-29
**Author:** the calendar/parser thread (commits `c111049`, `ef75ff0`, `e7b78a4`)
**Audience:** Slice 11 (`c0a743f`, `f0ae288`, `5820e77`, `70248b3`, `65f8b92`, `4992282`) and whoever next touches the run screen
**Status:** notice, not a request. The retarget lands as its own commit; this file is the reason it exists.

## Why this note exists

Three collisions in one pass, all real, none hostile:

| Time | Slice 11 | This thread |
|---|---|---|
| — | `c0a743f` widens `scenario_slots`' unique key so several goal races share a half-month | audit had reported that exact collision (406 slots → 24 keys) |
| — | `f0ae288` seeds goal races into `scenario_slots` from committed copies of `race_instances`/`races`/`ura-races` | `c111049` parses the same three exports into a new table |
| — | `70248b3` fixes the cell-clobber in `calendarCells()` and the blade | was step 6 of this thread's brief |

Both threads solved the same problem in different tables. This note records which one the read path uses and why, so nobody reconstructs the timeline from commit order.

## The deciding fact

`scenario_slots` columns today:

```
id, scenario_key, kind, slot_label, title, description, month, half, tier,
fans_needed, is_mandatory, is_maiden_gated, sort_order,
source_url, snapshot_path, fetched_at, source_timezone, is_manual, timestamps
```

It has **no `year`**. `c0a743f` added `source_key`, which makes several goal races legal in one half-month, but a Classic Early-May slot and a Senior Early-May slot remain indistinguishable — and `09` records **14 G1s that recur across Classic and Senior at the identical month and half**. Fourteen races the grid must place in two years with nothing in the row saying which year it is.

It also has **no `distance`, `surface`, `track`, or payout curve**, so the picker has nothing to show per row, and `f0ae288` skips `month === 99999` outright, so **the four scenario finals are not in it at all**.

`race_catalog_slots` has all of it and is populated.

## State of the populated table

`uma:fetch gametora-race-catalog` against a throwaway database, 2026-09-29:

```
'gametora-race-catalog': 0 updated, 410 created, 0 skipped (manual), 0 to review.
```

410 rows: Junior 58, Classic 161, Senior 185, finale block 6. That is `docs/scenarios/09-global-race-calendar.md`'s 413 slots minus Prix Niel, Prix Foy (`unreleased_servers: ['en']`) and `final_masters` (`[JP-Only]`, no `start_en`). `scenario_key` is set on exactly 4 rows — `ura_finale`, `unity_cup`, `trackblazer`, `grand_concert` — and null on the other 406, because the scenarios share every monthly slot and differ only at the final. All 410 carry `source_url`, `snapshot_path`, `fetched_at` and `source_timezone`, per ADR-0003 R3.

## What changes

`calendarCells()`, `race-calendar.blade.php`, `race-panel.blade.php` and their tests read `race_catalog_slots`, keyed on `(year, month, half)`. **`70248b3`'s multi-slot aggregation is kept as written** — it was the right fix for the symptom it addressed, and it is the shape the new table needs too. Only the source of the rows moves.

## What does not change

- **`scenario_slots` is untouched.** No column dropped, no index changed.
- **`source_key` stays.** Unused by the read path, still used by `f0ae288`.
- **`goal_race` stays in `VALID_KINDS`.** Removing it is deferred; see below.
- **`free_race`, `team_race`, `grade_deadline`, `scripted_event` stay and stay on `scenario_slots`.** They belong there. A Trainer-chosen free race has no catalogue row by definition, so `free_race` is correctly Slice 11's kind and this thread will not move it.
- **`ScenarioSlotSeeder` and the committed export files stay.**

## Known overlap, deliberately left

Both tables can hold a goal race today. That is not resolved here — resolving it means either deleting `f0ae288`'s seeded rows or dropping `race_catalog_slots`, and neither is a call one of the two threads gets to make alone. Filed as a gap with both hashes cited.

## Deferred: `isMandatoryGoal()`

`ScenarioSlot::isMandatoryGoal()` returns `isGoalRace() && $this->is_mandatory`, and `TrainingRun::calendarCell()` uses it to draw the red pennant. So the pennant currently marks **scenario-scoped** mandatory races — debut and finals — as if they were per-character Goals. `09` establishes from four client panels that Goals are per-trainee and that only the debut and the final are universally mandatory.

The fix is small: decouple the two, draw the pennant from a goals table, and leave `is_mandatory` as the honest but separate fact it is. It touches `ScenarioSlot.php`, which `c0a743f` modified and which drives the pennant `f0ae288` now feeds.

**If Slice 11 is still active, this is yours — say so and this thread will not touch it.** If Slice 11 has closed, this thread takes it with two-state tests: no Goal seeded means no pennant on the debut; a Goal seeded onto the debut means the pennant is present.

## Separate regression, not this thread's and not touched

`RunViewFrameTest:92` expects one `role="radiogroup"` in the scrolling log region and finds two, because `5820e77` added a "Race entry mode" fieldset at `race-panel.blade.php:29`. `guided-step.blade.php:117` owns the other. Left alone deliberately — it is Slice 11's surface and Slice 11's regression.

## One measurement trap worth not re-hitting

A verification script in this thread reported "year 2 = 0, year 3 = 0" and the numbers were wrong, not the data: Laravel's `Builder::where()` **mutates in place**, so reusing one builder across several counts scopes every later query by the earlier `where`. Build a fresh query per assertion. Same class of trap as a chained `git grep A && git grep B` short-circuiting on the first no-match.

## Noted, not fixed

`SourceFetcher` writes snapshots with a `.html` extension even for JSON bodies — visible in the stored `snapshot_path` for all 410 rows. Pre-existing, inherited by the new source, out of scope here.
