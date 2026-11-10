<script setup lang="ts">
/*
 * One card: the per-character Spirit gauge, an absence with its reason.
 *
 * Extracted from `UnityCupPanel.vue` for `S-RS-VISUAL-1` (see `TeamCard.vue` for why the panel split).
 * The value and the bar are the same reading (design-2.0 §45). No column records a per-member
 * Spirit value and no sourced maximum exists, so the track renders empty and the number reads N/A
 * with the reason. Copy is carried over verbatim except for the KI-133 addition: the Trainer may
 * enter ONE aggregate as a labelled manual proxy for the whole team, and when they have, the
 * number reads that figure beside the proxy label rather than pretending it is a game reading.
 */
import ResourceMeter from '../ResourceMeter.vue';
import AlertRow from '../AlertRow.vue';

const props = defineProps<{
    /**
     * The run's `current_spirit_proxy`: one Trainer-entered aggregate for the whole team, or null
     * until they enter one (KI-133). A manual proxy, not a game value: the gauge is per team
     * member with no published maximum, so this is-labelled beats a per-run integer read as one.
     */
    spiritProxy: number | null;
}>();
</script>

<template>
    <div class="card p-3">
        <h2 class="text-lg font-semibold text-ink-strong">Spirit</h2>
        <ResourceMeter
            class="mt-2"
            label="Current Spirit (manual proxy)"
            :value="props.spiritProxy"
            :max="null"
            :absence="'This build does not record a Spirit value. The client prints the gauge on its Team Info panel; until an input stores it, there is no reading to show.'"
        />
        <!-- KI-133's honesty line: a figure here is one Trainer-entered aggregate for the whole team,
             not a game reading, and the client's gauge has no published maximum for it to sit against. -->
        <p v-if="props.spiritProxy !== null" class="mt-1 text-xs text-ink-muted">
            Manual proxy, not a game value. The gauge is per team member; this one figure is the
            aggregate the Trainer entered for the whole team, with no published maximum to sit against.
        </p>
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
