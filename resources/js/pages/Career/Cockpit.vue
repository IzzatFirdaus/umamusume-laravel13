<script setup lang="ts">
/*
 * The Career Cockpit, `SCR-CAR-011` (SCREEN-009, plan §8 D8). The screen a Trainer reads on every turn.
 *
 * **Layout.** Desktop is the spec's three columns — timeline 20%, current decision 50%, advisor 30% —
 * with the scenario region full width beneath them (SCREEN-009 §12, design-2.0 §23). Tablet is two
 * columns with the timeline as a horizontal strip; mobile is one column in the spec's order: header,
 * current state, recommendation, action, scenario, timeline (SCREEN-009 §33, design-2.0 §40). The
 * order is CSS only — no element is hidden at a breakpoint, so nothing leaves the accessibility tree
 * and no primary decision needs a horizontal scroll.
 *
 * **The page pattern is `Catalog/Index.vue`'s**: the layout, `<Head title>` and the `#title` slot. The
 * calendar's year tabs left the page with D14a, so the only query-string state here is none at all. There is one form,
 * the manual correction, and it posts to the route that already owns a turn write.
 *
 * **Loading and error are page-level** (ADR-0007): the only user-initiated async action here is the
 * correction, and Inertia keeps the current page on screen during a visit, so the state is announced
 * rather than drawn as a skeleton. `invalid` and `exception` are both cancelable, and returning
 * `false` takes the failure out of Inertia's default modal so this page's own `role="alert"` is the
 * single surface.
 *
 * The props contract is declared locally: `defineProps<Imported>()` cannot resolve an imported type,
 * because TypeScript 7 ships no `lib/typescript.js` for `@vue/compiler-sfc` to load (plan §11). Every
 * page declares its own contract for the same reason.
 */
import CareerLayout from '../../layouts/CareerLayout.vue';
import CareerHeader from '../../components/career/CareerHeader.vue';
import CareerStatePanel from '../../components/career/CareerStatePanel.vue';
import ActionGrid from '../../components/career/ActionGrid.vue';
import AdvisorRail from '../../components/career/AdvisorRail.vue';
import RunRaceStrip from '../../components/career/RunRaceStrip.vue';
import ScenarioPanel from '../../components/scenario/ScenarioPanel.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';
import { useVisitState } from '../../composables/useVisitState';

interface Header {
    scenario_label: string;
    scenario_declared: boolean;
    year_label: string | null;
    month_label: string | null;
    turn: number;
    energy: number | null;
    mood: string | null;
    fans: number | null;
    skill_points: number | null;
    widgets: string[];
    values: Record<string, number | string | null>;
}

interface Correction {
    action: string;
    turn: number;
    values: {
        speed: number;
        stamina: number;
        power: number;
        guts: number;
        wit: number;
        sp: number | null;
        energy: number | null;
        fans: number | null;
        mood: string | null;
    };
}

const props = defineProps<{
    run: { id: number; trainee: string; trainee_ja: string | null; status: string; status_label: string; scenario_label: string; run_url: string; timeline_url: string; result_url: string };
    header: Header;
    state: {
        stats: { key: string; label: string; current: number | null; target: number | null; cap: number }[];
        meta: { key: string; label: string; value: number | string | null }[];
    };
    actions: { key: string; label: string; href: string; recommended: boolean; note: string }[];
    advisor: {
        action: string | null;
        band: 'AtOrAboveAdvisory' | 'BelowAdvisory' | null;
        reasons: string[];
        alternative: string | null;
        risk: string | null;
    };
    // The strip's own shape is declared at `components/career/RunRaceStrip.vue`, the one place that
    // reads it. The page forwards the bundle untouched, the way it forwarded the calendar cells.
    raceStrip: Record<string, unknown>;
    // The scenario panel's shape is declared at `components/scenario/ScenarioPanel.vue` for the same
    // reason: the shell is its one reader, and E2 to E4 register renderers against it.
    scenario: Record<string, unknown>;
    correction: Correction | null;
}>();

