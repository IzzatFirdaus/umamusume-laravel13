<script setup lang="ts">
import { computed } from 'vue';

/*
 * A series as one line (master instruction §3.3), for the Career Timeline's momentum strip.
 *
 * `DESIGN.md:745-747` holds sparklines behind a gate — "no sparklines until a derived number is
 * explainable from turns". This component computes nothing: it plots the numbers it is handed, and
 * the caller answers the gate. The Timeline passes entered per-turn readings it prints in the table
 * beside the line, so the shape states no fact the table does not.
 *
 * The line is `aria-hidden`: every value it draws is printed next to it, so a name would announce a
 * picture carrying nothing the page does not already say.
 *
 * Two values are the floor — one point is not a line — and a flat series sits on the mid-line rather
 * than dividing by a zero span. An empty series draws the empty box; the caller decides whether the
 * line is worth a place at all.
 */
const props = withDefaults(
    defineProps<{
        /** The series, oldest first. */
        values: number[];
        /** Drawn width in CSS pixels. */
        width?: number;
        /** Drawn height in CSS pixels. */
        height?: number;
    }>(),
    { width: 96, height: 24 },
);

/** Just over half the 1.5 stroke, so the line never clips on the box edge. */
const INSET = 1;

const points = computed(() => {
    const series = props.values.filter((value) => Number.isFinite(value));

    if (series.length < 2) {
        return '';
    }

    const low = Math.min(...series);
    const high = Math.max(...series);
    const span = high - low;
    const step = (props.width - INSET * 2) / (series.length - 1);

    return series
        .map((value, index) => {
            const x = INSET + index * step;
            const y = span === 0 ? props.height / 2 : INSET + (1 - (value - low) / span) * (props.height - INSET * 2);

            return `${x.toFixed(2)},${y.toFixed(2)}`;
        })
        .join(' ');
});
</script>

<template>
    <svg
        :width="width"
        :height="height"
        :viewBox="`0 0 ${width} ${height}`"
        fill="none"
        aria-hidden="true"
        class="shrink-0"
    >
        <polyline
            v-if="points"
            :points="points"
            stroke="currentColor"
            stroke-width="1.5"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
    </svg>
</template>
