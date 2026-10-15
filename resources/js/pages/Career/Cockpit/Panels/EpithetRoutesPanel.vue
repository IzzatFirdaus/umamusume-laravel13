<script setup lang="ts">
/*
 * Trackblazer Epithet Routes panel.
 *
 * Registered against the `epithet_routes` flag. Draws the epithet
 * route checklist with sub-cells and completion status.
 */

import AlertRow from '../../../../components/scenario/AlertRow.vue';
import ProvenanceBadge from '../../../../components/ProvenanceBadge.vue';
import SubCell from '../../../../components/SubCell.vue';
import StatusPill from '../../../../components/StatusPill.vue';
import type { ProvenanceState } from '../../types';

type Badge = ProvenanceState;

interface EpithetRow {
    label: string;
    state: string | null;
    badge: Badge | null;
    badge_title: string | null;
    absence: string | null;
    detail_url: string | null;
}

interface EpithetGroup {
    group: string;
    rows: EpithetRow[];
    empty_note: string | null;
}

const props = defineProps<{
    scenario: {
        epithets: EpithetGroup[];
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
        case 'available': return 'info';
        case 'earned': return 'success';
        case 'locked': return 'warning';
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
            v-for="group in props.scenario.epithets"
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
                {{ group.empty_note ?? 'No epithet routes recorded for this scenario yet.' }}
            </p>

            <ul v-else class="flex flex-col gap-2">
                <li
                    v-for="row in group.rows"
                    :key="row.label"
                    class="flex flex-wrap items-baseline gap-3 text-sm text-ink"
                >
                    <SubCell
                        :label="row.label"
                        :value="row.state === null ? 'N/A' : row.state"
                        value-class="font-medium text-ink-strong"
                        :title="row.absence ?? (row.state === null ? 'No state is recorded for this epithet route.' : undefined)"
                    />
                    <StatusPill
                        v-if="row.state !== null"
                        :label="row.state"
                        :variant="statusVariant(row.state)"
                        size="sm"
                    />
                    <ProvenanceBadge
                        v-if="row.badge !== null"
                        :state="row.badge"
                        :title="row.badge_title ?? undefined"
                    />

                    <a
                        v-if="row.detail_url !== null"
                        :href="row.detail_url"
                        class="ml-auto inline-flex min-h-11 items-center text-xs font-medium text-ink-strong underline"
                    >
                        View details
                    </a>
                </li>
            </ul>
        </section>
    </div>
</template>