<script setup lang="ts">
/*
 * Unity Cup Spirit Bursts panel.
 *
 * Registered against the `spirit_bursts` flag. Draws the Spirit Burst
 * count with provenance badges and sub-cells.
 */

import AlertRow from '../../../../components/scenario/AlertRow.vue';
import ProvenanceBadge from '../../../../components/ProvenanceBadge.vue';
import SubCell from '../../../../components/SubCell.vue';
import type { ProvenanceState } from '../../types';

type Badge = ProvenanceState;

interface BurstRow {
    label: string;
    value: string | number | null;
    badge: Badge | null;
    badge_title: string | null;
    absence: string | null;
}

interface BurstGroup {
    group: string;
    rows: BurstRow[];
    empty_note: string | null;
}

const props = defineProps<{
    scenario: {
        bursts: BurstGroup[];
        alerts: { level: string; text: string; detail: string | null }[];
    };
    label: string;
    kind: 'widget' | 'panel';
    name: string;
}>();

const headingId = (group: string): string =>
    `${props.name}-${group.replace(/\s+/g, '-').toLowerCase()}`;
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
            v-for="group in props.scenario.bursts"
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
                {{ group.empty_note ?? 'No Spirit Burst data recorded for this scenario yet.' }}
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
                        :title="row.absence ?? (row.value === null ? 'No reading was recorded for this Spirit Burst count.' : undefined)"
                    />
                    <ProvenanceBadge
                        v-if="row.badge !== null"
                        :state="row.badge"
                        :title="row.badge_title ?? undefined"
                    />
                </li>
            </ul>
        </section>
    </div>
</template>