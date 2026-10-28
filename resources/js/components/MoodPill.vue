<script setup lang="ts">
/*
 * Fills and arrows are written out as full literals, not built from the tier: Tailwind scans
 * sources for complete class names, so a concatenated `bg-mood-{...}` compiles to nothing.
 */
const props = withDefaults(
    defineProps<{
        // The MoodTier backing value ('GREAT' .. 'AWFUL'), or null when the turn stored no tier.
        tier?: string | null;
        unrecorded?: string;
        /**
         * The tier's own headline percentage, printed inside the pill. DM-5 makes the reading
         * word + arrow + percentage, and every cockpit HFW draws all three (`GREAT ↑ +20%`), so
         * the percentage belongs here rather than beside a pill that would then read as two facts.
         * Null prints nothing: a caller without the figure must not have one invented for it.
         */
        pct?: string | null;
    }>(),
    {
        tier: null,
        unrecorded: 'Mood not recorded',
        pct: null,
    },
);

const fills: Record<string, string> = {
    GREAT: 'bg-mood-great',
    GOOD: 'bg-mood-good',
    NORMAL: 'bg-mood-normal',
    BAD: 'bg-mood-bad',
    AWFUL: 'bg-mood-awful',
};

// The directional glyph D-259 makes mandatory: three of the five fills share a luminance with
// their neighbours, so hue cannot order them and the arrow is the only ordinal signal.
const arrows: Record<string, string> = {
    GREAT: '↑',
    GOOD: '↑',
    NORMAL: '→',
    BAD: '↓',
    AWFUL: '↓',
};
</script>

<template>
    <!-- Ink is `--color-on-mood`, stepped from the theme's dark ink because the light ink on these
         fills is 4.29:1 (DESIGN.md §6.17). -->
    <span
        v-if="tier !== null && fills[tier]"
        class="mood rounded-full px-1.5 py-0.5 font-mono text-xs uppercase tracking-wide text-on-mood"
        :class="fills[tier]"
    >{{ tier }}<span aria-hidden="true">{{ arrows[tier] }}</span><span v-if="pct !== null" class="ml-1">{{ pct }}</span></span>
    <span v-else class="text-xs text-ink-muted">{{ unrecorded }}</span>
</template>
