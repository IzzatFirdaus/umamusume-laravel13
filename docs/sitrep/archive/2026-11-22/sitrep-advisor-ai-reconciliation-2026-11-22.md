# SITREP — Advisor AI audit reconciliation and F1–F6 planning (2026-11-22)

**Agent:** DSH (frontend) · **Scope:** documentation-only audit reconciliation · **Tree state at work:** branch `master`, HEAD `ad0162f8`, with Hermes' Advisor backend slice in flight (5 modified + 17 untracked advisor files, preserved untouched).
**Deliverable:** `docs/audits/2026-11-22-advisor-ai-audit/` — original 01–11 preserved, reconciliation 12–18 added.

---

## 1. Executive conclusion

The corrected audit is implementation-ready. One confirmed gap exists — the `runs.advisor-analysis` endpoint has zero frontend consumers (grep-verified across `resources/js/`) — and it now has a code-derived contract, a chosen architecture (typed JSON client + composable, zero backend change), and a dependency-ordered F1–F6 roadmap a fresh DSH session can execute without rediscovery.

## 2. What was reconciled (R1–R8 dispositions)

| Item | Original audit claim | Disposition (evidence) |
|---|---|---|
| R1 | Missing loading/error in `NextDecision.vue` = production defect | **Reclassified** — future requirement of the AI integration (roadmap F3). Component is deterministic by design; `AdvisorAnalysis.php:16–19` names `NextDecision` untouched as a guarantee. No fetch logic goes there. |
| R2 | Sticky rail breaks `main` `@container` | **Withdrawn** — `AdvisorRail.vue:38–42` has no `position: sticky`; only `lg:max-h + lg:overflow-y-auto`, which containment doesn't affect. Repo's sticky constraint (`containment.spec.ts`, KI-150) doesn't apply. |
| R3 | WCAG 2.2 unverified | **Verification gap only** — deterministic rail is behavior-tested (named region, focusable scroll region, browser specs, axe in forms-spec scope). No confirmed violation. AI panel needs its own coverage (F5). |
| R4 | 12s total vs 20s Clef timeout mismatch | **Superseded** — absolute-deadline propagation with per-attempt clamping shipped: `ClefDecisionProvider.php:72–77`, `AdvisorExplainer.php:134–139`. Do not reopen. |
| R5 | No provenance rows written | **Approved existing behavior** — read-only is the design (`AdvisorAnalysis.php:21–23`); response citation provenance ≠ DB provenance rows. |
| R6 | Contradictory `advisor.analysis` test claims | **Corrected** — frozen-prop assertion exists (`AdvisorAnalysisRouteTest.php:76–84` `->missing('advisor.analysis')`; `CareerCockpitTest.php:154–166` pins `['decision','finale']`). Duplicate test requirement withdrawn. |
| R7 | No 503 error-path test | **Confirmed** (grep clean) — backend test gap, assigned Hermes as H-3. |
| R8 | "No SSRF assertion" security gap | **Withdrawn** — no caller input reaches any URL (`CockpitController.php:164–166`, `AdvisorSnapshot::forProvider()` narrow allowlist). Contract file's "user owning the run" auth claim corrected: no auth layer by design (`PRD.md` NFR-1). |

Additional corrections: `explanation_source` enum is `'deterministic' | 'logfare'`, not `'ai'` (`AdvisorExplainer.php:86`); "no design tokens" was wrong (rail uses `text-ink-strong`/`text-ink-muted`/`card`); "stale impossible by design" qualified — server cache is revision-keyed, but the in-flight race is a frontend obligation (F3, F6-8).

## 3. The confirmed gap

`GET /training-runs/{run}/advisor-analysis` (`routes/web.php:158`, `CockpitController.php:170–194`) returns the full AI analysis — recommended/deterministic actions, provider state (`validated`/`abstained`/`fallback`), explanation, evidence citations, alternatives, blocked actions, unknowns, revision — and nothing in `resources/js/` ever requests it. The deterministic rail renders from the frozen `advisor.decision` page prop and is complete. The AI layer exists only as backend capability.

## 4. Architecture decision

