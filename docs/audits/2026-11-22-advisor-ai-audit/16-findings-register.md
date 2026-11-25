# 16 — Findings Register (corrected) and Test Matrix

## A. Corrected findings register

Severities: **B** = blocks AI integration landing; **H** = high; **M** = medium; **L** = low; **—** = informational. Every row carries evidence; unverified claims from the original audit are not carried forward.

| ID | Severity | Classification | Evidence | Location | User impact | Required action | Owner | Acceptance |
|---|---|---|---|---|---|---|---|---|
| F-01 | B | **RESOLVED (landing note)** — Advisor AI frontend files exist on disk (`useAdvisorAnalysis.ts`, `services/advisor/analysis.ts`, `AdvisorAnalysisPanel.vue` wired into `AdvisorRail.vue` gated on `runUrl !== null`); runtime-verified 2026-11-23 by `career-cockpit-advisor-ai.spec.ts` (**16 passed** isolated against a fresh build). `AdvisorAnalysisPanel.vue` remains **untracked** — a release-process note, not a wiring defect | Whole frontend; backend `CockpitController::advisorAnalysis()` + route + `AdvisorAnalysisRouteTest.php` present | AI recommendations reach the Trainer when `runUrl` is set; the panel file is not versioned | Track `AdvisorAnalysisPanel.vue`; F1–F6 roadmap largely in place | DSH | A Trainer on an active career can request, read and dismiss AI analysis without the deterministic rail changing |
| F-02 | M | **Test gap closed** — 503 error-path test present | `AdvisorAnalysisRouteTest.php:64–108` forces `AdvisorAnalysis::analyze()` to throw; asserts status 503, `error.code = advisor_analysis_unavailable`, message match, no exception detail leaked, career unchanged; controller branch at `CockpitController.php:182–193` | `tests/Feature/AdvisorAnalysisRouteTest.php` | None — regression of the 503 contract is now pinned | None (test present); controller branch verified | Hermes (done) | Test asserts 503 + `advisor_analysis_unavailable` |
| F-03 | — | **Approved existing behavior** | `AdvisorAnalysis.php:21–23` "never written anywhere"; no save/update/dispatch | `app/Services/Advisor/` | None | None — read-only by design; do not add provenance writes | Hermes (already done) | — |
| F-04 | — | **Approved existing behavior** | Deadline clamped at `ClefDecisionProvider.php:72–77`, `AdvisorExplainer.php:134–139` | backend config consumers | None | None — R4 resolved in code | — (resolved) | — |
| F-05 | — | **Resolved/superseded** | Original 01/04 finding; reclassified by `12-reconciliation-log.md` R1 | `NextDecision.vue` | None today | Do **not** add fetch logic to `NextDecision.vue`; lifecycle lives in `useAdvisorAnalysis` (F3) | DSH (F3) | — |
| F-06 | — | **Resolved/superseded** | No `position: sticky` on `AdvisorRail.vue:38–42`; containment specs pin the constraint; layout asserted at 1440/390 | `AdvisorRail.vue` | None | None — original "sticky breaks @container" claim withdrawn | — | — |
| F-07 | L | **Verification gap** | Advisor viewport assertions exist at 1440/390 (`cockpit-shell.spec.ts:94,137`) but not 768/320; no AI-panel coverage exists at all | `tests/browser/` | Undetected regressions at mid/narrow widths once the panel ships | Cover 1280/768/390/320 in the new F6 spec | DSH (F5) | Matrix documented with evidence |
| F-08 | L | **Verification gap** (future) | No ARIA/status/error-announcement coverage for AI content (none exists yet) | future panel | AI-only states unannounced to AT | Keyboard, `role="status"`, `role="alert"`, native `<details>` in F2/F4/F5 | DSH | Axe pass within forms-spec scope |
| F-09 | — | **Corrected claim** | Original 07 said tokens unverified; rail uses `text-ink-strong`/`text-ink-muted`/`card` | `AdvisorRail.vue:40–43` | None | None | — | — |
| F-10 | — | **Corrected claim** | `explanation_source` is `'deterministic'\|'logfare'` (`AdvisorExplainer.php:86`), contract JSON said `'ai'` | contract file line 48 | Type mismatch would ship broken rendering | Frontend types use `'logfare'` (`14-api-contract.md` §3) | DSH (F1) | Typecheck green |
| F-11 | — | **Corrected claim** | Contract JSON claimed "user owning the run" authorization; repo has none by design (`PRD.md` NFR-1) | contract file line 148 | None (loopback tool) | Contract doc corrected (`14-api-contract.md` §1) | — (done) | — |
| F-12 | M | **Future implementation requirement** | Response can outlive the turn it was for (in-flight race); backend cache prevents only stale *cache hits* (`AdvisorAnalysis.php:62–64`) | request lifecycle | Turn 31 advice shown for turn 32 | Revision/turn guard before commit (F3) | DSH | F6-8 passes |
| F-13 | L | **Optional enhancement** | `decision.preference`/`confidence` are diagnostic (`AdvisorAnalysis.php:168`) | panel scope | None if unrendered | Not rendered in v1; a future diagnostic disclosure needs an owner decision | DSH (proposed) | — |
| F-14 | L | **Backend-dependent issue** | No ADR exists for the `advisor_ai` boundary; `config/advisor_ai.php:21–24` states one is owed | `docs/adr/` | Provenance gap at the governance layer | Write/accept the ADR | **Hermes**/Architect | ADR accepted |

