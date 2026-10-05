<script setup lang="ts">
/*
 * The Legacy Lab builder, `SCREEN-006` (PRD FR-G-1, ADR-0010, D-260 / D-268).
 *
 * **The six nodes are the payload, read back.** `REFERENCE` §1.5.4: two parents, each bringing two
 * ancestors of her own. That is `legacies[2].ancestors[2]` in the `legacy_selection` column, and this
 * page draws it and posts it again. It does not add the Trainee as a stored seventh node or a node
 * table: the column is the record (`ADR-0010` Decision), and this is how a person reads it. The Trainee
 * row is the run's own `umamusume`, which is what she would be on the client's own screen.
 *
 * **Persistence decision, stated because the prompt asked for it.** The prompt requires the wizard
 * (D2 to D7) to carry entered values forward and leaves the mechanism to the slice. The mechanism used
 * here is the existing `legacy_selection` json column on `training_runs`, with **no new column, no new
 * enum case and no migration**: the page opens on the payload already stored, and "Confirm Inheritance"
 * replaces it whole. A separate draft/planning run was rejected because `RunStatus` has three cases
 * (`Active`, `Completed`, `Retired`) and adding a fourth needs owner approval, while a *second* store
 * for the same value is exactly the "dual storage" PRD §6.12 cuts. The consequence a Trainer can see:
 * an assignment is not saved until Confirm, and the page says so on the button rather than letting her
 * assume otherwise.
 *
 * **D-260's fixity** — the Legacy Select result is fixed for the life of the run — is enforced by the
 * controller refusing a non-`Active` run, which is the first writer that now exists.
 */
import AppLayout from '../../layouts/AppLayout.vue';
import AncestryNode from '../../components/legacy/AncestryNode.vue';
import SparkChip from '../../components/legacy/SparkChip.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface SparkRow {
    kind: string;
    kind_label: string;
    target: string | null;
    stars: number | null;
}

interface ParentNode {
    slot: string;
    label: string;
    assigned: { id: number; name: string } | null;
    name: string | null;
    rank: number | null;
    is_guest: boolean;
    ancestors: { slot: string; name: string | null }[];
    sparks: SparkRow[];
    spark_counts: { kind: string; kind_label: string; count: number }[];
    probability: { value: null; title: string };
}

interface Graph {
    trainee: { name: string | null };
    parents: ParentNode[];
}

const props = defineProps<{
    run: { id: number; status_label: string; scenario_label: string };
    trainee: { id: number; name: string; name_ja: string | null };
    hasSelection: boolean;
    graph: Graph;
    roster: { id: number; name: string }[];
    knownNames: string[];
    sparkKinds: Record<string, string>;
    affinityGrades: string[];
    notice: string;
}>();

const form = useForm({
    affinity: '' as string,
    legacies: props.graph.parents.map((parent) => ({
        legacy_id: '' as string,
        rank: '' as string | number,
        is_guest: false,
        ancestors: ['', ''] as [string, string],
        sparks: [] as { kind: string; target: string; stars: string | number }[],
    })),
});

const errorFor = (slot: string): string | undefined => {
    const errors = form.errors as Record<string, string | undefined>;
    const index = props.graph.parents.findIndex((parent) => parent.slot === slot);

    return errors[`legacies.${index}.legacy_id`];
};

const ancestorError = (index: number, ancestor: number): string | undefined => {
    const errors = form.errors as Record<string, string | undefined>;

    return errors[`legacies.${index}.ancestors.${ancestor}`];
};

const assignedName = (slot: string): string | undefined => {
    const index = props.graph.parents.findIndex((parent) => parent.slot === slot);

    if (index < 0) {
        return undefined;
    }

    const picked = form.legacies[index].legacy_id;

    return picked === '' ? undefined : props.roster.find((row) => String(row.id) === picked)?.name;
};

function assignParent(index: number, id: string): void {
    form.legacies[index].legacy_id = id;

    if (id === '') {
        return;
    }

    // Picking a Veteran in the library does not copy her Sparks into the record. The Sparks a node
    // shows are the ones the Trainer read off the client and typed in below, and a library row holds
    // no Spark read-back at all. Filling them from the roster would be the tool asserting what a
    // parent carries, which is the computation this screen refuses (ADR-0020 §3).
    const veteran = props.roster.find((row) => String(row.id) === id);
    const stored = props.graph.parents[index];

    if (veteran !== undefined && stored.name === null) {
        stored.name = veteran.name;
    }
}

