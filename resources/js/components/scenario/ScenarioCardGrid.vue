<script setup lang="ts">
/*
 * The page's card composer: one 12-track grid, in the HFW's ruled order.
 *
 * **Why this file exists.** The page used to render the scenario boards as three independent blocks:
 * `UnityCupPanel` drew ten cards as one contiguous bento, `CareerGoalsPanel` drew two as a flex column,
 * and `ScenarioPanel` drew the reference strip. The owner's ruling W.2 = run-state primary requires an
 * order that interleaves cards from all three, and no arrangement of three contiguous blocks can
 * produce it (section `## 2026-11-10-scenario-detail-remodel.md` of
 * `docs/research-scratch/PROCESS-PLANS.md` §A.7 recorded exactly this as an
 * open consequence). The composer is the answer: the page owns the order, each card draws itself.
 *
 * **The order and the spans are the HFW's, transcribed rather than derived.** Measured from
 * `docs/design-research/scenario-detail-hfw-2026-11-09/scenario-detail-hfw.html` at 992 px: twelve
 * cards in `Team Race → Unity Cup progress → Burst count bands → Special Training → Trainee goals →
 * Mandatory races → Team race coverage → Team race shape → Team → Spirit → Team Rank → Team stat
 * grades`, spans `12 / 4 / 12 / 4 / 12 / 12 / 8 / 12 / 4 / 4 / 4 / 8`.
 *
 * **The holes are the HFW's too, deliberately.** Four rows use fewer than twelve tracks, which a
 * conventional layout would pack. They are kept because the directive is to match the drawing, not to
 * improve on it; `6493326a` had closed one of them and that closure is intentionally reverted here.
 * See the slice report for the record.
 *
 * **A card with no data contributes no cell.** The gates are on the item, not only inside the card,
 * because an empty grid cell holds its tracks and turns the row beside it into a full-width one.
 */
import { computed } from 'vue';
import GoalGroupCard from './cards/GoalGroupCard.vue';
import TeamCard from './cards/TeamCard.vue';
import SpiritCard from './cards/SpiritCard.vue';
import ProgressCard from './cards/ProgressCard.vue';
import StatGradesCard from './cards/StatGradesCard.vue';
import BurstBandsCard from './cards/BurstBandsCard.vue';
import SpecialTrainingCard from './cards/SpecialTrainingCard.vue';
import TeamRacePanel from '../TeamRacePanel.vue';
import ProvenanceBadge from '../ProvenanceBadge.vue';
import TeamRankGauge from '../TeamRankGauge.vue';
import UnityCupTeamBoard from './UnityCupTeamBoard.vue';
import UnityCupTournamentReadiness from './UnityCupTournamentReadiness.vue';
import AlertRow from './AlertRow.vue';

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
    bands_source: string | null;
    bands_caveat: string | null;
    payout_timing: string | null;
    special_training: { energy_penalty_removed: boolean | null; wit_burst_energy_bonus: number | null };
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
    team_name: string | null;
    team_motto: string | null;
    team_league_placement: number | null;
    team_preseason_wins: number | null;
    unity_trainings_count: number | null;
    spirit_bursts_count: number | null;
    extreme_bursts_count: number | null;
    combined_bursts: number | null;
    burst_band: string | null;
}

interface GoalGroup {
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
}

const props = defineProps<{
    team: TeamSection;
    objectives: GoalGroup[];
    /** Whether the matrix composes the burst machine for this run (`spirit_bursts` widget). */
    burstsEnabled: boolean;
}>();

/** The team's own identity renders when the Trainer entered any of the four fields (A6.6). */
const hasTeamIdentity = computed<boolean>(() =>
    props.team.team_name !== null
    || props.team.team_motto !== null
    || props.team.team_league_placement !== null
    || props.team.team_preseason_wins !== null,
);

/** The Unity Cup counters draw only when the Trainer stated one. */
const hasCounters = computed<boolean>(() =>
    props.team.unity_trainings_count !== null
    || props.team.spirit_bursts_count !== null
    || props.team.extreme_bursts_count !== null,
);

/** The Special Training card draws only when config states one of its two facts. */
const hasSpecialTraining = computed<boolean>(() =>
    props.team.special_training.energy_penalty_removed !== null
    || props.team.special_training.wit_burst_energy_bonus !== null,
);

/** The rank card's own +30 note. Kept beside `current()` because both read the rank letter (KI-155). */
const bonusTitle =
    'Seen beside the Rank S emblem in the recorded run (client tooltip). The figure is the ' +
    'league-placement bonus, not a reading of the rank letter: the published ladder pays 2nd-5th +50, ' +
    '6th-10th +30, 11th-15th +20 and 16th-20th +10, and this run\'s 8th place sits in the +30 band ' +
    '(docs/research/flows-career-workspace.md:232, citing UMAMUSUME_REFERENCE §2.2.2 and the run ' +
    'report §1.8). This build stores the placement but no ladder, so the line repeats the figure the ' +
    'client printed for THIS placement rather than computing one.';

const current = computed((): { rank: string; level: number | null } | null => (
    props.team.rank === null
        ? null
        : { rank: props.team.rank, level: props.team.facility_level }
));
</script>

