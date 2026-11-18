# ADR-0031: The maiden lock is one run-level state, not a per-race column

<!-- ADR-CONTRACT
  decision: proposed — the maiden gate is one run-level question ("has this run won the Debut or a Maiden?"), asked once per calendar and applied to every standard race; the three per-race gate columns are dropped
  code: built — migration 2026_10_27_120000_drop_per_race_maiden_gate_columns (with down()); TrainingRun::hasClearedMaidenLock(), TrainingRun::maidenGates(); tests/Feature/Schema/MaidenGateColumnDroppedTest.php, tests/Feature/CareerRaceDecisionTest.php
  superseded-by: —
  amended-by: —
  open: the turn-12 fallback for imported runs is inferred from where the Debut sits in the calendar, not read from a stored fact; whether the owner records this as Accepted is still open
-->

Status: **Proposed.** Drafted 2026-11-18 from shipped code and tests; the schema landed at `8ffee1a4` (2026-10-27)
without a decision record. **Nothing here binds until the owner records it as Accepted.** Amends the three race
tables `ADR-0003` creates.

## Context

The source states the gate once, on the maiden row, as a sentence about every standard race at once: a Trainer
cannot enter any of the listed races until a Debut or any Maiden has been won. It is not a flag the export sets
per race (`UMAMUSUME_REFERENCE.md` §1.2.6).

The tree modelled it per row anyway. `race_catalog_slots`, `scenario_slots` and `scenario_races` each carried a
boolean, and no writer ever set it to true: the catalogue parser seeded `false` and the scenario seeder seeded
`false`. So the lock existed as three columns, three model casts, a parser field, a seeder field and two factory
states, and it never fired. The measured consequence was a Race desk that offered graded races a winless run
could not enter, with no gate line to explain why.

## Decision

**One run-level predicate, asked once per surface.** `TrainingRun::hasClearedMaidenLock()` answers whether this
run has won either gate-clearing race, by a completed first-place finish in the grade code the Debut and the
Maiden carry. `TrainingRun::maidenGates($slot, $cleared)` applies that one answer to a calendar row, and only to
the standard grades: the gate-clearing races themselves and any row with no standard grade carry no gate.

- **The cleared state is passed in, not queried per row.** Callers ask `hasClearedMaidenLock()` once and hand the
  boolean to `maidenGates()`, so a calendar loop is one read rather than one per row. The planner's read-count test
  pins the calendar to a single read.
- **The gated set is the five standard grades.** `maidenGates()` gates grade codes 100 (G1), 200 (G2), 300 (G3), 400
  (OP) and 700 (Pre-OP) and nothing else: 800 and 900 are the gate-clearing races themselves, and a row with no
  standard grade, such as a scenario final, carries no gate.
- **Imported runs use the calendar position.** A run imported mid-career holds no entries for the turns it never
  played, so a record-only check would leave every standard race locked for the life of the run. The method
  therefore treats a position past the Debut's own slot as cleared, because the client's gate is absolute and the
  Debut is scheduled.
- **The three columns are dropped**, with their parser field, seeder field, factory states, model casts and the
  `Database/Races.vue` column. `MaidenGateColumnDroppedTest` pins their absence independently of whether the
  migration has run against any particular database.
- **A gated row still renders.** The surfaces print the reason ("the maiden lock is not cleared") and keep the
  row visible and comparable rather than hiding it.

## What this does not license

- **No per-race override.** A future need to un-gate one race is a new ruling, not a column quietly restored.
- **No inference from fan totals or grade points.** The gate reads one win; it does not estimate a run's strength.
- **No win prediction.** `PRD.md` §6.11 is untouched: the gate is a recorded-and-read rule, not a forecast.

## Links

- `ADR-0003` (the three tables whose per-race gate columns this drops; its `amended-by` records the relation)
- `PRD.md` US-10; `docs/UMAMUSUME_REFERENCE.md` §1.2.6
- `TrainingRun::hasClearedMaidenLock()`, `TrainingRun::maidenGates()`; `Career/RaceDecisionController`,
  `Career/RacePlannerController`
- `tests/Feature/Schema/MaidenGateColumnDroppedTest.php`, `tests/Feature/CareerRaceDecisionTest.php`,
  `tests/browser/career-race-planner.spec.ts`
- Round-5 reconciliation: `docs/audits/reconciliation-rounds/rice-shower-unity-cup-round5-reconciliation.md`
  (R5-07, and R5-32's dependency on it)

## Ledger

- 2026-11-18 · **drafted** — the columns had shipped on 2026-10-27 (`8ffee1a4`) with the migration's own docblock
  as the only record of the reasoning. This ADR is written after the fact from the code, the tests and §1.2.6;
  it does not claim the decision preceded the implementation, and its status is the owner's to set.
