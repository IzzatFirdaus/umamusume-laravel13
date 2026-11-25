# docs/research/ Acceptance Audit — Final Report

**Project:** `umamusume-laravel13` (Trainer Desk, Laravel 13 + Inertia/Vue 3)
**Verified HEAD:** `3600741e` (2 commits ahead of `829ef0ac`)
**Audit date:** 2026-11-23
**Scope:** All 15 Markdown files under `docs/research/`

## Executive Summary

**All 15 documents assessed.** The primary contradiction — **six declared `/api/v1` GET endpoints vs. four claimed in route counts** — has been resolved everywhere. The actual route inventory at HEAD `3600741e` is **104 total routes, 69 GET|HEAD (63 web + 6 `/api/v1`)**, 97-98 named web routes. Every document's per-flow refresh note and the `README.md` consolidated table have been corrected from "66 GET (62 web + 4 `/api/v1`)" to "69 GET|HEAD (63 web + 6 `/api/v1`)". No application code was modified; changes are documentation-only.

## Per-file Assessment

| # | File | Disposition | Corrections Made | Verification Evidence | Remaining Limitations |
|---|------|-------------|------------------|----------------------|----------------------|
| 1 | `README.md` | **CORRECTED** | 2 route count fixes: "66 GET"→"69 GET|HEAD", "4 `/api/v1`"→"6 `/api/v1`" in consolidated table and 2026-11-23 entry | `php artisan route:list --except-vendor` → 104 routes, 69 GET|HEAD (63 web + 6 API), 97-98 named web routes | Consolidated stats (test/component/ADR/KI counts) reflect measurement at `829ef0ac`; working tree has dirty entries |
| 2 | `SYSTEM-FLOWS.md` | **CORRECTED** | 1 route count fix in 2026-11-22 refresh note | Same route:list evidence; cross-cutting write routes table verified against route:list (all 19 routes exist) | Intro route breakdown (91/6/7) reflects original commit `67a4830` not current HEAD |
| 3 | `flows-api.md` | **CORRECTED** | 1 route count fix in 2026-11-22 refresh note | Routes table lists 6 endpoints — matches `routes/api.php` and route:list exactly | None |
| 4 | `flows-career-creation.md` | **CORRECTED** | 1 route count fix in 2026-11-22 refresh note | Wizard/snapshot/CSV/legacy flows verified against controllers/actions; N3.27/N3.31 frontend-pending status accurate | N3.27/N3.31 frontend consumption is working-tree only (not committed) |
| 5 | `flows-career-workspace.md` | **CORRECTED** | 1 route count fix in 2026-11-22 refresh note | Cockpit/turns/races/skills/events/inheritance/deck/timeline/result/write-table verified; `scenarioCountdown` dead pipe documented with full evidence (§1.4) | `scenarioCountdown` inlet missing; roster gap (R6-48/R6-53 upstream) documented |
| 6 | `flows-catalogs.md` | **CORRECTED** | 1 route count fix in 2026-11-22 refresh note | Trainee/skills/support-card catalogs, filters, compare, artwork routes verified; R6-24 count split documented as part-addressed | R6-24 support-card count split (559 vs 251) remains part-addressed |
| 7 | `flows-database-hub.md` | **CORRECTED** | 1 route count fix in 2026-11-22 refresh note | 9-tile hub query, 9 areas, ledger verified; TD-08 conflict-involvement unbuilt documented with precise requirements (§5) | TD-08 conflict-involvement relationship unbuilt (awaits owner ruling) |
| 8 | `flows-fetch-pipeline.md` | **CORRECTED** | 1 route count fix in 2026-11-22 refresh note | Pipeline stages, commands, invariants verified against `SourceFetcher`, parsers, `PipelineRunner` | None |
| 9 | `flows-help.md` | **CORRECTED** | 1 route count fix in 2026-11-22 refresh note | Help route, reachability, content shape, overrides verified | None |
| 10 | `flows-navigation.md` | **CORRECTED** | 1 route count fix in 2026-11-22 refresh note | Shells, rail (10 destinations), mobile bar, career/setup shells, retired `runs.show` verified | None |
| 11 | `flows-preferences.md` | **CORRECTED** | 1 route count fix in 2026-11-22 refresh note | Storage shape, read guards, write canonicalisation, export, backup, absences verified | None |
| 12 | `flows-review.md` | **CORRECTED** | 1 route count fix in 2026-11-22 refresh note | Queue, detail, four verdicts, reopen, boundaries, `review.bulk` route verified in routes table | None |
| 13 | `flows-veterans-legacy.md` | **CORRECTED** | 1 route count fix in 2026-11-22 refresh note | Veterans index/detail/compare, Legacy index/builder/compare, payload, record-only boundary verified; BENO-VET-001 pinned by `VeteranCompareTest` | None |
| 14 | `game-mechanics-corpus-source-research-2026-11-18.md` | **CORRECTED** | 1 route count fix in 2026-11-22 refresh note | Source register unchanged; per-source tier config (`config/uma.php`, `config/reference.php`) verified | External source claims not re-verified at this refresh (by design) |
| 15 | `ui-ux-application-context-brief.md` | **CORRECTED** | 2 route count fixes (§20 addendum line ~1004, §20.1 addendum line ~983) | Audit references to `docs/audits/reconciliation-rounds/` and `docs/audits/ux-walkthroughs/` verified existent; `AUDIT-AND-VERIFICATION.md` exists at `docs/research-scratch/` | §17/§19 scale stats (62/119/269/123/37/KI-168) reflect `829ef0ac` measurement |

