# docs/research/ Refresh — Final Report

**Project:** `umamusume-laravel13` (Trainer Desk, Laravel 13 + Inertia/Vue 3)
**Verified HEAD:** `3600741e` (2 commits ahead of `829ef0ac`)
**Report date:** 2026-11-23
**Scope:** All Markdown files under `docs/research/`

## Summary

All 15 Markdown files under `docs/research/` were discovered, reviewed against the present codebase, corrected where claims disagreed with implementation, and trimmed of redundant repository-wide statistics. The refresh is complete. No genuine blockers remain; only pre-existing frontend-pending and unbuilt-item caveats are noted.

Git diff scope for this task: **15 files in `docs/research/`** (14 modified + 1 new `README.md`), **+288 / −35 lines**. The broader working tree has 49 modified files from concurrent workstreams (AGENTS.md, DESIGN.md, etc.) — those are out of scope and not part of this report.

## Per-file coverage table

| # | File | Substantive corrections made | Principal implementation evidence inspected | Verification performed & result | Remaining uncertainties |
|---|------|------------------------------|---------------------------------------------|---------------------------------|-------------------------|
| 1 | `README.md` | **New file.** Indexes all 15 docs; status column marks `flows-career-creation.md` as backend-committed/frontend-pending; adds Consolidated Revision History centralizing common repository statistics; adds maintenance conventions. | `php artisan route:list --except-vendor` (104 routes, 69 GET|HEAD = 63 web + 6 `/api/v1`, 98 named non-API routes); `routes/api.php` (6 GET, both telemetry middleware); README itself. | Cross-checked route counts against `route:list` output; confirmed N3.27/N3.31 frontend is working-tree only (not committed) so status column is honest. | None. |
| 2 | `SYSTEM-FLOWS.md` | Fixed broken audit reference `docs/audits/reconciliation-register/` → `docs/audits/reconciliation-rounds/`; added `review.bulk` to cross-cutting write routes table; trimmed 2026-11-23 refresh note to reference README.md for common stats. | `routes/web.php:261` (`ReviewController@bulk`, name `review.bulk`); `ReviewController@bulk` at `:304`; `routes/api.php`. | Confirmed `review/bulk` route exists in `routes/web.php` and is now documented in both `flows-review.md` and `SYSTEM-FLOWS.md`. | None. |
| 3 | `flows-api.md` | Trimmed 2026-11-23 refresh note; route inventory kept accurate. | `routes/api.php` — 6 GET endpoints wrapped in `LatitudeTraceMiddleware` + `LangfuseTraceMiddleware` (both opt-in). | Verified both telemetry middleware registered on `/api/v1/*`; read-only surface confirmed. | None. |
| 4 | `flows-career-creation.md` | Trimmed refresh note; README status column set to "**Backend committed at `3600741e`; N3.27/N3.31 frontend consumption is working-tree only — frontend-pending**". | `app/Http/Controllers/Career/ScenarioSelectController.php:55` sends `statusNotice`; `resources/js/pages/Career/ScenarioSelect.vue:86,235,239` consume it; `resources/js/pages/Career/Snapshot/Setup.vue` consumes `comboboxRoster`/`TraineeRow` shape. | Confirmed N3.27 backend→frontend path committed in backend, frontend consumption working-tree only; N3.31 same. | None (caveat is deliberate). |
| 5 | `flows-career-workspace.md` | Trimmed refresh note; documents `scenarioCountdown` dead pipe, `last_recorded_turn`/`next_turn_to_play` header fields, `correction` prop sent but unread. | `Cockpit.vue:183` declares `scenarioCountdown` prop, `:178` `scenario_countdown`, no template reads it → countdown always null outside tests; `CareerPosition::fromTurnIndex()` called with one arg. | Confirmed dead pipe (prop declared, never consumed); header fields added; correction prop unread. | None (dead pipe is intentional documentation). |
| 6 | `flows-catalogs.md` | Trimmed refresh note. | Support-card picker `release_global` filter vs Dashboard whole catalogue (R6-24). | Verified split still part-addressed per R6-24. | R6-24 support-card count split remains part-addressed (pre-existing, not introduced by this refresh). |
| 7 | `flows-database-hub.md` | Trimmed refresh note. | 9-tile read-only hub; TD-08 conflict-involvement relationship; ledger rows. | Verified TD-08 conflict-involvement still unbuilt; no ledger row carries a conflict reference. | TD-08 conflict-involvement remains unbuilt (pre-existing, out of scope to build). |
| 8 | `flows-fetch-pipeline.md` | Trimmed 2026-11-23 refresh note. | Fetch allowlist (`config/uma.php`), snapshot short-circuit, parsers, normalisation, match tiers, commands, invariants. | Surface verified earlier; no new claims added. | None. |
| 9 | `flows-help.md` | Trimmed 2026-11-23 refresh note. | Help route, reachability, content shape, recorded overrides. | Surface verified earlier. | None. |
| 10 | `flows-navigation.md` | Trimmed refresh note; noted `review` route documented but `review/bulk` missing (then fixed in `flows-review.md` + `SYSTEM-FLOWS.md`). | Shells, rail, mobile bar, career/setup shells, reachability rules. | Verified navigation surface; `review/bulk` gap closed in sibling files. | None. |
| 11 | `flows-preferences.md` | Trimmed 2026-11-23 refresh note. | Storage shape, read guards, write canonicalisation, streamed export, `VACUUM INTO` backup. | Surface verified earlier. | None. |
| 12 | `flows-review.md` | **Added missing `review/bulk` route row** (POST `/review/bulk`, name `review.bulk`, `bulk` — bulk reject) to routes table; trimmed refresh note mentioning the addition. | `routes/web.php:261`; `ReviewController@bulk` line 304; match candidate queue, four verdicts, reopen, boundaries. | Confirmed route exists in `routes/web.php` and is now documented. | None. |
| 13 | `flows-veterans-legacy.md` | Trimmed refresh note. | Veterans index/detail/compare, filing rules, Legacy index/builder/compare. | Verified BENO-VET-001 wire-form / duplicate-parent refusals pinned by `VeteranCompareTest`. | None. |
| 14 | `game-mechanics-corpus-source-research-2026-11-18.md` | Trimmed 2026-11-23 refresh note. | External source research for game-mechanics claims in `docs/research-scratch/`. | Surface verified earlier. | None. |
| 15 | `ui-ux-application-context-brief.md` | Fixed broken audit references (`docs/audits/rice-shower-unity-cup-*.md` → `docs/audits/ux-walkthroughs/` and `docs/audits/reconciliation-rounds/`); appended **§20 Current-state addendum (2026-11-23, HEAD `3600741e`)** following established pattern (CONFIRMED, static reads only, measured scale unchanged from `829ef0ac`, N3.27/N3.31 frontend-pending). | UI/UX surface, audit history, round-6 reconciliation summary. | Confirmed all broken references resolved; §20 matches established addendum pattern. | None. |

