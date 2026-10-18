# VERSION

Current version: `2.0.0`, pre-release. Tagged `2.0.0`.

| Field            | Value                         | Measured                                                                      |
| ---------------- | ----------------------------- | ----------------------------------------------------------------------------- |
| Version          | `2.0.0`                       | decision recorded in `docs/adr/0019-semver-label-tag-and-release-branch.md`   |
| Pre-release      | yes                           | GitHub Release flag, not a semver hyphen identifier                           |
| Git tags         | `2.0.0`                       | `git tag`, `git ls-remote --tags origin`                                      |
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

No commit has been chosen for `2.0.0`. The `2.0.0` tag exists; `master` and `origin/master` are equal.
Documentation files may be uncommitted. This application runs on loopback and is not deployable.
`AGENTS.md` §1 and `PRD.md` §6.10 keep it there.

## Phase 1 exit criteria (owner ruling 2026-10-18)

Phase 1 is complete when all four criteria below are satisfied. The owner declared the *criteria*, not the
*completion*; the agent records the criteria and their current status.

| Criterion | Description | Current status |
|-----------|-------------|----------------|
| (a) US-1..US-3 pass with pipeline test | US-1 (catalog browse), US-2 (fetch cross-reference), US-3 (training run with turn logging) all pass end-to-end against seeded data, including a pipeline test with a faked HTTP source. | Not currently satisfied — pipeline test with faked source exists (`FetchPipelineTest`), but full US-1..US-3 end-to-end pass is not yet measured as a single gate. |
| (b) Known gaps recorded in KNOWN-ISSUES.md | NFR-20 items 5 & 6 (background work termination, loading/error state termination), NFR-20 item 4 (refresh test), and the refresh navigation determinism issue are recorded with status. | Blocked — KNOWN-ISSUES.md is held and not editable in this dispatch. No entries added. |
| (c) ADR-0025 accepted | The per-run entered ceilings decision is accepted and binding. | **Satisfied** — ADR-0025 accepted in commit `docs(adr): accept ADR-0025 to match CHANGELOG and PRD requirements` (2ff8c3e). |
| (d) Doc ratchet at acceptable level | DocCitationParity count at or below the 708 baseline, or the limit raised with a written reason. | Not currently satisfied — count is 1008 (measured 2026-10-18), baseline is 708. Limit not raised. |
