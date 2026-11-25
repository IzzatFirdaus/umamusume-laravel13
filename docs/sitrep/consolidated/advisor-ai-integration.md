# Consolidated SITREP — Advisor AI Integration (Workstream Group F)

**Generated:** consolidation of 5 source SITREPs  
**Scope:** Advisor AI audit reconciliation, backend integration, contract metadata, 503 regression, full-stack verification, frontend disclosure fix  
**Source files:** 5 reports spanning 2026-11-22 through 2026-11-23  
**Disposition:** Standalone workstream report; not a master SITREP; original sources archived in `docs/sitrep/archive/`

---

## 1. Source inventory

| # | Source SITREP | Date | SHA-256 | Role in consolidation |
|---|---|---|---|---|
| F1 | `sitrep-advisor-ai-reconciliation-2026-11-22.md` | 2026-11-22 | `f2a959cfb286ce1a77c7bad02b53c36edee1a7ecb2c30351052dc6749948e868` | Documentation-only audit reconciliation (R1–R8, F1–F6 roadmap) |
| F2 | `sitrep-hermes-advisor-ai-integration-2026-11-23.md` | 2026-11-23 | `d02f8d7cca36fac81ec129a6080d3e6ea0ee38095a9b6d2ea1915dcc04bb0b73` | ADR-0038 (Proposed), contract metadata corrections, 503 regression test |
| F3 | `sitrep-hermes-backend-verification-2026-11-23.md` | 2026-11-23 | `49aea481c3de1e18c672a455174d00edd4e0fdb5e351c7b180f52d1372c65e16` | Backend verification, 404 body probing, career revision assessment |
| F4 | `sitrep-advisor-ai-frontend-2026-11-23.md` | 2026-11-23 | `c785f7a08c2133cbf58b501e40223ca0f5c69cd8d1655e78eec0b3546dec0435` | Frontend disclosure remediation (20px→44px), integration verification |
| F5 | `sitrep-advisor-fullstack-2026-11-23.md` | 2026-11-23 | `37508a78662238267adfac951ae11959b44dfab357359301350a2883feaabbc9` | Full-stack consolidation, same-turn revision closure |

---

## 2. Consolidated findings

### 2.1 Audit reconciliation (F1)

Original audit claims → dispositions:

| # | Original claim | Disposition | Evidence |
|---|---|---|---|
| R1 | Missing loading/error in `NextDecision.vue` = production defect | **Reclassified** — future requirement AI integration (roadmap F3). Component deterministic by design; `AdvisorAnalysis.php:16–19` names `NextDecision` untouched guarantee; no fetch logic goes there. | — |
| R2 | Sticky rail breaks `main` `@container` | **Withdrawn** — `AdvisorRail.vue:38–42` has no `position: sticky`; only `lg:max-h + lg:overflow-y-auto`, which containment doesn't affect. | — |
| R3 | WCAG 2.2 unverified | **Verification gap only** — deterministic rail behavior-tested (named region, focusable scroll region, browser specs, axe in forms-spec scope). No confirmed violation. | — |
| R4 | 12s total vs 20s Clef timeout mismatch | **Superseded** — absolute-deadline propagation per-attempt clamping shipped: `ClefDecisionProvider.php:72–77`, `AdvisorExplainer.php:134–139`. | — |
| R5 | No provenance rows written | **Approved existing behavior** — read-only design (`AdvisorAnalysis.php:21–23`); response citation provenance ≠ DB provenance rows. | — |
| R6 | Contradictory `advisor.analysis` test claims | **Corrected** — frozen-prop assertion exists (`AdvisorAnalysisRouteTest.php:76–84` `->missing('advisor.analysis')`; `CareerCockpitTest.php:154–166` pins `['decision','finale']`). Duplicate test requirement withdrawn. | — |
| R7 | No 503 error-path test | **Confirmed** (grep clean) — backend test gap, assigned Hermes H-3. | — |
| R8 | "No SSRF assertion" security gap | **Withdrawn** — no caller input reaches any URL (`CockpitController.php:164–166`, `AdvisorSnapshot::forProvider()` narrow allowlist). No auth layer by design (`PRD.md` NFR-1). | — |

Additional corrections: `explanation_source` enum is `'deterministic' \| 'logfare'`, not `'ai'` (`AdvisorExplainer.php:86`); "no design tokens" wrong (rail uses `text-ink-strong`/`text-ink-muted`/`card`); "stale impossible by design" qualified — server cache is revision-keyed, but in-flight race frontend obligation (F3, F6–8).

