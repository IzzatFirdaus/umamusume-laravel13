/*
 * The AI Advisor's JSON client (Advisor AI frontend integration, F1).
 *
 * One endpoint, one response shape, one error shape: `GET /training-runs/{run}/advisor-analysis`
 * (`runs.advisor-analysis`, `app/Http/Controllers/Career/CockpitController.php:170`). The field
 * shapes below are the verified contract: read off the service that builds the payload
 * (`App\Services\Advisor\AdvisorAnalysis`) and confirmed by Hermes' 2026-11-23 sitrep, which also
 * corrected the metadata JSON (`docs/audits/2026-11-22-advisor-ai-audit/advisor-api-contract.json`;
 * `explanation_source` is
 * `'logfare'`, `scenario` carries the display label, decision and explanation provenance are
 * independent). `14-api-contract.md` predates those corrections: where the two disagree, this
 * file and the corrected JSON win, and the doc's `scenario_key` note describes a field the
 * payload never carried (do not model it).
 *
 * **The URL is derived from the cockpit's own prop, not from Ziggy.** This repository ships no
 * route helper to page code (`global.d.ts` declares `window.route`, but nothing defines it), and the
 * page convention is a server-supplied URL prop (`run.settings_url` and its siblings). The analysis
 * path is the cockpit's path with its trailing `/cockpit` swapped for `/advisor-analysis`; the
 * controller already hands the cockpit URL over as `run_url`, so no new prop crosses the wire.
 *
 * **`window.axios` is the transport** (`resources/js/bootstrap.ts:1–11`); this module owns nothing
 * else. No store, no cache: the server caches on the career's revision fingerprint, and the
 * composable owns the in-flight guard.
 */

const TIMEOUT_MS = 15_000;

/** The provider the answer came from. Clef ranks; `deterministic` is the engine's own answer. */
export type AdvisorProvider = 'deterministic' | 'clef';

/** How the provider call resolved. `abstained` means Clef declined to choose; the engine's answer stands. */
export type AdvisorDecisionStatus = 'fallback' | 'validated' | 'abstained';

/** Where the one-sentence explanation came from. `logfare`, not `ai` (14-api-contract.md §3). */
export type AdvisorExplanationSource = 'deterministic' | 'logfare';

export interface AdvisorRecommendedAction {
    id: string;
    label: string;
    kind: 'training' | 'recovery';
    /** Always `eligible` in the top-level recommendation. */
    eligibility: 'eligible';
}

export interface AdvisorDeterministicAction {
    id: string;
    label: string;
}

/**
 * `preference` and `confidence` are preference diagnostics, not probabilities of anything in the
 * game (14-api-contract.md §3; ADR-0001 §3, ADR-0020 §2). The types carry them because the
 * response has them; the panel must never render them as success chances.
 */
export interface AdvisorDecision {
    provider: AdvisorProvider;
    /**
     * The decision provider's model name. Null when deterministic; the config default is
     * `clef`, and a provider response that omits its model yields the string `'unknown'`
     * (Hermes sitrep 2026-11-23 §3) — so empty-string is not a value this field carries.
     */
    model: string | null;
    status: AdvisorDecisionStatus;
    fallback_used: boolean;
    /**
     * Diagnostic preference figures, keyed by candidate id. The deterministic 200 has been
     * observed with an **empty array** in this slot (Hermes' captured payload, sitrep
     * 2026-11-23 §3) — the JSON emitter writes a PHP list where an object was documented, so
     * consumption must tolerate `[]` as well as `{}`: read through `Object.keys/values`
     * (both see an empty list as empty), never `Object.entries`-on-assumed-shape.
     */
    preference: Record<string, number>;
    confidence: number | null;
}

export interface AdvisorEvidence {
    id: string;
    /** A repository path, e.g. `docs/UMAMUSUME_REFERENCE.md`. It is not a served URL: never link it. */
    path: string;
    heading: string;
    line: number;
    servers: Array<'Global' | 'JP'>;
    /** Preformatted `path § heading [server]`, the rail's own source-line vocabulary. */
    citation: string;
}

export interface AdvisorAlternative {
    id: string;
    label: string;
    reason: string;
    engine_choice: boolean;
}

export interface AdvisorBlockedAction {
    id: string;
    label: string;
    blocker: string;
}

export interface AdvisorAnalysis {
    contract_version: string;
    career_id: number;
    /**
     * The server-generated fingerprint over the turn, the readings and the target. The frontend
     * compares it, never reconstructs it (14-api-contract.md §1, contract corrections).
     */
    career_revision: string;
    /** The turn the advice is for. Stale-response rejection compares this to the turn at request time. */
    turn: number;
    /**
     * The scenario's **display label** (e.g. `Unity Cup`), not the scenario key — Hermes verified
     * the live payload (sitrep-hermes-advisor-ai-integration-2026-11-23.md §3). Render it; never
     * match it against a key.
     */
    scenario: string;
    recommended_action: AdvisorRecommendedAction | null;
    deterministic_action: AdvisorDeterministicAction | null;
    decision: AdvisorDecision;
    explanation: string;
    explanation_source: AdvisorExplanationSource;
    evidence: AdvisorEvidence[];
    retrieval: {
        degraded: boolean;
        lexical_count: number;
        semantic_count: number;
    };
    unknowns: string[];
    /** Always `[]` — reserved. Render nothing for it. */
    conflicts: unknown[];
    obligation: string | null;
    alternatives: AdvisorAlternative[];
    blocked: AdvisorBlockedAction[];
    /** ISO 8601 UTC. */
    generated_at: string;
}

