import { computed, onUnmounted, ref, watch, type ComputedRef, type Ref } from 'vue';
import { router } from '@inertiajs/vue3';

import {
    AdvisorAnalysisError,
    advisorAnalysisUrl,
    requestAdvisorAnalysis,
    type AdvisorAnalysis,
} from '../services/advisor/analysis';

/**
 * The AI Advisor's request lifecycle (Advisor AI frontend integration, F3).
 *
 * One owner of the in-flight state for the rail's analysis block: `idle`, `loading`, `ready`,
 * `disabled` (the 404, feature off) and `unavailable` (503, timeout or a dead network). Two
 * invariants keep an obsolete answer from overwriting a newer one:
 *
 * - **Late-response rejection.** Every response is checked against the turn and the career revision
 *   it was requested for. The server stamps `turn` and `career_revision` onto the payload, so a
 *   response for a state the page no longer stands on is discarded rather than rendered. The turn
 *   check cannot see a same-turn edit: correcting this turn's Energy reading or the build target
 *   moves the revision and leaves the turn number where it was, which is why the revision is
 *   compared too. The server-side cache key prevents a stale *cache hit*; these checks are the
 *   in-flight half of the same guarantee (14-api-contract.md, state 7).
 * - **Reactive invalidation.** When the page's turn, its career revision or the run's address
 *   changes underneath the block (the Trainer logs a turn or edits a reading, and Inertia patches
 *   the props in place, which is exactly how this page works), the in-flight request is aborted and
 *   any held answer drops back to `idle`: advice for the previous state is not advice for this one.
 *
 * **The revision is the server's, never a second implementation.** `advisor_revision` arrives as a
 * cockpit prop computed by the same function the analysis fingerprints with
 * (`AdvisorSnapshot::revision()`), so the two values are comparable by construction. This module
 * compares them; it never builds one.
 *
 * **Refresh is the boundary.** A prop cannot inform an open page about another tab's write. This
 * protects same-page mutations and every response; a career edited in a second tab is only seen
 * after a visit or reload, which is the supported model and not a promise made here.
 *
 * **No auto-fetch.** The panel asks the Trainer for the request (`idle` renders the button);
 * nothing fires on mount. That is the "Trainer voluntarily requests analysis" line: the server's
 * feature flag and the Trainer's per-turn decision are different things, and this composable owns
 * only the second.
 */

export type AdvisorLifecycleState = 'idle' | 'loading' | 'ready' | 'disabled' | 'unavailable';

export interface AdvisorLifecycle {
    state: Ref<AdvisorLifecycleState>;
    analysis: Ref<AdvisorAnalysis | null>;
    /** The 503 line, shown in the `unavailable` state; null in every other state. */
    failure: Ref<string | null>;
    loading: ComputedRef<boolean>;
    ready: ComputedRef<boolean>;
    request: () => Promise<void>;
    /** Stop an in-flight request and clear any held answer. */
    cancel: () => void;
}

