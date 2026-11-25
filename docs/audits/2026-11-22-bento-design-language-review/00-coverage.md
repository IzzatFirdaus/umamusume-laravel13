# 00 — Coverage

Independent full-site bento layout & design-language audit for TrainerDesk.
Read-only audit: no application code, tests, styles, configurations, or data were modified.

## Audit metadata

| Field | Value |
|---|---|
| Target application | TrainerDesk (Laravel 13, Inertia + Vue 3 + TS, Tailwind v4, Vite) |
| Target URL | http://127.0.0.1:8000 |
| Server status at audit time | Not running — dev server could not be started (read-only boundary prohibits restarting). Rendered evidence therefore comes from source-code inspection of Vue components, layouts, CSS, and SCREEN_SPEC.md state tables, not from live browser capture. |
| Inspection date | 2026-11-22 (Asia/Kuala_Lumpur) |
| Browser/build | N/A — no live browser session available |
| Themes in scope | Azure Daybreak (`light`), Midnight Circuit (`dark`), EVA-01 (`eva01`) |
| Viewports in scope | 1280px, 768px, 390px, 320px CSS px |
| Authority documents | `DESIGN.md`, `SCREEN_SPEC.md`, `AGENTS.md`, `PRD.md`, `ARCHITECTURE.md`, accepted ADRs, HFW override register (`SCREEN_SPEC.md` §8.25) |
| Audit directory | `docs/audits/2026-11-22-bento-design-language-review/` |
| Register target | `docs/audits/INDEX.md` |

## Theme / viewport inspection matrix

| Theme | 1280px | 768px | 390px | 320px |
|---|---|---|---|---|
| Azure Daybreak (light) | Source-only | Source-only | Source-only | Source-only |
| Midnight Circuit (dark) | Source-only | Source-only | Source-only | Source-only |
| EVA-01 (eva01) | Source-only | Source-only | Source-only | Source-only |

All theme/viewport combinations are marked **source-only**: no live browser session was available, so responsive and theme behavior is inferred from container-query breakpoints (`@container min-width: 585px` and `845px` in `resources/css/layout-grid.css`), the compact/wide/standard layout modes (`resources/js/composables/useLayout.ts`), and media queries at 820px (`layout-grid.css` §`.subnav`). Direct visual verification at each breakpoint is unverified.

## Complete route inventory

Source: `routes/web.php` (368 lines) cross-referenced against `SCREEN_SPEC.md` §3 Screen Matrix (§4. Screen Specifications) and `SCREEN_SPEC.md` §8.25 HFW override register.

### Career — Dashboard & shell (SCR-CAR-*)

| # | ID | Screen | Route | Name | Source | Status | Layout class |
|---|---|---|---|---|---|---|---|
| 1 | SCR-CAR-001 | Dashboard (2.0 landing) | `GET /` | `home` | SCR_SPEC §4.21 | Implemented | Bento-primary |
| 2 | SCR-CAR-011 | Career Cockpit | `GET /training-runs/{run}/cockpit` | `runs.cockpit` | SCR_SPEC §4.25 | Implemented | Bento-primary |
| 3 | SCR-CAR-029 | Career Scenario detail | `GET /training-runs/{run}/scenario` | `runs.scenario-detail` | SCR_SPEC §4.25 | Implemented | Bento-primary (panel grid) |
| 4 | SCR-CAR-028 | Career Team setup | `GET /training-runs/{run}/team` | `runs.team` | SCR_SPEC §4.25 | Implemented | Bento-hybrid |
| 5 | SCR-CAR-030 | Run settings | `GET /training-runs/{run}/settings` | `runs.settings` | SCR_SPEC §4.25 | Implemented | Conventional (form) |
| 6 | SCR-CAR-031 | Confirm delete (run) | `GET /training-runs/{run}/delete` | `runs.confirm-delete` | SCR_SPEC §4.25 | Implemented | Conventional (confirmation) |
| 7 | SCR-CAR-032 | Turn correction | `GET /training-runs/{run}/turns/{turn}/edit` | `runs.turns.edit` | SCR_SPEC §4.25 | Implemented | Conventional (form) |