### 2.2 Backend integration (F2/F3)

**ADR-0038** — `docs/adr/0038-ai-advisor-optional-read-only-layer.md`, status **Proposed** (owner acceptance outstanding). Ten boundary points:
1. Optional, off by default (`advisor_ai.enabled` defaults false; off = no cloud call, no local model call, no retrieval)
2. Deterministic engine is decision-safety floor (`TrainerAdvisor` unmodified; every provider state lands back on engine's recommendation)
3. Clef restricted to engine's ranked candidates (called only when >1 action ranked; out-of-set choices and malformed probability maps discarded)
4. Read-only execution (no save/update/dispatch; failed analysis leaves career byte-identical — regression-tested)
5. Cache boundary is career revision (`advisor-ai:{version}:{career_id}:{revision}`; fingerprint over run id, scenario key, next turn, status, build target, latest readings; note edits do not change it)
6. Provider degradation is designed path (no key / 401 / 429 / 5xx / timeout / budget / invalid choice / abstention all fall back; 12s request budget)
7. Evidence from local corpus with provenance (path/heading/line/servers; retrieval failure degrades to empty list, never fabricated citation)
8. Canonical data vs supporting documents separated (no entry added to `config('uma.sources')`)
9. No runtime dependency on agent tooling
10. Cockpit contract preserved (`advisor` prop stays exactly `decision` + `finale`; AI layer lives only on its own JSON route; 404-when-off intentional)

**Contract metadata corrections ([advisor-api-contract.json](../../audits/2026-11-22-advisor-ai-audit/advisor-api-contract.json)):** 12 targeted fixes, JSON re-parsed valid after each batch. No response-shape change. Key corrections:
- `authorization`: "Session-based..." → None by design (PRD.md NFR-1); no ownership policy exists
- 200 `scenario`: "scenario key" → **Display label** (live payload: `"Unity Cup"`); key is not a response field
- 200 `decision.model`: "e.g. 'clef', 'unknown'" → Config default `'clef'`; `'unknown'` when provider omits model; `null` when deterministic
- 200 `explanation_source`: `'deterministic' \| 'ai'` → `'deterministic' \| 'logfare'`
- `contract_version` stays `'1'` — no response-shape change

**503 regression test:** New case in `AdvisorAnalysisRouteTest.php`; suite 4 passed / 38 assertions / exit 0.

**Backend contract (`GET /training-runs/{run}/advisor-analysis`, `runs.advisor-analysis`, `routes/web.php:158`):**
- **200:** full payload or valid deterministic fallback, always
- **404 (layer disabled):** `abort_unless((bool) config('advisor_ai.enabled'), 404)`. Body depends on Accept header:
  - JSON request (`Accept: application/json` / `X-Requested-With`): `{"error":{"code":"NOT_FOUND","message":"Resource not found."}}` via `bootstrap/app.php` render closure — **not** Laravel HTML page contract JSON claims
  - HTML request: Laravel default 404 page (`text/html`)
- **404 (nonexistent run):** identical status and identical JSON envelope (binding `ModelNotFoundException` hits same render branch). **Bodies indistinguishable by design; DSH branches on status only.** No feature-discovery endpoint added.
- **503:** exact body `{"error":{"code":"advisor_analysis_unavailable","message":"The analysis could not be produced. The recommendation on the cockpit is unaffected."}}`; `application/json`; no exception text/token leakage; career untouched (pinned by existing 503 regression test).
- **Read-only:** grep-level write-path scan (zero matches over `app/Services/Advisor/**` + controller); `never writes to the career during analysis` test passing; 503-test before/after row equality passing.

**Career revision assessment (F3):**

`AdvisorSnapshot::fingerprint()` inputs: run id, scenario key, next turn number, run status, build target, latest turn's `id/turn/energy/energy_state/energy_band/mood/speed/stamina/power/guts/wit/sp/fans`. SHA-256, truncated 16 hex.

| Mutation | Revision changes? | Test result |
|---|---|---|
| New turn logged | Yes | ✓ |
| Same-turn reading edit (energy 60→35, same turn) | **Yes** | ✓ — case a turn check alone misses |
| Build target change (same turn) | Yes | ✓ |
| Run status change | Yes | ✓ |
| Scenario change | Yes | ✓ |
| Notes edit | **No** (deliberate) | ✓ |
| Race entry recorded against calendar | **No** — fingerprint limitation | Skipped (no seeded slot); obligations enter via `CockpitController::objectiveSection()`, never the fingerprint |

**Three guarantees separated:**
1. Backend cache freshness — holds (key carries revision)
2. Backend response consistency — holds (payload carries analyzed snapshot's revision)
3. Frontend presentation freshness — **DSH's alone** (response in flight during same-turn edit or race recording still arrives for older revision; compare `career_revision` at arrival and discard)

### 2.3 Full-stack same-turn revision closure (F5)

**Implemented:**
1. `AdvisorSnapshot::revision()` — public one-line entry over existing private `fingerprint()`. No second algorithm; page and response cannot disagree (same function).
2. `advisor_revision` cockpit prop — sibling of `advisor`, computed unconditionally. Not gated on `advisor_ai.enabled` (revision describes career; switch describes provider).
3. `Cockpit.vue` — prop typed `string`, forwarded to rail.
4. `AdvisorRail.vue` — `careerRevision` prop forwarded to panel.
5. `AdvisorAnalysisPanel.vue` — `careerRevision` prop threaded into composable.
6. `useAdvisorAnalysis.ts` — third argument (`Ref<string|null>`), `revisionAtRequest` capture, rejection branch beside turn check, reactive `watch(revisionRef, invalidate)`.
7. Tests — 3 new backend cases in `AdvisorAnalysisRouteTest`, 1 new browser case, fixture-honesty repair.

**Same-turn staleness:** Closed within supported refresh model. Cockpit exposes career's revision as sibling prop; composable compares it; response for career state page no longer shows is dropped.

**Remaining limitation (not a defect):** Cross-tab freshness not claimed. Revision arrives as Inertia prop; career edited in another tab seen only after visit/reload. Stated in composable header, spec footer, contract JSON.

### 2.4 Frontend disclosure fix (F4)

**Original problem:** `Sources (n)` summary rendered at **279×20** @1280 and **322×20** @390 vs 44px interactive target convention.

**Root cause:** `<summary class="cursor-pointer text-sm font-semibold text-ink-strong">` had no `flex`/`min-h-11`; UA sized native list-item summary to one 20px text line.

**Fix:** One line in `resources/js/components/career/AdvisorAnalysisPanel.vue`:
```html
<summary class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-ink-strong">
```
Uses project's existing `summary.flex::before` marker restoration (`app.css:1204`).

**After measurement:**
| Width | Before | After |
|---|---|---|
| 1280 | 279×20 | 279×44 |
| 768 | 444×20 | 444×44 |
| 390 | 322×20 | 322×44 |
| 320 | 252×20 | 252×44 |

Keyboard: Tab from rail's primary door reaches summary at every width; computed outline solid ≥2px while focused; Enter opens, Space closes; citation text visible only when open. Semantics preserved: native `<details>/<summary>`, accessible name `Sources (1)`, one focus stop, no nested controls, citation text remains text, token classes unchanged.

**Tests added:** `tests/browser/career-cockpit-advisor-ai.spec.ts` — 44px + keyboard + Enter/Space + focus + expand/collapse + surrounding-control matrix at 1280/768/390/320; three-theme × four-width geometry/overflow matrix. Eight pre-existing brittle assertions corrected (without weakening behavior).

**Integration verification:** Transport `spa.ts` → `bootstrap.ts` → `window.axios`; sole Vite entry; user-initiated only. States: idle, loading, ready, disabled (404), unavailable (503/timeout/network), cancelled. Provider attribution: four distinct sentences; Clef-vs-engine difference stated explicitly when they differ. Evidence: text citations only, never links; `[Global]` scope not doubled. Cancellation: cancel returns to idle, permits new request. Stale protection: request token (monotonic) + turn + revision + abort-on-navigate + unmount. 15 Advisor browser cases green.

---

## 3. Cross-stream notes relevant to consolidation

- **ADR-0038 remains Proposed** — does not bind (AGENTS.md §2). Owner acceptance is outstanding gate. No code change depends on it.
- **404 bodies indistinguishable** between "layer disabled" and "run not found" — confirmed deliberately; DSH branches on status only. Do not add feature-discovery endpoint.
- **Cross-tab freshness** not claimed — architectural, not a gap.
- The `window.axios` transport fix (one import in `spa.ts`) came from Group E frontend line; enabled Advisor panel to reach `ready`.
- The Advisor disclosure 20px→44px fix was handed off from Group E (E5 §8.1) and closed by F4.
- No application source, frontend component, backend route, migration, config, dependency, or test was modified outside documented changes.
- Nothing was committed, staged, or pushed by this workstream.