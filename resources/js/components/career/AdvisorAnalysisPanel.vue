<script setup lang="ts">
/*
 * The AI Advisor panel (Advisor AI frontend integration, F2/F4).
 *
 * It sits **below** `NextDecision` inside the advisor rail: the five-element deterministic answer
 * keeps its order and its single primary action, and this block only adds the AI's reading of the
 * same turn. `AdvisorRail.vue` renders it; nothing here re-derives a recommendation.
 *
 * **No auto-fetch.** The panel opens with the request button (`idle`): the Trainer asks for the
 * analysis, because the server's feature flag and the Trainer's per-turn decision are different
 * things (14-api-contract.md §2). Nothing is fired on mount.
 *
 * **One fact, one label.** The four provider states read as four sentences, and the distinction
 * between Clef's choice and the engine's floor is stated wherever they differ
 * (`recommended_action` vs `deterministic_action`) without changing which action the rail's
 * primary door points at: the door is `NextDecision`'s, and this block never executes or re-aims
 * a gameplay action.
 *
 * **Diagnostics stay diagnostic.** `decision.preference` and `decision.confidence` are never
 * rendered: they are model preference figures, not probabilities of anything in the game
 * (ADR-0001 §3, ADR-0020 §2; 14-api-contract.md §3).
 *
 * **Citations are text, not links.** An `evidence[].path` is a repository file, not a served
 * route; a clickable source link without a real destination is the exact thing F4 forbids. The
 * rail's own source-line vocabulary (NextDecision's `Source: ...` line) is the pattern: the
 * preformatted `citation` string is printed as-is.
 *
 * Props are read through the composable, not destructured into consts: Inertia patches the page
 * in place, and the composable's invalidation watch is what makes a stale turn's answer drop
 * rather than linger (`useAdvisorAnalysis.ts`).
 */
import { computed } from 'vue';

import BaseButton from '../Form/BaseButton.vue';
import { useAdvisorAnalysis } from '../../composables/useAdvisorAnalysis';
import type { AdvisorAnalysis } from '../../services/advisor/analysis';

const props = defineProps<{
    /** The cockpit's own `run_url` prop, the one server-supplied address for this run. */
    runUrl: string;
    /** The turn the page stands on, null while the run has recorded none. */
    currentTurn: number | null;
    /**
     * The career revision the cockpit was rendered for (the page's `advisor_revision` prop). The
     * panel compares it against the revision a response carries, which is how a same-turn edit is
     * caught: the turn number does not move for one, and the revision does.
     */
    careerRevision?: string | null;
}>();

const { state, analysis, failure, loading, request, cancel } = useAdvisorAnalysis(
    computed(() => props.runUrl),
    computed<number | null>(() => props.currentTurn),
    computed<string | null>(() => props.careerRevision ?? null),
);

const ready = computed(() => state.value === 'ready' && analysis.value !== null);

/**
 * The payload the `ready` branch renders. The `ready` gate above is the non-null check; the cast
 * keeps the template expressions type-checked without repeating the guard on every line.
 */
const answer = computed<AdvisorAnalysis>(() => analysis.value as AdvisorAnalysis);

/** The one sentence that says who answered. No provider state renders without it. */
const attribution = computed((): string | null => {
    const answer = analysis.value;
    if (answer === null) {
        return null;
    }

    const decision = answer.decision;

    if (decision.provider === 'clef' && decision.status === 'validated') {
        return 'Clef ranked this turn for you.';
    }

    if (decision.provider === 'clef' && decision.status === 'abstained') {
        return 'Clef declined to choose; the engine\'s own answer stands.';
    }

    return 'The engine\'s own ranking, no AI consulted.';
});

/**
 * Where the AI's pick and the engine's floor disagree. The rail's primary door is the engine's
 * door, so the difference must be stated rather than implied.
 */
const clefDiffers = computed((): { ai: string; engine: string } | null => {
    const answer = analysis.value;

    if (
        answer === null ||
        answer.recommended_action === null ||
        answer.deterministic_action === null ||
        answer.decision.provider !== 'clef'
    ) {
        return null;
    }

    if (answer.recommended_action.id === answer.deterministic_action.id) {
        return null;
    }

    return { ai: answer.recommended_action.label, engine: answer.deterministic_action.label };
});

/** The explanation's origin, as a source line in the rail's own vocabulary. */
const explanationSource = computed((): string | null => {
    const answer = analysis.value;

    if (answer === null) {
        return null;
    }

    return answer.explanation_source === 'logfare'
        ? 'Explanation: AI.'
        : 'Explanation: the engine\'s own wording.';
});

const hasEvidence = computed(() => (analysis.value?.evidence.length ?? 0) > 0);
const hasAlternatives = computed(() => (analysis.value?.alternatives.length ?? 0) > 0);
const hasBlocked = computed(() => (analysis.value?.blocked.length ?? 0) > 0);
const hasUnknowns = computed(() => (analysis.value?.unknowns.length ?? 0) > 0);
</script>

