<script setup lang="ts">
/*
 * Unity Cup Team Race Facility panel.
 *
 * Registered against the `team_race_facility` flag. Draws the team race
 * facility overview with sub-cells and status pills.
 */

import AlertRow from '../../../../components/scenario/AlertRow.vue';
import StatusPill from '../../../../components/StatusPill.vue';
import SubCell from '../../../../components/SubCell.vue';

interface FacilityRow {
    label: string;
    value: string | number | null;
    state: string | null;
    absence: string | null;
}

interface FacilityGroup {
    group: string;
    rows: FacilityRow[];
    empty_note: string | null;
}

const props = defineProps<{
    scenario: {
        facilities: FacilityGroup[];
        alerts: { level: string; text: string; detail: string | null }[];
    };
    label: string;
    kind: 'widget' | 'panel';
    name: string;
}>();

const headingId = (group: string): string =>
    `${props.name}-${group.replace(/\s+/g, '-').toLowerCase()}`;

const statusVariant = (state: string | null): 'info' | 'success' | 'warning' | 'neutral' => {
    if (!state) return 'neutral';
    switch (state) {
        case 'active': return 'info';
        case 'completed': return 'success';
        case 'warning': return 'warning';
        default: return 'neutral';
    }
};
</script>

<template>
    <div class="flex flex-col gap-4">
        <AlertRow
            v-for="alert in props.scenario.alerts"
            :key="alert.text"
            glyph="▲"
            :text="`${alert.level}: ${alert.text}`"
            :detail="alert.detail"
            tone="critical"
        />

        <section
            v-for="group in props.scenario.facilities"
            :key="group.group"
            class="flex flex-col gap-2"
            :aria-labelledby="headingId(group.group)"
        >
            <h3
                :id="headingId(group.group)"
                class="text-lg font-semibold text-ink-strong"
            >
                {{ group.group }}
            </h3>

            <p v-if="group.rows.length === 0" class="text-sm text-ink-muted">
                {{ group.empty_note ?? 'No facility data recorded for this scenario yet.' }}
            </p>

            <ul v-else class="flex flex-col gap-2">
                <li
                    v-for="row in group.rows"
                    :key="row.label"
                    class="flex flex-wrap items-baseline gap-3 text-sm text-ink"
                >
                    <SubCell
                        :label="row.label"
                        :value="row.value === null ? 'N/A' : String(row.value)"
                        value-class="font-mono text-base tabular-nums text-ink-strong"
                        :title="row.absence ?? (row.value === null ? 'No reading was recorded for this facility.' : undefined)"
                    />
                    <StatusPill
                        v-if="row.state !== null"
                        :label="row.state"
                        :variant="statusVariant(row.state)"
                        size="sm"
                    />
                </li>
            </ul>
        </section>
    </div>
</template>