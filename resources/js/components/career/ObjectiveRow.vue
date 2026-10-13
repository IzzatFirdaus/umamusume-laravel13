<script setup lang="ts">
/*
 * FD-3, one objective row (plan §9a, continuation prompt §4.1-§4.2).
 *
 * **State is a word and a mark, never a colour** (WCAG 1.4.1). The four marks below are the plan's own
 * vocabulary. `unknown` shares `○` with `upcoming` and is told apart by its word, because `?` belongs to
 * `ProvenanceBadge.vue`, which is the repository's only owner of the four provenance glyphs and a state
 * mark must not impersonate a provenance mark.
 *
 * **Nothing here dates what the calendar does not date.** Where `due_turn` is null the row prints `N/A`
 * with the row's own `title` as the reason (`AGENTS.md` §5), rather than a dash or a guessed turn, and the
 * rail never ranks it against a turn it does not have.
 *
 * Props are read through `computed`, never destructured: Inertia patches the page in place, so this row
 * keeps its instance across a visit with new props underneath it.
 *
 * The eight fields are FD-3's contract verbatim. There is no score, no confidence, no band and no
 * consequence unless a source supplies one, and no source does today, so `consequence` renders only when
 * it is non-null.
 */
import { computed } from 'vue';

import ProvenanceBadge from '../ProvenanceBadge.vue';
import type { ProvenanceState } from '../../types';

type Badge = ProvenanceState;

const props = defineProps<{
    label: string;
    state: 'upcoming' | 'current' | 'completed' | 'unknown';
    due_turn: number | null;
    due_label: string | null;
    consequence: string | null;
    source: string;
    badge: Badge;
    title: string;
    /** The screen that can settle this obligation. Null when the page has no such door: no dead link. */
    url: string | null;
}>();

const STATES: Record<'upcoming' | 'current' | 'completed' | 'unknown', { glyph: string; word: string }> = {
    completed: { glyph: '●', word: 'Met' },
    current: { glyph: '◉', word: 'Due' },
    upcoming: { glyph: '○', word: 'Ahead' },
    unknown: { glyph: '○', word: 'Undated' },
};

const state = computed(() => STATES[props.state]);
</script>

<template>
    <li class="flex flex-wrap items-baseline gap-x-2 gap-y-1 text-sm">
        <span class="shrink-0 font-mono text-ink-strong" aria-hidden="true">{{ state.glyph }}</span>
        <span class="font-semibold text-ink-strong">{{ state.word }}</span>
        <span class="min-w-0 flex-1 break-words text-ink">{{ props.label }}</span>

        <span class="text-xs text-ink-muted">
            <template v-if="props.due_label !== null">{{ props.due_label }}</template>
            <template v-else><span title="The calendar records this slot with no turn, so the tool cannot date it.">N/A</span></template>
        </span>

        <ProvenanceBadge :state="props.badge" :title="props.title" />

        <p v-if="props.consequence !== null" class="w-full text-xs text-ink-muted">
            {{ props.consequence }}
        </p>

        <a
            v-if="props.url !== null"
            :href="props.url"
            class="inline-flex min-h-11 items-center font-medium text-ink-strong underline"
        >
            Settle it at Race Decision
        </a>
    </li>
</template>