### Career — Wizard (SetupLayout, SCR-CAR-002 to SCR-CAR-010)

| # | ID | Screen | Route | Name | Source | Status | Layout class |
|---|---|---|---|---|---|---|---|
| 8 | SCR-CAR-002 | Scenario Selection (step 1) | `GET /career/setup/scenario` | `career.scenario` | SCR_SPEC §4.22 | Implemented | Conventional (wizard step, sequential) |
| 9 | SCR-CAR-003 | Trainee Selection (step 2) | `GET /career/setup/trainee` | `career.trainee` | SCR_SPEC §4.23 | Implemented | Conventional (wizard step, sequential) |
| 10 | SCR-CAR-004 | Trainee Profile | `GET /career/setup/trainee/{umamusume}` | `career.trainee.profile` | SCR_SPEC §4.24 | Implemented | Conventional (detail/read screen) |
| 11 | SCR-CAR-005 | Build Target (step 3) | `GET /career/setup/target` | `career.target` | SCR_SPEC §4.25 | Implemented | Conventional (wizard step, sequential) |
| 12 | SCR-CAR-008 | Legacy Select (step 4) | `GET /career/setup/legacy` | `career.legacy` | SCR_SPEC §4.25 | Implemented | Conventional (wizard step, sequential) |
| 13 | SCR-CAR-009 | Support Deck Select (step 5) | `GET /career/setup/deck` | `career.deck` | SCR_SPEC §4.25 | Implemented | Conventional (wizard step, sequential) |
| 14 | SCR-CAR-010 | Preflight / Review and start (step 6) | `GET /career/setup/preflight` | `career.preflight` | SCR_SPEC §4.25 | Implemented | Conventional (wizard step, sequential) |

### Career — Run-scoped decision/detail screens

| # | ID | Screen | Route | Name | Source | Status | Layout class |
|---|---|---|---|---|---|---|---|
| 15 | SCR-CAR-012 | Training Decision detail | `GET /training-runs/{run}/training` | `runs.training` | SCR_SPEC §4.26 | Implemented | Bento-hybrid (decision cards + context) |
| 16 | SCR-CAR-013 | Race Decision | `GET /training-runs/{run}/races` | `runs.races.decision` | SCR_SPEC §4.26 | Implemented | Bento-hybrid |
| 17 | SCR-CAR-014 | Event Decision | `GET /training-runs/{run}/events` | `runs.events.decision` | SCR_SPEC §4.26 | Implemented | Bento-hybrid |
| 18 | SCR-CAR-015 | Inheritance Event | `GET /training-runs/{run}/inheritance` | `runs.inheritance` | SCR_SPEC §4.26 | Implemented | Bento-hybrid |
| 19 | SCR-CAR-016 | Skills Planner | `GET /training-runs/{run}/skills` | `runs.skills.planner` | SCR_SPEC §4.26 | Implemented | Bento-hybrid |
| 20 | SCR-CAR-017 | Career Timeline | `GET /training-runs/{run}/timeline` | `runs.timeline` | SCR_SPEC §4.26 | Implemented | Conventional (timeline/list) |
| 21 | SCR-CAR-018 | Career Result | `GET /training-runs/{run}/result` | `runs.result` | SCR_SPEC §4.26 | Implemented | Bento-hybrid |
| 22 | SCR-CAR-019 | Scenario panel shell | mounted inside Cockpit | n/a | SCR_SPEC §4.26 | Implemented | Component (no standalone route) |
| 23 | SCR-CAR-020 | URA panel | mounted inside Cockpit scenario region | n/a | SCR_SPEC §4.26 | Built | Component (no standalone route) |
| 24 | SCR-CAR-021 | Unity Cup panel | mounted inside Cockpit scenario region | n/a | SCR_SPEC §4.26 | Built | Component (no standalone route) |
| 25 | SCR-CAR-022 | Trackblazer panel | mounted inside Cockpit scenario region | n/a | SCR_SPEC §4.26 | Built | Component (no standalone route) |
| 26 | SCR-CAR-023 | Scenario Race Planner | `GET /training-runs/{run}/races/planner` | `runs.races.planner` | SCR_SPEC §4.26 | Implemented | Conventional (calendar/planner) |
| 27 | SCR-CAR-024 | Our Grand Concert baseline strip | mounted inside Cockpit scenario region | n/a | SCR_SPEC §4.26 | Built | Component (no standalone route) |
| 28 | SCR-CAR-025 | Career Snapshot: entry | `GET /career/snapshot` | `career.snapshot` | SCR_SPEC §4.26 | Implemented | Conventional (entry) |
| 29 | SCR-CAR-026 | Career Snapshot: setup | `GET /career/snapshot/setup` | `career.snapshot.setup` | SCR_SPEC §4.26 | Implemented | Conventional (form) |
| 30 | SCR-CAR-027 | Career Snapshot: review | `GET /career/snapshot/review` | `career.snapshot.review` | SCR_SPEC §4.26 | Implemented | Conventional (review/confirm) |

