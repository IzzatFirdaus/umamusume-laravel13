# 12 — Audit Reconciliation Log

**Date:** 2026-11-22
**Reconciled against:** working tree at `ad0162f8` (HEAD of `master`) with uncommitted Advisor AI slice work present: `CockpitController.php`, `Cockpit.vue`, `AdvisorAnalysis.php`, `ClefDecisionProvider.php`, `AdvisorExplainer.php`, `AdvisorSnapshot.php`, `CandidateActions.php`, `AdvisorAnalysisRouteTest.php` and 7 further untracked Advisor files are uncommitted in-place (Hermes backend slice in flight).

The original audit (`01`–`11`) was written against the same tree but drew several conclusions the code does not support. Each original finding is dispositioned below. Classifications: confirmed defect / confirmed missing functionality / future implementation requirement / verification gap / backend-dependent issue / approved existing behavior / optional enhancement / resolved or superseded finding.

---

## R1 — Component-level loading and error UI in `NextDecision.vue`

**Original claim (01, 04):** missing loading/error/cancellation/retry is a production-readiness defect in `NextDecision.vue`.

**Verified:**
- `NextDecision.vue` (435 lines) renders only the five-element FD-2 order from server-provided `decision` props. It initiates no request. Its docblock: "this component formats what it is handed and acts for nobody."
- `AdvisorAnalysis.php` docblock lines 16–19: "the deterministic rail was not modified to make room for this class (`NextDecision` is untouched)" — the absence of fetch logic in the deterministic component is the documented design, not an omission.
- Cockpit page-level loading/error already exists (`Cockpit.vue:909–910`, `role="status"` / `role="alert"`, via `useVisitState.ts`).

**Disposition:** the missing async lifecycle (loading, disable, timeout, error, retry, cancellation, duplicate-request prevention, late-response rejection, revision comparison) is a **future implementation requirement for the not-yet-built AI integration**, owned by a new composable + presentation component (see `15-implementation-roadmap-f1-f6.md` F3). It is **not** a defect in the deterministic `NextDecision.vue`, and the request logic must not be forced into that component.

**Severity:** none as a defect; F3 is required before the AI integration ships.

## R2 — Sticky rail and container layout

**Original claim (03):** "sticky positioning breaks the main `@container` layout."

**Verified:**
- `AdvisorRail.vue:38–42` contains **no `position: sticky` declaration**. Its only layout classes are `card rounded-lg p-4 lg:max-h-[calc(100svh-1rem)] lg:overflow-y-auto` — a max-height with internal scroll, which container-type does not affect.
- The sticky-vs-`@container` interaction is a real, proven constraint in this repo (`tests/browser/containment-mechanism.spec.ts`, `containment.spec.ts`; KI-150 "keep no page-level sticky") — but it applies to sticky elements, and the advisor rail deliberately does not use one. `tests/browser/containment.spec.ts:124–150` even scans class attributes for reintroduced sticky utilities.
- Viewport behavior of the advisor layout is already asserted: `tests/browser/cockpit-shell.spec.ts:94` (two columns at 1440px), `:137` (one column at 390px), and `tests/browser/responsive.spec.ts:11` (56 route-widths swept for horizontal overflow, sticky elements and focus occlusions).

**Disposition:** the original claim is **unsupported and corrected**. There is no sticky declaration on the rail, so there is nothing for `@container` to break. The rail's max-height/internal-scroll pattern is the design that sidesteps the containment constraint. No layout refactor is prescribed. Residual verification need: 768px and 320px advisor-specific assertions (covered as F5 work, not a defect).

## R3 — WCAG 2.2 compliance

**Original claim (05):** "WCAG 2.2 compliance unverified; tabindex alone insufficient."

