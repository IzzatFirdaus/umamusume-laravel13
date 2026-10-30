<script setup lang="ts">
/*
 * HFW-OVERRIDE (computed/rect: the wizard-wide class) - `legacy-diff-{1280,390}.json` measures the
 * app's DESIGN.md tokens against the wireframe's mock CSS and the shell wrapper's offset. Tokens
 * outrank the mock palette (`AGENTS.md` §2) and the wrapper moves every box, so both buckets carry
 * entries by construction; the page is not restyled to chase the mock's values.
 */
/*
 * The career setup wizard's legacy step, `SCR-CAR-008` (PRD FR-G-1, `ADR-0020` §1 and §3, under
 * `ADR-0010`). Step 4 of six.
 *
 * **Record only.** This page stores what the Trainer read off the client and computes nothing: no
 * inheritance outcome, no expected stat, no Affinity payout, no Spark roll chance, no score, no ranking,
 * no recommended combination (`ADR-0020` §3, PRD FR-G-4). The sentence the Legacy Lab carries as its
 * banner arrives here as the `notice` prop from its one owner.
 *
 * **Two slots, not a graph (Phase D).** The wireframe (`career-legacy.html:87-120`) draws two parent
 * cards - `Parent A` and `Parent B`, each carrying a `Chosen` / `Not chosen` pill, the recorded name,
 * the veteran's scenario and save date, and the sparks the draft already holds. The six-node graph the
 * step used to draw moved to where it is read: `InheritanceEvent.vue:442-450` renders the ancestry on
 * the run, which is the one surface that can name every node from a persisted payload.
 *
 * **The pick stays a control, not a link.** The wireframe's empty card links out to a library browse
 * page (`Choose a veteran`), but the library is run-scoped and cannot write the session draft, so the
 * pick control is the slot's own `<select>` carrying that label, and `Inspect` opens the chosen
 * veteran's run record in the Legacy Lab. `Leave empty` clears the slot: the note beside it names an
 * empty slot a legitimate build, not an error.
 *
 * **Projection is a named absence.** The wireframe's Projected start table prints `cap + 16 per ★3`
 * arithmetic per parent. `ADR-0020` §3 forbids computing an inheritance outcome, so the projected
 * column states the named absence and the note says why; the recorded Blue spark against a stat is
 * what the two parent columns print.
 */
import SetupLayout from '../../layouts/SetupLayout.vue';
import AbsenceValue from '../../components/AbsenceValue.vue';
import { BaseInput, BaseSelect } from '../../components/Form';
import AncestorEditor from '../../components/legacy/AncestorEditor.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

interface SparkDraft {
    kind: string;
    target: string;
    stars: string | number;
}

interface ParentCard {
    veteranId: number;
    runId: number;
    name: string;
    japanese: string | null;
    scenario: string | null;
    stars: number | null;
    savedAt: string | null;
    sparks: { family: string; stat: string | null; stars: number | null }[];
}

const props = defineProps<{
    /** The steps this session has opened, the step bar's completed marking reads them. */
    visited: number[];
    /** The steps that have data stored in the draft (`SetupDraft::saved()`), read apart from `visited`. */
    saved: number[];
    trainee: { id: number; name: string; name_ja: string | null } | null;
    scenarioLabel: string | null;
    hasSelection: boolean;
    /** The grade the draft holds for the pair, or null before one was read. */
    affinity: string | null;
    /** The two slots' recorded payload, exactly as `legacy_selection` holds it, so a saved step reads back. */
    payload: {
        affinity: string | null;
        legacies: {
            legacy_id: number | null;
            /** A parent typed by name rather than picked from the library (ADR-0010, 2026-10-30). */
            name: string | null;
            rank: number | null;
            rank_letter: string | null;
            is_guest: boolean;
            ancestors: (string | null)[];
            ancestors_rank_letters: (string | null)[];
            ancestors_rank: (number | null)[];
            ancestors_sparks: { kind: string; target: string | null; stars: number | null }[][];
            sparks: { kind: string; target: string | null; stars: number | null }[];
        }[];
    } | null;
    /** The two parent cards, resolved through the library picks; null while a slot is empty. */
    parentA: ParentCard | null;
    parentB: ParentCard | null;
    /** The draft scenario's published ceilings per stat, for the projected-start table's read-out. */
    scenarioCaps: Record<string, number>;
    /** The stat matrix order the table prints, from `config('scenarios.stat_order')`. */
    statOrder: string[];
    /** The library ids the pick controls select, so a saved step reads back what was chosen rather
     *  than what the client still holds. Position in the list is the slot: 0 is Parent A. */
    parents: (number | null)[];
    roster: { id: number; name: string; costume: string | null; label: string }[];
    rosterTotal: number;
    affinityGrades: string[];
    /** The names a Trainer can pick from in a grandparent field: a spelling aid, never a requirement. */
    knownNames: string[];
    /** The Spark kind vocabulary, keyed by the stored value. */
    sparkKinds: Record<string, string>;
    /** The rank letters the client prints, for the rank-letter field's spelling aid. */
    rankLetters: string[];
    /** The targets each Spark category offers, from the same lists the write validates against. */
    sparkTargets: { Stat: string[]; Aptitude: string[]; Skill: string[] };
    notice: string;
    /** `route('career.legacy.store')`, supplied by the server so the page never hard-codes the URL. */
    action: string;
}>();

