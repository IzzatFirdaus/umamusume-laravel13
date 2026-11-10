<script setup lang="ts">
/*
 * The Unity Cup Team Cockpit (SCREEN-015, design-2.0 §27, plan §9.1). Registered against the two
 * Unity-Cup panel flags, and it draws the region the shell registered it for: the `team_race` registration is
 * the ported race panel, the `team_rank_ladder` registration is the §27 first-class Team Panel. The branch
 * reads the flag key the registry used, never a scenario name (G-33, D-240) — the component file's
 * name is the brief's own, and nothing else about it is.
 *
 * **Behaviour parity over redesign.** The three ported references keep their props and their copy
 * (`TeamRankGauge`, `SpiritBurstRoster`, `TeamRacePanel`, carried over from the run record screen's
 * Blade originals), so the Cockpit's panel and the run record's panels cannot disagree about one
 * fact. The figures this slice introduces get their provenance beside them: the +30 ranking bonus
 * renders Estimated, and the burst-band table renders with the Extreme-counting caveat the run
 * report §8.2 records.
 *
 * **What has no writer is an absence, not a zero.** Current Spirit, the run's
 * own band position, member stats and roles, and the held advice (readiness, timing, benefit) each
 * render `N/A` or a named sentence with the reason in a `title`. Burst readiness is not computed:
 * no Spirit value is recorded and no numeric threshold exists in config, so there is nothing to
 * compare — saying so is the honest state, not a stub.
 *
 * The roster and the burst regions gate on the `spirit_bursts` widget key the matrix carries, the
 * same membership the controller resolves server-side (D-221, gate G-34); a scenario could compose
 * the ladder without the burst machine, and this panel must not claim one it does not have.
 */
import { computed } from 'vue';
import AlertRow from './AlertRow.vue';
import ScenarioCardGrid from './ScenarioCardGrid.vue';
import TeamRacePanel from '../TeamRacePanel.vue';

type BurstState =
    | 'Chargeable'
    | 'Charged'
    | 'Held'
    | 'NormalBurstSpent'
    | 'ExtremeChargeable'
    | 'ExtremeSpent';

interface TeamSection {
    rank: string | null;
    facility_level: number | null;
    ladder: { rank: string; level: number }[];
    stat_grades: { key: string; label: string; grade: string | null }[];
    roster: { teammate: string; state: BurstState; stateLabel: string }[];
    race_guidance: number | null;
    race_entries: { title: string | null; tier: string | null; circles: number | null; placement: number | null }[];
    bands: { total: string; reward: string }[];
    /** The band figures' carrier, named so the panel never has to invent one. */
    bands_source: string | null;
    /** The low-band ambiguity the two publishers disagree on, stated rather than left to "may differ". */
    bands_caveat: string | null;
    payout_timing: string | null;
    special_training: { energy_penalty_removed: boolean | null; wit_burst_energy_bonus: number | null };
    /**
     * G4's two team surfaces. Null for a scenario whose matrix declares no team race, so the key set
     * stays one shape across the four scenarios and neither sub-component carries a scenario test.
     */
    coverage: {
        rows: { category: string; asks: string; fielded: string | null }[];
        badge: import('../../types').ProvenanceState;
        source_title: string;
        absence_title: string;
    } | null;
    tournament: {
        cadence_months: number | null;
        month_anchors: string[];
        category_count: number;
        clear_all_reward: string | null;
        badge: import('../../types').ProvenanceState;
        source_title: string;
        projection: string | null;
        projection_title: string;
        next: string | null;
        next_title: string;
        risks: string[];
    } | null;
    /** The team's own identity (A6.6), as the Trainer entered it; null until stated. */
    team_name: string | null;
    team_motto: string | null;
    team_league_placement: number | null;
    team_preseason_wins: number | null;
    /** The Unity Cup progression counters (A6.7); null until stated. */
    unity_trainings_count: number | null;
    spirit_bursts_count: number | null;
    extreme_bursts_count: number | null;
    /** KI-133's labelled manual proxy; one team-wide aggregate, null until entered. */
    current_spirit_proxy: number | null;
    /** The combined Spirit + Extreme total, and the band it falls in, or null when not both recorded. */
    combined_bursts: number | null;
    burst_band: string | null;
}

const props = defineProps<{
    /** The shell passes the whole §49 section; this renderer reads its `team` key only. */
    scenario: {
        widgets: string[];
        team: TeamSection;
        panels: Record<string, { on: boolean }>;
        /** The career-goal groups the grid places at positions 4 and 5 (URA adds a third). */
        objectives: {
            group: string;
            rows: {
                label: string;
                state: string | null;
                year_label: string | null;
                turn: number | null;
                race_url: string | null;
                absence: string | null;
            }[];
            summary: string | null;
            empty_note: string | null;
        }[];
    };
    label: string;
    kind: 'panel';
    name: string;
}>();

const isTeamPanel = computed(() => props.name === 'team_rank_ladder');
const isRacePanel = computed(() => props.name === 'team_race');

/**
 * Which of the two team flags this scenario composes. When both are on, the team registration draws
 * the whole system including the race panel, so the race registration draws nothing rather than
 * rendering a second copy above the bento (the owner asked for one grid).
 */
const composesRacePanel = computed(() => props.scenario.panels['team_race']?.on === true);
const composesTeamPanel = computed(() => props.scenario.panels['team_rank_ladder']?.on === true);

/** The burst machine's regions ride the matrix's own widget membership, not a scenario name. */
const composesBursts = computed(() => props.scenario.widgets.includes('spirit_bursts'));

</script>

<template>
    <!-- The `team_race` registration on its own, without the ladder: the ported race panel, guidance
         and margin word included. A matrix that turns the flag on without a margin gets the absence,
         never a printed 0. When both flags are on this arm draws nothing, because the grid below
         draws the race card. -->
    <TeamRacePanel
        v-if="isRacePanel && !composesTeamPanel && props.scenario.team.race_guidance !== null"
        :enabled="true"
        :guidance="props.scenario.team.race_guidance"
        :entries="props.scenario.team.race_entries"
    />
    <AlertRow
        v-else-if="isRacePanel && !composesTeamPanel"
        glyph="○"
        text="This scenario turns the team race panel on, but its matrix states no circle margin, so none is printed."
        detail="The margin is a config fact (`team_race.circles_guidance`). A recorded race list without a stated margin would leave the at-or-below comparison to invent, so the panel is withheld rather than guessed at."
        tone="note"
    />
    <!-- The `team_rank_ladder` registration: the whole system, as one bento grid in the HFW's
         ruled order. The order and the spans live in the composer, because it interleaves these
         cards with the goal groups another component draws. -->
    <ScenarioCardGrid
        v-else-if="isTeamPanel"
        :team="props.scenario.team"
        :objectives="props.scenario.objectives"
        :bursts-enabled="composesBursts"
    />
</template>