<template>
    <div class="grid grid-cols-1 items-start gap-3.5 @3xl:grid-cols-6 @5xl:grid-cols-12" data-bento="scenario-cards">
        <!-- `team_race` on its own, without the ladder: the ported race panel and its absence arm.
             A matrix that turns the flag on without a margin gets the absence, never a printed 0. -->
        <div v-if="team.race_guidance !== null" class="col-span-1 @3xl:col-span-6 @5xl:col-span-12" data-panel="team-race">
            <TeamRacePanel :enabled="true" :guidance="team.race_guidance" :entries="team.race_entries" />
        </div>
        <div v-else class="col-span-1 @3xl:col-span-6 @5xl:col-span-12" data-panel="team-race">
            <AlertRow
                glyph="○"
                text="This scenario turns the team race panel on, but its matrix states no circle margin, so none is printed."
                detail="The margin is a config fact (`team_race.circles_guidance`). A recorded race list without a stated margin would leave the at-or-below comparison to invent, so the panel is withheld rather than guessed at."
                tone="note"
            />
        </div>

        <!-- 1 — Unity Cup progress (run-state). -->
        <div v-if="hasCounters" class="col-span-1 @3xl:col-span-3 @5xl:col-span-4" data-panel="progress">
            <ProgressCard
                :unity-trainings-count="team.unity_trainings_count"
                :spirit-bursts-count="team.spirit_bursts_count"
                :extreme-bursts-count="team.extreme_bursts_count"
            />
        </div>

        <!-- 2 — Burst count bands, roster merged (run-state). -->
        <div
            v-if="burstsEnabled && team.bands.length > 0"
            class="col-span-1 @3xl:col-span-6 @5xl:col-span-12"
            data-panel="burst-bands"
        >
            <BurstBandsCard
                :bands="team.bands"
                :burst-band="team.burst_band"
                :combined-bursts="team.combined_bursts"
                :bands-source="team.bands_source"
                :bands-caveat="team.bands_caveat"
                :payout-timing="team.payout_timing"
                :roster="team.roster"
                :bursts-enabled="burstsEnabled"
            />
        </div>

        <!-- 3 — Special Training (run-state). -->
        <div v-if="hasSpecialTraining" class="col-span-1 @3xl:col-span-3 @5xl:col-span-4" data-panel="special-training">
            <SpecialTrainingCard :special-training="team.special_training" />
        </div>

        <!-- 4 and 5 — the goal groups, in the payload's own order (run-state). Trainee goals and
             Mandatory races are two groups; URA adds Happy Meek third, which the HFW does not draw and
             which therefore takes payload order after the other two. -->
        <div
            v-for="(group, index) in objectives"
            :key="group.group"
            class="col-span-1 @3xl:col-span-6 @5xl:col-span-12"
            :data-panel="`goal-group-${index}`"
        >
            <GoalGroupCard :group="group" />
        </div>

        <!-- 6 — Team race coverage (run-state). -->
        <div v-if="team.coverage !== null" class="col-span-1 @3xl:col-span-6 @5xl:col-span-8" data-panel="coverage">
            <UnityCupTeamBoard :coverage="team.coverage" />
        </div>

        <!-- 7 — Team race shape (run-state). -->
        <div v-if="team.tournament !== null" class="col-span-1 @3xl:col-span-6 @5xl:col-span-12" data-panel="shape">
            <UnityCupTournamentReadiness :tournament="team.tournament" />
        </div>

        <!-- 8, 9, 10 — Team, Spirit, Team Rank (team). -->
        <div v-if="hasTeamIdentity" class="col-span-1 @3xl:col-span-3 @5xl:col-span-4" data-panel="team">
            <TeamCard :team="team" />
        </div>
        <div v-if="burstsEnabled" class="col-span-1 @3xl:col-span-3 @5xl:col-span-4" data-panel="spirit">
            <SpiritCard />
        </div>
        <div class="col-span-1 @3xl:col-span-3 @5xl:col-span-4" data-panel="team-rank">
            <TeamRankGauge :enabled="true" :current="current" :ladder="team.ladder" />
            <!-- The +30 is the figure the recorded client shows, and it was read beside the S emblem.
                 Per `docs/research/flows-career-workspace.md:232` it is the LEAGUE-PLACEMENT bonus, not
                 a rank bonus: 6th-10th pays +30, and this run placed 8th (KI-155). The gate still reads
                 the rank because the observation is only recorded for this run's board, and this build
                 stores no placement ladder to generalise from (no `league_bonus` or `placement_bonus` key
                 exists in `config/scenarios.php`), so the line states an observed figure rather than a
                 derived one. It rides the rank card because that is where the client prints it. -->
            <p v-if="team.rank === 'S'" class="mt-2 flex flex-wrap items-center gap-2 text-sm text-ink">
                <span class="font-semibold text-ink-strong">Team Ranking Bonus: All Attributes +30</span>
                <ProvenanceBadge state="estimated" :title="bonusTitle" />
            </p>
        </div>

        <!-- 11 — Team stat grades (team). -->
        <div class="col-span-1 @3xl:col-span-6 @5xl:col-span-8" data-panel="stat-grades">
            <StatGradesCard :stat-grades="team.stat_grades" />
        </div>
    </div>
</template>
