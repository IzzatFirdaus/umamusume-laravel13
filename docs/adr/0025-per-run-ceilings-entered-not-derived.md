# ADR-0025: A run's per-stat ceilings are entered facts, not a composition

<!-- ADR-CONTRACT
  decision: accepted (2026-10-18) — the owner has approved the wording; migration and tests are built. The decision is now binding per PRD requirements US-3, C-1, C-2.
  code: built — migration 2026_10_12_140000_add_entered_caps_to_training_runs_table (ran, batch 16); ScenarioCaps::ENTERED_CAPS; tests/Feature/EnteredStatCapsTest.php
  superseded-by: —
  amended-by: 0015 (adds an override on top of its per-stat, per-scenario ceiling), 0004 (does not reopen its cap reference data), 0002 (does not reopen its bound)
  open: the run report's §1.2 gap stays open — this decision records the client's figures, it does not explain them
-->

Status: **Accepted (2026-10-18)** — the owner has approved the wording; migration and tests are built. The decision is now binding per PRD requirements US-3, C-1, C-2.
and not in question ("store entered caps, derive nothing"); what awaits approval is the wording that records
it. Amends `ADR-0015`'s read path; does not reopen `ADR-0002` or `ADR-0004`.

## Context

`ADR-0015` made a stat's ceiling a computed value: `base_cap` plus the run's scenario's per-stat bonus, clamped
to the engine `hard_cap`, read through `App\Services\ScenarioCaps` as the single owner. That made the ceiling
per-scenario, which is what the walk asked for at the time.

The Rice Shower / Unity Cup run report then measured a ceiling the tool cannot produce. The client showed four
caps over the Unity Cup floor of 1300 — Speed 1325, Stamina 1322, Power 1368, Guts 1308. The report's first pass
attributed the four extras to inheritance breakthroughs, and its own ancestry panels falsified that: the six
blue sparks do not reproduce the four figures under any activation schedule. The report states the failure
explicitly, so it needs no amendment — it already carries its own correction.

The candidate replacement layer does not exist as data either. The Max-Stat effect ids are real, but no
publisher in the research pass prints a per-level magnitude for a Global card, and the seeded catalogue's
effect arrays for the six cards in that run hold no Max-Stat entry at all. So the composition would have to be
invented.

`AGENTS.md` §5 forbids inventing a value the tool has no source for, and the Floor forbids a fact stored
without provenance. That settles the shape: **what the client shows is a recorded fact about the run, and the
tool records it rather than deriving it.** No magnitude table is asserted, so none can be wrong.

## Decision

`training_runs` gains four nullable **`unsignedSmallInteger`** columns — `cap_speed`, `cap_stamina`,
`cap_power`, `cap_guts` — each holding the ceiling the Trainer read off the client for that run, or null when
they read none. The type matches `turn_entries`' own stat columns. The migration backfills nothing, and its
`down()` drops exactly those four. **These are entered facts, not a composition**: the per-star breakthrough
table the run report first reached for is not adopted here, and the decision supersedes that attribution by
pointing at the report's own §1.5 falsification rather than re-arguing it.

