<script setup lang="ts">
import { computed } from 'vue';

// A `+` is a step within the base letter's colour, not a tenth colour, so modifiers are
// stripped before the lookup (KI-8). Keyed full class strings: Tailwind v4 scans sources
// for complete names, so interpolation would compile to nothing.
const props = defineProps<{ grade: string }>();

const fills: Record<string, string> = {
    G: 'bg-grade-g',
    F: 'bg-grade-f',
    E: 'bg-grade-e',
    D: 'bg-grade-d',
    C: 'bg-grade-c',
    B: 'bg-grade-b',
    A: 'bg-grade-a',
    S: 'bg-grade-s',
    SS: 'bg-grade-ss',
};

const fill = computed(() => fills[props.grade.replace(/[-+]+$/, '')] ?? 'bg-grade-g');
</script>

<template>
    <!-- 22px (size-5.5) square with the ink-strong letter: white on these fills is banned (D-258, G-47). -->
    <span
        class="inline-grid size-5.5 place-items-center rounded-md border border-rule text-xs font-bold text-ink-strong"
        :class="fill"
    >{{ grade }}</span>
</template>
