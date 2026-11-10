<script setup lang="ts">
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
    /**
     * Render as a labelled sub-block rather than a card of its own.
     *
     * Move 3 of the scenario-detail consolidation plan (`28d9afbf`, owner Ruling 2) merges this
     * panel into the `Burst count bands` card: the two state the same run's burst position, and
     * side by side they left 236 px of dead column under this one. In bare mode the card wrapper
     * and the `CapsuleHeader` go, and the heading becomes a `.sublabel` paragraph — one card, one
     * header, and the roster's own words preserved rather than dropped. The default keeps the
     * standalone card so this component still stands alone if another surface mounts it.
     */
    bare?: boolean;
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
    <div v-if="enabled" :class="bare ? '' : 'card p-3'">
        <p v-if="bare" class="sublabel mb-2">Spirit Burst</p>
        <h2 v-else class="text-lg font-semibold text-ink-strong">Spirit Burst</h2>

        <p v-if="roster.length === 0" class="mt-2 text-sm text-ink-muted" role="status">
            No teammate burst states recorded. The six states are chargeable, charged, held,
            normal spent, extreme chargeable and extreme spent.
        </p>
        <ul v-else class="flex flex-col gap-1.5" aria-label="Teammate burst states">
            <li
                v-for="row in roster"
                :key="row.teammate"
                class="flex items-baseline justify-between gap-3 card px-3 py-2 text-sm"
            >
                <span class="font-semibold text-ink-strong">{{ row.teammate }}</span>
                <span class="shrink-0 rounded-md px-2 py-0.5 font-mono text-xs font-bold" :class="treatments[row.state].treat">
                    {{ treatments[row.state].mark }} {{ row.stateLabel }}
                </span>
            </li>
        </ul>
    </div>
</template>
