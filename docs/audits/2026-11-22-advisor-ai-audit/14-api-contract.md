# 14 — Full-Stack API Contract (frontend consumption spec)

**Route:** `GET /training-runs/{run}/advisor-analysis` — named `runs.advisor-analysis` (`routes/web.php:158–159`).
**Controller:** `CockpitController::advisorAnalysis()` (`app/Http/Controllers/Career/CockpitController.php:170–194`).
**Producer:** `App\Services\Advisor\AdvisorAnalysis::analyze()` (`app/Services/Advisor/AdvisorAnalysis.php:147–196`).
**Contract version:** `AdvisorAnalysis::CONTRACT_VERSION = '1'` — the response's `contract_version` field; bumped when the shape changes so old cached payloads are never served under a new shape.

This file documents the schema derived from the service and provider code. See the adjacent [JSON contract](advisor-api-contract.json) for the reconciled metadata.

> **Reconciled 2026-11-23 (Hermes sitrep `docs/sitrep/sitrep-hermes-advisor-ai-integration-2026-11-23.md`).** The
> metadata JSON has since been corrected field-by-field against the same producer code, so the two now agree.
> Corrections applied to this file in place: `scenario` carries the **display label** (e.g. `Unity Cup`), not the
> scenario key — the earlier "scenario key" comment and the `scenario_label_unused_field_note` line described a
> phantom field the payload never shipped; neither exists in the TypeScript client and neither must be added.
> `explanation_source` is `'deterministic' | 'logfare'`; decision provenance and explanation provenance are
> independent; `decision.model` is nullable with `'unknown'` possible; the deterministic 200 has been observed
> with `preference: []` (a PHP list where the docs typed an object) — consumption must tolerate it.

---

## 1. URL and request

| Aspect | Value |
|---|---|
| Method | GET |
| Path parameter | `{run}` — route-model binding to `TrainingRun` |
| Query string | **Ignored.** "Nothing from the query string reaches the analysis" (`CockpitController.php:164–166`). The state comes from the run's own rows. |
| Body | None |
| CSRF | Not applicable (GET read) |
| Auth layer | **None by design** (`PRD.md` NFR-1; `ARCHITECTURE.md` §8). Session cookie via the `web` middleware group; loopback-only tool. The contract JSON file's "user owning the run" authorization claim is wrong for this repository. |
| URL construction | `window.route('runs.advisor-analysis', run.id)` is available via `resources/js/types/global.d.ts:2`; page code already receives server-supplied URL props (`run.settings_url` etc.). Either is acceptable; do not hand-build path strings. |

## 2. Status codes

| Status | When | Body |
|---|---|---|
| 200 | Analysis produced (any provider state, including full fallback) | Payload below |
| 404 | `config('advisor_ai.enabled')` is false (`abort_unless`, controller line 172) | Laravel default 404 page (Inertia `httpException`) |
| 503 | Any `\Throwable` inside `analyze()` | `{ error: { code: 'advisor_analysis_unavailable', message: 'The analysis could not be produced. The recommendation on the cockpit is unaffected.' } }` |

**Frontend rules:**
- 404 = feature disabled. Show a quiet "AI analysis is switched off" line inside the advisor card. **Never retry**, never surface as an error toast — the switch is the Trainer's own choice (`config/advisor_ai.php:30–37`).
- 503 = temporary analysis failure. The deterministic rail is still on screen (it is untouched by this endpoint). Show a dismissible inline error with a **Retry** affordance.
- 404 and 503 are **not interchangeable**; branch on the status code, not on response shape.

## 3. 200 payload — exact fields

All names verified against `AdvisorAnalysis.php:147–196`.

```ts
interface AdvisorAnalysisResponse {
    contract_version: string;              // '1'
    career_id: number;                     // run id
    career_revision: string;               // 16-char sha256 prefix over turn+readings+target (AdvisorSnapshot::fingerprint)
    turn: number;                          // run.nextTurnNumber() — the turn the advice is for
    scenario: string;                      // scenario DISPLAY LABEL, e.g. 'Unity Cup' (corrected 2026-11-23; the key is not a response field)

    recommended_action: {
        id: string;                        // candidate id, e.g. 'speed', 'rest'
        label: string;                     // human label, e.g. 'Speed', 'Rest'
        kind: 'training' | 'recovery';     // 'recovery' iff action is TrainerAdvisor::REST (CandidateActions.php:146)
        eligibility: 'eligible';           // always this single value in the top-level recommendation
    } | null;                              // null when the engine declines to rank (e.g. no build target) — absence is stated, not guessed

    deterministic_action: {
        id: string;
        label: string;
    } | null;                              // the engine's own answer, the floor

    decision: {
        provider: 'deterministic' | 'clef';
        model: string | null;              // provider model name; null when deterministic
        status: 'fallback' | 'validated' | 'abstained';
        fallback_used: boolean;
        preference: Record<string, number>;// provider probabilities — DIAGNOSTIC ONLY, never rendered as odds/success chance
        confidence: number | null;         // null when deterministic
    };

    explanation: string;                   // one-sentence rationale
    explanation_source: 'deterministic' | 'logfare';  // NOTE: 'logfare', NOT 'ai' — AdvisorExplainer.php:71–86

    evidence: {
        id: string;                        // doc identifier
        path: string;                      // e.g. 'docs/UMAMUSUME_REFERENCE.md'
        heading: string;
        line: number;                      // line number in source file
        servers: ('Global' | 'JP')[];      // subset
        citation: string;                  // preformatted 'path § heading [server]'
    }[];                                   // empty when retrieval disabled/unbuilt/degraded

    retrieval: {
        degraded: boolean;                 // true when the index was never built or retrieval threw
        lexical_count: number;
        semantic_count: number;
    };

    unknowns: string[];                    // human-readable missing facts: 'Energy', 'Mood', stat names, 'every turn reading'
    conflicts: unknown[];                  // ALWAYS [] — reserved; render nothing for it
    obligation: string | null;             // mandatory-race deadline text
    alternatives: {
        id: string;
        label: string;
        reason: string;
        engine_choice: boolean;            // true when this alternative is also the deterministic floor
    }[];
    blocked: {
        id: string;
        label: string;
        blocker: string;
    }[];
    generated_at: string;                  // ISO 8601 UTC, e.g. '2026-11-22T12:34:56+00:00' (gmdate('c'))
}
```

