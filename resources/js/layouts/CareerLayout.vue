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
import { computed } from 'vue';
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

/*
 * The topbar crumb, `{trainee} · {scenario} · {screen}` (HFW `cockpit-*.html` `.crumb`). The shell
 * owns it because every career screen shares the first two parts, and a per-screen crumb would be
 * nine places for the scenario label to drift. The screen part is the live section key, which
 * capitalises to its own label (`cockpit` -> `Cockpit`), so no second label map can drift from the
 * stepper's.
 */
const breadcrumb = computed(() => {
    const screen = props.current ? props.current.charAt(0).toUpperCase() + props.current.slice(1) : 'Cockpit';

    /*
     * R4-01/R4-02: two careers of one trainee in one scenario had two identical headers, and the id
     * they differ by lived only in the URL. The HFW draws no run identifier anywhere — a static
     * wireframe holds one run per file, so it structurally cannot say which one — but a real Careers
     * list can hold two. It rides on the scenario part rather than as a fourth part, so the crumb
     * keeps the HFW's three-segment shape.
     *
     * DOMAIN OVERRIDE (R4-01): the run id is printed, which the wireframe has no way to draw.
     */
    // A career screen may legitimately hold no run URL (the Save Veteran screen's controller passes
    // one, but a page that predates it or a blocked state may not); an absent URL must degrade to the
    // scenario-only crumb rather than throw in the layout, which unmounts the whole Inertia root and
    // leaves a blank page. Found by the 2026-10-28 parity audit.
    const runId = (props.runUrl ?? '').match(/training-runs\/(\d+)/)?.[1];
    const scenario = runId ? `${props.scenarioLabel} #${runId}` : props.scenarioLabel;

    return [{ label: props.trainee }, { label: scenario }, { label: screen }].filter((part) => part.label.length > 0);
});

/*
 * The topbar `who` pill (HFW `.who`): the run's own state, where every other screen shows the count
 * of active careers. `Turn 67 · Active` while the run is on the 72-turn grid, `Completed` once it is
 * past it. The turn figure is omitted rather than guessed when the payload has no position.
 */
/**
 * Whether this run is still being played. The stepper's presence and the `who` pill's tone are the
 * same question, so both read it from here rather than from two interpretations of `status`.
 */
const isActiveRun = computed((): boolean => props.status === 'active' || props.statusLabel.toLowerCase() === 'active');

const whoPill = computed(() => {
    const turn = props.position?.match(/turn\s+(\d+)/i)?.[1];

    if (!isActiveRun.value) {
        return { label: props.statusLabel, tone: 'neutral' as const };
    }

    return {
        label: turn ? `Turn ${turn} · Active` : 'Active',
        tone: 'positive' as const,
    };
});
</script>

<template>
    <AppLayout :breadcrumb="breadcrumb">
        <!-- HFW `.who`: on a career screen the run's own state outranks a count of careers. -->
        <template #who>
            <span
                class="inline-flex shrink-0 items-center rounded-pill px-2.5 py-[3px] text-[11.5px] font-extrabold leading-[1.45]"
                :class="
                    whoPill.tone === 'positive'
                        ? 'bg-status-positive text-status-positive-ink'
                        : 'bg-sunken text-ink-muted'
                "
                data-shell-run-pill
            >{{ whoPill.label }}</span>
        </template>

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
              HFW-OVERRIDE (stepper-consistency): `cockpit-unity-cup.html` draws the stepper and the
              trackblazer and grand-concert pages omit it, while `cockpit-ura-finale.html` — the one
              completed run — draws none. The stepper is therefore the active-run pattern, and a
              completed career loses it: there is no next turn to navigate, and the Result screen the
              stepper's last step points at is the only door such a run still has.
            -->
            <CareerSectionNav
                v-if="isActiveRun"
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
