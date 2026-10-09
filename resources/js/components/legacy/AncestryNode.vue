<script setup lang="ts">
/*
 * One node of the six-node ancestry graph (`REFERENCE` §1.5.4: two parents, each bringing two
 * ancestors of her own).
 *
 * **The graph is a labelled list, not a picture.** `design-2.0`'s version of this screen draws a tree,
 * and a tree drawn as an SVG is unreadable to a screen reader and unreachable by keyboard. So the
 * structure here is real markup — a nested list where the nesting *is* the ancestry — and the connector
 * lines are `border` decoration on that structure. The Law of Uniform Connectedness (plan §13) is
 * satisfied by the list nesting a sighted Trainer can see, and not by an image they cannot.
 *
 * **Assignment is a button, never a drag** (WCAG 2.2 SC 2.5.7, plan §12.1). The control is a real
 * `<select>` under a real `<label>`, so it is in the tab order, arrow-keyable and screen-reader
 * labelled with no custom key handling at all, and it is `min-h-11` for the 44px floor. A drag handle
 * would have needed a full keyboard equivalent written from scratch to reach the same place.
 *
 * **Nothing here is computed.** A node prints the name the Trainer entered, the Sparks a run recorded, and
 * `N/A` carrying its reason on a disclosure (`AbsenceValue.vue`) for anything the tool does not hold. The
 * probability slot is passed in and is always null from the controller, which is the honest answer: this
 * tree holds no star-roll table.
 */
import AbsenceValue from '../AbsenceValue.vue';
import SparkChip from './SparkChip.vue';

interface SparkRow {
    kind: string;
    /**
     * The `[Global]` word for the kind, sent by the server for a stored Spark. Optional because a row the
     * Trainer is adding in this session has no server-side label yet, and `SparkChip`'s `kindLabel` is a
     * required string: passing it `undefined` threw inside its render, which took the whole node down
     * with it and made "Add Spark" look like a button that did nothing.
     */
    kind_label?: string | null;
    target: string | null;
    stars: number | null;
}

/**
 * The kind's own word, from the stored key, for a Spark the Trainer is mid-entry on. `blue` reads
 * `Blue`: the five `[Global]` display words are the capitalised storage keys, which is the pairing
 * `AncestryGraph::SPARK_KIND_LABELS` states server-side and `LegacyController::parentNode()` already
 * falls back to for an unmapped kind. A stored Spark still prints the label the server sent.
 */
const sparkKindWord = (kind: string): string => kind.charAt(0).toUpperCase() + kind.slice(1);

withDefaults(defineProps<{
    label: string;
    name: string | null;
    rank: number | null;
    isGuest: boolean;
    sparks: SparkRow[];
    probability: { value: null; title: string };
    /** The `name` attribute of the node's pick control, unique per node in the form. */
    controlName?: string;
    /**
     * The currently picked id, as a string because that is what a `<select>` compares. `LegacySelect.vue`
     * §"Why the pick control now works" states the contract this satisfies: the node takes the value as a
     * prop and emits on `change`, because a native select fires `change` and never `update:model-value`.
     */
    modelValue?: string;
    /** Options for the pick control. Empty on the compare surface, which never assigns. */
    options?: { id: number; name: string }[];
    /** Field errors for this node's control, keyed by the control's own name. */
    error?: string;
    assignable?: boolean;
}>(), {
    controlName: undefined,
    modelValue: '',
    options: () => [],
    error: undefined,
    assignable: false,
});

const emit = defineEmits<{ 'update:modelValue': [value: string] }>();
</script>

<template>
    <!-- The node. `<li>` because every node in this graph is one item of the parent's list, and the
         browser's own list semantics are then the graph's semantics. -->
    <li class="rounded-md border border-rule bg-raised p-3">
        <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ label }}</p>

        <p class="mt-1 text-sm font-semibold text-ink-strong">
            <!-- The absent name is a named absence, never a dash and never a default (AGENTS.md §5).
                 `title` says which of the two absences it is: not chosen, or not read. -->
            <span v-if="name">{{ name }}</span>
            <span v-else class="text-ink-muted" title="No Umamusume entered for this node yet.">
                N/A, not chosen
            </span>
        </p>

        <div class="mt-0.5 flex flex-wrap items-baseline gap-x-3 gap-y-0.5 text-xs text-ink-muted">
            <div class="flex items-baseline gap-1">
                <!-- The label and its value stay one text run when there is a value: `Rank 4` is the unit
                     the node reads back as. When there is none, the disclosure is a sibling of the label
                     rather than a child of it, because `<details>` is flow content. -->
                <span v-if="rank !== null">Rank {{ rank }}</span>
                <template v-else>
                    <span>Rank</span>
                    <AbsenceValue reason="You have not recorded a rank for this parent." compact />
                </template>
            </div>
            <span v-if="isGuest">Rented from a friend</span>
        </div>

        <!-- The Spark list. A node with none says so rather than rendering an empty row (D-220). -->
        <ul v-if="sparks.length > 0" class="mt-2 flex flex-wrap gap-1.5">
            <li v-for="(spark, index) in sparks" :key="`${spark.kind}-${spark.target ?? 'none'}-${index}`">
                <SparkChip
                    :kind-label="spark.kind_label || sparkKindWord(spark.kind)"
                    :target="spark.target"
                    :stars="spark.stars"
                />
            </li>
        </ul>
        <p v-else class="mt-2 text-xs text-ink-muted" title="No Sparks recorded for this node.">
            No Sparks recorded.
        </p>

        <!--
            The chance a Spark rolls. Always `N/A` from the controller, and the disclosure says why, so
            the cell is a disclosure rather than a blank. The brief asks for `~10% ★★★`; the odds it
            would come from exist in the corpus only as a wiki pair flagged stale (§1.5.3), and the
            plan's rule for a silent corpus is that the screen renders absence, never a number.
        -->
        <div class="mt-2 text-xs text-ink-muted">
            Spark chance
            <AbsenceValue :reason="probability.title" compact />
        </div>

        <!-- The assignment control. Present only where the screen assigns; the compare surface renders
             the same node with no picker, so the two cannot disagree about what a node is. -->
        <div v-if="assignable" class="mt-3">
            <label class="block text-xs font-medium text-ink" :for="controlName">
                Assign to {{ label }}
            </label>
            <select
                :id="controlName"
                :name="controlName"
                :value="modelValue"
                class="mt-1 block min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-sm text-ink"
                :class="error ? 'border-risk' : ''"
                :aria-describedby="error ? `${controlName}-error` : undefined"
                :aria-invalid="error ? 'true' : undefined"
                @change="emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
            >
                <option value="">Not chosen</option>
                <option v-for="option in options ?? []" :key="option.id" :value="option.id">
                    {{ option.name }}
                </option>
            </select>
            <!-- The error is tied to its control with `aria-describedby` (WCAG 3.3.1) rather than
                 rendered near it and left for the Trainer to connect by eye. -->
            <p v-if="error" :id="`${controlName}-error`" class="mt-1 text-xs text-risk">
                {{ error }}
            </p>
        </div>
    </li>
</template>
