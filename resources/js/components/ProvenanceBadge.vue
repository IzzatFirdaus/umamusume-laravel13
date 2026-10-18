<script lang="ts">
/**
 * Compatibility re-export (plan 1.7).
 *
 * `ProvenanceBadge.vue` was the only owner of the four provenance glyphs. The mark now renders the
 * three exception states and nothing for `confirmed`, and lives in `ProvenanceMark.vue`. Sixteen files
 * still import this path, so the file stays as a thin alias rather than a second implementation: one
 * component, two names, no drift.
 *
 * The union still re-exports from here because callers alias `ProvenanceState` off this module.
 */
import type { ProvenanceState } from '../types';

export type { ProvenanceState };
</script>

<script setup lang="ts">
import ProvenanceMark from './ProvenanceMark.vue';

defineProps<{
    state: 'confirmed' | 'calculated' | 'estimated' | 'unknown';
    /** Optional reason. Shown on focus and hover, and always in the accessible name. */
    title?: string;
}>();
</script>

<template>
    <ProvenanceMark :state="state" :title="title" />
</template>
