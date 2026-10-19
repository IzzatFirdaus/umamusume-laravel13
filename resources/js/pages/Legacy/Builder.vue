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
import BaseInput from '@/components/Form/BaseInput.vue';
import BaseSelect from '@/components/Form/BaseSelect.vue';
import BaseCheckbox from '@/components/Form/BaseCheckbox.vue';
import BaseButton from '@/components/Form/BaseButton.vue';

interface SparkRow {
    kind: string;
    kind_label: string;
    target: string | null;
    stars: number | null;
}

interface SparkDraft {
    kind: string;
    category: string;
    target: string;
    stars: string | number;
}

interface ParentNode {
    slot: string;
    label: string;
    assigned: { id: number; name: string } | null;
    name: string | null;
    rank: number | null;
    rank_letter: string | null;
    rank_label: string;
    is_guest: boolean;
    ancestors: {
        slot: string;
        name: string | null;
        rank: number | null;
        rank_letter: string | null;
        rank_label: string;
        sparks: SparkRow[];
        sparks_label: string;
    }[];
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
    affinity: string | null;
    roster: { id: number; name: string }[];
    knownNames: string[];
    sparkKinds: Record<string, string>;
    affinityGrades: string[];
    rankLetters: string[];
    sparkTargets: { Stat: string[]; Aptitude: string[]; Skill: string[] };
    notice: string;
}>();

/**
 * The library row a stored parent was picked from, by her name.
 *
 * The payload keeps a name and not a library id (`ADR-0010`: `legacy_id` is a lookup key, not a stored
 * fact), so the pick control is re-seeded through the roster. `LegacyController::roster()` is ordered
 * `latest('id')`, so the first match is the newest Veteran for that trainee, and any Veteran of that
 * trainee resolves back to the same `umamusume_id` on the write. A parent whose library row was
 * deleted therefore stays unpreselected, which is the honest answer rather than a guessed id.
 */
const rosterIdFor = (name: string | null): string => {
    if (name === null) {
        return '';
    }

    return String(props.roster.find((row) => row.name === name)?.id ?? '');
};

/**
 * The three target categories, and the one inference the read-back needs: the payload stores only the
 * target string, so a stored Spark's category is recovered from which list holds it. The same words the
 * wizard step uses, because both screens post against the one request.
 */
const SPARK_CATEGORIES = ['Stat', 'Aptitude', 'Skill'];

function inferCategory(target: string): string {
    if (target === '') {
        return 'Stat';
    }

    if (props.sparkTargets.Stat.includes(target)) {
        return 'Stat';
    }

    if (props.sparkTargets.Aptitude.includes(target)) {
        return 'Aptitude';
    }

    return 'Skill';
}

/**
 * A draft row the Trainer added and left blank. The editor appends one on Add Spark, and it is not a
 * Spark until it carries a target or a star count: the server discards the same shape
 * (`StoreLegacySelectionRequest::payload()`), so the posted payload stays honest while it is a draft.
 */
function isBlankSpark(spark: SparkDraft): boolean {
    return spark.target === '' && spark.stars === '';
}

