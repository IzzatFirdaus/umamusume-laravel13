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
 * **What has no writer is an absence, not a zero.** Per-stat team grades, current Spirit, the run's
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
import CapsuleHeader from '../CapsuleHeader.vue';
import ProvenanceBadge from '../ProvenanceBadge.vue';
import SpiritBurstRoster from '../SpiritBurstRoster.vue';
import TeamRacePanel from '../TeamRacePanel.vue';
import TeamRankGauge from '../TeamRankGauge.vue';
import AlertRow from './AlertRow.vue';
import ResourceMeter from './ResourceMeter.vue';

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
    payout_timing: string | null;
    special_training: { energy_penalty_removed: boolean | null; wit_burst_energy_bonus: number | null };
    /** The team's own identity (A6.6), as the Trainer entered it; null until stated. */
    team_name: string | null;
    team_motto: string | null;
    team_league_placement: number | null;
    team_preseason_wins: number | null;
    /** The Unity Cup progression counters (A6.7); null until stated. */
    unity_trainings_count: number | null;
    spirit_bursts_count: number | null;
    extreme_bursts_count: number | null;
    /** The combined Spirit + Extreme total, and the band it falls in, or null when not both recorded. */
    combined_bursts: number | null;
    burst_band: string | null;
}

const props = defineProps<{
    /** The shell passes the whole §49 section; this renderer reads its `team` key only. */
    scenario: { widgets: string[]; team: TeamSection };
    label: string;
    kind: 'panel';
    name: string;
}>();

const isTeamPanel = computed(() => props.name === 'team_rank_ladder');
const isRacePanel = computed(() => props.name === 'team_race');

/** The burst machine's regions ride the matrix's own widget membership, not a scenario name. */
const composesBursts = computed(() => props.scenario.widgets.includes('spirit_bursts'));

const current = computed(() =>
    props.scenario.team.rank === null
        ? null
        : { rank: props.scenario.team.rank, level: props.scenario.team.facility_level },
);

const bonusTitle =
    'Seen beside the Rank S emblem in the recorded run (client tooltip). That the bonus binds to the rank ' +
    'rather than the league standing is inferred from screen position, so it is Estimated, not Confirmed ' +
    '(run report §1.8, §7.5).';

/** The team's own identity renders when the Trainer entered any of the four (A6.6). */
const hasTeamIdentity = computed(
    (): boolean =>
        props.scenario.team.team_name !== null
        || props.scenario.team.team_motto !== null
        || props.scenario.team.team_league_placement !== null
        || props.scenario.team.team_preseason_wins !== null,
);

/**
 * The Unity Cup counters, as the brief's own line: `Unity Trainings 55 · Spirit Bursts 6 / 5 Extreme`.
 * The burst half pairs the two tallies, so it is drawn whole when both are recorded and named alone
 * when only one is, never as `6 / N/A` (an unrecorded tally is not a zero, D-220).
 */
const counterSegments = computed((): string[] => {
    const team = props.scenario.team;
    const segments: string[] = [];

    if (team.unity_trainings_count !== null) {
        segments.push(`Unity Trainings ${team.unity_trainings_count}`);
    }

    if (team.spirit_bursts_count !== null && team.extreme_bursts_count !== null) {
        segments.push(`Spirit Bursts ${team.spirit_bursts_count} / ${team.extreme_bursts_count} Extreme`);
    } else if (team.spirit_bursts_count !== null) {
        segments.push(`Spirit Bursts ${team.spirit_bursts_count}`);
    } else if (team.extreme_bursts_count !== null) {
        segments.push(`${team.extreme_bursts_count} Extreme`);
    }

    return segments;
});
</script>

