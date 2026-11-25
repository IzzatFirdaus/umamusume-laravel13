<script setup lang="ts">
/*
 * The Unity Cup team's readable face (`UC2.1`): what the run has recorded about its team, laid out
 * so a Trainer can read it without opening a form.
 *
 * The page used to be one permanently expanded seven-field form, so the answer to "what is this
 * team" was a column of inputs and the counters read as fields rather than as a record. This is the
 * read; `UnityCupEditor` stays the one write, behind the disclosure this component owns.
 *
 * **Every figure is the run's own recorded value, or a named absence.** Null is "not stated" and
 * never a zero (`D-220`), so a blank stays blank with its reason rather than printing 0. The tiles
 * print `N/A` plus a sentence for the three states a reader actually needs to tell apart: not
 * entered yet, not applicable to the scenario, and no source publishes it.
 *
 * **Two different counts are never merged**, which is `§1.8`'s own warning. The Team Rank letter is
 * the ladder rung; the league placement is the standing, and the bonus beside it is what that
 * standing pays (`BO-015`, read from the scenario's ladder). One number is not derived from the
 * other here.
 *
 * **The team race countdown is transcribed, not predicted.** `team_race_countdown` is the Trainer's
 * own reading off the client (`D03`); no source publishes the career turn a round falls on, so there
 * is no figure to compute from the calendar and this component computes none.
 */
import AbsenceValue from '../AbsenceValue.vue';
import UnityCupEditor from './UnityCupEditor.vue';
import { computed } from 'vue';

const props = defineProps<{
    /** The team section `TeamPanelBuilder::teamSection()` builds; every field nullable. */
    team: {
        rank: string | null;
        facility_level: number | null;
        team_name: string | null;
        team_motto: string | null;
        team_league_placement: number | null;
        league_bonus: number | null;
        team_preseason_wins: number | null;
        unity_trainings_count: number | null;
        spirit_bursts_count: number | null;
        extreme_bursts_count: number | null;
        current_spirit_proxy: number | null;
        combined_bursts: number | null;
        burst_band: string | null;
        /**
         * `D03`'s recorded countdown. Absent from the payload until the builder carries it, and null
         * when the Trainer has not read one; both render the same named absence rather than a figure
         * derived from the career calendar.
         */
        team_race_countdown?: number | null;
        /** The number of identities the client itself showed training alongside the trainee
         * (UC3.3). Null when the run has not recorded an observation; does not increase when
         * recorded members are added or removed.
         */
        observed_member_count: number | null;
        /**
         * `D-10`'s per-round preseason outcomes: one entry per stored row. An entry with a null
         * `outcome` is a round the Trainer opened and left blank; a round with no entry at all is
         * absent. Three states, kept apart here and never collapsed into "lost" (D-220).
         */
        preseason_rounds?: { round: number; outcome: string | null }[];
        /** The scenario's published cadence, printed as the reason no turn is computed. */
        tournament?: { cadence_months: number | null; month_anchors: string[] } | null;
        /** The five per-stat grades the editor's selects bind to, in the config's stat order. */
        stat_grades: { key: string; label: string; grade: string | null }[];
    };
    /** `route('runs.update', $run)`, the single write door for these columns. */
    action: string;
    umamusumeId: number;
    status: string;
}>();

const shown = (value: string | null): string | null => (value === null || value.trim() === '' ? null : value);

/** The four rounds the client's Unity Cup panel lists, in its own order. */
const PRESEASON_ROUNDS = [1, 2, 3, 4] as const;

/**
 * The three states a round can be in, as data the template branches on rather than as a bare
 * `outcome === null` test repeated four times. `absent` is a round with no stored row; `unrecorded`
 * is a stored row whose outcome was cleared. They read the same to a Trainer but are different
 * records, and the sentence under the table is what states that a blank is never a loss.
 */
const preseasonRounds = computed(() => PRESEASON_ROUNDS.map((round) => {
    const row = (props.team.preseason_rounds ?? []).find((entry) => entry.round === round);

    if (row === undefined) {
        return { round, state: 'absent' as const, label: 'Not recorded' };
    }

    if (row.outcome === null) {
        return { round, state: 'unrecorded' as const, label: 'Not recorded' };
    }

    return {
        round,
        state: row.outcome === 'WIN' ? ('won' as const) : ('lost' as const),
        label: row.outcome === 'WIN' ? 'Win' : 'Loss',
    };
}));

