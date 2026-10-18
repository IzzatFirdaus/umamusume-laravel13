<script setup lang="ts">
/*
 * The exception pill (plan 1.6).
 *
 * It exists to mark the rows that are NOT plain truth: a figure the tool estimated, a value it does
 * not know, a match it could not confirm. There is deliberately no `confirmed` case — `'confirmed'` is
 * not in the union, so passing it is a TypeScript error at a typed call site. A surface whose every
 * row is confirmed carries no badge at all, which is the point: a badge on everything marks nothing.
 *
 * The union is the compile-time guard; `KNOWN` below is the runtime one. A server hand-off is JSON and
 * TypeScript stops at the component boundary, so an unrecognised state must render NOTHING rather than
 * a pill with an undefined tone and the raw string inside it.
 *
 * The rendered pill carries the WORD beside the colour, never colour alone (WCAG 1.4.1). The three
 * tones sit within a few percent luminance of each other, so hue cannot carry the state on its own and
 * a reader who cannot distinguish the fills still reads the sentence.
 */
import { computed } from 'vue';

const KNOWN = ['estimated', 'unknown', 'unconfirmed'] as const;

type KnownState = (typeof KNOWN)[number];

const props = withDefaults(
    defineProps<{
        /** The exception's name. `'confirmed'` is intentionally absent. */
        state: KnownState | string;
        /** The visible word. Defaults to the state's own name, capitalised. */
        label?: string;
        /**
         * Why the row is an exception. Rendered as both a `title` (hover) and an `sr-only` suffix
         * (always), so the reason is never reachable by pointer alone (WCAG 1.4.13).
         */
        title?: string;
    }>(),
    { label: undefined, title: undefined },
);

const WORDS: Record<KnownState, string> = {
    estimated: 'Estimated',
    unknown: 'Unknown',
    unconfirmed: 'Unconfirmed',
};

/*
 * Tone per state, as complete literals rather than concatenated class names: Tailwind scans source
 * for whole class strings, so `bg-exception-${state}` would compile to nothing.
 */
const TONES: Record<KnownState, string> = {
    estimated: 'bg-sunken text-ink-strong border-rule',
    unknown: 'bg-sunken text-ink-muted border-rule',
    unconfirmed: 'bg-sunken text-ink-strong border-rule',
};

const isKnown = (value: string): value is KnownState => (KNOWN as readonly string[]).includes(value);

/** `null` when the state is not one this component marks, which is what suppresses the pill. */
const word = computed<string | null>(() => (isKnown(props.state) ? (props.label ?? WORDS[props.state]) : null));

const tone = computed<string>(() => (isKnown(props.state) ? TONES[props.state] : ''));
</script>

<template>
    <span
        v-if="word !== null"
        class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs font-semibold"
        :class="tone"
        :title="props.title"
    >
        <!-- The word is the state. The dot is decoration and is hidden from the accessibility tree so
             the pill reads "Estimated", not "bullet Estimated". -->
        <span aria-hidden="true" class="font-mono">•</span>
        <span>{{ word }}</span>
        <!-- The same sentence the tooltip carries, always in the accessibility tree: a title reachable
             only by hover is unavailable to a keyboard or touch reader. -->
        <span v-if="props.title" class="sr-only">: {{ props.title }}</span>
    </span>
</template>