const form = useForm({
    affinity: props.affinity ?? '',
    legacies: props.graph.parents.map((parent) => ({
        legacy_id: rosterIdFor(parent.name),
        rank: parent.rank === null ? '' : String(parent.rank),
        rank_letter: parent.rank_letter ?? '',
        is_guest: parent.is_guest,
        ancestors: parent.ancestors.map((ancestor) => ancestor.name ?? '') as [string, string],
        ancestors_rank_letters: parent.ancestors.map((ancestor) => ancestor.rank_letter ?? ''),
        ancestors_rank: parent.ancestors.map((ancestor) =>
            ancestor.rank === null || ancestor.rank === undefined ? '' : String(ancestor.rank),
        ),
        ancestors_sparks: parent.ancestors.map((ancestor) => ancestor.sparks.map((spark) => ({
            kind: spark.kind,
            category: inferCategory(spark.target ?? ''),
            target: spark.target ?? '',
            stars: spark.stars === null ? '' : String(spark.stars),
        }))),
        sparks: parent.sparks.map((spark) => ({
            kind: spark.kind,
            category: inferCategory(spark.target ?? ''),
            target: spark.target ?? '',
            stars: spark.stars === null ? '' : String(spark.stars),
        })),
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

const fieldError = (index: number, field: string): string | undefined => {
    const errors = form.errors as Record<string, string | undefined>;

    return errors[`legacies.${index}.${field}`];
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
    form.legacies[index].sparks.push({ kind: 'blue', category: 'Stat', target: '', stars: 1 });
}

function removeSpark(index: number, sparkIndex: number): void {
    form.legacies[index].sparks.splice(sparkIndex, 1);
}

function addAncestorSpark(index: number, ancestorIndex: number): void {
    form.legacies[index].ancestors_sparks[ancestorIndex].push({ kind: 'blue', category: 'Stat', target: '', stars: 1 });
}

function removeAncestorSpark(index: number, ancestorIndex: number, sparkIndex: number): void {
    form.legacies[index].ancestors_sparks[ancestorIndex].splice(sparkIndex, 1);
}

const confirmDialog = ref<HTMLDialogElement | null>(null);

/**
 * The confirmation is destructive-only. A run that already stores a selection is about to lose it, so
 * the write waits behind a dialog that says so in the run's own words. A run with nothing stored has
 * nothing to destroy and the press is the write (`Slice 7.11`). The dialog is a native `<dialog>`
 * opened with `showModal()`, which is where the focus trap and the Escape close come from rather than
 * from focus code written here.
 */
function requestConfirm(): void {
    if (!props.hasSelection) {
        submit();

        return;
    }

    confirmDialog.value?.showModal();
}

function submit(): void {
    // The payload posts exactly what the column stores: the parent is named by the Veteran it was
    // picked from, the ancestors are names, and every rank and Spark figure travels as the Trainer
    // entered it. A blank field posts as the absence rather than as a zero or an empty string, and a
    // draft Spark row with neither a target nor a star count is discarded the way the server discards
    // it, so confirming never widens the record and never narrows it either.
    form.transform((data) => ({
        ...data,
        affinity: data.affinity === '' ? null : data.affinity,
        legacies: data.legacies.map((legacy) => ({
            ...legacy,
            legacy_id: legacy.legacy_id === '' ? null : Number(legacy.legacy_id),
            rank: legacy.rank === '' ? null : Number(legacy.rank),
            rank_letter: legacy.rank_letter === '' ? null : legacy.rank_letter,
            ancestors_rank_letters: legacy.ancestors_rank_letters.map((letter: string) => (letter === '' ? null : letter)),
            ancestors_rank: legacy.ancestors_rank.map((rank: string) => (rank === '' ? null : Number(rank))),
            ancestors_sparks: legacy.ancestors_sparks.map((list) => list
                .filter((spark) => !isBlankSpark(spark))
                .map((spark) => ({
                    kind: spark.kind,
                    category: inferCategory(spark.target ?? ''),
                    target: spark.target === '' ? null : spark.target,
                    stars: spark.stars === '' ? null : Number(spark.stars),
                }))),
            sparks: legacy.sparks.filter((spark) => !isBlankSpark(spark)).map((spark) => ({
                kind: spark.kind,
                category: inferCategory(spark.target ?? ''),
                target: spark.target === '' ? null : spark.target,
                stars: spark.stars === '' ? null : Number(spark.stars),
            })),
        })),
    })).put(`/legacy/${props.run.id}`);

    confirmDialog.value?.close();
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

        <h2 class="text-lg font-semibold text-ink-strong">
            {{ trainee.name }}
            <span v-if="trainee.name_ja" lang="ja" class="ml-2 text-sm font-normal text-ink-muted">
                {{ trainee.name_ja }}
            </span>
        </h2>
        <p class="mt-1 text-sm text-ink-muted">
            Run #{{ run.id }} · {{ run.scenario_label }} · {{ run.status_label }}
        </p>

        <details class="mt-3 rounded-md border border-rule bg-sunken px-3 py-2 text-sm text-ink">
            <summary class="flex cursor-pointer items-start gap-2">
                <span aria-hidden="true" class="font-mono font-bold text-ink-strong">i</span>
                <span>What this screen records</span>
            </summary>
            <p class="mt-2">{{ notice }}</p>
        </details>

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
                                :rank-label="parent.rank_label"
                                :is-guest="parent.is_guest"
                                :sparks="assignedSparks(index)"
                                :probability="parent.probability"
                                :control-name="`legacies.${index}.legacy_id`"
                                :model-value="form.legacies[index].legacy_id"
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
                                     class="rounded-md border border-rule bg-panel p-3"
                                >
                                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">
                                        {{ ancestor.slot }}
                                    </p>
                                    <p class="mt-2 text-xs text-ink-muted">
                                        Stored by name; leave blank if you have not recorded one. Ranks are
                                        unbounded and blank stays not recorded.
                                    </p>

<label class="mt-1 block text-xs font-medium text-ink" :for="`ancestor-${parent.slot}-${ancestorIndex}`">
                                         {{ ancestor.slot }}
                                     </label>
                                     <BaseInput
                                         :id="`ancestor-${parent.slot}-${ancestorIndex}`"
                                         v-model="form.legacies[index].ancestors[ancestorIndex]"
                                         type="text"
                                         list="known-umamusume-names"
                                         autocomplete="off"
                                         :aria-describedby="ancestorError(index, ancestorIndex) ? `ancestor-${parent.slot}-${ancestorIndex}-error` : undefined"
                                         :error="ancestorError(index, ancestorIndex)"
                                     />
                                     <p
                                         v-if="ancestorError(index, ancestorIndex)"
                                         :id="`ancestor-${parent.slot}-${ancestorIndex}-error`"
                                         class="mt-1 text-xs text-risk"
                                     >
                                         {{ ancestorError(index, ancestorIndex) }}
                                     </p>

                                    <!-- The grandparent's two rank fields, the same two facts the wizard
                                         step carries per ancestor (R3-09: four of the six ranks in the
                                         tree sit on these nodes). The labels say grandparent rather than
                                         the wizard's "Own rank" because this screen has two kinds of rank
                                         in one column and the Builder's browser spec pins `Own rank` to
                                         the parent's star count; the wording names the node instead. -->
<label
                                         class="mt-2 block text-xs font-medium text-ink"
                                         :for="`ancestor-rank-letter-${parent.slot}-${ancestorIndex}`"
                                     >
                                         Grandparent rank (letters)
                                     </label>
                                     <BaseInput
                                         :id="`ancestor-rank-letter-${parent.slot}-${ancestorIndex}`"
                                         v-model="form.legacies[index].ancestors_rank_letters[ancestorIndex]"
                                         type="text"
                                         list="known-rank-letters"
                                         autocomplete="off"
                                     />

                                     <label
                                         class="mt-2 block text-xs font-medium text-ink"
                                         :for="`ancestor-rank-${parent.slot}-${ancestorIndex}`"
                                     >
                                         Grandparent rank (stars)
                                     </label>
                                     <BaseInput
                                         :id="`ancestor-rank-${parent.slot}-${ancestorIndex}`"
                                         v-model="form.legacies[index].ancestors_rank[ancestorIndex]"
                                         type="number"
                                         min="0"
                                     />

                                    <!-- The grandparent's own Sparks, the same record the wizard step edits.
                                         The category select and the target it filters are the wizard's
                                         shape, so a write is refused at the boundary when a Stat target is
                                         not a stat (`StoreLegacySelectionRequest::assertTargetMatchesCategory`).
                                         The disclosure wording avoids the parent's own Add Spark control so
                                         the two editors are named apart. -->
                                    <details class="mt-2 rounded-md border border-rule bg-panel p-2">
                                        <summary class="flex min-h-11 cursor-pointer items-center text-xs font-medium text-ink">
                                            <!-- The ancestor's own name in the visible word: four identical
                                                 "Grandparent Sparks" summaries are told apart by proximity
                                                 alone, and the word is the state's carrier (the rule the
                                                 sr-only span below the old summary served). -->
                                            Sparks for {{ ancestor.slot }}
                                        </summary>

<ul
                                            v-if="form.legacies[index].ancestors_sparks[ancestorIndex].length > 0"
                                            class="mt-2 space-y-2"
                                        >
                                            <li
                                                v-for="(spark, sparkIndex) in form.legacies[index].ancestors_sparks[ancestorIndex]"
                                                :key="sparkIndex"
                                                 class="flex flex-wrap items-end gap-2 rounded-md border border-rule bg-panel p-2"
                                            >
                                                <BaseSelect
                                                    :id="`ancestor-spark-kind-${parent.slot}-${ancestorIndex}-${sparkIndex}`"
                                                    v-model="spark.kind"
                                                    label="Kind"
                                                    :options="Object.keys(sparkKinds).map(kind => ({ value: kind, label: sparkKinds[kind] }))"
                                                  />
                                                <BaseSelect
                                                    :id="`ancestor-spark-category-${parent.slot}-${ancestorIndex}-${sparkIndex}`"
                                                    v-model="spark.category"
                                                    label="Category"
                                                    :options="SPARK_CATEGORIES.map(category => ({ value: category, label: category }))"
                                                    @change="spark.target = ''"
                                                  />
                                                <BaseSelect
                                                    :id="`ancestor-spark-target-${parent.slot}-${ancestorIndex}-${sparkIndex}`"
                                                    v-model="spark.target"
                                                    label="Target"
                                                    :options="[{ value: '', label: 'Not recorded' }, ...(sparkTargets[spark.category] || []).map(target => ({ value: target, label: target }))]"
                                                  />
                                                <BaseInput
                                                    :id="`ancestor-spark-stars-${parent.slot}-${ancestorIndex}-${sparkIndex}`"
                                                    v-model="spark.stars"
                                                    type="number"
                                                    min="1"
                                                    max="3"
                                                    label="Stars"
                                                  />
                                                <button
                                                    type="button"
                                                    class="min-h-11 rounded-md border border-rule px-3 text-sm font-medium text-ink"
                                                    @click="removeAncestorSpark(index, ancestorIndex, sparkIndex)"
                                                >
                                                    Remove grandparent Spark
                                                    <span class="sr-only"> from {{ ancestor.slot }}</span>
                                                </button>
                                            </li>
                                        </ul>
                                        <p v-else class="mt-1 text-xs text-ink-muted">
                                            {{ ancestor.sparks_label }}
                                        </p>

                                        <button
                                            type="button"
                                            class="mt-2 min-h-11 rounded-md border border-rule px-3 text-sm font-medium text-ink"
                                            @click="addAncestorSpark(index, ancestorIndex)"
                                        >
                                            Add grandparent Spark
                                            <span class="sr-only"> to {{ ancestor.slot }}</span>
                                        </button>
                                    </details>
                                </li>
                            </ol>

                            <!-- The parent's recorded fields: her own rank, whether she is rented,
                                 and her Spark list. Each is entered, and each is nullable because a
                                 half-read screen is a real state (ADR-0010 Consequences §3). -->
                             <div class="mt-3 rounded-md border border-rule bg-panel p-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">
                                    {{ parent.label }} as you read her
                                </p>
                                <p class="mt-2 text-xs text-ink-muted">
                                    Own rank is unbounded and blank stays not recorded. The letter is the
                                    client's own rank letter set.
                                </p>

                                <label class="mt-3 block text-xs font-medium text-ink" :for="`rank-${parent.slot}`">
                                     Own rank
                                 </label>
                                 <BaseInput
                                     :id="`rank-${parent.slot}`"
                                     v-model="form.legacies[index].rank"
                                     type="number"
                                     min="0"
                                 />

                                <!-- The letter rank the client prints beside the star count, the same
                                     field the wizard step edits against the same request. It sits after
                                     the star count here because the Builder's browser spec pins
                                     `Own rank` to the star count input; the wizard's order is letters
                                     first, and this screen does not reorder what a spec holds. -->
                                <label class="mt-3 block text-xs font-medium text-ink" :for="`rank-letter-${parent.slot}`">
                                     Own rank (letters)
                                 </label>
                                 <BaseInput
                                     :id="`rank-letter-${parent.slot}`"
                                     v-model="form.legacies[index].rank_letter"
                                     type="text"
                                     list="known-rank-letters"
                                     autocomplete="off"
                                     :error="fieldError(index, 'rank_letter')"
                                 />
                                 <p
                                     v-if="fieldError(index, 'rank_letter')"
                                     :id="`rank-letter-${parent.slot}-error`"
                                     class="mt-1 text-xs text-risk"
                                 >
                                     {{ fieldError(index, 'rank_letter') }}
                                 </p>

                                <label class="mt-3 flex min-h-11 items-center gap-2">
                                     <BaseCheckbox
                                         v-model="form.legacies[index].is_guest"
                                       />
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
                                            <BaseSelect
                                                :id="`spark-kind-${parent.slot}-${sparkIndex}`"
                                                v-model="spark.kind"
                                                label="Kind"
                                                :options="Object.keys(sparkKinds).map(kind => ({ value: kind, label: sparkKinds[kind] }))"
                                              />

                                            <BaseInput
                                                :id="`spark-target-${parent.slot}-${sparkIndex}`"
                                                v-model="spark.target"
                                                type="text"
                                                label="Applies to"
                                              />

<BaseInput
                                                :id="`spark-stars-${parent.slot}-${sparkIndex}`"
                                                v-model="spark.stars"
                                                type="number"
                                                min="1"
                                                max="3"
                                                label="Stars"
                                              />

                                            <button
                                                type="button"
                                                class="min-h-11 rounded-full border border-rule px-3 text-sm font-semibold text-ink hover:bg-sunken transition-colors duration-150 ease-out"
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

            <datalist id="known-rank-letters">
                <option v-for="letter in rankLetters" :key="letter" :value="letter" />
            </datalist>

            <!--
                Affinity. The payload stores one grade for the pair (§1.5.4) and the Trainer read it off
                the client, so it is an entered value here. It is not computed, and the note says so,
                because the natural next question on this screen is what the grade is worth in stat
                points and the answer is that this tool does not price it (ADR-0010, D-270).
            -->
<div class="mt-4 max-w-sm">
                 <label class="block text-xs font-medium text-ink" for="affinity">Affinity for the pair</label>
                 <BaseSelect
                     id="affinity"
                     v-model="form.affinity"
                     name="affinity"
                     :options="[{ value: '', label: 'Not recorded' }, ...affinityGrades.map(grade => ({ value: grade, label: grade }))]"
                 />
                 <p class="mt-1 text-xs text-ink-muted" title="The published reference grades each link; this records the pair's own grade as read.">
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
                 <BaseButton
                     type="button"
                     variant="primary"
                     :disabled="form.processing"
                     @click="requestConfirm"
                 >
                     {{ form.processing ? 'Saving…' : 'Confirm Inheritance' }}
                 </BaseButton>

                 <a
                     :href="`/legacy/compare?runs[]=${run.id}`"
                     class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised transition-colors duration-150 ease-out"
                 >
                     Compare this run
                 </a>

                 <p v-if="hasAnyNode" class="text-sm text-ink-muted">
                     Nothing is saved until you confirm.
                 </p>
             </div>

            <!-- Destructive confirmation. A native `<dialog>` opened with `showModal()`: the focus
                 trap, the Escape close and the inert background are the platform's, and the shadow is
                 the one overlay elevation (Track 2-B). -->
            <dialog
                ref="confirmDialog"
                aria-labelledby="legacy-confirm-title"
                class="overlay-shadow w-full max-w-md rounded-lg border border-rule bg-panel p-6 text-ink backdrop:bg-scrim"
            >
                <h3 id="legacy-confirm-title" class="text-lg font-semibold text-ink-strong">Confirm Inheritance</h3>
                <p class="mt-2 text-sm text-ink">
                    This will replace the entire Legacy selection for this run with the values you entered.
                    Any previous selection will be lost.
                </p>
                <p class="mt-2 text-xs text-ink-muted">
                    Nothing is saved until you confirm.
                </p>
                <div class="mt-4 flex justify-end gap-2">
                    <BaseButton
                        type="button"
                        variant="secondary"
                        @click="confirmDialog?.close()"
                    >
                        Cancel
                    </BaseButton>
                    <BaseButton
                        type="button"
                        variant="primary"
                        :disabled="form.processing"
                        @click="submit"
                    >
                        {{ form.processing ? 'Saving…' : 'Confirm' }}
                    </BaseButton>
                </div>
            </dialog>
        </form>
    </AppLayout>
</template>
