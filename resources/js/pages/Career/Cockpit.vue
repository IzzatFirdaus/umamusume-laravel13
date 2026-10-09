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
import { Head, router, useForm } from '@inertiajs/vue3';
import { TURN_ENTRY_STAT_FIELDS as statFields } from '../../domain/turnEntryFields';
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

export interface GradeObjective {
    index: number;
    name: string;
    required: number;
}

interface Correction {
    action: string;
    destroy_action: string;
    turn: number;
    selected_turn_id: number;
    turns: { id: number; turn: number }[];
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
    run: { id: number; trainee: string; trainee_ja: string | null; umamusume_id: number; scenario: string | null; status: string; status_label: string; scenario_label: string; run_url: string; timeline_url: string; result_url: string; training_url: string; status_labels: Record<string, string>; scenarios: Record<string, string>; update_url: string; destroy_url: string; export_csv_url: string; export_json_url: string; current_objective_index: number | null };
    header: Header;
    /**
     * Where the career stands, as `CareerPosition::toArray()` carries it. The header's Year, Month and
     * Turn are resolved from it server-side when `hasImportedPosition` is true, because the month and
     * year spellings already live in `CareerCalendar` and `CareerYear::label()`; spelling them again
     * here would be a second place for the calendar to be wrong (ADR-0015).
     */
    careerPosition: {
        year: number;
        month: number;
        phase: string;
        turn_index: number;
        scenario_countdown: number | null;
        field_states: Record<string, string> | null;
        field_values: Record<string, number> | null;
    } | null;
    hasImportedPosition: boolean;
    scenarioCountdown: number | null;
    gradeObjectives: GradeObjective[];
    /*
     * The run's own identity, as the Trainer entered it (A6.1 to A6.3). Each field is null until a
     * writer records it, so a block with nothing to say renders the absence rather than a zero. The
     * fan ladder is read server-side through `FanLadder`, so the class and the gap are one band's
     * answer; `growth_rate` is keyed by the stat matrix, the same keys `state.stats` carries.
     */
    identity: {
        trainee_rarity: number | null;
        potential_level: number | null;
        card_title: string | null;
        growth_rate: Record<string, number | null> | null;
        stat_order: string[];
        fans: number | null;
        fan_ladder: { class: string | null; nextThreshold: number | null; gap: number | null } | null;
    };
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
        finale: { label: string; state: string; turns_away: number | null } | null;
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

const turnSelector = ref<HTMLSelectElement | null>(null);

/*
 * F2, plan §9.6 ruling 2: arbitrary-turn correction. `?edit_turn=<id>` names the turn the form opens
 * on; the selector navigates that query string with `preserveState` so the disclosure stays open, and
 * the watch below re-syncs the form to the turn the server returned.
 */
function switchTurn(id: string): void {
    router.get(window.location.pathname, { edit_turn: id }, { preserveState: true, preserveScroll: true });
}

watch(
    () => props.correction,
    (correction) => {
        if (correction === null) {
            return;
        }

        correctionForm.turn = String(correction.turn);
        correctionForm.speed = String(correction.values.speed);
        correctionForm.stamina = String(correction.values.stamina);
        correctionForm.power = String(correction.values.power);
        correctionForm.guts = String(correction.values.guts);
        correctionForm.wit = String(correction.values.wit);
        correctionForm.sp = correction.values.sp === null ? '' : String(correction.values.sp);
        correctionForm.energy = correction.values.energy === null ? '' : String(correction.values.energy);
        correctionForm.fans = correction.values.fans === null ? '' : String(correction.values.fans);
        correctionForm.mood = correction.values.mood ?? '';
        correctionForm.clearErrors();
    },
);

const saveCorrection = (): void => {
    if (props.correction === null) {
        return;
    }

    correctionForm.put(props.correction.action, {
        preserveScroll: true,
        onSuccess: () => {
            nextTick(() => turnSelector.value?.focus());
        },
    });
};

// Delete one turn and its failure event (the controller's own transaction). A separate form because
// the two writes are separate routes; the disclosure gates it the way the run-delete door does.
const turnDeleteForm = useForm({});

const deleteTurn = (): void => {
    if (props.correction === null) {
        return;
    }

    turnDeleteForm.delete(props.correction.destroy_action, {
        preserveScroll: true,
        onSuccess: () => {
            nextTick(() => turnSelector.value?.focus());
        },
    });
};

/*
 * The header edit forms (F2, plan §9.6 ruling 1). All three POST through `runs.update`, which shares
 * StoreTrainingRunRequest with create and so writes every field it is handed (the request fills the
 * columns it does not receive). Each form therefore carries the run's current scenario, status and
 * objective-index as hidden fields so a partial edit — status only, scenario only, period only —
 * cannot blank the sibling fields the form did not touch.
 *
 * The status form re-posts umamusume_id because the request treats it as required on store; a run
 * that already exists never changes its trainee here, so carrying the stored id is the honest value
 * rather than inventing a default.
 */
const statusForm = useForm({
    umamusume_id: String(props.run.umamusume_id ?? ''),
    scenario: props.run.scenario ?? '',
    current_objective_index: props.run.current_objective_index === null ? '' : String(props.run.current_objective_index),
    status: props.run.status,
});

const scenarioForm = useForm({
    umamusume_id: String(props.run.umamusume_id ?? ''),
    status: props.run.status,
    current_objective_index: props.run.current_objective_index === null ? '' : String(props.run.current_objective_index),
    scenario: props.run.scenario ?? '',
});

const periodForm = useForm({
    umamusume_id: String(props.run.umamusume_id ?? ''),
    status: props.run.status,
    scenario: props.run.scenario ?? '',
    current_objective_index: props.run.current_objective_index === null ? '' : String(props.run.current_objective_index),
});

const saveStatus = (): void => {
    statusForm.put(props.run.update_url, { preserveScroll: true });
};

const saveScenario = (): void => {
    scenarioForm.put(props.run.update_url, { preserveScroll: true });
};

const savePeriod = (): void => {
    periodForm.put(props.run.update_url, { preserveScroll: true });
};

const deleteForm = useForm({});

const deleteRun = (): void => {
    if (props.run.destroy_url === '') {
        return;
    }

    deleteForm.delete(props.run.destroy_url);
};

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

