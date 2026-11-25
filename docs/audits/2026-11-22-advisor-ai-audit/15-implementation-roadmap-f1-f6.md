# 15 — F1–F6 Implementation Roadmap (frontend)

**Owner of this plan:** DSH (frontend). **Execution session:** a future DSH implementation assignment — this document is written so that session needs no rediscovery.
**Preserved invariant:** the existing deterministic advisor (`advisor.decision` frozen eight-key payload, `advisor.finale`) renders exactly as today on every code path. The AI layer is an *addition* inside the rail, never a replacement, and never widens the page prop contract (`AdvisorAnalysisRouteTest.php:76–84` keeps `advisor.analysis` absent).

**Chosen architecture (§6 of the brief): Option A — typed JSON client/composable.**
Reasons, all verified against the tree: the endpoint is a dedicated JSON GET designed for "requestable without a full Inertia visit" (`CockpitController.php:156–162`); Option B (partial reload) would require a backend change to add the analysis to page props — Hermes-owned, and it couples an optional feature to a heavy page payload; Option C (deferred props) requires a new backend contract that does not exist. Option A is zero-backend-change, matches the existing `window.axios` bootstrap (`bootstrap.ts:1–11`) and the server-supplied-URL convention (`run.settings_url` pattern), and keeps request lifecycle state local to the rail.

**File plan (new files, no renames):**

```
resources/js/services/advisor/analysis.ts        — typed client + response/error types (F1)
resources/js/composables/useAdvisorAnalysis.ts   — request lifecycle state machine (F1, F3)
resources/js/components/career/AdvisorAnalysisPanel.vue — AI presentation inside the rail (F2, F4)
```

**Touched files:** `Cockpit.vue` (pass `run.id` + `run.status` + current turn into the rail), `AdvisorRail.vue` (render the panel below `NextDecision`), nothing else.

---

## F1 — Contract and API integration

| | |
|---|---|
| **Objective** | Typed frontend integration with the existing `runs.advisor-analysis` endpoint. |
| **Current implementation** | Endpoint live (`CockpitController.php:170`); zero frontend consumers (verified by grep). `window.axios` is wired in `bootstrap.ts`; `window.route()` is typed in `types/global.d.ts:2`. |
| **Confirmed gap** | No client, no types, no composable. |
| **Required changes** | 1. `services/advisor/analysis.ts`: export `AdvisorAnalysisResponse`, `AdvisorAnalysisError`, `AdvisorProviderState` types exactly as `14-api-contract.md` §3 specifies (`explanation_source` is `'deterministic' \| 'logfare'`, not `'ai'`). 2. `requestAdvisorAnalysis(runId: number): Promise<AdvisorAnalysisResponse>` using `route('runs.advisor-analysis', runId)`; throw a discriminated `AdvisorAnalysisHttpError` carrying `status` (404 vs 503 vs other) and the parsed `error.code`. 3. `composables/useAdvisorAnalysis.ts` implementing the F3 state machine. |
| **Dependencies** | None (backend complete). |
| **Responsible** | DSH. |
| **Acceptance** | An existing career can obtain a valid 200 payload without modifying the cockpit `advisor` prop contract. 404 and 503 classify into distinct states. |
| **Verification** | `npm run typecheck`; focused Vitest-free assert check if the repo gains one, else browser spec (F6). |
| **Risks** | `route()` availability on page code — use it inside the service where `global.d.ts` declares it, or accept a server-supplied URL prop mirroring `settings_url`. |
| **Evidence** | Type-check output; first successful 200 rendered in a browser spec. |

## F2 — Recommendation presentation

