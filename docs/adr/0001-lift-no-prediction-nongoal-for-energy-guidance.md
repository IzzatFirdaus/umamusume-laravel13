# ADR-0001: Lift the no-prediction non-goal for Energy guidance

Status: **accepted in part.** The Energy decision stands. §5 (schema) is superseded by `ADR-0003`, which consolidates Energy, Fans, `turn_events` and race tracking into one amendment. Owner edits to PRD.md and CLAUDE.md are still outstanding.
Date: 2026-09-27
Deciders: product owner (decision), design consultant (recording and consequences)
Supersedes: `PRD.md` §6.11 (partial), `CLAUDE.md` Planner Domain Rules 1, 4, 5 (partial)

## Context

The consolidated tool descends from a legacy app that shipped race-day predictions and immutable snapshots. Those were cut. `PRD.md` §6.11 records the cut as a non-goal: *"No race simulation, prediction engine, or race-day snapshots… run math stays deterministic over Trainer-entered turns."* `CLAUDE.md` reinforces it with three Planner Domain Rules: no speculative simulation (R1), every derived number is a pure function of entered `turn_entries` (R4), and no unexplained recommended numbers (R5).

The owner has since directed that the tool should manage Energy actively, including recommending turn sequences and surfacing failure risk, on the grounds that Energy management is the core loop of a training run and a tracker that cannot warn about it omits the thing a Trainer actually needs. This is a scope decision the owner is entitled to make. It is recorded here because it reverses a documented non-goal, and `AGENTS.md` requires an ADR when one changes.

## Decision

Lift §6.11 **for Energy guidance only**. It continues to apply in full to race outcomes.

Explicitly out of scope, unchanged by this decision:

- Race simulation, race result prediction, race-day snapshots (PRD §6.11, unmodified)
- Support-card deck optimisation, gacha or Scouts modelling (PRD §6.9)
- Any figure presented as a game mechanic that is not sourced

In scope:

1. Store per-turn Energy and Mood tier as Trainer-entered facts.
2. Render an Energy gauge with the sourced 50-point advisory band.
3. Rank and suggest training actions for the next turn.
4. Surface training-failure risk.

## Consequences

### 1. Documentation now contradicts the design and must be amended by the owner

This ADR does not itself change the governing documents, and the design must not be built against a doc set that forbids it. Required before implementation:

- `PRD.md` §6.11: narrow to race outcomes, and add a functional requirement for Energy. Nothing in FR-A through FR-E currently covers it.
- `CLAUDE.md` Planner Rules 1, 4, 5: Rule 1 must permit action recommendation; Rule 4 must permit derivation from declared external constants, not entered turns alone; Rule 5 must permit an advisory figure whose formula is shown.
- `AGENTS.md`: the Planner Domain Specialist role description says "no race predictions… deterministic run math". That line needs the same narrowing.
- `docs/PRE-MORTEM.md` §4.1 records why the legacy prediction system was cut. Re-read it against this decision rather than assuming it still applies unchanged.

### 2. Rule 4 cannot be satisfied literally, so it is replaced by a disclosure requirement

R4 says every derived number is a pure function of `turn_entries`. Energy guidance cannot be: it needs the game's cost and recovery constants, which live outside the database. Restricting guidance to entered turns would produce a recommender that knows nothing about what a Rest actually returns.

Replacement, proposed for R4: **every derived number is a pure function of entered `turn_entries` plus a declared, versioned constant set, and the constants used are visible wherever the number appears.** Deterministic and testable, but honest that it reads a table as well as the run.

The constant set, with the citation each one carries. These are the only values the engine may use:

| Constant | Value | Source in `UMAMUSUME_REFERENCE.md` | Confidence |
|---|---|---|---|
| Session Energy cost | 17-28, scaling with training level | §1.1.6, GameWith | ⚠ STALE, measured 2023-02-25 |
| Rest recovery | +30 | §1.1.5, GameWith 2026-09-25 | current |
| Wit session cost | 0, recovers a small amount | §1.1.1, Game8 2026-09-10 | current |
| Advisory threshold | 50 Energy | §1.1.5: success rate 「大きく変わってくる」 | current, **qualitative only** |
| Mood multipliers | +20 / +10 / 0 / −10 / −20 % | §1.1.6, Kamigame + GameWith | corroborated |
| Injury stat hit | −5 to −10 | §1.1.5, GameWith | ⚠ STALE, 2023-02-25 |
| Maximum Energy raise from events | +12 | §1.1.6, GameWith 2026-09-25 | current |

### 3. A failure percentage cannot be sourced, so it ships as a band and the number is opt-in

