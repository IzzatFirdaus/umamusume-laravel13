<script setup lang="ts">
// The chip self-gates on composesPanel('epithet_routes') in Blade, the same flag as the
// checklist, not a race panel; kept as-is (G-33). D-230: the bands are percentages in the
// source against 1 / 2 / 3 / 4+ consecutive races and one source is not two, so the chip
// says a word and names where the number lives, never a percentage.
// `riskWord` is RaceFatiguePayload::riskWord() resolved server-side (the band derivation
// stays in the payload); `hideAfterLabel` is config race_fatigue.hide_after mapped through
// the server's label map (unmapped keys arrive as themselves, KI-18 lesson).
defineProps<{
    enabled: boolean;
    consecutiveRaces: number | null;
    riskWord: string | null;
    hideAfterLabel: string | null;
}>();
</script>

<template>
    <div v-if="enabled" class="rounded-md border border-rule bg-raised px-3 py-2 text-sm">
        <p class="flex flex-wrap items-baseline gap-x-3">
            <span class="font-bold text-ink-strong">Race fatigue</span>
            <span v-if="consecutiveRaces === null" class="text-ink-muted">no consecutive-race reading recorded</span>
            <span v-else class="text-ink">
                {{ consecutiveRaces }}
                {{ consecutiveRaces === 1 ? 'race' : 'races' }} in a row, a mood
                downgrade is <span class="font-bold">{{ riskWord }}</span>
            </span>
        </p>
        <p class="mt-1 text-xs text-ink-muted">
            The percentages and the countermeasures live in docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md section "Gameplay Flow & Race Fatigue".
            The table stops being quoted after late December, and the final three races pay
            coins instead of fatigue
            <template v-if="hideAfterLabel !== null">(this scenario's calendar hides the reading after {{ hideAfterLabel }})</template>
            .
        </p>
    </div>
</template>