        <CareerHeader
            :trainee="props.run.trainee"
            :trainee-ja="props.run.trainee_ja"
            :header="props.header"
            :countdown="props.scenarioCountdown"
            :card-title="props.identity.card_title"
            :trainee-rarity="props.identity.trainee_rarity"
            :potential-level="props.identity.potential_level"
        />

        <!-- The header edit forms (F2, plan §9.6 ruling 1). Status, scenario and grade-point period
             all write through `runs.update`, the route and validator the 0.1.0 record screen used. A
             failure comes back with `page.props.errors`, which the page-level alert names. Each form
             carries the sibling fields the request still writes, so editing one cannot blank another. -->
        <section aria-labelledby="cockpit-header-edit" class="mt-2">
            <h3 id="cockpit-header-edit" class="sr-only">Edit the run's identity</h3>
            <ul class="flex flex-wrap items-end gap-4 text-sm">
                <li class="flex flex-col gap-1">
                    <label for="status" class="text-ink-muted">Status</label>
                    <form @submit.prevent="saveStatus">
                        <select
                            id="status"
                            v-model="statusForm.status"
                            name="status"
                            class="min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-ink"
                        >
                            <option v-for="(label, value) in props.run.status_labels" :key="value" :value="value">{{ label }}</option>
                        </select>
                        <button
                            type="submit"
                            class="mt-1 inline-flex min-h-11 items-center rounded-full border-2 border-rule px-4 font-bold text-ink-strong disabled:opacity-60"
                            :disabled="statusForm.processing"
                        >
                            {{ statusForm.processing ? 'Saving…' : 'Change status' }}
                        </button>
                        <p v-if="statusForm.recentlySuccessful" class="mt-1 text-xs text-ink-muted">Saved.</p>
                    </form>
                </li>

                <li class="flex flex-col gap-1">
                    <label for="scenario" class="text-ink-muted">Scenario</label>
                    <form @submit.prevent="saveScenario">
                        <select
                            id="scenario"
                            v-model="scenarioForm.scenario"
                            name="scenario"
                            class="min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-ink"
                        >
                            <option value="">No scenario set</option>
                            <option v-for="(label, key) in props.run.scenarios" :key="key" :value="key">{{ label }}</option>
                        </select>
                        <button
                            type="submit"
                            class="mt-1 inline-flex min-h-11 items-center rounded-full border-2 border-rule px-4 font-bold text-ink-strong disabled:opacity-60"
                            :disabled="scenarioForm.processing"
                        >
                            {{ scenarioForm.processing ? 'Saving…' : 'Change scenario' }}
                        </button>
                    </form>
                </li>

                <li
                    v-if="props.gradeObjectives.length > 0"
                    class="flex flex-col gap-1"
                >
                    <label for="current_objective_index" class="text-ink-muted">Grade Point period</label>
                    <form @submit.prevent="savePeriod">
                        <select
                            id="current_objective_index"
                            v-model="periodForm.current_objective_index"
                            name="current_objective_index"
                            class="min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-ink"
                        >
                            <option value="">Not reported</option>
                            <option
                                v-for="objective in props.gradeObjectives"
                                :key="objective.index"
                                :value="String(objective.index)"
                            >
                                {{ objective.index }}. {{ objective.name }}
                            </option>
                        </select>
                        <button
                            type="submit"
                            class="mt-1 inline-flex min-h-11 items-center rounded-full border-2 border-rule px-4 font-bold text-ink-strong disabled:opacity-60"
                            :disabled="periodForm.processing"
                        >
                            {{ periodForm.processing ? 'Saving…' : 'Report period' }}
                        </button>
                    </form>
                </li>

