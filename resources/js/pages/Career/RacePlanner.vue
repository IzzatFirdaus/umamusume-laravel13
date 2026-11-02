<script setup lang="ts">
/*
 * The Scenario Race Planner, `SCR-CAR-023` (SCREEN-017, plan §9 E5). The whole calendar, grouped, with
 * the races the Trainer picks compared side by side.
 *
 * The page pattern is `Career/RaceDecision.vue`'s: `CareerLayout`, `<Head title>`, the `#title` slot,
 * page-level loading and error because the only user-initiated async action is a card's own Enter Race
 * (ADR-0007). The one write is inside `RaceCard`'s drawer and posts to the existing `runs.races.store`,
 * so this page holds no form.
 *
 * **Race facts only.** Every figure is a `race_catalog_slots` column, a value the Trainer entered, or a
 * named absence. The brief's win probability and expected risk are held on `ADR-0016` and print `N/A`
 * with the reason; its recommendation block is not built, and the deadline region states the next
 * obligation and its turn rather than promising that none will be missed.
 *
 * The comparison and the alignment are one `<table>` each, with the same columns, because aligning
 * identical properties vertically is the whole point (design-2.0 §46) and two separate card grids
 * would make the Trainer hold the difference in their head.
 */
import CareerLayout from '../../layouts/CareerLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import Note from '../../components/ui/Note.vue';
import ProvenanceBadge from '../../components/ProvenanceBadge.vue';
import RacePlanList from '../../components/career/RacePlanList.vue';
import AlertRow from '../../components/scenario/AlertRow.vue';
import AbsenceNote from '../../components/ui/AbsenceNote.vue';
import Section from '../../components/ui/Section.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useVisitState } from '../../composables/useVisitState';

interface Cell {
    key: string;
    label: string;
    value: string | null;
    title: string | null;
}

interface Race {
    id: number;
    title: string;
    tier: string | null;
    year_label: string;
    turn: number | null;
    fans_needed: number | null;
    maiden_gated: boolean;
    is_mandatory: boolean;
    is_special: boolean;
    facts: Cell[];
    detail_recorded: boolean;
    status: string | null;
    placement: string | null;
    fans_gain: number | null;
    grade_points: number | null;
    comparison: Cell[];
    alignment: Cell[];
    aptitude: { surface: string | null; distance: string | null; label: string };
    blocked_reason: string | null;
}

interface Group {
    key: string;
    label: string;
    definition: string;
    total: number;
    truncated: string | null;
    empty: string | null;
    races: Race[];
}

const props = defineProps<{
    run: { id: number; trainee: string; trainee_ja: string | null; scenario_label: string; status_label: string; run_url: string; races_url: string };
    nextTurn: { year_label: string; turn: number } | null;
    groups: { mandatory: Group; upcoming: Group; optional: Group; rival: Group } | null;
    comparison: { rows: { key: string; label: string }[] };
    alignment: { recorded: boolean; absence: string | null; rows: { key: string; label: string }[] };
    held: { win_probability: { label: string; title: string }; expected_risk: { label: string; title: string } };
    readiness: { label: string; title: string };
    deadline: {
        next: { id: number; title: string; year_label: string; turn: number | null; state: string } | null;
        alert: { text: string; detail: string } | null;
    };
    entry: { action: string; turns: { id: number; turn: number }[] };
    empty: string | null;
}>();

const { visiting, visitFailed } = useVisitState();

const LIMIT = 4;

const selected = ref<number[]>([]);

function toggle(id: number): void {
    selected.value = selected.value.includes(id)
        ? selected.value.filter((chosen) => chosen !== id)
        : [...selected.value, id].slice(0, LIMIT);
}

/** Every race the page lists, so a selected id can be resolved back to its row. */
const listed = computed<Race[]>(() => {
    if (props.groups === null) {
        return [];
    }

    return Object.values(props.groups).flatMap((group) => group.races);
});

const chosen = computed<Race[]>(() =>
    selected.value
        .map((id) => listed.value.find((race) => race.id === id))
        .filter((race): race is Race => race !== undefined),
);

/** The ordered groups, so the template renders them in one loop rather than four copies. */
const orderedGroups = computed<Group[]>(() => (props.groups === null ? [] : Object.values(props.groups)));

