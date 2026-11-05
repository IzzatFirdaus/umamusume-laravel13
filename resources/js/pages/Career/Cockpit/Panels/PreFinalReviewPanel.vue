<script setup lang="ts">
/*
 * The pre-final review panel, extracted from UraPanel for Phase 7A.1.
 *
 * Registered against the `pre_final_review` flag (not yet added to config). Reuses
 * `UraPreFinalReview.vue`, which owns the finale readiness check rendering.
 */
import UraPreFinalReview from '@/components/scenario/UraPreFinalReview.vue';
import type { ProvenanceState } from '../../../../types';

const props = defineProps<{
    scenario: {
        pre_final: {
            window: {
                state: string;
                label: string;
                turns_away: number | null;
                turns_ahead: number;
                title: string;
            };
            /*
             * `badge` is a provenance state, not free text: `CockpitController::preFinalSection()` writes
             * `'confirmed'` or null, and `UraPreFinalReview`'s `StatRow` types it `ProvenanceState | null`
             * because it feeds it to `ProvenanceBadge`. Declaring `string | null` here made the two
             * shapes disagree at the one prop this panel exists to forward.
             */
            recorded: Array<{
                label: string;
                value: string | null;
                cap: string | null;
                badge: ProvenanceState | null;
                title: string;
            }>;
            speed_target: {
                phrasing: string;
                value: string | null;
                short_by: string | null;
                /** `'calculated'` or null, from `preFinalSection()`; `ProvenanceBadge` reads it. */
                badge: ProvenanceState | null;
                title: string;
            };
            stamina_floors: {
                rows: Array<{ distance: string; floor: string }>;
                badge: ProvenanceState;
                title: string;
            };
            distance: { value: string | null; title: string };
        } | null;
    };
    label: string;
    kind: 'widget' | 'panel';
    name: string;
}>();
</script>

<template>
    <UraPreFinalReview v-if="props.scenario.pre_final !== null" :pre-final="props.scenario.pre_final" />
</template>
