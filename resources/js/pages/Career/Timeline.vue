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
import PageHeader from '../../components/ui/PageHeader.vue';
import Note from '../../components/ui/Note.vue';
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
        position: string;
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


// The HFW's filters are two selects over the already-fetched list; both reset the page.
const kindFilter = ref('');
const yearFilter = ref('');
const page = ref(1);
const PAGE_SIZE = 15;

const kindOptions = computed(() => [...new Set(props.entries.map((entry) => entry.kind_label))].sort());

const yearOptions = computed(() => {
    const years = new Set<string>();
    for (const entry of props.entries) {
        const head = entry.position.split(' · ')[0];
        if (head !== 'After the grid') {
            years.add(head);
        }
    }

    return [...years];
});

const filteredEntries = computed(() =>
    props.entries.filter((entry) => {
        const kindOk = kindFilter.value === '' || entry.kind_label === kindFilter.value;
        const yearOk = yearFilter.value === '' || entry.position.startsWith(yearFilter.value);

        return kindOk && yearOk;
    }),
);

const pageCount = computed(() => Math.max(1, Math.ceil(filteredEntries.value.length / PAGE_SIZE)));
const visibleEntries = computed(() => filteredEntries.value.slice((page.value - 1) * PAGE_SIZE, page.value * PAGE_SIZE));

const clearFilters = (): void => {
    kindFilter.value = '';
    yearFilter.value = '';
    page.value = 1;
};

const energyOf = (entry: { after: Record<string, number | null> | null }): number | null => entry.after?.energy ?? null;
const spOf = (entry: { after: Record<string, number | null> | null }): number | null => entry.after?.sp ?? null;

const lastTurn = computed(() => Math.max(0, ...props.entries.map((entry) => entry.turn)));
const resultUrl = computed(() => props.run.cockpit_url.replace(/\/cockpit$/, '/result'));

