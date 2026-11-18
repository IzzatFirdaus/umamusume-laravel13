# ADR-0034: The Team Rank is a stored current state, read ahead of the turn-event history

<!-- ADR-CONTRACT
  decision: accepted (owner ruling R6-48, 2026-11-15) — training_runs.team_rank holds the run's current league letter as one value; the turn-event path stays for the editor's write route, and one accessor reads the column first and falls back to the latest reported event
  code: built — migration 2026_11_15_143000_add_team_rank_to_training_runs (with down()); TrainingRun::latestTeamRank(), TrainingRun::booted() vocabulary guard; tests/Feature/CockpitTeamPanelTest.php, ScenarioPanelTest.php, DashboardTest.php
  superseded-by: —
  amended-by: —
  open: S+ is accepted by the column and maps to no facility level, so a panel drawing a ladder must print the letter without inventing a rung
-->

Status: **accepted** (owner ruling R6-48, 2026-11-15), recorded in
`docs/audits/browser-observation-2026-11-15-followup/08-r6-48-review-prep.md` §"Decision (owner ruling, 2026-11-15)".
The agent drafts an ADR, the owner accepts it; this one's ruling predates the draft and is quoted, not re-derived.

## Context

The Unity Cup's Team Info panel prints a league rank letter. The tree stored it only as a turn event, on the
reading that a rank belongs to the turn it was observed. The source says something narrower: the panel is a
status readout, not a performance record, so the rank is the run's *current* standing.

Event-sourcing one letter had three measured costs. The Cockpit panel had to resolve the current letter out of the
event stream, a run with no reported event rendered an absence even when the Trainer knew the rank, and the header
strip, the Team panel, the gauge and the Dashboard tile could each resolve a different letter from the same run.

## Decision

**`training_runs.team_rank` is the stored current state.** A nullable string holding one of
`TeamRankPayload::LETTERS` (`G` through `S+`), validated by the model's `saving` guard rather than by a DB enum,
because the vocabulary has one owner and SQLite has no `CHECK` through the builder.

- **One accessor, two stores, one priority order.** `TrainingRun::latestTeamRank()` returns the column when it is
  set and otherwise the latest reported turn event, so the four surfaces read one method and cannot disagree. It
  refuses for a run whose scenario does not compose the rank panel.
- **The turn-event path is kept, not migrated away.** The editor's write route still writes `TeamRankPayload`, and
  a run whose letter predates the column resolves through the fallback. Nothing back-fills the column from events.
- **`S+` is accepted and maps to no facility level.** It sits above `S` and grants a second hint rather than a
  higher facility, so the ladder gives it no rung and a panel must print the letter without inventing one. The
  per-stat path still rejects `S+`: it is an overall rank, not a per-stat grade.
- **Null is an absence.** A run that has not stated a rank renders the absence row, never a default letter.

## What this does not license

- **No event back-fill.** Writing the column for runs whose only letter is an event would create a second
  migration step whose correctness no test could check against the original observation.
- **No widening to a per-turn history.** The column is the current state; per-turn ranks stay in `turn_events`,
  and a surface wanting a history reads events.
- **No per-stat use of `S+`.** `ADR-0029` B-0.5's five grades stay `G`..`S`.

## Links

- `ADR-0029` (B-0.5, the per-stat grades this sits beside and deliberately differs from), `ADR-0003` (turn_events)
- `PRD.md` FR-C-1, US-10; `UMAMUSUME_REFERENCE.md` §1.8
- `app/Models/TurnEvents/TeamRankPayload.php` (`LETTERS`, `S+` note); `TrainingRun::latestTeamRank()`,
  `TrainingRun::facilityLevelForStat()`
- `docs/audits/browser-observation-2026-11-15-followup/08-r6-48-review-prep.md`
- `tests/Feature/CockpitTeamPanelTest.php`, `tests/Feature/ScenarioPanelTest.php`, `tests/Feature/DashboardTest.php`

## Ledger

- 2026-11-18 · **drafted** — the column shipped at `a4d5d673` on the owner's 2026-11-15 ruling and was drafted here
  as an ADR afterwards, so the ruling's date and this record's date differ by three days. Nothing in the decision
  changed; the draft adds the reciprocal relation to `ADR-0029` and the digest line's citation target.