| | |
|---|---|
| **Objective** | Show AI analysis inside the established advisor experience without disturbing the deterministic recommendation. |
| **Current implementation** | `AdvisorRail.vue` renders heading + `NextDecision` + standing footnote. Cockpit gates the rail on `isActive` (`Cockpit.vue:1206–1214`). |
| **Confirmed gap** | No AI surface exists. |
| **Required changes** | New `AdvisorAnalysisPanel.vue`, rendered by `AdvisorRail` **below** `NextDecision` so the five-element deterministic answer keeps its position (browser specs pin its block order — `career-cockpit.spec.ts:344`). Panel sections: (1) source line — `decision.provider`/`status` mapped to copy: "Clef ranked this turn" (validated) / "Clef declined to choose; the engine's answer stands" (abstained) / "Engine's own ranking" (fallback); (2) the explanation sentence; (3) evidence disclosure (F4); (4) alternatives list; (5) `unknowns` line; (6) `generated_at` + revision tail. **`preference` and `confidence` are not rendered in v1** — they are diagnostic figures (`AdvisorAnalysis.php:168`) and FR-F-3/ADR-0001 forbid presenting preference as success probability. When `recommended_action.id !== deterministic_action.id`, the panel names both and states Clef chose differently, but the rail's single primary action remains the deterministic `NextDecision` one — the AI layer never executes or re-aims the primary (F6-9). `recommended_action === null` renders the explanation's absence sentence. `retrieval.degraded === true` adds a quiet "citations unavailable this refresh" line. |
| **Dependencies** | F1. |
| **Responsible** | DSH. |
| **Acceptance** | Trainer can identify the recommended action, its origin (provider state), and the explanation. Deterministic rail unchanged byte-for-byte in DOM order. |
| **Verification** | Browser spec asserting all four provider-state copy variants (F6-2/4/7). |
| **Risks** | Pushing `NextDecision` down; mitigate with the below-placement + order assertion. |

## F3 — Asynchronous interaction safety

| | |
|---|---|
| **Objective** | Every async hazard of an on-demand seconds-long request handled, in one place. |
| **Current implementation** | Nothing — this is the future requirement R1 was reclassified into. |
| **Confirmed gap** | Whole lifecycle absent. |
| **Required changes** | `useAdvisorAnalysis` state machine: `idle → loading → ready \| disabled(404) \| failed(503/other)`. Requirements, each with its mechanism: **Loading** — `role="status"` line in the panel (ADR-0007: this IS a user-initiated async action, so a custom loading state is permitted and required). **Disabled** — the fetch button and Retry disable while loading. **Timeout** — AbortController at 15s (above the server's 12s budget) → `failed`. **Error** — `role="alert"` inline in the panel, not the page banner (the page's `visitFailed` banner is for visits, not this fetch). **Retry** — button on `failed`; never shown for 404 (§2 of contract doc). **Cancellation** — `AbortController.abort()` on unmount, on re-request, and on Inertia navigation start. **Duplicate prevention** — in-flight guard: a second request call while `loading` is a no-op. **Late-response rejection** — every response checks the run's current turn/revision captured at request time; on turn change (Trainer logged a turn via another tab or after navigation) discard without rendering. **Career revision comparison** — compare `response.turn` against the cockpit's current `props.header.turn`/`next_turn_to_play` before commit; mismatch → discard silently (the rail is about to be superseded anyway). **Navigation/unmount** — `onUnmounted` aborts; the composable is per-rail-instance. Acceptance target: a response for Turn 31 cannot overwrite the visible recommendation for Turn 32. |
| **Dependencies** | F1. |
| **Responsible** | DSH. |
| **Acceptance** | F6-8 (stale rejected) and F6-3/5 pass; abort on unmount proven by test. |
| **Verification** | Browser spec: start request on turn N, log turn N (page reloads via Inertia), assert no stale AI block; unit-style assert file for the revision guard if extracted pure. |
| **Risks** | Inertia prop patching after a turn write re-mounts props, not necessarily the composable — read revision from `props` reactively, not a captured const (same trap `NextDecision.vue:40–42` documents). |

## F4 — Evidence and explainability

| | |
|---|---|
| **Objective** | Show where the answer comes from without inventing links. |
| **Current implementation** | Deterministic rail already renders `why` rows with `ProvenanceBadge` and a "Source: … read YYYY-MM-DD" pattern the browser specs pin (`career-cockpit.spec.ts:570–573`). |
| **Confirmed gap** | AI `evidence[]` has no renderer. |
| **Required changes** | `<details>` disclosure "Sources" inside the panel listing `evidence[]`: `citation` as the visible text, `path § heading` + line, `servers` chips, using existing `ProvenanceBadge` idiom. **No clickable links to doc paths** — a `path` is a repo file, not a served route; inventing a URL would violate the no-fabricated-destination rule. Citation text only, exactly like the deterministic rail's source line. `obligation` renders beside the deterministic obligations reading; `blocked[]` renders as a quiet list ("engine withheld: {label} — {blocker}"); `alternatives[]` as the cost/contrast list; `unknowns[]` as "Missing: Energy." matching the rail's existing refusal vocabulary (`career-cockpit.spec.ts:197`). `conflicts` is always `[]` — render nothing (no empty section). |
| **Dependencies** | F2. |
| **Responsible** | DSH. |
| **Acceptance** | Trainer inspects supporting evidence; no invented facts, no unsupported links. |
| **Verification** | Browser spec: disclosure opens, citation strings match fixture payload, zero anchors with `href` inside the panel. |