**Verified:**
- `AdvisorRail.vue` carries `aria-labelledby="career-advisor-heading"` and a `tabindex="0"` focusable-scroll-region treatment that `DESIGN.md` §2.3 mandates and the browser suite keeps regression-tested.
- `tests/browser/career-cockpit.spec.ts` asserts the advisor region's semantics extensively (region role, headings, obligations table columns, disclosure buttons, absence states).
- `tests/browser/career-cockpit-forms.spec.ts` states its scope as "(layout, advisor, the doors, axe)" — an axe pass over the advisor is part of the forms spec's declared coverage.

**Disposition:** the deterministic advisor's accessibility is **behavior-tested today**; missing tests do not establish noncompliance, and no semantic or keyboard failure was observed. Reclassified as **verification gap only for the future AI content** — the new AI panel must carry its own keyboard, status-announcement and disclosure coverage (F5). No confirmed accessibility-rule violation exists to report.

## R4 — Deadline configuration (12s total vs 20s Clef timeout)

**Original claim (08):** "`total_timeout` 12s; decision timeout 20s" listed as a gap.

**Verified:** the mismatch was already resolved in code before this reconciliation:
- `AdvisorAnalysis.php:77–78` computes an absolute `$deadline = $startedAt + total_timeout` and passes it down.
- `ClefDecisionProvider.php:64–77`: `$timeout = min($configuredTimeout, max(0.0, $deadline - microtime(true)))` — the configured 20s is an **upper bound only**; each attempt is capped to the remaining wall-clock budget, and `:109` stops retrying once the deadline has passed.
- `AdvisorExplainer.php:134–139` applies the identical cap to the explanation call.
- `AdvisorAnalysis.php:95–98, 112, 120–135`: retrieval, Clef and explanation are all skipped or degraded when the budget is exhausted, shipping the deterministic template.

**Disposition:** **resolved/superseded finding.** The deadline propagation the later Hermes report described is present in the current tree. No configuration change is requested; the 20s value is a per-attempt ceiling that the 12s budget clamps in practice.

## R5 — Provenance writes

**Original claim (11):** "AdvisorAnalysis does not write provenance rows" listed under Gap.

**Verified:** `AdvisorAnalysis.php` docblock lines 21–23: "A recommendation is never written anywhere. This service reads models and caches a payload; it has no save, no update and no dispatch." The controller adds none (`CockpitController.php:168`). Provenance in this slice is **citation provenance in the response** (`evidence[]` with path/heading/line/servers, backed by `AdvisorProvenanceTest`, 946 lines) — not persistent database provenance rows, which belong to the data-fetch promote path and were never in scope for a read-only advisor.

**Disposition:** **approved existing behavior**, not a defect. No requirement obliges the advisor to write provenance rows; classifying the absence as a gap conflated the two kinds of provenance. Original finding superseded.

## R6 — Cockpit prop contract tests

**Original claim (09):** contradictory lines — the finding list said the test notes "missing 'advisor.analysis'" while a second "Gap" line said the test was missing that assertion.

**Verified:** `tests/Feature/AdvisorAnalysisRouteTest.php:76–84` explicitly asserts `->has('advisor')->has('advisor.decision')->has('advisor.finale')->missing('advisor.analysis')`. `CareerCockpitTest.php:154–166` independently pins `array_keys($advisor) === ['decision', 'finale']` and the frozen eight-key decision shape.

**Disposition:** the contradictory gap line is **corrected** — the cockpit-prop-is-frozen assertion exists and passes. The duplicate test requirement is withdrawn. Confirmed remaining test gap: no explicit 503-path test (see R7).

## R7 — HTTP 503 coverage

**Verified:** `AdvisorAnalysisRouteTest.php` contains 3 tests (404 default-off, 200 JSON contract, frozen cockpit prop). Grep for `503` in the file: no match. The controller's 503 branch (`CockpitController.php:182–193`, `error.code = 'advisor_analysis_unavailable'`) is untested.

**Disposition:** **confirmed missing functionality — backend test gap, assigned to Hermes** (controller/route behavior and backend contract tests are Hermes-owned). DSH takes no controller action. Recorded as Hermes dependency H-3 in `17-dsh-hermes-handoff.md`.

## R8 — Security claims