const { visiting, visitFailed } = useVisitState();

const empty = computed(() => props.header.turn === 0);

/*
 * The manual correction (design-2.0 §38). One disclosure holding every field of the latest turn, put
 * to the route and Form Request that already own a turn write; no second endpoint is created and no
 * field the request does not accept is offered. The five stats are required by that request, so they
 * are prefilled from the stored row and marked required here rather than silently defaulted.
 */
const correctionForm = useForm({
    turn: props.correction === null ? '' : String(props.correction.turn),
    speed: props.correction === null ? '' : String(props.correction.values.speed),
    stamina: props.correction === null ? '' : String(props.correction.values.stamina),
    power: props.correction === null ? '' : String(props.correction.values.power),
    guts: props.correction === null ? '' : String(props.correction.values.guts),
    wit: props.correction === null ? '' : String(props.correction.values.wit),
    sp: props.correction?.values.sp === null || props.correction === null ? '' : String(props.correction.values.sp),
    energy: props.correction?.values.energy === null || props.correction === null ? '' : String(props.correction.values.energy),
    fans: props.correction?.values.fans === null || props.correction === null ? '' : String(props.correction.values.fans),
    mood: props.correction?.values.mood ?? '',
});

const alert = ref<HTMLElement | null>(null);

// A refused write comes back with the field's message. The alert is focused rather than left for the
// Trainer to find, because the refusal is the whole answer to the press (WCAG 2.2 SC 3.3.1).
watch(
    () => correctionForm.hasErrors,
    async (hasErrors: boolean) => {
        if (!hasErrors) {
            return;
        }

        await nextTick();
        alert.value?.focus();
    },
);

const saveCorrection = (): void => {
    if (props.correction === null) {
        return;
    }

    correctionForm.put(props.correction.action, { preserveScroll: true });
};

const statFields = [
    { name: 'speed', label: 'Speed' },
    { name: 'stamina', label: 'Stamina' },
    { name: 'power', label: 'Power' },
    { name: 'guts', label: 'Guts' },
    { name: 'wit', label: 'Wit' },
] as const;

// E4: the Cockpit renders a "Shop" jump only when the matrix composes one. The shape is the
// same across scenarios (the key is always present), so a `null` check carries the whole
// branch. Other scenarios see no Shop affordance.
const hasShop = computed((): boolean => {
    const shop = (props.scenario as { shop?: unknown }).shop;

    return shop !== null && shop !== undefined;
});

/*
 * Scroll to and focus the Shop heading (§28 "quick access from the Cockpit"). We do not rely
 * on `:focus-visible` scroll-margin because the heading carries `tabindex="-1"` and would
 * otherwise miss the visible scroll target.
 */
const jumpToShop = (): void => {
    const heading = document.getElementById('trackblazer-shop-heading');

    if (heading === null) {
        return;
    }

    heading.scrollIntoView({ behavior: 'smooth', block: 'start' });
    heading.focus({ preventScroll: true });
};
</script>

