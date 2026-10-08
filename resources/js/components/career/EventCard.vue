<script setup lang="ts">
/*
 * A recorded choice event (SCREEN-012, plan §8 D11). Renders the source, turn, event name,
 * the choice taken, and its recorded outcome or the incomplete-outcome warning.
 *
 * Props mirror the flattened TurnEvent row the controller returns.
 */
interface Props {
    id: number;
    turn: number;
    event_type: string;
    source_label: string;
    source_name: string;
    choice_index: number | null;
    choice_label: string | null;
    support_card_name: string | null;
    bond_delta: number | null;
    origin_note: string | null;
    outcome_recorded: boolean;
}

const props = defineProps<Props>();
</script>

<template>
    <article class="rounded-md border border-rule bg-raised p-3">
        <header class="flex flex-wrap items-start justify-between gap-2">
            <div class="flex flex-wrap items-baseline gap-2">
                <span class="inline-flex min-h-11 items-center rounded-md bg-pick px-2 text-xs font-semibold text-on-pick">
                    {{ props.source_label }}
                </span>
                <span class="font-mono tabular-nums text-sm text-ink-strong">Turn {{ props.turn }}</span>
                <span class="text-xs text-ink-muted">#{{ props.id }}</span>
            </div>
            <span v-if="!props.outcome_recorded"
                  class="inline-flex min-h-6 items-center rounded-full bg-warning px-2 py-0.5 text-xs font-semibold text-on-warning"
                  title="No recorded outcome was entered for this choice. The trainer must choose manually.">
                ⚠ Event outcome incomplete - choose manually
            </span>
        </header>

        <div class="mt-2 space-y-1 text-sm">
            <p class="text-ink-strong">{{ props.source_name }}</p>

            <p v-if="props.choice_label" class="text-ink">
                <span class="font-medium">Choice:</span> {{ props.choice_label }}
            </p>
            <p v-else class="text-ink-muted"><span class="font-medium">Choice:</span> <i>Unnamed</i></p>

            <p v-if="props.support_card_name" class="text-ink-muted">
                <span class="font-medium">Support Card:</span> {{ props.support_card_name }}
            </p>

            <p v-if="props.bond_delta !== null" class="text-ink-muted">
                <span class="font-medium">Bond delta:</span> {{ props.bond_delta >= 0 ? '+' : '' }}{{ props.bond_delta }}
            </p>

            <p v-if="props.origin_note" class="text-ink">
                <span class="font-medium">Recorded outcome:</span> {{ props.origin_note }}
            </p>
            <p v-else-if="!props.outcome_recorded" class="text-warning">
                <span class="font-medium">No outcome recorded.</span>
            </p>

            <p v-if="props.outcome_recorded && props.origin_note === null"
               class="text-xs font-mono text-ink-muted">
                Deltas: {{ JSON.stringify(props.deltas ?? {}) }}
            </p>
        </div>
    </article>
</template>