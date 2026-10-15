<script setup lang="ts">
/*
 * The pre-final review panel, extracted from UraPanel for Phase 7A.1.
 *
 * Registered against the `pre_final_review` flag (not yet added to config). Reuses
 * `UraPreFinalReview.vue`, which owns the finale readiness check rendering.
 */
import UraPreFinalReview from '@/components/scenario/UraPreFinalReview.vue';

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
            recorded: Array<{
                label: string;
                value: string | null;
                cap: string | null;
                badge: string | null;
                title: string;
            }>;
            speed_target: {
                phrasing: string;
                value: string | null;
                short_by: string | null;
                badge: string | null;
                title: string;
            };
            stamina_floors: {
                rows: Array<{ distance: string; floor: string }>;
                badge: string;
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
