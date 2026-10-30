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
import AncestorEditor from '@/components/legacy/AncestorEditor.vue';
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
    /**
     * The run's starting ceilings per stat, already owned by `ScenarioCaps::forRun()` server-side
     * (`LegacyController::startingCaps()`, which prints the run's entered ceiling where it states one
     * and the scenario's published cap otherwise). The preview table's `Scenario cap` column reads
     * this; no arithmetic is done in the page, and `note` carries the reason when `value` is null.
     */
    caps: { stat: string; label: string; value: number | null; note: string | null }[];
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

/*
 * The draft as it opened, captured once. The savebar's status word and its Discard control both read
 * from this: "Unsaved legacy" means the draft differs from the record, and Discard restores exactly
 * this snapshot. Captured after `useForm` seeds from the payload, so it is the stored selection rather
 * than an empty draft.
 */
const pristineSnapshot = JSON.stringify(form.data());

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

/*
 * The preview's parent columns.
 *
 * Each parent cell prints the spark the record holds for that stat — its family and star rank, e.g.
 * "★3 Blue" — which is what `Career/LegacySelect.vue` prints for the same table. It never prints a
 * cap contribution, because summing one is the derivation ADR-0020 §3 bars (see the card's own
 * DOMAIN OVERRIDE comment).
 */
function parentSparkFor(parentIndex: number, statLabel: string): string | null {
    const sparks = form.legacies[parentIndex]?.sparks ?? [];
    const match = sparks.find((spark) => spark.category === 'Stat' && spark.target === statLabel);

    if (match === undefined) {
        return null;
    }

    const stars = match.stars === '' ? '?' : match.stars;

    return `★${stars} ${match.kind}`;
}

/** The recorded blue sparks across both parents — a count of what the record holds, not a price. */
const blueSparkCount = computed((): number =>
    form.legacies.reduce(
        (total, parent) => total + parent.sparks.filter((spark) => spark.kind === 'blue').length,
        0,
    ),
);

/**
 * Whether the draft differs from what the record already stores.
 *
 * The savebar shows Discard only when there is something to discard, so this compares the serialised
 * form against the payload the page opened with. Comparing the two JSON strings is deliberate: every
 * field in `form` is a scalar or a list of scalars, so the string is a faithful stand-in for the
 * draft and costs nothing to recompute.
 */
const isDirty = computed((): boolean => JSON.stringify(form.data()) !== pristineSnapshot);

/*
 * Discard returns the form to the state it opened in, without a round trip: the payload is already in
 * the page, so re-seeding from it is both cheaper and safer than reloading and losing the run context.
 */
