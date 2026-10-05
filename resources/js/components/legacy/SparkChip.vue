<script setup lang="ts">
/*
 * One Spark, as the client renders it: its kind, what it acts on, and how many stars it rolled.
 *
 * **One component for every Spark on every Legacy surface** (Law of Similarity, plan §13). A second
 * chip shape for grandparents, or one for the compare columns, would read as a different kind of fact
 * than it is, and the whole point of this screen is that every chip in it means the same thing.
 *
 * **Three channels, never one.** WCAG 1.4.1 and plan §10 forbid showing state through colour alone, so
 * the kind is always spelled (`Blue`, `Pink`, …), always next to its own star count, and the colour is
 * decoration on top of that. The kind is also in the accessible name, so a screen reader announces
 * "Blue Spark, Speed, 2 stars" rather than four separate fragments.
 *
 * **The colour comes from the design tokens, not from a palette lookup.** The five Spark hues are the
 * client's own, and the repository holds no token per Spark kind; adding five tokens is a `DESIGN.md`
 * change this slice has no basis for. So the swatch is a 1.5rem cell of `bg-sunken` carrying the kind's
 * initial, which is a shape, and the kind is spelled beside it. That keeps G-19 and `DesignTokensTest`
 * satisfied (no `dark:`, no non-token utility) and keeps a missing token from silently producing an
 * unlabelled grey chip that is indistinguishable from every other one.
 */
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        kindLabel: string;
        target?: string | null;
        stars?: number | null;
    }>(),
    { target: null, stars: null },
);

const starsLabel = computed(() => (props.stars === null ? 'stars not recorded' : `${props.stars} star`));

const accessibleName = computed(() => {
    const target = props.target === null || props.target === '' ? null : props.target;

    return target === null
        ? `${props.kindLabel} Spark, ${starsLabel.value}`
        : `${props.kindLabel} Spark, ${target}, ${starsLabel.value}`;
});
</script>

<template>
    <!-- The star glyph is decorative: `aria-hidden` because the accessible name below already says
         the count in words, and a screen reader that read "black star" before every number would make
         the column slower to scan for no gain. A sighted Trainer still gets the shape. -->
    <span
        class="inline-flex items-center gap-1.5 rounded-full border border-rule bg-sunken px-2 py-0.5 text-xs text-ink"
        :title="accessibleName"
    >
        <span aria-hidden="true" class="font-mono text-[0.65rem] font-bold text-ink-strong">
            {{ kindLabel.slice(0, 1) }}
        </span>
        <span class="font-medium">{{ kindLabel }}</span>
        <span v-if="target" class="text-ink-muted">{{ target }}</span>
        <span aria-hidden="true" class="text-pick-line">★</span>
        <span class="font-mono tabular-nums">{{ stars ?? 'N/A' }}</span>
        <span class="sr-only">{{ accessibleName }}</span>
    </span>
</template>