/** Whether any round carries a stored row at all, so the block can say what "absent" means once. */
const hasPreseasonRows = computed(() => (props.team.preseason_rounds?.length ?? 0) > 0);
</script>

<template>
    <!-- Named by `aria-label`, not by the `h2` below: the `h2` is the team's own name, so labelling the
         landmark with it would make the landmark's name change every time the team is renamed and would
         leave the region unnamed on a run that recorded no name. A fixed region name is what a
         screen-reader landmark list needs, and the team's name stays the section's visible heading. -->
    <section role="region" aria-label="Team overview" class="card">
        <div class="flex flex-wrap items-start justify-between gap-x-4 gap-y-2">
            <div class="min-w-0">
                <h2 class="text-lg font-semibold text-ink-strong">
                    {{ shown(props.team.team_name) ?? 'Team not named' }}
                </h2>
                <p v-if="shown(props.team.team_motto) !== null" class="mt-0.5 text-sm text-ink">
                    &ldquo;{{ props.team.team_motto }}&rdquo;
                </p>
                <p v-else class="mt-0.5 text-sm text-ink-muted">
                    No motto recorded for this team.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="pill pill-outline">
                    Team Rank
                    <span class="ml-1 font-mono font-bold text-ink-strong">{{ props.team.rank ?? 'N/A' }}</span>
                </span>
                <span v-if="props.team.facility_level !== null" class="pill pill-neutral">
                    Ladder level {{ props.team.facility_level }}
                </span>
            </div>
        </div>

        <!-- The progression record, as tiles: the client's own counters (§1.8), each one a figure the
             Trainer read off the team panel. `.tiles` + the tile-columns token so five figures sit on
             one row where they fit and fold rather than squeeze. -->
        <div class="tiles growth-row mt-3">
            <div class="tile">
                <div class="k">League placement</div>
                <div v-if="props.team.team_league_placement !== null" class="v">
                    {{ props.team.team_league_placement }}<span class="text-xs font-semibold text-ink-muted"> place</span>
                </div>
                <div v-else class="v na">N/A</div>
            </div>
            <div class="tile">
                <div class="k">Ranking bonus</div>
                <div v-if="props.team.league_bonus !== null" class="v">
                    +{{ props.team.league_bonus }}
                </div>
                <div v-else class="v na" title="No band of the scenario's published ladder names this standing, so no bonus is printed.">
                    N/A
                </div>
            </div>
            <div class="tile">
                <div class="k">Preseason wins</div>
                <div v-if="props.team.team_preseason_wins !== null" class="v">{{ props.team.team_preseason_wins }}</div>
                <div v-else class="v na">N/A</div>
            </div>
            <div class="tile">
                <div class="k">Unity Trainings</div>
                <div v-if="props.team.unity_trainings_count !== null" class="v">{{ props.team.unity_trainings_count }}</div>
                <div v-else class="v na">N/A</div>
            </div>
            <div class="tile">
                <div class="k">Spirit Bursts</div>
                <div v-if="props.team.spirit_bursts_count !== null" class="v">{{ props.team.spirit_bursts_count }}</div>
                <div v-else class="v na">N/A</div>
            </div>
            <div class="tile">
                <div class="k">Extreme Bursts</div>
                <div v-if="props.team.extreme_bursts_count !== null" class="v">{{ props.team.extreme_bursts_count }}</div>
                <div v-else class="v na">N/A</div>
            </div>
            <div class="tile">
                <div class="k">Combined bursts</div>
                <div v-if="props.team.combined_bursts !== null" class="v">
                    {{ props.team.combined_bursts }}
                    <span v-if="props.team.burst_band !== null" class="text-xs font-semibold text-ink-muted">
                        in {{ props.team.burst_band }}
                    </span>
                </div>
                <div
                    v-else
                    class="v na"
                    title="Both tallies have to be recorded before they can be added: an unrecorded one is not a zero."
                >N/A</div>
            </div>
            <div class="tile">
                <div class="k">Team race in</div>
                <div v-if="props.team.team_race_countdown !== null && props.team.team_race_countdown !== undefined" class="v">
                    {{ props.team.team_race_countdown }}
                    <span class="text-xs font-semibold text-ink-muted">turn(s)</span>
                </div>
                <div v-else class="v na">
                    <AbsenceValue
                        compact
                        reason="No team race countdown has been recorded for this run. It is a figure you read off the client; no source publishes the career turn a round falls on, so this tool cannot compute one."
                    />
                </div>
            </div>

            <div class="tile">
                <div class="k">Observed members</div>
                <div v-if="props.team.observed_member_count !== null" class="v">
                    {{ props.team.observed_member_count }}
                </div>
                <div v-else class="v na">N/A</div>
            </div>
            <!-- KI-133: the Spirit gauge has no per-run per-member column and no published maximum,
                 so the only figure the tool can hold is the aggregate the Trainer transcribes. Its
                 label says "manual proxy" here for the same reason the editor's field does: a proxy
                 is not a measurement, and printing it as one would overstate the record. -->
            <div class="tile">
                <div class="k">Current Spirit (proxy)</div>
                <div v-if="props.team.current_spirit_proxy !== null" class="v">{{ props.team.current_spirit_proxy }}</div>
                <div v-else class="v na">
                    <AbsenceValue
                        compact
                        reason="No Spirit aggregate has been entered. The game publishes no per-member maximum, so this tool holds a figure the Trainer reads and enters, not a gauge it measures."
                    />
                </div>
            </div>
        </div>

        <!-- D-10: the four preseason rounds, each read as its own record. The table exists because
             the three states it prints are genuinely different facts — a round never opened, a round
             opened and left blank, and a round held and lost — and a summary count cannot tell them
             apart. The legacy `team_preseason_wins` tile above stays exactly as it was: it is the
             tally the Trainer read off the client, and nothing here recomputes it from these rows. -->
        <div class="mt-3 rounded-md border border-rule-soft" data-region="preseason-rounds">
            <h3 class="border-b border-rule-soft px-3 py-2 text-sm font-semibold text-ink-strong">Preseason rounds</h3>
            <ul class="divide-y divide-rule-soft">
                <li
                    v-for="round in preseasonRounds"
                    :key="round.round"
                    class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1 px-3 py-2 text-sm"
                    :data-round-state="round.state"
                >
                    <span class="text-ink-muted">Round {{ round.round }}</span>
                    <span v-if="round.state === 'won'" class="font-semibold text-status-positive">
                        {{ round.label }}
                    </span>
                    <span v-else-if="round.state === 'lost'" class="font-semibold text-risk">
                        {{ round.label }}
                    </span>
                    <span v-else class="text-ink-muted">{{ round.label }}</span>
                </li>
            </ul>
            <p class="border-t border-rule-soft px-3 py-2 text-xs text-ink-muted">
                <template v-if="!hasPreseasonRows">
                    No preseason round has been opened yet, so all four read
                    <em>Not recorded</em>. A round with no result recorded is not a loss.
                </template>
                <template v-else>
                    A round reading <em>Not recorded</em> was not recorded; it is never counted as a
                    loss. The Preseason wins figure above is the tally you read off the client and is
                    not recalculated from these rows.
                </template>
            </p>
        </div>

        <p class="mt-2 text-xs text-ink-muted">
            Team Rank is the ladder letter. League placement is where the run stands in the league, and
            the ranking bonus beside it is what that standing pays. Two different facts, so neither is
            derived from the other.
            <template v-if="props.team.tournament?.cadence_months">
                A team race arrives every {{ props.team.tournament.cadence_months }} months, at
                {{ props.team.tournament.month_anchors.join(' and ') }}.
            </template>
        </p>

        <!-- The one write. A disclosure rather than a permanent form, so the page reads before it
             edits; the editor itself is unchanged, which is what keeps `runs.update` the single door
             and the blank-posts-null rule (`D-220`) intact. -->
        <details id="team-state-editor" class="mt-4 rounded-md border border-rule bg-raised">
            <summary class="flex min-h-11 cursor-pointer list-item items-center px-3 text-sm font-semibold text-ink-strong">
                Edit team state
            </summary>
            <div class="border-t border-rule p-3">
                <p class="text-xs text-ink-muted">
                    An edit here writes through the run's own update route, so it is the same write the
                    Cockpit makes. A blank field posts nothing rather than a zero.
                </p>
                <UnityCupEditor
                    class="mt-3"
                    :action="props.action"
                    :umamusume-id="props.umamusumeId"
                    :status="props.status"
                    :team="props.team"
                />
            </div>
        </details>
    </section>
</template>
