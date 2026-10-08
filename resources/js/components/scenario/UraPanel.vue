<script setup lang="ts">
/*
 * SCREEN-014's panel: career goals, the mandatory race record, and Happy Meek
 * (plan §9 E2, `SCR-CAR-020`).
 *
 * Registered against the `career_goals` flag, so it draws for any scenario whose matrix turns that
 * flag on and for none that leaves it off. It never reads `scenario.label` and never compares a
 * scenario name: every string it prints comes from the rows the controller composed (gate G-33).
 *
 * **Three of the five components this screen names are absences, and the panel says so in the open
 * rather than filling them in.** No table holds a trainee's per-character goals, and nothing stores a
 * Happy Meek level, so those rows arrive with no reading and render `N/A` beside the sentence that
 * explains why (design-2.0 §29, D-220). What the repository really holds is the mandatory race set,
 * and that is the one module with numbers in it.
 *
 * The state mark is glyph **plus word**, never a colour: `●` recorded, `◉` due now, `○` upcoming,
 * `×` missed. `◉`/`○` were reserved for Phase E deadlines by `SCR-CAR-017`, and this is the slice
 * that spends them.
 */

import AlertRow from './AlertRow.vue';

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
}

interface GoalAlert {
    level: string;
    text: string;
    detail: string | null;
}

const props = defineProps<{
    scenario: {
        objectives: GoalGroup[];
        alerts: GoalAlert[];
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

/**
 * A heading id derived from the flag this renderer was registered against, never from a scenario
 * name: the panel key is data, and §7 treats a hardcoded scenario name in a component as a defect.
 */
const headingId = (group: string): string =>
    `${props.name}-${group.replace(/\s+/g, '-').toLowerCase()}`;

const glyph = (state: string): string => glyphs[state] ?? '○';
const word = (state: string): string => words[state] ?? state;

/** The calendar position, or the absence of one: a finale block has a year and no turn. */
const deadline = (row: GoalRow): string =>
    row.turn === null ? 'N/A' : `${row.year_label ?? 'N/A'} year, turn ${row.turn}`;

const deadlineTitle = (row: GoalRow): string | undefined =>
    row.turn === null
        ? (row.absence ?? 'The calendar records no turn for this race.')
        : 'The career turn the race calendar puts it at.';
</script>

<template>
    <div class="flex flex-col gap-4">
        <!-- design-2.0 §4's Level 1 asks for prominent placement, so the alerts lead. -->
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
                class="text-xs font-semibold uppercase tracking-wide text-ink-muted"
            >
                {{ group.group }}
            </h3>

            <p v-if="group.rows.length === 0" class="text-sm text-ink-muted">
                The calendar records no mandatory race for this scenario yet.
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

                    <!-- An unrecorded reading is `N/A` with the reason visible too: a `title` alone is
                         not reliably announced, and the sentence is the screen's actual content. -->
                    <template v-else>
                        <span class="text-xs text-ink-muted" :title="row.absence ?? undefined">N/A</span>
                        <span v-if="row.absence !== null" class="w-full text-xs text-ink-muted">
                            {{ row.absence }}
                        </span>
                    </template>

                    <!-- App copy, no client source: the client prints no string for this door, so the
                         tool's own words are used and must read as the tool's. If a client string is
                         ever sourced, it replaces this label rather than the link. -->
                    <a
                        v-if="row.race_url !== null"
                        :href="row.race_url"
                        class="inline-flex min-h-11 items-center text-xs font-medium text-ink-strong underline"
                    >
                        Record this race
                    </a>
                </li>
            </ul>
        </section>
    </div>
</template>
