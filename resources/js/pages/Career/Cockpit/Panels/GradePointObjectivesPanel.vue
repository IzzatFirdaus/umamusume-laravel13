<script setup lang="ts">
/*
 * Trackblazer Grade Point Objectives panel.
 *
 * Registered against the `grade_objectives` flag. Draws the grade point
 * objectives with sub-cells, provenance badges, and progress indicators.
 */

import AlertRow from '../../../../components/scenario/AlertRow.vue';
import ProvenanceBadge from '../../../../components/ProvenanceBadge.vue';
import SubCell from '../../../../components/SubCell.vue';
import type { ProvenanceState } from '../../types';

type Badge = ProvenanceState;

interface ObjectiveRow {
    label: string;
    value: string | number | null;
    target: string | number | null;
    badge: Badge | null;
    badge_title: string | null;
    absence: string | null;
}

interface ObjectiveGroup {
    group: string;
    rows: ObjectiveRow[];
    empty_note: string | null;
}

const props = defineProps<{
    scenario: {
        objectives: ObjectiveGroup[];
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
            v-for="group in props.scenario.objectives"
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
                {{ group.empty_note ?? 'No grade point objectives recorded for this scenario yet.' }}
            </p>

            <ul v-else class="flex flex-col gap-2">
                <li
                    v-for="row in group.rows"
                    :key="row.label"
                    class="flex flex-col gap-1 rounded-md border border-rule bg-panel p-3"
                >
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <span class="font-medium text-ink-strong">{{ row.label }}</span>
                        <ProvenanceBadge
                            v-if="row.badge !== null"
                            :state="row.badge"
                            :title="row.badge_title ?? undefined"
                        />
                    </div>

                    <SubCell
                        label="Current"
                        :value="row.value === null ? 'N/A' : String(row.value)"
                        value-class="font-mono text-base tabular-nums text-ink-strong"
                        :title="row.absence ?? (row.value === null ? 'No current reading was recorded.' : undefined)"
                    />

                    <SubCell
                        label="Target"
                        :value="row.target === null ? 'N/A' : String(row.target)"
                        value-class="font-mono text-base tabular-nums text-ink-strong"
                        :title="row.target === null ? 'No target is set for this objective.' : undefined"
                    />
                </li>
            </ul>
        </section>
    </div>
</template>