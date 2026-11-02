<script setup lang="ts">
interface Props {
    label: string;
    variant?: 'info' | 'success' | 'warning' | 'neutral' | 'positive' | 'caution';
    size?: 'sm' | 'md';
    /**
     * The stored state value (e.g. `active`), rendered as `data-status` so the pill's meaning is
     * machine-readable and never carried by colour alone (AUD-20261102-006 F-01).
     */
    status?: string;
}

const props = defineProps<Props>();

const variantClasses = {
    info: 'bg-green-tint text-green-deep border-green-line',
    success: 'bg-green-tint text-green-deep border-green-line',
    warning: 'bg-raised text-ink-strong border-rule',
    neutral: 'bg-sunken text-ink-muted border-rule',
    positive: 'bg-status-positive text-status-positive-ink border-status-positive',
    caution: 'bg-status-caution text-status-caution-ink border-status-caution',
};

const sizeClasses = {
    sm: 'px-2 py-0.5 text-xs',
    md: 'px-3 py-1 text-sm',
};
</script>

<template>
    <span
        class="pill"
        :class="[variantClasses[props.variant ?? 'neutral'], sizeClasses[props.size ?? 'md']]"
        :data-status="props.status"
    >
        {{ props.label }}
    </span>
</template>