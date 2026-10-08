<script setup lang="ts">
/*
 * Whether the guide behind a scenario is complete (the brief's PARTIALLY DOCUMENTED marker).
 *
 * **Absence means documented.** The badge fires on an explicit `documented => false`, and no Global
 * scenario declares one, so this build only ever draws the documented state. That is stated here
 * because it is a real limitation of the screen, not a finished feature: the brief's correction
 * table marks Our Grand Concert PARTIALLY DOCUMENTED, and the matrix's `documented` key flipped to
 * `true` on 2026-10-05 when the mechanics read landed, so it records provenance rather than the
 * badge's meaning. A dedicated `partially_documented` switch would fix that; it is not committed,
 * and this component will not read a key that exists only in a working tree. Plan §4.1 item 6
 * carries the hand-off.
 *
 * Glyph plus word, never colour alone. The two glyphs are ones this codebase already prints for the
 * same kinds of claim: `●` for a recorded, ordinary state (the Career Timeline's completed rows) and
 * `⚠` for a state the reader should stop on (`EventCard`'s incomplete outcome).
 */
const props = defineProps<{
    /** The matrix's `documented` key, resolved to a boolean by the controller. */
    documented: boolean;
}>();

const label = (documented: boolean): string => (documented ? 'Documented' : 'Partially documented');

const glyph = (documented: boolean): string => (documented ? '●' : '⚠');

const detail = (documented: boolean): string =>
    documented
        ? 'A guide for this scenario is held in this build, so its panels declare what they render.'
        : 'Part of this scenario is not documented in this build, so its panels render only what a source states.';
</script>

<template>
    <span
        class="inline-flex items-baseline gap-1.5 rounded-full px-2 py-0.5 text-xs font-semibold"
        :class="props.documented ? 'bg-panel text-ink-muted' : 'bg-warning text-on-warning'"
    >
        <span aria-hidden="true" class="font-mono">{{ glyph(props.documented) }}</span>
        <span :title="detail(props.documented)">{{ label(props.documented) }}</span>
    </span>
</template>