<template>
    <!-- The AI's reading of the turn, one block below the deterministic answer. A region, not a
         card: the rail already owns the card and the region's accessible name, and this block is
         part of that same answer, not a second one. -->
    <div class="mt-4 border-t-2 border-rule pt-4">
        <div class="flex flex-wrap items-center gap-2">
            <h3 class="text-lg font-semibold text-ink-strong">AI advisor</h3>
            <span v-if="ready" class="pill pill-neutral">
                Turn {{ answer.turn }}
            </span>
        </div>

        <!-- Idle: the request is the Trainer's, not the page's. No auto-fetch, by design. -->
        <div v-if="state === 'idle'" class="mt-2">
            <p class="text-sm text-ink">
                Ask the AI to rank this turn against the target you entered. The advisor's
                recommendation above is unaffected by the answer.
            </p>
            <BaseButton
                class="mt-2"
                variant="secondary"
                size="sm"
                label="Ask the AI to analyze this turn"
                @click="request()"
            />
        </div>

        <!-- Loading: a user-initiated async action, which ADR-0007 allows a custom loading state
             for. Announced, not drawn as a skeleton. -->
        <div v-else-if="state === 'loading'" class="mt-2">
            <p role="status" class="text-sm text-ink-muted">
                Working on it. The engine's recommendation above stays in place while this runs.
            </p>
            <BaseButton
                class="mt-2"
                variant="secondary"
                size="sm"
                label="Cancel"
                @click="cancel()"
            />
        </div>

        <!-- 404: terminal and quiet, never an error, never a retry. The common cause is the
             feature switch being off, but the same 404 answers an unresolvable run, so the copy
             claims only that no analysis exists here — the deterministic rail above is the
             complete answer either way. -->
        <p v-else-if="state === 'disabled'" class="mt-2 text-sm text-ink-muted">
            No AI analysis is available for this run here. The recommendation above is the
            engine's own, and it stands on its own.
        </p>

        <!-- 503 / timeout / network: a recoverable failure, with its own retry. The server's
             message is the one printed, because it states what the Trainer needs to know: the
             recommendation on the cockpit is unaffected. -->
        <div v-else-if="state === 'unavailable'" class="mt-2">
            <p role="alert" class="text-sm text-risk">{{ failure }}</p>
            <BaseButton
                class="mt-2"
                variant="secondary"
                size="sm"
                label="Try again"
                @click="request()"
            />
        </div>

        <!-- Ready: the answer, in the rail's own order. -->
        <template v-else-if="ready">
            <p class="mt-1 text-sm font-semibold text-ink-strong">{{ attribution }}</p>

            <p class="mt-1 text-sm text-ink">{{ answer.explanation }}</p>

            <!-- Clef chose a different action than the engine. Stated, not implied: the primary
                 door above is the engine's, and this line is what keeps that from reading as the
                 AI silently moving it. -->
            <p v-if="clefDiffers !== null" class="mt-1 text-sm text-ink">
                Clef would pick {{ clefDiffers.ai }}; the engine's answer above is
                {{ clefDiffers.engine }}. Take the door for whichever one you mean to play.
            </p>

            <!-- Degraded retrieval: the answer is real, the citations are not. Say so once. -->
            <p v-if="answer.retrieval.degraded" class="mt-1 text-xs text-ink-muted">
                Citations are unavailable this refresh; the answer itself is unaffected.
            </p>

            <!-- One-sentence provenance, the rail's own source-line vocabulary. -->
            <p class="mt-1 text-xs text-ink-muted">
                {{ explanationSource }} &middot; {{ answer.generated_at }}
            </p>

            <!-- The obligation the engine carried into the analysis. Text only: the rail's
                 obligations table above is the table, and this line is the AI's own read of it. -->
            <p v-if="answer.obligation !== null" class="mt-2 text-sm text-ink">
                <span class="font-semibold text-ink-strong">Obligation: </span>
                {{ answer.obligation }}
            </p>

            <!-- Alternatives the engine ranked, with the engine's own reason line each. -->
            <template v-if="hasAlternatives">
                <h4 class="mt-3 text-base font-semibold text-ink-strong">Alternatives</h4>
                <ul class="mt-1 space-y-1">
                    <li
                        v-for="option in answer.alternatives"
                        :key="option.id"
                        class="text-sm text-ink"
                    >
                        <span class="font-semibold text-ink-strong">{{ option.label }}</span>
                        {{ option.reason }}
                        <span v-if="option.engine_choice" class="text-ink-muted">
                            (the engine's own answer)
                        </span>
                    </li>
                </ul>
            </template>

            <!-- What the engine withheld, and why. A list, not a gate: the engine decides, the
                 Trainer plays. -->
            <template v-if="hasBlocked">
                <h4 class="mt-3 text-base font-semibold text-ink-strong">Not offered</h4>
                <ul class="mt-1 space-y-1">
                    <li v-for="action in answer.blocked" :key="action.id" class="text-sm text-ink">
                        <span class="font-semibold text-ink-strong">{{ action.label }}</span>
                        : {{ action.blocker }}
                    </li>
                </ul>
            </template>

            <!-- Missing facts, in the rail's own refusal vocabulary. -->
            <p v-if="hasUnknowns" class="mt-2 text-sm text-ink">
                Missing: {{ answer.unknowns.join(', ') }}.
            </p>

            <!-- Sources as text. The path is a repository file, not a served route, so a link
                 would be invented; the preformatted citation is the rail's own source line. -->
            <details v-if="hasEvidence" class="mt-3">
                <summary class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-ink-strong">
                    Sources ({{ answer.evidence.length }})
                </summary>
                <ul class="mt-1 space-y-1">
                    <li v-for="item in answer.evidence" :key="item.id" class="text-xs text-ink">
                        <!-- The citation already carries its server scope (`Evidence::citation()`
                             appends ` [Global]`); printing the `servers` array again would double it. -->
                        {{ item.citation }}
                    </li>
                </ul>
            </details>
        </template>
    </div>
</template>