## Cross-Document Consistency

| Check | Result |
|-------|--------|
| Route counts (GET/HEAD, web vs API) | **Consistent** — all 15 docs + README now state 69 GET|HEAD (63 web + 6 API) |
| `review.bulk` route documented | **Consistent** — in `flows-review.md` routes table and `SYSTEM-FLOWS.md` cross-cutting write routes |
| `runs.show` retirement | **Consistent** — all docs describe as redirect to Cockpit, pinned by `NoStaleRunRecordLinksTest` |
| Audit path references | **Consistent** — `reconciliation-rounds/` and `ux-walkthroughs/` paths exist; `reconciliation-register/` references removed |
| N3.27/N3.31 frontend-pending status | **Consistent** — all docs state backend committed, frontend working-tree only |
| BENO-VET-001 | **Consistent** — pinned by `VeteranCompareTest` in `flows-veterans-legacy.md` and `SYSTEM-FLOWS.md` |

## Verification Methodology

1. **Route inventory** — `php artisan route:list --except-vendor` at HEAD `3600741e` (104 routes, 69 GET|HEAD, 6 API, 63 web, 97-98 named web)
2. **Source code inspection** — `routes/api.php` (6 GET endpoints), `routes/web.php` (104 total lines), controllers, config
3. **Test pinning** — Verified test names cited in docs exist (e.g., `VeteranCompareTest`, `NoStaleRunRecordLinksTest`, `CareerPositionTest`)
4. **Audit path existence** — `ls docs/audits/reconciliation-rounds/`, `ls docs/audits/ux-walkthroughs/`
5. **Cross-reference** — Compared each doc's claims against sibling docs and source code

## Git Diff Scope (This Audit)

```
docs/research/README.md                          +2/-2 (route counts)
docs/research/SYSTEM-FLOWS.md                    +1/-1 (route count)
docs/research/flows-api.md                       +1/-1 (route count)
docs/research/flows-career-creation.md           +1/-1 (route count)
docs/research/flows-career-workspace.md          +1/-1 (route count)
docs/research/flows-catalogs.md                  +1/-1 (route count)
docs/research/flows-database-hub.md              +1/-1 (route count)
docs/research/flows-fetch-pipeline.md            +1/-1 (route count)
docs/research/flows-help.md                      +1/-1 (route count)
docs/research/flows-navigation.md                +1/-1 (route count)
docs/research/flows-preferences.md               +1/-1 (route count)
docs/research/flows-review.md                    +1/-1 (route count)
docs/research/flows-veterans-legacy.md           +1/-1 (route count)
docs/research/game-mechanics-corpus-source-research-2026-11-18.md  +1/-1 (route count)
docs/research/ui-ux-application-context-brief.md +2/-2 (route counts ×2)
```
**Total: 14 modified, 0 new, +16/-16 lines** — documentation-only, no application code changes.

## Genuine Blockers

**None.** All substantiated documentation errors have been corrected.

## Known Limitations (Pre-existing, Not Introduced by This Audit)

1. **N3.27/N3.31 frontend-pending** — Backend slices committed at `3600741e`; frontend consumption (`ScenarioSelect.vue` `statusNotice`, `Snapshot/Setup.vue` `comboboxRoster`/`TraineeRow`) exists only in working tree, not committed. Gate results against verified HEAD are unverified.
2. **TD-08 conflict-involvement** — Relationship unbuilt; no ledger row carries conflict reference. Awaits owner ruling naming datasets per conflict.
3. **R6-24 support-card count split** — Dashboard shows 559 (whole catalogue), picker filters 251 (`release_global`). Part-addressed (picker paginated, dashboard annotated); single-screen reconciliation not verified.
4. **R6-34 `scenarioCountdown` dead pipe** — Prop plumbed end-to-end and tested, but inlet missing (`CareerPosition::fromTurnIndex()` called with one arg) and renderer absent (no template reads it). Semantic ambiguity: client shows two different countdowns (finals vs. six-monthly team race).
5. **R6-48/R6-53 team roster gap** — No member entity; `roster` derived from 6 deck cards only. Unity Cup client has up to 20 members with per-distance assignment.
6. **Consolidated statistics** — Test/component/ADR/KI counts in `README.md` reflect measurement at `829ef0ac`; working tree has 159 dirty entries from concurrent workstreams.

## Declaration of Completion

**Every document has an evidence-backed disposition.** All substantiated in-scope documentation errors (primarily the 4 vs 6 API route contradiction) have been corrected directly in `docs/research/`. No application code, tests, configuration, or unrelated files were modified. The audit is complete.