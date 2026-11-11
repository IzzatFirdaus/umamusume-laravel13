<script setup lang="ts">
/*
 * C2, the discipline comparison: the recorded facts a Trainer weighs side by side when choosing
 * between Speed and Guts at turn 40 - the deck's attendance, the facility levels this run has
 * actually read, and the run's own historical mean gain - in one table beside the published bases
 * the option cards already print.
 *
 * It is a record, not a projection. The note states the boundary in the tool's own voice, and every
 * unrecorded cell renders N/A with its reason rather than a default that would read as data. The
 * history arrives as one prop computed server-side from the run's own rows, so this table and the
 * write path cannot disagree about what a run has recorded.
 */
defineProps<{
    /** One entry per discipline, keyed by the client's stat name, from `discipline_history`. */
    history: Record<string, {
        attendance_count: number;
        recorded_facility_levels: number[];
        historical_gain: { mean: number; sample_size: number } | null;
    }>;
    /** The five disciplines in the client's own order, from the config's `stat_order`. */
    order: string[];
}>();
</script>

<template>
    <table class="t">
        <thead>
            <tr>
                <th scope="col">Discipline</th>
                <th scope="col" class="n">Attendance</th>
                <th scope="col">Recorded facility levels</th>
                <th scope="col" class="n">Historical mean gain</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="stat in order" :key="stat">
                <td>{{ stat }}</td>
                <td class="n">{{ history[stat]?.attendance_count ?? 0 }}</td>
                <td>
                    <span v-if="(history[stat]?.recorded_facility_levels ?? []).length > 0">
                        {{ (history[stat] as { recorded_facility_levels: number[] }).recorded_facility_levels.join(', ') }}
                    </span>
                    <span v-else title="No facility level has been recorded for this discipline yet.">N/A</span>
                </td>
                <td class="n">
                    <span v-if="history[stat]?.historical_gain !== null && history[stat]?.historical_gain !== undefined">
                        +{{ (history[stat] as { historical_gain: { mean: number; sample_size: number } }).historical_gain.mean }}
                        <span class="text-ink-muted">· {{ (history[stat] as { historical_gain: { mean: number; sample_size: number } }).historical_gain.sample_size }} turn{{ (history[stat] as { historical_gain: { mean: number; sample_size: number } }).historical_gain.sample_size === 1 ? '' : 's' }}</span>
                    </span>
                    <span v-else title="No pair of recorded turns lets this run state a gain for this discipline yet.">N/A</span>
                </td>
            </tr>
        </tbody>
    </table>
</template>
