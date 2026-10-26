<script setup lang="ts">
/*
 * One measuring tile (HFW `.tile`, shell.css:618-621): small caps key over a figure, or over a
 * word when the figure is absent.
 *
 * `hint` is the reason a figure is `N/A`. It is the accessible twin of the word, not the only
 * place the reason lives: a tooltip alone is unreadable on touch and invisible to print, so callers
 * pair every absent value with a sentence in the card. Legible per DESIGN.md §5.
 */
withDefaults(
    defineProps<{
        label: string;
        value: string;
        /** True when `value` is a word about absence rather than a figure (`.v.na`). */
        absent?: boolean;
        hint?: string;
    }>(),
    { absent: false, hint: undefined },
);
</script>

<template>
    <div class="rounded-sm border border-rule bg-raised px-2.5 py-2">
        <div class="text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">{{ label }}</div>
        <div
            class="mt-0.5 font-extrabold leading-[1.3]"
            :class="absent ? 'text-[12.5px] font-semibold text-ink-muted' : 'text-base text-ink-strong'"
            :title="hint"
            data-tile-value
        >{{ value }}</div>
    </div>
</template>