function discard(): void {
    form.defaults(JSON.parse(pristineSnapshot) as typeof form.data);
    form.reset();
    form.clearErrors();
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
                                    <AncestorEditor
                                        :legacy="form.legacies[index]"
                                        :ancestor-index="ancestorIndex"
                                        :slot="parent.slot"
                                        :label="ancestor.slot"
                                        :spark-kinds="sparkKinds"
                                        :spark-targets="sparkTargets"
                                        :empty-sparks-label="ancestor.sparks_label"
                                        :error="ancestorError(index, ancestorIndex)"
                                    />

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

            <!--
                The preview card (`legacy-builder.html:100-121`).

                DOMAIN OVERRIDE (ADR-0020 §3): the wireframe prints `Scenario cap 1400 | From this
                legacy +32 | Starting ceiling 1432` and a "Blue total +32" tile. Those are sums — a
                predicted cap derived from the picks — and ADR-0020 §3 keeps derivation out of this
                tool: "Nothing computes a Spark firing, an affinity payout, an offspring, or an
                'expected inheritance' … it does not derive an outcome."

                This is not a new reading of that clause. `Career/LegacySelect.vue:29-30`, `:435-437`
                and `:442-444` already refused the identical figure on the identical grounds and shipped
                the absence instead, printing each parent column as the recorded spark (`★3 Blue`)
                rather than a priced sum. Two screens disagreeing about whether a number may be derived
                would be worse than one screen short of the wireframe, so this card adopts the wizard's
                shape: the columns the wireframe draws, the figures the record actually holds.
            -->
            <section class="card mt-4" aria-labelledby="legacy-preview">
                <h2 id="legacy-preview" class="text-lg font-semibold text-ink-strong">
                    Preview for a new run
                </h2>
                <p class="mt-1 text-xs text-ink-muted">
                    What these two parents add to a run, before a single turn is played. Each parent
                    column prints the spark the record holds; the projected sum is never computed,
                    because an inheritance outcome is not this tool's to derive (ADR-0020 §3).
                </p>

                <div class="mt-3 overflow-x-auto" role="region" tabindex="0" aria-label="Preview for a new run, scrollable">
                    <table class="t">
                        <thead>
                            <tr>
                                <th scope="col">Stat</th>
                                <th scope="col" class="n">Scenario cap</th>
                                <th scope="col" class="n">From Parent A</th>
                                <th scope="col" class="n">From Parent B</th>
                                <th scope="col" class="n">Projected</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="cap in props.caps" :key="cap.stat">
                                <th scope="row" class="text-left font-normal">{{ cap.label }}</th>
                                <td class="n font-mono tabular-nums">
                                    <template v-if="cap.value !== null">{{ cap.value }}</template>
                                    <span v-else class="text-ink-muted" :title="cap.note ?? undefined">N/A</span>
                                </td>
                                <td class="n font-mono tabular-nums">
                                    {{ parentSparkFor(0, cap.label) ?? '-' }}
                                </td>
                                <td class="n font-mono tabular-nums">
                                    {{ parentSparkFor(1, cap.label) ?? '-' }}
                                </td>
                                <td class="n text-ink-muted">not computed</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="tiles mt-3 sm:grid-cols-3">
                    <div class="tile">
                        <div class="k">Blue sparks recorded</div>
                        <div class="v">{{ blueSparkCount }}</div>
                    </div>
                    <div class="tile">
                        <div class="k">Aptitude shifts</div>
                        <div class="v na">none this pick</div>
                    </div>
                    <div class="tile">
                        <div class="k">Skill hints</div>
                        <div class="v na">none this pick</div>
                    </div>
                </div>

                <p class="note mt-3 text-xs text-ink-muted">
                    Picking one blue per parent is the maximum this pair offers. Choosing Memory instead
                    of a spark leaves that parent contributing nothing to the run's caps.
                </p>
            </section>

            <p
                v-if="form.errors.legacies"
                role="alert"
                class="mt-3 rounded-md border border-risk bg-raised px-3 py-2 text-sm text-risk"
            >
                {{ form.errors.legacies }}
            </p>

            <!--
                The savebar (`legacy-builder.html:123-127`).

                One filled action, a status word, and a Discard that appears only when there is
                something to discard. The wireframe's status slot is `.dirty`; it reads "Unsaved legacy"
                while the draft differs from the stored selection and "Saved" once it does not.
            -->
            <div class="savebar mt-5">
                <span class="dirty" role="status">{{ isDirty ? 'Unsaved legacy' : 'Saved' }}</span>

                <button
                    v-if="isDirty"
                    type="button"
                    class="inline-flex min-h-11 items-center rounded-full border border-rule bg-raised px-4 font-semibold text-ink-strong transition-colors duration-150 ease-out hover:bg-panel"
                    :disabled="form.processing"
                    @click="discard"
                >
                    Discard
                </button>

                <a
                    :href="`/legacy/compare?runs[]=${run.id}`"
                    class="inline-flex min-h-11 items-center rounded-full border border-rule bg-raised px-4 font-semibold text-ink-strong transition-colors duration-150 ease-out hover:bg-panel"
                >
                    Compare this run
                </a>


                <!--
                    The savebar's own statement of what the press does, restored where it has to be
                    read. R4 moved this sentence into the confirm dialog and left only that copy, so
                    it became invisible to anyone who has not opened the dialog - and
                    `legacy.spec.ts:228` reads it as the note beside the action, not as dialog text.
                    The dialog keeps its own copy, because a Trainer reading the confirmation needs it
                    there too; this is the one on the bar.
                -->
                <p v-if="hasAnyNode" class="text-sm text-ink-muted">
                    Nothing is saved until you confirm.
                </p>

                <BaseButton
                    type="button"
                    variant="primary"
                    :disabled="form.processing"
                    @click="requestConfirm"
                >
                    {{ form.processing ? 'Saving…' : 'Save legacy' }}
                </BaseButton>
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
