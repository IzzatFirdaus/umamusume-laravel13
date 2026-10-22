<script lang="ts">
/**
 * The four provenance states, re-exported so callers' import path stays `ProvenanceBadge`'s old one.
 * The canonical union lives in `resources/js/types.ts`; see the note in `ProvenanceBadge.vue` for why
 * `state` keeps its literal here rather than importing the whole contract into `defineProps`.
 */
import type { ProvenanceState } from '../types';

export type { ProvenanceState };
</script>

<script setup lang="ts">
/*
 * ProvenanceMark (plan 1.7). Replaces `ProvenanceBadge.vue`, which marked all four provenance states.
 *
 * **Confirmed renders nothing.** A figure the tool is sure of does not need a badge: marking every
 * normal figure is how a screen ends up with a badge beside everything and a badge that means nothing.
 * Only the exceptions are marked — `calculated`, `estimated`, `unknown` — so the eye lands on the
 * numbers that need a second look.
 *
 * The union keeps `confirmed` because it is a server contract (`types.ts`, two payloads carry it) and
 * the backlog's `DESIGN.md` §5 ruling keeps `ProvenanceState` one union. What changed is the render:
 * `confirmed` is accepted and draws nothing, which is also how a legacy call site that still passes it
 * degrades — silently and correctly, rather than as a runtime error on a screen a Trainer is reading.
 *
 * **The title is never hover-only (WCAG 1.4.13).** The reason renders as an `sr-only` suffix in the
 * accessible name at all times, and the visible text appears in a popover on BOTH focus and hover. A
 * keyboard reader reaching the badge with Tab sees the same sentence a pointer reader sees.
 *
 * The placement rule from the old component still holds: the mark sits beside the figure it labels,
 * never in a page-level legend, and a figure with no mark is a confirmed figure rather than a bug.
 */
import { computed, ref } from 'vue';
import type { ProvenanceState } from '../types';

const props = defineProps<{
    state: 'confirmed' | 'calculated' | 'estimated' | 'unknown';
    /** Optional reason or arithmetic note. Shown on focus and hover, and always in the accessible name. */
    title?: string;
}>();

/*
 * Only the three exception states carry a mark. `confirmed` is deliberately absent from this map: its
 * absence is what makes the render below null, and it is clearer than a branch that returns nothing.
 */
const MARKS: Record<'calculated' | 'estimated' | 'unknown', { glyph: string; word: string }> = {
    calculated: { glyph: '∑', word: 'Calculated' },
    estimated: { glyph: '~', word: 'Estimated' },
    unknown: { glyph: '?', word: 'Unknown' },
};

/**
 * `null` for `confirmed`, which suppresses the whole mark, and `null` for any state the map does not
 * hold. `defineProps` validates nothing at runtime and `config/reference.php` carries the state as a
 * plain string, so an out-of-vocabulary value is a production input: without the `?? null` it reached
 * `MARKS[state]`, and the template threw on `mark.glyph` during render, taking the whole page with it.
 * An unknown state now draws nothing, which is how `confirmed` already degrades.
 */
const mark = computed(() => (props.state === 'confirmed' ? null : (MARKS[props.state] ?? null)));

const name = computed(() => (props.title ? `${mark.value?.word}: ${props.title}` : (mark.value?.word ?? '')));

// Visible on focus as well as hover. `mouseenter`/`mouseleave` rather than CSS `group-hover`, because
// the same element has to respond to focus, and CSS cannot express "focus OR hover" on a span
// without either duplicating rules or relying on `:focus-within` that a non-focusable span never gets.
const open = ref(false);
</script>

<template>
    <span v-if="mark !== null" class="relative inline-flex align-middle">
        <span
            role="img"
            :aria-label="name"
            tabindex="0"
            class="pill pill-neutral focus:outline-2 focus:outline-offset-2"
            @mouseenter="open = true"
            @mouseleave="open = false"
            @focus="open = true"
            @blur="open = false"
        >
            <span aria-hidden="true" class="font-mono">{{ mark.glyph }}</span>
            <span>{{ mark.word }}</span>
        </span>
        <!-- The reason, when there is one. `role="tooltip"` and positioned above the mark; it stays in
             the accessible name above regardless of whether this popover is open, so opening it is a
             convenience for sighted readers and not the only route to the sentence. -->
        <span
            v-if="props.title && open"
            role="tooltip"
            class="card absolute bottom-full left-0 z-10 mb-1 w-max max-w-xs bg-raised px-2 py-1 text-xs font-normal text-ink shadow-overlay"
        >
            {{ props.title }}
        </span>
    </span>
</template>