export function useAdvisorAnalysis(
    runUrl: Ref<string> | string,
    currentTurn: Ref<number | null> | number | null,
    currentRevision: Ref<string | null> | string | null = null,
): AdvisorLifecycle {
    const runUrlRef = computed<string>(() => (typeof runUrl === 'string' ? runUrl : runUrl.value));
    const turnRef = computed<number | null>(() =>
        typeof currentTurn === 'number' || currentTurn === null ? currentTurn : currentTurn.value,
    );
    const revisionRef = computed<string | null>(() =>
        typeof currentRevision === 'string' || currentRevision === null
            ? currentRevision
            : currentRevision.value,
    );

    const state = ref<AdvisorLifecycleState>('idle');
    const analysis = ref<AdvisorAnalysis | null>(null);
    const failure = ref<string | null>(null);
    const loading = computed(() => state.value === 'loading');
    const ready = computed(() => state.value === 'ready');

    let controller: AbortController | null = null;
    /** The turn the in-flight request was made for; a response for any other turn is discarded. */
    let requestedFor: number | null = null;
    /**
     * Monotonic request token. The server's revision key guarantees only its *cache*: an
     * in-flight response can still land after its request was superseded, and abort() is not
     * always observable in time. A response answers only if it is the one this composable
     * currently waits for (Hermes sitrep 2026-11-23 §3: do not assume backend caching prevents
     * in-flight races).
     */
    let requestToken = 0;
    let unmounted = false;

    function abortInFlight(): void {
        controller?.abort();
        controller = null;
        requestedFor = null;
        // Bump the token so a response already past abort() cannot win the race either.
        requestToken += 1;
    }

    /** Drop any held answer and stop: the career moved on and this answer belongs to a past turn. */
    function invalidate(): void {
        abortInFlight();
        analysis.value = null;
        failure.value = null;
        state.value = 'idle';
    }

    async function request(): Promise<void> {
        // Duplicate-request prevention: a request while one is in flight is a no-op.
        if (state.value === 'loading') {
            return;
        }

        const url = advisorAnalysisUrl(runUrlRef.value);

        if (url === null) {
            state.value = 'unavailable';
            failure.value = 'The analysis could not be produced.';
            return;
        }

        const turnAtRequest = turnRef.value;
        const revisionAtRequest = revisionRef.value;
        controller = new AbortController();
        const signal = controller.signal;
        const tokenAtRequest = requestToken;
        state.value = 'loading';
        failure.value = null;
        requestedFor = turnAtRequest;

        try {
            const payload = await requestAdvisorAnalysis(url, signal);

            // The request was superseded (a new request, a turn change, an unmount, a navigation):
            // this response belongs to whoever started it, not to whoever is still here.
            if (
                controller === null ||
                signal.aborted ||
                unmounted ||
                tokenAtRequest !== requestToken
            ) {
                return;
            }

            // Stale-response rejection: the turn the page stands on moved past the turn this answer
            // was produced for. A response for turn 31 never overwrites the view of turn 32.
            if (requestedFor !== null && payload.turn !== requestedFor) {
                abortInFlight();
                analysis.value = null;
                state.value = 'idle';
                return;
            }

            // Same-turn stale-response rejection: the page was rendered for a different career
            // revision than the one this answer computed. A reading or target edit inside the turn
            // the page still stands on leaves the turn number alone, so the check above cannot see
            // it; the revision can. Only compared when the page supplied one — a null prop means
            // the feature is off and no analysis was requestable in the first place.
            if (revisionAtRequest !== null && payload.career_revision !== revisionAtRequest) {
                abortInFlight();
                analysis.value = null;
                state.value = 'idle';
                return;
            }

            analysis.value = payload;
            state.value = 'ready';
        } catch (error) {
            if (unmounted || tokenAtRequest !== requestToken) {
                // The request was superseded (or the page is gone): its failure belongs to the
                // request that started it, never to the state this composable now holds.
                return;
            }

            // A cancellation this composable issued (turn change, new request, navigation): the
            // state was already set by its caller. Never render an error for a request the Trainer
            // stopped.
            if (signal.aborted) {
                abortInFlight();
                return;
            }

            if (error instanceof AdvisorAnalysisError && error.kind === 'disabled') {
                // 404: terminal, quiet, never retried. The common cause is the feature switch
                // being off (the default), but a 404 also answers a run that no longer resolves,
                // so nothing here may claim *why* — only that no analysis exists at this address
                // (Hermes sitrep 2026-11-23 §3). The deterministic rail on screen is the
                // complete answer either way.
                state.value = 'disabled';
                return;
            }

            // Everything else: the 503 body, a timeout, a network fault, or an unexpected throw.
            // The server's own line wins when it carried one, because the controller composes it as
            // the statement the Trainer reads and a restatement could drift from it; the client
            // line is the floor for the faults that carry no body at all.
            if (error instanceof AdvisorAnalysisError && error.detail !== null) {
                failure.value = error.detail;
            } else {
                failure.value = 'The analysis could not be produced. The recommendation on the cockpit is unaffected.';
            }
            state.value = 'unavailable';
        }
    }

    function cancel(): void {
        if (state.value === 'loading') {
            invalidate();
        }
    }

    // A turn change underneath the block invalidates it, in any state: the answer is for the turn
    // the page used to stand on, and the in-flight request was asked about that same turn.
    watch(turnRef, () => {
        invalidate();
    });

    // The same rule for the revision, and this is the watch the turn one cannot replace: a
    // correction to this turn's readings, or a build-target edit, re-renders the page with a new
    // `advisor_revision` and the same turn number. The answer on screen is for the old state.
    watch(revisionRef, () => {
        invalidate();
    });

    // The same rule for a run change (the rail's `run_url` prop is the run's own address).
    watch(runUrlRef, () => {
        invalidate();
    });

    // Leaving the page aborts the in-flight request. Inertia fires `start` for hover prefetches
    // too, and a prefetch is not navigation this block must serve, so the guard is the same one
    // `useVisitState.ts` reasons through.
    const stopNav = router.on('start', (event) => {
        if (event.detail.visit.prefetch) {
            return;
        }
        abortInFlight();
    });

    onUnmounted(() => {
        unmounted = true;
        abortInFlight();
        stopNav();
    });

    return { state, analysis, failure, loading, ready, request, cancel };
}
