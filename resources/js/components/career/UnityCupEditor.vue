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

const props = defineProps<{
    /** `route('runs.update', $run)`, supplied by the server like the header forms' own action. */
    action: string;
    /** Carried because `StoreTrainingRunRequest` requires both on every write to that route. */
    umamusumeId: number;
    status: string;
    /** The run's team payload as `CockpitController` sends it, every field nullable. */
    team: {
        team_name: string | null;
        team_motto: string | null;
        team_league_placement: number | null;
        team_preseason_wins: number | null;
        unity_trainings_count: number | null;
        spirit_bursts_count: number | null;
        extreme_bursts_count: number | null;
        stat_grades: { key: string; label: string; grade: string | null }[];
    };
}>();

const text = (value: string | null): string => value ?? '';
const numeric = (value: number | null): string => (value === null ? '' : String(value));

const form = useForm({
    umamusume_id: String(props.umamusumeId),
    status: props.status,
    team_name: text(props.team.team_name),
    team_motto: text(props.team.team_motto),
    team_league_placement: numeric(props.team.team_league_placement),
    team_preseason_wins: numeric(props.team.team_preseason_wins),
    unity_trainings_count: numeric(props.team.unity_trainings_count),
    spirit_bursts_count: numeric(props.team.spirit_bursts_count),
    extreme_bursts_count: numeric(props.team.extreme_bursts_count),
    team_stat_grades: Object.fromEntries(
        props.team.stat_grades.map((grade) => [grade.key, grade.grade ?? '']),
    ) as Record<string, string>,
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
                // A blank select is "not recorded"; a letter is the Trainer's reading (D-220).
                team_stat_grades: Object.fromEntries(
                    Object.entries(data.team_stat_grades).map(([stat, grade]) => [stat, grade === '' ? null : grade]),
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
                    Per-stat grades are G to S. S+ is the overall team rank, recorded elsewhere.
                </p>
            </fieldset>

            <!-- R4-13 / ruling R2 (2026-10-25): the editor offers no rank input, and silence about that
                 leaves a Trainer who read the client's rank with no path from this panel. The rank has an
                 owner already — a turn's team-race event records it — so the fix is this sentence rather
                 than a second writer for one fact. -->
            <p class="text-xs text-ink-muted @3xl:col-span-2">
                Team Rank is recorded through a turn's team-race event, not through this editor.
            </p>

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
