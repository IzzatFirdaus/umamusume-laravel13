# VERSION

Current version: `3.0.0`, pre-release. Tagged: `0.1.0`, `2.0.1`, `2.0.2`, `2.1.0`, `2.1.1`.

Every figure in this file was re-measured on 2026-11-18 at `HEAD 816e923e`, and each names the command
that produces it. Nothing here is aspirational: a row that cannot be re-derived from a command printed
beside it does not belong in this file.

| Field | Value | Measured |
| --- | --- | --- |
| Version | `3.0.0` | MAJOR for the HFW-parity landing (2026-10-27): the frontend was re-shaped slice by slice against the wireframes, which changes every screen's composition, while the three public surfaces named below are untouched (no route, schema or `lang/en/uma.php` change in the release). `ADR-0019` is **Advisory**; labelling is owner-directed. `2.1.1` was the 2026-10-24 PATCH; `2.1.0` was MINOR for the `review-detail` route; `2.0.1`/`2.0.2` were the 2026-10-22 UX-audit fixes |
| Pre-release | yes | GitHub Release flag, not a semver hyphen identifier |
| Git tags | `0.1.0`, `2.0.1`, `2.0.2`, `2.1.0`, `2.1.1` | `git tag -l` -> those five, and no `3.0.*`. No `2.0.0` tag exists locally or on the remote |
| Release commit | not fixed | `git tag -l 'v3*'` is empty. The tag cannot be cut until the divergence row below reaches `0 0` and the owner authorises it (docs/roadmap/MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md section S4.4) |
| Framework | `laravel/framework` **13.42.0** | `composer.lock` (locked `v13.42.0`), constraint `^13.0`; `composer show laravel/framework` -> `versions : * v13.42.0` |
| Inertia (server) | `inertiajs/inertia-laravel` **3.5.1** | `composer.lock` |
| Pest | `pestphp/pest` **4.7.8** | `composer.lock`; the PHP floor stays `^8.3` while Pest is on the 4 line |
| PHP | **8.5.8** | `php -v` -> `PHP 8.5.8 (cli)`, against `require.php: ^8.3` |
| Divergence from `origin/master` | **8 behind / 38 ahead** | `git rev-list --left-right --count origin/master...HEAD` -> `8  38` |

## Suite baselines at this HEAD

Both baselines were produced on 2026-11-18 on a quiet box, one process at a time.

| Gate | Result | Command |
| --- | --- | --- |
| PHP suite | **2467 passed, 2 failed** (37,575 assertions, 504s) | `php artisan test --compact < /dev/null` |
| Browser suite | not run in full at this HEAD; per-spec runs are recorded in the roadmap's S1.2 deliverable | `npx playwright test --list` -> 884 tests in 126 files |
| Citation census | **808 dead links against a 708 baseline** (the ratchet is open, not raised) | `python tools/doc_census.py` |
| ADR checker | 2 findings, both a word-cap breach (ADR-0026, ADR-0029) | `python tools/adr-check.py docs/adr` |

The two PHP failures are `DocCitationParityTest` (the 808-vs-708 ratchet above, expected red until the
ratchet slice lands) and `LineEndingGuardTest` (a CRLF file, repaired in this session and green on
re-run). Neither is a shipped-behaviour defect, and neither is claimed as a pass.

## Why 0.y.z is over

Semantic Versioning 2.0.0 item 5 reserves `0.y.z` for initial development, where anything may change and
the public API is not stable. Item 6 makes `1.0.0` the release that defines the public API. `ADR-0019`
records that this project had not defined one. The `2.x` line and the `3.0.0` line settled the three
surfaces below, so the label moved, while the **1.0.0** gate in the next section stays open.

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
`OPEN` defect touches the API or the schema. None of the three is satisfied at this HEAD; the roadmap's
S5.9 is the slice that closes them.

## Release state

`master` is **8 behind and 38 ahead** of `origin/master` at `816e923e`
(`git rev-list --left-right --count origin/master...HEAD` -> `8  38`). No `3.0.0` tag exists
(`git tag -l 'v3*'` is empty). The application runs on loopback and is not deployable: `AGENTS.md` §1 and
`PRD.md` §6.10 keep it there. Reconciling the divergence and cutting the tag are owner acts, recorded as
`D-3` and `D-12` in the roadmap's decision register.

## Phase 1 exit criteria (owner ruling 2026-10-18)

Phase 1 is complete when all four criteria below are satisfied. The owner declared the *criteria*, not the
*completion*; the agent records the criteria and their current status.

Last reviewed 2026-10-19. The statuses below are that pass's measurements, kept as the record of what was
true then. Where a later measurement changes a verdict it is named in the row.

| Criterion | Description | Current status |
| --- | --- | --- |
| (a) US-1..US-3 pass with pipeline test | US-1 (catalog browse), US-2 (fetch cross-reference), US-3 (training run with turn logging) all pass end-to-end against seeded data, including a pipeline test with a faked HTTP source. | Not satisfied as stated: each story has a passing test of its own (US-1 `CatalogRosterTreeTest`, US-2 `FetchPipelineTest` with a faked HTTP source, US-3 `TrainingRunTest` and `TurnEntryFieldSourceTest`; 57 passed / 390 assertions, measured 2026-10-19), but no single gate measures all three end-to-end against seeded data, which is what this criterion asks for. The 2026-11-18 full suite passes at 2467 tests, which strengthens each story's own coverage without creating that single gate. |
| (b) Known gaps recorded in KNOWN-ISSUES.md | The NFR-20 gaps (no page request triggers background work, loading/error state termination, the refresh test, and the refresh navigation determinism issue) are recorded with status. | Satisfied: every one is recorded in `KNOWN-ISSUES.md` with a status. Measured 2026-10-19; the register is still the status authority (`KNOWN-ISSUES.md:2-117`). |
| (c) ADR-0025 accepted | The per-run entered ceilings decision is accepted and binding. | Satisfied: ADR-0025 accepted in commit `2ff8c3e`; its status line reads "Accepted (2026-10-18)". Measured 2026-10-19. |
| (d) Doc ratchet at acceptable level | DocCitationParity count at or below the 708 baseline, or the limit raised with a written reason. | **Not satisfied at this HEAD.** The 2026-10-19 measurement read 698 against the 708 baseline; `python tools/doc_census.py` now reads **808 (808 GONE, 0 UNTRACKED)**. The limit was not raised, and raising it would breach `GOVERNANCE.md` C-8. The ratchet is open work, not a waiver. |

## Documented rules that this file does not restate

`AGENTS.md` §14 owns the git and change-management rules, `PRD.md` owns the non-goals, and
docs/roadmap/MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md owns the execution order and the owner decision
register. This file owns the version, the tag list, the measured baselines and the two gates above.