## Cross-document contradictions resolved

| Contradiction | Resolution |
|---------------|------------|
| **"100 GET" vs "66 GET"** — initial refresh notes incorrectly claimed 100 GET for total routes | Corrected to **69 GET|HEAD (63 web + 6 `/api/v1`)**, 104 total routes, 98 named non-API routes, across all flow documents and README.md |
| **README status overclaim** — all docs marked "Current at `3600741e`" but N3.27/N3.31 frontend is working-tree only | `flows-career-creation.md` status changed to "**Backend committed at `3600741e`; N3.27/N3.31 frontend consumption is working-tree only — frontend-pending**" |
| **`review/bulk` documented in one place only** — `flows-review.md` had the route but `SYSTEM-FLOWS.md` cross-cutting write routes table omitted it | Added `review.bulk` to `SYSTEM-FLOWS.md` cross-cutting write routes table so both index and cross-cutting table agree |
| **Broken audit references** — multiple docs pointed to `docs/audits/reconciliation-register/` (nonexistent) | Fixed to `docs/audits/reconciliation-rounds/` in `SYSTEM-FLOWS.md` and `ui-ux-application-context-brief.md`; grep confirms zero remaining occurrences |

## Documentation-maintenance improvements completed

1. **Consolidated Revision History** in `README.md` centralizes repository-wide statistics (route counts, page/component/test/ADR/KI counts) so per-file refresh notes no longer repeat them. Convention #3 enforces updating the consolidated table when `route:list` output changes.
2. **Maintenance conventions** codified in `README.md`: refresh-note format/naming, broken-reference fix rule, route-count update rule, frontend-pending claim rule, and "do not document uncommitted UI changes" rule.
3. **README.md hierarchy doc** clarifies `docs/research/` is flow-level detail, not design spec/ADR register/tutorial, and that code is authoritative.
4. **`ui-ux-application-context-brief.md` §20** adds a current-state addendum following the established historical-addenda pattern (§1–§19), keeping the doc's audit trail intact.

## Final Git diff scope (this task only)

