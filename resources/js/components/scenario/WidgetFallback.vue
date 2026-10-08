<script setup lang="ts">
/*
 * The shell's last resort for one key (plan §9 E1).
 *
 * **An unrecognised key is a state, not an error.** A scenario declares a widget this build cannot
 * draw; the honest render is a labelled notice naming what is missing, never a blank (which reads
 * as "nothing to see") and never a thrown error (which takes the whole Cockpit down for one key).
 *
 * Two kinds, because the two read differently:
 *  - a **panel** flag names its own label, which the matrix holds, so the notice can say which
 *    panel it is;
 *  - a **widget** key the matrix does not label has no name to borrow, so the notice prints the key
 *    itself. That is the case the `widgets` array exists for: a key with no label and no value is
 *    still a fact about the scenario, and printing it is better than inventing a name.
 */
import AlertRow from './AlertRow.vue';

withDefaults(
    defineProps<{
        kind: 'widget' | 'panel';
        /** The key as the matrix spells it, printed verbatim when there is no label. */
        name: string;
        /** The matrix's own label, present for a panel and for a labelled widget. */
        label?: string | null;
    }>(),
    { label: null },
);

const sentence = (kind: 'widget' | 'panel', name: string, label: string | null): string =>
    label === null
        ? `This scenario declares a ${kind} this build cannot draw: ${name}.`
        : `This scenario declares a ${kind} this build cannot draw yet: ${label}.`;
</script>

<template>
    <AlertRow
        glyph="○"
        :text="sentence(kind, name, label)"
        detail="The scenario matrix names it; no renderer for this key has landed in this build."
        tone="note"
    />
</template>