const phaseCount = (head: string): number => props.entries.filter((entry) => entry.position.startsWith(head)).length;

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
        current="timeline"
    >
        <Head :title="'Career timeline: ' + props.run.trainee" />
        <template #head>
            <PageHeader
                eyebrow="Career timeline"
                title="Every logged turn, race and event"
                :description="props.run.trainee + '\u2019s ' + props.run.scenario_label + ' career, in order. ' + props.entries.length + ' entries recorded. This is the auditable record behind the Result screen.'"
            >
                <template #actions>
                    <a :href="resultUrl" class="inline-flex min-h-11 items-center rounded-md border border-rule bg-raised px-4 text-sm font-semibold text-ink hover:text-ink-strong">Career result</a>
                    <button type="button" class="btn btn-quiet min-h-11" title="The build has no undo; this screen proposes one.">Undo turn {{ lastTurn }}</button>
                    <a href="/training-runs" class="inline-flex min-h-11 items-center rounded-md border border-rule bg-raised px-4 text-sm font-semibold text-ink hover:text-ink-strong">All careers</a>
                </template>
            </PageHeader>
        </template>

        <p v-if="visiting" role="status" class="mb-3 text-sm text-ink-muted">Loading…</p>
        <p v-if="visitFailed" role="alert" class="mb-3 rounded-md border border-risk bg-raised px-3 py-2 text-sm text-risk">
            That screen did not load. Nothing was saved; try again.
        </p>

        <div class="grid gap-3 g-4">
            <div class="card"><div class="text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Turns recorded</div><div class="font-mono text-[22px] font-extrabold text-ink-strong">{{ recordedTurns }}</div></div>
            <div class="card"><div class="text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Races entered</div><div class="font-mono text-[22px] font-extrabold text-ink-strong">{{ raceCount }}</div></div>
            <div class="card"><div class="text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Events</div><div class="font-mono text-[22px] font-extrabold text-ink-strong">{{ eventCount }}</div></div>
            <div class="card"><div class="text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Finale</div><div class="text-base font-extrabold text-ink-strong">{{ finaleText }}</div></div>
        </div>

        <div class="mt-3.5 flex flex-wrap items-end gap-2.5">
            <label class="flex flex-col gap-1">
                <span class="text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Kind</span>
                <select v-model="kindFilter" name="kind" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-sm" @change="page = 1">
                    <option value="">All entries</option>
                    <option v-for="kind in kindOptions" :key="kind" :value="kind">{{ kind }}</option>
                </select>
            </label>
            <label class="flex flex-col gap-1">
                <span class="text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Year</span>
                <select v-model="yearFilter" name="year" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-sm" @change="page = 1">
                    <option value="">All years</option>
                    <option v-for="year in yearOptions" :key="year" :value="year">{{ year }}</option>
                </select>
            </label>
            <button type="button" class="btn btn-quiet min-h-11" @click="clearFilters">Clear</button>
        </div>

        <section class="card mt-3" aria-labelledby="timeline-record-heading">
            <h2 id="timeline-record-heading">The record</h2>
            <p v-if="props.entries.length === 0" class="mt-1 text-sm text-ink">{{ props.empty }}</p>
            <p v-else-if="filteredEntries.length === 0" class="mt-1 text-sm text-ink">No entry matches those filters.</p>
            <template v-else>
                <div class="overflow-x-auto">
                    <table class="t w-full min-w-[44rem] border-collapse text-sm">
                        <thead>
                            <tr><th class="text-right">Turn</th><th class="text-left">Position</th><th class="text-left">Kind</th><th class="text-left">What happened</th><th class="text-right">Energy</th><th class="text-right">SP</th></tr>
                        </thead>
                        <tbody>
                            <tr v-for="entry in visibleEntries" :key="entry.key" :class="entry.corrected ? 'is-current bg-green-tint' : ''" class="border-b border-rule-soft">
                                <td class="py-2 pr-3 text-right font-mono">{{ entry.turn }}</td>
                                <td class="py-2 pr-3">{{ entry.position }}</td>
                                <td class="py-2 pr-3">{{ entry.kind_label }}</td>
                                <td class="py-2 pr-3"><b>{{ entry.action_label }}</b> <span class="text-ink-muted">· {{ entry.result }}</span></td>
                                <td class="py-2 pr-3 text-right font-mono" title="This turn recorded no energy figure.">{{ energyOf(entry) ?? 'N/A' }}</td>
                                <td class="py-2 pr-3 text-right font-mono" title="This turn recorded no skill-point figure.">{{ spOf(entry) ?? 'N/A' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-sm">
                    <span class="text-ink-muted">{{ filteredEntries.length }} entries · showing {{ visibleEntries.length }}</span>
                    <div class="flex gap-2">
                        <button type="button" class="btn btn-sm min-h-11" :disabled="page <= 1" @click="page = Math.max(1, page - 1)">Previous</button>
                        <button type="button" class="btn btn-sm min-h-11" :disabled="page >= pageCount" @click="page = Math.min(pageCount, page + 1)">Next</button>
                    </div>
                </div>
            </template>
            <Note class="mt-2.5">
                Turn numbers are the run's own; the position is the calendar's label for that turn. Energy and SP print
                N/A where the import did not capture them: an N/A is "not recorded", never zero.
            </Note>
        </section>

        <section class="card mt-3.5" aria-labelledby="timeline-phase-heading">
            <h2 id="timeline-phase-heading">Phase grouping</h2>
            <p class="sub">Collapsed by phase, the shape a 72-turn run needs. The open phase is the one you are reading.</p>
            <details open class="mt-2">
                <summary class="flex min-h-11 cursor-pointer items-center text-sm font-extrabold text-ink-strong">Junior Year · {{ phaseCount('Junior') }} entries</summary>
                <p class="mt-1 text-xs text-ink-muted">Turns 1-24. Duration unknown for most entries; the Debut race is the one dated obligation.</p>
            </details>
            <details class="mt-2">
                <summary class="flex min-h-11 cursor-pointer items-center text-sm font-extrabold text-ink-strong">Classic Year · {{ phaseCount('Classic') }} entries</summary>
            </details>
            <details class="mt-2">
                <summary class="flex min-h-11 cursor-pointer items-center text-sm font-extrabold text-ink-strong">Senior Year · {{ phaseCount('Senior') }} entries</summary>
            </details>
            <details class="mt-2">
                <summary class="flex min-h-11 cursor-pointer items-center text-sm font-extrabold text-ink-strong">After the grid · finale block ({{ phaseCount('After the grid') }})</summary>
            </details>
        </section>
    </CareerLayout>
</template>