/**
 * The form is seeded from the draft, not left blank: a step the Trainer returns to shows what they
 * entered. Every nullable figure stays nullable on the way in and posts as an empty field on the way
 * out, because a half-read screen is a real state the payload exists to hold (`ADR-0010` Consequences
 * §3) and a zero would be a claim.
 *
 * The four grandparents **are** edited on this step: a parent's grandparents are frequently not in
 * the local catalogue, so each node is recorded by name with its rank letter, star rank and Sparks.
 * That half needs no library row and no new payload key - `ancestors`, `ancestors_rank_letters`,
 * `ancestors_rank` and `ancestors_sparks` were already on the draft and already posted back here. A
 * stored payload is read back whole on save, so re-saving a
 * pick never erases the record behind it.
 */
/**
 * A slot is in manual-name mode when the payload carries a name and no library id: the picker then
 * shows the sentinel and the editors render, so a saved manual parent reads back as what it is
 * rather than as "Not chosen".
 */
function manualFor(index: number): boolean {
    const legacy = props.payload?.legacies?.[index];

    return legacy !== undefined && legacy.legacy_id === null && (legacy.name ?? '') !== '';
}

/** True while a slot is in manual-name mode; the picker then shows the name input instead of a row. */
const manual = ref([manualFor(0), manualFor(1)]);

/** The picker's own value: the sentinel while the slot is manual, the library id otherwise. */
const pickValue = (index: number): string =>
    manual.value[index] ? '__manual__' : (form.legacies[index]?.legacy_id ?? '');

const form = useForm({
    affinity: props.affinity ?? '',
    legacies: (props.payload?.legacies ?? []).map((legacy, index) => ({
        legacy_id: String(props.parents[index] ?? ''),
        name: legacy.name ?? '',
        rank: legacy.rank === null ? '' : String(legacy.rank),
        rank_letter: legacy.rank_letter ?? '',
        is_guest: legacy.is_guest,
        ancestors: [(legacy.ancestors[0] ?? '') as string, (legacy.ancestors[1] ?? '') as string] as [string, string],
        ancestors_rank_letters: (legacy.ancestors_rank_letters ?? []).map((letter) => letter ?? ''),
        ancestors_rank: (legacy.ancestors_rank ?? []).map((rank) => (rank === null ? '' : String(rank))),
        ancestors_sparks: (legacy.ancestors_sparks ?? []).map((list) =>
            list.map((spark) => ({
                kind: spark.kind,
                // Same transient recovery as the parent's own Sparks: the editor filters its target
                // list by this category, and the stored payload keeps only the target string.
                category: sparkCategoryFor(spark.target),
                target: spark.target ?? '',
                stars: spark.stars === null ? '' : spark.stars,
            })),
        ),
        sparks: (legacy.sparks ?? []).map((spark) => ({
            kind: spark.kind,
            // The category is a transient form field (the payload stores only the target); the
            // editor needs it to filter the target list, so it is recovered from the stored target.
            category: sparkCategoryFor(spark.target),
            target: spark.target ?? '',
            stars: spark.stars === null ? '' : spark.stars,
        })),
    })),
});

