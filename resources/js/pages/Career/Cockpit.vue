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
 * calendar's year tabs left the page with D14a. The one query-string state is `?edit_turn=<id>`, which
 * names the turn the correction disclosure opens on (plan §9.6 ruling 2); the selector navigates it.
 * There is one form, the manual correction, and it posts to the route that already owns a turn write.
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
import CockpitIdentityCard from '../../components/career/CockpitIdentityCard.vue';
import StatSummary from '../../components/career/StatSummary.vue';
import CareerStatePanel from '../../components/career/CareerStatePanel.vue';
import ActionGrid from '../../components/career/ActionGrid.vue';
import AdvisorRail from '../../components/career/AdvisorRail.vue';
import Section from '../../components/ui/Section.vue';
import RunRaceStrip from '../../components/career/RunRaceStrip.vue';
import ScenarioPanel from '../../components/scenario/ScenarioPanel.vue';
import UnityCupEditor from '../../components/career/UnityCupEditor.vue';
import BaseInput from '@/Components/Form/BaseInput.vue';
import BaseSelect from '@/Components/Form/BaseSelect.vue';
import BaseRadio from '@/Components/Form/BaseRadio.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { TURN_ENTRY_ENERGY_BANDS as energyBands, TURN_ENTRY_ENERGY_STATES as energyStates, TURN_ENTRY_STAT_FIELDS as statFields } from '../../domain/turnEntryFields';
import { computed, nextTick, ref, watch } from 'vue';
import { useVisitState } from '../../composables/useVisitState';

interface Header {
    scenario_label: string;
    scenario_declared: boolean;
    year_label: string | null;
    month_label: string | null;
    turn: number;
    energy: number | null;
    energy_text: string | null;
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
        /**
         * Energy's own state and, when the state is `band`, the coarse word the client showed. They
         * arrive separately because a correction must be able to say "not recorded" and have the
         * previous band cleared, which a single number cannot express (R2-16).
         */
        energy_state: string | null;
        energy_band: string | null;
        fans: number | null;
        mood: string | null;
    };
}

const props = defineProps<{
    run: { id: number; trainee: string; trainee_ja: string | null; umamusume_id: number; scenario: string | null; status: string; status_label: string; scenario_label: string; run_url: string; timeline_url: string; result_url: string; training_url: string; deck_url: string; status_labels: Record<string, string>; scenarios: Record<string, string>; update_url: string; destroy_url: string; export_csv_url: string; export_json_url: string; current_objective_index: number | null };
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
        /**
         * The four stats whose ceiling a Trainer may enter, each with what the run holds and what the
         * scenario publishes (`ADR-0025`). Wit is absent: no column holds it, so no field may write one.
         */
        caps: Record<string, { entered: number | null; scenario: number }>;
        fans: number | null;
        fan_ladder: { class: string | null; nextThreshold: number | null; gap: number | null } | null;
    };
    state: {
        stats: { key: string; label: string; current: number | null; target: number | null; cap: number }[];
        meta: { key: string; label: string; value: number | string | null }[];
    };
    actions: { key: string; label: string; href: string; recommended: boolean; note: string }[];
    // The decision's own shape is declared at `components/career/NextDecision.vue`, the one place that
    // reads it, and forwarded untouched the way the scenario section is (FD-2, plan §9a). `finale` stays
    // a sibling of the contract rather than a seventh key inside it.
    advisor: {
        decision: Record<string, unknown>;
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

// The door an objective can be settled at, read off the grid's own entries rather than from a second
// route table. The grid already owns the href, and a due obligation with no door renders none.
const raceDoor = computed(() => props.actions.find((action) => action.key === 'race')?.href ?? null);

// The section nav's targets are the headings the screen already carries (audit 2026-10-12 §3.6).
// The hrefs are fragment ids, which Inertia leaves to the browser, so these are plain anchors.
const sectionLinks = [
    { href: '#career-header-heading', label: 'Position' },
    { href: '#cockpit-header-edit', label: 'Run identity' },
    { href: '#career-state-heading', label: 'Stats and state' },
    { href: '#career-actions-heading', label: 'Actions' },
    { href: '#career-scenario-heading', label: 'Scenario' },
    { href: '#career-timeline-heading', label: 'Races in this run' },
    { href: '#career-advisor-heading', label: 'Advisor' },
];

/*
 * The Unity Cup team editor (R2-14). The team system is the scenario's own, so the matrix's
 * `team_rank_ladder` panel flag decides whether the editor renders: a scenario name written into this
 * page would be the defect AGENTS.md §7 forbids. The payload's key set is uniform across scenarios, so
 * the flag and not the payload's presence is what gates the control.
 *
 * The shape is declared here rather than imported from the editor, because a `.vue` file's prop type
 * cannot be imported; the two are structurally identical by construction.
 */
interface TeamState {
    team_name: string | null;
    team_motto: string | null;
    team_league_placement: number | null;
    team_preseason_wins: number | null;
    unity_trainings_count: number | null;
    spirit_bursts_count: number | null;
    extreme_bursts_count: number | null;
}

const teamState = computed((): TeamState | null => (props.scenario as { team?: TeamState }).team ?? null);

const composesTeamPanel = computed(
    (): boolean =>
        (props.scenario as { panels?: Record<string, { on?: boolean }> }).panels?.team_rank_ladder?.on === true,
);

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
    energy_state: props.correction?.values.energy_state ?? 'unknown',
    energy_band: props.correction?.values.energy_band ?? '',
    fans: props.correction?.values.fans === null || props.correction === null ? '' : String(props.correction.values.fans),
    mood: props.correction?.values.mood ?? '',
});

