<script setup lang="ts">
import { computed } from 'vue';

// The hue sweep makes green read as "good" and risk as "low", reversing two settled rules
// (DESIGN.md §2.1 reserves green for action; --color-risk is for failure). The brief's shape
// ships anyway; the semantic conflict is recorded here, not repaired.
const props = defineProps<{ energy: number | null }>();

const sweep = [
    'bg-sp', 'bg-sp', 'bg-green', 'bg-green', 'bg-lattice',
    'bg-lattice', 'bg-pick', 'bg-pick', 'bg-risk', 'bg-risk',
];

const recorded = computed(() => props.energy !== null);
const value = computed(() => Math.trunc(props.energy as number));
const filled = computed(() => (recorded.value ? Math.max(0, Math.min(10, Math.ceil(value.value / 10))) : 0));

// 50 is the only sourced Energy threshold (D-204).
const state = computed(() => {
    if (!recorded.value) return null;
    if (value.value > 50) return 'Safe';
    if (value.value >= 30) return 'Caution';
    return 'Danger';
});

const stateClass = computed(() => {
    if (state.value === 'Danger') return 'bg-risk text-on-chrome';
    if (state.value === 'Caution') return 'bg-pick text-on-pick';
    return 'bg-green-tint text-ink';
});
</script>

<template>
    <div class="flex flex-col gap-1">
        <!-- Absent, not zero: an empty gauge would say the trainee is exhausted on a run
             that has not logged a turn (D-220). -->
        <span v-if="!recorded" class="font-mono text-sm text-ink-muted" title="No Energy value recorded for this run">
            Energy N/A
        </span>
        <template v-else>
            <div class="relative flex h-2 gap-0.5" role="img" :aria-label="`Energy ${value} of 100`">
                <span
                    v-for="(fill, i) in sweep"
                    :key="i"
                    class="flex-1 rounded-sm"
                    :class="i < filled ? fill : 'bg-idle'"
                ></span>
                <span class="absolute -inset-y-1 border-l-2 border-risk" style="left: 50%" aria-hidden="true"></span>
            </div>
            <span class="flex items-center gap-2 text-xs">
                <span class="font-mono tabular-nums text-ink-muted">{{ value }}/100</span>
                <span class="rounded border border-transparent px-2 py-0.5 font-bold" :class="stateClass">{{ state }}</span>
            </span>
        </template>
    </div>
</template>
