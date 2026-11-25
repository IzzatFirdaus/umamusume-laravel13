<script setup lang="ts">
/*
 * The team's five attribute grades (`UC2.2`), as one strip.
 *
 * These are the TEAM's grades, which the client prints on its Team Info panel (§1.8), and they are
 * not the trainee's own stats, not the individual members' stats, and not the facility levels. A
 * grade is a letter the client computes for the team; a facility level is a rung on the training
 * ladder. The two travel side by side here and never merge, because deriving one from the other is
 * exactly the inference `ADR-0034` refuses: the ladder gives a facility level for the run's Team
 * Rank, and no published source maps a per-stat grade onto a training level.
 *
 * **The colour is a stat's own, and the letter is always printed.** `StatBadge`'s rule applies: the
 * label and the grade both read as words, so a reader who cannot separate the five fills still reads
 * the entry. An unrecorded grade uses the absence channel, so "no grade recorded" is not a blank.
 *
 * **S+ is absent on purpose.** Per-stat grades stop at S; S+ is the overall Team Rank, which is a
 * different field and lives in the overview. The editor states the same rule beside its selects.
 */
import AbsenceValue from '../AbsenceValue.vue';

const props = defineProps<{
    /** The five grades, in the config's own stat order, each nullable. */
    grades: { key: string; label: string; grade: string | null }[];
    /**
     * The ladder rung this run's Team Rank resolves to, or null. `ADR-0030`/`ADR-0034`: it is derived
     * from the rank letter, so it is printed as the rank's reading rather than a sixth grade.
     */
    facilityLevel: number | null;
    /** The run's recorded Team Rank letter; the strip's own subject is the five grades beside it. */
    rank: string | null;
}>();

/*
 * Whole-string class maps, per `StatBadge.vue`'s rule: Tailwind v4 scans source text, so an
 * interpolated `text-stat-${key}-ink` would compile to nothing. Five keys, five literals.
 */
const INK: Record<string, string> = {
    Speed: 'text-stat-speed-ink',
    Stamina: 'text-stat-stamina-ink',
    Power: 'text-stat-power-ink',
    Guts: 'text-stat-guts-ink',
    Wit: 'text-stat-wit-ink',
};

const MARK: Record<string, string> = {
    Speed: 'bg-tint-speed border-line-speed',
    Stamina: 'bg-tint-stamina border-line-stamina',
    Power: 'bg-tint-power border-line-power',
    Guts: 'bg-tint-guts border-line-guts',
    Wit: 'bg-tint-wit border-line-wit',
};

const ink = (key: string): string => INK[key] ?? 'text-ink-strong';
const mark = (key: string): string => MARK[key] ?? 'bg-sunken border-rule';
</script>

<template>
    <section aria-labelledby="team-grades-heading" class="card">
        <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
            <h2 id="team-grades-heading" class="text-lg font-semibold text-ink-strong">Team attribute grades</h2>
            <p v-if="props.rank !== null" class="text-xs text-ink-muted">
                Overall Team Rank
                <span class="font-mono font-bold text-ink-strong">{{ props.rank }}</span>
                <template v-if="props.facilityLevel !== null">
                    · ladder level {{ props.facilityLevel }}
                </template>
            </p>
        </div>

        <ul class="tiles growth-row mt-3" aria-label="Team attribute grades">
            <li
                v-for="grade in props.grades"
                :key="grade.key"
                :class="['tile flex items-baseline justify-between gap-2 border', mark(grade.key)]"
            >
                <span :class="['text-sm font-semibold', ink(grade.key)]">{{ grade.label }}</span>
                <span v-if="grade.grade !== null" :class="['font-mono text-2xl font-extrabold', ink(grade.key)]">
                    {{ grade.grade }}
                </span>
                <AbsenceValue
                    v-else
                    compact
                    :class="['font-mono', ink(grade.key)]"
                    reason="No team grade is recorded for this attribute yet. Enter it in the team-state editor; a team's grade is not the sum of its members' stats."
                />
            </li>
        </ul>

        <p class="mt-2 text-xs text-ink-muted">
            These are the team's own grades and stop at S. The overall Team Rank above them is the
            ladder letter and the only field that carries S+. Individual member stats are recorded on
            the roster below, and neither set is averaged into the other.
        </p>
    </section>
</template>