## B. Test matrix

### Existing tests (verified present — do not duplicate)

| Test | What it pins | Status |
|---|---|---|
| `tests/Feature/AdvisorAnalysisRouteTest.php` (**7 tests**, 89 assertions, executed 2026-11-23) | 404 default-off; 200 JSON structure + null `recommended_action` on no target; **503 error path with no leak**; cockpit prop frozen (`missing('advisor.analysis')`); revision moves; revision carried with AI off | Present; **F-02 closed and executed** |
| `tests/Feature/CareerCockpitTest.php:154–166` | Frozen `['decision','finale']` prop keys, eight-key decision, no score/confidence/band/risk | Present |
| `tests/Feature/TrainerAdvisorTest.php` | Deterministic ranking rules, source/date constants | Present |
| `tests/Feature/AdvisorAiPipelineTest.php` (834 lines) | Provider calls under `Http::fake`, snapshot stability, budget-exhaustion determinism | Present |
| `tests/Feature/AdvisorProvenanceTest.php` (946 lines) | Decision provenance, explanation correctness | Present |
| `tests/browser/career-cockpit.spec.ts` | Advisor block order (`:344`), refusal copy, obligations table, one-primary ruling | Present |
| `tests/browser/cockpit-shell.spec.ts:94,137` | Advisor column at 1440/390px | Present |
| `tests/browser/responsive.spec.ts:11` | 56 route-widths: overflow/sticky/focus sweep | Present |
| `tests/browser/career-cockpit-forms.spec.ts` | Declared axe scope includes advisor | Present |

### Missing tests (to create)

| Test | Covers | Owner | Command |
|---|---|---|---|
| 503 error-path feature test | F-02 | **Hermes** | `php artisan test --filter=AdvisorAnalysisRouteTest` |
| `tests/browser/career-cockpit-advisor-ai.spec.ts` | F6-2..10: provider-state copy, evidence disclosure, 404/503 handling, stale rejection, no auto-execution, viewport matrix 1280/768/390/320 | DSH | `npm run test:browser` (session-private: `PLAYWRIGHT_PORT=<port> PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-<port>.sqlite`) |
| Revision-guard assert check (pure logic extracted from composable, if any) | F3 | DSH | assert-style self-check per AGENTS.md §19 |

### Focused verification commands (per phase)

| Phase | Command |
|---|---|
| F1/F3 | `npm run typecheck` |
| F2–F5 | `npm run typecheck` + new browser spec, scoped filters |
| F6 | `npm run typecheck` && `npm run build` (manifest) && `npm run test:browser` |
| Style gates at hand-off | `vendor/bin/pint --test --format agent <touched php paths>` (none expected for DSH), `npm run lint:ui` |
| Not DSH-run | PHP suite (Hermes owns backend tests this slice) |

## C. Handoff documents

- **DSH/Hermes ownership split and pending requests:** `17-dsh-hermes-handoff.md`
- **SITREP:** `18-sitrep.md`

---

## Re-validation — 2026-11-23 (modernization pass)

**Repository HEAD at re-validation:** `3600741e`
**Document baseline:** 2026-11-22
**F-01 status:** Blocking AI integration landing (B severity); `grep advisor-analysis advisorAnalysis advisor_analysis resources/js/` returns 0 hits — frontend integration completely missing
**F-02 status:** 503 error-path test gap; `tests/Feature/AdvisorAnalysisRouteTest.php` has no 503 match; controller branch exists at `CockpitController.php:182–193`
**F-12 status:** Future implementation requirement; response can outlive turn it was for; backend cache prevents stale cache hits only
**PA-01 status:** Partial — dirty-form confirm guard landed (`51d78d3e`, 14/21 values); draft-persistence arm open under consolidated remediation plan (§9.6)
**PA-02 status:** CLOSED — strict-commitment contract landed (`282e5437` → `aaf21cd3`); 9 passed at HEAD; `commitTypedText()`/`commitExactText()` removed

