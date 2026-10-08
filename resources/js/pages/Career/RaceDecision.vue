<script setup lang="ts">
/*
 * The Race Decision, `SCR-CAR-013` (SCREEN-011, plan §8 D10). Whether and where to race, at the turn
 * being decided.
 *
 * The page pattern is `Catalog/Index.vue`'s: `CareerLayout`, `<Head title>`, the `#title` slot, and
 * Inertia `Link` for the routes `spa.ts` resolves. The only write is inside `RaceCard`'s drawer, and
 * it posts to the existing `runs.races.store` through `useForm`, so this page holds no form of its
 * own.
 *
 * Loading and error are page-level (ADR-0007): the page's only user-initiated async action is the
 * drawer's Enter Race, which carries its own in-flight label, so Inertia's visit state is announced
 * rather than drawn as a skeleton. `invalid` and `exception` are both cancelable, and returning
 * `false` takes the failure out of Inertia's default modal so this page's own `role="alert"` is the
 * single surface.
 *
 * Every figure is a catalogue fact, an entered value, or a named absence. The brief's readiness band
 * and win figure are held on `ADR-0016` and print `N/A` with the reason.
 */
import CareerLayout from '../../layouts/CareerLayout.vue';
import RaceCard from '../../components/career/RaceCard.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useVisitState } from '../../composables/useVisitState';

interface Fact {
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
    facts: Fact[];
    status: string | null;
    placement: string | null;
    fans_gain: number | null;
    grade_points: number | null;
}

const props = defineProps<{
    run: { id: number; trainee: string; trainee_ja: string | null; scenario_label: string; status_label: string; run_url: string };
    nextTurn: { year_label: string; turn: number; position_label: string } | null;
    races: Race[];
    deadlines: { id: number; title: string; year_label: string; turn: number | null; state: string; is_next: boolean; reached: boolean }[];
    readiness: { label: string; title: string };
    entry: { action: string; turns: { id: number; turn: number }[] };
    planner_url: string;
    empty: string | null;
}>();

const { visiting, visitFailed } = useVisitState();

// The next obligation is the Level 1 item; the rest are context, so only one row is emphasised.
const nextDeadline = computed(() => props.deadlines.find((row) => row.is_next) ?? null);
</script>

<template>
    <CareerLayout
        :trainee="props.run.trainee"
        :scenario-label="props.run.scenario_label"
        :status-label="props.run.status_label"
        :run-url="props.run.run_url"
    >
        <Head :title="`Race decision: ${props.run.trainee}`" />
        <template #title>Race decision</template>

        <p v-if="visiting" role="status" class="mb-4 text-sm text-ink-muted">Loading…</p>
        <p v-if="visitFailed" role="alert" class="mb-4 rounded border border-risk bg-raised px-3 py-2 text-sm text-risk">
            That screen did not load. Nothing was saved; try again.
        </p>

        <section aria-labelledby="race-turn-heading" class="rounded-md border border-rule bg-panel p-4">
            <h2 id="race-turn-heading" class="text-base font-semibold text-ink-strong">The turn being decided</h2>
            <p v-if="props.nextTurn !== null" class="mt-1 text-sm text-ink">
                {{ props.nextTurn.year_label }} ·
                {{ props.nextTurn.position_label }} ·
                <span class="font-mono tabular-nums">Turn {{ props.nextTurn.turn }}</span>
            </p>
            <p v-else class="mt-1 text-sm text-ink" title="No turn is being decided on this run.">N/A</p>
        </section>

        <!-- Level 1, Critical (design-2.0 §4): a career obligation gets strong emphasis, a glyph and a
             clear word. The glyph is aria-hidden and the word carries the meaning, so the state never
             rests on colour alone. -->
        <section
            v-if="props.deadlines.length > 0"
            aria-labelledby="race-deadlines-heading"
            class="mt-4 rounded-md border-2 border-goal-line bg-raised p-4"
        >
            <h2 id="race-deadlines-heading" class="text-base font-semibold text-ink-strong">
                <span aria-hidden="true" class="font-mono font-bold text-goal-line">!</span>
                Mandatory races
            </h2>
            <p class="mt-1 text-sm text-ink">
                The races this scenario obliges whichever trainee is running. A mandatory race is a
                career rule; it is not the same claim as a Goal, which is per-trainee and has no source here.
            </p>

            <ol class="mt-3 flex flex-col gap-1.5">
                <li
                    v-for="deadline in props.deadlines"
                    :key="deadline.id"
                    class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1 rounded-md border border-rule bg-panel px-3 py-2 text-sm"
                >
                    <span class="min-w-0 flex-1">
                        <span class="font-semibold text-ink-strong">{{ deadline.title }}</span>
                        <span class="ml-1.5 text-xs text-ink-muted">
                            {{ deadline.year_label }}
                            <template v-if="deadline.turn !== null"> · Turn {{ deadline.turn }}</template>
                        </span>
                        <span v-if="deadline.is_next" class="ml-1.5 text-xs font-bold text-ink-strong">
                            <span aria-hidden="true">!</span> NEXT
                        </span>
                    </span>
                    <span class="shrink-0 text-xs font-bold text-ink-strong">{{ deadline.state }}</span>
                </li>
            </ol>
        </section>

        <p v-if="nextDeadline !== null" class="mt-2 text-sm text-ink">
            <span aria-hidden="true" class="font-mono font-bold text-goal-line">!</span>
            Next obligation: <span class="font-semibold text-ink-strong">{{ nextDeadline.title }}</span>,
            {{ nextDeadline.year_label }} turn {{ nextDeadline.turn }}. It reads "{{ nextDeadline.state }}".
        </p>

        <section aria-labelledby="race-readiness-heading" class="mt-4 rounded-md border border-rule bg-panel p-4">
            <h2 id="race-readiness-heading" class="text-base font-semibold text-ink-strong">Readiness</h2>
            <p class="mt-1 text-sm text-ink">
                <span class="font-semibold text-ink-strong" :title="props.readiness.title">{{ props.readiness.label }}</span>
                <span class="ml-2 text-ink-muted">No readiness band is computed for this race.</span>
            </p>
        </section>

        <section aria-labelledby="race-list-heading" class="mt-4">
            <h2 id="race-list-heading" class="text-base font-semibold text-ink-strong">Races to decide between</h2>
            <!-- The door to the Scenario Race Planner, which draws the whole calendar rather than this
                 turn. A screen reachable only by URL is a defect (D14). -->
            <p class="mt-1 text-sm">
                <Link :href="props.planner_url" class="inline-flex min-h-11 items-center rounded-md px-3 font-semibold text-ink underline">
                    Plan the whole calendar
                </Link>
            </p>

            <p
                v-if="props.empty !== null"
                class="mt-2 rounded-md border border-dashed border-rule bg-raised p-4 text-sm text-ink"
            >
                {{ props.empty }}
            </p>
            <ul v-else class="mt-3 flex flex-col gap-3">
                <RaceCard
                    v-for="race in props.races"
                    :key="race.id"
                    :race="race"
                    :readiness="props.readiness"
                    :entry="props.entry"
                    :skip-url="props.run.run_url"
                />
            </ul>
        </section>
    </CareerLayout>
</template>