<template>
    <CareerLayout
        :trainee="props.run.trainee"
        :scenario-label="props.run.scenario_label"
        :status-label="props.run.status_label"
        :run-url="props.run.run_url"
    >
        <Head :title="`Career: ${props.run.trainee}`" />
        <template #title>{{ props.run.trainee }}</template>

        <p v-if="visiting" role="status" class="mb-4 text-sm text-ink-muted">Loading…</p>
        <p v-if="visitFailed" role="alert" class="mb-4 rounded border border-risk bg-raised px-3 py-2 text-sm text-risk">
            That screen did not load. Nothing was saved; try again.
        </p>

        <CareerHeader :trainee="props.run.trainee" :trainee-ja="props.run.trainee_ja" :header="props.header" />

        <!-- The spec's order, as CSS only. `order-*` drives the single mobile column; from `md` up the
             explicit row and column placement takes over, so the tablet strip and the desktop
             three-column cockpit are the same DOM. -->
        <div class="mt-4 grid grid-cols-1 gap-4 md:items-start md:grid-cols-[2fr_1fr] lg:items-start lg:grid-cols-[1fr_2.5fr_1.5fr]">
            <div class="order-1 min-w-0 md:order-none md:col-start-1 md:row-start-2 lg:col-start-2 lg:row-start-1">
                <CareerStatePanel :stats="props.state.stats" :meta="props.state.meta" />

                <section v-if="props.correction !== null" aria-labelledby="career-correction-heading" class="mt-3">
                    <h3 id="career-correction-heading" class="sr-only">Correct the latest turn by hand</h3>

                    <details class="rounded-md border border-rule bg-panel p-4">
                        <summary class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-ink-strong">
                            Correct turn {{ props.correction.turn }} by hand
                        </summary>
                        <p class="mt-2 text-sm text-ink-muted">
                            Every field of the turn the app is reading. It writes on submit and shows no
                            preview, so use it to fix a mistyped reading rather than to log a new turn.
                        </p>

                        <div
                            v-if="correctionForm.hasErrors"
                            ref="alert"
                            role="alert"
                            tabindex="-1"
                            class="mt-3 rounded-md border border-risk bg-raised p-3 text-sm text-risk"
                        >
                            <p class="font-semibold">The correction was not saved.</p>
                            <ul class="mt-1 list-disc pl-5">
                                <li v-for="(message, key) in correctionForm.errors" :key="key">{{ message }}</li>
                            </ul>
                        </div>

                        <form class="mt-3 grid grid-cols-2 gap-3 text-sm md:grid-cols-3" @submit.prevent="saveCorrection">
                            <label class="flex flex-col gap-1">
                                <span class="text-ink-muted">Turn *</span>
                                <input v-model="correctionForm.turn" type="number" name="turn" min="1" required class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                            </label>
                            <label v-for="field in statFields" :key="field.name" class="flex flex-col gap-1">
                                <span class="text-ink-muted">{{ field.label }} *</span>
                                <input v-model="correctionForm[field.name]" type="number" :name="field.name" min="0" required class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                            </label>
                            <label class="flex flex-col gap-1">
                                <span class="text-ink-muted">Skill Points</span>
                                <input v-model="correctionForm.sp" type="number" name="sp" min="0" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                            </label>
                            <label class="flex flex-col gap-1">
                                <span class="text-ink-muted">Energy</span>
                                <input v-model="correctionForm.energy" type="number" name="energy" min="0" max="100" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                            </label>
                            <label class="flex flex-col gap-1">
                                <span class="text-ink-muted">Fans</span>
                                <input v-model="correctionForm.fans" type="number" name="fans" min="0" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                            </label>
                            <label class="flex flex-col gap-1">
                                <span class="text-ink-muted">Mood</span>
                                <select v-model="correctionForm.mood" name="mood" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                                    <option value="">not recorded</option>
                                    <option value="GREAT">GREAT</option>
                                    <option value="GOOD">GOOD</option>
                                    <option value="NORMAL">NORMAL</option>
                                    <option value="BAD">BAD</option>
                                    <option value="AWFUL">AWFUL</option>
                                </select>
                            </label>
                            <button
                                type="submit"
                                class="enamel inline-flex min-h-11 items-center justify-center rounded-full bg-chrome px-4 font-semibold text-on-chrome disabled:opacity-60 md:col-span-3"
                                :disabled="correctionForm.processing"
                            >
                                {{ correctionForm.processing ? 'Saving…' : 'Save correction' }}
                            </button>
                        </form>
                    </details>
                </section>
            </div>

            <div class="order-2 min-w-0 md:order-none md:col-start-2 md:row-span-2 md:row-start-2 lg:col-start-3 lg:row-start-1">
                <AdvisorRail
                    :action="props.advisor.action"
                    :band="props.advisor.band"
                    :reasons="props.advisor.reasons"
                    :alternative="props.advisor.alternative"
                    :risk="props.advisor.risk"
                />
            </div>

            <div class="order-3 min-w-0 md:order-none md:col-start-1 md:row-start-3 lg:col-start-2 lg:row-start-2">
                <!-- The empty state names what is missing, why it matters and what to do (plan §10). It
                     sits above the grid rather than replacing it: the grid's own entries are still the
                     doors, and the advisor's refusal is a real state rather than a blank. -->
                <section
                    v-if="empty"
                    aria-labelledby="career-empty-heading"
                    class="mb-3 rounded-md border border-dashed border-rule bg-raised p-4"
                >
                    <h2 id="career-empty-heading" class="text-base font-semibold text-ink-strong">No turn recorded yet</h2>
                    <p class="mt-1 text-sm text-ink">
                        Energy, Mood, Fans, Skill Points and the five stats are what this screen reads, and
                        none has been entered for this run. Until one turn is recorded the advisor has
                        nothing to rank, so it declines rather than guessing.
                    </p>
                    <a :href="props.run.run_url" class="mt-2 inline-flex min-h-11 items-center font-medium text-ink-strong underline">
                        Record the first turn on the run screen
                    </a>
                </section>

                <ActionGrid :actions="props.actions" />

                <!-- Trackblazer's Pro Shop is reachable from the Cockpit's action area (plan §9
                     E4, design-2.0 §28 "The Shop should be quickly accessible from the Career
                     Cockpit"). The button is rendered only when the matrix composes a shop, so
                     other scenarios see no Shop affordance. WCAG 2.4.11: focus moves to the
                     Shop heading after the visit, and the body has `scroll-margin-top` so the
                     sticky mobile nav does not cover the focused control. -->
                <button
                    v-if="hasShop"
                    type="button"
                    class="mt-2 inline-flex min-h-11 items-center justify-center rounded-full border-2 border-rule px-4 font-semibold text-ink-strong"
                    @click="jumpToShop"
                >
                    Shop
                </button>
            </div>

            <section
                class="order-4 min-w-0 rounded-md border border-rule bg-panel p-4 md:order-none md:col-span-2 md:row-start-4 lg:col-span-3 lg:row-start-3"
                aria-labelledby="career-scenario-heading"
            >
                <h2 id="career-scenario-heading" class="text-base font-semibold text-ink-strong">Scenario</h2>

                <!-- The shell switches on the matrix's own flags and widget keys and knows no
                     scenario name (plan §9 E1, gate G-33). The prop's own shape is declared at
                     `components/scenario/ScenarioPanel.vue`; the page forwards it untouched. -->
                <div class="mt-2">
                    <ScenarioPanel :scenario="props.scenario as never" />
                </div>
            </section>

            <section
                class="order-5 min-w-0 rounded-md border border-rule bg-panel p-4 md:order-none md:col-span-2 md:row-start-1 lg:col-span-1 lg:col-start-1 lg:row-span-2 lg:row-start-1"
                aria-labelledby="career-timeline-heading"
            >
                <h2 id="career-timeline-heading" class="text-base font-semibold text-ink-strong">
                    Races in this run
                </h2>

                <RunRaceStrip :strip="props.raceStrip as never" />

                <p class="mt-3 text-xs text-ink-muted">
                    This region carries the run's own races. Every logged turn, race and event, in order,
                    is on the Career Timeline.
                </p>

                <a
                    :href="props.run.timeline_url"
                    class="mt-2 inline-flex min-h-11 items-center text-xs font-medium text-ink-strong underline"
                >
                    Career Timeline
                </a>

                <!-- The Career Result's only other door was the 0.1.0 record page's header, so it sits
                     beside the timeline door here rather than in the action grid: the grid is the
                     advisor's answer about the next turn, and this is a summary of the career. -->
                <a
                    :href="props.run.result_url"
                    class="mt-1 inline-flex min-h-11 items-center text-xs font-medium text-ink-strong underline"
                >
                    Career Result
                </a>
            </section>
        </div>
    </CareerLayout>
</template>