### Commands executed at re-validation

None — read-only source inspection.

### Limitations

No local server available for live re-measurement. Browser-verified claims rest on prior test evidence.

---

## Re-validation — 2026-11-23 (source re-check; corrects the same-day modernization-pass grep)

**Repository HEAD at re-validation:** `3600741e` (working tree preserved: 162 modified, 67 untracked — concurrent development, not a defect)
**Document baseline:** 2026-11-22
**Method:** read-only source inspection of `resources/js/`, `app/Http/Controllers/Career/CockpitController.php`, and `tests/Feature/AdvisorAnalysisRouteTest.php`. No local server; no test execution.

**F-01 — PARTIALLY RESOLVED.** The same-day modernization pass recorded `grep advisor-analysis advisorAnalysis advisor_analysis resources/js/` = 0 hits. That grep predates the shipped files: `resources/js/composables/useAdvisorAnalysis.ts`, `resources/js/services/advisor/analysis.ts` (`advisorAnalysisUrl()` derives the endpoint from `run_url`), and `resources/js/components/career/AdvisorAnalysisPanel.vue` all exist on disk, and `AdvisorRail.vue` imports the panel gated on `props.runUrl !== null`. `AdvisorAnalysisPanel.vue` is **untracked** (not in git). Evidence category: IMPLEMENTED, STATICALLY VERIFIED. Landing is still blocked by the untracked panel file and the `runUrl` gate, so the finding is partially resolved, not closed.

**F-02 — TEST GAP CLOSED.** The same-day modernization pass recorded "no 503 match". The 503 error-path test is present at `tests/Feature/AdvisorAnalysisRouteTest.php:64–108`: it forces `AdvisorAnalysis::analyze()` to throw, asserts status 503, `error.code = advisor_analysis_unavailable`, the user-facing message, that the exception's own words and token do not leak, and that the career is value-identical after the failed analysis. The controller branch it exercises exists at `CockpitController.php:182–193` (`abort_unless(config('advisor_ai.enabled'), 404)`; try/catch → 503 with `advisor_analysis_unavailable`). No action remains.

**Limitations:** read-only source inspection only; browser/runtime behaviour rests on prior test evidence.

---

## Runtime verification — 2026-11-23 (targeted executable checks)

**Method:** `npm run build` then isolated Playwright runs, each with its own scratch database and port
(`PLAYWRIGHT_SCRATCH_DB`, distinct `PLAYWRIGHT_PORT`). `PLAYWRIGHT_FRESH_BUILD=1` was set because a
concurrent session was editing unrelated files under `resources/js/` while this pass ran; the bundle was
rebuilt immediately before the run so it matches the source at that instant.

**F-02 — RUNTIME PASS.** `php artisan test --filter=AdvisorAnalysisRouteTest` → **7 passed (89 assertions)**,
including "it answers 503 with a stable code when the analysis throws, without leaking the failure". The
test gap is closed and executed, not merely present.

**F-01 — RUNTIME PASS.** `npx playwright test career-cockpit-advisor-ai.spec.ts` → **16 passed** in isolation,
covering the 404/503 error paths, loading and retry, stale-response rejection, no auto-execution, keyboard
and axe checks, at 4 widths × 3 themes. Evidence category upgraded to IMPLEMENTED, STATICALLY AND RUNTIME VERIFIED
for the panel/composable/service wiring. The landing note above (untracked panel file, `runUrl` gate) is a
release-process observation, not a defect in the wiring.

**TD-01 / TD-02 — RUNTIME PASS.** The surfaces are covered by `run-settings.spec.ts`, `turn-edit.spec.ts`,
`run-delete.spec.ts`, `review.spec.ts` and `review-detail.spec.ts`; all green (see
`2026-11-21-frontend-browser-review/01-findings.md`).

**Flakiness note.** A combined six-file serial run reported 2 failures in `review-detail.spec.ts`
("Confirm this match for Fixture Trainee 1", "Record alias" not found). Re-run in isolation: **7 passed**.
The failures were cross-spec state pollution from one shared scratch database across six spec files, not a
code defect; the harness builds a fresh scratch DB per run, which is the supported contract.
