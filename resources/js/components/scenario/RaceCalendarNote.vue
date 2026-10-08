<script setup lang="ts">
/*
 * The `race_calendar` panel's renderer (plan §9.1). The Cockpit draws this scenario's calendar in
 * its own left column (`RunRaceStrip`, D14a), so the shell's fallback would print a false "cannot
 * draw" beside a column that is showing exactly this calendar. The renderer states where the
 * calendar lives instead of duplicating it, and it names no scenario: any run whose matrix turns
 * the flag on gets the same sentence.
 *
 * Lives as a component rather than a registration inline only because the registry seam must stay
 * importable from a Node-side test; see the registration block on `ScenarioPanel.vue`.
 */
import AlertRow from './AlertRow.vue';

defineProps<{
    label: string;
    kind: 'panel';
    name: string;
}>();
</script>

<template>
    <AlertRow
        glyph="○"
        text="The race calendar renders in this screen's Races in this run column: this run's recorded races, the turn being decided, and the mandatory races still to come."
        detail="The matrix turns this flag on, and the Cockpit draws it once, in its own left column."
        tone="note"
    />
</template>