This is the sharpest limit on the decision and it is a data problem, not a policy objection. The sources state only that failure probability "scales inversely with Energy" (§1.1.5). **No source publishes a curve, a table, or a single probability value.** The reference doc explicitly records "no published threshold beyond the 50 Energy guide", and separately flags that whether Wit training can fail at all is unsettled between sources (Source Conflict Log row 2).

Therefore:

- **Default: a three-band risk indicator**, Safe / Caution / Danger, classified against the sourced 50 line. A band is a stated threshold applied to a stored value, which is defensible.
- **Optional: a numeric estimate, off by default.** When enabled it must render with its formula and parameters visible on the same surface, be labelled as this tool's model rather than the game's, and be stored in config rather than hardcoded. Shipping "23% risk" as bare UI copy would be a fabricated statistic, which `AGENTS.md` forbids for the Docs Writer and the Lore Guardian alike for every other artifact.
- The 30-Energy "highly dangerous" line quoted in the owner's brief is **not in the sources** and must not appear as game fact. If the owner wants a Danger band below 30, it is recorded here as an owner ruling and attributed as such.

### 4. Recommendation must be explainable at the row level

A suggested sequence is allowed, but the UI must be able to answer "why this" from the constants above. The accepted pattern is a per-suggestion reason line naming the arithmetic: *"Wit costs 0 Energy and you are at 42."* A suggestion with no derivable reason does not ship. This is R5 carried forward in a workable form rather than deleted.

### 5. Schema additions required

`turn_entries` currently holds turn, five stats, `sp`, and `condition`. Energy guidance needs:

| Column | Type | Notes |
|---|---|---|
| `energy` | unsigned smallint, nullable | 0-100. Nullable because historical runs were logged without it and must not be backfilled with guesses |
| `mood` | string, nullable, enum-backed | `MoodTier`: Peak / Good / Normal / Poor / Worst |

Two notes on this table:

- **Negative stat changes need no schema change.** The model stores absolute per-turn values, so an injury is already representable as turn 12 Speed 302, turn 13 Speed 294, and the timeline renders the drop as a blue decrease. This was raised in the brief as a gap and is not one.
- **`mood` overlaps the existing `condition` string.** Either `condition` is retired in favour of the enum, or it stays as free text and `mood` is added beside it. This is unresolved and is the one schema question that should be answered before a migration is written.

### 6. Terminology conflict, currently blocking UI copy

The brief uses Peak / Good / Normal / Poor / Worst. Those are Game8's English **glosses** of 絶好調 / 好調 / 普通 / 不調 / 絶不調. `UMAMUSUME_REFERENCE.md` §1.1.6 records the exact Global client strings as **❌ UNVERIFIED**, and the screenshot corpus shows the client actually rendering `GREAT` and `GOOD` on its mood pill.

`docs/design-research/CONSTRAINTS.md` D-20 forbids promoting an unverified item into UI copy. Resolve by one of: capture the remaining three pills from the client, accept the Game8 glosses as an explicit owner override to D-20, or use the enum names without claiming they are client strings.

### 7. Test surface grows, and the stale constants are the risk

The recommender is deterministic arithmetic over a declared table, so it is straightforwardly unit-testable, which is the silver lining of keeping the constants explicit. The exposure is that the two most load-bearing constants, session cost 17-28 and injury −5 to −10, come from a **2023-02-25 source flagged stale**. If those drifted, recommendations drift with them. Mitigation: the constants are versioned and dated in config, every suggestion displays the date of the data behind it, and a re-verification task is implied at implementation time.

## Alternatives considered

**Record and visualise only, no recommendations.** Rejected by the owner. It keeps every rule intact and is still a large improvement over the current design, at the cost of a schema change and no guidance.

**Recommend without storing Energy, inferring it from action types.** Rejected on correctness: the inference is lossy, since Wit recovers a variable amount and events raise the maximum, so the derived series would drift from the real one and produce confident wrong advice. Worse than the option it was meant to preserve.

**Full probability estimates as primary UI.** Rejected on evidence, not on policy. There is no curve to implement. Presenting an invented one as a game mechanic would be the exact failure mode the pre-mortem was written to prevent.

## Links

- `PRD.md` §6.11, FR-C-2, US-3
- `CLAUDE.md` Planner Domain Rules 1, 4, 5, 6
- `AGENTS.md` Planner Domain Specialist, escalation paths 2 and 4
- `docs/PRE-MORTEM.md` §4.1
- `docs/UMAMUSUME_REFERENCE.md` §1.1.1, §1.1.5, §1.1.6, Source Conflict Log row 2
- `docs/design-research/CONSTRAINTS.md` D-20, D-35, D-36
