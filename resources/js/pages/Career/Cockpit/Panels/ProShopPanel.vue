<script setup lang="ts">
/*
 * Trackblazer Pro Shop panel.
 *
 * Registered against the `shop` flag. Draws the shop items with
 * provenance badges, sub-cells, and purchase affordances.
 */

import AlertRow from '../../../../components/scenario/AlertRow.vue';
import ProvenanceBadge from '../../../../components/ProvenanceBadge.vue';
import SubCell from '../../../../components/SubCell.vue';
import type { ProvenanceState } from '../../types';

type Badge = ProvenanceState;

interface ShopItem {
    label: string;
    cost: string | number | null;
    currency: string;
    owned: boolean;
    badge: Badge | null;
    badge_title: string | null;
    absence: string | null;
    purchase_url: string | null;
}

interface ShopGroup {
    group: string;
    items: ShopItem[];
    empty_note: string | null;
}

const props = defineProps<{
    scenario: {
        shop: ShopGroup[];
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
            v-for="group in props.scenario.shop"
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

            <p v-if="group.items.length === 0" class="text-sm text-ink-muted">
                {{ group.empty_note ?? 'No shop items available for this scenario yet.' }}
            </p>

            <ul v-else class="flex flex-col gap-2">
                <li
                    v-for="item in group.items"
                    :key="item.label"
                    class="flex flex-col gap-2 rounded-md border border-rule bg-panel p-3"
                >
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <span class="font-medium text-ink-strong">{{ item.label }}</span>
                        <ProvenanceBadge
                            v-if="item.badge !== null"
                            :state="item.badge"
                            :title="item.badge_title ?? undefined"
                        />
                    </div>

                    <SubCell
                        label="Cost"
                        :value="item.cost === null ? 'N/A' : `${item.cost} ${item.currency}`"
                        value-class="font-mono text-base tabular-nums text-ink-strong"
                        :title="item.absence ?? (item.cost === null ? 'No cost is recorded for this item.' : undefined)"
                    />

                    <a
                        v-if="item.purchase_url !== null && !item.owned"
                        :href="item.purchase_url"
                        class="mt-2 inline-flex min-h-11 items-center text-xs font-medium text-ink-strong underline"
                    >
                        Purchase
                    </a>
                    <span v-else-if="item.owned" class="mt-2 text-xs text-ink-muted">
                        Owned
                    </span>
                </li>
            </ul>
        </section>
    </div>
</template>