<script setup lang="ts">
/*
 * The career milestones panel, extracted from UraPanel for Phase 7A.1.
 *
 * Registered against the `career_milestones` flag, so it draws only when a scenario's matrix turns
 * that flag on. Reuses `UraMilestoneTracker.vue`, which owns the fan-gate timeline rendering.
 */
import UraMilestoneTracker from '@/components/scenario/UraMilestoneTracker.vue';

const props = defineProps<{
    scenario: {
        milestones: Array<{
            label: string;
            state: string;
            title: string;
            figures: string;
            figures_badge: string;
            figures_title: string;
            due_turn: number | null;
            due_label: string;
            due_title: string;
            consequence: string;
            condition: string | null;
            decision_url: string;
        }>;
    };
    label: string;
    kind: 'widget' | 'panel';
    name: string;
}>();
</script>

<template>
    <UraMilestoneTracker :milestones="props.scenario.milestones" />
</template>