// A draft that never wrote a selection has no legs array; the two slots still exist on the form so the
// first pick is a full payload row, not a half of one.
while (form.legacies.length < 2) {
    form.legacies.push({
        legacy_id: '',
        name: '',
        rank: '',
        rank_letter: '',
        is_guest: false,
        ancestors: ['', ''],
        ancestors_rank_letters: [],
        ancestors_rank: [],
        ancestors_sparks: [],
        sparks: [],
    });
}

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
            name: legacy.name === '' ? null : legacy.name,
            rank: legacy.rank === '' ? null : Number(legacy.rank),
            rank_letter: legacy.rank_letter === '' ? null : legacy.rank_letter,
            ancestors_rank_letters: legacy.ancestors_rank_letters.map((letter: string) => (letter === '' ? null : letter)),
            ancestors_rank: legacy.ancestors_rank.map((rank: string) => (rank === '' ? null : Number(rank))),
            ancestors_sparks: legacy.ancestors_sparks.map((list) =>
                list
                    .filter((spark) => !isBlankSpark(spark))
                    .map((spark) => ({
                        kind: spark.kind,
                        target: spark.target === '' ? null : spark.target,
                        stars: spark.stars === '' ? null : Number(spark.stars),
                    })),
            ),
            sparks: legacy.sparks
                .filter((spark) => !isBlankSpark(spark))
                .map((spark) => ({
                    kind: spark.kind,
                    // Transient: the request validates the target against this category at the write
                    // boundary and stores neither.
                    category: spark.category,
                    target: spark.target === '' ? null : spark.target,
                    stars: spark.stars === '' ? null : Number(spark.stars),
                })),
        })),
    })).put(props.action, {
        preserveScroll: true,

        // Focus returns after the new props land: the component instance survives a client-side visit
        // so an `onMounted` hook would never re-run, and taking focus on a cold load would steal it
        // from a Trainer who has not acted yet (WCAG 2.4.3). The `id` is on the button itself, because
        // an `id` on a non-focusable wrapper makes `.focus()` a silent no-op.
        onSuccess: () => {
            nextTick(() => document.getElementById('legacy-save')?.focus());
        },
    });
};

function assignParent(index: number, value: string): void {
    if (value === '__manual__') {
        // Two writers, one fact: a slot either picks a library row or holds a typed name, never both.
        // legacy_id stays '' so the picker never carries a value the server would refuse.
        form.legacies[index].legacy_id = '';
        manual.value[index] = true;
        form.legacies[index].name = form.legacies[index].name ?? '';

        return;
    }

    manual.value[index] = false;
    form.legacies[index].legacy_id = value;
}

function clearParent(index: number): void {
    form.legacies[index].legacy_id = '';
    manual.value[index] = false;
    form.legacies[index].name = '';
}

/**
 * A draft row the Trainer added and left blank. It is not a Spark until it carries a target or a star
 * count: the server discards the same shape (`StoreLegacySelectionRequest::payload()`), and this keeps
 * the posted payload honest while it is still a draft.
 */
/**
 * The category a stored target belongs to, recovered for the transient form field. The payload keeps
 * only the target string (ADR-0010), so the editor re-derives what it already knew from the list the
 * server validated against.
 */
