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
        <p v-if="visitFailed" role="alert" class="mb-4 rounded border border-risk bg-raised px-3 py-2 text-sm text-risk">
            That screen did not load. Nothing was saved; try again.
        </p>

        <p class="max-w-3xl text-sm text-ink-muted">
            The run's recorded turns, races and events, oldest first. Each row's BEFORE stat block
            is the previous logged turn; EXPECTED and ACTUAL collapse because ADR-0003 stores the
            absolute end-of-turn value, not the expected-then-actual pair. A row marked "Updated"
            was edited after its first save; the run record screen is where the edit happened.
        </p>

        <section
            v-if="props.origin !== null"
            aria-labelledby="timeline-origin-heading"
            class="mt-4 rounded-md border border-rule bg-panel p-4"
        >
            <h2 id="timeline-origin-heading" class="text-base font-semibold text-ink-strong">Timeline opens here</h2>
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
            <h2 id="timeline-filter-heading" class="text-base font-semibold text-ink-strong">Filter by type</h2>
            <p class="mt-1 text-xs text-ink-muted">
                Toggle a type to hide its rows. Empty filter shows everything (showing {{ visibleCount }} of {{ props.entries.length }}).
            </p>
            <ul class="mt-3 flex flex-wrap gap-2" role="group" aria-label="Filter timeline rows by type">
                <li v-for="label in props.filters.available" :key="label">
                    <button
                        type="button"
                        class="enamel inline-flex min-h-11 items-center rounded-full border border-rule px-3 text-xs font-semibold aria-pressed:bg-pick aria-pressed:text-on-pick"
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

        <CareerTimeline v-else :entries="props.entries" :active-kinds="activeKinds" />

        <div class="mt-4 max-w-sm">
            <a :href="props.run.cockpit_url" class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised">
                Back to Cockpit
            </a>
        </div>
    </CareerLayout>
</template>
