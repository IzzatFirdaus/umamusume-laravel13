# ADR-0025: A run's per-stat ceilings are entered facts, not a composition

<!-- ADR-CONTRACT
  decision: accepted (owner ruling "store entered caps, derive nothing", 2026-10-12) — four nullable cap_* columns on training_runs; an entered ceiling outranks the scenario figure for that run
  code: built — migration 2026_10_12_140000_add_entered_caps_to_training_runs_table, tests/Feature/StatCeilingsTest.php (read path), tests/Feature/EnteredStatCapsTest.php (write path)
  superseded-by: —
  amended-by: —
  open: why the measured caps sit above the published floor is recorded, not explained; a source that publishes the real composition supersedes this decision rather than extending it
-->

Status: **Accepted.** The ruling is the owner's ("store entered caps, derive nothing", entered on both
ruled surfaces, 2026-10-12); this text was drafted by the implementing agent and passes the four-condition
acceptance check the landing brief sets. Amends `ADR-0015`'s read path; reopens neither `ADR-0002` nor
`ADR-0004`.

Date: 2026-10-12
Deciders: product owner (ruling), implementing agent (draft)
Relates to: `ADR-0015` (the per-stat, per-scenario ceiling this adds an override to), `ADR-0004` (the
`Scenario` record's published caps), `ADR-0003` (the columns it does not change), PRD **FR-C-1**,
**FR-C-2**, **US-3**, and the Rice Shower / Unity Cup run report §1.2 and §1.5

## Context

`ADR-0015` made a stat's ceiling a computed value: `base_cap` plus the run's scenario's per-stat bonus,
clamped to the engine `hard_cap`, read through `App\Services\ScenarioCaps` as the single owner. That made
the ceiling per-scenario, which is what the walk asked for at the time.

The Rice Shower / Unity Cup run report then measured ceilings the tool cannot produce: four stats whose
client cap sits above the floor the scenario publishes, each named in that report's §1.2. The report's
first pass attributed the four extras to inheritance breakthroughs at a fixed magnitude per star rank
(`★1 +4 / ★2 +9 / ★3 +16`), and its own §1.5 ancestry panels falsified that: the six blue sparks that run
holds do not reproduce the four figures under any activation schedule. The report states the failure
itself, so it carries its own correction and needs none here; this decision supersedes the attribution by
pointing at it, not by re-arguing it.

The candidate replacement layer does not exist as data either. `support_effects` carries real Max-Stat
effect slots, but no publisher in the research pass prints a per-level magnitude for a Global card, and the
seeded catalogue's effect arrays for the six cards in that run hold no Max-Stat entry at all. So the
composition would have to be invented.

`AGENTS.md` §5 forbids inventing a value the tool has no source for, and the Floor forbids a fact stored
without provenance. That settles the shape: **what the client shows is a recorded fact about the run, and
the tool records it rather than deriving it.** No magnitude table is asserted, so none can be wrong.

This is the second attempt to store a run's ceiling. `2026_10_10_140000` added a `stat_ceilings` json bag on
the same reasoning and `2026_10_10_170000` dropped it four days later, because the render half never landed
and no reader of the column remained. The difference this time is the reader. Four typed columns rather
than a bag, because these are four whole numbers with independent bounds, and `ScenarioCaps::ENTERED_CAPS`
is the single list that names them.

## Decision

`training_runs` gains four nullable **`unsignedSmallInteger`** columns — `cap_speed`, `cap_stamina`,
`cap_power`, `cap_guts` — each holding the ceiling the Trainer read off the client for that run, or null
when they read none. The type matches `turn_entries`' own stat columns. The migration backfills nothing,
and its `down()` drops exactly those four. **These are entered facts, not a composition**: the per-star
breakthrough table the run report first reached for is not adopted here, and the decision supersedes that
attribution by pointing at the report's own §1.5 falsification rather than re-arguing it.

- **A ceiling the run recorded outranks the ceiling the scenario publishes.** `forRun()` answers the
  effective bound — an entered figure where one exists, `ADR-0015`'s arithmetic otherwise — and
  `scenarioCaps()` answers the published arithmetic alone. `ENTERED_CAPS` is the one stat-to-column map and
  no site outside `ScenarioCaps` reads the columns. Every validator, band and advisor keeps calling
  `forRun()`, so a form bound and a drawn ceiling cannot disagree; the single surface whose own source line
  names `config/scenarios.php` (the cockpit's *Published stat caps* table) calls `scenarioCaps()`, because a
  Trainer's reading is not in that file.
- **The floor is the run's own recorded stat; the ceiling is the engine's.** A ceiling may not sit below
  the highest value this run's turn entries already hold for that stat, and clearing one is refused on the
  same ground when the reverted bound would fall under a stored row: a recorded stat above its own ceiling
  is the state this rule exists to prevent. Nothing floors it further — no source claims a client ceiling
  can never sit below the scenario figure, and inventing that claim is the error this decision avoids. The
  engine hard cap stays the upper bound.
- **It is Trainer data, so it is never engine-owned.** No `data_sources` row is written for a ceiling and
  the fetch engine never touches the columns, the rule `is_manual` rows already carry (`AGENTS.md` §8).
- **Four stats, not five.** Wit gets no entered column: the ruling named the entry surfaces and did not
  extend to Wit, and a column with no ruled writer would be schema for a fact nobody may enter.
- **Entered on two surfaces.** The build target step, where a target above the scenario figure is refused
  today and where the wizard must carry the values into the run at `Start Career`; and the Cockpit identity
  form, so a reading corrected mid-run moves the turn form's bound without recreating the run. A ceiling
  entered in the same save as the target that needs it is the bound that target is measured against.
- **Null reverts, it does not zero, and an absent key is not a clear.** An empty field returns the ceiling
  to the scenario's figure, exactly the behaviour before this decision; a write naming no ceiling key leaves
  the stored ones alone, which is what lets the status, scenario and period forms save without touching
  them. `ADR-0002`'s UI condition holds: the halved-gains marker and the run's ceiling stay two separate
  visible markers.

## Explicitly unchanged

- **No derivation.** Sparks, support-card effects and bursts contribute nothing to a ceiling anywhere in
  this application. `ADR-0001`'s guidance scope and `ADR-0020`'s no-prediction line are untouched.
- **`ADR-0015` stands as the fallback.** This is an override layer with a null default, not a replacement: a
  run that names a scenario and enters nothing behaves exactly as it did before.
- **The run report's gap stays open.** This decision records the client's figures; it does not explain them,
  and the shortfall the report names is measured, not resolved.
- **`scenarios.cap_*` is a different object.** Those columns and `scenarios.hard_cap` are the engine's
  published, per-*scenario* figures, maintained by `GametoraScenarioParser`. The four here are per-*run* and
  Trainer-entered. `ScenarioCaps` takes the scenario figure from `config/scenarios.php` (`ADR-0015`) and the
  entered one from the run's own row; serving published caps from the `scenarios` table instead of config is
  a separate decision, not an extension of this one.

## Consequences

### 1. The source's own caps become enterable

A Unity Cup run can hold the four caps its client showed and the turn form accepts stats to them. Before
this decision those figures cannot be expressed at all: the bound is computed, so one point above the
scenario floor is refused no matter what the client displays.

### 2. The ceiling's authority moves to the observation

When a ceiling is entered, the number the Trainer read outranks the arithmetic. Both stay visible: the
form's bound follows the entered figure and the published one is printed beside the field, so an entered
ceiling never reads as something the matrix states.

### 3. What this does not close

Nothing derives a ceiling, so a Trainer who enters none sees the scenario figure and cannot reach the
client's higher one — recording is their act, and the tool will not guess it. The entry surfaces carry no
history: correcting a ceiling mid-run overwrites it, because no requirement asks which ceilings a run held
last month. And the engine hard cap still bounds an entered figure, so a reading above it is refused rather
than trusted.
