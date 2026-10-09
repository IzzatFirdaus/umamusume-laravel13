<script setup lang="ts">
/*
 * The career-scoped shell (plan §8: `CareerLayout` for D8 to D13). It is `AppLayout` plus the
 * career bar, not a second shell: the global destinations stay reachable, because the Cockpit is a
 * top-level career screen rather than a modal the Trainer cannot leave, and re-declaring the nav
 * here would be a second place for the same nine entries to drift.
 *
 * The career bar carries the one fact every career screen shares — which run this is — and the two
 * doors a Trainer wants from it: back to the Cockpit, and back to the Dashboard. D9 to D13 render
 * inside this layout and inherit both. The Cockpit door takes the server's own `run_url`, which is
 * `route('runs.cockpit', $run)` on every career screen, so the label and the destination agree.
 *
 * `#title` is forwarded to `AppLayout`'s own heading rather than rendered here, so the page keeps one
 * `h1` and the document outline stays one document wide.
 */
import AppLayout from './AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    trainee: string;
    scenarioLabel: string;
    statusLabel: string;
    runUrl: string;
}>();

const doorClass =
    'inline-flex min-h-11 items-center rounded-md px-3 text-sm font-medium text-ink hover:bg-raised hover:text-ink-strong';
</script>

<template>
    <AppLayout>
        <template #title><slot name="title" /></template>

        <section
            aria-label="Career"
            class="mb-4 flex flex-wrap items-center justify-between gap-x-4 gap-y-2 rounded-md border border-rule bg-panel px-4 py-2"
        >
            <p class="text-sm text-ink">
                <span class="font-semibold text-ink-strong">{{ props.trainee }}</span>
                <span aria-hidden="true"> · </span>
                <span>{{ props.scenarioLabel }}</span>
                <span aria-hidden="true"> · </span>
                <span class="text-ink-muted">{{ props.statusLabel }}</span>
            </p>
            <div class="flex flex-wrap gap-1">
                <Link :href="props.runUrl" :class="doorClass">Cockpit</Link>
                <Link href="/" :class="doorClass">Dashboard</Link>
            </div>
        </section>

        <slot />
    </AppLayout>
</template>
