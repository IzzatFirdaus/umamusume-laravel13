# ADR-0025: A run's per-stat ceilings are entered facts, not a composition

<!-- ADR-CONTRACT
  decision: proposed — the owner's ruling is recorded and not in question ("store entered caps, derive nothing"); what awaits approval is the wording that records it
  code: built — migration 2026_10_12_140000_add_entered_caps_to_training_runs_table (ran, batch 16); ScenarioCaps::ENTERED_CAPS; tests/Feature/EnteredStatCapsTest.php
  superseded-by: —
  amended-by: 0015 (adds an override on top of its per-stat, per-scenario ceiling), 0004 (does not reopen its cap reference data), 0002 (does not reopen its bound)
  open: the run report's §1.2 gap stays open — this decision records the client's figures, it does not explain them
-->

Status: **Proposed** — drafted 2026-10-12 for the owner's review of this text. The owner's ruling is recorded
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

`training_runs` gains four nullable integer columns: **`cap_speed`, `cap_stamina`, `cap_power`, `cap_guts`**.
Each holds the ceiling the Trainer read off the client for that run, or null when they read none.

- **`ScenarioCaps` stays the single ceiling owner.** Its read becomes: a non-null entered cap for that stat
  wins; otherwise the `ADR-0015` scenario computation runs unchanged. Every validator and renderer keeps calling
  it; no second site computes a ceiling.
- **An entered cap is bounded only by what is known.** Validation accepts an integer from 1 to the engine
  `hard_cap`. No floor at the scenario base is imposed, because the tool holds no source claiming a client
  ceiling can never sit below it, and inventing such a claim is the error this decision exists to avoid.
- **It is Trainer data, so it is never engine-owned.** No `data_sources` row is written for a cap and the fetch
  engine never touches the columns, the same rule `is_manual` rows carry (`AGENTS.md` §8).
- **Wit is excluded.** Its scenario base is 1800 and no entered `cap_wit` column is added. The owner's ruling
  named the entry surfaces; it did not extend to Wit, and a column with no ruled writer would be a schema for a
  fact nobody may enter.
- **Entered on two surfaces.** The build target step, because that is where a target is refused today and the
  wizard must carry the values into the run at Start Career; and the Cockpit identity form, so a cap corrected
  mid-run feeds the turn form's bound without recreating the run.
- **Null reverts, it does not zero.** Clearing an entered cap returns the ceiling to the scenario base,
  byte-identical to the behaviour before this decision. `ADR-0002`'s UI condition still holds: the 1,200
  halved-gains marker and the run's ceiling stay two separate visible markers.

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

When a cap is entered, the number the Trainer read outranks the arithmetic. `ScenarioCaps` reports which of the
two answered, so a surface that explains a bound can name the entered cap rather than implying the scenario
produced it.

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
