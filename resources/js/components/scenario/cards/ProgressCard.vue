<script setup lang="ts">
/*
 * One card: the Unity Cup progression counters (A6.7), as the brief's own line
 * `Unity Trainings 55 · Spirit Bursts 6 / 5 Extreme`.
 *
 * Extracted from `UnityCupPanel.vue` for `S-RS-VISUAL-1`. The burst half pairs the two tallies, so
 * it is drawn whole when both are recorded and named alone when only one is, never as `6 / N/A`
 * (an unrecorded tally is not a zero, D-220). The caller decides whether the card renders at all:
 * it draws only when the Trainer stated one of the counters.
 */
import { computed } from 'vue';

const props = defineProps<{
    unityTrainingsCount: number | null;
    spiritBurstsCount: number | null;
    extremeBurstsCount: number | null;
}>();

const counterSegments = computed((): string[] => {
    const segments: string[] = [];

    if (props.unityTrainingsCount !== null) {
        segments.push(`Unity Trainings ${props.unityTrainingsCount}`);
    }

    if (props.spiritBurstsCount !== null && props.extremeBurstsCount !== null) {
        segments.push(`Spirit Bursts ${props.spiritBurstsCount} / ${props.extremeBurstsCount} Extreme`);
    } else if (props.spiritBurstsCount !== null) {
        segments.push(`Spirit Bursts ${props.spiritBurstsCount}`);
    } else if (props.extremeBurstsCount !== null) {
        segments.push(`${props.extremeBurstsCount} Extreme`);
    }

    return segments;
});
</script>

<template>
    <div class="card p-3">
        <h2 class="text-lg font-semibold text-ink-strong">Unity Cup progress</h2>
        <p class="mt-2 text-sm text-ink">{{ counterSegments.join(' · ') }}</p>
    </div>
</template>
