<script setup lang="ts">
import { computed } from 'vue';

/*
 * A stat against its cap, drawn as a ring (master instruction §3.1).
 *
 * **The arc is a stroked SVG path, not a `conic-gradient`.** The instruction named a conic gradient
 * behind a `mask-image`; `DESIGN.md:68` is owner-signed and permits a gradient only as a documented
 * motion cue, of which none is proposed, so the mechanism here is the one the house already draws
 * marks with — one stroked path on one weight (`NavGlyph.vue`). A `stroke-dasharray` arc carries the
 * same fraction with no gradient and no mask, and it survives forced-colours mode, where a masked
 * gradient does not.
 *
 * The centre prints the reading itself, because an arc alone is only a proportion. `tabular-nums`
 * keeps a row of rings aligned (DESIGN.md §3).
 *
 * No `N/A` branch: `UiHygieneTest` counts every title-less marker and an absence belongs to the
 * caller's words (`AbsenceValue.vue`), not to a gauge that would imply one by drawing an empty ring.
 */
const props = defineProps<{
    /** The stat as recorded. */
    value: number;
    /** The cap the ring is full at. */
    max: number;
    /** The stat's ink tier, e.g. `text-stat-speed-ink`; the arc and the number both take it. */
    color?: string;
    /** The ring's accessible name, e.g. `Speed`. An unnamed progressbar is an axe violation. */
    label: string;
}>();

/** Radius 16 on a 36 box leaves room for the 3-wide stroke inside the viewBox. */
const RADIUS = 16;
const CIRCUMFERENCE = 2 * Math.PI * RADIUS;

/** Clamped, so a value past the cap fills the ring instead of overdrawing it, and a bad `max` draws empty. */
const fraction = computed(() => {
    if (!Number.isFinite(props.value) || !Number.isFinite(props.max) || props.max <= 0) {
        return 0;
    }

    return Math.min(Math.max(props.value / props.max, 0), 1);
});

const dash = computed(() => `${(fraction.value * CIRCUMFERENCE).toFixed(2)} ${CIRCUMFERENCE.toFixed(2)}`);
</script>

<template>
    <div
        class="relative inline-flex items-center justify-center"
        :class="color"
        role="progressbar"
        :aria-label="label"
        :aria-valuenow="value"
        aria-valuemin="0"
        :aria-valuemax="max"
    >
        <svg viewBox="0 0 36 36" class="h-14 w-14" fill="none" stroke-width="3" aria-hidden="true">
            <circle cx="18" cy="18" :r="RADIUS" class="stroke-idle" />
            <circle
                cx="18"
                cy="18"
                :r="RADIUS"
                stroke="currentColor"
                :stroke-dasharray="dash"
                transform="rotate(-90 18 18)"
            />
        </svg>
        <span class="absolute font-mono text-sm font-bold tabular-nums">{{ value }}</span>
    </div>
</template>
