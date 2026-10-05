<script setup lang="ts">
import CapsuleHeader from './CapsuleHeader.vue';

// Unity Cup's widget list carries spirit_bursts; the controller resolves that membership to
// `enabled` and the component never sees a scenario slug (D-221, gate G-34, G-33).
// `state` is the SpiritBurstState backing value (a tool identifier, never printed, D-20);
// `stateLabel` is the enum's label() already formatted server-side.
type BurstState =
    | 'Chargeable'
    | 'Charged'
    | 'Held'
    | 'NormalBurstSpent'
    | 'ExtremeChargeable'
    | 'ExtremeSpent';

defineProps<{
    enabled: boolean;
    roster: { teammate: string; state: BurstState; stateLabel: string }[];
}>();

// Six states, each with its own word and its own mark, and the mark is never the only
// signal (D-12). The treatments are existing token pairs; nothing here opens a new
// colour role.
const treatments: Record<BurstState, { mark: string; treat: string }> = {
    Chargeable: { mark: '·', treat: 'bg-sunken text-ink-muted' },
    Charged: { mark: '●', treat: 'bg-raised text-ink ring-2 ring-ring' },
    Held: { mark: '▲', treat: 'bg-pick text-on-pick' },
    NormalBurstSpent: { mark: '○', treat: 'bg-sunken text-ink' },
    ExtremeChargeable: { mark: '◆', treat: 'bg-green-tint text-ink border border-green-line' },
    ExtremeSpent: { mark: '◇', treat: 'bg-risk text-on-chrome' },
};
</script>

<template>
    <div v-if="enabled" class="rounded-md border border-rule bg-panel p-3">
        <CapsuleHeader title="Spirit Burst" class="mb-3" />

        <p v-if="roster.length === 0" class="text-sm text-ink-muted" role="status">
            No teammate burst states recorded. The six states are chargeable, charged, held,
            normal spent, extreme chargeable and extreme spent.
        </p>
        <ul v-else class="flex flex-col gap-1.5" aria-label="Teammate burst states">
            <li
                v-for="row in roster"
                :key="row.teammate"
                class="flex items-baseline justify-between gap-3 rounded-md border border-rule bg-raised px-3 py-2 text-sm"
            >
                <span class="font-semibold text-ink-strong">{{ row.teammate }}</span>
                <span class="shrink-0 rounded px-2 py-0.5 font-mono text-xs font-bold" :class="treatments[row.state].treat">
                    {{ treatments[row.state].mark }} {{ row.stateLabel }}
                </span>
            </li>
        </ul>
    </div>
</template>
