<script setup lang="ts">
/*
 * The Happy Meek duel panel, extracted from UraPanel for Phase 7A.1.
 *
 * Registered against the `happy_meek` flag, so it draws only when a scenario's matrix turns that
 * flag on. UraPanel still owns the display-only dialog (`UraDuelPanel.vue`); this wrapper carries
 * the props through and mounts the dialog opener.
 */
import UraDuelPanel from '@/components/scenario/UraDuelPanel.vue';

const props = defineProps<{
    scenario: {
        duel: {
            rules: Array<{ label: string; value: string | null; badge: string | null; title: string }>;
            hints: Array<{ stat: string; name: string; sp_cost: string | null; badge: string | null; title: string }>;
            run: Array<{ label: string; value: string | null; badge: string | null; title: string }>;
        } | null;
    };
    label: string;
    kind: 'widget' | 'panel';
    name: string;
}>();
</script>

<template>
    <UraDuelPanel v-if="props.scenario.duel !== null" :duel="props.scenario.duel" />
</template>
