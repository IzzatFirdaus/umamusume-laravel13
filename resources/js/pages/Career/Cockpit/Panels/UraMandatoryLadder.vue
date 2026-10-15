<script setup lang="ts">
/*
 * The URA mandatory race ladder, extracted from UraPanel for Phase 7A.1.
 *
 * Registered against the `ura_mandatory_ladder` flag. Shows only the "Mandatory races" group from
 * the scenario's objectives, filtering out other goal categories. Reuses the same rendering logic
 * as CareerGoalsPanel but scoped to one group.
 */
import { computed } from 'vue';
import AlertRow from '@/components/scenario/AlertRow.vue';

interface GoalRow {
    label: string;
    state: string | null;
    year_label: string | null;
    turn: number | null;
    race_url: string | null;
    absence: string | null;
}

interface GoalGroup {
    group: string;
    rows: GoalRow[];
    summary: string | null;
    empty_note: string | null;
}

const props = defineProps<{
    scenario: {
        objectives: GoalGroup[];
    };
    label: string;
    kind: 'widget' | 'panel';
    name: string;
}>();

/** Filter to only the Mandatory races group, which is what URA's ladder is. */
const mandatoryGroup = computed(() =>
    props.scenario.objectives.find((g) => g.group === 'Mandatory races'),
);

const glyphs: Record<string, string> = {
    completed: '●',
    current: '◉',
    upcoming: '○',
    missed: '×',
};

const words: Record<string, string> = {
    completed: 'Recorded',
    current: 'Due now',
    upcoming: 'Upcoming',
    missed: 'Missed',
};

const glyph = (state: string): string => glyphs[state] ?? '○';
const word = (state: string): string => words[state] ?? state;

const deadline = (row: GoalRow): string =>
    row.turn === null ? 'N/A' : `${row.year_label ?? 'N/A'} year, turn ${row.turn}`;

const deadlineTitle = (row: GoalRow): string | undefined =>
    row.turn === null
        ? (row.absence ?? 'The calendar records no turn for this race.')
        : 'The career turn the race calendar puts it at.';
</script>

<template>
    <section v-if="mandatoryGroup" class="flex flex-col gap-2" aria-labelledby="ura-mandatory-ladder">
        <h3 id="ura-mandatory-ladder" class="text-lg font-semibold text-ink-strong">
            {{ mandatoryGroup.group }}
        </h3>

        <p v-if="mandatoryGroup.rows.length === 0" class="text-sm text-ink-muted">
            {{ mandatoryGroup.empty_note ?? 'The calendar records no mandatory race for this scenario yet.' }}
        </p>

        <ul v-else class="flex flex-col gap-2">
            <li
                v-for="row in mandatoryGroup.rows"
                :key="row.label"
                class="flex flex-wrap items-baseline gap-x-2 gap-y-1 text-sm text-ink"
            >
                <span v-if="row.state !== null" aria-hidden="true" class="font-mono">{{ glyph(row.state) }}</span>
                <span class="font-medium">{{ row.label }}</span>
                <span v-if="row.state !== null" class="text-xs text-ink-muted">{{ word(row.state) }}</span>

                <span v-if="row.state !== null" class="text-xs text-ink-muted" :title="deadlineTitle(row)">
                    {{ deadline(row) }}
                </span>

                <template v-else>
                    <span class="text-xs text-ink-muted" :title="row.absence ?? undefined">N/A</span>
                    <span v-if="row.absence !== null" class="w-full text-xs text-ink-muted">
                        {{ row.absence }}
                    </span>
                </template>

                <a
                    v-if="row.race_url !== null"
                    :href="row.race_url"
                    class="inline-flex min-h-11 items-center text-xs font-medium text-ink-strong underline"
                >
                    Record this race
                </a>
            </li>
        </ul>

        <p v-if="mandatoryGroup.summary !== null" class="text-xs font-semibold text-ink-strong">
            {{ mandatoryGroup.summary }}
        </p>
    </section>
</template>
