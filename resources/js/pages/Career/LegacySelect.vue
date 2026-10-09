<script setup lang="ts">
/*
 * The career setup wizard's ancestry step, `SCR-CAR-008` (PRD FR-G-1, `ADR-0020` §1 and §3, under
 * `ADR-0010`). Step 4 of six.
 *
 * **Record only.** This page stores what the Trainer read off the client and computes nothing: no
 * inheritance outcome, no expected stat, no Affinity payout, no Spark roll chance, no score, no ranking,
 * no recommended combination (`ADR-0020` §3, PRD FR-G-4). The chance a Spark rolls is `N/A` with the reason
 * in its `title`, and the grade for the pair is a letter that was read, never a value. The sentence the
 * Legacy Lab carries as its banner arrives here as the `notice` prop from its one owner.
 *
 * **Six nodes, one shape.** The graph is `AncestryGraph::build()` — the same call the run-scoped builder
 * and the compare surface make — so a node means the same thing on this step as it does on a career. The
 * nodes are a nested list rather than a drawn tree, because a tree drawn as a picture is unreadable to a
 * screen reader and unreachable by keyboard, and the ancestry assignment is a labelled `<select>` with no
 * dragging path at all (WCAG 2.2 SC 2.5.7).
 *
 * **Why the pick control now works.** `AncestryNode` emitted nothing from its `<select>` until this step
 * landed: a native select fires `change`, not `update:model-value`, so the listener every assignable surface
 * carries never ran and a chosen parent was dropped on submit. The node emits on `change` and takes its
 * current value as a prop, which is also what lets a saved step read the pick back off the draft.
 */
import SetupLayout from '../../layouts/SetupLayout.vue';
import AncestryNode from '../../components/legacy/AncestryNode.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { nextTick } from 'vue';

interface SparkRow {
    kind: string;
    kind_label: string;
    target: string | null;
    stars: number | null;
}

interface ParentNode {
    slot: string;
    label: string;
    name: string | null;
    rank: number | null;
    rank_letter: string | null;
    rank_label: string;
    is_guest: boolean;
    ancestors: { slot: string; name: string | null }[];
    sparks: SparkRow[];
    spark_counts: { kind: string; kind_label: string; count: number }[];
    probability: { value: null; title: string };
}

interface SparkDraft {
    kind: string;
    target: string;
    stars: string | number;
}

const props = defineProps<{
    trainee: { id: number; name: string; name_ja: string | null } | null;
    scenarioLabel: string | null;
    hasSelection: boolean;
    /** The grade the draft holds for the pair, or null before one was read. */
    affinity: string | null;
    graph: { trainee: { name: string | null }; parents: ParentNode[] };
    parents: (number | null)[];
    roster: { id: number; name: string; costume: string | null; label: string }[];
    rosterTotal: number;
    knownNames: string[];
    sparkKinds: Record<string, string>;
    affinityGrades: string[];
    rankLetters: string[];
    notice: string;
}>();

/**
 * The form is seeded from the draft, not left blank: a step the Trainer returns to shows what they entered.
 * Every nullable figure stays nullable on the way in and posts as an empty field on the way out, because a
 * half-read screen is a real state the payload exists to hold (`ADR-0010` Consequences §3) and a zero would
 * be a claim.
 */
const form = useForm({
    affinity: props.affinity ?? '',
    legacies: props.graph.parents.map((parent, index) => ({
        legacy_id: String(props.parents[index] ?? ''),
        rank: parent.rank === null ? '' : String(parent.rank),
        rank_letter: parent.rank_letter ?? '',
        is_guest: parent.is_guest,
        ancestors: [parent.ancestors[0]?.name ?? '', parent.ancestors[1]?.name ?? ''] as [string, string],
        sparks: parent.sparks.map((spark) => ({
            kind: spark.kind,
            target: spark.target ?? '',
            stars: spark.stars === null ? '' : spark.stars,
        })),
    })),
});

const saving = (): void => {
    form.transform((data) => ({
        ...data,
        // An empty grade is "not recorded", and the payload's own reader accepts null and nothing else
        // (`LegacySelectionPayload::affinity()`), so the blank field is sent as the absence rather than as
        // the empty string the select carries.
        affinity: data.affinity === '' ? null : data.affinity,
        legacies: data.legacies.map((legacy) => ({
            ...legacy,
            legacy_id: legacy.legacy_id === '' ? null : Number(legacy.legacy_id),
            rank: legacy.rank === '' ? null : Number(legacy.rank),
            rank_letter: legacy.rank_letter === '' ? null : legacy.rank_letter,
            sparks: legacy.sparks.filter((spark) => !isBlankSpark(spark)).map((spark) => ({
                kind: spark.kind,
                target: spark.target === '' ? null : spark.target,
                stars: spark.stars === '' ? null : Number(spark.stars),
            })),
        })),
    })).put('/career/setup/legacy', {
        preserveScroll: true,
        // Focus returns after the new props land, in `onSuccess` + `nextTick`: the component instance
        // survives a client-side visit so an `onMounted` hook would never re-run, and taking focus on a cold
        // load would steal it from a Trainer who has not acted yet (WCAG 2.4.3). The `id` is on the button
        // itself, because an `id` on a non-focusable wrapper makes `.focus()` a silent no-op.
        onSuccess: () => {
            nextTick(() => document.getElementById('legacy-save')?.focus());
        },
    });
};

