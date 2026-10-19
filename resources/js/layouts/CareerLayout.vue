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
import CareerSectionNav from '../components/career/CareerSectionNav.vue';
import { useLayout } from '../composables/useLayout';

const props = defineProps<{
    trainee: string;
    scenarioLabel: string;
    statusLabel: string;
    runUrl: string;
    /**
     * The remaining identity facts the section nav prints, added by slice 4.1. Optional so the eight
     * callers that predate the nav keep rendering exactly as they did: an absent `current` simply
     * marks no tab live, and an absent `status` leaves every section reachable, which is the safe
     * default because a tab wrongly disabled hides a live screen.
     */
    traineeJa?: string | null;
    status?: string;
    position?: string | null;
    current?: string;
}>();

/*
 * CareerLayout applies the same width class as every other shell, sourced from the shared
 * `useLayout` map. It wraps AppLayout and carries its own `data-layout-container` so a browser
 * gate can assert the width without depending on AppLayout's `#main` implementation.
 */
const { layoutWidthClass } = useLayout();
</script>

<template>
    <AppLayout>
        <!--
          A page that supplies its own whole title band does so through `#head`, the same either/or the
          wizard shell documents (3.8): the band renders `#head` or `#title`, never both, so exactly one
          `h1` reaches the document. `#title` stays for the pages that still fill the band themselves.
        -->
        <template v-if="$slots.head" #head><slot name="head" /></template>
        <template v-if="$slots.eyebrow && !$slots.head" #eyebrow><slot name="eyebrow" /></template>
        <template v-if="!$slots.head" #title><slot name="title" /></template>

        <div :class="[layoutWidthClass, 'mx-auto w-full']" data-layout-container>
            <!--
              The run's identity and its nine sections, from one place. This replaced the older
              "Cockpit / Dashboard" door pair (slice 4.1): the global navigation still carries the
              Dashboard, and a per-screen pair of doors is the drift the nav exists to remove.
            -->
            <CareerSectionNav
                :trainee="props.trainee"
                :trainee-ja="props.traineeJa"
                :scenario-label="props.scenarioLabel"
                :status-label="props.statusLabel"
                :status="props.status"
                :position="props.position"
                :run-url="props.runUrl"
                :current="props.current"
            />

            <slot />
        </div>
    </AppLayout>
</template>
