<script setup lang="ts">
/*
 * One card: one of the career-goal groups the payload carries.
 *
 * Extracted from `CareerGoalsPanel.vue` for `S-RS-VISUAL-1`. That component rendered
 * `<section v-for="group in props.scenario.objectives">`, so Trainee goals and Mandatory races
 * arrived as two cards inside one component and could not be placed independently. The HFW's ruled
 * order puts them at positions 4 and 5, between cards from other components, so each group needs its
 * own mount point (`ScenarioCardGrid`).
 *
 * **The group is data, never a scenario name.** The same card draws Trainee goals, Mandatory races
 * and URA's Happy Meek without knowing which it holds, which is what keeps a fifth scenario a config
 * entry (gate G-33) and is why the split needed no scenario branch. Group order across cards is the
 * composer's, read from the payload's own group list.
 *
 * **The panel-level alerts are not here.** `CareerGoalsPanel` rendered `props.scenario.alerts` above
 * its sections, once for the whole panel; the composer keeps that slot immediately before the first
 * goal card, so their position relative to the goals is unchanged.
 */
import { computed } from 'vue';

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
    group: GoalGroup;
}>();

const glyphs: Record<string, string> = {
    completed: '●',
    current: '◉',
    upcoming: '○',
    missed: '×',
    not_reached: '○',
};

const words: Record<string, string> = {
    completed: 'Recorded',
    current: 'Due now',
    upcoming: 'Upcoming',
    missed: 'Missed',
    not_reached: 'Not reached',
};

/** A per-group id derived from the group's own name, so URA's three groups never collide. */
const headingId = computed((): string =>
    `goal-group-${props.group.group.replace(/\s+/g, '-').toLowerCase()}`,
);

const glyph = (state: string): string => glyphs[state] ?? '○';
const word = (state: string): string => words[state] ?? state;

const deadline = (row: GoalRow): string =>
    row.turn === null ? 'N/A' : `${row.year_label ?? 'N/A'} year, turn ${row.turn}`;

const deadlineTitle = (row: GoalRow): string | undefined =>
    row.turn === null
        ? (row.absence ?? 'The calendar records no turn for this goal.')
        : 'The career turn the calendar puts it at.';
</script>

<template>
    <section
        class="card flex flex-col gap-2 p-3"
        :aria-labelledby="headingId"
        :data-ladder-group="group.group"
    >
        <h2 :id="headingId" class="text-lg font-semibold text-ink-strong">{{ group.group }}</h2>

        <p v-if="group.rows.length === 0" class="text-sm text-ink-muted">
            {{ group.empty_note ?? 'No career goals recorded for this scenario yet.' }}
        </p>

        <ul v-else class="flex flex-col gap-2">
            <li
                v-for="row in group.rows"
                :key="row.label"
                data-ladder-row
                :data-state="row.state"
                class="flex flex-wrap items-baseline gap-x-2 gap-y-1 text-sm text-ink"
            >
                <span v-if="row.state !== null" aria-hidden="true" class="font-mono">{{ glyph(row.state) }}</span>
                <span class="font-medium">{{ row.label }}</span>
                <span v-if="row.state !== null" class="text-xs text-ink-muted">{{ word(row.state) }}</span>

                <span v-if="row.state !== null" class="text-xs text-ink-muted" :title="deadlineTitle(row)">
                    {{ deadline(row) }}
                </span>

                <template v-else>
                    <span class="text-xs text-ink-muted">N/A</span>
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

        <p v-if="group.summary !== null" class="text-xs font-semibold text-ink-strong">
            {{ group.summary }}
        </p>
    </section>
</template>