function assignParent(index: number, value: string): void {
    form.legacies[index].legacy_id = value;

    // Choosing a Veteran does not copy her Sparks into the record. The Sparks a node shows are the ones the
    // Trainer read off the client and typed below, and a library row holds no Spark read-back at all.
    // Filling them from the roster would be this tool asserting what a parent carries, which is the
    // computation the slice refuses (`ADR-0020` §3).
}

function addSpark(index: number): void {
    form.legacies[index].sparks.push({ kind: 'blue', target: '', stars: '' } as SparkDraft);
}

function removeSpark(index: number, sparkIndex: number): void {
    form.legacies[index].sparks.splice(sparkIndex, 1);
}

/**
 * A draft row the Trainer added and left blank. The editor appends one on `Add Spark`, and it is not a
 * Spark until it carries a target or a star count: the server discards the same shape
 * (`StoreLegacySelectionRequest::payload()`), and this keeps the chip list and the posted payload honest
 * while it is still a draft.
 */
function isBlankSpark(spark: SparkDraft): boolean {
    return spark.target === '' && spark.stars === '';
}

/**
 * The chips a node shows are the rows in the form, so a Spark reads the same while it is being typed as it
 * does once saved. `kind_label` comes from the server map for a stored Spark and from the kind's own word
 * for one being added now; either way `SparkChip` spells the kind in words beside its star count, so
 * nothing is carried by colour alone (WCAG 1.4.1).
 */
const nodeSparks = (index: number): SparkRow[] =>
    form.legacies[index].sparks.filter((spark) => !isBlankSpark(spark)).map((spark) => ({
        kind: spark.kind,
        kind_label: props.sparkKinds[spark.kind] ?? spark.kind,
        target: spark.target === '' ? null : spark.target,
        stars: spark.stars === '' ? null : Number(spark.stars),
    }));

const errorFor = (index: number): string | undefined =>
    (form.errors as Record<string, string | undefined>)[`legacies.${index}.legacy_id`];

const ancestorError = (index: number, slot: number): string | undefined =>
    (form.errors as Record<string, string | undefined>)[`legacies.${index}.ancestors.${slot}`];
</script>