function cell(race: Race, key: string, from: 'comparison' | 'alignment'): Cell | undefined {
    return race[from].find((entry) => entry.key === key);
}

/**
 * The alignment figures this run has no target for.
 *
 * `alignment.recorded` is the controller's own group-level fact: with no build target recorded,
 * every alignment cell reads "Not recorded" instead of a comparison, so the whole group is
 * absent and one sentence names it. The labels are the alignment rows' own, so the sentence
 * says which figures are missing rather than that something is.
 */
const alignmentAbsent = computed<string[]>(() =>
    props.alignment.recorded ? [] : props.alignment.rows.map((row) => row.label),
);

/**
 * The comparison figures no chosen race carries.
 *
 * A row is absent when every chosen race leaves it null, which is the only sense in which the
 * table lacks it: one race carrying a figure and another not is a per-cell fact, and that cell
 * keeps its own "N/A" and its own reason.
 */
const comparisonAbsent = computed<string[]>(() => {
    if (chosen.value.length === 0) {
        return [];
    }

    return props.comparison.rows
        .filter((row) => chosen.value.every((race) => (cell(race, row.key, 'comparison')?.value ?? null) === null))
        .map((row) => row.label);
});

/** Empty the selection, so the Trainer can choose again from the top. */
function clearSelection(): void {
    selected.value = [];
}

/** The selected race's own comparison cell, by key. */
function comparisonValue(race: Race, key: string): string | null {
    return cell(race, key, 'comparison')?.value ?? null;
}

const disciplineLabel = (race: Race): string => {
    const surface = comparisonValue(race, 'surface') ?? 'surface not recorded';
    const band = comparisonValue(race, 'distance_band') ?? 'band not recorded';

    return `${surface} · ${band}`;
};

const aptitudePill = (race: Race): string => race.aptitude.distance ?? race.aptitude.surface ?? 'N/A';

const racesUrl = computed(() => props.run.races_url);

</script>