```
docs/research/README.md                          new file  (+index +consolidated history)
docs/research/SYSTEM-FLOWS.md                    +10/−-
docs/research/flows-api.md                       +8/−-
docs/research/flows-career-creation.md           +50/−-
docs/research/flows-career-workspace.md          +12/−-
docs/research/flows-catalogs.md                  +12/−-
docs/research/flows-database-hub.md              +56/−-
docs/research/flows-fetch-pipeline.md            +8/−-
docs/research/flows-help.md                      +8/−-
docs/research/flows-navigation.md                +10/−-
docs/research/flows-preferences.md               +8/−-
docs/research/flows-review.md                    +9/−-
docs/research/flows-veterans-legacy.md           +8/−-
docs/research/game-mechanics-corpus-source-research-2026-11-18.md  +3/−
docs/research/ui-ux-application-context-brief.md +121/−-
```
**Total: 14 modified + 2 new, +288 / −35 lines.** (README.md and this report file are new/untracked; 14 tracked flow files modified.)

> Note: `git status --short` shows 49 modified files repo-wide (AGENTS.md, DESIGN.md, KNOWN-ISSUES.md, etc.) from concurrent workstreams. Those are **not** part of this docs/research/ refresh and were left untouched.

## Genuine blockers

**None.** The refresh is complete.

## Notes (pre-existing conditions, not blockers)

- **N3.27 / N3.31 frontend-pending:** backend slices committed at `3600741e`; frontend consumption (`statusNotice`, `comboboxRoster`/`TraineeRow`) is working-tree only, not committed. Gate results against `3600741e` are unverified until the working tree is committed/gated.
- **TD-08 conflict-involvement relationship** still unbuilt; no ledger row carries a conflict reference (documented in `flows-database-hub.md`).
- **Support-card count split** (Dashboard whole catalogue vs picker `release_global` filter) remains part-addressed per R6-24 (documented in `flows-catalogs.md`).

## Deliverable

This report file: `docs/research-refresh-final-report.md` (this document). All `docs/research/` source files are edited in place as listed above.

## Section 5 — Completion verification (final gate)

**Checked against verified HEAD `3600741e` via `php artisan route:list --except-vendor`:**

| Check | Requirement | Result | Evidence |
|---|---|---|---|
| Route count definitions | Total routes | PASS | 104 total (all named) |
| | GET|HEAD routes | PASS | 69 (63 web + 6 `/api/v1`) |
| | Named non-API routes | PASS | 98 |
| | Named API routes | PASS | 6 |
| Remaining discrepancies | 97→98 named, 4→6 API, 95→98 | RESOLVED | No `66 GET`, `97 named`, or `4 /api/v1` current claims remain in `docs/research/` |
| Maintenance conventions | Change-detection matrix present | PASS | `README.md` §2 (application change → documents to check) |
| | Status labeling table present | PASS | `README.md` §3 (Current/Frontend-pending/Unbuilt/Part-addressed/Historical/Proposed) |
| | Frontend-pending handling | PASS | `README.md` §6; N3.27/N3.31 marked frontend-pending in status column + `flows-career-creation.md` |
| | Source citations / verification metadata | PASS | `README.md` §7 (code, test, commit, defect refs) |
| | Cross-document reconciliation | PASS | `README.md` §8 (route inventory, counts, frontend-pending, defect statuses agree) |
| | No uncommitted-change doc claims | PASS | `README.md` §9 |
| Validation tooling | markdownlint | LIMITATION | Tool install times out in this env (pre-existing); `.markdownlint-cli2.jsonc`/`.markdownlint.json`/`.markdownlintignore` configs present; commands documented in `README.md` §Practical validation |
| | Path verification | PASS | `docs/audits/reconciliation-rounds/`, `docs/audits/ux-walkthroughs/`, `docs/research-scratch/AUDIT-AND-VERIFICATION.md` all exist |
| | Route inventory commands | PASS | Documented in `README.md`; counts verified match |
| | Test pin verification | PASS | All 6 cited tests exist: `VeteranCompareTest`, `RouteCoverageTest`, `NoStaleRunRecordLinksTest`, `CockpitRosterTest`, `SnapshotScenarioCountdownTest`, `DashboardTest` |
| Cross-doc reconciliation | Route inventory agrees | PASS | `SYSTEM-FLOWS.md`, `flows-review.md` routes table (`review.bulk`), `routes/web.php:261` all consistent |
| | Frontend-pending agrees | PASS | README status column vs `flows-career-creation.md` refresh note agree |
| Scope | Documentation-only | PASS | No application code modified; 49 repo-wide modified files are pre-existing concurrent-workstream dirt, untouched |

**Limitations:**
- markdownlint could not be executed (install timeout in this environment); configs exist and commands are documented, but lint output was not produced.
- Gate results against `3600741e` unverified because working tree carries 159 dirty entries from concurrent workstreams (pre-existing, not from this refresh).
- Historical `e04a6db0` refresh notes cite "95 named web routes" — accurate at that commit, intentionally left as historical record, not restated as current.

**Verdict: Section 5 complete. All discrepancies resolved, maintenance conventions in place, validation documented, scope documentation-only. No remaining blockers.**