**Original claim (10):** "No SSRF/allowlist check specific to advisor" listed as a gap; error handling and read-only posture confirmed.

**Verified:**
- Read-only: confirmed by code and docblock (R5 above). No write path exists.
- Outbound HTTP: the advisor calls fixed endpoints from `config/advisor_ai.php` (logfare.ai for decision/explanation, `127.0.0.1:11434` for local Ollama embeddings). No request input reaches any URL — `AdvisorSnapshot::forProvider()` (`AdvisorSnapshot.php:224–241`) is a deliberately narrow allowlist of stored readings, excluding all Trainer-authored prose, and `CockpitController.php:164–166` states "Nothing from the query string reaches the analysis."
- Secrets: `LOGFARE_API_KEY` is env-only, never printed, never read by the browser (`config/advisor_ai.php:13–14, 123–126`).
- Error responses: the 503 body carries a fixed `code` + fixed `message` (`CockpitController.php:187–192`); the exception detail goes to `Log::warning`, not the response.
- Authorization: the untracked `advisor-api-contract.json` claims "requires authenticated user owning the run" — **wrong for this repository**. There is no authorization layer by design (`PRD.md` NFR-1, `ARCHITECTURE.md` §8; no policy/gate exists). Rate limiting is absent, which is consistent with a loopback-only tool (`PRD.md` §6.10), not a finding.

**Disposition:** the "no SSRF assertion" gap line is **withdrawn** — SSRF is not applicable where no caller-controlled input reaches a URL. Security posture confirmed sound. The contract file's authorization line is corrected in `14-api-contract.md`.

---

## Other corrections found during reconciliation

| # | Original statement | Correction |
|---|---|---|
| C-1 | 07: "No evidence design tokens used" in advisor components | **Wrong.** `AdvisorRail.vue` uses `text-ink-strong`, `text-ink-muted`, the shared `card` class; `NextDecision.vue` is token-driven throughout. Themes (Azure Daybreak / Midnight Circuit / EVA-01) are token-switched via `useTheme.ts` and inherited automatically. No finding. |
| C-2 | 06: "No viewport test evidence" | **Overstated.** 1440px and 390px are asserted (`cockpit-shell.spec.ts:94,137`) and the responsive sweep covers all routes. Missing only: advisor-specific 768/320 assertions → F5. |
| C-3 | `advisor-api-contract.json` line 48: `explanation_source` is `'deterministic' \| 'ai'` | **Wrong value set.** `AdvisorExplainer.php:71–86` returns `'deterministic'` or `'logfare'`. Frontend must match on `'logfare'`. See `14-api-contract.md`. |
| C-4 | Contract file: "authorization: session-based, user owning the run" | **Wrong for this repo** (no auth layer by design). See R8. |
| C-5 | Contract file: "stale_result_behavior: Impossible by design" | **Server-side truth only.** The cache key prevents serving a stale *server* answer, but the frontend can still receive a response for turn N after the page has moved to turn N+1 (Trainer logs a turn while the request is in flight). The response's `career_revision`/`turn` fields exist precisely so the frontend can reject that; this is a frontend obligation (F3), not something the backend makes impossible. |
| C-6 | 00-coverage "Overall assessment" | Rewritten: the only confirmed integration gap is the absent frontend consumer of `runs.advisor-analysis`. |

## Confirmed integration gap (unchanged, the task's core)

Grep of `resources/js/` for `advisor-analysis|advisorAnalysis|advisor_analysis`: **no matches**. No Vue or TypeScript code requests `runs.advisor-analysis`. The deterministic rail is fully wired; the AI endpoint has zero frontend consumers. Everything else in the original audit either stands as approved behavior or is dispositioned above.

---

## Re-validation — 2026-11-23 (modernization pass)

**Repository HEAD at re-validation:** `3600741e`
**Document baseline:** 2026-11-22
**Status:** Reviewed; no source changes affecting this document's claims since baseline.
**Limitations:** No local server available for live re-measurement; all claims rest on source-static inspection.
