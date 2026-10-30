<script setup lang="ts">
/*
 * One grandparent node's editable record: name, rank letter, star rank, and its own Sparks.
 *
 * **The field-id contract is load-bearing and cannot be renamed.** Three specs locate these nodes by
 * id and label - `legacy.spec.ts`, `legacy-index.spec.ts` and `career-inheritance-event.spec.ts` - so
 * the shape is fixed at `ancestor-${slot}-${index}`, `ancestor-rank-letter-${slot}-${index}`,
 * `ancestor-rank-${slot}-${index}`, `ancestor-spark-kind-${slot}-${index}-${sparkIndex}` (and
 * `-category-`, `-target-`, `-stars-`). Renaming an id here is a spec break, not a refactor.
 *
 * The draft row arrives as a prop and is mutated in place, which is what the page it was extracted
 * from already does: `legacy` is a reactive row of the enclosing `useForm`, so `v-model` writes
 * through to the form without a prop/emit hop per keystroke. Passing the row rather than four
 * parallel arrays is what keeps the index arithmetic in one place.
 *
 * A blank node is legitimate: an empty name posts as null and the payload omits the companion keys
 * when every row is empty (`ancestorsSparks()` / `ancestorsRankLetters()` / `ancestorsRanks()` all
 * return null on an all-empty list), so a names-only ancestry keeps the exact shape it had.
 */
import BaseInput from '../Form/BaseInput.vue';
import BaseSelect from '../Form/BaseSelect.vue';

/** One draft Spark on a grandparent node: the same shape the enclosing form posts. */
interface AncestorSparkDraft {
    kind: string;
    category: string;
    target: string | null;
    stars: number | string | null;
}

/** The slice of a legacy draft row this node edits. The page's own type satisfies it. */
interface AncestorLegacyDraft {
    ancestors: (string | null)[];
    ancestors_rank_letters: (string | null)[];
    ancestors_rank: (number | string | null)[];
    ancestors_sparks: AncestorSparkDraft[][];
}

const props = defineProps<{
    /** The enclosing draft row, mutated in place so `v-model` writes through to the form. */
    legacy: AncestorLegacyDraft;
    /** Which of the parent's two grandparents this is: 0 or 1. */
    ancestorIndex: number;
    /** The parent's slot letter, part of every field id (`a` / `b`). */
    slot: string;
    /** The visible node name: "Grandparent A" / "Grandparent B". */
    label: string;
    /** `config/uma.php`'s spark kind vocabulary, keyed by the stored value. */
    sparkKinds: Record<string, string>;
    /** The targets each category offers, from the same config the server validates against. */
    sparkTargets: { Stat: string[]; Aptitude: string[]; Skill: string[] };
    /** The server's message for this node's name, when it refused one. */
    error?: string;
    /** The sentence the server supplies for a node that records no Sparks yet. */
    emptySparksLabel: string;
}>();

/*
 * The three target categories, in the order the wizard places them. Declared here rather than passed
 * because it is the one list both surfaces must agree on and neither may vary: the server refuses a
 * Spark whose target does not belong to its category
 * (`StoreLegacySelectionRequest::assertTargetMatchesCategory`).
 */
const SPARK_CATEGORIES = ['Stat', 'Aptitude', 'Skill'];

const id = (field: string): string => `${field}-${props.slot}-${props.ancestorIndex}`;
const sparkId = (field: string, sparkIndex: number): string =>
    `${field}-${props.slot}-${props.ancestorIndex}-${sparkIndex}`;

/**
 * A draft row the Trainer added and left blank. It is not a Spark until it carries a target or a star
 * count, which is the rule the server applies when it normalises the payload.
 */
/**
 * The node's own Spark list, materialised on first read: the enclosing form seeds
 * `ancestors_sparks` as an empty array when the draft held no Sparks, so index 0/1 do not exist
 * until the Trainer adds one. Every access goes through here; a straight index read of `.length`
 * was a render-time TypeError on a fresh draft.
 */
function nodeSparks(): AncestorSparkDraft[] {
    if (props.legacy.ancestors_sparks[props.ancestorIndex] === undefined) {
        props.legacy.ancestors_sparks[props.ancestorIndex] = [];
    }

    return props.legacy.ancestors_sparks[props.ancestorIndex];
}

function addSpark(): void {
    nodeSparks().push({
        kind: 'blue',
        category: 'Stat',
        target: '',
        stars: 1,
    });
}