## F5 — Design, responsiveness, and accessibility

| | |
|---|---|
| **Objective** | The AI panel passes the same bar the deterministic rail already meets. |
| **Current implementation** | Rail: named region (`aria-labelledby`), focusable scroll container, token classes only (`text-ink-strong`, `text-ink-muted`, `card`), asserted at 1440/390px (`cockpit-shell.spec.ts:94,137`) and swept at 56 widths (`responsive.spec.ts`). Themes via `useTheme.ts` tokens — the panel inherits all three themes for free if it uses tokens only. |
| **Confirmed gap** | No advisor-specific assertions at 768/320px; new panel has no coverage yet. |
| **Required changes** | Token-only classes (zero arbitrary values — `tools/ui-framework-check.mjs` baseline); keyboard path: the panel's disclosure and buttons are natively focusable elements; status announcements: loading `role="status"`, errors `role="alert"`; disclosure semantics via native `<details>/<summary>`; long explanations and long source titles wrap (`break-words`), never truncate mid-fact; responsive overflow: panel lives inside the rail's existing `lg:overflow-y-auto` container — no new scroll container. Viewport matrix to verify: 1280, 768, 390, 320. |
| **Dependencies** | F2, F4. |
| **Responsible** | DSH. |
| **Acceptance** | Matrix passes with documented evidence; axe clean within the forms spec's existing advisor scope. |
| **Verification** | Extend `career-cockpit-forms.spec.ts`'s axe scope or add a focused spec; width-matrix assertions in the new F6 spec. |

## F6 — Full-stack acceptance

| | |
|---|---|
| **Objective** | The ten specified end-to-end behaviors, tested against the real backend contract. |
| **Required tests** (new browser spec `tests/browser/career-cockpit-advisor-ai.spec.ts`, plus one Feature assertion already covered): 1. Existing career loads (existing `career-cockpit.spec.ts` covers; reference, don't duplicate). 2. Deterministic advisor remains usable with AI on and off. 3. AI analysis requested on demand (button click — no auto-fetch on mount: the Trainer opts in, matching "only the Trainer turns it on"). 4. Valid 200 renders panel with provider-state copy. 5. Evidence displayed accurately (citation strings from `Http::fake`-seeded... note: browser tests hit the real backend path with `advisor_ai.enabled` env-toggled via a scratch-DB seeded run; `AdvisorAiPipelineTest` already proves the backend pipeline under `Http::fake`). 6. Disabled AI (404): quiet off-line, no retry. 7. Provider failure (503, faked at the HTTP layer where the backend allows): deterministic rail still on screen, inline error + Retry. 8. Stale response rejected (turn-change race from F3). 9. No AI recommendation auto-executes any gameplay action — the panel renders text only, zero forms/posts (assert no submit targets inside `data-region="advisor"`). 10. Existing cockpit contracts intact: re-run `AdvisorAnalysisRouteTest.php` (backend, Hermes) and `CareerCockpitTest.php:154–166` unchanged; frontend: the block-order assertion at `career-cockpit.spec.ts:344` still green. |
| **Dependencies** | F1–F5. |
| **Responsible** | DSH (frontend specs); Hermes keeps the route/contract tests. |
| **Acceptance** | All ten behaviors verified; evidence quoted in hand-off. |
| **Verification** | `npm run test:browser` (session-private per AGENTS.md §9), `npm run typecheck`, `npm run build` before the run if the manifest is stale. |

---

## Execution order

F1 → F3 (state machine before UI so hazards are never bolted on) → F2 → F4 → F5 → F6. F1 and F3 can land as one commit series; F2/F4/F5 as the next; F6 last.

## Hermes dependencies blocking DSH

None for F1–F6 as scoped. Two Hermes-owned items run parallel (not blockers): H-3 (503 path test, `17-dsh-hermes-handoff.md`) and the owed ADR for the `advisor_ai` boundary (`config/advisor_ai.php:21–24` states it is listed as owed in the hand-off).

---

## Re-validation — 2026-11-23 (modernization pass)

**Repository HEAD at re-validation:** `3600741e`
**Document baseline:** 2026-11-22
**Status:** Reviewed; no source changes affecting this document's claims since baseline.
**Limitations:** No local server available for live re-measurement; all claims rest on source-static inspection.
