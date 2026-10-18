<script setup lang="ts">
/*
 * The wizard's action bar (plan 1.10).
 *
 * Two slots and no opinion about what is in them: `#left` is the way back, `#right` is the way
 * forward. Keeping the bar dumb means a step can put a "Skip" in either, and the bar still sticks.
 *
 * **The sticky element must not be wrapped in its own padding.** `position: sticky` pins an element
 * only while its CONTAINING BLOCK still extends below it. An earlier version of this component put the
 * clearance inside a wrapper div, which shrank the containing block to the bar's own height plus the
 * padding and left the bar with almost no range to stick across — it measured `position: sticky` and
 * behaved like `static`. The footer is therefore the ROOT element of the component.
 *
 * Clearance for the focused element is handled by `scroll-margin-bottom` on whatever the page renders
 * rather than by padding here: the shell already sets `scroll-padding-bottom` for the mobile nav, and a
 * bar that padded itself would be padding its own containing block. The `margin-bottom` below is
 * spacing from the content above, not clearance for it.
 */
withDefaults(
    defineProps<{
        /**
         * Space above the bar, in rem, separating it from the step's last field. Not clearance: this
         * is the gap, and it is `margin`, which does not shrink the sticky range.
         */
        gap?: number;
    }>(),
    { gap: 1.5 },
);
</script>

<template>
    <footer
        :style="{ marginTop: `${gap}rem` }"
        class="sticky bottom-0 z-10 flex flex-wrap items-center justify-between gap-3 border-t border-rule bg-page py-3"
    >
        <div class="flex items-center gap-2">
            <slot name="left">
                <button
                    type="button"
                    class="inline-flex min-h-11 items-center rounded-md border border-rule px-4 text-sm font-medium text-ink hover:text-ink-strong transition-colors duration-150 ease-out"
                >
                    Back
                </button>
            </slot>
        </div>
        <div class="flex items-center gap-2">
            <slot name="right">
                <button
                    type="button"
                    class="inline-flex min-h-11 items-center rounded-md bg-primary-action px-4 text-sm font-semibold text-on-primary-action transition-colors duration-150 ease-out"
                >
                    Continue
                </button>
            </slot>
        </div>
    </footer>
</template>