function removeSpark(sparkIndex: number): void {
    nodeSparks().splice(sparkIndex, 1);
}
</script>

<template>
    <div>
        <label class="mt-1 block text-xs font-medium text-ink" :for="id('ancestor')">
            {{ props.label }}
        </label>
        <BaseInput
            :id="id('ancestor')"
            v-model="props.legacy.ancestors[props.ancestorIndex]"
            type="text"
            list="known-umamusume-names"
            autocomplete="off"
            :aria-describedby="props.error ? `${id('ancestor')}-error` : undefined"
            :error="props.error"
        />
        <p v-if="props.error" :id="`${id('ancestor')}-error`" class="mt-1 text-xs text-risk">
            {{ props.error }}
        </p>

        <!--
          The node's two rank fields. Ranks are unbounded and a blank stays not recorded (ADR-0010
          Consequences §3): four of the six ranks in the tree sit on these nodes, and the client
          prints a letter beside the star count, so both are recorded.
        -->
        <label class="mt-2 block text-xs font-medium text-ink" :for="id('ancestor-rank-letter')">
            Grandparent rank (letters)
        </label>
        <BaseInput
            :id="id('ancestor-rank-letter')"
            v-model="props.legacy.ancestors_rank_letters[props.ancestorIndex]"
            type="text"
            list="known-rank-letters"
            autocomplete="off"
        />

        <label class="mt-2 block text-xs font-medium text-ink" :for="id('ancestor-rank')">
            Grandparent rank (stars)
        </label>
        <BaseInput
            :id="id('ancestor-rank')"
            v-model="props.legacy.ancestors_rank[props.ancestorIndex]"
            type="number"
            min="0"
        />

        <!-- The node's own Sparks. The summary carries the node's name because four identical
             "Grandparent Sparks" summaries would be told apart by proximity alone. -->
        <details class="mt-2 rounded-md border border-rule bg-panel p-2">
            <summary class="flex min-h-11 cursor-pointer items-center text-xs font-medium text-ink">
                Sparks for {{ props.label }}
            </summary>

            <ul v-if="nodeSparks().length > 0" class="mt-2 space-y-2">
                <li
                    v-for="(spark, sparkIndex) in nodeSparks()"
                    :key="sparkIndex"
                    class="flex flex-wrap items-end gap-2 rounded-md border border-rule bg-panel p-2"
                >
                    <BaseSelect
                        :id="sparkId('ancestor-spark-kind', sparkIndex)"
                        v-model="spark.kind"
                        label="Kind"
                        :options="Object.keys(props.sparkKinds).map((kind) => ({ value: kind, label: props.sparkKinds[kind] }))"
                    />
                    <BaseSelect
                        :id="sparkId('ancestor-spark-category', sparkIndex)"
                        v-model="spark.category"
                        label="Category"
                        :options="SPARK_CATEGORIES.map((category) => ({ value: category, label: category }))"
                        @change="spark.target = ''"
                    />
                    <BaseSelect
                        :id="sparkId('ancestor-spark-target', sparkIndex)"
                        v-model="spark.target"
                        label="Target"
                        :options="[{ value: '', label: 'Not recorded' }, ...(props.sparkTargets[spark.category as 'Stat' | 'Aptitude' | 'Skill'] || []).map((target) => ({ value: target, label: target }))]"
                    />
                    <BaseInput
                        :id="sparkId('ancestor-spark-stars', sparkIndex)"
                        v-model="spark.stars"
                        type="number"
                        min="1"
                        max="3"
                        label="Stars"
                    />
                    <button
                        type="button"
                        class="inline-flex min-h-11 items-center rounded-md border border-rule bg-raised px-3 text-xs font-semibold text-ink hover:bg-panel"
                        @click="removeSpark(sparkIndex)"
                    >
                        Remove Spark
                        <span class="sr-only"> from {{ props.label }}</span>
                    </button>
                </li>
            </ul>
            <p v-else class="mt-1 text-xs text-ink-muted">
                {{ props.emptySparksLabel }}
            </p>

            <button
                type="button"
                class="mt-2 inline-flex min-h-11 items-center rounded-md border border-rule bg-raised px-3 text-xs font-semibold text-ink hover:bg-panel"
                @click="addSpark"
            >
                Add Spark
                <span class="sr-only"> to {{ props.label }}</span>
            </button>
        </details>
    </div>
</template>
