<script setup lang="ts">
/*
 * The advisor rail (SCREEN-009 §12, design-2.0 §23): the right column, and the only place on the
 * cockpit that makes a claim about what to do next.
 *
 * It carries the same five contract fields the card does and adds nothing to them — the column is the
 * heading and the standing explanation, the card is the recommendation. The explanation line states
 * the boundary in words: the advisor ranks, the Trainer decides (design-2.0 §2, §36).
 */
import RecommendationCard from './RecommendationCard.vue';

const props = defineProps<{
    action: string | null;
    band: 'AtOrAboveAdvisory' | 'BelowAdvisory' | null;
    reasons: string[];
    alternative: string | null;
    risk: string | null;
}>();
</script>

<template>
    <section aria-labelledby="career-advisor-heading" class="rounded-md border border-rule bg-panel p-4">
        <h2 id="career-advisor-heading" class="text-base font-semibold text-ink-strong">Advisor</h2>

        <RecommendationCard
            class="mt-3"
            :action="props.action"
            :band="props.band"
            :reasons="props.reasons"
            :alternative="props.alternative"
            :risk="props.risk"
        />

        <p class="mt-3 text-xs text-ink-muted">
            The advisor ranks the next turn against the target you entered. It records nothing and acts
            for nobody: every action is yours to take.
        </p>
    </section>
</template>
