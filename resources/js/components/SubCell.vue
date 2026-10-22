<script setup lang="ts">
/*
 * A labelled figure in a dense tile: `<dt>` label, `<dd>` value, and the reason the value is absent.
 *
 * **The reason is not a `title`.** `DESIGN.md` §5 (a)'s 2026-10-17 amendment makes the hover-only
 * channel superseded: `title` is reachable by a pointer and by nothing else. Hosts pass the reason
 * through `title` still — that prop name is kept because eleven call sites read it — and the component
 * renders it through `AbsenceValue`, whose summary carries it as the accessible name and whose
 * disclosure opens with no script.
 *
 * **`title` is only a reason when the value is absent.** When a host passes it beside a real value it
 * is qualifying a number, which is a provenance question (`ProvenanceMark`) and not this component's;
 * the value renders plainly so a present figure never wears an absence marker.
 */
import AbsenceValue from './AbsenceValue.vue';

interface Props {
    label: string;
    value: string | number;
    valueClass?: string;
    /** Why the value is absent. Rendered as a disclosure, not a `title`. */
    title?: string;
}

const props = defineProps<Props>();

/** The marker the product uses for "this run has not recorded it". */
const ABSENT = 'N/A';

const isAbsent = (value: string | number): boolean => String(value) === ABSENT;
</script>

<template>
    <!-- The root is the `<dl>` the `<dt>`/`<dd>` pair requires (WCAG 1.3.1, axe `dlitem`): the same
         self-contained fix `KpiCard.vue` got, so every host stays valid without remembering a parent.
         Both call sites (the Dashboard career strip, the Runs list row) already sit inside plain divs. -->
    <dl data-stat-tile class="card p-3 min-w-0">
        <dt class="text-xs font-medium text-ink-muted">{{ props.label }}</dt>
        <dd
            data-stat-value
            :class="[props.valueClass ?? 'font-mono text-lg tabular-nums text-ink-strong', { 'text-ink-muted': isAbsent(props.value) && props.title }]"
        >
            <AbsenceValue
                v-if="isAbsent(props.value) && props.title"
                compact
                :reason="props.title"
            />
            <template v-else>{{ props.value }}</template>
        </dd>
    </dl>
</template>
