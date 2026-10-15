<script setup lang="ts">
/*
 * URA Finale Happy Meek panel.
 *
 * Registered against the `happy_meek` flag. Draws the Happy Meek level
 * with provenance badge, sub-cells, and N/A reasons.
 */

import AlertRow from '../../../../components/scenario/AlertRow.vue';
import ProvenanceBadge from '../../../../components/ProvenanceBadge.vue';
import SubCell from '../../../../components/SubCell.vue';
import type { ProvenanceState } from '../../types';

type Badge = ProvenanceState;

interface MeekRow {
    label: string;
    value: string | number | null;
    badge: Badge | null;
    badge_title: string | null;
    absence: string | null;
}

interface MeekGroup {
    group: string;
    rows: MeekRow[];
    empty_note: string | null;
}

const props = defineProps<{
    scenario: {
        meek: MeekGroup[];
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
            v-for="group in props.scenario.meek"
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
                {{ group.empty_note ?? 'No Happy Meek data recorded for this scenario yet.' }}
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
                        :title="row.absence ?? (row.value === null ? 'No reading was recorded for this Happy Meek level.' : undefined)"
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