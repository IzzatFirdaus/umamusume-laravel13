<script setup lang="ts">
import { useId } from 'vue';

/*
 * A named region of a page (DESIGN.md §2.2's Heading tier). The `h2` is the region's own name, at
 * `text-lg font-semibold text-ink-strong`, so a page never fakes a section header out of a styled
 * `div` or skips a level: the shell owns the `h1`, this owns the `h2`, and a card inside it starts at
 * `h3`.
 *
 * The heading level is a prop because two sections in the same region (the compare tray and the
 * table beneath it) may need to sit at different depths. It defaults to `2`, the level the shell
 * leaves free.
 *
 * `mt-8` is the space *between* regions, taken by the section itself rather than by the page. That
 * way the rhythm is identical everywhere and a page that forgets a spacer cannot make one region
 * run into the next.
 */
withDefaults(
    defineProps<{
        /** The region's name, rendered as an `h2` (or the level given). */
        title: string;
        /** One optional line under the name, in Body tone. */
        description?: string;
        /** Heading level. 2 by default, the level a page's shell `h1` leaves free. */
        level?: 2 | 3 | 4;
    }>(),
    { description: undefined, level: 2 },
);

// The description is associated with the region by `aria-describedby`, so it is read as part of the
// section's name rather than as a stray paragraph a screen-reader user may skip. `useId` gives each
// instance its own id and keeps it stable across re-renders and hydration (Vue 3.5).
const descriptionId = `section-desc-${useId()}`;
</script>

<template>
    <section class="mt-8" :aria-describedby="description ? descriptionId : undefined">
        <component
            :is="`h${level}`"
            class="text-lg font-semibold text-ink-strong"
        >
            {{ title }}
        </component>
        <p v-if="description" :id="descriptionId" class="mt-1 text-sm text-ink">
            {{ description }}
        </p>
        <slot />
    </section>
</template>
