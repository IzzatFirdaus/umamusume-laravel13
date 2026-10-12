<script setup lang="ts">
import { useVisitState } from '../composables/useVisitState';

/*
 * The visit progress indicator, KI-63's replacement. Inertia's bundled NProgress draws `role="bar"`,
 * an invalid ARIA role that fails a page-wide axe scan whenever a visit is in flight; this is a
 * native `<progress>` with an accessible name instead, whose implicit role and indeterminate state
 * axe accepts without any ARIA attribute at all. It renders nothing when no visit is in flight, so
 * a settled page carries no control.
 *
 * The element is indeterminate on purpose, and that is not a shortfall: Inertia fires progress
 * events only for FormData uploads (`@inertiajs/core`'s `onProgress`), and this app uploads no file
 * (PRD §6), so no percentage exists to show and a trickle would be invented progress. The element
 * carries no animation of its own, so the document's reduced-motion answer (`resources/css/app.css`'s
 * kill-switch, which the browser specs assert) has nothing to quiet here.
 *
 * It is mounted from `spa.ts` onto a body-level element rather than imported by the layouts: one
 * place covers all three shells, and staying outside `#app` keeps the mount element from becoming
 * `#app > *`'s first child, which would let every spec's app-mounted wait pass before the page
 * chunk resolves.
 */
const { visiting } = useVisitState();
</script>

<template>
    <progress
        v-if="visiting"
        aria-label="Page load progress"
        class="visit-progress fixed inset-x-0 top-0 z-50 h-1 w-full appearance-none border-0 bg-chrome"
    />
</template>
