<script setup lang="ts">
/*
 * SCREEN-014's panel: career goals, the mandatory race record, and Happy Meek
 * (plan §9 E2, `SCR-CAR-020`), with G3's three decision surfaces mounted beside those groups.
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
import UraDuelPanel from './UraDuelPanel.vue';
import UraMilestoneTracker from './UraMilestoneTracker.vue';
import UraPreFinalReview from './UraPreFinalReview.vue';
import type { ProvenanceState } from '../../types';

type Badge = ProvenanceState;

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
    /**
     * The group's own achieved count, in the client's shape ("Goals Achieved 5 of 8"), or null.
     *
     * An integer pair and nothing else: `ux-laws-audit-plan-2026-10-13.md` §9 item 6 and
     * `frontend-fidelity-2026-10-13.md` §8 item 3 forbid a score, a percentage or a progress bar on this
     * surface, and a later pass reading a barless count must not "improve" it into one.
     */
    summary: string | null;
    /**
     * What an empty group means, in the server's own words. The generic line below speaks of mandatory
     * races, so a goals group that holds nothing has to say so itself rather than borrow it.
     */
    empty_note: string | null;
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
        // Declared locally for the same reason the rest of this contract is: `defineProps<Imported>()`
        // cannot resolve an imported type (plan §11). The server composes each of the three; the
        // component that draws them owns the wording, and this block only carries them through.
        milestones: {
            label: string;
            state: string;
            title: string;
            figures: string;
            figures_badge: Badge;
            figures_title: string;
            due_turn: number | null;
            due_label: string;
            due_title: string;
            consequence: string;
            condition: string | null;
            decision_url: string;
        }[];
        duel: {
            rules: { label: string; value: string | null; badge: Badge; title: string }[];
            hints: { stat: string; name: string; sp_cost: string | null; badge: Badge; title: string }[];
            run: { label: string; value: string | null; badge: Badge; title: string }[];
        } | null;
        pre_final: {
            window: { state: string; label: string; turns_away: number | null; turns_ahead: number; title: string };
            recorded: { label: string; value: string | null; cap: string | null; badge: Badge; title: string }[];
            speed_target: { phrasing: string; value: string | null; short_by: string | null; badge: Badge; title: string };
            stamina_floors: { rows: { distance: string; floor: string }[]; badge: Badge; title: string };
            distance: { value: string | null; badge: Badge; title: string };
        } | null;
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

            <!-- The client's own ladder footer (screenshot-notes/02:165, "Goals Achieved 5/8"), placed
                 after the rows so the last thing a Trainer reads is how far the ladder goes. -->
            <p v-if="group.summary !== null" class="text-xs font-semibold text-ink-strong">
                {{ group.summary }}
            </p>
        </section>

        <!-- G3's three surfaces, mounted beside the groups rather than in place of them: the milestone
             list draws the gates the groups do not carry, the duel panel opens over the published
             rules, and the pre-final review is absent outside its window. -->
        <UraMilestoneTracker :milestones="props.scenario.milestones" />

        <UraDuelPanel v-if="props.scenario.duel !== null" :duel="props.scenario.duel" />

        <UraPreFinalReview v-if="props.scenario.pre_final !== null" :pre-final="props.scenario.pre_final" />
    </div>
</template>