<template>
    <CareerLayout
        :trainee="props.run.trainee"
        :scenario-label="props.run.scenario_label"
        :status-label="props.run.status_label"
        :run-url="props.run.run_url"
        current="races"
    >
        <Head :title="'Race planner: ' + props.run.trainee" />

        <template #head>
            <PageHeader
                eyebrow="Race planner"
                title="Plan the calendar"
                description="Every race the scenario catalogue holds, grouped by obligation. Pick two to four and their rewards line up side by side; win probability and expected risk are held and print N/A."
            >
                <template #actions>
                    <a :href="props.run.run_url" class="inline-flex min-h-11 items-center rounded-md border border-rule bg-raised px-4 text-sm font-semibold text-ink hover:text-ink-strong">Back to cockpit</a>
                    <a :href="racesUrl" class="inline-flex min-h-11 items-center rounded-md border border-rule bg-raised px-4 text-sm font-semibold text-ink hover:text-ink-strong">This turn's race</a>
                </template>
            </PageHeader>
        </template>

        <p v-if="visiting" role="status" class="mb-3 text-sm text-ink-muted">Loading…</p>
        <p v-if="visitFailed" role="alert" class="mb-3 rounded-md border border-risk bg-raised px-3 py-2 text-sm text-risk">
            That screen did not load. Nothing was saved; try again.
        </p>

        <section class="card" aria-labelledby="deadline-heading">
            <h2 id="deadline-heading">Deadlines</h2>
            <p v-if="props.deadline.next !== null" class="mt-1 text-sm">
                Next mandatory race: <b class="text-ink-strong">{{ props.deadline.next.title }}</b>,
                <span class="font-mono">Turn {{ props.deadline.next.turn ?? 'N/A' }}</span>.
            </p>
            <p v-else class="mt-1 text-sm text-ink">No mandatory race is outstanding on this calendar.</p>
            <p v-if="props.deadline.alert !== null" role="status" class="mt-1 rounded-md border border-risk bg-raised px-2 py-1 text-sm text-risk">
                {{ props.deadline.alert.text }} {{ props.deadline.alert.detail }}
            </p>
            <p class="mt-1.5 text-xs text-ink-muted">
                This states the next obligation the run has not cleared. Whether the rest of the career keeps its deadlines is not something this tool predicts.
            </p>
        </section>

        <div class="mt-3.5 grid gap-3.5 g-3">
            <section class="card" aria-labelledby="planning-heading">
                <h2 id="planning-heading">Planning from</h2>
                <p class="mt-1 text-sm">
                    <template v-if="props.nextTurn !== null">{{ props.nextTurn.year_label }} · <span class="font-mono">Turn {{ props.nextTurn.turn }}</span></template>
                    <template v-else>No turn is being planned.</template>
                </p>
            </section>
            <section class="card" aria-labelledby="held-heading">
                <h2 id="held-heading">Held figures</h2>
                <p class="mt-1 text-sm">Win probability: <b class="nai" :title="props.held.win_probability.title">{{ props.held.win_probability.label }}</b></p>
                <p class="mt-1 text-sm">Expected risk: <b class="nai" :title="props.held.expected_risk.title">{{ props.held.expected_risk.label }}</b></p>
            </section>
            <section class="card" aria-labelledby="readiness-heading">
                <h2 id="readiness-heading">Readiness</h2>
                <p class="mt-1 text-sm"><span :title="props.readiness.title">{{ props.readiness.label }}</span></p>
            </section>
        </div>

        <template v-if="props.groups !== null">
            <!-- One loop over the controller's own groups. The page rendered `mandatory` and `upcoming`
                 only, so `optional` and `rival` were computed and transmitted and never displayed
                 (AUD-20261102-003 F-01); `orderedGroups` existed for exactly this loop and nothing used it
                 (F-03). Each group carries its own key, label, definition and empty sentence, so the loop
                 states structure and the controller owns the words. -->
            <section
                v-for="group in orderedGroups"
                :key="group.key"
                class="card mt-3.5"
                :aria-labelledby="`${group.key}-heading`"
            >
                <h2 :id="`${group.key}-heading`">{{ group.label }}</h2>
                <p class="sub">{{ group.definition }}</p>
                <p v-if="group.races.length === 0" class="text-sm text-ink">{{ group.empty }}</p>
                <table v-else class="t mt-2 w-full border-collapse text-sm">
                    <caption class="sr-only">{{ group.label }}: {{ group.definition }}</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="text-left">Race</th><th scope="col" class="text-left">Grade</th><th scope="col" class="text-left">Surface · distance</th>
                            <th scope="col" class="text-right">Turn</th><th scope="col" class="text-right">Fans needed</th><th scope="col" class="text-right"><span class="sr-only">Compare</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="race in group.races" :key="race.id" :class="selected.includes(race.id) ? 'is-current bg-green-tint' : ''" class="border-b border-rule-soft">
                            <td class="py-2 pr-3">
                                <b>{{ race.title }}</b>
                                <span class="block text-xs text-ink-muted">{{ comparisonValue(race, 'distance') ?? 'Distance not recorded' }}</span>
                            </td>
                            <td class="py-2 pr-3">{{ race.tier ?? 'N/A' }}</td>
                            <td class="py-2 pr-3" :title="race.aptitude.label">
                                {{ disciplineLabel(race) }}
                                <span class="pill pill-neutral ml-1 align-[1px]">{{ aptitudePill(race) }}</span>
                                <span class="sr-only">, the race's aptitude letter for this distance and surface</span>
                            </td>
                            <td class="py-2 pr-3 text-right font-mono">{{ race.turn ?? 'N/A' }}</td>
                            <td class="py-2 pr-3 text-right font-mono">{{ race.fans_needed ?? 'N/A' }}</td>
                            <td class="py-2 text-right">
                                <button
                                    type="button"
                                    class="btn btn-sm min-h-11"
                                    :disabled="!selected.includes(race.id) && selected.length >= LIMIT"
                                    :aria-describedby="!selected.includes(race.id) && selected.length >= LIMIT ? 'compare-limit-hint' : undefined"
                                    @click="toggle(race.id)"
                                >Compare</button>
                                <span v-if="race.blocked_reason !== null" class="ml-1 block text-[10.5px] text-ink-muted" :title="race.blocked_reason">{{ race.maiden_gated ? 'Maiden lock not cleared' : 'Blocked' }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p v-if="group.truncated !== null" class="mt-1.5 text-xs text-ink-muted">{{ group.truncated }}</p>
            </section>
        </template>
        </template>

        <p v-else-if="props.empty !== null" class="card mt-3.5 text-sm text-ink">{{ props.empty }}</p>

        <section class="card mt-3.5" aria-labelledby="compare-heading">
            <h2 id="compare-heading">Reward comparison</h2>
            <p class="sub">Pick two to four races with the Compare control above and their rewards line up here, one property per row.</p>

            <template v-if="chosen.length < 2">
                <div class="mt-2 rounded-md border border-dashed border-rule bg-raised p-4" role="status">
                    <h3 class="text-[15px] font-semibold text-ink-strong">Nothing to compare yet</h3>
                    <p class="mt-1 text-sm text-ink-muted">
                        Choose at least two races above. The comparison shows recorded and sourced values
                        only; a cell with no source behind it reads N/A, and its title says why.
                    </p>
                    <!-- The cap was silent: a fifth Compare press was dropped by `.slice(0, LIMIT)` with
                         no feedback, and the prompt named only the minimum
                         (AUD-20261102-003 F-02). -->
                    <p id="compare-limit-hint" class="mt-2 text-xs text-ink-muted">
                        Up to {{ LIMIT }} races can be lined up at once. Choosing a fifth replaces nothing:
                        clear one with its Compare control first.
                    </p>
                </div>
            </template>

            <template v-else>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                    <span class="text-ink-muted">{{ chosen.length }} races selected</span>
                    <button type="button" class="btn btn-sm min-h-11" @click="clearSelection">Clear selection</button>
                </div>
                <div class="mt-2 overflow-x-auto">
                    <table class="t w-full min-w-[40rem] border-collapse text-sm">
                        <thead>
                            <tr>
                                <th class="text-left">Property</th>
                                <th v-for="race in chosen" :key="race.id" class="text-left">{{ race.title }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in props.comparison.rows" :key="row.key" class="border-b border-rule-soft">
                                <th scope="row" class="py-2 pr-3 text-left font-medium text-ink">{{ row.label }}</th>
                                <td v-for="race in chosen" :key="row.key + '-' + race.id" class="py-2 pr-3">
                                    <span v-if="comparisonValue(race, row.key) !== null">{{ comparisonValue(race, row.key) }}</span>
                                    <span v-else class="text-ink-muted" :title="cell(race, row.key, 'comparison')?.title ?? undefined">N/A</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p class="mt-2 text-xs text-ink-muted">
                    The comparison shows recorded and sourced values only. Rows every chosen race leaves
                    blank: {{ comparisonAbsent.length > 0 ? comparisonAbsent.join(', ') : 'none' }}.
                </p>

                <!-- HFW OVERRIDE (target alignment): the wireframe compares races against each other; the
                     app also compares each race's band/surface with the run's recorded build target, which
                     is the question a target exists to answer. -->
                <p class="mt-3 text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Target alignment</p>
                <p v-if="!props.alignment.recorded" class="text-sm text-ink-muted">{{ props.alignment.absence }}</p>
                <div v-else class="mt-1 overflow-x-auto">
                    <table class="t w-full min-w-[40rem] border-collapse text-sm">
                        <thead>
                            <tr>
                                <th class="text-left">Property</th>
                                <th v-for="race in chosen" :key="'a-' + race.id" class="text-left">{{ race.title }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in props.alignment.rows" :key="row.key" class="border-b border-rule-soft">
                                <th scope="row" class="py-2 pr-3 text-left font-medium text-ink">{{ row.label }}</th>
                                <td v-for="race in chosen" :key="'a-' + row.key + '-' + race.id" class="py-2 pr-3">
                                    <span v-if="cell(race, row.key, 'alignment')?.value">{{ cell(race, row.key, 'alignment')?.value }}</span>
                                    <span v-else class="text-ink-muted" :title="cell(race, row.key, 'alignment')?.title ?? undefined">N/A</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-if="alignmentAbsent.length > 0" class="mt-1.5 text-xs text-ink-muted">
                    Target alignment fields with no recorded target: {{ alignmentAbsent.join(', ') }}.
                </p>
            </template>
        </section>
    </CareerLayout>
</template>
