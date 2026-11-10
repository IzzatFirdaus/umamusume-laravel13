<script setup lang="ts">
/*
 * One card: this run's Special Training facts — the penalty's removal and the Wit bonus.
 *
 * Extracted from `UnityCupPanel.vue` for `S-RS-VISUAL-1`. Copy carried over verbatim. The caller
 * decides whether it renders: it draws only when config states one of the two facts.
 */
defineProps<{
    specialTraining: {
        energy_penalty_removed: boolean | null;
        wit_burst_energy_bonus: number | null;
    };
}>();
</script>

<template>
    <div class="card p-3">
        <h2 class="text-lg font-semibold text-ink-strong">Special Training</h2>
        <p v-if="specialTraining.energy_penalty_removed" class="mt-2 text-sm text-ink">
            The extra Energy cost on Special Training was removed on 2026-07-01.
        </p>
        <p v-if="specialTraining.wit_burst_energy_bonus !== null" class="mt-1 text-sm text-ink">
            A burst on the Wit facility grants
            {{ specialTraining.wit_burst_energy_bonus }} Energy recovery.
        </p>
        <p class="mt-2 text-xs text-ink-muted" title="This build does not record facility levels, per-member training state or Special Training gains; the Cockpit's turns carry what the Trainer entered.">
            Per-facility levels and per-member training state are not recorded here.
        </p>
    </div>
</template>