/*
 * The band travels with the state that names it, the rule `TrainingDetail` already applies on the
 * guided form: a state of `exact` keeps no band, and any state but `band` clears it. Without this a
 * correction that switches the state to `not recorded` would leave the old band on the row and the
 * next reader would take a word for a reading nobody made (R2-16).
 */
watch(
    () => correctionForm.energy_state,
    (state: string) => {
        if (state !== 'exact') {
            correctionForm.energy = '';
        }

        if (state !== 'band') {
            correctionForm.energy_band = '';
        }
    },
);

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
 * The three run states. The Cockpit is a decision surface while a career is being played, and a summary
 * of one that has ended once it has not, so the top of the screen switches on `run.status` and names the
 * state in the DOM for the specs to read. `Retired` keeps the Completed shape and loses the Result door,
 * because a retired career has no finished result to open.
 */
const runState = computed((): string => props.run.status.toLowerCase());
const isActive = computed((): boolean => props.run.status === 'Active');

// The summary line reads only stored figures. A stat with no reading is `N/A`, the same marker the rows
// below it use, and no total turn count is printed because nothing in the props states one.
const summaryStats = computed((): string =>
    props.state.stats
        .map((stat: { label: string; current: number | null }): string =>
            `${stat.label} ${stat.current === null ? 'N/A' : stat.current.toLocaleString('en-US')}`,
        )
        .join(' · '),
);

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
        correctionForm.energy_state = correction.values.energy_state ?? 'unknown';
        correctionForm.energy_band = correction.values.energy_band ?? '';
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

/*
 * The identity write (R2-13). The three forms above each carry the whole key set the request demands,
 * because `umamusume_id` and `status` are required; this one does the same and adds the three facts the
 * header already printed but nothing could enter. `growth_rate` is keyed by the stat matrix, so the
 * request refuses a sixth stat rather than storing one (`StoreTrainingRunRequest`).
 */
/** The request key that holds one stat's entered ceiling. The stats themselves come from `identity.caps`. */
const capColumn = (stat: string): string => `cap_${stat.toLowerCase()}`;

