# 18 — SITREP: Advisor AI audit reconciliation (2026-11-22)

## 1. Executive conclusion

**The corrected audit is implementation-ready.** One real gap exists — no frontend consumer of `runs.advisor-analysis` — and it now has a verified contract (`14-api-contract.md`), a chosen architecture (typed JSON client + composable, Option A), and a concrete F1–F6 roadmap (`15-implementation-roadmap-f1-f6.md`) that a fresh DSH session can execute without rediscovery. Five original audit findings were withdrawn or corrected as unsupported; two genuine secondary gaps (503 backend test, owed ADR) are assigned to Hermes and do not block frontend work.

## 2. Reconciled findings

**Confirmed (unchanged):**
- F-01: endpoint has zero frontend consumers (grep-verified) — the task's core gap.
- F-02: no 503 error-path test (`AdvisorAnalysisRouteTest.php` greps clean for `503`) — Hermes.
- Read-only service, safe-by-default config, deterministic fallback chain — all verified in code.

**Corrected (original claim wrong):**
- 01/04: "missing loading/error in `NextDecision.vue` = production defect" → future requirement for the AI integration (F3); `NextDecision` is deterministic-by-design and must stay untouched (`AdvisorAnalysis.php:16–19`).
- 03: "sticky breaks `@container`" → withdrawn; the rail has no `position: sticky` at all (`AdvisorRail.vue:38–42`), and the repo's containment constraint (`containment.spec.ts`, KI-150) doesn't apply to `max-h + overflow-y-auto`.
- 07: "no design tokens" → wrong; rail uses `text-ink-strong`/`text-ink-muted`/`card` throughout.
- Contract JSON: `explanation_source` enum is `'logfare'` not `'ai'`; "user owning the run" authorization claim withdrawn (no auth layer by design); "stale impossible by design" qualified (server cache yes, in-flight race no).
- 09: contradictory "advisor.analysis" statements → the frozen-prop assertion exists (`AdvisorAnalysisRouteTest.php:76–84`); duplicate test requirement withdrawn.

**Superseded:** R4 deadline mismatch (clamping shipped: `ClefDecisionProvider.php:72–77`); R5 provenance-writes gap (read-only is the design).

**Still unverified / not executed:** no live browser observation was made in this reconciliation (documentation-only mandate); viewport matrix 768/320 for the advisor remains unasserted until F5/F6; the 503 test is Hermes' to run.

## 3. Actual frontend gap

`GET /training-runs/{run}/advisor-analysis` (`routes/web.php:158`, `CockpitController.php:170`) returns the full AI analysis — recommended/deterministic actions, provider state, explanation, evidence, alternatives, unknowns — and nothing in `resources/js/` ever calls it. The deterministic rail renders from the frozen `advisor.decision` page prop and is complete. The AI layer therefore exists only as a backend capability.

## 4. Architecture decision

**Option A — typed JSON client + composable** (`services/advisor/analysis.ts`, `composables/useAdvisorAnalysis.ts`, `components/career/AdvisorAnalysisPanel.vue`). The endpoint was designed as a standalone JSON GET ("requestable without a full Inertia visit", `CockpitController.php:156–162`); Options B/C (partial reload / deferred props) both require backend changes that don't exist and would couple an optional feature to the heavy cockpit payload; `window.axios` is already bootstrapped (`bootstrap.ts:1–11`). Least disruptive, zero backend change, preserves the deterministic experience.

## 5. Implementation roadmap

F1 typed client → F3 async-safety state machine (loading/disable/timeout/error/retry/cancel/duplicate-guard/late-response rejection/revision compare/unmount abort — Turn 31 can never overwrite Turn 32) → F2 panel presentation (provider-state copy, single primary preserved) → F4 evidence disclosure (citations as text, no invented links) → F5 token-only styling + 1280/768/390/320 + ARIA announcements → F6 ten-behavior full-stack acceptance spec. Full detail: `15-implementation-roadmap-f1-f6.md`.

## 6. Hermes dependencies

H-1 owed ADR for the advisor_ai boundary (`config/advisor_ai.php:21–24` says so itself); H-2 fix/delete the incorrect `advisor-api-contract.json` metadata; H-3 the 503 path test. H-4 records the deadline work as confirmed complete — do not reopen. None block DSH F1–F6. Full list: `17-dsh-hermes-handoff.md`.

## 7. Verification

**Executed (read-only inspection):** git state (`HEAD ad0162f8`, 5 modified + 17 untracked advisor files in flight — peers' work preserved untouched); source reads of `AdvisorAnalysis.php`, `ClefDecisionProvider.php`, `AdvisorExplainer.php`, `AdvisorSnapshot.php`, `CockpitController.php` (advisorAnalysis + advisorSection), `NextDecision.vue`, `AdvisorRail.vue`, `Cockpit.vue` (props/advisor region/loading), `config/advisor_ai.php`, all four advisor test files, browser specs (containment/cockpit-shell/responsive/career-cockpit), greps for frontend consumers, `useVisitState.ts`, `routes/web.php`.
**Not run:** PHP suite, browser suite, pint/phpstan/typecheck/lore — no source or tests were modified by this task (documentation-only mandate), so no gate is owed; recorded as not run rather than passed.

## 8. Repository state

- Branch `master`, HEAD `ad0162f8` (at reconciliation start; shared tree, other sessions active).
- **Files changed by this task only:** `docs/audits/2026-11-22-advisor-ai-audit/` — new `12-reconciliation-log.md`, `14-api-contract.md`, `15-implementation-roadmap-f1-f6.md`, `16-findings-register.md`, `17-dsh-hermes-handoff.md`, `18-sitrep.md` (this file); `00-coverage.md` updated with a reconciliation pointer; `docs/audits/INDEX.md` — entry description extended. Nothing staged, committed, pushed, reset, or merged; peer working-tree changes untouched.

## 9. Implementation starting point (next DSH session)

1. **First file:** `resources/js/services/advisor/analysis.ts` — types + `requestAdvisorAnalysis(runId)` per `14-api-contract.md` §3 (mind `explanation_source: 'logfare'`).
2. **First behavior:** the F3 state machine in `composables/useAdvisorAnalysis.ts` (idle→loading→ready|disabled|failed, AbortController, revision guard) before any UI.
3. **First acceptance test:** browser spec asserting 404 renders the quiet off-line with no retry affordance, and a 503 renders the inline `role="alert"` with Retry while `NextDecision`'s DOM is byte-identical to its AI-off rendering.

---

## Re-validation — 2026-11-23 (modernization pass)

**Repository HEAD at re-validation:** `3600741e`
**Document baseline:** 2026-11-22
**Status:** Reviewed; no source changes affecting this document's claims since baseline.
**Limitations:** No local server available for live re-measurement; all claims rest on source-static inspection.
