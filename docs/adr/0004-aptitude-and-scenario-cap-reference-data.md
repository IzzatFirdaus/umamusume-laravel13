# ADR-0004: Store aptitude letters and scenario stat caps as reference data

<!-- ADR-CONTRACT
  decision: accepted (owner ruling 2026-09-27) — aptitudes and scenario caps arrive as engine-owned reference data through the fetch pipeline
  code: built — migrations 2026_09_27_090100 (scenarios), 2026_09_27_090000 (aptitudes); GametoraScenarioParserTest; CareerTraineeSelectTest
  superseded-by: —
  amended-by: 0015 (the scenarios cap row's read path, per-stat), 0012 (the profile table, same provenance pattern), 0025 (does not reopen the cap reference data); settled ADR-0002's withdrawn base/bonus bullet by shipping absolute caps
  open: the scenarios table has no FK from training_runs yet; repointing the free-text scenario name is a separate migration
-->

Status: **accepted by owner 2026-09-27**, as the "Schema Slice" step following the config and
reference-document steps. Closes `PRD.md` OQ-4 in the "in Phase 2 as engine-owned facts" direction.

## Context

The catalog could not answer two questions a Trainer asks before planning a run: which distances and
running styles a unit is good at, and what the highest a stat can reach in a given scenario is.
`PRD.md` OQ-4 named both as undecided and forbade schema until decided. This ADR is that decision.

## Decision

Store both as **reference data reached through the fetch pipeline**, not as hand-written seeders and not as
simulation inputs. Aptitudes ride the existing `SourceParser` record shape through `PipelineRunner` and
`PromoteMatchedRecord`, so every value keeps a `data_sources` provenance row and obeys the `is_manual`
immutability rule. The tables and columns are owned by migrations `2026_09_27_090000` (aptitudes on
`umamusume`) and `2026_09_27_090100` (`scenarios`), and are not restated here.

`GametoraScenarioParser` deliberately implements its own `ScenarioSourceParser` interface rather than
`SourceParser`: scenarios have no display name to cross-reference against Trainer-owned rows, so routing
them through the match and review stages would queue noise for a human to dismiss.

**`cap_*` values are derived, not copied.** The dataset publishes a per-stat bonus over a base cap of 1200,
and `1200 + bonus` reproduces the four Global scenario rows figure for figure. That is asserted by
`tests/Feature/GametoraScenarioParserTest.php` rather than claimed in prose, and the agreement between
`config/scenarios.php` and the `scenarios` row is asserted by
`tests/Feature/ScenarioCapParityTest.php`.

## Alternatives rejected

- **Seeding the units and scenarios by hand.** Rejected: `UmamusumeSeeder` states the rule in its own
  comment, engine-owned facts arrive only through `uma:fetch` with provenance, and a stored fact that
  cannot name its source is deleted. A hand-seeded table also has nothing to re-run when the publisher adds
  content.
- **Computing caps at read time from a formula in the UI.** Rejected: the formula would be untested and
  invisible. It lives in the parser with a constant and a test naming each of the four Global scenarios it
  reproduces.
- **A generic polymorphic provenance table.** Rejected as scope creep. `data_sources` is
  `umamusume_id`-scoped today, so scenario rows carry `source_url` and `fetched_at` inline instead.
  Generalising provenance is a separate decision if a second reference domain ever needs it.

## Consequences

- `caps_reworked_at` is set only for a scenario that reached `[Global]` before 2026-07-01, because a later
  launch ships already raised. It is null for `[JP-Only]` rows, where the `[Global]` rework does not apply,
  and null means "not asserted", never "no rework happened".
- **The `0..1200` validation bound is not widened by this change.** `ADR-0002` option B is accepted, but
  changing it edits a requirement and its test, which needs the owner's wording; `ADR-0015` later settled
  it.
- Nothing here computes a race, a training outcome or a probability.
- Scenario rows have no UI and no foreign key from `training_runs` yet. `FR-C-1` stores a free-text
  scenario name today, and repointing it at `scenarios` is a separate migration with its own data question,
  so no relationship was invented here.

## The catalogue-versus-document rule (standing ruling, 2026-10-09)

Which reading wins when a scenario document and the stored catalogue disagree:

- **The GameTora export is the authoritative catalogue source for a trainee's ten aptitude letters.** They
  arrive through `uma:fetch` carrying a `data_sources` provenance row and stand under the `is_manual`
  immutability rule.
- **A scenario document is a secondary reference.** Where the two disagree, the catalogue holds and the
  document is corrected by its own dated erratum. A document transcribes one client frame at one date; it
  carries no provenance row and nothing re-runs it when a publisher changes content, which is the reason it
  was rejected as a seeding route above.
- **A disagreement is recorded as open, not resolved in the display path.** No per-trainee UI exception,
  and no edit of stored data to match a document (`AGENTS.md` §5 gates the display path and forbids
  renaming or rewriting dataset values to satisfy a reading).

Applied once so far: D8 of `docs/audits/rice-shower-unity-cup-ux-walk.md` reported the picker showing
Mile C / Front B / Late C against the `[Rosy Dreams]` document's B / A / B. The committed export carries
`["A","G","E","C","A","A","B","A","C","G"]`, the seed and the mapping reproduce it letter for letter, and
`TraineeSelectController::aptitudes()` is faithful — so the catalogue stands, the document carries the
erratum, and the disagreement is open rather than corrected. All ten letters are pinned by
`CareerTraineeSelectTest` (commit `be48e60`).

## Links

- `PRD.md` OQ-4, FR-A, FR-A-5, FR-B, FR-C-1, FR-C-2, US-1, US-3
- `ADR-0002` (the bound), `ADR-0003` (Phase 1 schema), `ADR-0012` (the same provenance pattern on the
  profile table), `ADR-0015` (the cap read path)
- `tests/Feature/GametoraScenarioParserTest.php`, `tests/Feature/CareerTraineeSelectTest.php`,
  `tests/Feature/ScenarioCapParityTest.php`
- `docs/UMAMUSUME_REFERENCE.md` §1.3.4, §2.1, §2.2, §2.7, §3.1