### Career — Builders & legacy (SCR-CAR-006, SCR-CAR-007)

| # | ID | Screen | Route | Name | Source | Status | Layout class |
|---|---|---|---|---|---|---|---|
| 31 | SCR-CAR-006 | Legacy Lab | `GET /legacy`, `GET/PUT /legacy/{run}`, `GET /legacy/compare` | `legacy.index`, `legacy.builder`, `legacy.compare` | SCR_SPEC §4.25 | Implemented | Bento-hybrid (index/builder), Conventional (compare table) |
| 32 | SCR-CAR-007 | Support Deck Builder | `GET /training-runs/{run}/deck`, `POST /training-runs/{run}/deck` | `runs.deck` | SCR_SPEC §4.25 | Implemented | Bento-hybrid |

### Runs (SCR-RUN-*)

| # | ID | Screen | Route | Name | Source | Status | Layout class |
|---|---|---|---|---|---|---|---|
| 33 | SCR-RUN-001 | Training runs list | `GET /training-runs` | `runs.index` | SCR_SPEC §4.1 | Implemented | Conventional (table/list) |
| 34 | SCR-RUN-002 | New training run | `GET /training-runs/create` | `runs.create` | SCR_SPEC §4.2 | Implemented | Conventional (form) |
| 35 | SCR-RUN-003 | Run detail & turn log | `GET /training-runs/{run}` → 302 `runs.cockpit` | `runs.show` | SCR_SPEC §4.3 | Retired 2026-10-09 (F2) | N/A — redirects to Cockpit |
| 36 | SCR-RUN-004 | Import a historical run (form) | `GET /training-runs/import` | `runs.import` | SCR_SPEC §4.4 | Implemented | Conventional (form) |
| 37 | SCR-RUN-005 | Import preview & confirm | `POST /training-runs/import/preview` | `runs.import.preview` | SCR_SPEC §4.5 | Implemented | Conventional (preview table) |

### Catalog (SCR-CAT-*)

| # | ID | Screen | Route | Name | Source | Status | Layout class |
|---|---|---|---|---|---|---|---|
| 38 | SCR-CAT-001 | Umamusume catalog | `GET /umamusume` | `catalog.index` | SCR_SPEC §4.5 | Implemented | Conventional (roster tree/list) |
| 39 | SCR-CAT-002 | Umamusume profile detail | `GET /umamusume/{slug}` | `catalog.show` | SCR_SPEC §4.5 | Implemented | Bento-hybrid (identity header + sections) |

### Skills (SCR-SKL-*)

| # | ID | Screen | Route | Name | Source | Status | Layout class |
|---|---|---|---|---|---|---|---|
| 40 | SCR-SKL-001 | Skill search | `GET /skills` | `skills.index` | SCR_SPEC §4.6 | Implemented | Conventional (search results list) |
| 41 | SCR-SKL-002 | Skill detail | `GET /skills/{skill}` | `skills.show` | SCR_SPEC §4.6 | Implemented | Bento-hybrid (detail + sections) |

### Support cards (SCR-SUP-*)

