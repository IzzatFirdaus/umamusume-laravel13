<script setup lang="ts">
/*
 * The Career Timeline, `SCR-CAR-017` (SCREEN-018, plan §8 D14). A read screen that flattens the
 * run's turns, races and events into one ordered rail. No new write route; every value on the page
 * comes from data the existing routes already own.
 *
 * **Page pattern is `Career/RaceDecision.vue`'s**: `CareerLayout`, `<Head title>` and the `#title`
 * slot, page-level loading and error states. Filter chips are local state and remove nothing from
 * the server payload.
 *
 * Props contract is declared locally: TypeScript 7 ships no `lib/typescript.js` for
 * `@vue/compiler-sfc` to load (plan §11).
 */
import CareerLayout from '../../layouts/CareerLayout.vue';
import CareerTimeline from '../../components/career/CareerTimeline.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useVisitState } from '../../composables/useVisitState';

const props = defineProps<{
    run: { id: number; trainee: string; trainee_ja: string | null; scenario_label: string; status_label: string; run_url: string; cockpit_url: string };
    origin: { year_label: string; month_label: string; turn: number; imported: boolean } | null;
    entries: {
        key: string;
        kind: string;
        kind_label: string;
        glyph: '●' | '◉' | '○' | '×';
        turn: number;
        action_label: string;
        before: Record<string, number | null> | null;
        after: Record<string, number | null> | null;
        expected: Record<string, number | null> | null;
        actual: Record<string, number | null> | null;
        mood: string | null;
        result: string;
        corrected: boolean;
        correction_id: string | null;
        decision_url: string;
    }[];
    filters: { available: string[]; active: string[] };
    empty: string | null;
    /** DM-1: `CareerCalendar::TURNS_PER_CAREER` (strictly 72). The finale sits outside it. */
    total_turns: number;
    /** The scenario's finale as `FinaleReader::forRun()` carries it, or null when the calendar has no row. */
    finale: { reached: boolean; outcome: string | null; placement: string | null; turn: number | null; label: string } | null;
}>();

const { visiting, visitFailed } = useVisitState();

// Filter state is local-only: the rail is a presentation over an already-fetched list.
const activeKinds = ref<string[]>([]);

const toggleKind = (label: string): void => {
    if (activeKinds.value.includes(label)) {
        activeKinds.value = activeKinds.value.filter((existing) => existing !== label);
    } else {
        activeKinds.value = [...activeKinds.value, label];
    }
};

const filterCount = computed(() => activeKinds.value.length);
const visibleCount = computed(() =>
    props.entries.filter((entry) => activeKinds.value.length === 0 || activeKinds.value.includes(entry.kind_label)).length,
);

/**
 * The counts the HFW's strip prints, read off the payload rather than re-queried. Turns recorded is
 * the career's own denominator (`DM-1`), so a half-logged career reads as what it is.
 */
const recordedTurns = computed(() => props.entries.filter((entry) => entry.kind === 'turn').length);
const raceCount = computed(() => props.entries.filter((entry) => entry.kind === 'race').length);
const eventCount = computed(() => props.entries.filter((entry) => entry.kind === 'event').length);

const finaleText = computed(() => {
    if (props.finale === null) {
        return 'No finale row';
    }

    if (!props.finale.reached) {
        return 'Not reached';
    }

    return props.finale.outcome === null ? 'Reached, not recorded' : [props.finale.outcome, props.finale.placement].filter(Boolean).join(' · ');
});
</script>

