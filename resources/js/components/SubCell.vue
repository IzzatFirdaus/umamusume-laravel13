<script setup lang="ts">
interface Props {
    label: string;
    value: string | number;
    valueClass?: string;
    title?: string;
}

const props = defineProps<Props>();
</script>

<template>
    <!-- The root is the `<dl>` the `<dt>`/`<dd>` pair requires (WCAG 1.3.1, axe `dlitem`): the same
         self-contained fix `KpiCard.vue` got, so every host stays valid without remembering a parent.
         Both call sites (the Dashboard career strip, the Runs list row) already sit inside plain divs. -->
    <dl data-stat-tile class="rounded-md border border-rule bg-raised p-3 min-w-0">
        <dt class="text-xs font-medium text-ink-muted">{{ props.label }}</dt>
        <dd
            data-stat-value
            :class="[props.valueClass ?? 'font-mono text-lg tabular-nums text-ink-strong', { 'text-ink-muted': props.title }]"
            :title="props.title"
        >{{ props.value }}</dd>
    </dl>
</template>