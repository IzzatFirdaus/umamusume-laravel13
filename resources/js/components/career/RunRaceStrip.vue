<script setup lang="ts">
/*
 * The run-scoped race strip, D14a (`SCREEN_SPEC.md` SCR-CAR-011). Three bounded regions in the
 * Cockpit's left column: what this run has already raced, the turn it is deciding, and the
 * obligations still to come.
 *
 * **It is not the race calendar.** `RaceCalendar.vue` is the 0.1.0 catalogue grid and stays the run
 * record screen's component; a 20-share column that prints every race the calendar holds is the wall
 * this slice removes. The full option list, the race facts and the fan-gap arithmetic are the Race
 * Decision screen's (`SCR-CAR-013`, D10), so This Turn counts what is offered and links there rather
 * than re-listing it.
 *
 * **Nothing here predicts.** No win figure, no readiness band, no percentage, no distance band read
 * off a metre count and no condition applicability (`ADR-0016`, `ADR-0020` §3). Every value on the
 * strip is a stored row: the run's own `race_entries`, or the catalogue's own title, tier and
 * mandatory flag. An unrecorded value renders `N/A` with its reason (`AGENTS.md` §5).
 *
 * **D14 owns this region's future.** The milestone timeline is D14, and it may absorb this contract,
 * narrow the presentation, or replace the column. That is why the data arrives as one prop bundle and
 * the component reads nothing but it.
 */
import { computed } from 'vue';

interface RecordedRow {
    id: number | null;
    entry_id: number;
    title: string;
    tier: string | null;
    status: string;
    status_label: string;
    placement: string | null;
    turn: number | null;
}

interface AheadRow {
    id: number;
    title: string;
    tier: string | null;
    year_label: string;
    turn: number | null;
}

interface Strip {
    shown: boolean;
    notice: string | null;
    run: RecordedRow[];
    this_turn: {
        turn: number;
        year_label: string;
        offered: number;
        recorded: RecordedRow[];
    } | null;
    ahead: AheadRow[];
    absences: { turn_link: string; goal: string };
    empty: { run: string | null; this_turn: string | null; ahead: string | null };
    links: { decision_url: string; run_url: string };
}

const props = defineProps<{ strip: Strip }>();

// `computed`, not a module-level const: Inertia re-renders this page on every visit and a plain const
// would keep the first render's value (the trap `TrainingCard.vue` records).
const recorded = computed(() => props.strip.run);
const ahead = computed(() => props.strip.ahead);
const thisTurn = computed(() => props.strip.this_turn);
</script>

<template>
    <p v-if="props.strip.notice" class="mt-1 text-sm text-ink">
        <span :title="props.strip.notice">N/A</span>.
        The strip has nothing to orient on, so it says so rather than drawing an empty grid.
    </p>

    <div v-else class="mt-3 flex flex-col gap-4">
        <section aria-labelledby="race-strip-recorded">
            <h3 id="race-strip-recorded" class="text-xs font-semibold uppercase tracking-wide text-ink-muted">
                Recorded in this run
            </h3>

            <p v-if="props.strip.empty.run" class="mt-1 text-sm text-ink">
                {{ props.strip.empty.run }}
                <a :href="props.strip.links.run_url" class="inline-flex min-h-11 items-center font-medium text-ink-strong underline">
                    Go to the run record screen
                </a>
            </p>

            <ul v-else class="mt-1 flex flex-col">
                <li
                    v-for="row in recorded"
                    :key="row.entry_id"
                    class="flex flex-wrap items-baseline gap-x-2 border-b border-rule py-2 text-sm last:border-b-0"
                >
                    <span class="font-medium text-ink-strong">{{ row.title }}</span>
                    <span v-if="row.tier !== null" class="text-xs text-ink-muted">{{ row.tier }}</span>
                    <span class="text-xs text-ink-muted">
                        Turn
                        <span v-if="row.turn === null" :title="props.strip.absences.turn_link">N/A</span>
                        <template v-else>{{ row.turn }}</template>
                    </span>
                    <span class="text-xs font-semibold text-ink-strong">{{ row.status_label }}</span>
                    <span v-if="row.placement !== null" class="text-xs text-ink-muted">{{ row.placement }}</span>
                </li>
            </ul>
        </section>

        <section aria-labelledby="race-strip-turn">
            <h3 id="race-strip-turn" class="text-xs font-semibold uppercase tracking-wide text-ink-muted">
                This turn
            </h3>

            <p v-if="thisTurn === null" class="mt-1 text-sm text-ink">
                {{ props.strip.empty.this_turn }}
            </p>

            <template v-else>
                <p class="mt-1 text-sm text-ink">
                    {{ thisTurn.year_label }} Year, turn {{ thisTurn.turn }}. The calendar carries
                    {{ thisTurn.offered }} {{ thisTurn.offered === 1 ? 'race' : 'races' }} here.
                </p>

                <ul v-if="thisTurn.recorded.length > 0" class="mt-1 flex flex-col">
                    <li
                        v-for="row in thisTurn.recorded"
                        :key="row.entry_id"
                        class="flex flex-wrap items-baseline gap-x-2 border-b border-rule py-2 text-sm last:border-b-0"
                    >
                        <span class="font-medium text-ink-strong">{{ row.title }}</span>
                        <span class="text-xs font-semibold text-ink-strong">{{ row.status_label }}</span>
                    </li>
                </ul>

                <a
                    :href="props.strip.links.decision_url"
                    class="mt-1 inline-flex min-h-11 items-center font-medium text-ink-strong underline"
                >
                    Open Race Decision for turn {{ thisTurn.turn }}
                </a>
            </template>
        </section>

        <section aria-labelledby="race-strip-ahead">
            <h3 id="race-strip-ahead" class="text-xs font-semibold uppercase tracking-wide text-ink-muted">
                Still to come
            </h3>

            <p v-if="props.strip.empty.ahead" class="mt-1 text-sm text-ink">
                {{ props.strip.empty.ahead }}
            </p>

            <ul v-else class="mt-1 flex flex-col">
                <li
                    v-for="row in ahead"
                    :key="row.id"
                    class="flex flex-wrap items-baseline gap-x-2 border-b border-rule py-2 text-sm last:border-b-0"
                >
                    <span class="font-bold text-ink-strong">
                        <span aria-hidden="true">!</span> Mandatory
                    </span>
                    <span class="font-medium text-ink-strong">{{ row.title }}</span>
                    <span v-if="row.tier !== null" class="text-xs text-ink-muted">{{ row.tier }}</span>
                    <span class="text-xs text-ink-muted">
                        {{ row.year_label }} Year<template v-if="row.turn !== null"> · Turn {{ row.turn }}</template>
                    </span>
                </li>
            </ul>

            <!-- The goal races the client prints are per trainee, and no table here holds them, so the
                 region names the absence once rather than printing a flag it cannot source. -->
            <p class="mt-2 text-xs text-ink-muted">
                Goal races:
                <span :title="props.strip.absences.goal">N/A</span>.
            </p>
        </section>
    </div>
</template>
