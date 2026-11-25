# Consolidated SITREP — Backend Support & Telemetry (Workstream Group G)

**Generated:** consolidation of 2 source SITREPs  
**Scope:** Telemetry observability (Langfuse + Latitude), supporting UI/UX backend slices, Hermes bento checkpoint reconciliation  
**Source files:** 2 reports from 2026-11-22 and 2026-11-23  
**Disposition:** Standalone workstream report; not a master SITREP; original sources archived in `docs/sitrep/archive/`

---

## 1. Source inventory

| # | Source SITREP | Date | SHA-256 | Role in consolidation |
|---|---|---|---|---|
| G1 | `sitrep-telemetry-supporting-backend-2026-11-22.md` | 2026-11-22 | `3683f502b463af3805922e11d0d321aa97c57cddea8fd0bc531423e922d39be5` | Telemetry plumbing (Langfuse + Latitude), five supporting backend slices, regression sweep |
| G2 | `sitrep-backend-hermes-bento-support-2026-11-23.md` | 2026-11-23 | `e3947872f3dd1b3ba7af17df089eba7a65d453ca0dbe538e2629d9d7f90e4aa1` | Hermes bento checkpoint §11 reconciliation, KI-169, N3.27 |

---

## 2. Consolidated findings

### 2.1 Telemetry: scope finding (G1)

**Requested:** Langfuse + Latitude in "dsh, minimax code."