function sparkCategoryFor(target: string | null): string {
    if (target === null || target === '') {
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

function isBlankSpark(spark: SparkDraft): boolean {
    return spark.target === '' && spark.stars === '';
}

const errorFor = (index: number, field = 'legacy_id'): string | undefined =>
    (form.errors as Record<string, string | undefined>)[`legacies.${index}.${field}`];

/** The server's message for one grandparent's name, keyed the way the rules name it. */
const ancestorError = (index: number, ancestor: number): string | undefined =>
    (form.errors as Record<string, string | undefined>)[`legacies.${index}.ancestors.${ancestor}`];

const parents = (): (ParentCard | null)[] => [props.parentA, props.parentB];

const pickOptions = (): { value: string; label: string }[] => [
    { value: '', label: 'Not chosen' },
    ...props.roster.map((row) => ({ value: String(row.id), label: row.label })),
    // A parent who is not a saved Veteran: a friend's finished Umamusume, rented from the friend
    // list (UMAMUSUME_REFERENCE.md §1.5.4), has no Veteran row to pick. The sentinel is a picker
    // value only and never reaches the form's legacy_id, which the server validates against the
    // veterans table; `assignParent` clears that id and seeds the name when this is chosen.
    { value: '__manual__', label: 'Not in my library - enter a name' },
];
</script>

<template>
    <SetupLayout :step="4" :visited="props.visited" :saved="props.saved">
        <Head title="Inherit from two parents" />
        <template #eyebrow>New career</template>
        <template #title>Inherit from two parents</template>
        <template #lede>
            A run starts with the sparks its parents carry. Both slots are optional; a run with no parents
            starts from the card's own base.
        </template>

        <!--
          The HFW's head-band pair (`career-legacy.html:61-65`). Both doors used to live at the foot of the
          form, below the ancestry graph, so the step's only exit sat after every panel on the page.

          "Continue to deck" stays a navigation rather than becoming this step's submit: the write endpoint
          redirects back to `career.legacy` to report the save, so a forward button that merely posted would
          leave the Trainer on this step with a "saved" message and no way on. The write keeps its own
          control beside the fields it writes.
        -->
        <template #actions>
            <a
                href="/career/setup/target"
                class="inline-flex min-h-11 items-center justify-center rounded-md border border-rule bg-raised px-4 text-sm font-semibold text-ink hover:bg-panel"
            >
                Back
            </a>
            <a
                href="/career/setup/deck"
                class="inline-flex min-h-11 items-center justify-center rounded-md border border-primary-action bg-primary-action px-4 text-sm font-semibold text-on-primary-action hover:bg-primary-action/90"
            >
                Continue to deck
            </a>
        </template>

        <p
            role="note"
            class="mt-3 flex items-start gap-2 rounded-md border border-rule bg-sunken px-3 py-2 text-sm text-ink"
        >
            <span aria-hidden="true" class="font-mono font-bold text-ink-strong">i</span>
            <span>{{ notice }}</span>
        </p>

        <!-- The library is empty on a fresh install, and a pick control with no rows is not a choice. Said
             here as a named absence with the way to fill it, rather than leaving the two slots looking like
             controls that are merely unused. -->
        <p
            v-if="props.rosterTotal === 0"
            class="mt-3 rounded-lg border border-dashed border-rule bg-panel p-3 text-sm text-ink-muted"
        >
            No Veterans recorded yet. The Veteran library is where a parent is picked from, and it holds a
            run once you complete one, so this step cannot name a parent until then.
        </p>
        <p v-else-if="props.rosterTotal > props.roster.length" class="mt-3 text-sm text-ink-muted">
            The library holds {{ props.rosterTotal }} rows and this pick list shows the first
            {{ props.roster.length }}. The rest are on the Legacy Lab browse screen.
        </p>

        <!-- The HFW's two parent slots (`career-legacy.html:87-120`): one card per parent, with the
             recorded name, the veteran's scenario and save date, and the draft's sparks. The chosen card
             takes the pick border and the positive pill; the empty slot stays neutral. -->
        <div class="mt-4 grid grid-cols-1 gap-4 min-[1081px]:grid-cols-2">
            <article
                v-for="(card, index) in parents()"
                :key="index"
                class="card"
                :class="card !== null ? 'is-picked' : ''"
            >
                <!-- The slot header is not a `.card > h2` title: the wireframe draws the parent name
                     and its state pill on one row with no rule under it, so a wrapper keeps the
                     primitive's heading rule off this one. -->
                <header class="flex items-center justify-between">
                    <h2 class="flex w-full items-center justify-between text-lg font-semibold text-ink-strong">
                        <span>{{ index === 0 ? 'Parent A' : 'Parent B' }}</span>
                        <span
                            class="pill"
                            :class="card !== null ? 'pill-positive' : 'pill-neutral'"
                        >
                            {{ card !== null ? 'Chosen' : 'Not chosen' }}
                        </span>
                    </h2>
                </header>

                <template v-if="card !== null">
                    <div class="mt-2 text-[15px] font-extrabold text-ink-strong">
                        {{ card.name }}
                        <span v-if="card.japanese" lang="ja" class="text-xs font-semibold text-ink-muted">
                            {{ card.japanese }}
                        </span>
                    </div>
                    <div class="mt-0.5 text-xs text-ink-muted">
                        <span v-if="card.scenario">{{ card.scenario }}</span>
                        <template v-if="card.stars !== null"> · {{ '★'.repeat(card.stars) }}</template>
                        <template v-if="card.savedAt !== null"> · saved {{ card.savedAt }}</template>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <span
                            v-for="(spark, sparkIndex) in card.sparks"
                            :key="sparkIndex"
                            class="tag"
                        >
                            {{ spark.family }} spark<template v-if="spark.stat !== null"> · {{ spark.stat }}</template>
                            <template v-if="spark.stars !== null"> · ★{{ spark.stars }}</template>
                        </span>
                    </div>
                </template>
                <p v-else class="mt-2 text-sm text-ink-muted">
                    Pick from your veteran library, or leave this slot empty.
                </p>

                <div class="mt-3">
                    <label class="block text-xs font-medium text-ink" :for="`legacy-pick-${index}`">
                        {{ card === null ? 'Choose a veteran' : 'Change the veteran' }}
                    </label>
                    <BaseSelect
                        :id="`legacy-pick-${index}`"
                        :model-value="pickValue(index)"
                        :name="`legacies.${index}.legacy_id`"
                        :options="pickOptions()"
                        :error="errorFor(index)"
                        @update:model-value="assignParent(index, String($event))"
                    />
                    <div v-if="manual[index]" class="mt-2">
                        <label class="block text-xs font-medium text-ink" :for="`legacy-name-${index}`">
                            Parent name
                        </label>
                        <BaseInput
                            :id="`legacy-name-${index}`"
                            v-model="form.legacies[index].name"
                            :name="`legacies.${index}.name`"
                            type="text"
                            list="known-umamusume-names"
                            autocomplete="off"
                        />
                        <p class="mt-1 text-xs text-ink-muted">
                            The name of the Umamusume who finished the career, as you read it. The list is
                            the catalogue of trainees this tool knows, offered as a spelling aid; a name
                            not in it is still storable, because a friend's finished run is never a local
                            row (UMAMUSUME_REFERENCE.md §1.5.4).
                        </p>

                        <!-- The parent's own record, the same three facts the payload stores for a
                             library pick. Entered here, read by the projected-start table in realtime. -->
                        <div class="mt-2 grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-medium text-ink" :for="`legacy-rank-letter-${index}`">
                                    Rank (letters)
                                </label>
                                <BaseInput
                                    :id="`legacy-rank-letter-${index}`"
                                    v-model="form.legacies[index].rank_letter"
                                    :name="`legacies.${index}.rank_letter`"
                                    type="text"
                                    list="known-rank-letters"
                                    autocomplete="off"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-ink" :for="`legacy-rank-${index}`">
                                    Rank (stars)
                                </label>
                                <BaseInput
                                    :id="`legacy-rank-${index}`"
                                    v-model="form.legacies[index].rank"
                                    :name="`legacies.${index}.rank`"
                                    type="number"
                                    min="0"
                                />
                            </div>
                        </div>

                        <!-- The parent's own Sparks: kind, category, target, stars. The same boundary the
                             server validates - a Stat category whose target is not a stat is refused
                             (StoreLegacySelectionRequest::assertTargetMatchesCategory). -->
                        <details class="mt-2 rounded-md border border-rule bg-panel p-2">
                            <summary class="flex min-h-11 cursor-pointer items-center text-xs font-medium text-ink">
                                Sparks for this parent
                            </summary>
                            <ul v-if="form.legacies[index].sparks.length > 0" class="mt-2 space-y-2">
                                <li
                                    v-for="(spark, sparkIndex) in form.legacies[index].sparks"
                                    :key="sparkIndex"
                                    class="flex flex-wrap items-end gap-2 rounded-md border border-rule bg-panel p-2"
                                >
                                    <BaseSelect
                                        :id="`legacy-spark-kind-${index}-${sparkIndex}`"
                                        v-model="spark.kind"
                                        label="Kind"
                                        :options="Object.keys(props.sparkKinds).map((kind) => ({ value: kind, label: props.sparkKinds[kind] }))"
                                    />
                                    <BaseSelect
                                        :id="`legacy-spark-category-${index}-${sparkIndex}`"
                                        v-model="spark.category"
                                        label="Category"
                                        :options="['Stat', 'Aptitude', 'Skill'].map((category) => ({ value: category, label: category }))"
                                        @change="spark.target = ''"
                                    />
                                    <BaseSelect
                                        :id="`legacy-spark-target-${index}-${sparkIndex}`"
                                        v-model="spark.target"
                                        label="Target"
                                        :options="[{ value: '', label: 'Not recorded' }, ...(props.sparkTargets[(spark.category as 'Stat' | 'Aptitude' | 'Skill')] || []).map((target) => ({ value: target, label: target }))]"
                                    />
                                    <BaseInput
                                        :id="`legacy-spark-stars-${index}-${sparkIndex}`"
                                        v-model="spark.stars"
                                        type="number"
                                        min="1"
                                        max="3"
                                        label="Stars"
                                    />
                                    <button
                                        type="button"
                                        class="inline-flex min-h-11 items-center rounded-md border border-rule bg-raised px-3 text-xs font-semibold text-ink hover:bg-panel"
                                        @click="form.legacies[index].sparks.splice(sparkIndex, 1)"
                                    >
                                        Remove Spark
                                    </button>
                                </li>
                            </ul>
                            <button
                                type="button"
                                class="mt-2 inline-flex min-h-11 items-center rounded-md border border-rule bg-raised px-3 text-xs font-semibold text-ink hover:bg-panel"
                                @click="form.legacies[index].sparks.push({ kind: 'blue', category: 'Stat', target: '', stars: 1 })"
                            >
                                Add Spark
                            </button>
                        </details>
                    </div>

                    <p v-if="errorFor(index)" :id="`legacy-pick-${index}-error`" class="mt-1 text-xs text-risk">
                        {{ errorFor(index) }}
                    </p>
                </div>


                <div class="mt-3 flex flex-wrap gap-2">
                    <a
                        v-if="card !== null"
                        :href="`/legacy/${card.runId}`"
                        class="inline-flex min-h-11 items-center justify-center rounded-md border border-rule bg-raised px-3 text-sm font-semibold text-ink hover:bg-panel"
                    >
                        Inspect
                    </a>
                    <button
                        type="button"
                        class="inline-flex min-h-11 items-center justify-center rounded-md border border-rule bg-raised px-3 text-sm font-semibold text-ink hover:bg-panel"
                        @click="clearParent(index)"
                    >
                        Leave empty
                    </button>
                </div>
            </article>
        </div>

        <!-- The projection and the affinity reading sit side by side: the grade is the record the
             projection is read against, and the save control belongs beside the fields it writes
             (`career-legacy-deck-steps.spec.ts:167` records that intent). -->
        <div class="mt-4 grid grid-cols-1 gap-4 min-[1081px]:grid-cols-2">
            <!--
              DOMAIN OVERRIDE (ADR-0020 §3): the wireframe's Projected start table prints the
              `cap + 16 per ★3` sum as a number; this tool prices no pair, so the projected column
              states the absence and the two parent columns print only what the record holds.
            -->
            <section class="card" aria-labelledby="projected-start">
                <h2 id="projected-start">Projected start</h2>
                <p class="sub">
                    What the two parents add to this run, before a single turn is played. The recorded Blue
                    spark against a stat is what a parent column prints; the projected sum is never
                    computed, because an inheritance outcome is not this tool's to derive (ADR-0020 §3).
                </p>

                <div class="mt-2 overflow-x-auto" role="region" tabindex="0" aria-label="Projected start, scrollable">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-rule text-left text-xs font-semibold uppercase tracking-wider text-ink-muted">
                            <th class="py-2 pr-3">Stat</th>
                            <th class="pr-3 text-right">Scenario cap</th>
                            <th class="pr-3 text-right">From Parent A</th>
                            <th class="pr-3 text-right">From Parent B</th>
                            <th class="text-right">Projected</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="stat in props.statOrder" :key="stat" class="border-b border-rule">
                            <td class="py-2 pr-3 text-ink">{{ stat }}</td>
                            <td class="pr-3 text-right font-mono tabular-nums text-ink-muted">
                                {{ props.scenarioCaps[stat] ?? '-' }}
                            </td>
                            <td class="pr-3 text-right font-mono tabular-nums text-ink">
                                <template v-if="form.legacies[0]?.sparks.some((s) => s.kind === 'blue' && s.target === stat)">
                                    ★{{ form.legacies[0].sparks.find((s) => s.kind === 'blue' && s.target === stat)?.stars ?? '?' }} Blue
                                </template>
                                <template v-else>-</template>
                            </td>
                            <td class="pr-3 text-right font-mono tabular-nums text-ink">
                                <template v-if="form.legacies[1]?.sparks.some((s) => s.kind === 'blue' && s.target === stat)">
                                    ★{{ form.legacies[1].sparks.find((s) => s.kind === 'blue' && s.target === stat)?.stars ?? '?' }} Blue
                                </template>
                                <template v-else>-</template>
                            </td>
                            <td class="text-right font-mono tabular-nums text-ink-muted">-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            </section>

            <section class="card" aria-labelledby="pair-affinity">
                <h2 id="pair-affinity">Pair Affinity</h2>
                <div class="mt-2">
                    <label class="block text-xs font-medium text-ink" for="affinity">Affinity</label>
                    <BaseSelect
                        id="affinity"
                        v-model="form.affinity"
                        name="affinity"
                        :options="[{ value: '', label: 'Not recorded' }, ...props.affinityGrades.map(grade => ({ value: grade, label: grade }))]"
                    />
                    <p class="mt-1 text-xs text-ink-muted">
                        The grade you read for the pair, not a score. This tool does not price it.
                    </p>
                    <p v-if="form.errors.affinity" class="mt-1 text-xs text-risk">{{ form.errors.affinity }}</p>
                </div>

            <p v-if="!props.hasSelection" class="mt-2 text-sm text-ink-muted">
                No Legacy is recorded for this setup yet. Nothing is stored until you save, and a figure you have
                not read is left blank rather than filled in.
            </p>
            <p v-else class="mt-2 text-sm text-ink-muted">
                This setup already holds a Legacy. Saving below replaces it whole; a Spark you remove here is
                gone, not hidden.
            </p>

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
                    class="inline-flex min-h-11 items-center rounded-md border border-rule bg-raised px-4 text-sm font-semibold text-ink hover:bg-panel disabled:cursor-wait disabled:opacity-60"
                    @click="saving"
                >
                    {{ form.processing ? 'Saving…' : 'Save Legacy' }}
                </button>

                <p v-if="form.processing" role="status" class="text-sm text-ink-muted">Saving the Legacy…</p>
            </div>
            </section>
        </div>

        <!--
          The four grandparents, editable here, grouped by parent. A parent's grandparents are
          frequently absent from the local catalogue, so they are recorded by name rather than picked
          (`ADR-0010` Consequences §2). The editors live in their own card and not inside the parent
          `<article>`: the parent card is measured against a 25-descendant ceiling by the step spec,
          and two full editors per card would put every card permanently over it. The "two per parent"
          grouping survives here as two labelled subgroups.
        -->
        <section class="card mt-4" aria-labelledby="grandparents">
            <h2 id="grandparents">Grandparents</h2>
            <div class="mt-3 grid grid-cols-1 gap-4 min-[1081px]:grid-cols-2">
                <div v-for="(slotNames, parentIndex) in ['a', 'b']" :key="slotNames">
                    <p class="text-sm font-semibold text-ink-strong">
                        {{ parentIndex === 0 ? 'Grandparents of Parent A' : 'Grandparents of Parent B' }}
                    </p>
                    <ol class="mt-2 space-y-2">
                        <li
                            v-for="(slotName, ancestorIndex) in (parentIndex === 0 ? ['Grandparent A1', 'Grandparent A2'] : ['Grandparent B1', 'Grandparent B2'])"
                            :key="slotName"
                            class="rounded-md border border-rule bg-panel p-3"
                        >
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">
                                {{ slotName }}
                            </p>
                            <AncestorEditor
                                :legacy="form.legacies[parentIndex]"
                                :ancestor-index="ancestorIndex"
                                :slot="parentIndex === 0 ? 'a' : 'b'"
                                :label="slotName"
                                :spark-kinds="props.sparkKinds"
                                :spark-targets="props.sparkTargets"
                                empty-sparks-label="No Sparks recorded for this node."
                                :error="ancestorError(parentIndex, ancestorIndex)"
                            />
                        </li>
                    </ol>
                </div>
            </div>
        </section>

        <!--
          DOMAIN OVERRIDE (DM-3): four spark families; HFW draws three
          The wireframe draws Blue, Pink and the unique-skill factor. Mechanics has four families, because
          Green (unique-skill hints) and White (skill hints and competition) both exist and a parent's
          spark list can carry either. Drawing three would make the page silently disagree with the data
          the row above it prints, so all four are named here and the override is recorded rather than
          hidden.
        -->
        <section class="card mt-5" aria-labelledby="inheritance-does">
            <h2 id="inheritance-does">What inheritance actually does</h2>

            <div class="mt-2 overflow-x-auto" role="region" tabindex="0" aria-label="What inheritance actually does, scrollable">
                <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-rule text-left text-xs font-semibold uppercase tracking-wider text-ink-muted">
                        <th class="py-2 pr-3">Mechanism</th>
                        <th class="pr-3">Effect</th>
                        <th>When it applies</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-rule align-top">
                        <td class="py-2 pr-3 font-semibold text-ink-strong">Blue spark</td>
                        <td class="pr-3 text-ink">+16 to the stat's cap per ★3</td>
                        <td class="text-ink-muted">At run start, and again at both inheritance moments.</td>
                    </tr>
                    <tr class="border-b border-rule align-top">
                        <td class="py-2 pr-3 font-semibold text-ink-strong">Pink spark</td>
                        <td class="pr-3 text-ink">+5 / +12 / +21 by grade</td>
                        <td class="text-ink-muted">Same three moments as the Blue spark, on the aptitude it names.</td>
                    </tr>
                    <tr class="border-b border-rule align-top">
                        <td class="py-2 pr-3 font-semibold text-ink-strong">Unique-skill factor</td>
                        <td class="pr-3 text-ink">Inherits her unique skill as a factor</td>
                        <td class="text-ink-muted">Read at run start; a factor is not a cost and carries no SP price here.</td>
                    </tr>
                    <tr class="border-b border-rule align-top">
                        <td class="py-2 pr-3 font-semibold text-ink-strong">Green spark</td>
                        <td class="pr-3 text-ink">Unique-skill hints</td>
                        <td class="text-ink-muted">Hints raise the chance a skill is offered, not its level.</td>
                    </tr>
                    <tr class="border-b border-rule align-top">
                        <td class="py-2 pr-3 font-semibold text-ink-strong">White spark</td>
                        <td class="pr-3 text-ink">Skill hints and competition bonuses</td>
                        <td class="text-ink-muted">Same reading as Green: a hint, not a granted skill.</td>
                    </tr>
                    <tr class="align-top">
                        <td class="py-2 pr-3 font-semibold text-ink-strong">Enhanced spark</td>
                        <td class="pr-3"><AbsenceValue compact reason="No source in this repository states an enhanced spark's rate, so none is printed." /></td>
                        <td class="text-ink-muted">Named because the client shows it; unpriced because this tool holds no figure.</td>
                    </tr>
                </tbody>
            </table>
            </div>

            <!-- The wireframe's own note inside this card (`career-legacy.html:146`): it names the loop
                 the +16 rule belongs to, so the number above reads as one step rather than a bonus. -->
            <p class="note mt-3">
                <b>The cap raise is the loop.</b> Train to a final total, bank the factor grade, inherit the
                +16 per ★3, then train past the previous ceiling. A run with two ★3 blue parents therefore
                starts with a higher ceiling than the scenario publishes, which is why the cockpit shows an
                entered ceiling beside the scenario's rather than one number.
            </p>
        </section>

        <!-- Full width now: the rule is a statement about the pair, so it reads across the page
             while its prose stays at a readable measure rather than running the full width. -->
        <section class="card mt-4" aria-labelledby="parents-differ">
            <h2 id="parents-differ">Parents must differ</h2>
            <p class="mt-2 max-w-3xl text-sm leading-relaxed text-ink-muted">
                A veteran cannot be its own parent, and the two slots cannot hold the same record. The
                picker enforces the first rule by hiding the trainee you are running; the second by
                refusing an already-chosen record.
            </p>
            <p class="note mt-3 max-w-3xl text-sm text-ink-muted">
                Choosing no parents is a legitimate build, not an error. The run carries two
                nullable parent ids for exactly that reason, and no screen invents a default pair.
            </p>
        </section>
    
        <!--
          The two spelling aids the grandparent editors point at with `list=`. Both are aids and
          never requirements: a name absent from the list is still storable, because a grandparent
          is frequently not in the local catalogue (`ADR-0010` Consequences §2).
        -->
        <datalist id="known-umamusume-names">
            <option v-for="name in props.knownNames" :key="name" :value="name" />
        </datalist>
        <datalist id="known-rank-letters">
            <option v-for="letter in props.rankLetters" :key="letter" :value="letter" />
        </datalist>
</SetupLayout>
</template>
