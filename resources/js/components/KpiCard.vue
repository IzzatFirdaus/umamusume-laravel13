<script setup lang="ts">
interface Props {
    label: string;
    value: string | number;
    subLabel?: string;
    valueClass?: string;
}

const props = defineProps<Props>();
</script>

<template>
    <!-- The root is the `<dl>` the `<dt>`/`<dd>` pair requires (WCAG 1.3.1, axe `dlitem`):
         a description list item outside a description list is invalid HTML, and the
         accessibility gate scans for it. The card styles ride on the `<dl>` itself, so the
         component stays self-contained and no caller has to remember to supply the parent. -->
    <dl data-stat-tile class="card min-w-0 text-center">
        <dt class="truncate text-xs font-medium text-ink-muted" :title="props.label">{{ props.label }}</dt>
        <dd data-stat-value :class="[props.valueClass ?? 'font-mono text-kpi tabular-nums text-ink-strong', 'mt-1 truncate']">
            {{ props.value }}
        </dd>
        <dd v-if="props.subLabel" class="mt-1 truncate text-xs text-ink-muted">{{ props.subLabel }}</dd>
    </dl>
</template>