**Option A — typed JSON client + composable.** New files: `resources/js/services/advisor/analysis.ts`, `resources/js/composables/useAdvisorAnalysis.ts`, `resources/js/components/career/AdvisorAnalysisPanel.vue`; touched: `AdvisorRail.vue` (render panel below `NextDecision`), `Cockpit.vue` (pass run id/status/turn only). Rejected B (partial reload) and C (deferred props): both require backend changes that don't exist and couple an optional feature to the heavy cockpit payload; the endpoint was designed as a standalone JSON GET ("requestable without a full Inertia visit", controller docblock); `window.axios` already bootstrapped. Invariants preserved: cockpit `advisor` prop never widened; single primary action stays deterministic; `preference`/`confidence` never rendered as gameplay odds (v1: unrendered).

## 5. Roadmap F1–F6 (detail: `15-implementation-roadmap-f1-f6.md`)

1. **F1** typed client — response/error types per verified contract, 404/503 classified distinctly (404 = feature off, never retried; 503 = transient, retry allowed).
2. **F3** (before any UI) async-safety state machine — loading (`role="status"`), disable, 15s abort, inline `role="alert"` error, retry, cancellation on unmount/navigation, duplicate-request guard, late-response rejection, career-revision/turn compare. Acceptance: Turn 31 response can never overwrite Turn 32.
3. **F2** panel presentation — provider-state copy, absence sentence for null recommendation, degraded-citations line; deterministic `NextDecision` DOM byte-identical.
4. **F4** evidence disclosure — citations as text (no invented links to repo paths), alternatives/blocked/unknowns using existing rail vocabulary.
5. **F5** token-only styling, native `<details>`, keyboard path, 1280/768/390/320 matrix.
6. **F6** ten-behavior acceptance spec (`tests/browser/career-cockpit-advisor-ai.spec.ts`), including stale-response rejection and no-auto-execution.

## 6. Hermes dependencies (detail: `17-dsh-hermes-handoff.md`)

- **H-1** — author/accept the owed ADR for the `advisor_ai` boundary (`config/advisor_ai.php:21–24` states it is owed).
- **H-2** — fix or delete incorrect `advisor-api-contract.json` (`'ai'` enum, auth claim).
- **H-3** — add the 503 error-path feature test (F-02).
- **H-4** — none: deadline implementation confirmed complete; recorded so nobody reopens it.

None block DSH F1–F6; H-3 and F1–F3 can proceed in parallel on disjoint trees.

## 7. Verification

- **Executed (read-only):** git state inspection; source reads of all backend advisor services, controller, config, frontend components, and all four backend test files; browser-spec greps (containment, cockpit-shell, responsive, career-cockpit, forms); frontend-consumer greps (0 hits).
- **Not run (documentation-only task, no source/test modified → no gate owed):** PHP suite, browser suite, pint, phpstan, typecheck, lore, audits. Recorded as not run, not passed.

## 8. Repository state

Branch `master`, HEAD `ad0162f8` at reconciliation. Changed files, all documentation, nothing staged/committed:

- `docs/audits/2026-11-22-advisor-ai-audit/` — new: `12-reconciliation-log.md`, `14-api-contract.md`, `15-implementation-roadmap-f1-f6.md`, `16-findings-register.md`, `17-dsh-hermes-handoff.md`, `18-sitrep.md`; `00-coverage.md` gained a reconciliation section; original `01`–`11` preserved as historical evidence.
- `docs/audits/INDEX.md` — entry description extended.
- Peer working-tree changes (backend advisor slice, Cockpit pending diffs) untouched.

## 9. Starting point for the next DSH session

1. **First file:** `resources/js/services/advisor/analysis.ts` — types + `requestAdvisorAnalysis(runId)` per `14-api-contract.md` §3 (`explanation_source: 'logfare'`).
2. **First behavior:** the F3 state machine in `useAdvisorAnalysis.ts` before any UI.
3. **First acceptance test:** browser spec — 404 renders quiet off-state with no retry; 503 renders inline `role="alert"` + Retry; `NextDecision` DOM unchanged by the AI layer.

Full documents: [18-sitrep (audit)](../audits/2026-11-22-advisor-ai-audit/18-sitrep.md) · [14-api-contract](../audits/2026-11-22-advisor-ai-audit/14-api-contract.md) · [15-roadmap](../audits/2026-11-22-advisor-ai-audit/15-implementation-roadmap-f1-f6.md) · [12-reconciliation](../audits/2026-11-22-advisor-ai-audit/12-reconciliation-log.md) · [16-register](../audits/2026-11-22-advisor-ai-audit/16-findings-register.md) · [17-handoff](../audits/2026-11-22-advisor-ai-audit/17-dsh-hermes-handoff.md)
