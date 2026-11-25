# SITREP — independent backend support for the Hermes bento checkpoint (2026-11-23)

Checkpoint: Hermes `04-remediation-roadmap.md` §11 (`master`, HEAD `3600741e`, 166 porcelain entries).
Scope: reconcile backend contracts only; no full backend audit, no frontend or browser-spec edits.
No plan slice was dispatched for this verification; it reports on the §11 Hermes checkpoint.

## Repository state

- Branch `master`, HEAD `3600741e3a67f06e542337de4ad7a517a7ffa188`; `origin/master` at `1b987cec`.
- Working tree: 166 porcelain entries at entry and at exit (peer telemetry, advisor AI, bento
  frontend, packaging and docs work preserved byte-intact). No staging, commit, reset, rebase or push.
- The four regression files named in the previous handoff (`VeteranCompareTest.php`,
  `SnapshotRefusalPersistenceTest.php`, `ReviewQueueTest.php`, `CareerScenarioSelectTest.php`)
  are tracked and committed in HEAD (`3600741e`, "feat(backend): complete five supporting UI/UX
  backend slices"). There is no untracked preservation risk for that evidence.
- Files added by this pass: this sitrep only. No PHP, route, frontend, browser-spec or data file changed.

## Hermes reconciliation (read-only)

- **H-1 `Dashboard.vue`** adds `role="region"`, `tabindex="0"`, `aria-label` to the Recent veterans
  scroller. The server already supplies `recentVeterans = {rows, total, limit, library_url}` from
  `ListVeterans`, with rows carrying trainee/scenario/saved-date/run URL and honest null/absence
  values (`DashboardTest` pins empty rows with `total: 0` and `activeCareer: null`). No prop change.
- **H-2 `Review/Detail.vue`** adds `role="region"` + `tabindex="0"` to the existing "Other candidates"
  card, reusing its heading name. The server already supplies `candidate.entity` classified from
  `source_key` (ADR-0037), `otherForms` and verdict URLs; no entity-type or action change. No prop change.
- Peer change in `Review/Index.vue` (frontend-owned) now labels race rows from `source_key`, matching
  the controller's existing ADR-0037 classifier. No backend change.
- `04-remediation-roadmap.md` §11 supersedes the earlier §§6–10 statuses: **KI-144 / BENO-COCKPIT-001
  is closed** (the Career tools disclosure no longer renders), so no owner gate is open on the backend.
  ND-13, the 768px proposal and G-18 remain owner/environment items, not PHP domain defects.

## KI-169 — Review Queue data contract: BACKEND SATISFIED / FRONTEND IMPLEMENTATION PENDING

`ReviewController::index()` (`/review`, Inertia component `Review/Index`) supplies:

| Prop | Definition | Query scope |
|---|---|---|
| `candidates.total` | Pending rows (`status = Pending`) counted by the paginator | Filtered by `entity` / `match_tier` when those query parameters are present; otherwise the whole pending queue |
| `resolvedToday` | Rows with `status != Pending` and `whereDate(updated_at, today())` | Global (not filter-scoped) |
| `resolvedCount` | All rows with `status != Pending`, all time | Global (not filter-scoped) |
| `neverFetched` | `MatchCandidate::query()->count() === 0` | Global; distinguishes "never fetched" from "queue worked through" |
| `candidates.data[]` | `{id, proposed_name, proposed_name_ja, entity, match_tier, match_tier_label, source_key, created_at, suggestion, suggested_umamusume_id, review_url}` | Filtered, paginated at 25 |

Definitions: `status` leaves `Pending` only on the verdict action, so `updated_at` is the moment of
decision; the all-time count is independent of rows still pending on other pages. These semantics are
pinned by `ReviewQueueTest` (28 pending -> 27 after one verdict; `resolvedCount = 1`, `resolvedToday = 1`).
The frontend already consumes both wireframe numbers in its `dl` (`Review/Index.vue:137-150`) and
`resolvedCount` for the progress bar. The failure at `review.spec.ts:372` is only the absence of two
`.tile` markers in the wireframe's right-hand "Why this exists" card; the app prints no `.tile` class.
That is presentation, not missing data. No duplicate count query and no counting-semantics change made.

Caveat for the frontend: when `entity` / `match_tier` filters are active, `candidates.total` is the
*filtered* pending count while the two resolved counts stay global. If the restored tiles are meant
to be unfiltered totals, consume them on the unfiltered index or state the filter scope in the copy;
do not ask the backend to silently change one count's scope.

## N3.27 — conditional stale-build-target warning: VERIFIED

`ScenarioSelectController::store()` sets the warning only when the posted scenario is non-null, a
different scenario was already recorded, **and** `SetupDraft::buildTarget() !== null`. The merge in
`SetupDraft::write()` keeps both the new scenario and the target; `planningRun()` is an unsaved model,
so no run is created or modified. The flash (`->with('status', ...)`) travels to the next render as
`statusNotice` and is one-request only; a plain render supplies `null`.

`CareerScenarioSelectTest` passes all five required cases: changed scenario with a target -> warning
naming step 3; re-post of same scenario with a target -> "Scenario set."; change with no target ->
"Scenario set."; scenario and target both preserved; `statusNotice` carries the exact warning on the
redirected GET and is `null` on a plain render. No backend defect; no PHP change.

Browser verification still owed to the frontend (ordinary status was already exercised by Hermes):
save a build target at step 3, return to step 1, change scenario, assert the `role="status"` line reads
"Scenario changed. The build target you entered was read against the previous scenario, so review it
at step 3."; then re-post the same scenario and assert "Scenario set."; change scenario with no target
and assert "Scenario set.".

## Implementation and verification

- Confirmed backend defects: none. Corrections applied: none. Final diff: only this handoff document.
- `LOG_CHANNEL=stderr vendor/bin/pest tests/Feature/ReviewQueueTest.php tests/Feature/CareerScenarioSelectTest.php --compact`
  -> **33 passed, 439 assertions**.
- The same plus `VeteranCompareTest`, `SnapshotRefusalPersistenceTest`, `ReviewDetailPayloadTest`,
  `ReviewBulkRejectTest`, `CareerRaceDecisionTest`, `DatabaseTest`, `SnapshotEntryTest`, `DashboardTest`
  -> **125 passed, 2,003 assertions** (exceeds the historical 98/1,559 baseline; stronger because it
  covers ten named files on the current tree). All preserved integrations are green.
- Not executed (justified): full Pest and full Playwright suites (explicitly out of scope); Pint and
  PHPStan (no PHP file changed); browser specs (frontend-owned). `LOG_CHANNEL=stderr` was used
  defensively; no PHPStan scratch `tmpDir` was needed. No telemetry-file errors were re-attributed.

## Remaining work / owners

1. Frontend (KI-169 owner): restore the two `.tile` markers at the wireframe position consuming
   `candidates.total` and `resolvedToday`, keeping the server counts intact; re-run
   `tests/browser/review.spec.ts` focused case.
2. Frontend (browser verification): exercise the three N3.27 warning branches listed above.
3. Owner-gated/environment: ND-13 spark colours, the unratified 768px proposal, G-18 token contrast
   on hosts that deny its temp-path write. No backend blocker and no backend next action.
