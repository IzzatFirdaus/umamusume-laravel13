# Frontend and UX Roadmap

**Authoritative roadmap for UI/UX.** This file is the single execution authority for all frontend/UX tasks: UI/UX audit phases 0-5, rendered verification R0-R5, browser truth (spec triage), register truth (KI series), and MASTER phase-0 slice S0.1, phase-2 slices S2.2/S2.4, and phase-5 slice S5.8. Canonical IDs retained. Historical sources archived in `docs/roadmap/archive/`. Completed work is marked and preserved as historical evidence; do not re-open.

---

## 1. UI/UX Audit Implementation (from UI-UX-IMPLEMENTATION-ROADMAP-2026-11-18)

**Driving audit**: `docs/audits/UI-UX-PRODUCT-AUDIT-2026-11-18.md`

| Phase | Scope | Status at HEAD `cfd8bd78` | Evidence |
|-------|-------|---------------------------|----------|
| Phase 0 | Baseline measurement | **DONE** | Measured at HEAD |
| Phase 1 | Dashboard + Cockpit homes | **DONE** | All findings resolved |
| Phase 2 | Stat denominator defect | **DONE** | Fixed |
| Phase 3 | Routing table + screen-level findings | **DONE** | Sent to `AUDIT-AND-VERIFICATION.md` |
| Phase 4 | WCAG AA token pairs | **DONE** | `DesignTokensTest` 17 passed; ratios 4.566/5.187/7.190 ≥ 4.5:1 |
| Phase 5 (added 2026-11-19) | Error summary, IME-safe Enter, deferred props, WCAG 2.2 cases, Baseline rows | **IMPLEMENTATION LANDED IN WORKING TREE — HEAD VERIFICATION OUTSTANDING** | All five slices are present on disk (2026-11-24) but **not at HEAD `3600741e`**: `tests/browser/error-summary.spec.ts` (2 cases) exists only in the working tree (untracked); IME-safe Enter (`isComposing` / `keyCode === 229`) appears in 5 working-tree files (`Form/SearchableSkillPicker.vue`, `legacy/SparkTargetPicker.vue`, `TraineeCombobox.vue`, `ui/Combobox.vue`, `pages/Career/TrainingDetail.vue`) vs 1 at HEAD (`TrainingDetail.vue`); `Inertia::defer(..., 'cockpit-secondary', true)` in `CockpitController.php:377` plus `<Deferred>` on Cockpit is working-tree only (absent at HEAD); WCAG 2.2 cases in `accessibility.spec.ts` (HEAD 1 → working 4); Baseline rows in `DESIGN.md` §13 (HEAD 107 table rows → working 114). Gates 2026-11-24 measured the working tree: `npm run typecheck` clean, `npm run lint:ui` exit 0 (208 arbitrary vs 209 baseline), `npm run build` built, `npm run test:a11y` 13 passed, `review-findings-regression.spec.ts` 6 passed |

**Phase 5 evidence status (corrected 2026-11-24).** The five Phase 5 slices are *implemented* — the working
tree carries the specs, the IME guards, the deferred prop and the Baseline rows, and the 2026-11-24 gates
pass against that tree. They are **not** *verified at HEAD* `3600741e`, and "DONE" without that qualifier
was the inconsistency this row corrects. Three distinct gaps remain:

1. **Whole-surface coverage at HEAD** — the slices are measured only where they landed, not across the full
   Phase 5 surface (every Enter-handling control, every WCAG 2.2 target, every screen the Baseline table
   claims to cover).
2. **Untracked evidence** — `tests/browser/error-summary.spec.ts` and `tests/Feature/RaceEntryObjectiveIndexBoundaryTest.php`
   are untracked; until committed they pin nothing for a clean clone.
