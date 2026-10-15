<script setup lang="ts">
// Decision-surface hint (plan 6A.9, client 05:32): names the suggested id and the source of the suggestion.
defineProps<{
    suggestion: string;
    umamusumeId: number | null;
    sourceKey: string;
}>();
</script>

<template>
    <p class="text-xs text-ink-muted">
        Suggested id: {{ suggestion }}<span v-if="umamusumeId !== null" class="font-mono tabular-nums"> id {{ umamusumeId }}</span>, matched from {{ sourceKey }}.
    </p>
</template>