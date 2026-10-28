# VERSION

Current version: `3.0.0`, pre-release. Tagged: `0.1.0`, `2.0.1`, `2.0.2`, `2.1.0`, `2.1.1`.

| Field            | Value                         | Measured                                                                      |
| ---------------- | ----------------------------- | ----------------------------------------------------------------------------- |
| Version          | `3.0.0`                       | MAJOR for the HFW-parity landing (2026-10-27): the frontend was re-shaped slice by slice against the wireframes, which changes every screen's composition, while the three public surfaces `VERSION.md` names are untouched (no route, schema or `lang/en/uma.php` change in the release). The number stands per the plan's §10, and `ADR-0019` remains **Advisory** — labelling is owner-directed. `2.1.1` was the 2026-10-24 PATCH; `2.1.0` was MINOR for the `review-detail` route; `2.0.1`/`2.0.2` were the 2026-10-22 UX-audit fixes |
| Pre-release      | yes           | GitHub Release flag, not a semver hyphen identifier                           |
| Git tags         | `0.1.0`, `2.0.1`, `2.0.2`, `2.1.0`, `2.1.1` | `git tag`, `git ls-remote --tags origin`; no `2.0.0` tag exists locally or on the remote. `2.0.1` is `da15a5ba`, `2.0.2` is `e3ac31bd`, `2.1.0` is annotated on its release commit. **`3.0.0` is not tagged** — `git tag -l '3.0.*'` is empty; the 2026-10-28 parity audit found 32 divergent, 1 blank and 17 near pages, so the tag waits for the repair pass and its re-audit. Whoever cuts it updates this row and the Release commit row. |
| Release commit   | not fixed                     | see Release state below                                                       |
| Framework        | `laravel/framework` 13.32.0   | `composer.lock`, constraint `^13.0`                                           |
| PHP              | 8.5.8                         | runtime, against `require.php: ^8.3`                                          |

## Why 0.y.z

Semantic Versioning 2.0.0 item 5 reserves `0.y.z` for initial development, where anything may change and
the public API is not stable. Item 6 makes `1.0.0` the release that defines the public API. `ADR-0019`
records that this project has not defined one: `PRD.md` carries **four open/partially-resolved questions**,
`ADR-0001` is accepted in part with its schema section superseded, and `KNOWN-ISSUES.md` carries open
entries through **KI-98**.

## What the digits mean here

The public API is three surfaces and nothing else.

1. The JSON endpoints under `/api/v1`, declared by `Route::prefix('v1')` in `routes/api.php`.
2. The SQLite schema the Trainer's own data lives in.
3. The displayed vocabulary in `lang/en/uma.php`.

- MAJOR moves when a migration invalidates existing local Trainer data rather than adding to it, or when
  an `/api/v1` field is removed or renamed.
- MINOR is additive: a new endpoint, a new screen, a new nullable column, new seeded catalog data.
- PATCH is a fix: a bug, a copy correction, or a dependency bump that changes nothing observable.

Blade internals, screen layout, and `.ai/rules` are not a public API.

## Gate to 1.0.0

A condition, not a date. All three hold before the label moves: the **four** open/partially-resolved
questions in `PRD.md` are closed, the `/api/v1` response shapes are declared in `ARCHITECTURE.md`, and no
`OPEN` defect touches the API or the schema.

## Release state

No commit has been chosen for `2.0.0`, and no `2.0.0` tag exists: `git tag` and `git ls-remote --tags
origin` both list only `0.1.0`. `master` is ahead of `origin/master` and behind by nothing (23 commits
ahead at the 2026-10-19 measurement; the count grows with each landed commit). Documentation files may
be uncommitted. This application runs on loopback and is not deployable. `AGENTS.md` Â§1 and `PRD.md`
Â§6.10 keep it there.

## Phase 1 exit criteria (owner ruling 2026-10-18)

Phase 1 is complete when all four criteria below are satisfied. The owner declared the *criteria*, not the
*completion*; the agent records the criteria and their current status.

Last reviewed 2026-10-19, during the Phase 8 documentation pass. Every status below is a measurement
taken on that date, and each names the command or the file that produced it.

| Criterion | Description | Current status |
|-----------|-------------|----------------|
| (a) US-1..US-3 pass with pipeline test | US-1 (catalog browse), US-2 (fetch cross-reference), US-3 (training run with turn logging) all pass end-to-end against seeded data, including a pipeline test with a faked HTTP source. | Not satisfied â€” each story has a passing test of its own (US-1 `CatalogRosterTreeTest`, US-2 `FetchPipelineTest` with a faked HTTP source, US-3 `TrainingRunTest` and `TurnEntryFieldSourceTest`; 57 passed / 390 assertions, measured 2026-10-19), but no single gate measures all three end-to-end against seeded data, which is what this criterion asks for. |
| (b) Known gaps recorded in KNOWN-ISSUES.md | The NFR-20 gaps â€” no page request triggers background work, loading/error state termination, the refresh test, and the refresh navigation determinism issue â€” are recorded with status. | Satisfied â€” every one is recorded in `KNOWN-ISSUES.md` with a status: background-work termination as the 2026-10-19 entry KI-102, loading/error-state termination as the 2026-10-19 entry KI-103, the refresh navigation-determinism red case as the 2026-10-19 entry KI-101 (open, blocked on the owner trace), and the refresh test state registered in the same register. Measured 2026-10-19. |
| (c) ADR-0025 accepted | The per-run entered ceilings decision is accepted and binding. | **Satisfied** â€” ADR-0025 accepted in commit `docs(adr): accept ADR-0025 to match CHANGELOG and PRD requirements` (2ff8c3e); its status line still reads "Accepted (2026-10-18) â€” the owner has approved the wording; migration and tests are built." Measured 2026-10-19. |
| (d) Doc ratchet at acceptable level | DocCitationParity count at or below the 708 baseline, or the limit raised with a written reason. | **Satisfied** â€” 698 (698 GONE, 0 UNTRACKED) in a clean clone of HEAD against a 708 baseline; `DocCitationParityTest` passes, 2 passed / 5 assertions, exit 0. The limit was not raised: ten citations that no reader could follow were repointed, and the gate's own ledger Section 39 records the reconciliation. Measured 2026-10-19. The working-tree number reads lower at the same moment, because another session's uncommitted markdown edits are in the tree too; the committed number is the one that ships. |
