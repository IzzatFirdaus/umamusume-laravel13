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
    }>(),
    {
        tier: null,
        unrecorded: 'Mood not recorded',
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
    <!-- Ink is `--color-on-mood`, stepped from the theme's dark ink because #482720 on these
         fills is 4.29:1 (DESIGN.md §6.17). -->
    <span
        v-if="tier !== null && fills[tier]"
        class="inline-flex items-center gap-1 rounded-full px-1.5 py-0.5 font-mono text-xs font-bold uppercase tracking-wide text-on-mood"
        :class="fills[tier]"
    >{{ tier }}<span aria-hidden="true">{{ arrows[tier] }}</span></span>
    <span v-else class="text-xs text-ink-muted">{{ unrecorded }}</span>
</template>