| # | ID | Screen | Route | Name | Source | Status | Layout class |
|---|---|---|---|---|---|---|---|
| 42 | SCR-SUP-001 | Support-card catalog | `GET /support-cards` | `support-cards.index` | SCR_SPEC §4.7 | Implemented | Conventional (catalog list) |
| 43 | SCR-SUP-002 | Support-card detail | `GET /support-cards/{card}` | `support-cards.show` | SCR_SPEC §4.7 | Implemented | Bento-hybrid (artwork frame + detail sections) |

### Review (SCR-REV-*)

| # | ID | Screen | Route | Name | Source | Status | Layout class |
|---|---|---|---|---|---|---|---|
| 44 | SCR-REV-001 | Match review queue | `GET /review` | `review.index` | SCR_SPEC §4.8 | Implemented | Conventional (queue/cards) |
| 45 | SCR-REV-002 | Match review detail | `GET /review/{candidate}` | `review.detail` | SCR_SPEC §4.8 | Implemented | Conventional (resolve form) |

### Veterans (SCR-VET-*)

| # | ID | Screen | Route | Name | Source | Status | Layout class |
|---|---|---|---|---|---|---|---|
| 46 | SCR-VET-001 | Veteran library | `GET /veterans` | `veterans.index` | SCR_SPEC §4.16 | Implemented | Conventional (library list) |
| 47 | SCR-VET-002 | Veteran detail | `GET /veterans/{veteran}` | `veterans.show` | SCR_SPEC §4.17 | Implemented | Bento-hybrid (detail + context) |
| 48 | SCR-VET-003 | Save Veteran | `GET /training-runs/{run}/veteran`, `POST /training-runs/{run}/veteran` | `runs.veteran`, `runs.veteran.store` | SCR_SPEC §4.19 | Built | Conventional (save form) |
| 49 | SCR-VET-004 | Veteran comparison | `GET /veterans/compare` | `veterans.compare` | SCR_SPEC §4.20 | Built (gate RED at audit time) | Conventional (comparison matrix) |
| 50 | SCR-VET-005 | Transfer requests | `GET /veterans/transfers` | `veterans.transfers` | SCR_SPEC §4.19 | Implemented | Conventional (list) |

### System / Database (SCR-SYS-*)

| # | ID | Screen | Route | Name | Source | Status | Layout class |
|---|---|---|---|---|---|---|---|
| 51 | SCR-SYS-001 | Page not found (404) | error rendering for any 404 | n/a | SCR_SPEC §4.27 | Implemented | Conventional (error document) |
| 52 | SCR-SYS-002 | Preferences | `GET /preferences`, `PUT /preferences`, plus export/backup | `preferences.edit`, `preferences.update` | SCR_SPEC §4.28 | Implemented | Conventional (settings form) |
| 53 | SCR-SYS-003 | Session expired (419) | error rendering for any 419 | n/a | SCR_SPEC §4.29 | Implemented | Conventional (error document) |
| 54 | SCR-SYS-004 | Server error (500) | error rendering for any uncaught failure | n/a | SCR_SPEC §4.30 | Implemented | Conventional (error document) |
| 55 | SCR-SYS-005 | Database hub | `GET /database` | `database.index` | SCR_SPEC §4.31 | Implemented | Bento-primary (hub of eight area tiles) |
| 56 | SCR-SYS-006 | Race database | `GET /database/races` | `database.races` | SCR_SPEC §4.31 | Implemented | Conventional (paginated table) |
| 57 | SCR-SYS-007 | Scenario matrix | `GET /database/scenarios` | `database.scenarios` | SCR_SPEC §4.31 | Implemented | Conventional (cards + tables) |
| 58 | SCR-SYS-008 | Events reference | `GET /database/events` | `database.events` | SCR_SPEC §4.31 | Implemented | Conventional (catalog card + tables) |
| 59 | SCR-SYS-009 | Shop Items reference | `GET /database/shop-items` | `database.shopItems` | SCR_SPEC §4.31 | Implemented | Conventional (catalog card + tables) |
| 60 | SCR-SYS-010 | Sparks reference | `GET /database/sparks` | `database.sparks` | SCR_SPEC §4.31 | Implemented | Conventional (cards + table) |
| 61 | SCR-SYS-011 | Sources reference | `GET /database/sources` | `database.sources` | SCR_SPEC §4.32 | Implemented | Conventional (cards + ledger) |
| 62 | SCR-SYS-012 | Help and documentation | `GET /help` | `help` | SCR_SPEC §4.33 | Implemented | Conventional (cards/documentation) |
| 63 | SCR-SYS-013 | Database trainees | `GET /database/trainees` | `database.trainees` | SCR_SPEC §4.34 | Implemented | Conventional (shared catalog list) |
| 64 | SCR-SYS-014 | Database support cards | `GET /database/supports` | `database.supports` | SCR_SPEC §4.34 | Implemented | Conventional (shared catalog list) |
| 65 | SCR-SYS-015 | Database skills | `GET /database/skills` | `database.skills` | SCR_SPEC §4.34 | Implemented | Conventional (shared catalog list) |
| 66 | SCR-SYS-016 | Race detail | `GET /database/races/{id}` | `database.races.show` | SCR_SPEC §4.34 | Implemented | Conventional (detail) |