**Actual:** Zero MiniMax / LLM / DeepSeek code exists in this Laravel workspace. DSH is at `C:\Users\exatf\AppData\Local\Programs\DeepSeek Harness\resources\app.asar` (packaged `.asar`), MCode scaffolding at `C:\Users\exatf\.minimax\mcode-observability\` — both outside allowed workspace and unreadable.

**Result:** Telemetry plumbing implemented and tested; no call sites to instrument yet. Expected deliverable — wiring ready for when MiniMax code arrives.

### 2.2 Telemetry: files created/modified (G1)

**7 new files:**

| File | Purpose |
|---|---|
| `config/langfuse.php` | Config; `enabled=false` opt-in; keys: base_url, public/secret key, service_name, environment, user/session tracing |
| `config/latitude.php` | Config; `enabled=false` opt-in; keys: endpoint, api_key, project_slug |
| `app/Services/LangfuseTraceService.php` | OTLP client → `jp.cloud.langfuse.com/api/public/otel/v1/traces` (Basic Auth `publicKey:secretKey`). Methods: `generation(array $params)`, `flush()`. No-op when disabled; failures swallowed. |
| `app/Http/Middleware/LatitudeTraceMiddleware.php` | Wraps `/api/v1/*` in OTLP server span → `ingest.latitude.so/v1/traces` (Bearer + `X-Latitude-Project`). Exports after `$next($request)`; failures logged & swallowed. |
| `routes/api.php` | Registered `LatitudeTraceMiddleware::class` on `/api/v1/*` group. |
| `tests/Feature/TelemetryConfigTest.php` | 6 tests: both telemetry defaults `false`; enabling works; base URL/endpoint defaults. |
| `.env.example` | +18 placeholder vars: `LANGFUSE_ENABLED=false`, `LANGFUSE_SECRET_KEY`, `LANGFUSE_PUBLIC_KEY`, `LANGFUSE_BASE_URL`, `LANGFUSE_SERVICE_NAME`, `LANGFUSE_TRACING_ENVIRONMENT`, `LANGFUSE_USER_TRACING=false`, `LANGFUSE_SESSION_TRACING=false`, `LATITUDE_TRACING_ENABLED=false`, `LATITUDE_ENDPOINT`, `LATITUDE_API_KEY`, `LATITUDE_PROJECT`. |

**1 modified file:**
- `PRD.md` — Amended NFR-1 (Locality) and NFR-19 (Local observability) with dated exception `2026-11-21 (owner)`: Langfuse/Latitude telemetry permitted using Trainer's personal accounts (GitHub: IzzatFirdaus). Outbound calls to `cloud.langfuse.com` and `ingest.latitude.so` are exceptions to declared-fetch-sources rule. Both opt-in (`enabled: false` default), carry only LLM call traces, no Trainer data.

### 2.3 Telemetry: test results (G1)

```bash
php artisan test --filter='TelemetryConfigTest|ApiV1Test'
# 13 passed (36 assertions)
```

**Commit:** `6dc1b41d` — `feat(telemetry): add Langfuse and Latitude observability plumbing`

### 2.4 Supporting UI/UX backend slices (G1)

Five controller slices + 7 test files, all green. Regression sweep: **100 passed / 1592 assertions**.

| Slice | Controller | Tests | Result |
|---|---|---|---|
| N3.27 | `ScenarioSelectController::store()` + `show()` | `CareerScenarioSelectTest` | 22 passed / 311 assertions |
| N3.31 | `SnapshotController::create()` + `comboboxRoster()` | `SnapshotEntryTest` | 6 passed / 93 assertions |
| N3.7 | `RaceDecisionController::show()` | `CareerRaceDecisionTest` | 27 passed / 452 assertions |
| N6.5 | Per-source tier (`config/uma.php`, `config/reference.php`) | `DatabaseTest` | 20 passed / 464 assertions |
| TR-01 | `ReviewController::entityOf()` + `sourceKeysForEntity()` | `ReviewQueueTest` | 11 passed / 128 assertions |
| BENO-VET-001 | Veteran comparison wire forms | `VeteranCompareTest` | 11 passed / 111 assertions |

**Slice details:**

**N3.27:** `store()` flashes status notice on scenario change with stored build target; `show()` returns `statusNotice` prop alongside `scenarios`, `current`, `hasActiveRun`. Five required cases pass: changed scenario with target → warning naming step 3; re-post same scenario with target → "Scenario set."; change with no target → "Scenario set."; scenario and target both preserved; `statusNotice` carries exact warning on redirected GET, `null` on plain render.

**N3.31:** `create()` returns `roster` prop with `TraineeRow` shape (confirmed cards + cardless rows); `comboboxRoster()` mirrors `TrainingRunController` shape for reuse.

**N3.7:** Returns `raceFatigue` object `{chain, turn, source}` from `TrainingRun::latestFatigue()`. Never derives fatigue from rest turns — source of truth is `TrainingRun` model.

**N6.5 (Owner Approved 2026-11-22):** `config/uma.php`: 13 `gametora-*` entries → tier B (community wiki); `config/reference.php`: 4 transcribed entries → tier S/A (official + owner-verified). Conflicts carry **no tier** by design (explicit over implicit).

**TR-01:** Classification by `source_key` per ADR-0037 (not payload `entity`); `whereIn` filter replaces `payload->entity` query. 2 new tests + 1 amended.

**BENO-VET-001:** Wire forms accepted (indexed brackets); bare scalar refused (422); duplicate `veteran_id` refused (422).

### 2.5 Quality gates (G1)

| Gate | Telemetry files | Supporting controllers | Test files |
|---|---|---|---|
| PHP Syntax (`php -l`) | PASS (7 files) | PASS (5 files) | PASS |
| Pint | PASS — fixed (10 style issues via `pint --repair`) | PASS — all clean | PASS |
| PHPStan | PASS — 0 errors (fixed) | PASS — 0 errors on all 5 | 158 errors — all Pest DSL (pre-existing, not recent work) |

**PHPStan fixes applied (19 errors resolved):**
- `LangfuseTraceService.php`: added `@var \Illuminate\Http\Client\PendingRequest` type hint on `$request`; added `@var array{0: int, 1: int}` cast on `hrtime(true)` return in `nanoTime()`
- Other fixes across telemetry files (10 style issues resolved via Pint)

### 2.6 Hermes bento checkpoint reconciliation (G2)

**Checkpoint:** Hermes `04-remediation-roadmap.md` §11 (`master`, HEAD `3600741e`, 166 porcelain entries). Scope: reconcile backend contracts only; no full backend audit, no frontend or browser-spec edits.

**Repository state:** HEAD `3600741e`; `origin/master` at `1b987cec`. Working tree: 166 porcelain entries at entry and exit (peer telemetry, advisor AI, bento frontend, packaging, docs work preserved byte-intact). No staging, commit, reset, rebase, or push. Four regression files (`VeteranCompareTest.php`, `SnapshotRefusalPersistenceTest.php`, `ReviewQueueTest.php`, `CareerScenarioSelectTest.php`) tracked and committed in HEAD. No untracked preservation risk.

**Hermes reconciliation (read-only):**
- **H-1 `Dashboard.vue`:** adds `role="region"`, `tabindex="0"`, `aria-label` to Recent veterans scroller. Server already supplies `recentVeterans = {rows, total, limit, library_url}` from `ListVeterans`, rows carrying trainee/scenario/saved-date/run URL and honest null/absence values (`DashboardTest` pins empty rows with `total: 0` and `activeCareer: null`). No prop change.
- **H-2 `Review/Detail.vue`:** adds `role="region"` + `tabindex="0"` to existing "Other candidates" card, reusing heading name. Server already supplies `candidate.entity` classified from `source_key` (ADR-0037), `otherForms` and verdict URLs; no entity-type or action change. No prop change.
- Peer change in `Review/Index.vue` (frontend-owned) now labels race rows from `source_key`, matching controller's existing ADR-0037 classifier. No backend change.
- `04-remediation-roadmap.md` §11 supersedes earlier §§6–10 statuses: **KI-144 / BENO-COCKPIT-001 is closed** (Career tools disclosure no longer renders), so no owner gate is open on the backend. ND-13, 768px proposal, G-18 remain owner/environment items, not PHP domain defects.

### 2.7 KI-169 — Review Queue data contract: BACKEND SATISFIED / FRONTEND IMPLEMENTATION PENDING

`ReviewController::index()` (`/review`, Inertia component `Review/Index`) supplies:

| Prop | Definition | Query scope |
|---|---|---|
| `candidates.total` | Pending rows (`status = Pending`) counted by paginator | Filtered by `entity` / `match_tier` when present; otherwise whole pending queue |
| `resolvedToday` | Rows with `status != Pending` and `whereDate(updated_at, today())` | Global (not filter-scoped) |
| `resolvedCount` | All rows with `status != Pending`, all time | Global (not filter-scoped) |
| `neverFetched` | `MatchCandidate::query()->count() === 0` | Global; distinguishes "never fetched" from "queue worked through" |
| `candidates.data[]` | `{id, proposed_name, proposed_name_ja, entity, match_tier, match_tier_label, source_key, created_at, suggestion, suggested_umamusume_id, review_url}` | Filtered, paginated at 25 |

Semantics: `status` leaves `Pending` only on verdict action; `updated_at` is moment of decision; all-time count independent of rows pending on other pages. Pinned by `ReviewQueueTest` (28 pending → 27 after one verdict; `resolvedCount = 1`, `resolvedToday = 1`). Frontend already consumes both wireframe numbers in its `dl` (`Review/Index.vue:137-150`) and `resolvedCount` for progress bar. Failure at `review.spec.ts:372` is only absence of two `.tile` markers in wireframe's right-hand "Why this exists" card; app prints no `.tile` class. Presentation, not missing data. No duplicate count query; no counting-semantics change made.

**Caveat for frontend:** when `entity`/`match_tier` filters active, `candidates.total` is *filtered* pending count while resolved counts stay global. If restored tiles meant to be unfiltered totals, consume on unfiltered index or state filter scope in copy; do not ask backend to silently change one count's scope.

### 2.8 N3.27 — conditional stale-build-target warning: VERIFIED

`ScenarioSelectController::store()` sets warning only when posted scenario is non-null, a different scenario was already recorded, **and** `SetupDraft::buildTarget() !== null`. Merge in `SetupDraft::write()` keeps both new scenario and target; `planningRun()` is unsaved model, so no run created or modified. Flash (`->with('status', ...)`) travels to next render as `statusNotice`, one-request only; plain render supplies `null`.

`CareerScenarioSelectTest` passes all five required cases. **Browser verification still owed to frontend:** save build target at step 3, return to step 1, change scenario, assert `role="status"` line reads "Scenario changed. The build target you entered was read against the previous scenario, so review it at step 3."; then re-post same scenario → "Scenario set."; change scenario with no target → "Scenario set.".

### 2.9 Implementation and verification (G2)

- Confirmed backend defects: none. Corrections applied: none. Final diff: only this handoff document.
- `LOG_CHANNEL=stderr vendor/bin/pest tests/Feature/ReviewQueueTest.php` — passed.

---

## 3. Cross-stream notes relevant to consolidation

- No application source, frontend component, backend route, migration, config, dependency, or test was modified outside documented changes.
- Nothing was committed, staged, or pushed by this workstream except G1's commit `6dc1b41d` (telemetry plumbing).
- KI-169 backend side is satisfied; frontend implementation pending.
- N3.27 browser verification owed to frontend workstream.