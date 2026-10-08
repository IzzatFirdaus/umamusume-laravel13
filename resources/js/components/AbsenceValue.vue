<script setup lang="ts">
/*
 * A value the product does not have, and the reason it does not have it.
 *
 * This replaces `N/A` with a `title`: a `title` is reachable with a mouse and by nothing else, and the
 * reason is the half that tells a Trainer whether to go and enter a reading or to stop looking for one.
 * The marker is a native `<details>`, so the disclosure, the expanded state, the keyboard path and the
 * announcement come from the platform rather than from focus code written here.
 *
 * **The reason is the summary's accessible name.** A screen reader hears "N/A, A Fan count has not been
 * recorded for this run." before deciding whether to open it, and a sighted Trainer reads the same
 * sentence by opening it — one statement in two media, which is the rule `DESIGN.md` §6 already sets for
 * a glyph plus its word. Nothing here is carried by colour, and no glyph stands in for words.
 *
 * **`compact` is the dense-row form** — a table cell or a metric strip, at 24px, which is WCAG 2.2 SC
 * 2.5.8's floor. The house 44px is the bar for a primary control (`DESIGN.md` §12), and a disclosure
 * inside a data cell is not one.
 *
 * **No type is set here, on purpose.** The marker inherits the size and family of the value it stands in
 * for, so `N/A` reads at the same weight as the numbers beside it; a caller in a numeric context adds
 * `class="font-mono"`, which Vue merges onto the root element. A component that picked its own font would
 * put a sans `N/A` in a column of monospace figures, which is the mismatch this avoids.
 *
 * **The reason's wording is the caller's, and it is not a vocabulary this component invents.** The
 * product already distinguishes "not recorded" (the Trainer has not entered it) from "no source" (the
 * game or the corpus does not publish it) from a value the product refuses to compute, and each caller
 * already has that sentence. Passing it through is what keeps the distinctions from being flattened here.
 */
defineProps<{
    /** Why there is no value, in the caller's own words. Rendered verbatim. */
    reason: string;
    /** The dense-row form: 24px summary instead of the 44px house target. */
    compact?: boolean;
}>();
</script>

<template>
    <details class="inline-block align-baseline">
        <summary
            :class="[
                'inline-flex cursor-pointer items-baseline text-ink-muted',
                compact === true ? 'min-h-6' : 'min-h-11',
            ]"
        >
            N/A<span class="sr-only">, {{ reason }}</span>
        </summary>
        <span :class="['mt-1 block text-ink-muted', compact === true ? 'text-xs' : 'text-sm']">
            {{ reason }}
        </span>
    </details>
</template>