                <li class="flex flex-col gap-1">
                    <span class="text-ink-muted">Export</span>
                    <!-- The export door (F2, plan §9.6). Both are GET file responses, so they are
                         plain links: routing a download through a redirect would turn it into an
                         HTML navigation instead of a file. -->
                    <span class="flex flex-wrap gap-2">
                        <a
                            :href="props.run.export_csv_url"
                            class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-semibold text-ink-strong underline"
                        >
                            Download CSV
                        </a>
                        <a
                            :href="props.run.export_json_url"
                            class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-semibold text-ink-strong underline"
                        >
                            Download JSON
                        </a>
                    </span>
                </li>
            </ul>
        </section>

        <!-- The spec's order, as CSS only. `order-*` drives the single mobile column; from `md` up the
             explicit row and column placement takes over, so the tablet strip and the desktop
             three-column cockpit are the same DOM. -->
        <div class="mt-4 grid grid-cols-1 gap-4 md:items-start md:grid-cols-[2fr_1fr] lg:items-start lg:grid-cols-[1fr_2.5fr_1.5fr]">
            <div class="order-1 min-w-0 md:order-none md:col-start-1 md:row-start-2 lg:col-start-2 lg:row-start-1">
                <CareerStatePanel
                    :stats="props.state.stats"
                    :meta="props.state.meta"
                    :growth-rate="props.identity.growth_rate"
                    :fan-ladder="props.identity.fan_ladder"
                />

                <section v-if="props.correction !== null" aria-labelledby="career-correction-heading" class="mt-3">
                    <h3 id="career-correction-heading" class="sr-only">Correct a turn by hand</h3>

                    <!-- The turn selector (F2, plan §9.6 ruling 2). It sits outside the disclosure so a
                         switch does not close the form; `?edit_turn=<id>` is the same query the run
                         screen and the Timeline's decision link already use. -->
                    <label class="flex items-center gap-2 text-sm" for="correction-turn-select">
                        <span class="text-ink-muted">Correct turn</span>
                        <select
                            id="correction-turn-select"
                            ref="turnSelector"
                            :value="String(props.correction.selected_turn_id)"
                            class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink"
                            @change="switchTurn(($event.target as HTMLSelectElement).value)"
                        >
                            <option v-for="turn in props.correction.turns" :key="turn.id" :value="String(turn.id)">Turn {{ turn.turn }}</option>
                        </select>
                    </label>

                    <details class="mt-2 rounded-md border border-rule bg-panel p-4">
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

                        <!-- Delete one turn and its failure event (F2, plan §9.6 ruling 2). A second
                             disclosure inside the correction panel: the write is `runs.turns.destroy`,
                             the route the record screen already used. -->
                        <details class="mt-3 border-t border-rule pt-3">
                            <summary class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-risk">
                                Delete turn {{ props.correction.turn }}
                            </summary>
                            <p class="mt-2 text-sm text-ink-muted">
                                Deletes turn {{ props.correction.turn }} and its recorded failure event.
                                Other turns are unaffected. There is no undo.
                            </p>
                            <form class="mt-2" @submit.prevent="deleteTurn">
                                <button
                                    type="submit"
                                    class="min-h-11 rounded-full border-2 border-risk px-4 font-semibold text-risk"
                                    :disabled="turnDeleteForm.processing"
                                >
                                    {{ turnDeleteForm.processing ? 'Deleting…' : `Delete turn ${props.correction.turn}` }}
                                </button>
                            </form>
                        </details>
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
                    :finale-context="props.advisor.finale"
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
                    <a :href="props.run.training_url" class="mt-2 inline-flex min-h-11 items-center font-medium text-ink-strong underline">
                        Record the first turn through Training
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
                    class="mt-2 inline-flex min-h-11 items-center text-xs font-medium text-ink-strong underline"
                >
                    Career Result
                </a>

                <!-- The run-delete door (F2, plan §9.6 ruling 4). Two-step disclosure: the summary
                     names the destructive action, the form inside submits the DELETE only after the
                     Trainer opens it and confirms. `deleteForm` carries no fields — the route is run
                     scoped and the controller deletes the bound row — so the only data that matters
                     is the URL. -->
                <details class="mt-4">
                    <summary
                        class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-risk"
                    >
                        Delete this career
                    </summary>
                    <p class="mt-2 text-sm text-ink-muted">
                        Permanently removes this run and every record tied to it: turns, race entries,
                        turn events, skills, support-deck slots, and the linked Veteran library record
                        (if any was saved). There is no undo.
                    </p>
                    <form
                        @submit.prevent="deleteRun"
                        class="mt-2"
                    >
                        <button
                            type="submit"
                            class="min-h-11 rounded-full border-2 border-risk px-4 font-semibold text-risk"
                            :disabled="deleteForm.processing"
                        >
                            {{ deleteForm.processing ? 'Deleting…' : 'Delete this career' }}
                        </button>
                    </form>
                </details>
            </section>
        </div>
    </CareerLayout>
</template>