const identityForm = useForm({
    umamusume_id: String(props.run.umamusume_id ?? ''),
    status: props.run.status,
    trainee_rarity: props.identity.trainee_rarity === null ? '' : String(props.identity.trainee_rarity),
    potential_level: props.identity.potential_level === null ? '' : String(props.identity.potential_level),
    // The four entered ceilings, prefilled with what the run holds. An empty field is a clear rather than
    // a no-op, which is why they ride this form's key set at all (`ADR-0025`). The stats come from the
    // server's map, so this page names none of them.
    ...Object.fromEntries(
        Object.entries(props.identity.caps).map(([stat, ceiling]) => [
            capColumn(stat),
            ceiling.entered === null ? '' : String(ceiling.entered),
        ]),
    ) as Record<string, string>,
    growth_rate: Object.fromEntries(
        props.identity.stat_order.map((stat) => {
            const value = props.identity.growth_rate?.[stat];

            return [stat, value === null || value === undefined ? '' : String(value)];
        }),
    ) as Record<string, string>,
});

const saveIdentity = (): void => {
    identityForm
        // An empty field is "not stated" and `0` is a real figure, so the two are kept apart here
        // rather than collapsed by a `Number('')` on the way out (D-220). A growth row with nothing
        // stated is `null` rather than five nulls: the request's own contract is that a run which
        // stated no growth stores no row.
        .transform((data) => {
            const growth = Object.fromEntries(
                Object.entries(data.growth_rate).map(([stat, value]) => [stat, value === '' ? null : Number(value)]),
            );

            // A ceiling field with nothing in it is a clear, which is the statement "use the scenario's
            // own bound" — never a zero the client never showed. The four keys are the request's column
            // names, so they post flat beside the identity fields.
            const ceilings = Object.fromEntries(
                Object.keys(props.identity.caps).map((stat) => {
                    const column = capColumn(stat);
                    const value = data[column];

                    return [column, value === '' || value === undefined ? null : Number(value)];
                }),
            );

            return {
                ...data,
                ...ceilings,
                trainee_rarity: data.trainee_rarity === '' ? null : Number(data.trainee_rarity),
                potential_level: data.potential_level === '' ? null : Number(data.potential_level),
                growth_rate: Object.values(growth).every((value) => value === null) ? null : growth,
            };
        })
        .put(props.run.update_url, { preserveScroll: true });
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
        :trainee-ja="props.run.trainee_ja"
        :scenario-label="props.run.scenario_label"
        :status-label="props.run.status_label"
        :status="props.run.status"
        :position="props.header.turn === 0 ? null : `Turn ${props.header.turn}`"
        :run-url="props.run.run_url"
        current="cockpit"
    >
        <Head :title="`Career: ${props.run.trainee}`" />
        <template #eyebrow>Cockpit</template>
        <template #title>{{ props.run.trainee }}</template>

        <p v-if="visiting" role="status" class="mb-4 text-sm text-ink-muted">Loading…</p>
        <p v-if="visitFailed" role="alert" class="mb-4 rounded-md border border-risk bg-raised px-3 py-2 text-sm text-risk">
            That screen did not load. Nothing was saved; try again.
        </p>

        <!-- The condensed read, above the wayfinder so the nav slides beneath it while the page scrolls
             (the strip is `top-0` with a solid background and a higher z-index). It is a second READING
             of the header's own facts and never a second primary: the one filled CTA stays in the
             advisor rail, and the strip's outline affordance moves a Trainer to it. The class a source
             scan would count is deliberately not printed here, so that a count of filled primaries in
             this file stays equal to the number the page actually renders. -->
        <StatSummary
            :turn="props.header.turn"
            :energy-text="props.header.energy_text"
            :mood="props.header.mood"
            :fans="props.header.fans"
            :skill-points="props.header.skill_points"
            :recommended="isActive ? (props.advisor.decision.recommended?.action ?? null) : null"
        />

        <!-- The page's own wayfinder (audit 2026-10-12 §3.6): anchors into the regions the screen
             already names, never tabs - SCREEN_SPEC.md §SCR-CAR-011 forbids hiding a region at a
             breakpoint, and tabs hide panels by definition. Sticky from md up, the same widths the
             wizard's step bar ruling chose; below md it scrolls with the page and nothing leaves
             the accessibility tree. -->
        <nav aria-label="On this page" class="mb-4 border-b border-rule bg-page py-1 md:sticky md:top-0 md:z-10 md:-mx-6 md:px-6">
            <ul class="flex flex-wrap gap-1 text-sm">
                <li v-for="link in sectionLinks" :key="link.href">
                    <a :href="link.href" class="inline-flex min-h-11 items-center rounded-md px-3 text-ink hover:bg-raised hover:text-ink-strong transition-colors duration-150 ease-out">{{ link.label }}</a>
                </li>
            </ul>
        </nav>

        <div :data-run-state="runState" class="contents">
        <CockpitIdentityCard
            :trainee="props.run.trainee"
            :trainee-ja="props.run.trainee_ja"
            :header="props.header"
            :countdown="props.scenarioCountdown"
            :card-title="props.identity.card_title"
            :trainee-rarity="props.identity.trainee_rarity"
            :potential-level="props.identity.potential_level"
        />

        <!-- The finished career's own summary (Step 5). It reads the header's turn, the run's status and
             the five stat readings this screen already prints, and names the one door a closed career
             still has: the Career Result. Nothing here is derived that the props do not carry, so there
             is no total turn count and no epithet line, because no prop states either. -->
        <Section
            v-if="!isActive"
            id="cockpit-summary-heading"
            :title="`${props.run.trainee} · ${props.run.scenario_label}`"
            class="rounded-lg border border-rule bg-panel p-4"
        >
            <p class="mt-1 text-sm text-ink">
                {{ props.run.status_label }} · Turn {{ props.header.turn }}
            </p>
            <p class="mt-1 font-mono text-sm tabular-nums text-ink-strong">{{ summaryStats }}</p>
            <a
                v-if="props.run.status === 'Completed'"
                :href="props.run.result_url"
                class="mt-3 inline-flex min-h-11 items-center rounded-md border border-rule px-4 font-semibold text-ink-strong underline"
            >
                Open the Career Result
            </a>
        </Section>

        <!-- The header edit forms (F2, plan §9.6 ruling 1). Status, scenario and grade-point period
             all write through `runs.update`, the route and validator the 2.0.0 record screen used. A
             failure comes back with `page.props.errors`, which the page-level alert names. Each form
             carries the sibling fields the request still writes, so editing one cannot blank another. -->
        <section aria-labelledby="cockpit-header-edit" class="mt-2">
            <h3 id="cockpit-header-edit" class="text-lg font-semibold text-ink-strong">Run identity</h3>

            <!-- The export and deck doors are reads, not writes, so they stay outside the disclosure
                 a Trainer must open to change a value (audit 2026-10-12 §3.3). -->
            <ul class="mt-2 flex flex-wrap items-end gap-4 text-sm">
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

                <li class="flex flex-col gap-1">
                    <span class="text-ink-muted">Support deck</span>
                    <!-- R2-17: `/training-runs/{run}/deck` answers and writes this run's six slots, and
                         the Cockpit offered no door to it, so a Trainer mid-career could reach the
                         builder only by typing its URL. A GET, so it is a plain link, like the exports. -->
                    <a
                        :href="props.run.deck_url"
                        class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-semibold text-ink-strong underline"
                    >
                        Edit this run's deck
                    </a>
                </li>
            </ul>

            <details data-region="edit-identity" class="mt-2 rounded-lg border border-rule bg-panel p-4">
                <summary class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-ink-strong">
                    Edit identity
                </summary>
                <ul class="mt-3 flex flex-wrap items-end gap-4 text-sm">
                <li class="flex flex-col gap-1">
                    <label for="status" class="text-ink-muted">Status</label>
                    <form @submit.prevent="saveStatus">
                         <BaseSelect
                           id="status"
                           v-model="statusForm.status"
                           name="status"
                           :options="Object.keys(props.run.status_labels).map(value => ({ value: value, label: props.run.status_labels[value] }))"
                         />
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
                         <BaseSelect
                           id="scenario"
                           v-model="scenarioForm.scenario"
                           name="scenario"
                           :options="[{ value: '', label: 'No scenario set' }, ...Object.keys(props.run.scenarios).map(key => ({ value: key, label: props.run.scenarios[key] }))]"
                         />
                        <button
                            type="submit"
                            class="mt-1 inline-flex min-h-11 items-center rounded-full border-2 border-rule px-4 font-bold text-ink-strong disabled:opacity-60"
                            :disabled="scenarioForm.processing"
                        >
                            {{ scenarioForm.processing ? 'Saving…' : 'Change scenario' }}
                        </button>
                        <p v-if="scenarioForm.recentlySuccessful" role="status" class="mt-1 text-xs text-ink-muted">Saved.</p>
                    </form>
                </li>

                <li
                    v-if="props.gradeObjectives.length > 0"
                    class="flex flex-col gap-1"
                >
                    <label for="current_objective_index" class="text-ink-muted">Grade Point period</label>
                    <form @submit.prevent="savePeriod">
                         <BaseSelect
                           id="current_objective_index"
                           v-model="periodForm.current_objective_index"
                           name="current_objective_index"
                           :options="[{ value: '', label: 'Not reported' }, ...props.gradeObjectives.map(objective => ({ value: String(objective.index), label: objective.index + '. ' + objective.name }))]"
                         />
                        <button
                            type="submit"
                            class="mt-1 inline-flex min-h-11 items-center rounded-full border-2 border-rule px-4 font-bold text-ink-strong disabled:opacity-60"
                            :disabled="periodForm.processing"
                        >
                            {{ periodForm.processing ? 'Saving…' : 'Report period' }}
                        </button>
                        <p v-if="periodForm.recentlySuccessful" role="status" class="mt-1 text-xs text-ink-muted">Saved.</p>
                    </form>
                </li>

                <li class="flex flex-col gap-1">
                    <span class="text-ink-muted">Trainee rarity and potential</span>
                    <!-- R2-13: the header printed `★3 · Potential 2` and the growth row, and nothing
                         could enter either. The same write as the three forms above, with the three
                         facts added; empty stays "not stated" and `0` stays a real growth figure. -->
                    <form @submit.prevent="saveIdentity" class="flex flex-col gap-2">
                        <span class="flex flex-wrap items-end gap-3">
                            <BaseSelect
                              id="trainee_rarity"
                              v-model="identityForm.trainee_rarity"
                              name="trainee_rarity"
                              label="Rarity"
                              :options="[{ value: '', label: 'Not stated' }, ...[1, 2, 3].map(rarity => ({ value: String(rarity), label: '★' + String(rarity) }))]"
                            />
                            <label for="potential_level" class="flex flex-col gap-1">
                                <span class="text-xs text-ink-muted">Potential Level</span>
                                     <BaseInput
                                         id="potential_level"
                                         v-model="identityForm.potential_level"
                                         type="number"
                                         name="potential_level"
                                         min="1"
                                         max="5"
                                       />
                            </label>
                        </span>
                        <fieldset class="flex flex-wrap items-end gap-3">
                            <legend class="text-xs text-ink-muted">Growth rate</legend>
                            <label
                                v-for="stat in props.identity.stat_order"
                                :key="stat"
                                :for="`growth_rate_${stat}`"
                                class="flex flex-col gap-1"
                            >
                                <span class="text-xs text-ink-muted">{{ stat }}</span>
                                <span class="flex items-center gap-1">
                                     <BaseInput
                                         :id="`growth_rate_${stat}`"
                                         v-model="identityForm.growth_rate[stat]"
                                         type="number"
                                         :name="`growth_rate[${stat}]`"
                                         min="0"
                                         max="30"
                                       />
                                    <span class="text-ink-muted">%</span>
                                </span>
                            </label>
                        </fieldset>
                        <fieldset class="flex flex-wrap items-end gap-3">
                            <legend class="text-xs text-ink-muted">Stat ceilings</legend>
                            <!-- The ceiling the client shows for this run, entered rather than computed
                                 (`ADR-0025`): nothing here derives it from sparks or card effects. The
                                 published figure stays printed beside the field, so an entered ceiling is
                                 never read as the scenario's own arithmetic. -->
                            <label
                                v-for="(ceiling, stat) in props.identity.caps"
                                :key="stat"
                                class="flex flex-col gap-1"
                            >
                                <span class="text-xs text-ink-muted">{{ stat }}</span>
                                <span class="flex items-center gap-1">
                                     <BaseInput
                                         :id="`ceiling_${stat}`"
                                         v-model="identityForm[capColumn(String(stat))]"
                                         type="number"
                                         :name="capColumn(String(stat))"
                                         :aria-label="`${stat} ceiling`"
                                         min="1"
                                       />
                                    <span
                                        class="text-xs text-ink-muted"
                                        title="The figure this scenario publishes in its own cap table. An entered ceiling replaces it for this run; leaving the field empty restores it."
                                    >Scenario {{ ceiling.scenario }}</span>
                                </span>
                            </label>
                        </fieldset>
                        <p v-if="identityForm.isDirty" class="text-xs text-ink-muted">Unsaved changes.</p>
                        <button
                            type="submit"
                            class="mt-1 inline-flex min-h-11 w-fit items-center rounded-full border-2 border-rule px-4 font-bold text-ink-strong disabled:opacity-60"
                            :disabled="identityForm.processing"
                        >
                            {{ identityForm.processing ? 'Saving…' : 'Save identity' }}
                        </button>
                        <p v-if="identityForm.recentlySuccessful" class="text-xs text-ink-muted">Saved.</p>
                    </form>
                </li>
                </ul>
            </details>
        </section>

        <!-- The team write path rides the same disclosure as the other edit panels. It is `open` while the
             career is being played, so the Active screen keeps the panel it has always shown, and closed
             once the career has ended, where it is a setting rather than a decision. -->
        <details
            v-if="composesTeamPanel && teamState !== null"
            data-region="team-state"
            :open="isActive"
            class="mt-3 rounded-lg border border-rule bg-panel p-4"
        >
            <summary class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-ink-strong">
                Edit team state
            </summary>
            <UnityCupEditor
                :action="props.run.update_url"
                :umamusume-id="props.run.umamusume_id"
                :status="props.run.status"
                :team="teamState"
            />
        </details>

        <!-- The spec's order, as CSS only. `order-*` drives the single mobile column; from `md` up the
             explicit row and column placement takes over, so the tablet strip and the desktop
             three-column cockpit are the same DOM. -->
        <div class="mt-4 grid grid-cols-1 gap-4 md:items-start @4xl:grid-cols-[2fr_1fr] lg:items-start @5xl:grid-cols-[1fr_2.5fr_1.5fr]">
            <div class="order-1 min-w-0 md:order-none @4xl:col-start-1 @4xl:row-start-2 @5xl:col-start-2 @5xl:row-start-1">
                <CareerStatePanel
                    :stats="props.state.stats"
                    :meta="props.state.meta"
                    :growth-rate="props.identity.growth_rate"
                    :fan-ladder="props.identity.fan_ladder"
                    denominator-label="target"
                />

                <section v-if="props.correction !== null" aria-labelledby="career-correction-heading" class="mt-3">
                    <h3 id="career-correction-heading" class="sr-only">Correct a turn by hand</h3>

                    <!-- The turn selector (F2, plan §9.6 ruling 2). It sits outside the disclosure so a
                         switch does not close the form; `?edit_turn=<id>` is the same query the run
                         screen and the Timeline's decision link already use. -->
<BaseSelect
                            id="correction-turn-select"
                            ref="turnSelector"
                            label="Correct turn"
                            :value="String(props.correction.selected_turn_id)"
                            @update:model-value="switchTurn($event)"
                            :options="props.correction.turns.map(turn => ({ value: String(turn.id), label: 'Turn ' + turn.turn }))"
                        />

                    <details data-region="correct-turn" class="mt-2 rounded-lg border border-rule bg-panel p-4">
                        <summary class="flex min-h-11 cursor-pointer items-center text-lg font-semibold text-ink-strong">
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

                        <form class="mt-3 grid grid-cols-2 gap-3 text-sm @4xl:grid-cols-3" @submit.prevent="saveCorrection">
                            <BaseInput
                              v-model="correctionForm.turn"
                              type="number"
                              id="turn"
                              name="turn"
                              label="Turn *"
                              required
                              :error="correctionForm.errors.turn"
                            />
                            <BaseInput
                              v-for="field in statFields"
                              :key="field.name"
                              v-model="correctionForm[field.name]"
                              type="number"
                              :id="field.name"
                              name="field.name"
                              :label="`${field.label} *`"
                              required
                              :error="correctionForm.errors[field.name]"
                            />
                            <BaseInput
                              v-model="correctionForm.sp"
                              type="number"
                              id="sp"
                              name="sp"
                              label="Skill Points"
                              :error="correctionForm.errors.sp"
                            />
                            <fieldset class="col-span-2 flex flex-col gap-1 @4xl:col-span-3">
                                <legend class="text-ink-muted">Energy</legend>
                                <div class="flex flex-wrap items-center gap-x-4">
                                    <BaseRadio
                                      v-for="state in energyStates"
                                      :key="state.value"
                                      v-model="correctionForm.energy_state"
                                      :id="`energy_state_${state.value}`"
                                      name="energy_state"
                                      :label="state.label"
                                      :value="state.value"
                                      :error="correctionForm.errors.energy_state"
                                    />
                                </div>
                                <BaseInput
                                  v-if="correctionForm.energy_state === 'exact'"
                                  v-model="correctionForm.energy"
                                  type="number"
                                  id="energy"
                                  name="energy"
                                  label="Energy figure"
                                  :min="0"
                                  :max="100"
                                  :error="correctionForm.errors.energy"
                                />
                                <BaseSelect
                                  v-else-if="correctionForm.energy_state === 'band'"
                                  v-model="correctionForm.energy_band"
                                  name="energy_band"
                                  label="Energy band"
                                  :options="energyBands"
                                  :error="correctionForm.errors.energy_band"
                                />
                            </fieldset>
                            <BaseInput
                              v-model="correctionForm.fans"
                              type="number"
                              id="fans"
                              name="fans"
                              label="Fans"
                              :error="correctionForm.errors.fans"
                            />
                            <BaseSelect
                              v-model="correctionForm.mood"
                              name="mood"
                              label="Mood"
                              :options="[{ value: '', label: 'not recorded' }, { value: 'GREAT', label: 'GREAT' }, { value: 'GOOD', label: 'GOOD' }, { value: 'NORMAL', label: 'NORMAL' }, { value: 'BAD', label: 'BAD' }, { value: 'AWFUL', label: 'AWFUL' }]"
                              :error="correctionForm.errors.mood"
                            />
                            <button
                                type="submit"
                                class="inline-flex min-h-11 items-center justify-center rounded-md bg-primary-action px-4 font-semibold text-on-primary-action disabled:opacity-60 @4xl:col-span-3"
                                :disabled="correctionForm.processing"
                            >
                                {{ correctionForm.processing ? 'Saving…' : 'Save correction' }}
                            </button>
                            <!-- The two header forms on this screen already report their own write; this
                                 one said nothing, so a Trainer could not tell a saved correction from a
                                 dead button (R3-04). `Correction saved.` names which of the three forms
                                 is speaking. -->
                            <p v-if="correctionForm.recentlySuccessful" class="text-xs text-ink-muted @4xl:col-span-3">
                                Correction saved.
                            </p>
                        </form>

                        <!-- Delete one turn and its failure event (F2, plan §9.6 ruling 2). A second
                             disclosure inside the correction panel: the write is `runs.turns.destroy`,
                             the route the record screen already used. -->
                        <details class="mt-3 border-t border-rule pt-3">
                            <summary class="flex min-h-11 cursor-pointer items-center text-lg font-semibold text-risk">
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

            <div data-region="advisor" class="order-2 min-w-0 md:order-none @4xl:col-start-2 @4xl:row-span-2 @4xl:row-start-2 @5xl:col-start-3 @5xl:row-start-1">
                <AdvisorRail v-if="isActive" :decision="props.advisor.decision" :race-url="raceDoor" :timeline-url="props.run.timeline_url" />

                <!-- A career that has ended has no turn being decided, so the advisor's own refusal is not
                     the answer this region owes. One statement and the one door a finished career still has. -->
                <Section
                    v-else
                    id="career-closed-heading"
                    :title="props.run.status === 'Completed' ? 'This run is complete.' : 'This run is retired.'"
                    class="rounded-lg border border-rule bg-panel p-4"
                >
                    <p class="mt-1 text-sm text-ink">
                        Nothing is being decided, so nothing is ranked and nothing is refused.
                    </p>
                </Section>
            </div>

            <div class="order-3 min-w-0 md:order-none @4xl:col-start-1 @4xl:row-start-3 @5xl:col-start-2 @5xl:row-start-2">
                <!-- The empty state names what is missing, why it matters and what to do (plan §10). It
                     sits above the grid rather than replacing it: the grid's own entries are still the
                     doors, and the advisor's refusal is a real state rather than a blank. -->
                <Section
                    v-if="empty"
                    id="career-empty-heading"
                    title="No turn recorded yet"
                    class="mb-3 rounded-lg border border-dashed border-rule bg-panel p-4"
                >
                    <p class="mt-1 text-sm text-ink">
                        Energy, Mood, Fans, Skill Points and the five stats are what this screen reads, and
                        none has been entered for this run. Until one turn is recorded the advisor has
                        nothing to rank, so it declines rather than guessing.
                    </p>
                    <a :href="props.run.training_url" class="mt-2 inline-flex min-h-11 items-center font-medium text-ink-strong underline">
                        Record the first turn through Training
                    </a>
                </Section>

                <ActionGrid v-if="isActive" :actions="props.actions" />

                <!-- The actions a finished career still offers are tools rather than decisions, so they sit
                     below the fold in the same shape as the other settings. -->
                <details v-else data-region="run-tools" class="mt-3 rounded-lg border border-rule bg-panel p-4">
                    <summary class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-ink-strong">
                        Run tools
                    </summary>
                    <ActionGrid class="mt-3" :actions="props.actions" />
                </details>

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

            <Section
                id="career-scenario-heading"
                title="Scenario"
                class="order-4 min-w-0 rounded-lg border border-rule bg-panel p-4 md:order-none @4xl:col-span-2 @4xl:row-start-4 @5xl:col-span-3 @5xl:row-start-3"
            >
                <!-- The shell switches on the matrix's own flags and widget keys and knows no
                     scenario name (plan §9 E1, gate G-33). The prop's own shape is declared at
                     `components/scenario/ScenarioPanel.vue`; the page forwards it untouched. -->
                <div class="mt-2">
                    <ScenarioPanel :scenario="props.scenario as never" />
                </div>
            </Section>

            <section
                class="order-5 min-w-0 rounded-lg border border-rule bg-panel p-4 md:order-none @4xl:col-span-2 @4xl:row-start-1 @5xl:col-span-1 @5xl:col-start-1 @5xl:row-span-2 @5xl:row-start-1"
                aria-labelledby="career-timeline-heading"
            >
                <h2 v-if="isActive" id="career-timeline-heading" class="text-lg font-semibold text-ink-strong">
                    Races in this run
                </h2>

                <!-- Once the career has ended the race list is history, not a decision, so it collapses
                     below the fold. The heading id moves onto the `summary` so the section keeps the one
                     accessible name it has while open. -->
                <details v-else data-region="races-in-this-run" class="mt-1 rounded-lg border border-rule bg-raised p-4">
                    <summary
                        id="career-timeline-heading"
                        class="flex min-h-11 cursor-pointer items-center text-lg font-semibold text-ink-strong"
                    >
                        Races in this run
                    </summary>
                    <RunRaceStrip class="mt-2" :strip="props.raceStrip as never" />
                </details>

                <RunRaceStrip v-if="isActive" :strip="props.raceStrip as never" />

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

                <!-- The Career Result's only other door was the 2.0.0 record page's header, so it sits
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
                <details data-region="danger-zone" class="mt-4">
                    <summary
                        class="flex min-h-11 cursor-pointer items-center text-lg font-semibold text-risk"
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
        </div>
    </CareerLayout>
</template>
