<script lang="ts">
/**
 * The four provenance states (design-2.0 §49, verbatim).
 *
 * Exported from the plain script block so other modules can type a `ref<ProvenanceState>()` or a
 * helper signature with it. One caveat, stated once here so no later slice re-learns it the hard way:
 * a consuming SFC **cannot** put this imported type inside its own `defineProps<>` — `@vue/compiler-sfc`
 * resolves prop types with no filesystem access, so an imported type fails the build ("No fs option
 * provided to compileScript in non-Node environment"). Pages declare their props contract locally;
 * this export is for everything around props.
 */
export type ProvenanceState = 'confirmed' | 'calculated' | 'estimated' | 'unknown';
</script>

<script setup lang="ts">
/*
 * ProvenanceBadge (design-2.0 §49; ADR-0020 §2).
 *
 * **This file is the only owner of the four provenance glyphs.** The map below holds the glyph and the
 * word of each state, and no other source in the repository may repeat either as a rendered marker.
 * The states are §49's own set — Confirmed, Calculated, Estimated, Unknown — and §20's HIGH / MEDIUM /
 * LOW vocabulary is deliberately not merged in: numeric confidence is dropped entirely, so nothing
 * here ever prints a percentage.
 *
 * **The placement rule (Law of Proximity, design-2.0 §13):** the badge sits immediately beside the
 * figure it labels, never in a page-level legend. A figure with no badge is a bug, and `Unknown` is
 * the state for "there is no number", not for "we forgot".
 *
 * **The accessible name is the word, not the glyph.** The glyph span is `aria-hidden="true"` and the
 * component carries `role="img"` with an `aria-label` set to the state word (with the caller's
 * `title` appended when one was given), so a screen reader hears one statement rather than two. Trap
 * for the component's own tests: Playwright's `toHaveText` reads `textContent` *including*
 * `aria-hidden` text, so a spec asserts the name via `getByRole('img', { name })` and never
 * `toHaveText` on this wrapper — `tests/browser/legacy.spec.ts` is red for exactly that reason.
 *
 * Contrast comes from the measured token pairs (`text-ink-strong` on `bg-sunken`), never a raw hex,
 * and each state carries a distinct glyph AND a distinct word, so the state is never colour-only.
 *
 * `copy` and `name` are `computed`, not plain consts. Inertia patches a page in place, so a component
 * whose props change underneath it keeps its instance: a value derived once at setup stays frozen at
 * the first render's state. Nothing renders this badge with a changing `state` today, but a screen
 * that does (the Career Timeline is the likely first) would otherwise print the previous state's word
 * beside the new figure. The Career Cockpit hit exactly that with its advisor band. The in-place case
 * is pinned by `tests/browser/provenance-badge.spec.ts`; reverting these two lines to consts fails it.
 */
import { computed } from 'vue';

const props = defineProps<{
    state: 'confirmed' | 'calculated' | 'estimated' | 'unknown';
    /** Optional reason or arithmetic note; appended to the accessible name and shown as a tooltip. */
    title?: string;
}>();

const BADGES: Record<'confirmed' | 'calculated' | 'estimated' | 'unknown', { glyph: string; word: string }> = {
    confirmed: { glyph: '✓', word: 'Confirmed' },
    calculated: { glyph: '∑', word: 'Calculated' },
    estimated: { glyph: '~', word: 'Estimated' },
    unknown: { glyph: '?', word: 'Unknown' },
};

const copy = computed(() => BADGES[props.state]);

const name = computed(() => (props.title ? `${copy.value.word}: ${props.title}` : copy.value.word));
</script>

<template>
    <span
        role="img"
        :aria-label="name"
        :title="props.title"
        class="inline-flex items-center gap-1 rounded-full bg-sunken px-2 py-0.5 text-xs font-semibold text-ink-strong"
    >
        <span aria-hidden="true" class="font-mono">{{ copy.glyph }}</span>
        <span>{{ copy.word }}</span>
    </span>
</template>