- **A ceiling the run recorded outranks the ceiling the scenario publishes.** `forRun()` answers the effective
  bound — an entered figure where one exists, `ADR-0015`'s arithmetic otherwise — and `scenarioCaps()` answers
  the published arithmetic alone. `ENTERED_CAPS` is the one stat-to-column map and no site outside
  `ScenarioCaps` reads the columns. Every validator, band and advisor keeps calling `forRun()`, so a form bound
  and a drawn ceiling cannot disagree; the single surface whose own source line names `config/scenarios.php`
  (the cockpit's *Published stat caps* table) calls `scenarioCaps()`, because a Trainer's reading is not in
  that file.
- **The floor is the run's own recorded stat; the ceiling is the engine's.** A ceiling may not sit below the
  highest value this run's turn entries already hold for that stat, and clearing one is refused on the same
  ground when the reverted bound would fall under a stored row: a recorded stat above its own ceiling is the
  state this rule exists to prevent. Nothing floors it further — no source claims a client ceiling can never
  sit below the scenario figure, and inventing that claim is the error this decision avoids. The engine hard
  cap stays the upper bound.
- **It is Trainer data, so it is never engine-owned.** No `data_sources` row is written for a ceiling and the
  fetch engine never touches the columns, the rule `is_manual` rows already carry (`AGENTS.md` §8).
- **Four stats, not five.** Wit gets no entered column: the ruling named the entry surfaces and did not extend
  to Wit, and a column with no ruled writer would be schema for a fact nobody may enter.
- **Entered on two surfaces.** The build target step, where a target above the scenario figure is refused today
  and where the wizard must carry the values into the run at Start Career; and the Cockpit identity form, so a
  reading corrected mid-run moves the turn form's bound without recreating the run. A ceiling entered in the
  same save as the target that needs it is the bound that target is measured against.
- **Null reverts, it does not zero, and an absent key is not a clear.** An empty field returns the ceiling to
  the scenario's figure, exactly the behaviour before this decision; a write naming no ceiling key leaves the
  stored ones alone, which is what lets the status, scenario and period forms save without touching them.
  `ADR-0002`'s UI condition holds: the halved-gains marker and the run's ceiling stay two separate visible
  markers.

## Explicitly unchanged

- **No derivation.** Sparks, support-card effects and bursts contribute nothing to a ceiling anywhere in this
  application. `ADR-0001`'s guidance scope and `ADR-0020`'s no-prediction line are untouched.
- **`ADR-0015` stands as the fallback.** This is an override layer with a null default, not a replacement: a run
  that names a scenario and enters nothing behaves exactly as it does today.
- **The run report's §1.2 gap stays open.** This decision records the client's figures; it does not explain
  them, and the shortfall it names is measured, not resolved.

## Consequences

### 1. The source's own caps become enterable

A Unity Cup run can hold Speed 1325, Stamina 1322, Power 1368 and Guts 1308, and the target step and the turn
form accept stats to those values. Today those four figures cannot be expressed at all: the bound is computed,
so a value above the scenario base is refused no matter what the client shows.

### 2. The ceiling's authority moves to the observation

When a cap is entered, the number the Trainer read outranks the arithmetic. Two figures stay visible beside the
field that holds it: the form's bound follows the entered number, and the published scenario figure is printed
next to it, so an entered ceiling reads as a reading rather than as something the matrix states.

### 3. What this does not close

Nothing derives a cap, so a Trainer who enters none sees the scenario base and cannot reach 1325 — recording is
their act, and the tool will not guess it. The run report's unexplained extras stay unexplained; a future source
that publishes the actual composition would supersede this decision rather than extend it. And the entry
surfaces carry no history: correcting a cap mid-run overwrites it, because no requirement asks which caps a run
held last month.

## Links

- `ADR-0015` (the per-stat, per-scenario ceiling this adds an override to), `ADR-0004` (the `Scenario` record's
  five caps and hard cap), `ADR-0003` (the columns it does not change), `ADR-0002` (the UI condition it keeps),
  `ADR-0020` (the no-prediction line)
- `PRD.md` FR-C-1, FR-C-2, US-3; `AGENTS.md` §5, §8
- migration `2026_10_12_140000_add_entered_caps_to_training_runs_table`; `App\Services\ScenarioCaps`
  (`ENTERED_CAPS`, `forRun()`); `App\Http\Requests\Concerns\ValidatesEnteredCaps`
- `tests/Feature/EnteredStatCapsTest.php`, `tests/Feature/StatCeilingsTest.php`
- `docs/audits/rice-shower-unity-cup-ux-walk.md` §1.2 and §1.5 (the run report's own correction)
- `docs/framework-ui-ux/design-framework-index-2026-10-21.md` (the re-check table); rules UX-4, UX-2,
  GP-2, TOG-3

## Ledger

- 2026-10-22 · **framework check against `docs/framework-ui-ux/` — the last bullet of the Decision keeps a
  condition that is unbuilt.** "ADR-0002's UI condition holds: the halved-gains marker and the run's ceiling
  stay two separate visible markers" stands as a requirement and is not changed by this entry. What the
  check found is that no shipped component draws either marker: the component that did, `StatBand.vue`,
  was deleted in the frontend cutover (`b30906a`), and the stat panel that replaced it is target-shaped by
  design. The rules this decision otherwise meets are worth recording too, because they are the reason
  the override is safe to read: **UX-4** (the provenance mark sits beside the number it labels — the
  published scenario figure is printed next to the entered one), **GP-2** (a derived figure states the
  rule that produced it) and **TOG-3** (status may be stale or absent, but never wrong — an empty field
  reverts to the scenario's figure rather than zeroing it). The full finding and the framework rules the
  condition binds are in `ADR-0002`'s ledger.