<template>
    <CareerLayout
        :trainee="props.run.trainee"
        :scenario-label="props.run.scenario_label"
        :status-label="props.run.status_label"
        :run-url="props.run.run_url"
    >
        <Head :title="`Career timeline: ${props.run.trainee}`" />
        <template #title>Career timeline</template>

        <p v-if="visiting" role="status" class="mb-4 text-sm text-ink-muted">Loading…</p>
        <p v-if="visitFailed" role="alert" class="mb-4 rounded-md border border-risk bg-raised px-3 py-2 text-sm text-risk">
            That screen did not load. Nothing was saved; try again.
        </p>

        <p class="max-w-3xl text-sm text-ink-muted">
            The run's recorded turns, races and events, oldest first. Each row's BEFORE stat block
            is the previous logged turn; the END OF TURN block is the row's own absolute values, because
            each turn stores the end-of-turn reading rather than an expected-then-actual pair. A row
            marked "Updated" was edited after its first save; the Cockpit is where the edit happened.
        </p>

        <!-- The HFW's four counts, read off the payload. No aggregate and no score: four facts, one per
             tile, and the final tile is the finale's own state rather than a derived grade. -->
        <section aria-labelledby="timeline-counts-heading" class="mt-4">
            <h2 id="timeline-counts-heading" class="sr-only">What this career has recorded</h2>
            <dl class="grid grid-cols-2 gap-3 min-[1081px]:grid-cols-4">
                <div class="rounded-md border border-rule bg-panel p-3">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Turns recorded</dt>
                    <dd class="mt-1 font-mono text-xl font-semibold tabular-nums text-ink-strong">
                        {{ recordedTurns }} <span class="text-sm font-normal text-ink-muted">of {{ props.total_turns }}</span>
                    </dd>
                </div>
                <div class="rounded-md border border-rule bg-panel p-3">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Races entered</dt>
                    <dd class="mt-1 font-mono text-xl font-semibold tabular-nums text-ink-strong">{{ raceCount }}</dd>
                </div>
                <div class="rounded-md border border-rule bg-panel p-3">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Events</dt>
                    <dd class="mt-1 font-mono text-xl font-semibold tabular-nums text-ink-strong">{{ eventCount }}</dd>
                </div>
                <div class="rounded-md border border-rule bg-panel p-3">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Finale</dt>
                    <dd class="mt-1 text-base font-semibold text-ink-strong">{{ finaleText }}</dd>
                </div>
            </dl>
        </section>

        <section
            v-if="props.origin !== null"
            aria-labelledby="timeline-origin-heading"
            class="mt-4 rounded-md border border-rule bg-panel p-4"
        >
            <h2 id="timeline-origin-heading" class="text-lg font-semibold text-ink-strong">Timeline opens here</h2>
            <p class="mt-1 text-sm text-ink">
                {{ props.origin.year_label }} · {{ props.origin.month_label }} · Turn {{ props.origin.turn }}
                <span v-if="props.origin.imported" class="ml-2 text-xs text-ink-muted">imported from a snapshot</span>
            </p>
        </section>

        <section
            v-if="props.filters.available.length > 0"
            aria-labelledby="timeline-filter-heading"
            class="mt-4 rounded-md border border-rule bg-panel p-4"
        >
            <h2 id="timeline-filter-heading" class="text-lg font-semibold text-ink-strong">Filter by type</h2>
            <p class="mt-1 text-xs text-ink-muted">
                Toggle a type to hide its rows. Empty filter shows everything (showing {{ visibleCount }} of {{ props.entries.length }}).
            </p>
            <ul class="mt-3 flex flex-wrap gap-2" role="group" aria-label="Filter timeline rows by type">
                <li v-for="label in props.filters.available" :key="label">
                    <button
                        type="button"
                        class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-xs font-semibold aria-pressed:bg-pick aria-pressed:text-on-pick"
                        :aria-pressed="activeKinds.includes(label)"
                        @click="toggleKind(label)"
                    >
                        {{ label }}
                    </button>
                </li>
            </ul>
            <p v-if="filterCount > 0" class="mt-2 text-xs text-ink-muted">
                {{ filterCount }} filter{{ filterCount === 1 ? '' : 's' }} active.
            </p>
        </section>

        <p
            v-if="props.empty !== null"
            class="mt-4 rounded-md border border-dashed border-rule bg-raised p-4 text-sm text-ink"
        >
            {{ props.empty }}
        </p>

        <CareerTimeline v-else :entries="props.entries" :active-kinds="activeKinds" :total-turns="props.total_turns" />

        <!-- DM-1: the finale is the block after the grid, so it is its own section and never a row
             numbered 73. -->
        <!-- DOMAIN OVERRIDE (DM-1): finale block sits outside the 72-turn grid -->
        <section aria-labelledby="timeline-finale-heading" class="mt-4 rounded-md border border-rule bg-panel p-4">
            <h2 id="timeline-finale-heading" class="text-lg font-semibold text-ink-strong">After the grid · finale block</h2>
            <template v-if="props.finale !== null">
                <p class="mt-1 text-sm text-ink">
                    <span class="font-semibold">{{ props.finale.label ?? 'This scenario has no label for it' }}</span>
                    · {{ props.finale.reached ? 'reached' : 'not reached yet' }}
                </p>
                <p class="mt-1 text-sm text-ink">
                    {{ props.finale.reached
                        ? props.finale.outcome === null
                            ? 'Reached, with no outcome recorded against it. The Result screen prints the same absence.'
                            : `Outcome: ${props.finale.outcome}${props.finale.placement === null ? '' : ` · ${props.finale.placement}`}`
                        : 'The grid has not run out yet, so nothing has been recorded here.' }}
                </p>
                <p class="mt-1 text-xs text-ink-muted">
                    The twelve months of each year are 24 turns; the finale follows Senior Late December and
                    is not one of the {{ props.total_turns }} numbered turns.
                </p>
            </template>
            <p v-else class="mt-1 text-sm text-ink-muted">
                This scenario declares no finale row in the career calendar, so there is nothing to report here.
                That is an absence in the catalogue, not a missing result.
            </p>
        </section>

        <div class="mt-4 max-w-sm">
            <a :href="props.run.cockpit_url" class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised transition-colors duration-150 ease-out">
                Back to Cockpit
            </a>
        </div>
    </CareerLayout>
</template>
