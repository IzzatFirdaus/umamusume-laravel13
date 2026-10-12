# ADR-0001: Lift the no-prediction non-goal for Energy guidance

<!-- ADR-CONTRACT
  decision: accepted in part (owner ruling 2026-09-27) — §6.11 is lifted for Energy guidance only; §5 (schema) is superseded by ADR-0003
  code: built — config/advisor.php constants; TrainerAdvisor; tests/Feature/TrainerAdvisorTest.php
  superseded-by: —
  amended-by: 0003 (§5 schema), 0020 (widens the §6.11 lift to target-based recommendation), 0024 (extends the turn_entries.energy column)
  open: owner edits to PRD.md and CLAUDE.md were never landed; the optional numeric failure estimate is unbuilt by design
-->

Status: **accepted in part** (owner ruling 2026-09-27). The Energy decision stands. §5 (schema) is
superseded by `ADR-0003`; `ADR-0020` later widened the §6.11 lift from Energy guidance to target-based
training recommendation.

## Context

The consolidated tool descends from a legacy app that shipped race-day predictions and immutable
snapshots. Those were cut. `PRD.md` §6.11 records the cut as a non-goal: *"No race simulation, prediction
engine, or race-day snapshots… run math stays deterministic over Trainer-entered turns."* `CLAUDE.md`
reinforced it with three Planner Domain Rules: no speculative simulation (R1), every derived number is a
pure function of entered `turn_entries` (R4), and no unexplained recommended numbers (R5).

The owner directed that the tool manage Energy actively, including recommending turn sequences and
surfacing failure risk, on the grounds that Energy management is the core loop of a training run. This is
a scope decision the owner is entitled to make, recorded here because it reverses a documented non-goal.

## Decision

Lift §6.11 **for Energy guidance only**. It continues to apply in full to race outcomes.

In scope: store per-turn Energy and Mood tier as Trainer-entered facts; render an Energy gauge with the
sourced 50-point advisory band; rank and suggest training actions for the next turn; surface
training-failure risk.

Explicitly out of scope, unchanged by this decision:

- Race simulation, race result prediction, race-day snapshots (`PRD.md` §6.11, unmodified)
- Support-card deck optimisation, gacha or Scouts modelling (`PRD.md` §6.9)
- Any figure presented as a game mechanic that is not sourced

R4 is replaced by a disclosure requirement, because Energy guidance cannot be a pure function of entered
turns: **every derived number is a pure function of entered `turn_entries` plus a declared, versioned
constant set, and the constants used are visible wherever the number appears.** The constant set, with
each value's source and read date, is owned by `config/advisor.php` (FR-F-2) and is not restated here.

## What this does not license

- **No failure percentage as a game fact.** No source publishes a curve, table, or single probability
  value; the sources state only that failure probability "scales inversely with Energy". The shipped
  surface is a three-band indicator (Safe / Caution / Danger) classified against the sourced 50 line.
- **A numeric estimate is opt-in and unbuilt.** If ever enabled it must render with its formula and
  parameters on the same surface, be labelled as this tool's model rather than the game's, and live in
  config. Shipping "23% risk" as bare UI copy would be a fabricated statistic.
- **No risk band for Wit.** Energy is the band's only input and a Wit session enters it with full Energy,
  so the rule would render Wit `Safe` at every level, asserting an exemption a source conflict leaves
  open. A Wit row renders `Risk not measured`, not Safe; low-risk and risk-free are different claims.
- **The 30-Energy "highly dangerous" line is not in the sources** and must not appear as game fact.
- **Recommendation must be explainable at the row level.** A suggestion with no derivable reason line
  naming the arithmetic does not ship (R5 carried forward).

## Options considered and rejected

- **Record and visualise only, no recommendations.** Rejected by the owner: keeps every rule intact but
  gives no guidance.
- **Recommend without storing Energy, inferring it from action types.** Rejected on correctness: the
  inference is lossy (Wit recovers a variable amount, events raise the maximum), so the derived series
  would drift and produce confident wrong advice.
- **Full probability estimates as primary UI.** Rejected on evidence, not policy: there is no curve to
  implement, and presenting an invented one would be the failure the pre-mortem exists to prevent.

## Links

- `PRD.md` §6.11, FR-C-2, FR-F-2, US-3; `config/advisor.php` (the constant set, versioned and dated)
- `App\Enums\MoodTier` (the five client strings); `docs/UMAMUSUME_REFERENCE.md` §1.1.6
- `CLAUDE.md` Planner Domain Rules; `AGENTS.md` Planner Domain Specialist, escalation paths 2 and 4
- `docs/research-scratch/GOVERNANCE.md` §"PRE-MORTEM.md" §4.1

## Ledger

- 2026-09-27 · **terminology conflict resolved** — the brief used the Game8 glosses
  Peak / Good / Normal / Poor / Worst; the client's own Mood Effect panel prints
  `GREAT`/`GOOD`/`NORMAL`/`BAD`/`AWFUL`. The `MoodTier` cases are the client strings, and
  the five-tier list lives in `App\Enums\MoodTier`, not here. Kept because the false claim
  (the glosses) is what D-20's "no unverified item in UI copy" rule was applied to.
- 2026-10-09 · **note, appended by the provenance/absence contract pass** — the optional numeric
  estimate described above is unbuilt, and unbuilt because there is nothing lawful for it to model:
  the only figure wanting an estimate is a race outcome, which §6.11 with `ADR-0016` keeps out of
  reach. `failure_estimate` stores an `off`/`on` preference gating no rendering
  (`tests/Feature/PreferenceControlsTest.php:24`); `TrainerAdvisor` ships the two bands. The owner
  ratified on 2026-10-09 that the `Estimated` badge means a weak-source figure, not this section's
  opt-in estimate.
- 2026-09-27 · **§5 schema questions superseded** — the `energy`/`mood` column table and the
  `condition`-versus-`mood` question were both resolved by `ADR-0003`'s consolidated schema, which
  is the owner of those columns.
