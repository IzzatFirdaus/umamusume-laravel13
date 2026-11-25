<script setup lang="ts">
/*
 * The Unity Cup team state, editable (R2-14).
 *
 * A sibling of `components/scenario/UnityCupPanel.vue` rather than an extension of it. That component
 * is a panel-registry member: the shell mounts it to *read* the §49 section, its props come from the
 * registry bundle, and it owns no write surface. Giving it one would thread edit state through the
 * registry for a single caller. This one is run-scoped, is rendered by the Cockpit alone, and posts
 * the same `runs.update` route the header forms use.
 *
 * Every field is null until a Trainer states it, so an empty input posts `null` and never `0`: zero is
 * not a league placement, and a zero burst count is a real reading rather than an unstated one (D-220).
 * The labels are `UnityCupPanel.vue`'s own words, so the editor and the panel it edits name one fact
 * once.
 */
import { useForm } from '@inertiajs/vue3';
import { BaseInput, BaseSelect } from '../Form';

const GRADE_OPTIONS = [
    { value: '', label: 'Not recorded' },
    { value: 'G', label: 'G' },
    { value: 'F', label: 'F' },
    { value: 'E', label: 'E' },
    { value: 'D', label: 'D' },
    { value: 'C', label: 'C' },
    { value: 'B', label: 'B' },
    { value: 'A', label: 'A' },
    { value: 'S', label: 'S' },
];

/**
 * `D-10`'s two recorded outcomes. Null is not a third outcome: a round left blank stays `''` in the
 * form and posts null, which the server stores as an explicit unknown, never as a loss.
 */
const OUTCOME_OPTIONS = [
    { value: '', label: 'Not recorded' },
    { value: 'WIN', label: 'Win' },
    { value: 'LOSS', label: 'Loss' },
];

/*
 * ADR-0034's vocabulary, mirrored here so the select states the whole set: `S+` is a letter the
 * client's board shows and the ladder deliberately gives no facility level, so its label says so
 * rather than implying a missing option.
 */
const RANK_OPTIONS = [
    { value: '', label: 'Not recorded' },
    { value: 'G', label: 'G' },
    { value: 'F', label: 'F' },
    { value: 'E', label: 'E' },
    { value: 'D', label: 'D' },
    { value: 'C', label: 'C' },
    { value: 'B', label: 'B' },
    { value: 'A', label: 'A' },
    { value: 'S', label: 'S' },
    { value: 'S+', label: 'S+ (no facility level)' },
];

const props = defineProps<{
    /** `route('runs.update', $run)`, supplied by the server like the header forms' own action. */
    action: string;
    /** Carried because `StoreTrainingRunRequest` requires both on every write to that route. */
    umamusumeId: number;
    status: string;
    /** The run's team payload as `CockpitController` sends it, every field nullable. */
    team: {
        /** The run's current league letter, seeded into the select; null is "not stated". */
        rank: string | null;
        team_name: string | null;
        team_motto: string | null;
        team_league_placement: number | null;
        team_preseason_wins: number | null;
        /**
         * `D-10`'s per-round outcomes: one entry per stored row. A round with no entry was never
         * opened; an entry whose `outcome` is null was opened and left blank. The two are different
         * records and neither is a loss (D-220), so the select keeps `Not recorded` as its own value
         * and never falls back to a loss.
         */
        preseason_rounds?: { round: number; outcome: string | null }[];
        unity_trainings_count: number | null;
        spirit_bursts_count: number | null;
        extreme_bursts_count: number | null;
        /**
         * KI-133's manual proxy: one aggregate for the whole team the Trainer enters, labelled
         * on the display. Null until they do; blank posts null rather than zero (D-220).
         */
        current_spirit_proxy: number | null;
        stat_grades: { key: string; label: string; grade: string | null }[];
    };
}>();

const text = (value: string | null): string => value ?? '';
const numeric = (value: number | null): string => (value === null ? '' : String(value));

/**
 * The outcome the run stored for a round, as the select's own value. No row and a null outcome both
 * read `Not recorded` on screen, but only the stored value is what "unchanged" is measured against,
 * so a round the Trainer never opened never travels as a write (D-10).
 */
const seededRound = (round: number): string =>
    (props.team.preseason_rounds ?? []).find((entry) => entry.round === round)?.outcome ?? '';

