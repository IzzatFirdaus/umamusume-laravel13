<script setup lang="ts">
/*
 * URA Finale mandatory race ladder panel.
 *
 * Registered against the `ura_mandatory_ladder` flag. Draws the client's
 * mandatory race record with state marks and ladder footer.
 */

import AlertRow from '../../../../components/scenario/AlertRow.vue';
import type { ProvenanceState } from '../../types';

type Badge = ProvenanceState;

interface LadderRow {
    label: string;
    state: string | null;
    year_label: string | null;
    turn: number | null;
    race_url: string | null;
    absence: string | null;
}

interface LadderGroup {
    group: string;
    rows: LadderRow[];
    summary: string | null;
    empty_note: string | null;
}

const props = defineProps<{
    scenario: {
        objectives: LadderGroup[];
        alerts: { level: string; text: string; detail: string | null }[];
    };
    label: string;
    kind: 'widget' | 'panel';
    name: string;
}>();

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

const headingId = (group: string): string =>
    `${props.name}-${group.replace(/\s+/g, '-').toLowerCase()}`;

const glyph = (state: string): string => glyphs[state] ?? '○';
const word = (state: string): string => words[state] ?? state;

const deadline = (row: LadderRow): string =>
    row.turn === null ? 'N/A' : `${row.year_label ?? 'N/A'} year, turn ${row.turn}`;

const deadlineTitle = (row: LadderRow): string | undefined =>
    row.turn === null
        ? (row.absence ?? 'The calendar records no turn for this race.')
        : 'The career turn the race calendar puts it at.';
</script>

<template>
    <div class="flex flex-col gap-4">
        <AlertRow
            v-for="alert in props.scenario.alerts"
            :key="alert.text"
            glyph="▲"
            :text="`${alert.level}: ${alert.text}`"
            :detail="alert.detail"
            tone="critical"
        />

        <section
            v-for="group in props.scenario.objectives"
            :key="group.group"
            class="flex flex-col gap-2"
            :aria-labelledby="headingId(group.group)"
        >
            <h3
                :id="headingId(group.group)"
                class="text-lg font-semibold text-ink-strong"
            >
                {{ group.group }}
            </h3>

            <p v-if="group.rows.length === 0" class="text-sm text-ink-muted">
                {{ group.empty_note ?? 'The calendar records no mandatory race for this scenario yet.' }}
            </p>

            <ul v-else class="flex flex-col gap-2">
                <li
                    v-for="row in group.rows"
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

            <p v-if="group.summary !== null" class="text-xs font-semibold text-ink-strong">
                {{ group.summary }}
            </p>
        </section>
    </div>
</template>