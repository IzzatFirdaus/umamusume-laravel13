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
import ProvenanceBadge from '../../components/ProvenanceBadge.vue';
import RacePlanList from '../../components/career/RacePlanList.vue';
import AlertRow from '../../components/scenario/AlertRow.vue';
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
    status: string | null;
    placement: string | null;
    fans_gain: number | null;
    grade_points: number | null;
    comparison: Cell[];
    alignment: Cell[];
}

interface Group {
    key: string;
    label: string;
    definition: string;
    empty: string | null;
    races: Race[];
}

const props = defineProps<{
    run: { id: number; trainee: string; trainee_ja: string | null; scenario_label: string; status_label: string; run_url: string };
    nextTurn: { year_label: string; turn: number } | null;
    groups: { mandatory: Group; upcoming: Group; optional: Group; rival: Group } | null;
    comparison: { rows: { key: string; label: string }[] };
    alignment: { recorded: boolean; absence: string | null; rows: { key: string; label: string }[] };
    held: { win_probability: { label: string; title: string }; expected_risk: { label: string; title: string } };
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
</script>

<template>
    <CareerLayout
        :trainee="props.run.trainee"
        :scenario-label="props.run.scenario_label"
        :status-label="props.run.status_label"
        :run-url="props.run.run_url"
    >
        <Head :title="`Race planner: ${props.run.trainee}`" />
        <template #title>Race planner</template>

        <p v-if="visiting" role="status" class="mb-4 text-sm text-ink-muted">Loading…</p>
        <p v-if="visitFailed" role="alert" class="mb-4 rounded border border-risk bg-raised px-3 py-2 text-sm text-risk">
            That screen did not load. Nothing was saved; try again.
        </p>

        <section aria-labelledby="race-planner-position-heading" class="rounded-md border border-rule bg-panel p-4">
            <h2 id="race-planner-position-heading" class="text-base font-semibold text-ink-strong">Planning from</h2>
            <p v-if="props.nextTurn !== null" class="mt-1 text-sm text-ink">
                {{ props.nextTurn.year_label }} ·
                <span class="font-mono tabular-nums">Turn {{ props.nextTurn.turn }}</span>
            </p>
            <p v-else class="mt-1 text-sm text-ink" title="No turn has been logged on this run, so there is no position to plan from.">
                N/A
            </p>
        </section>

        <!-- The two figures the brief asks for that this tool must not state. Each carries the reason
             in its `title`, and neither is a band, a percentage or a number. -->
        <section aria-labelledby="race-planner-held-heading" class="mt-4 rounded-md border border-rule bg-panel p-4">
            <h2 id="race-planner-held-heading" class="text-base font-semibold text-ink-strong">Held figures</h2>
            <dl class="mt-2 flex flex-col gap-1 text-sm">
                <div class="flex flex-wrap items-baseline gap-x-2">
                    <dt class="text-ink">Win probability</dt>
                    <dd class="font-semibold text-ink-strong" :title="props.held.win_probability.title">
                        {{ props.held.win_probability.label }}
                    </dd>
                </div>
                <div class="flex flex-wrap items-baseline gap-x-2">
                    <dt class="text-ink">Expected risk</dt>
                    <dd class="font-semibold text-ink-strong" :title="props.held.expected_risk.title">
                        {{ props.held.expected_risk.label }}
                    </dd>
                </div>
            </dl>
        </section>

        <!-- Level 1, Critical (design-2.0 §4): a glyph plus the word, never colour alone. It is raised
             by the calendar — the obligation's turn has arrived and nothing is recorded against it —
             and the region otherwise states only the next obligation and its turn. -->
        <section v-if="props.deadline.next !== null" aria-labelledby="race-planner-deadline-heading" class="mt-4 rounded-md border border-rule bg-panel p-4">
            <h2 id="race-planner-deadline-heading" class="text-base font-semibold text-ink-strong">Deadlines</h2>
            <AlertRow
                v-if="props.deadline.alert !== null"
                glyph="▲"
                :text="props.deadline.alert.text"
                :detail="props.deadline.alert.detail"
                tone="critical"
            />
            <p class="mt-2 text-sm text-ink">
                Next mandatory race:
                <span class="font-semibold text-ink-strong">{{ props.deadline.next.title }}</span>,
                {{ props.deadline.next.year_label }}
                <template v-if="props.deadline.next.turn !== null"> turn {{ props.deadline.next.turn }}</template>
                <template v-else> (the post-December block)</template>.
                It reads "{{ props.deadline.next.state }}".
            </p>
            <p class="mt-1 text-xs text-ink-muted">
                This states the next obligation the run has not cleared. Whether the rest of the career
                keeps its deadlines is not something this tool predicts.
            </p>
        </section>

        <!-- No calendar at all: one sentence naming what is missing and what to do. -->
        <p
            v-if="props.empty !== null"
            class="mt-4 rounded-md border border-dashed border-rule bg-raised p-4 text-sm text-ink"
        >
            {{ props.empty }}
        </p>
        <template v-else>
            <RacePlanList
                v-for="group in orderedGroups"
                :key="group.key"
                :group="group"
                :readiness="props.held.win_probability"
                :entry="props.entry"
                :skip-url="props.run.run_url"
                :selected="selected"
                :limit="LIMIT"
                @toggle="toggle"
            />

            <section aria-labelledby="race-planner-comparison-heading" class="mt-6">
                <h2 id="race-planner-comparison-heading" class="text-base font-semibold text-ink-strong">
                    Reward comparison
                </h2>

                <p
                    v-if="chosen.length < 2"
                    class="mt-2 rounded-md border border-dashed border-rule bg-raised p-4 text-sm text-ink"
                >
                    Nothing to compare yet. Pick two to four races with the "Compare" control on any
                    card above and their rewards line up here, one property per row.
                </p>
                <template v-else>
                    <!-- The scroll container carries the focus stop rather than the table, so a screen
                         reader reads a table and 320px can still reach every column (WCAG 1.4.10). -->
                    <div
                        role="region"
                        aria-label="Selected races, rewards side by side"
                        tabindex="0"
                        class="mt-3 overflow-x-auto rounded-md border border-rule"
                    >
                        <table class="w-full min-w-[40rem] border-collapse bg-raised text-sm">
                            <caption class="px-3 py-2 text-left text-xs text-ink-muted">
                                Recorded and sourced values only. A cell with no source behind it reads
                                N/A, and its title says why.
                            </caption>
                            <thead>
                                <tr class="border-b border-rule">
                                    <th scope="col" class="px-3 py-2 text-left font-semibold text-ink-strong">Property</th>
                                    <th
                                        v-for="race in chosen"
                                        :key="race.id"
                                        scope="col"
                                        class="px-3 py-2 text-left font-semibold text-ink-strong"
                                    >
                                        {{ race.title }}
                                        <span class="block text-xs font-normal text-ink-muted">
                                            {{ race.year_label }}
                                            <template v-if="race.turn !== null"> · Turn {{ race.turn }}</template>
                                        </span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in props.comparison.rows" :key="row.key" class="border-b border-rule">
                                    <th scope="row" class="px-3 py-2 text-left font-medium text-ink">{{ row.label }}</th>
                                    <td
                                        v-for="race in chosen"
                                        :key="race.id"
                                        class="px-3 py-2 text-ink"
                                        :title="cell(race, row.key, 'comparison')?.title ?? undefined"
                                    >
                                        {{ cell(race, row.key, 'comparison')?.value ?? 'N/A' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h3 class="mt-6 flex flex-wrap items-center gap-x-2 text-base font-semibold text-ink-strong">
                        Target alignment
                        <!-- Calculated: the cell is derived from two recorded values, so it is a
                             comparison rather than a reading. Never a score and never a percentage. -->
                        <ProvenanceBadge
                            state="calculated"
                            title="Derived by comparing the race's own catalogue values with the run's recorded build target."
                        />
                    </h3>
                    <p class="mt-1 text-sm text-ink-muted">
                        {{ props.alignment.absence ?? 'Each race against the build target this run records.' }}
                    </p>

                    <div
                        role="region"
                        aria-label="Selected races against the build target"
                        tabindex="0"
                        class="mt-3 overflow-x-auto rounded-md border border-rule"
                    >
                        <table class="w-full min-w-[40rem] border-collapse bg-raised text-sm">
                            <thead>
                                <tr class="border-b border-rule">
                                    <th scope="col" class="px-3 py-2 text-left font-semibold text-ink-strong">Property</th>
                                    <th
                                        v-for="race in chosen"
                                        :key="race.id"
                                        scope="col"
                                        class="px-3 py-2 text-left font-semibold text-ink-strong"
                                    >
                                        {{ race.title }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in props.alignment.rows" :key="row.key" class="border-b border-rule">
                                    <th scope="row" class="px-3 py-2 text-left font-medium text-ink">{{ row.label }}</th>
                                    <td
                                        v-for="race in chosen"
                                        :key="race.id"
                                        class="px-3 py-2 text-ink"
                                        :title="cell(race, row.key, 'alignment')?.title ?? undefined"
                                    >
                                        {{ cell(race, row.key, 'alignment')?.value ?? 'N/A' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </template>
            </section>
        </template>
    </CareerLayout>
</template>