const form = useForm({
    umamusume_id: String(props.umamusumeId),
    status: props.status,
    team_rank: props.team.rank ?? '',
    team_name: text(props.team.team_name),
    team_motto: text(props.team.team_motto),
    team_league_placement: numeric(props.team.team_league_placement),
    team_preseason_wins: numeric(props.team.team_preseason_wins),
    unity_trainings_count: numeric(props.team.unity_trainings_count),
    spirit_bursts_count: numeric(props.team.spirit_bursts_count),
    extreme_bursts_count: numeric(props.team.extreme_bursts_count),
    current_spirit_proxy: numeric(props.team.current_spirit_proxy),
    team_stat_grades: Object.fromEntries(
        props.team.stat_grades.map((grade) => [grade.key, grade.grade ?? '']),
    ) as Record<string, string>,
    // `D-10`: one select per round, seeded from the run's own rows. A round with no row seeds
    // `Not recorded`, which is exactly what "no row" means, so opening the form changes nothing.
    preseason_rounds: Object.fromEntries(
        [1, 2, 3, 4].map((round) => [
            round,
            (props.team.preseason_rounds ?? []).find((entry) => entry.round === round)?.outcome ?? '',
        ]),
    ) as Record<number, string>,
});

const save = (): void => {
    form
        // A blank field is "not stated" and `0` is a figure the Trainer read, so the two are kept
        // apart here rather than collapsed by a `Number('')` on the way out (D-220).
        .transform((data) => {
            // `v-model` on a number input hands back a number once one is typed and `''` when the
            // field is cleared, so the blank test is an identity check and never a `.trim()`: a
            // number has no `trim`, which is how the first cut of this threw at submit.
            const figure = (value: string): number | null => (value === '' ? null : Number(value));

            return {
                ...data,
                team_name: data.team_name.trim() === '' ? null : data.team_name,
                team_motto: data.team_motto.trim() === '' ? null : data.team_motto,
                team_league_placement: figure(data.team_league_placement),
                team_preseason_wins: figure(data.team_preseason_wins),
                unity_trainings_count: figure(data.unity_trainings_count),
                spirit_bursts_count: figure(data.spirit_bursts_count),
                extreme_bursts_count: figure(data.extreme_bursts_count),
                current_spirit_proxy: figure(data.current_spirit_proxy),
                // A blank select is "not recorded"; a letter is the Trainer's reading (D-220).
                team_stat_grades: Object.fromEntries(
                    Object.entries(data.team_stat_grades).map(([stat, grade]) => [stat, grade === '' ? null : grade]),
                ),
                // `D-10`: only the rounds the Trainer moved travel, so a save that touched one round
                // cannot rewrite its three siblings. A blank posts null — a round opened and left
                // blank is an explicit unknown, never a loss (D-220), and never a deletion.
                preseason_rounds: Object.fromEntries(
                    Object.entries(data.preseason_rounds ?? {})
                        .filter(([round, outcome]) => outcome !== seededRound(Number(round)))
                        .map(([round, outcome]) => [round, outcome === '' ? null : outcome]),
                ),
            };
        })
        .put(props.action, { preserveScroll: true });
};
</script>