### Shell (not a screen, but inspected)

| ID | Screen | Route | Name | Source | Status |
|---|---|---|---|---|---|
| SCR-SYS-Global nav | Global navigation shell | `resources/js/layouts/AppLayout.vue` | n/a | SCREEN_SPEC §2, §4.29 | Implemented |
| SCR-SYS-SetupLayout | Wizard shell | `resources/js/layouts/SetupLayout.vue` | n/a | SCREEN_SPEC §4.22-4.25 | Implemented |

## Action endpoints (no screens of their own)

`runs.store`, `runs.update`, `runs.destroy`, `runs.export/{format}`, `runs.turns.store`, `runs.turns.update`, `runs.turns.destroy`, `runs.skills.sync`, `runs.deck.sync`, `runs.purchases.store`, `runs.import.store`, `review.resolve`, `preferences.update`. Not assessed as separate screens.

## Coverage totals

| Metric | Count |
|---|---|
| Total user-facing screens discovered (route-based, SCREEN_SPEC matrix) | 66 |
| Screens inspected (source code + SCREEN_SPEC state tables) | 66 |
| Screens with distinct rendered layouts reviewed | 66 |
| Screens retired / redirected (no standalone render) | 1 (SCR-RUN-003 → Cockpit) |
| Screens built but gate not green / not landed | 2 (SCR-VET-004 gate RED; SCR-VET-003 built) |
| Screens marked source-only (no live browser verification) | 66 |
| Layout classifications: Bento-primary | 4 (Dashboard, Cockpit, Scenario detail, Database hub) |
| Layout classifications: Bento-hybrid | 12 (see 02 for detail) |
| Layout classifications: Conventional | 48 |
| Layout classifications: Approved exception | 0 explicit; HFW overrides recorded for 5 career screens |
| Unverified theme/viewport combinations | All 18 (3 themes × 4 viewports × 66 screens — source-only inference) |

## Verification notes

- `SCR-VET-004` (Veteran comparison): gate RED at audit time — `Compare.vue:85` sends `router.get` the key `'veterans[]'` over an array, so the request refuses it. Two-column render still unasserted in a browser. Layout assessment below reflects source structure only.
- `SCR-VET-003` (Save Veteran): built, gate green; browser spec written and unrun at audit time per SCREEN_SPEC.
- `SCR-RUN-003` (Run detail): retired 2026-10-09 (F2); `GET /training-runs/{run}` 302s to `runs.cockpit`. No standalone render to assess.
- `SCR-CAR-019` through `SCR-CAR-024` are scenario panels mounted inside the Cockpit's scenario region; they have no standalone route and are assessed as sub-components of the Cockpit.
- Route count from `routes/web.php`: 368 lines, ~70 named routes; screen inventory deduplicates action endpoints and nested turn-edit routes into 66 distinct screens.

---

## Re-validation — 2026-11-23 (modernization pass)

**Repository HEAD at re-validation:** `3600741e`
**Document baseline:** 2026-11-22
**Status:** Reviewed; no source changes affecting this document's claims since baseline.
**Limitations:** No local server available for live re-measurement; all claims rest on source-static inspection.
