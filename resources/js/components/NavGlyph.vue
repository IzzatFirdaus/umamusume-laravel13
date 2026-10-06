<script setup lang="ts">
/*
 * The navigation's marks (design-2.0 §44, drawn through the DESIGN.md §6 motif gate).
 *
 * §44 asks for marks that are "simple and consistent" and forbids emoji as the primary icon system, so
 * these are inline paths on one stroke, one weight and one grid. §6 then filters §44's suggestions: the
 * allowed motifs are stopwatches, bar charts, tactical grids, target reticles, track lines and abstract
 * geometric shapes, and the equestrian and animal forms §6 refuses are refused here too. Two of §44's
 * suggestions therefore change hands: its "DNA" for Legacy becomes the node graph (the same shape the
 * six-node ancestry already prints, and §6 separately refuses a family-tree treatment), and its "Home",
 * "Cards", "Book", "Gear", "Spark" and "Triangle" are neutral objects that clear the gate unchanged.
 *
 * **The `Veterans` trophy is the owner's choice, recorded 2026-10-06.** §44 names a trophy and §6 neither
 * lists nor forbids one, so it was put to the owner rather than decided here, and they took the brief's
 * word. `DESIGN.md` §6 is where that ruling belongs permanently; the line is flagged for the Lore
 * Guardian to land there, not asserted by this file.
 *
 * `aria-hidden` always, because a mark here never carries meaning on its own: `AppLayout.vue` keeps the
 * destination's word in an `sr-only` label even when the rail is collapsed (§42, "text alternatives for
 * icons"), and the active state is a fill plus a rule, never colour alone.
 */

/** One mark per destination, as path data on a 24 grid, stroked rather than filled. */
const MARKS: Record<string, string[]> = {
    home: ['M4 11 12 4l8 7', 'M6.6 9.6V20h10.8V9.6'],
    flag: ['M6 3.4v17.2', 'M6 4.6h11l-2.7 3.5L17 11.6H6'],
    list: ['M8 7h12', 'M8 12h12', 'M8 17h12', 'M4.4 7h.01', 'M4.4 12h.01', 'M4.4 17h.01'],
    network: ['M6 7h.01', 'M18 7h.01', 'M12 17h.01', 'M6.5 7.7 11.3 16', 'M17.5 7.7 12.7 16'],
    trophy: [
        'M8 4h8v4.2a4 4 0 0 1-8 0z',
        'M8 5H5.6a2.4 2.4 0 0 0 2.4 3.9',
        'M16 5h2.4a2.4 2.4 0 0 1-2.4 3.9',
        'M12 12.4v2.8',
        'M8.6 20h6.8',
        'M12 15.2a3 3 0 0 0-3.4 4.8h6.8a3 3 0 0 0-3.4-4.8',
    ],
    cards: ['M9.4 4.6h8v10h-8z', 'M5 8.6h9.4v10.8H5z'],
    spark: ['M12 4l2 6 6 2-6 2-2 6-2-6-6-2 6-2z'],
    triangle: ['M12 5 20.6 19H3.4z', 'M12 10.2v3.6', 'M12 16.4h.01'],
    book: [
        'M12 6.6C10 5.1 7 4.7 4.2 5.3v13C7 17.7 10 18.1 12 19.6',
        'M12 6.6c2-1.5 5-1.9 7.8-1.3v13C17 17.7 14 18.1 12 19.6',
        'M12 6.6v13',
    ],
    gear: [
        'M12 9.2a2.8 2.8 0 1 0 0 5.6 2.8 2.8 0 0 0 0-5.6z',
        'M12 3.6v2.2',
        'M12 18.2v2.2',
        'M3.6 12h2.2',
        'M18.2 12h2.2',
        'M6.1 6.1l1.6 1.6',
        'M16.3 16.3l1.6 1.6',
        'M17.9 6.1l-1.6 1.6',
        'M7.7 16.3l-1.6 1.6',
    ],
    /** The rail's own control, so the collapse affordance is drawn like the marks and not a typographic glyph. */
    chevron: ['M9.5 5.5 16 12l-6.5 6.5'],
};

const props = defineProps<{ name: keyof typeof MARKS | string }>();
</script>

<template>
    <svg
        aria-hidden="true"
        :width="20"
        :height="20"
        viewBox="0 0 24 24"
        fill="none"
        :stroke="'currentColor'"
        stroke-width="1.6"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="shrink-0"
    >
        <path v-for="(d, index) in MARKS[props.name] ?? []" :key="index" :d="d" />
    </svg>
</template>