<template>
    <!-- The disclosure is the caller's, not this component's: the Cockpit wraps this editor in the
         one `<details>` that owns the gating, the open-while-Active behaviour and the team-state
         region marker. This component used to carry its own identical `<details>` as well, which
         rendered a disclosure inside a disclosure under one label — the strict-mode collision the
         team-editor browser cases caught (fixed 2026-10-16). The form is the component's root. -->
    <form class="grid grid-cols-1 gap-3 text-sm @3xl:grid-cols-2" @submit.prevent="save">
            <label for="team_name" class="flex flex-col gap-1">
                <span class="text-ink-muted">Team name</span>
                <BaseInput
                    id="team_name"
                    v-model="form.team_name"
                    type="text"
                    name="team_name"
                    maxlength="255"
                />
            </label>
            <label for="team_motto" class="flex flex-col gap-1">
                <span class="text-ink-muted">Team motto</span>
                <BaseInput
                    id="team_motto"
                    v-model="form.team_motto"
                    type="text"
                    name="team_motto"
                    maxlength="255"
                />
            </label>
            <label for="team_league_placement" class="flex flex-col gap-1">
                <span class="text-ink-muted">League placement</span>
                <BaseInput
                    id="team_league_placement"
                    v-model="form.team_league_placement"
                    type="number"
                    name="team_league_placement"
                    min="1"
                    max="99"
                />
            </label>
            <label for="team_preseason_wins" class="flex flex-col gap-1">
                <span class="text-ink-muted">Preseason wins</span>
                <BaseInput
                    id="team_preseason_wins"
                    v-model="form.team_preseason_wins"
                    type="number"
                    name="team_preseason_wins"
                    min="0"
                    max="4"
                />
            </label>
            <label for="unity_trainings_count" class="flex flex-col gap-1">
                <span class="text-ink-muted">Unity Trainings</span>
                <BaseInput
                    id="unity_trainings_count"
                    v-model="form.unity_trainings_count"
                    type="number"
                    name="unity_trainings_count"
                    min="0"
                />
            </label>
            <label for="spirit_bursts_count" class="flex flex-col gap-1">
                <span class="text-ink-muted">Spirit Bursts</span>
                <BaseInput
                    id="spirit_bursts_count"
                    v-model="form.spirit_bursts_count"
                    type="number"
                    name="spirit_bursts_count"
                    min="0"
                />
            </label>
            <label for="extreme_bursts_count" class="flex flex-col gap-1">
                <span class="text-ink-muted">Extreme Bursts</span>
                <BaseInput
                    id="extreme_bursts_count"
                    v-model="form.extreme_bursts_count"
                    type="number"
                    name="extreme_bursts_count"
                    min="0"
                />
            </label>
            <!-- KI-133: the Spirit gauge has no per-run column (it is per team member with no
                 published maximum), so this is the labelled manual proxy the dispatch ruled:
                 one Trainer-entered aggregate for the whole team, kept apart from zero (D-220). -->
            <label for="current_spirit_proxy" class="flex flex-col gap-1">
                <span class="text-ink-muted">Current Spirit (manual proxy)</span>
                <BaseInput
                    id="current_spirit_proxy"
                    v-model="form.current_spirit_proxy"
                    type="number"
                    name="current_spirit_proxy"
                    min="0"
                />
            </label>

            <fieldset class="flex flex-col gap-2 @3xl:col-span-2">
                <legend class="text-ink-muted">Team stat grades</legend>
                <div class="grid grid-cols-1 gap-3 @3xl:grid-cols-5">
                    <label
                        v-for="grade in props.team.stat_grades"
                        :key="grade.key"
                        :for="`team_stat_grade_${grade.key}`"
                        class="flex flex-col gap-1"
                    >
                        <span class="text-ink-muted">{{ grade.label }}</span>
                        <BaseSelect
                            :id="`team_stat_grade_${grade.key}`"
                            v-model="form.team_stat_grades[grade.key]"
                            :name="`team_stat_grades[${grade.key}]`"
                            :options="GRADE_OPTIONS"
                        />
                    </label>
                </div>
                <!-- R10: per-stat ranks stop at S; S+ is the overall team rank and is not this column. -->
                <p class="text-xs text-ink-muted">
                    Per-stat grades are G to S. S+ is the overall Team Rank field above, never a
                    per-stat value.
                </p>
            </fieldset>

            <!-- `D-10`: the four preseason rounds, each its own record, so one round's result can be
                 entered or cleared without touching its siblings. `Not recorded` is a value, not an
                 empty option: a round never opened and a round opened and left blank are different
                 records, and neither is a loss (D-220). -->
            <fieldset class="flex flex-col gap-2 @3xl:col-span-2">
                <legend class="text-ink-muted">Preseason rounds</legend>
                <div class="grid grid-cols-1 gap-3 @3xl:grid-cols-4">
                    <label
                        v-for="round in [1, 2, 3, 4]"
                        :key="round"
                        :for="`preseason_round_${round}`"
                        class="flex flex-col gap-1"
                    >
                        <span class="text-ink-muted">Round {{ round }}</span>
                        <BaseSelect
                            :id="`preseason_round_${round}`"
                            v-model="form.preseason_rounds[round]"
                            :name="`preseason_rounds[${round}]`"
                            :options="OUTCOME_OPTIONS"
                        />
                    </label>
                </div>
                <p class="text-xs text-ink-muted">
                    A round left at <em>Not recorded</em> was not recorded; it is never counted as a
                    loss. Preseason wins above is the tally you read off the client, not a count of
                    these rounds.
                </p>
            </fieldset>

            <!-- ADR-0034 (owner ruling R6-48, 2026-11-15): the Team Rank is stored current state, so
                 this editor is its write door. The letter is entered, never inferred; `S+` is a legal
                 letter and the ladder gives it no facility level, which the option's own label states.
                 The turn-event path stays as `latestTeamRank()`'s fallback, not as a second writer. -->
            <div class="@3xl:col-span-2">
                <BaseSelect
                    id="team_rank"
                    v-model="form.team_rank"
                    name="team_rank"
                    label="Team Rank"
                    :options="RANK_OPTIONS"
                />
                <p class="mt-1 text-xs text-ink-muted">
                    The run's current league standing, exactly as the client's board prints it. Blank
                    means not stated, never a default letter.
                </p>
            </div>

            <div class="flex flex-col gap-1">
                <button
                    type="submit"
                    class="mt-1 inline-flex min-h-11 w-fit items-center rounded-full border-2 border-rule px-4 font-bold text-ink-strong disabled:opacity-60"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Saving…' : 'Save team state' }}
                </button>
                <p v-if="form.recentlySuccessful" class="text-xs text-ink-muted">Saved.</p>
            </div>
        </form>
</template>