### Provider states (how to read `decision`)

| State | `decision.provider` | `decision.status` | `decision.fallback_used` | `explanation_source` | Meaning for UI |
|---|---|---|---|---|---|
| Deterministic / fallback | `deterministic` | `fallback` | `true` | `deterministic` | Engine's own ranking; no AI involved (switch off, no key, provider down, timeout, invalid choice) |
| Clef validated | `clef` | `validated` | `false` | `logfare` | Clef chose within the candidate set and its revision matched |
| Clef abstained | `clef` | `abstained` | `true` | `deterministic` | Clef returned insufficient_evidence; the engine's choice stands and the UI must still show it |

**Distinguishing the seven required states:**

1. **AI disabled** → HTTP 404 (not a payload).
2. **Temporary analysis failure** → HTTP 503 (not a payload).
3. **Deterministic fallback** → 200 + `decision.provider === 'deterministic'` (covers switch-off-at-runtime, provider failures, timeout — the frontend cannot and need not subdivide these; `decision.fallback_used === true`).
4. **Successful Clef ranking** → 200 + `provider === 'clef'` + `status === 'validated'`.
5. **Insufficient data (Clef abstained)** → 200 + `provider === 'clef'` + `status === 'abstained'` — recommendation still shown, sourced deterministic.
6. **Null recommendation** → 200 + `recommended_action === null` — the explanation carries the absence sentence; render it, never a blank.
7. **Stale career revision** → frontend-side check: response `career_revision` (or `turn`) must equal the run's current revision/turn at render time. The backend makes a stale *server cache hit* impossible (`AdvisorAnalysis.php:62–64`, revision in cache key) but cannot prevent an in-flight response arriving after the Trainer logs a turn — that rejection is F3's obligation.

### Contract-file corrections vs `advisor-api-contract.json`

- `explanation_source` enum: file says `'deterministic' | 'ai'`; code returns `'deterministic' | 'logfare'` (`AdvisorExplainer.php:86`).
- `scenario`: file describes it as "scenario key"; correct — and the payload has **no** `scenario_label` key despite the label existing on `AdvisorSnapshot`. The label the UI already shows comes from the cockpit's own props.
- Authorization claim: withdrawn (no auth layer by design).
- "stale_result_behavior: Impossible by design": qualified per state 7 above.

## 4. Missing-data representation

The response never invents a zero. Missing facts surface as `null` fields plus `unknowns[]` entries (`AdvisorSnapshot.php`):

- Energy not recorded / band / unknown state → `recommended_action` may still exist; `unknowns` includes `'Energy'`.
- No turns logged at all → all stats `null`, `unknowns` includes `'every turn reading'`.
- No build target → engine declines to rank → `recommended_action: null`, absence sentence in `explanation` (pinned by `AdvisorAnalysisRouteTest.php:58–60`).

## 5. Error body shape

```ts
interface AdvisorAnalysisError {
    error: {
        code: 'advisor_analysis_unavailable';
        message: string;
    };
}
```

`message` is a fixed string; the exception detail goes to `Log::warning` only (`CockpitController.php:183`). Never parse the message; branch on the HTTP status and `error.code`.

## 6. Caching and performance characteristics

- Server caches the full payload under `advisor-ai:{contract_version}:{career_id}:{revision}`, TTL 900s (`config/advisor_ai.php:184–186`).
- Interactive budget: `total_timeout` 12s absolute deadline; Clef and explanation attempts are clamped to the remaining time (`ClefDecisionProvider.php:72–77`, `AdvisorExplainer.php:134–139`) — the earlier "12s vs 20s mismatch" is resolved in code.
- First-call latency is provider-bound (seconds, not ms); subsequent same-revision calls are cache hits. The frontend must therefore support a loading state measured in seconds (F3), not assume instant responses.

---

## Re-validation — 2026-11-23 (modernization pass)

**Repository HEAD at re-validation:** `3600741e`
**Document baseline:** 2026-11-22
**Status:** Reviewed; no source changes affecting this document's claims since baseline.
**Limitations:** No local server available for live re-measurement; all claims rest on source-static inspection.
