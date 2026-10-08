<script setup lang="ts">
/*
 * One scenario resource: its value as text, and the same value as a bar (design-2.0 §45; WCAG 2.2
 * AA requires state never to be carried by the bar alone, so the number is the readout and the bar
 * is the second, redundant statement).
 *
 * **No value prints `N/A` with a reason, never a zero.** No column holds a scenario resource yet,
 * so every meter this build renders is in that state; a `0` would say "the run has none", which is
 * a different claim from "this tool cannot see it" (D-220, AGENTS.md §5).
 *
 * **No maximum means no fill.** A bar with a proportional width needs a denominator, and the tool
 * has no sourced one for these resources, so the track renders empty and the accessible name states
 * the value without implying a proportion. A caller that later holds a sourced maximum passes it and
 * the fill appears; inventing one here would draw a share of nothing.
 */
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        /** The matrix's own display name for this resource. Required: an unlabelled meter is the failure this component prevents. */
        label: string;
        value: number | string | null;
        /** The sourced ceiling, or null when no source states one. */
        max?: number | null;
        /** Why the value is absent, printed as a `title`. */
        absence?: string | null;
    }>(),
    { max: null, absence: null },
);

const text = computed<string>(() => (props.value === null ? 'N/A' : String(props.value)));

const ariaLabel = computed<string>(() => {
    if (props.value === null) {
        return `${props.label}: not recorded`;
    }

    return props.max === null ? `${props.label}: ${text.value}` : `${props.label}: ${text.value} of ${props.max}`;
});

const fillPercent = computed<number>(() => {
    if (props.value === null || props.max === null || props.max <= 0) {
        return 0;
    }

    const numeric = typeof props.value === 'number' ? props.value : Number(props.value);

    if (Number.isNaN(numeric)) {
        return 0;
    }

    return Math.max(0, Math.min(100, (numeric / props.max) * 100));
});
</script>

<template>
    <div>
        <p class="flex flex-wrap items-baseline justify-between gap-x-2">
            <span class="text-xs text-ink-muted">{{ props.label }}</span>
            <span
                class="font-mono text-sm tabular-nums text-ink-strong"
                :title="props.value === null ? (props.absence ?? undefined) : undefined"
            >{{ text }}</span>
        </p>

        <div
            class="mt-1 h-1.5 overflow-hidden rounded-full bg-sunken"
            role="img"
            :aria-label="ariaLabel"
        >
            <div v-if="fillPercent > 0" class="h-full rounded-full bg-chrome" :style="{ width: `${fillPercent}%` }" />
        </div>
    </div>
</template>
