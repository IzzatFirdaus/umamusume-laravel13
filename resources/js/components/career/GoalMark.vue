<script setup lang="ts">
/*
 * The client's Goal pennant, stated in words (PRD FR-A-10, ADR-0026).
 *
 * `resources/css/app.css:70-80` reserved `--color-goal` for exactly this mark and withheld it until a
 * per-character goal table existed to drive it honestly. It does now, so the token is spent rather than
 * a new one added: this is Track 1, "spend the existing client-derived tokens more deliberately"
 * (frontend-fidelity §5), and no radius, shadow, type tier or motion is introduced here.
 *
 * **The word carries the meaning; the colour only pairs with it.** `DESIGN.md`'s glyph-plus-word rule and
 * `ux-laws-audit-plan` §9 item 3 both bind: a mark that reads as colour alone fails, and a goal is an
 * obligation rather than a fault, so `--color-risk` stays on errors and this never borrows an alert
 * triangle. "Goal" is printed on every use, including for a screen reader.
 *
 * One component because the same mark belongs on three strip regions, the decision screen and the race
 * drawer. Four copies of a span is how five vocabularies for one fact start.
 */
const props = defineProps<{
    /** The objective as the source states it, already composed by `TraineeGoal::obligationLine()`. */
    requirement: string;
}>();
</script>

<template>
    <span class="w-full text-xs">
        <span class="font-semibold text-goal">Goal</span>
        <span class="text-ink-muted">· {{ props.requirement }}</span>
    </span>
</template>