<template>
    <SetupLayout :step="4">
        <Head title="Legacy" />
        <template #title>Legacy</template>

        <p class="max-w-2xl text-sm text-ink-muted">
            The two parents this career will be bred from are entered here as you read them in the client,
            before any run exists. Their own two ancestors each are part of the record, so the diagram holds
            six nodes.
        </p>

        <p
            role="note"
            class="mt-3 flex items-start gap-2 rounded-md border border-rule bg-sunken px-3 py-2 text-sm text-ink"
        >
            <span aria-hidden="true" class="font-mono font-bold text-ink-strong">i</span>
            <span>{{ notice }}</span>
        </p>

        <p class="mt-3 text-sm text-ink">
            Trainee:
            <strong v-if="props.trainee" class="text-ink-strong">
                {{ props.trainee.name }}
                <span v-if="props.trainee.name_ja" lang="ja" class="font-normal text-ink-muted">
                    {{ props.trainee.name_ja }}
                </span>
            </strong>
            <span
                v-else
                class="text-ink-muted"
                title="No trainee has been chosen in this setup draft yet, so the graph has no first node."
            >N/A, no trainee chosen</span>
            &middot;
            <span v-if="props.scenarioLabel" class="text-ink-muted">{{ props.scenarioLabel }}</span>
            <span
                v-else
                class="text-ink-muted"
                title="No scenario has been chosen in this setup draft yet."
            >Scenario: N/A</span>
        </p>

        <p v-if="!props.hasSelection" class="mt-2 text-sm text-ink-muted">
            No Legacy is recorded for this setup yet. Nothing is stored until you save, and a figure you have
            not read is left blank rather than filled in.
        </p>
        <p v-else class="mt-2 text-sm text-ink-muted">
            This setup already holds a Legacy. Saving below replaces it whole; a Spark you remove here is
            gone, not hidden.
        </p>

        <!-- The library is empty on a fresh install, and a pick control with no rows is not a choice. Said
             here as a named absence with the way to fill it, rather than leaving the two slots looking like
             controls that are merely unused. -->
        <p
            v-if="props.rosterTotal === 0"
            class="mt-3 rounded-md border border-dashed border-rule bg-raised p-3 text-sm text-ink-muted"
        >
            No Veterans recorded yet. The Veteran library is where a parent is picked from, and it holds a
            run once you complete one, so this step cannot name a parent until then. Record the parents you
            read below as ancestors instead, or open the Legacy Lab to see the library.
        </p>
        <p v-else-if="props.rosterTotal > props.roster.length" class="mt-3 text-sm text-ink-muted">
            The library holds {{ props.rosterTotal }} rows and this pick list shows the first
            {{ props.roster.length }}. The rest are on the Legacy Lab browse screen.
        </p>

        <ol class="mt-4 space-y-2">
            <li class="rounded-md border-2 border-rule bg-panel p-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Trainee</p>
                <p class="mt-1 text-sm font-semibold text-ink-strong">
                    <span v-if="props.graph.trainee.name">{{ props.graph.trainee.name }}</span>
                    <span v-else class="text-ink-muted" title="No trainee chosen in this draft yet.">
                        N/A, not chosen
                    </span>
                </p>
            </li>

            <li
                v-for="(parent, index) in props.graph.parents"
                :key="parent.slot"
                class="ml-3 border-l-2 border-rule pl-3"
            >
                <ul class="space-y-2">
                    <li>
                        <AncestryNode
                            :label="parent.label"
                            :name="roster.find((row) => String(row.id) === form.legacies[index].legacy_id)?.name ?? parent.name"
                            :rank="parent.rank"
                            :rank-label="parent.rank_label"
                            :is-guest="form.legacies[index].is_guest"
                            :sparks="nodeSparks(index)"
                            :probability="parent.probability"
                            :control-name="`legacies.${index}.legacy_id`"
                            :model-value="form.legacies[index].legacy_id"
                            :options="roster"
                            :error="errorFor(index)"
                            assignable
                            @update:model-value="assignParent(index, String($event))"
                        />

                        <ol class="mt-2 ml-3 space-y-2 border-l-2 border-rule pl-3">
                            <li
                                v-for="(ancestor, ancestorIndex) in parent.ancestors"
                                :key="ancestor.slot"
                                class="rounded-md border border-rule bg-raised p-3"
                            >
                                <label
                                    class="block text-xs font-medium text-ink"
                                    :for="`ancestor-${parent.slot}-${ancestorIndex}`"
                                >
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

                        <div class="mt-3 rounded-md border border-rule bg-raised p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">
                                {{ parent.label }} as you read her
                            </p>

                            <label class="mt-2 block text-xs font-medium text-ink" :for="`rank-letter-${parent.slot}`">
                                Own rank (letters)
                            </label>
                            <input
                                :id="`rank-letter-${parent.slot}`"
                                v-model="form.legacies[index].rank_letter"
                                type="text"
                                list="known-rank-letters"
                                autocomplete="off"
                                class="mt-1 block min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-sm text-ink"
                            >
                            <p class="mt-1 text-xs text-ink-muted">
                                The letter the client shows, from its own set. Blank stays not recorded.
                            </p>

                            <label class="mt-3 block text-xs font-medium text-ink" :for="`rank-${parent.slot}`">
                                Own rank (stars)
                            </label>
                            <input
                                :id="`rank-${parent.slot}`"
                                v-model="form.legacies[index].rank"
                                type="number"
                                min="0"
                                class="mt-1 block min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-sm text-ink"
                            >
                            <p class="mt-1 text-xs text-ink-muted">
                                Blank stays N/A. Unbounded on purpose: the sources give no ceiling on a
                                Legacy's own star count (`ADR-0010`).
                            </p>

                            <label class="mt-3 flex min-h-11 items-center gap-2">
                                <input
                                    v-model="form.legacies[index].is_guest"
                                    type="checkbox"
                                    class="h-5 w-5 rounded border-rule"
                                >
                                <span class="text-xs text-ink">Rented for the run</span>
                            </label>

                            <fieldset class="mt-3">
                                <legend class="text-xs font-semibold uppercase tracking-wide text-ink-muted">
                                    Sparks
                                </legend>

                                <ul v-if="form.legacies[index].sparks.length > 0" class="mt-2 space-y-2">
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
                                                    v-for="(label, kind) in props.sparkKinds"
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
                                <p
                                    v-else
                                    class="mt-1 text-xs text-ink-muted"
                                    title="No Sparks entered for this parent yet."
                                >
                                    No Sparks recorded.
                                </p>

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
            <option v-for="name in props.knownNames" :key="name" :value="name" />
        </datalist>

        <datalist id="known-rank-letters">
            <option v-for="letter in props.rankLetters" :key="letter" :value="letter" />
        </datalist>

        <div class="mt-4 max-w-sm">
            <label class="block text-xs font-medium text-ink" for="affinity">Affinity for the pair</label>
            <select
                id="affinity"
                v-model="form.affinity"
                name="affinity"
                class="mt-1 block min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-sm text-ink"
            >
                <option value="">Not recorded</option>
                <option v-for="grade in props.affinityGrades" :key="grade" :value="grade">{{ grade }}</option>
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
                id="legacy-save"
                type="button"
                :disabled="form.processing"
                class="enamel inline-flex min-h-11 items-center rounded-full bg-chrome px-4 font-semibold text-on-chrome disabled:cursor-wait disabled:bg-disabled"
                @click="saving"
            >
                {{ form.processing ? 'Saving…' : 'Save Legacy' }}
            </button>

            <p v-if="form.processing" role="status" class="text-sm text-ink-muted">Saving the Legacy…</p>

            <a
                href="/career/setup/deck"
                class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised"
            >
                Next: Support deck
            </a>
        </div>
    </SetupLayout>
</template>
