<script setup lang="ts">
/*
 * One card: the five per-stat team grades.
 *
 * Extracted from `UnityCupPanel.vue` for `S-RS-VISUAL-1`. An unrecorded grade keeps the shared
 * absence channel (`AbsenceValue`, `DESIGN.md` §5 (a)) rather than a bare `N/A`, which is the shape
 * `AbsenceChannelTest` pins for this surface.
 */
import AbsenceValue from '../../AbsenceValue.vue';

defineProps<{
    statGrades: { key: string; label: string; grade: string | null }[];
}>();
</script>

<template>
    <div class="card p-3">
        <h2 class="text-lg font-semibold text-ink-strong">Team stat grades</h2>
        <ul class="mt-2 flex flex-col gap-1" aria-label="Team stat grades">
            <li
                v-for="grade in statGrades"
                :key="grade.key"
                class="flex items-baseline justify-between gap-3 text-sm"
            >
                <span class="text-ink">{{ grade.label }}</span>
                <span
                    v-if="grade.grade !== null"
                    class="font-mono tabular-nums text-ink-strong"
                >{{ grade.grade }}</span>
                <AbsenceValue
                    v-else
                    compact
                    class="font-mono tabular-nums"
                    :reason="`No per-stat team grade is recorded for this run yet. Enter it in the team-state editor.`"
                />
            </li>
        </ul>
    </div>
</template>
