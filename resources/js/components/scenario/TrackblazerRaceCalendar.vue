<script setup lang="ts">
/*
 * The three races this run has coming up (G5, plan §17), grouped by the career period they fall in.
 *
 * **Why this exists when the panel already lists races.** The block above it, "Rival races and
 * schedule", prints the scenario's catalogue as a reference and caps at eight rows. This one is
 * windowed against the turn the run is actually deciding, drops rows the run already entered, and
 * carries the two figures each race is worth. `race_calendar` is off for Trackblazer in the matrix,
 * so the run strip prints its own "ahead" region for the scenarios that turn it on and nothing here:
 * this is the only surface where a Trackblazer run sees what is still to come.
 *
 * **The values are published rules, not prospects.** A win at G2 pays 80 Grade Points whether or not
 * this trainee can win it, and coins do not move with the grade at all
 * (`docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md:372`). Nothing here compares her aptitudes
 * to the race, which is the shape `ADR-0016` holds and `PRD.md` §6.11 keeps out.
 *
 * Every figure and sentence is composed in
 * `App\Http\Controllers\Career\CockpitController::calendarSection()`; this file holds no numbers.
 *
 * At 320px each row stacks: the label line, then the values, then the consequence. The list never
 * scrolls sideways.
 */
import { computed } from 'vue';
import ProvenanceBadge from '../ProvenanceBadge.vue';

type Badge = 'confirmed' | 'calculated' | 'estimated' | 'unknown' | null;

interface CalendarRow {
    title: string;
    tier: string | null;
    year_label: string;
    turn: number | null;
    turns_away: string | null;
    distance: string;
    surface: string | null;
    surface_title: string | null;
    grade_points_win: string | null;
    grade_points_badge: Badge;
    grade_points_title: string;
    coins_win: string | null;
    coins_badge: Badge;
    coins_title: string;
    skip_consequence: string;
    skip_badge: 'confirmed' | 'calculated' | 'estimated' | 'unknown';
    skip_title: string;
}

interface CalendarSection {
    window: number;
    turns: { year: number; year_label: string; turn: number } | null;
    rows: CalendarRow[];
    absence: string | null;
}

const props = defineProps<{
    calendar: CalendarSection;
}>();

const headingId = 'trackblazer-race-calendar';

/**
 * The rows in the order the server sent them, bucketed by the career period a race falls in.
 * `ponytail:` a linear scan rather than a keyed reduce, because the window is three rows and they
 * arrive already sorted; a Map would only matter if the window grew.
 */
const groups = computed((): { label: string; rows: CalendarRow[] }[] => {
    const out: { label: string; rows: CalendarRow[] }[] = [];

    for (const row of props.calendar.rows) {
        const last = out[out.length - 1];

        if (last !== undefined && last.label === row.year_label) {
            last.rows.push(row);

            continue;
        }

        out.push({ label: row.year_label, rows: [row] });
    }

    return out;
});
</script>

<template>
    <section :aria-labelledby="headingId" class="flex flex-col gap-3 rounded-md border border-rule bg-panel p-4">
        <h4 :id="headingId" class="text-base font-semibold text-ink-strong">
            Races ahead
        </h4>

        <p v-if="props.calendar.turns !== null" class="text-xs text-ink-muted">
            The {{ props.calendar.window }} catalogue rows after
            {{ props.calendar.turns.year_label }}, turn {{ props.calendar.turns.turn }}
            that this run has not entered.
        </p>

        <p v-if="props.calendar.absence !== null" class="text-sm text-ink-muted" role="status">
            {{ props.calendar.absence }}
        </p>

        <ol v-else class="flex flex-col gap-3" aria-label="Races still to come">
            <li v-for="group in groups" :key="group.label" class="flex flex-col gap-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">
                    {{ group.label }}
                </p>
                <ol class="flex flex-col gap-2" :aria-label="`Races ahead in ${group.label}`">
                    <li
                        v-for="row in group.rows"
                        :key="`${row.year_label}-${row.turn}-${row.title}`"
                        class="rounded-md border border-rule bg-raised px-3 py-2"
                    >
                        <p class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                            <span class="text-sm font-medium text-ink-strong">{{ row.title }}</span>
                            <span class="font-mono text-xs text-ink-muted">
                                {{ row.tier ?? 'grade N/A' }}
                                · {{ row.distance }}
                                · <span :title="row.surface_title ?? undefined">{{ row.surface ?? 'surface N/A' }}</span>
                                <template v-if="row.turns_away !== null">
                                    · in {{ row.turns_away }} {{ row.turns_away === '1' ? 'turn' : 'turns' }}
                                </template>
                            </span>
                        </p>

                        <p class="mt-1 flex flex-wrap items-baseline gap-x-2 gap-y-1 text-xs">
                            <ProvenanceBadge v-if="row.grade_points_badge !== null" :state="row.grade_points_badge" :title="row.grade_points_title" />
                            <span class="text-ink">
                                A win pays
                                <span class="font-mono tabular-nums">{{ row.grade_points_win ?? 'N/A' }}</span> Grade Points
                            </span>
                            <span aria-hidden="true" class="text-ink-muted">·</span>
                            <ProvenanceBadge v-if="row.coins_badge !== null" :state="row.coins_badge" :title="row.coins_title" />
                            <span class="text-ink">
                                and <span class="font-mono tabular-nums">{{ row.coins_win ?? 'N/A' }}</span> Shop Coins
                            </span>
                        </p>

                        <p class="mt-1 text-xs text-ink-muted">
                            <ProvenanceBadge :state="row.skip_badge" :title="row.skip_title" />
                            {{ row.skip_consequence }}
                        </p>

                        <p v-if="row.grade_points_win === null" class="mt-1 text-xs text-ink-muted">
                            {{ row.grade_points_title }}
                        </p>
                    </li>
                </ol>
            </li>
        </ol>
    </section>
</template>
