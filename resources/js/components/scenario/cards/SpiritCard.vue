<script setup lang="ts">
/*
 * One card: the per-character Spirit gauge, an absence with its reason.
 *
 * Extracted from `UnityCupPanel.vue` for `S-RS-VISUAL-1` (see `TeamCard.vue` for why the panel split).
 * The value and the bar are the same reading (design-2.0 §45). No column records a Spirit value and
 * no sourced maximum exists, so the track renders empty and the number reads N/A with the reason.
 * Copy is carried over verbatim.
 */
import ResourceMeter from '../ResourceMeter.vue';
import AlertRow from '../AlertRow.vue';
</script>

<template>
    <div class="card p-3">
        <h2 class="text-lg font-semibold text-ink-strong">Spirit</h2>
        <ResourceMeter
            class="mt-2"
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
            detail="Both are advice this build holds: the trigger rule is recorded (a burst fires on the next Special Training once a teammate's gauge is full), but what to do about it and what it would yield are not computed here."
            tone="note"
        />
    </div>
</template>
