# ADR-0004: Store aptitude letters and scenario stat caps as reference data

Status: **accepted by owner 2026-09-27**, as the "Schema Slice" step following the config and
reference-document steps. Closes `PRD.md` OQ-4 in the "in Phase 2 as engine-owned facts" direction.
Date: 2026-09-27
Deciders: product owner (approval), implementing agent (column definitions)
Relates to: `ADR-0002` (cap bound), `ADR-0003` (Phase 1 schema expansion), `PRD.md` FR-A, FR-B, US-1,
`UMAMUSUME_REFERENCE.md` Sections 2.1, 2.2, 2.7, 3.1

## Problem

The catalog could not answer two questions a Trainer asks before planning a run.

1. Which distances and running styles is this Umamusume good at? The reference document publishes ten
   aptitude letters per unit, confirmed cell by cell against two publishers, and the `umamusume` table
   stored none of them.
2. What is the highest a stat can reach in a given scenario? `ADR-0002` records that every live
   scenario caps above the `0..1200` bound the app validates against, so the stored bound and the game
   disagree, and the app had no per-scenario figures to consult.

`PRD.md` OQ-4 named both as undecided and forbade schema until decided. This ADR is that decision.

## Decision

Store both as **reference data reached through the fetch pipeline**, not as hand-written seeders and
not as simulation inputs.

| Object          | Where                                                                       | Columns                                                                                                                           |
| --------------- | --------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------- |
| Aptitudes       | `umamusume`, ten nullable `char(1)` columns in the export's element order   | `aptitude_turf`, `aptitude_dirt`, four distance, four running style                                                               |
| Scenario caps   | new `scenarios` table                                                       | slug, names, order, both server start dates, five caps, `hard_cap`, `caps_reworked_at`, `source_url`, `fetched_at`, `is_manual`   |

Aptitudes ride the existing `SourceParser` record shape through `PipelineRunner` and
`PromoteMatchedRecord`, so every value keeps a `DataSource` provenance row like the rest of the catalog
and obeys the `is_manual` immutability rule. `GametoraScenarioParser` deliberately implements its own
`ScenarioSourceParser` interface rather than `SourceParser`: scenarios have no display name to
cross-reference against Trainer-owned rows, so routing them through the match and review stages would
queue noise for a human to dismiss.

## Alternatives rejected

- **Seeding the 135 units and 14 scenarios by hand.** Rejected because `UmamusumeSeeder` states the
  rule in its own comment, engine-owned facts arrive only through `uma:fetch` with provenance, and
  `AGENTS.md` deletes a stored fact that cannot name its source. A hand-seeded table also has nothing
  to re-run when the publisher adds content.
- **Computing caps at read time from a formula in the UI.** Rejected because the formula would then be
  untested and invisible; it lives in the parser with a constant and a test naming each of the four
  Global scenarios it reproduces.
- **A generic polymorphic provenance table.** Rejected as scope creep. `DataSource` is
  `umamusume_id`-scoped today, so scenario rows carry `source_url` and `fetched_at` inline instead.
  Generalising provenance is a separate decision if a second reference domain ever needs it.

## Consequences

- `cap_*` values are **derived**, not copied: the dataset publishes a per-stat bonus over a base cap of
  1200, and `1200 + bonus` reproduces the four Global scenario rows figure for figure, which is now a
  parametrised test rather than a claim in prose. The base is a named constant for that reason.
- `caps_reworked_at` is set only for a scenario that reached `[Global]` before 2026-07-01, because a
  later launch ships already raised. It is null for `[JP-Only]` rows, where the `[Global]` rework does
  not apply, and null means "not asserted", never "no rework happened".
- **The `0..1200` validation bound is not widened by this change.** `ADR-0002` Option B is accepted,
  but `StoreTurnEntryRequest` and `PRD.md` FR-C-2 still say 1200 and `US-3` still uses "a stat outside
  0..1200 is rejected" as an acceptance test. Changing it edits a requirement and its test, which needs
  the owner's wording, so it stays open as the next decision rather than being settled inside a schema
  change.
- Nothing here computes a race, a training outcome or a probability. The app's rule that every number
  is explainable from Trainer-entered turns is untouched.
- Scenario rows have no UI and no foreign key from `training_runs` yet. FR-C-1 stores a free-text
  scenario name today, and repointing it at `scenarios` is a separate migration with its own data
  question, so no relationship was invented here.

## Verification

`php artisan test --compact` covers the derivation for all four Global scenarios, the rework-date rule,
malformed dataset handling for both parsers, aptitude persistence through the pipeline with provenance,
and the rule that a body without aptitudes never blanks stored grades.

## Erratum — 2026-10-09 (D8, Rice Shower / Unity Cup UX walk remediation)

The Decision above names the export as the source and the columns as "the export's element order", and
it was never wrong. What it did not state — and what a UX walk found it needed to state — is which
reading wins when a scenario document and the stored catalogue disagree. Recorded here so the answer is
a standing ruling rather than a judgement made per incident:

- **The GameTora export is the authoritative catalogue source for a trainee's ten aptitude letters.**
  They arrive through `uma:fetch` carrying a `DataSource` provenance row and stand under the `is_manual`
  immutability rule — precisely the machinery this ADR chose over hand-written values.
- **A scenario document is a secondary reference.** Where the two disagree, the catalogue holds and the
  document is corrected by its own dated erratum. A document transcribes one client frame at one date;
  it carries no provenance row and nothing re-runs it when a publisher changes content, which is the
  reason it was rejected as a seeding route above.
- **A disagreement is recorded as open, not resolved in the display path.** No per-trainee UI exception,
  and no edit of stored data to match a document: `AGENTS.md` §5 gates the display path and forbids
  renaming or rewriting dataset values to satisfy a reading.

Applied once so far: D8 of `docs/audits/rice-shower-unity-cup-ux-walk.md` reported the picker showing
Mile C / Front B / Late C against the `[Rosy Dreams]` document's B / A / B. The committed export
(card `103001`) carries `["A","G","E","C","A","A","B","A","C","G"]`, the seed and the mapping reproduce
it letter for letter, and `TraineeSelectController::aptitudes()` is faithful — so the catalogue stands,
the document carries the erratum, and the disagreement is open rather than corrected. All ten letters
are pinned by `CareerTraineeSelectTest` (commit `be48e60`).