const assignedSparks = (index: number): SparkRow[] => form.legacies[index].sparks as unknown as SparkRow[];

function addSpark(index: number): void {
    form.legacies[index].sparks.push({ kind: 'blue', target: '', stars: 1 });
}

function removeSpark(index: number, sparkIndex: number): void {
    form.legacies[index].sparks.splice(sparkIndex, 1);
}

function submit(): void {
    // The payload posts exactly what the column stores: the parent is named by the Veteran it was
    // picked from, and the ancestors are names. `rank`, `is_guest` and the Spark list travel as the
    // Trainer entered them. Nothing is summed, weighted or derived on the way out.
    form.transform((data) => ({
        ...data,
        affinity: data.affinity === '' ? null : data.affinity,
    })).put(`/legacy/${props.run.id}`);
}

const hasAnyNode = computed(() =>
    form.legacies.some(
        (legacy) => legacy.legacy_id !== '' || legacy.ancestors.some((name) => name !== ''),
    ),
);
</script>

<template>
    <AppLayout>
        <Head title="Legacy builder" />
        <template #title>Legacy builder</template>

        <h2 class="text-2xl font-semibold text-ink-strong">
            {{ trainee.name }}
            <span v-if="trainee.name_ja" lang="ja" class="ml-2 text-lg font-normal text-ink-muted">
                {{ trainee.name_ja }}
            </span>
        </h2>
        <p class="mt-1 text-sm text-ink-muted">
            Run #{{ run.id }} · {{ run.scenario_label }} · {{ run.status_label }}
        </p>

        <p
            role="note"
            class="mt-3 flex items-start gap-2 rounded-md border border-rule bg-sunken px-3 py-2 text-sm text-ink"
        >
            <span aria-hidden="true" class="font-mono font-bold text-ink-strong">i</span>
            <span>{{ notice }}</span>
        </p>

        <!-- The disclosure about what is and is not saved. An assignment is in memory until Confirm,
             and saying so on the page is the difference between a Trainer who trusts the button and
             one who re-checks whether the work survived. -->
        <p class="mt-3 text-sm text-ink-muted">
            <template v-if="hasSelection">
                This run already has a Legacy selection recorded. Editing below and confirming replaces
                it; nothing is written until you confirm.
            </template>
            <template v-else>
                No Legacy selection is recorded on this run yet. Nothing is written until you confirm.
            </template>
        </p>

        <form class="mt-4" :aria-busy="form.processing" @submit.prevent="submit">
            <!--
                The six-node graph, as a nested list. The nesting is the ancestry: the Trainee is the
                outermost item, each parent is a child of hers, and each parent's two ancestors are
                children of that parent. A screen reader reads it as "Trainee, Parent A, Grandparent
                A1, Grandparent A2, Parent B, …", which is the structure; a tree drawn as an SVG would
                be a picture of it. The connector is a left border on the nested list, so the visual
                line and the semantic nesting are the same thing (Law of Uniform Connectedness).
            -->
            <ol class="space-y-2">
                <li class="rounded-md border-2 border-rule bg-panel p-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Trainee</p>
                    <p class="mt-1 text-sm font-semibold text-ink-strong">{{ trainee.name }}</p>
                </li>

                <li
                    v-for="(parent, index) in graph.parents"
                    :key="parent.slot"
                    class="ml-3 border-l-2 border-rule pl-3"
                >
                    <!--
                        The node and its two ancestors are one item, so the list a screen reader walks
                        is the diagram: the parent, then her own two. `AncestryNode` renders the parent
                        row; the two ancestors follow it as their own list at the same nesting depth,
                        which is why this is one `<li>` and not two siblings.
                    -->
                    <ul class="space-y-2">
                        <li>
                            <AncestryNode
                                :label="parent.label"
                                :name="assignedName(parent.slot) ?? parent.name"
                                :rank="parent.rank"
                                :is-guest="parent.is_guest"
                                :sparks="assignedSparks(index)"
                                :probability="parent.probability"
                                :control-name="`legacies.${index}.legacy_id`"
                                :options="roster"
                                :error="errorFor(parent.slot)"
                                assignable
                                @update:model-value="assignParent(index, String($event))"
                            />

                            <!-- The parent's two ancestors. Free text, because a Trainer's
                                 grandparents are frequently not in the local catalogue and an id
                                 column would be a second, emptier version of the same fact
                                 (ADR-0010 Consequences §2). The `<datalist>` is a spelling aid, never
                                 a requirement: a name that is not in the list is still storable, and
                                 that is the normal case. -->
                            <ol class="mt-2 ml-3 space-y-2 border-l-2 border-rule pl-3">
                                <li
                                    v-for="(ancestor, ancestorIndex) in parent.ancestors"
                                    :key="ancestor.slot"
                                    class="rounded-md border border-rule bg-raised p-3"
                                >
                                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">
                                        {{ ancestor.slot }}
                                    </p>
                                    <label class="mt-1 block text-xs font-medium text-ink" :for="`ancestor-${parent.slot}-${ancestorIndex}`">
                                        {{ ancestor.slot }}
                                    </label>
                                    <input
                                        :id="`ancestor-${parent.slot}-${ancestorIndex}`"
                                        v-model="form.legacies[index].ancestors[ancestorIndex]"
                                        type="text"
                                        list="known-umamusume-names"
                                        autocomplete="off"
                                        :aria-describedby="ancestorError(index, ancestorIndex) ? `ancestor-${parent.slot}-${ancestorIndex}-error` : undefined"
                                        :class="[
                                            'mt-1 block min-h-11 w-full rounded-md border bg-raised px-2 text-sm text-ink',
                                            ancestorError(index, ancestorIndex) ? 'border-risk' : 'border-rule',
                                        ]"
                                    >
                                    <p
                                        v-if="ancestorError(index, ancestorIndex)"
                                        :id="`ancestor-${parent.slot}-${ancestorIndex}-error`"
                                        class="mt-1 text-xs text-risk"
                                    >
                                        {{ ancestorError(index, ancestorIndex) }}
                                    </p>
                                    <p
                                        v-else
                                        class="mt-1 text-xs text-ink-muted"
                                        title="An ancestor is stored by name. Leave it blank if you have not recorded one."
                                    >
                                        Enter the name as you read it. Blank stays N/A.
                                    </p>
                                </li>
                            </ol>

                            <!-- The parent's recorded fields: her own rank, whether she is rented,
                                 and her Spark list. Each is entered, and each is nullable because a
                                 half-read screen is a real state (ADR-0010 Consequences §3). -->
                            <div class="mt-3 rounded-md border border-rule bg-raised p-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">
                                    {{ parent.label }} as you read her
                                </p>

                                <label class="mt-2 block text-xs font-medium text-ink" :for="`rank-${parent.slot}`">
                                    Own rank
                                </label>
                                <input
                                    :id="`rank-${parent.slot}`"
                                    v-model="form.legacies[index].rank"
                                    type="number"
                                    min="0"
                                    class="mt-1 block min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-sm text-ink"
                                >
                                <p class="mt-1 text-xs text-ink-muted">
                                    Blank stays N/A. Unbounded on purpose: the sources give no ceiling
                                    on a Legacy’s own star count (ADR-0010).
                                </p>

                                <label class="mt-3 flex min-h-11 items-center gap-2">
                                    <input
                                        v-model="form.legacies[index].is_guest"
                                        type="checkbox"
                                        class="h-5 w-5 rounded border-rule"
                                    >
                                    <span class="text-xs text-ink">Rented from a friend</span>
                                </label>

                                <fieldset class="mt-3">
                                    <legend class="text-xs font-semibold uppercase tracking-wide text-ink-muted">
                                        Sparks
                                    </legend>

                                    <p
                                        v-if="form.legacies[index].sparks.length === 0"
                                        class="mt-1 text-xs text-ink-muted"
                                        title="No Sparks entered for this parent yet."
                                    >
                                        No Sparks recorded.
                                    </p>

                                    <ul v-else class="mt-2 space-y-2">
                                        <li
                                            v-for="(spark, sparkIndex) in form.legacies[index].sparks"
                                            :key="sparkIndex"
                                            class="flex flex-wrap items-end gap-2 rounded-md border border-rule bg-panel p-2"
                                        >
                                            <label class="flex flex-col gap-1 text-xs text-ink">
                                                <span>Kind</span>
                                                <select
                                                    v-model="spark.kind"
                                                    class="min-h-11 rounded-md border border-rule bg-raised px-2 text-sm"
                                                >
                                                    <option
                                                        v-for="(label, kind) in sparkKinds"
                                                        :key="kind"
                                                        :value="kind"
                                                    >{{ label }}</option>
                                                </select>
                                            </label>

                                            <label class="flex flex-col gap-1 text-xs text-ink">
                                                <span>Applies to</span>
                                                <input
                                                    v-model="spark.target"
                                                    type="text"
                                                    class="min-h-11 rounded-md border border-rule bg-raised px-2 text-sm"
                                                >
                                            </label>

                                            <label class="flex flex-col gap-1 text-xs text-ink">
                                                <span>Stars</span>
                                                <input
                                                    v-model="spark.stars"
                                                    type="number"
                                                    min="1"
                                                    max="3"
                                                    class="min-h-11 w-20 rounded-md border border-rule bg-raised px-2 text-sm"
                                                >
                                            </label>

                                            <button
                                                type="button"
                                                class="enamel min-h-11 rounded-full bg-chrome px-3 text-sm font-semibold text-on-chrome"
                                                @click="removeSpark(index, sparkIndex)"
                                            >
                                                Remove Spark
                                                <span class="sr-only"> from {{ parent.label }}</span>
                                            </button>
                                        </li>
                                    </ul>

                                    <button
                                        type="button"
                                        class="mt-2 min-h-11 rounded-md border border-rule px-3 text-sm font-medium text-ink"
                                        @click="addSpark(index)"
                                    >
                                        Add Spark
                                        <span class="sr-only"> to {{ parent.label }}</span>
                                    </button>
                                </fieldset>
                            </div>
                        </li>
                    </ul>
                </li>
            </ol>

            <datalist id="known-umamusume-names">
                <option v-for="name in knownNames" :key="name" :value="name" />
            </datalist>

            <!--
                Affinity. The payload stores one grade for the pair (§1.5.4) and the Trainer read it off
                the client, so it is an entered value here. It is not computed, and the note says so,
                because the natural next question on this screen is what the grade is worth in stat
                points and the answer is that this tool does not price it (ADR-0010, D-270).
            -->
            <div class="mt-4 max-w-sm">
                <label class="block text-xs font-medium text-ink" for="affinity">Affinity for the pair</label>
                <select
                    id="affinity"
                    v-model="form.affinity"
                    name="affinity"
                    class="mt-1 block min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-sm text-ink"
                >
                    <option value="">Not recorded</option>
                    <option v-for="grade in affinityGrades" :key="grade" :value="grade">{{ grade }}</option>
                </select>
                <p class="mt-1 text-xs text-ink-muted" title="REFERENCE §1.5.4 grades each link; this records the pair's own grade as read.">
                    The grade you read for the pair, not a score. This tool does not price it.
                </p>
                <p v-if="form.errors.affinity" class="mt-1 text-xs text-risk">{{ form.errors.affinity }}</p>
            </div>

            <p
                v-if="form.errors.legacies"
                role="alert"
                class="mt-3 rounded-md border border-risk bg-raised px-3 py-2 text-sm text-risk"
            >
                {{ form.errors.legacies }}
            </p>

            <div class="mt-5 flex flex-wrap items-center gap-3">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="enamel inline-flex min-h-11 items-center rounded-full bg-chrome px-4 font-semibold text-on-chrome disabled:opacity-60"
                >
                    {{ form.processing ? 'Saving…' : 'Confirm Inheritance' }}
                </button>

                <a
                    :href="`/legacy/compare?runs[]=${run.id}`"
                    class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised"
                >
                    Compare this run
                </a>

                <p v-if="hasAnyNode" class="text-sm text-ink-muted">
                    Nothing is saved until you confirm.
                </p>
            </div>
        </form>
    </AppLayout>
</template>