3. **Ordering** — `RECONCILIATION-AND-ACTIONABILITY.md` §1 still carries Phase 5 as **OPEN** ("not measured
   against HEAD `3600741e`"), which is the correct status for the *verification* claim; only the
   implementation half has moved. Both statements are now true simultaneously and are distinguished here.

**KI-166**: `/training-runs/{run}/races` overflows 59px at 320px → **CLOSED 2026-11-18**
- Fix: `.card:has(> table.t)` gains `position: relative` (establishes containing block)
- A/B measured on session-private DB at 320x900: Race Decision 59→0, Race Planner 40→0
- `career-race-decision.spec.ts` gained 3 occurrences of `320` as regression case
- Register row: `KI-166 CLOSED 2026-11-18`

**Phase 5 verify-command drift.** The archived roadmap's §5.3 verify line runs
`php artisan test --filter=DeferredProps`, which matches no test class. The acceptance it describes is
pinned by `GrandConcertPanelTest::ships_the_five_Performance_types_and_no_invented_reading_for_them`
(`loadDeferredProps('cockpit-secondary')`); §5.1/§5.2's spec names and §5.4's `npm run test:a11y`
match real files.

---

## 2. KI-167 Conflict Resolution

| Source | Claim | Resolution |
|--------|-------|------------|
| UI-UX §11/§12 | "Root cause NOT established, just filed" | **SUPERSEDED** |
| EXECUTION-RECORD S2.2 | "DONE — root cause found and fixed" | **AUTHORITATIVE** |
| **Root cause** | `:disabled="form.processing"` on scenario radio | Fixed |
| **Evidence** | `EXECUTION-RECORD` S2.2 explicitly states DONE with root cause | Used |

**Decision**: EXECUTION-RECORD is later and measured against live tree; UI-UX roadmap §11/§12 reflects earlier state. Canonical status: **DONE — root cause fixed**.

---

## 3. Rendered Frontend Verification (from FRONTEND-RENDERED-VERIFICATION-ROADMAP-2026-11-22)

**Base commit**: `829ef0ac` | **Integration HEAD**: `3600741e`

| Phase | Scope | Status | Details |
|-------|-------|--------|---------|
| R0 | Harness | **DONE** | Playwright session-private harness operational |
| R1 | Route reconciliation — 59 GET routes | **DONE** | All 59 routes mapped |
| R2 | Rendered defects R2.1-R2.5 | **DONE** | All 5 defect classes resolved |
| R3 | Screens left alone | **DONE** | Intentionally unmodified screens catalogued |
| R4 | Remaining work R4.1-R4.5 | **DONE** | Measurements on isolated scratch DB |
| R5 | Integration verification at HEAD `3600741e` | **DONE** | All DONE; R4.3 error flow **PARTIALLY VERIFIED** |

### R4.3 Error Flow — Partially Verified
- 419 (CSRF) cannot be produced via browser navigation due to Laravel 13 `PreventRequestForgery::hasValidOrigin()`
- Other error flows verified on isolated scratch DB
- **Status**: `PARTIALLY VERIFIED` — 419 path not browser-reproducible

### AdvisorAnalysisPanel.vue — Untracked Peer Work (RESOLVED 2026-11-24)
- Component is **untracked peer work**, not modified by this consolidation
- Disclosure `<summary>` element: previously measured 279×20px; **now corrected to 279×44px** (44px target, spacing exception satisfied)
- **RESOLVED**: `BENO-COCKPIT-001` closed; `career-cockpit-advisor-ai.spec.ts` green across 1280/768/390/320px and three themes (light/dark/eva01)
- **Not a WCAG 2.5.8 failure** — 44px exceeds the 24px minimum floor and the spacing exception is satisfied

---

## 4. Browser Truth (from PROJECT-ROADMAP-2026-11-16 Phase B, REMAINING-WORK Phase 2)

| Item | Status | Details |
|------|--------|---------|
| 15 failing specs (38 failed tests at `3cbbd8b9`) | **TRIAGED** | 3 stale specs identified (literals absent from `resources/js`); 12 need isolated re-run |
| Stale specs (literal absent) | **DELETE/REWRITE** | `_screens-indep-training.spec.ts`, `career-preflight-indep.spec.ts`, `accessibility.spec.ts` — assert removed controls |
| Counter-spec | **PROTECTED** | `career-preflight.spec.ts` asserts Phase E2 cut (`toHaveCount(0)`) — **must not touch** |
| Specs needing isolated re-run | **OPEN** | `career-deck-summary` (6), `career-trainee-select` (6), `career-legacy-deck-steps` (4), `career-profile` (8, timeout in `beforeEach`), `career-surface-cutover` (2), `career-deck-select` (1), `career-race-planner` (1), `career-race-strip` (1), `career-scenario-select` (1), `career-snapshot` (1), `catalog-detail` (1), `catalog-to-wizard` (1) |
| `career-profile.spec.ts` | **ISOLATED RUN 2026-11-24** | 8 passed / 1 failed in an isolated worktree at `3600741e`. The one failure expects `[data-region="phead"] img[data-artwork-slot]`, which is absent because the isolated environment has no artwork mirror (`AGENTS.md` §8: an unrun mirror renders nothing). Classified as a verification-environment limitation, not a product regression; no assertion was weakened. |

**Isolated re-run discipline**:
```bash
PLAYWRIGHT_PORT=8191 \
PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-8191.sqlite \
npx playwright test tests/browser/<spec>.spec.ts --workers=1 --reporter=line
```

---

## 5. Register Truth — UI/UX Entries (from PROJECT-ROADMAP Phase A, REMAINING-WORK Phase 1)

| KI | Verdict | Evidence |
|----|---------|----------|
| KI-152 | **ALREADY-FIXED** | `Catalog/Show.vue:44-46,150-152` renders `base_stats`/`growth_rates` |
| KI-144 | **ALREADY-FIXED** | `Cockpit.vue` 0 live disclosures; `ConfirmDelete.vue` own door |
| KI-148 | **TERMINAL** | Purged by direct delete, no code change |
| KI-140 | **ALREADY-FIXED** | `FilterContractTest` no longer exists in `tests/` |
| KI-151 | **ALREADY-FIXED** | `--text-kpi` 0 hits in `resources/css` |
| KI-137 | **FIX** | `full-career-journey.spec.ts` 3 stale/ambiguous assertions |
| KI-136 | **FIX** | Same spec, locator matches 2 disclosures (peer-owned — coordinate) |
| KI-141 | **ALREADY-FIXED** | Index `KNOWN-ISSUES.md:92` CLOSED; `BuildTarget.vue` renders `AbsenceValue` with visible reasons |
| KI-159 | **ALREADY-FIXED** | Index `KNOWN-ISSUES.md:110` CLOSED; deck-step `title`-only reasons removed and index corrected |
| KI-143 | **FIX** | `review-detail.spec.ts` describes unrendered variant |
| KI-147 | **FIX** | `career-race-strip.spec.ts` walks strip cockpit stopped rendering |
| KI-160 | **FIX** | Cockpit overflow 88px at 640px → `NextDecision.vue` (DONE) |
| KI-145 | **FIX** | `lint:ui` 282 arbitrary values vs 219 baseline → `ui-lint-baseline.json` |
| KI-150 | **TERMINAL** | Page-level sticky unavailable (option 3); `AppLayout.vue:615-618` `@container` load-bearing |
| KI-142 | **TERMINAL** | Duplicate block at `:4621`; second copy must be deleted |
| KI-149 | **TERMINAL** | `.card > h2` 15→16px, 13 screens unmeasured — pending measurement pass |

**Acceptance**: All 17 entries carry exactly one of `FIXED`, `TERMINAL`, `ALREADY-FIXED` + command evidence. Duplicate blocks (KI-142) collapse to one.

---

## 6. Source-to-Destination Register

| Source File | Source Section | Destination | Status |
|-------------|----------------|-------------|--------|
| `UI-UX-IMPLEMENTATION-ROADMAP-2026-11-18.md` | Phases 0-5, KI-166, KI-167 | §1, §2 | Consolidated |
| `frontend-rendered-verification-roadmap-2026-11-22.md` | Phases R0-R5 | §3 | Consolidated |
| `project-roadmap-2026-11-16.md` | Phase B (browser truth) | §4 | Consolidated |
| `project-roadmap-2026-11-16.md` | Phase A (register truth) | §5 | Consolidated |
| `remaining-work-roadmap-2026-11-17.md` | Phase 2 (test-suite truth), Phase 3 (defects) | §4, §5 | Consolidated |
| `EXECUTION-RECORD-2026-11-18.md` | S2.2 (KI-167) | §2 | Conflict resolved |
| `RESIDUAL-VERIFICATION-2026-11-18.md` | Browser-related slices | §4 | Consolidated |

---

## 7. Open Items Requiring Action

| Item | Owner | Blocker | Next Step |
|------|-------|---------|-----------|
| Phase 5 UI/UX items (error summary, IME-safe Enter, deferred props, WCAG 2.2, Baseline rows) | Frontend | **IMPLEMENTATION LANDED — working tree only; not at HEAD `3600741e`** (see §1 evidence-status note) | Commit the working-tree slices (`tests/browser/error-summary.spec.ts`, IME guards in 4 further components, `CockpitController.php` deferred prop, `accessibility.spec.ts` WCAG 2.2 cases, `DESIGN.md` §13 rows), then re-verify the whole Phase 5 surface at the resulting HEAD |
| Remaining specs from the isolated-triage list | QA | Only `career-profile` was re-run in this pass; its failure is an artwork-fixture limitation | Run the remaining specs on a session-private harness with an explicit artwork-mirror decision; classify each result before the full sweep |
| `lint:ui` arbitrary values (measured 208 vs 209 baseline; exit 0) | Frontend | Debt paydown or enumerate with reasons | Update `ui-lint-baseline.json` or fix values |
| AdvisorAnalysisPanel.vue disclosure (measured 279×44 at 1280/768/390/320, three themes; **not a WCAG 2.5.8 failure** — exceeds 44px floor with spacing exception) | Peer owner | **RESOLVED 2026-11-24** — disclosure corrected to 44px target by peer; `career-cockpit-advisor-ai.spec.ts` green across all widths/themes; `BENO-COCKPIT-001` closed | No action required |
| R4.3 error flow 419 | QA | Laravel 13 `PreventRequestForgery::hasValidOrigin()` blocks browser reproduction | Document as partially verified; no code fix possible |

---

## 8. Rendered Verification - 2026-11-22 Frontend Browser Review

Source findings: `docs/audits/2026-11-22-frontend-browser-review/01-findings.md` (TD-01..TD-08). That
audit's own 2026-11-23 re-validation was read-only source inspection and marked all eight OPEN. Each
was re-measured against the rendered application on a session-private database, 2026-11-24:

| ID | Severity | Rendered result | Evidence |
|----|----------|-----------------|----------|
| TD-01 | High | **FIXED** - Run Settings, Correct Turn and Delete confirmation all address `{run_url}/cockpit`, checked at 1280px and again at 390px | `tests/browser/review-findings-regression.spec.ts` |
| TD-02 | High | **FIXED** - a `gametora-races` row reads `Race · from gametora-races`; its case asks `Is this the right race?` and offers no Umamusume id control | same spec |
| TD-03 | Medium | **FIXED** - the row is labelled `Open source career`, bound to `run_url`, and the Veteran door stays explicit | `tests/browser/dashboard.spec.ts` |
| TD-04 | Medium | **FIXED** - a completed career keeps the nine-step spine, with Result live and marked current | `review-findings-regression.spec.ts` |
| TD-05 | Medium | **FIXED** - the voice credit row is labelled `Voice actor` with `va_ja`/`va_en` intact | `tests/browser/catalog-detail.spec.ts` |
| TD-06 | Medium | **FIXED** - the ~180-character absence sentence appears in zero text nodes (sr-only included); one group Note carries it, each marker a short label | `review-findings-regression.spec.ts` |
| TD-07 | Medium | **FIXED** - Snapshot Setup picks the trainee through `#trainee-combobox`, the shared searchable control | `tests/browser/career-snapshot.spec.ts` |
| TD-08 | Low | **FIXED** - Kind / Read date / Tier filters narrow the ledger and announce `N of M sources match.` | `review-findings-regression.spec.ts` |

The same file also pins the three-theme contract: Light, Dark and Evangelion Unit-01 are selectable,
each moves the measured card surface off the system palette, and the choice survives a reload.

**Backend dependency carried forward.** The Dashboard payload carries only `run_url`, so the audit's
alternative correction for TD-03 (binding the Recent Veterans row to `veteran_url`) needs a server
field and an owner decision. The sanctioned label correction shipped instead.

```bash
LOG_CHANNEL=stderr PLAYWRIGHT_PORT=8435 PLAYWRIGHT_SCRATCH_DB=$PWD/.scratch-uma/frontend-findings-2026-11-24/browser-8435.sqlite \
  npx playwright test tests/browser/review-findings-regression.spec.ts --workers=1   # 6 passed
LOG_CHANNEL=stderr PLAYWRIGHT_PORT=8432 PLAYWRIGHT_SCRATCH_DB=$PWD/.scratch-uma/frontend-findings-2026-11-24/browser-8432.sqlite \
  npx playwright test tests/browser/dashboard.spec.ts tests/browser/catalog-detail.spec.ts tests/browser/career-snapshot.spec.ts --workers=1   # 41 passed
```

---

## 9. Phase 5 Final Status Reconciliation (2026-11-24)

**Frontend session verdict (2026-11-24):** **IMPLEMENTATION LANDED — working tree verified.** All five Phase 5 slices are present and pass their gates on the live tree:
- Error summary: `tests/browser/error-summary.spec.ts` (2 cases) ✓
- IME-safe Enter: `isComposing`/`keyCode===229` guard in 5 components ✓
- Deferred props: `Inertia::defer(..., 'cockpit-secondary', true)` + `<Deferred>` on Cockpit ✓
- WCAG 2.2 cases: `accessibility.spec.ts` expanded from 1 → 4 cases (13 passed) ✓
- Baseline rows: `DESIGN.md` §13 expanded (114 rows, two overstatements corrected by measurement) ✓
- Gates: `typecheck` clean, `lint:ui` exit 0 (208 vs 209 baseline), `build` OK, `test:a11y` 13 passed, `review-findings-regression.spec.ts` 6 passed ✓

**Fullstack session verdict (2026-11-23):** **PARTIALLY VERIFIED at HEAD `3600741e`.** The slices exist only in the working tree, not at HEAD. R5 at HEAD (`FRONTEND-AND-UX-ROADMAP.md:72`) reports "All DONE; R4.3 error flow PARTIALLY VERIFIED". The PARTIALLY VERIFIED qualifier applies **only** to R4.3 (419 error path, blocked by Laravel 13 CSRF origin check), not to Phase 5 implementation. Phase 5 slices are **not** at HEAD — they are working-tree changes awaiting commit.

**Reconciled status:**
- **Individual Phase 5 slices: IMPLEMENTATION COMPLETE.** Each slice has working-tree code + browser regression pins. Do not reopen TD-01–TD-08 without evidence of regression.
- **Whole-surface browser verification at HEAD: OUTSTANDING.** Commit the working-tree changes, then re-verify the full Phase 5 surface at the new HEAD.
- **TD-03 server-contract limitation preserved:** Dashboard payload carries only `run_url`; the alternative `veteran_url` binding requires a server field and owner decision (see §8).
- **Retired-run verification limitation preserved:** R5 at HEAD includes R4.3 PARTIALLY VERIFIED (419 path not browser-reproducible). This is a backend contract issue, not a frontend defect.

**Outstanding to reach full DONE at HEAD:**
1. Commit working-tree Phase 5 slices (error-summary spec, 4 IME guards, CockpitController deferred prop, accessibility 4 cases, DESIGN.md §13 rows)
2. Re-run full Phase 5 surface verification at resulting HEAD
3. The R4.3 PARTIALLY VERIFIED item remains documented; no code fix possible in Laravel 13

| MASTER Slice | Slice Name | Canonical Owner | Status |
|---|---|---|---|
| S0.1 | Commit UI/UX accessibility batch | FRONTEND | OPEN — owner-gated |
| S2.2 | KI-167 focus round trip | FRONTEND | DONE |
| S2.4 | KI-127 card wrapper | FRONTEND | DONE |
| S5.8 | MWG accessibility items | FRONTEND | OPEN — owner-gated peer work |

*All MASTER phase-0 S0.1, phase-2 S2.2/S2.4, phase-5 S5.8 owned by FRONTEND-AND-UX-ROADMAP.md*


---

## 10. Residual frontend and visual slices (verified 2026-11-24, dependency order)

Phase 5 implementation is landed in the working tree and TD-01..TD-08 are fixed (§8); they are not
reopened here. The slices below are the only frontend/visual items still substantiated. All run on a
session-private harness with a fresh bundle; none may be closed on static inspection alone.

| Order / ID | Category | Objective and affected components | Prerequisite | Acceptance and verification |
|---|---|---|---|---|
| 1 · F-1 | Confirmed incomplete absence disclosure | **DONE 2026-11-24.** `SparkSummary.vue` star-count absence now renders `AbsenceValue compact` whose summary carries "N/A, No star count was recorded for this spark." as its accessible name; the group tally sentence stays. | None. | `legacy.spec.ts` 12 passed (new F-1 case asserts the disclosure summary text and visible reason in the DOM without hover). |
| 2 · F-2 | Confirmed incomplete absence disclosure | **DONE 2026-11-24.** Re-audited the `:title=` occurrences: the two N/A cells (comparison L375, alignment L405) and the held/readiness labels (L253/255/260) carried mouse-only reasons. All five now render visible prose or `AbsenceValue compact` disclosures. The remaining three `:title=` (aptitude label, poor-fit chip, blocked reason) are present-value qualifiers with visible text, not absence channels. | None. | `career-race-planner.spec.ts` F-2 case passes (reward-row N/A disclosure + held/readiness visible prose); `AbsenceChannelTest` inventory did not grow; `UiHygieneTest` 5 passed. |
| 3 · F-3 | Visual/accessibility improvement | Add explicit print and forced-colors handling for `app-backdrop`, and constrain `background-attachment: fixed` on narrow viewports. Affects `resources/css/app.css:998-1005` and the error blades. | Owner decision on whether error blades should inherit the backdrop; this is a visual scope change. | Rendered probe: print and forced-colors produce a legible flat surface; 320 px has no fixed-attachment scroll cost; error shells match the chosen policy. Baseline probe on 2026-11-24 measured `image: none` under forced colors and on the 404 shell. |
| 4 · F-4 | Owner-gated visual requirement | Reconcile EVA-01 green coverage with the recorded 5-7% target. Affects the EVA-01 token ramp and card accents in `app.css`. | Owner ruling: keep the target and change the ramp, or revise the target. | Re-measure with the `DESIGN.md:577-583` pixel-classification method; currently 0.46% dashboard / 0.41% runs index against 82.1% / 82.9% purple, target unmet. Runtime backdrop rendering was probed and is not the blocker. |
| 5 · F-5 | Verification-only | Finish the isolated re-runs named in §4, then a full sweep at a settled HEAD. | Stable tree and fresh build in an isolated worktree; artwork-mirror policy for `career-profile`. | Every remaining spec green or classified with a register row; quote the command and output. No full suite in this pass. |
| 6 · F-6 | Verification-only | KI-127 composed-surface pin for the team facility panel. | The panel must first be enabled by a scenario `panels` block; it is a dead registration today. | Browser assertion on the composed Cockpit, not only the isolated mount; isolated mount already passed 2/2. |

Dependencies: F-1/F-2 are independent and need no design ruling; **both DONE 2026-11-24.** F-3 and F-4
need owner direction before implementation; F-5 follows a settled tree; F-6 follows any decision to
compose the panel.

---

*Generated: 2026-11-23. All canonical IDs retained. Historical sources in `docs/roadmap/archive/`. KI-167 resolved: EXECUTION-RECORD authoritative over UI-UX roadmap.*