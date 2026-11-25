# 17 — DSH / Hermes Handoff

Ownership split for the Advisor AI slice, with each pending item as an exact, actionable request. Derived from `AGENTS.md` §8 (Delegation boundaries), §9; backend files and backend tests are Hermes-owned; DSH touches only `resources/js/**` and browser specs.

## DSH owns (F1–F6, per `15-implementation-roadmap-f1-f6.md`)

| Area | Files (proposed) |
|---|---|
| Typed API client | `resources/js/services/advisor/analysis.ts` (new) |
| Request lifecycle composable | `resources/js/composables/useAdvisorAnalysis.ts` (new) |
| AI presentation panel | `resources/js/components/career/AdvisorAnalysisPanel.vue` (new) |
| Rail integration | `resources/js/components/career/AdvisorRail.vue` (render panel below `NextDecision`) |
| Page wiring | `resources/js/pages/Career/Cockpit.vue` (pass run id/status/turn; nothing else) |
| Browser specs | `tests/browser/career-cockpit-advisor-ai.spec.ts` (new) |
| Type declarations | response/error types per `14-api-contract.md` |

**Hard boundaries DSH observes:** never widen the cockpit `advisor` prop (pinned by `AdvisorAnalysisRouteTest.php:76–84` and `CareerCockpitTest.php:154–166`); never add fetch logic to `NextDecision.vue` (`AdvisorAnalysis.php:16–19` design note); never render `preference` as probability of success (`AdvisorAnalysis.php:168`, ADR-0001 §3); never add auth/policy (none by design, `PRD.md` NFR-1).

## Hermes owns

Advisor decision logic, Clef integration, eligibility, retrieval, backend orchestration, provider timeout policy, response schema corrections, route/controller behavior, backend contract tests.

## Pending Hermes requests (exact)

### H-1 — Write and accept the ADR for the `advisor_ai` boundary

- **Evidence:** `config/advisor_ai.php:21–24` — "An architecture decision record for this boundary would have to be Accepted by the owner, and it is not written yet… an ADR is listed as owed in the hand-off."
- **Ask:** author `docs/adr/0038-advisor-ai-optional-ranking-layer.md` (or next number) covering: off-by-default switch, deterministic-floor guarantee, read-only service, cache-key-on-revision, provider degradation order, corpus-path-not-in-`uma.sources` ruling. Regenerate `docs/adr/README.md`.
- **Blocks:** nothing in DSH F1–F6; governance debt only.

### H-2 — Correct the response-contract metadata file

- **Evidence:** untracked `advisor-api-contract.json` carries two wrong claims: line 48 `explanation_source: 'deterministic' | 'ai'` (code returns `'logfare'`, `AdvisorExplainer.php:86`) and line 148 `authorization: "requires authenticated user owning the run"` (no auth layer exists, `PRD.md` NFR-1). It also lacks the `decision.status: 'abstained'` provider state description (line 104–117 describes abstained but lists `provider_states` keys that don't mention it under that name — minor).
- **Ask:** either delete the file (the verified contract is `docs/audits/2026-11-22-advisor-ai-audit/14-api-contract.md`) or fix the two lines and add a pointer to the audit contract doc.
- **Blocks:** nothing (DSH is instructed to follow `14-api-contract.md` regardless).

### H-3 — Add the 503 error-path test (F-02)

- **Evidence:** `CockpitController.php:182–193` returns 503 `{error:{code:'advisor_analysis_unavailable'}}` on `\Throwable`; `tests/Feature/AdvisorAnalysisRouteTest.php` has no test touching it.
- **Ask:** add a test that forces `analyze()` to throw (e.g. bind a mocked `AdvisorAnalysis` or seed state that throws inside the try) and asserts `assertStatus(503)` + `error.code === 'advisor_analysis_unavailable'` + the cockpit rail unaffected.
- **Blocks:** F6-7 can proceed with the frontend handling the documented shape; the test is a backend regression guard, parallel work.

### H-4 — (No action needed) deadline implementation confirmed complete

- Recorded so nobody reopens it: absolute-deadline propagation with per-attempt clamping is present and correct (`ClefDecisionProvider.php:64–77`, `AdvisorExplainer.php:134–139`, `AdvisorAnalysis.php:77–98,120–135`). The historical "12s vs 20s mismatch" is resolved in code.

## Cross-agent sequencing

1. Hermes H-3 (backend test) and DSH F1–F3 can proceed in parallel; both are read/write on disjoint trees.
2. DSH F4–F6 need no Hermes input.
3. If Hermes changes the response shape (new `CONTRACT_VERSION`), `14-api-contract.md` §3 and the frontend types change together; the `contract_version` field is the tripwire (`AdvisorAnalysis.php:31–32`).

## What DSH will NOT do

- No backend file edits, no route changes, no migrations, no `advisor_api-contract.json` edits (Hermes-owned, untracked, in-flight).
- No dependency installs (`axios` already present, `bootstrap.ts:1`).
- No authorization, rate limiting, or deploy work — none exists by design.

---

## Re-validation — 2026-11-23 (modernization pass)

**Repository HEAD at re-validation:** `3600741e`
**Document baseline:** 2026-11-22
**Status:** Reviewed; no source changes affecting this document's claims since baseline.
**Limitations:** No local server available for live re-measurement; all claims rest on source-static inspection.