export interface AdvisorAnalysisErrorBody {
    error: {
        /** Fixed value; the controller prints no detail of the thrown exception. */
        code: 'advisor_analysis_unavailable';
        message: string;
    };
}

/**
 * The two failure classes the panel can act on. `disabled` is the 404: on a cockpit the career
 * is known-good by construction (the page is that run's own screen), so a 404 here reads as the
 * feature being switched off, and nothing is ever retried for it. `unavailable` is the 503, a
 * timeout and a dead network: recoverable, and the only class the panel offers a retry for.
 */
export type AdvisorFailureKind = 'disabled' | 'unavailable';

export class AdvisorAnalysisError extends Error {
    /**
     * The server's own line, for the 503 body. The panel prints it verbatim: the controller composes
     * it as the statement the Trainer reads, and a client-side restatement could drift from it.
     */
    readonly detail: string | null;

    constructor(
        readonly kind: AdvisorFailureKind,
        message: string,
        detail: string | null = null,
    ) {
        super(message);
        this.name = 'AdvisorAnalysisError';
        this.detail = detail;
    }
}

/**
 * The analysis URL from the cockpit's own `run_url` prop. Inertia route paths never carry query
 * strings, so swapping the final segment is the whole derivation; a shape it does not recognise
 * refuses to guess rather than build a URL that 404s for the wrong reason.
 */
export function advisorAnalysisUrl(runUrl: string): string | null {
    const trimmed = runUrl.replace(/\/+$/, '');

    if (!trimmed.endsWith('/cockpit')) {
        return null;
    }

    return `${trimmed.slice(0, -'/cockpit'.length)}/advisor-analysis`;
}

export function advisorAnalysisTimeoutMs(): number {
    return TIMEOUT_MS;
}

/**
 * One analysis request. Resolves to the 200 payload; rejects with an `AdvisorAnalysisError` in all
 * other cases (404 → `disabled`, 503/timeout/network/abort → `unavailable`). A non-JSON body on a
 * successful status is treated as `unavailable`: a page of HTML where an object was promised is an
 * unavailable analysis, not a parseable one.
 *
 * The 404 class is named `disabled` for the common case — the layer is switched off, which is the
 * default — but a 404 also answers a run that does not resolve. Both are terminal and
 * non-retryable, and the UI copy must not claim more than "this analysis is not available here"
 * (Hermes sitrep 2026-11-23 §3: a 404 does not *definitively prove* the feature switch is off).
 */
export async function requestAdvisorAnalysis(
    url: string,
    signal: AbortSignal,
): Promise<AdvisorAnalysis> {
    let response: { status: number; data: unknown };

    try {
        response = await window.axios.get<unknown>(url, { signal, timeout: TIMEOUT_MS });
    } catch (error) {
        // A cancellation is not a failure of the analysis: it is the composable (a turn change,
        // an unmount, a navigation) stopping the request, and it reports the state itself.
        if (axiosAborted(error)) {
            throw new AdvisorAnalysisError('unavailable', 'The analysis request was cancelled.');
        }

        if (axiosStatus(error) === 404) {
            throw new AdvisorAnalysisError('disabled', 'The AI analysis is switched off.');
        }

        // A timeout (axios's own `ECONNABORTED`) and a dead network are the same class from the
        // panel's side: the analysis is unavailable and a retry is offered.
        const detail = errorDetail(error);
        throw new AdvisorAnalysisError('unavailable', 'The analysis could not be produced.', detail);
    }

    if (typeof response.data !== 'object' || response.data === null || Array.isArray(response.data)) {
        throw new AdvisorAnalysisError('unavailable', 'The analysis could not be produced.');
    }

    return response.data as AdvisorAnalysis;
}

function axiosAborted(error: unknown): boolean {
    return (
        (error instanceof Error && error.name === 'AbortError') ||
        (typeof error === 'object' &&
            error !== null &&
            (error as { code?: string }).code === 'ERR_CANCELED')
    );
}

function axiosStatus(error: unknown): number | null {
    const response = (error as { response?: { status?: number } } | null)?.response;

    return typeof response?.status === 'number' ? response.status : null;
}

/** The 503 body's `error.message`, if the response carried the contract's error shape. */
function errorDetail(error: unknown): string | null {
    const body = (error as { response?: { data?: AdvisorAnalysisErrorBody | null } } | null)?.response
        ?.data;

    if (
        typeof body === 'object' &&
        body !== null &&
        typeof (body as { error?: { message?: unknown } }).error === 'object' &&
        (body as { error: { message?: unknown } }).error !== null &&
        typeof (body as { error: { message?: string } }).error.message === 'string'
    ) {
        return (body as { error: { message: string } }).error.message;
    }

    return null;
}