<template>
    <!-- The `team_race` registration: the ported race panel, guidance and margin word included.
         A matrix that turns the flag on without a margin gets the absence, never a printed 0. -->
    <TeamRacePanel
        v-if="isRacePanel && props.scenario.team.race_guidance !== null"
        :enabled="true"
        :guidance="props.scenario.team.race_guidance"
        :entries="props.scenario.team.race_entries"
    />
    <AlertRow
        v-else-if="isRacePanel"
        glyph="○"
        text="This scenario turns the team race panel on, but its matrix states no circle margin, so none is printed."
        detail="The margin is a config fact (`team_race.circles_guidance`). A recorded race list without a stated margin would leave the at-or-below comparison to invent, so the panel is withheld rather than guessed at."
        tone="note"
    />

    <!-- The `team_rank_ladder` registration: the Team Panel itself. Team cards sit beside the burst regions
         on desktop and stack above them on mobile, with no horizontal scroll at any width. -->
    <div v-else-if="isTeamPanel" class="grid grid-cols-1 gap-3 lg:grid-cols-2">
        <div class="flex flex-col gap-3">
            <!-- The team's own identity (A6.6), drawn only when the Trainer entered one of the four:
                 a run that named no team gets no card rather than four empty rows. -->
            <div v-if="hasTeamIdentity" class="rounded-md border border-rule bg-panel p-3">
                <CapsuleHeader title="Team" class="mb-2" />
                <dl class="flex flex-col gap-1 text-sm">
                    <div v-if="props.scenario.team.team_name !== null" class="flex items-baseline justify-between gap-3">
                        <dt class="text-ink-muted">Name</dt>
                        <dd class="text-ink-strong">{{ props.scenario.team.team_name }}</dd>
                    </div>
                    <div v-if="props.scenario.team.team_motto !== null" class="flex items-baseline justify-between gap-3">
                        <dt class="text-ink-muted">Motto</dt>
                        <dd class="text-ink">{{ props.scenario.team.team_motto }}</dd>
                    </div>
                    <div v-if="props.scenario.team.team_league_placement !== null" class="flex items-baseline justify-between gap-3">
                        <dt class="text-ink-muted">League placement</dt>
                        <dd class="font-mono tabular-nums text-ink-strong">{{ props.scenario.team.team_league_placement }}</dd>
                    </div>
                    <div v-if="props.scenario.team.team_preseason_wins !== null" class="flex items-baseline justify-between gap-3">
                        <dt class="text-ink-muted">Preseason rounds won</dt>
                        <dd class="font-mono tabular-nums text-ink-strong">{{ props.scenario.team.team_preseason_wins }}</dd>
                    </div>
                </dl>
            </div>

            <TeamRankGauge :enabled="true" :current="current" :ladder="props.scenario.team.ladder" />

            <!-- The +30 is the figure the recorded client shows, and it was read beside the S
                 emblem. Other ranks pay something this corpus does not state, so the line renders
                 only where the observation applies. -->
            <p v-if="props.scenario.team.rank === 'S'" class="flex flex-wrap items-center gap-2 text-sm text-ink">
                <span class="font-semibold text-ink-strong">Team Ranking Bonus: All Attributes +30</span>
                <ProvenanceBadge state="estimated" :title="bonusTitle" />
            </p>

            <div class="rounded-md border border-rule bg-panel p-3">
                <CapsuleHeader title="Team stat grades" class="mb-2" />
                <ul class="flex flex-col gap-1" aria-label="Team stat grades">
                    <li
                        v-for="grade in props.scenario.team.stat_grades"
                        :key="grade.key"
                        class="flex items-baseline justify-between gap-3 text-sm"
                    >
                        <span class="text-ink">{{ grade.label }}</span>
                        <span
                            class="font-mono tabular-nums text-ink-strong"
                            title="No column records a team stat grade. The client prints these letters on its Team Info panel, and this tool has no input that stores them."
                        >N/A</span>
                    </li>
                </ul>
            </div>

            <SpiritBurstRoster :enabled="composesBursts" :roster="props.scenario.team.roster" />
        </div>

        <div class="flex flex-col gap-3">
            <div v-if="composesBursts" class="rounded-md border border-rule bg-panel p-3">
                <CapsuleHeader title="Spirit" class="mb-2" />
                <!-- The value and the bar are the same reading (design-2.0 §45). No column records a
                     Spirit value and no sourced maximum exists, so the track renders empty and the
                     number reads N/A with the reason. -->
                <ResourceMeter
                    label="Current Spirit"
                    :value="null"
                    :max="null"
                    absence="No column records a Spirit value. The client prints the gauge on its Team Info panel; until an input stores it, there is no reading to show."
                />
                <AlertRow
                    class="mt-2"
                    glyph="○"
                    text="Burst readiness is not computed: the bands below are thresholds, but no Spirit reading is recorded, so there is nothing to set against them."
                    detail="Readiness would be Calculated once a Spirit value has a writer. Until then the burst states in the roster above are the machine this build can see, and a percentage or a band would be invented."
                    tone="note"
                />
                <AlertRow
                    class="mt-1"
                    glyph="○"
                    text="Recommended timing and projected benefit are not built."
                    detail="Both are advice this build holds (ADR-0020 §3): the trigger rule is recorded (a burst fires on the next Special Training once a teammate's gauge is full), but what to do about it and what it would yield are not computed here."
                    tone="note"
                />
            </div>

            <!-- The Unity Cup progression counters (A6.7). Drawn only when the Trainer stated one; the
                 combined total below reads these two tallies. -->
            <div v-if="counterSegments.length > 0" class="rounded-md border border-rule bg-panel p-3">
                <CapsuleHeader title="Unity Cup progress" class="mb-2" />
                <p class="text-sm text-ink">{{ counterSegments.join(' · ') }}</p>
            </div>

            <div v-if="composesBursts && props.scenario.team.bands.length > 0" class="rounded-md border border-rule bg-panel p-3">
                <div class="mb-2 flex flex-wrap items-baseline gap-2">
                    <CapsuleHeader title="Burst count bands" />
                    <ProvenanceBadge
                        state="estimated"
                        title="Transcribed from the scenario guide's post-rework table, which rests on one publisher where two report different Skill Point figures, and the guide's own line that Extreme Bursts count toward the total is a live disagreement (run report §7.4, §8.2)."
                    />
                </div>
                <table class="w-full text-sm">
                    <caption class="sr-only">
                        Reward by combined Spirit Burst and Extreme Spirit Burst count
                    </caption>
                    <thead>
                        <tr class="border-b border-rule text-left text-xs text-ink-muted">
                            <th scope="col" class="py-1 font-semibold">Total bursts</th>
                            <th scope="col" class="py-1 font-semibold">Reward</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- The band the run's combined total falls in is marked, so the table reads
                             the run's own position rather than only the reward reference. -->
                        <tr
                            v-for="band in props.scenario.team.bands"
                            :key="band.total"
                            class="border-b border-rule last:border-b-0"
                            :class="band.total === props.scenario.team.burst_band ? 'bg-raised' : ''"
                        >
                            <th scope="row" class="py-1.5 pr-2 text-left font-mono font-normal tabular-nums text-ink">
                                {{ band.total }}
                                <span v-if="band.total === props.scenario.team.burst_band" class="ml-1 text-xs font-semibold text-ink-strong">(this run)</span>
                            </th>
                            <td class="py-1.5 text-ink">{{ band.reward }}</td>
                        </tr>
                    </tbody>
                </table>
                <AlertRow
                    class="mt-2"
                    glyph="⚠"
                    text="Three parts of this table are publisher conflicts, not settled facts: whether Extreme Bursts count toward the total, the Skill Point figures, and the month of the payout event."
                    detail="gametora counts the bursts combined and pays 10/20/30/40 SP; umamusu.wiki's criteria never say whether Extremes increment, and two other publishers report 15/15/20/20 with no stat component; the payout is dated late November where those publishers print the first half. The client's own Team Info count is the tiebreaker (run report §7.4, §8.2)."
                    tone="note"
                />
                <p class="mt-2 text-xs text-ink-muted">
                    <template v-if="props.scenario.team.payout_timing !== null">
                        The gold versions arrive from the scripted event in {{ props.scenario.team.payout_timing }}.
                    </template>
                    <!-- The run's own position, read off the combined Spirit + Extreme total the
                         counters above carry. Until both tallies are recorded there is no combined
                         count to place, so the absence is stated rather than a band guessed. -->
                    <template v-if="props.scenario.team.combined_bursts !== null">
                        This run: {{ props.scenario.team.combined_bursts }} bursts combined<template v-if="props.scenario.team.burst_band !== null">, inside the {{ props.scenario.team.burst_band }} band</template>.
                    </template>
                    <template v-else>
                        Where this run sits in the bands is not shown: both burst tallies have not been recorded.
                    </template>
                </p>
            </div>

            <div
                v-if="props.scenario.team.special_training.energy_penalty_removed !== null
                    || props.scenario.team.special_training.wit_burst_energy_bonus !== null"
                class="rounded-md border border-rule bg-panel p-3"
            >
                <CapsuleHeader title="Special Training" class="mb-2" />
                <p v-if="props.scenario.team.special_training.energy_penalty_removed" class="text-sm text-ink">
                    The extra Energy cost on Special Training was removed on 2026-07-01.
                </p>
                <p v-if="props.scenario.team.special_training.wit_burst_energy_bonus !== null" class="mt-1 text-sm text-ink">
                    A burst on the Wit facility grants
                    {{ props.scenario.team.special_training.wit_burst_energy_bonus }} Energy recovery.
                </p>
                <p class="mt-2 text-xs text-ink-muted" title="No column records facility levels, per-member training state or Special Training gains; the Cockpit's turns carry what the Trainer entered.">
                    Per-facility levels and per-member training state are not recorded here.
                </p>
            </div>
        </div>
    </div>
</template>
