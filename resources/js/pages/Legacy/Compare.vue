<script setup lang="ts">
/*
 * The Legacy Lab compare surface (`design-2.0` §46: "comparisons should align identical properties
 * vertically… never force the user to compare two separate cards mentally").
 *
 * **Rows are properties, columns are runs, and every cell is a stored value.** There is no total row,
 * no "best" marker, no score and no sort: each of those would rank configurations on a figure the
 * tool does not hold, which is the computation `ADR-0020` §3 bans and the banner on the screen says so
 * in the Trainer's own words. A Spark count is printed per kind, not summed, because a sum across
 * kinds is a new number rather than a recorded one.
 *
 * The alignment is real markup: one `<table>` with a `<th scope="row">` per property, so a screen
 * reader announces the property with each cell and the columns cannot drift out of alignment the way
 * a grid of independent cards does. The table is inside a scroll container with a `tabindex="0"` and a
 * `role="region"` label, which is the WCAG 1.4.10 pattern for content that must scroll: without the
 * focusable container a keyboard user cannot reach the right-hand columns at 320px.
 *
 * HFW-OVERRIDE (structure: checkbox picker) — `legacy-compare.html` draws no selection UI at all: the
 * run pair arrives in `?runs[]`. This page keeps its checkbox list because the cap of four is a limit
 * the Trainer should see before submitting rather than discover from a rejected write, and a
 * `<select multiple>` hides its options behind a click.
 */
import AppLayout from '../../layouts/AppLayout.vue';
import AbsenceValue from '../../components/AbsenceValue.vue';
import { BaseCheckbox } from '../../components/Form';
import { Head, router, usePage } from '@inertiajs/vue3';
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
    name: string | null;
    rank: number | null;
    is_guest: boolean;
    ancestors: { slot: string; name: string | null }[];
    sparks: SparkRow[];
    spark_counts: { kind: string; kind_label: string; count: number }[];
    probability: { value: null; title: string };
}

interface StartingCap {
    stat: string;
    label: string;
    value: number | null;
    note: string | null;
}

interface CompareColumn {
    veteran_id: number;
    run_id: number;
    trainee: string;
    tags: string[];
    notes: string | null;
    hasSelection: boolean;
    graph: { trainee: { name: string | null }; parents: ParentNode[] };
    caps: StartingCap[];
}

const props = defineProps<{
    columns: CompareColumn[];
    maxRuns: number;
    comparable: { id: number; label: string }[];
    sparkKinds: Record<string, string>;
    notice: string;
}>();

const page = usePage();
const errors = computed(() => (page.props.errors as Record<string, string | undefined> | undefined) ?? {});

// 7.9: every-all-shares surface kept as a comparison, not a redirect. The canonical shape is enough
// to tell "all columns agree on every value" from "any column disagrees". One function, sortable.
function columnSignature(column: CompareColumn): string {
    const parents = column.graph.parents.map((parent) => ({
        slot: parent.slot,
        name: parent.name,
        rank: parent.rank,
        is_guest: parent.is_guest,
        sparks: parent.sparks.map((spark) => ({
            kind: spark.kind,
            target: spark.target,
            stars: spark.stars,
        })),
    }));

    return JSON.stringify({
        trainee: column.graph.trainee.name,
        parents,
        tags: column.tags,
        notes: column.notes,
    });
}

const allColumnsIdentical = computed((): boolean => {
    if (props.columns.length < 2) {
        return false;
    }

    const signatures = props.columns.map(columnSignature);
    const first = signatures[0];

    return signatures.every((signature) => signature === first);
});

// The selection is the query string's own `runs[]`, so the view is shareable and survives a reload
// without any client state to rehydrate. `page.url` rather than a prop, because the two can only ever
// agree at first paint and the picker writes straight to the URL.
const selected = ref(
    (new URL(page.url, 'http://localhost').searchParams.getAll('runs[]')).filter((value) => value !== ''),
);
const loading = ref(false);

function compare(): void {
    router.get(
        '/legacy/compare',
        { runs: selected.value },
        {
            preserveState: true,
            preserveScroll: true,
            onStart: () => {
                loading.value = true;
            },
            onFinish: () => {
                loading.value = false;
            },
        },
    );
}

function isChecked(id: number): boolean {
    return selected.value.includes(String(id));
}

function toggle(id: number): void {
    const value = String(id);

    selected.value = isChecked(id)
        ? selected.value.filter((run) => run !== value)
        : // The cap is enforced here as well as server-side, so a fifth tick explains itself instead
          // of posting something that comes back as a field error. The server rule is still the one
          // that decides; this is the message, not the authority.
          selected.value.length >= props.maxRuns
            ? selected.value
            : [...selected.value, value];
}

const parent = (column: CompareColumn, slot: string): ParentNode | undefined =>
    column.graph.parents.find((node) => node.slot === slot);

const ancestorsOf = (column: CompareColumn, slot: string): string =>
    (parent(column, slot)?.ancestors ?? [])
        .map((ancestor) => ancestor.name)
        .filter((name): name is string => name !== null)
        .join(', ');

const sparkCount = (column: CompareColumn, slot: string, kind: string): number =>
    parent(column, slot)?.spark_counts.find((entry) => entry.kind === kind)?.count ?? 0;

/*
 * The difference column (`legacy-compare.html`, slice 7 step 3.3).
 *
 * "Differences in a column" means one verdict per property, not a per-cell diff the reader has to
 * assemble: `same` when every selected run agrees, `differs by N` when the values are numbers (the star
 * ranks), and `differs (N values)` when they are text. Matching rows still print — an absent difference
 * is a result, and a table that hid its matches would make the reader wonder whether the row was
 * checked at all. "Nothing decisive" is a permitted verdict, so a comparison with no difference says so
 * in words rather than leaving the table to be interpreted.
 */
type Difference = { property: string; verdict: string };

function differenceOf(values: (string | number | null)[]): string {
    if (values.length < 2) {
        return 'single column';
    }

    const present = values.filter((value): value is string | number => value !== null && value !== '');

    if (present.length === 0) {
        return 'same'; // nothing is recorded on any column, so there is nothing that differs
    }

    // A recorded value against an absent one is a difference, not a match: saying "same" there
    // would make the column claim a comparison that never happened.
    if (present.length < values.length) {
        if (values.length === 2) {
            const only = present.length === 1
                ? (values[0] !== null && values[0] !== '' ? 'A' : 'B')
                : null;

            return only === null ? 'differs (one side absent)' : `only ${only} records it`;
        }

        return `differs (${present.length} of ${values.length} recorded)`;
    }

    const distinct = new Set(present.map((value) => String(value)));

    if (distinct.size === 1) {
        return 'same';
    }

    const numbers = present.map((value) => Number(value));

    if (numbers.every((value) => Number.isFinite(value))) {
        // Two columns name the side the difference favours, as the wireframe's "+24 to A" does; a
        // number alone leaves the reader to work out which legacy is ahead.
        if (values.length === 2) {
            const higher = numbers[0] > numbers[1] ? 'A' : 'B';

            return `+${Math.max(...numbers) - Math.min(...numbers)} to ${higher}`;
        }

        // More than two columns cannot be tagged with one letter; name the two extremes instead
        // ("A +80 vs C"), so the reader still knows which sides carry the gap.
        const high = numbers.indexOf(Math.max(...numbers));
        const low = numbers.indexOf(Math.min(...numbers));

        return high === low
            ? `differs by ${Math.max(...numbers) - Math.min(...numbers)}`
            : `${String.fromCharCode(65 + high)} +${Math.max(...numbers) - Math.min(...numbers)} vs ${String.fromCharCode(65 + low)}`;
    }

    return `differs (${distinct.size} values)`;
}

const differences = computed((): Difference[] => {
    const ofColumn = <T>(read: (column: CompareColumn) => T): T[] => props.columns.map(read);

    const rows: Difference[] = [
        { property: 'Trainee', verdict: differenceOf(ofColumn((column) => column.graph.trainee.name)) },
        { property: 'Parent A', verdict: differenceOf(ofColumn((column) => parent(column, 'parent_a')?.name ?? null)) },
        { property: 'Parent A rank', verdict: differenceOf(ofColumn((column) => parent(column, 'parent_a')?.rank ?? null)) },
        { property: 'Parent A ancestors', verdict: differenceOf(ofColumn((column) => ancestorsOf(column, 'parent_a'))) },
        { property: 'Parent B', verdict: differenceOf(ofColumn((column) => parent(column, 'parent_b')?.name ?? null)) },
        { property: 'Parent B rank', verdict: differenceOf(ofColumn((column) => parent(column, 'parent_b')?.rank ?? null)) },
        { property: 'Parent B ancestors', verdict: differenceOf(ofColumn((column) => ancestorsOf(column, 'parent_b'))) },
        {
            property: 'Rented from a friend',
            verdict: differenceOf(ofColumn((column) => {
                const rented = (parent(column, 'parent_a')?.is_guest === true) || (parent(column, 'parent_b')?.is_guest === true);

                return rented ? 'yes' : 'no';
            })),
        },
    ];

    for (const [kind, label] of Object.entries(props.sparkKinds)) {
        rows.push({
            property: `${label} Sparks`,
            verdict: differenceOf(ofColumn((column) => sparkCount(column, 'parent_a', kind) + sparkCount(column, 'parent_b', kind))),
        });
    }

    // The table draws a verdict for every row, and `Spark chance` stores nothing on any column:
    // `differenceOf` answers `same` for all-absent, which is the honest reading rather than a blank.
    rows.push({ property: 'Spark chance', verdict: differenceOf(ofColumn((column) => parent(column, 'parent_a')?.probability.value ?? null)) });
    rows.push({ property: 'Tags', verdict: differenceOf(ofColumn((column) => column.tags.join(', '))) });
    rows.push({ property: 'Notes', verdict: differenceOf(ofColumn((column) => column.notes)) });

    return rows;
});

/*
 * The fold (`legacy-compare.html:70-83`): the verdict is the LAST COLUMN of the one table, not a
 * second table below it. It is a lookup over the same array the old section rendered, so the two
 * cannot disagree — one verdict per property, computed once.
 */
const verdictBy = computed((): Record<string, string> =>
    Object.fromEntries(differences.value.map((row) => [row.property, row.verdict])),
);

/*
 * `same` and `single column` are neutral states, not differences: one says the runs agree, the other
 * says there was nothing to compare against. Both wore the caution tint, which reads as "a difference
 * was found" on a row that carries none (AUD-20261102-024 F-01). The one-column case stays a rendered
 * table — the builder hands a single run to this surface on purpose (`legacy.spec.ts:232`, `:373`), so
 * only the tint was wrong, not the guard that hid the table and was reverted for it.
 */
const verdictPill = (verdict: string): string =>
    verdict === 'same' || verdict === 'single column' ? 'pill pill-neutral' : 'pill pill-caution';
</script>

<template>
    <AppLayout>
        <Head title="Compare two legacies" />
        <template #title>Compare two legacies</template>

        <h2 class="text-lg font-semibold text-ink-strong">Compare recorded selections</h2>

        <form class="mt-4" @submit.prevent="compare">
            <p class="text-sm font-medium text-ink">
                Choose up to {{ maxRuns }} runs that have a Legacy selection recorded
            </p>

            <!-- The checkboxes rather than a multi-select: a `<select multiple>` hides its options
                 behind a click and is awkward with a keyboard, and the cap is a real limit the
                 Trainer should see rather than discover from a rejected submit. -->
            <fieldset class="mt-2">
                <legend class="sr-only">Runs to compare</legend>

                <p v-if="comparable.length === 0" class="rounded-md border border-dashed border-rule bg-panel p-4 text-sm text-ink-muted">
                    No run has a Legacy selection recorded yet, so there is nothing to align. Record one
                    in the Legacy builder first.
                </p>

                <ul v-else class="flex flex-wrap gap-x-4 gap-y-1">
                    <li v-for="option in comparable" :key="option.id">
                        <BaseCheckbox
                            :model-value="isChecked(option.id) ? option.id : ''"
                            :value="option.id"
                            :id="`legacy-compare-run-${option.id}`"
                            :name="`legacy-compare-${option.id}`"
                            :label="option.label"
                            @update:model-value="toggle(option.id)"
                        />
                    </li>
                </ul>
            </fieldset>

            <p v-if="errors.runs" role="alert" class="mt-2 text-sm text-risk">{{ errors.runs }}</p>
            <p
                v-else-if="selected.length > maxRuns"
                role="alert"
                class="mt-2 text-sm text-risk"
            >
                Choose at most {{ maxRuns }} runs.
            </p>

            <button
                type="submit"
                :disabled="loading"
                class="mt-3 inline-flex min-h-11 items-center rounded-md bg-primary-action px-4 font-semibold text-on-primary-action disabled:opacity-60"
            >
                {{ loading ? 'Loading…' : 'Compare' }}
            </button>
        </form>

        <p
            v-if="columns.length === 0"
            class="mt-8 rounded-md border border-dashed border-rule bg-panel p-6 text-sm text-ink-muted"
        >
            Nothing selected to compare. Pick at least one run above; a comparison needs two sides to
            be a comparison, and one side is the builder's own page.
        </p>

        <template v-else>
            <p
                v-if="allColumnsIdentical"
                class="mt-6 rounded-md border border-rule bg-status-caution px-3 py-2 text-sm text-status-caution-ink"
            >
                Nothing decisive: all selected runs share every value, so no row in the difference
                column can be read as a reason to pick one.
            </p>

            <!-- The scroll container. `overflow-x-auto` plus a focusable, labelled region is the
                 WCAG 1.4.10 pattern: the table itself must be keyboard-reachable to scroll, which it
                 is not by default, and 320px cannot hold four columns. The `tabindex` is on the
                 container rather than the table so a screen reader reads the table as a table. -->
            <div
                role="region"
                aria-label="Recorded Legacy selections, side by side"
                tabindex="0"
                class="mt-6 overflow-x-auto rounded-md border border-rule"
            >
                <table class="t min-w-[40rem]">
                    <caption class="px-3 py-2 text-left text-xs text-ink-muted">
                        Recorded values only. Every cell is what a run stores; nothing here is scored,
                        ranked or totalled.
                    </caption>

                    <thead>
                        <tr class="border-b border-rule">
                            <th scope="col" class="px-3 py-2 text-left font-semibold text-ink-strong">
                                Property
                            </th>
                            <th
                                v-for="column in columns"
                                :key="column.run_id"
                                scope="col"
                                class="px-3 py-2 text-left font-semibold text-ink-strong"
                            >
                                {{ column.trainee }}
                                <span class="block text-xs font-normal text-ink-muted">
                                    Run #{{ column.run_id }}
                                </span>
                            </th>
                            <th scope="col" class="px-3 py-2 text-left font-semibold text-ink-strong">
                                Difference
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <!-- One row per stored property, in the same order for every column, which is
                             the whole of §46's ask. -->
                        <tr class="border-b border-rule">
                            <th scope="row" class="px-3 py-2 text-left font-medium text-ink">Trainee</th>
                            <td v-for="column in columns" :key="column.run_id" class="px-3 py-2 text-ink">
                                <span v-if="column.graph.trainee.name">{{ column.graph.trainee.name }}</span>
                                <AbsenceValue v-else compact reason="No trainee name is recorded for this run." />
                            </td>
                            <td class="px-3 py-2">
                                <span :class="verdictPill(verdictBy['Trainee'])">{{ verdictBy['Trainee'] }}</span>
                            </td>
                        </tr>

                        <tr class="border-b border-rule">
                            <th scope="row" class="px-3 py-2 text-left font-medium text-ink">Parent A</th>
                            <td
                                v-for="column in columns"
                                :key="column.run_id"
                                class="px-3 py-2 text-ink"
                            >
                                <span v-if="parent(column, 'parent_a')?.name">
                                    {{ parent(column, 'parent_a')?.name }}
                                </span>
                                <AbsenceValue
                                    v-else
                                    compact
                                    reason="No parent recorded in slot A for this run."
                                />
                            </td>
                            <td class="px-3 py-2">
                                <span :class="verdictPill(verdictBy['Parent A'])">{{ verdictBy['Parent A'] }}</span>
                            </td>
                        </tr>

                        <tr class="border-b border-rule">
                            <th scope="row" class="px-3 py-2 text-left font-medium text-ink">Parent A rank</th>
                            <td
                                v-for="column in columns"
                                :key="column.run_id"
                                class="px-3 py-2 font-mono tabular-nums text-ink"
                            >
                                <template v-if="parent(column, 'parent_a')?.rank !== null && parent(column, 'parent_a') !== undefined">
                                    {{ parent(column, 'parent_a')?.rank }}
                                </template>
                                <!-- The absence is a reason, so it prints as text rather than only on hover. -->
                                <template v-else>
                                    N/A
                                    <span class="block font-sans text-xs font-normal text-ink-muted">
                                        Star count not recorded.
                                    </span>
                                </template>
                            </td>
                            <td class="px-3 py-2">
                                <span :class="verdictPill(verdictBy['Parent A rank'])">{{ verdictBy['Parent A rank'] }}</span>
                            </td>
                        </tr>

                        <tr class="border-b border-rule">
                            <th scope="row" class="px-3 py-2 text-left font-medium text-ink">
                                Parent A ancestors
                            </th>
                            <td
                                v-for="column in columns"
                                :key="column.run_id"
                                class="px-3 py-2 text-ink"
                            >
                                <span v-if="ancestorsOf(column, 'parent_a')">
                                    {{ ancestorsOf(column, 'parent_a') }}
                                </span>
                                <AbsenceValue
                                    v-else
                                    compact
                                    reason="No ancestors recorded for Parent A on this run."
                                />
                            </td>
                            <td class="px-3 py-2">
                                <span :class="verdictPill(verdictBy['Parent A ancestors'])">{{ verdictBy['Parent A ancestors'] }}</span>
                            </td>
                        </tr>

                        <tr class="border-b border-rule">
                            <th scope="row" class="px-3 py-2 text-left font-medium text-ink">Parent B</th>
                            <td
                                v-for="column in columns"
                                :key="column.run_id"
                                class="px-3 py-2 text-ink"
                            >
                                <span v-if="parent(column, 'parent_b')?.name">
                                    {{ parent(column, 'parent_b')?.name }}
                                </span>
                                <span
                                    v-else
                                    class="text-ink-muted"
                                title="This comparison has no second parent recorded." >N/A <span class="text-xs">· no Parent B</span></span>
                            </td>
                            <td class="px-3 py-2">
                                <span :class="verdictPill(verdictBy['Parent B'])">{{ verdictBy['Parent B'] }}</span>
                            </td>
                        </tr>

                        <tr class="border-b border-rule">
                            <th scope="row" class="px-3 py-2 text-left font-medium text-ink">Parent B rank</th>
                            <td
                                v-for="column in columns"
                                :key="column.run_id"
                                class="px-3 py-2 font-mono tabular-nums text-ink"
                            >
                                <template v-if="parent(column, 'parent_b')?.rank !== null && parent(column, 'parent_b') !== undefined">
                                    {{ parent(column, 'parent_b')?.rank }}
                                </template>
                                <template v-else>
                                    N/A
                                    <span class="block font-sans text-xs font-normal text-ink-muted">
                                        Star count not recorded.
                                    </span>
                                </template>
                            </td>
                            <td class="px-3 py-2">
                                <span :class="verdictPill(verdictBy['Parent B rank'])">{{ verdictBy['Parent B rank'] }}</span>
                            </td>
                        </tr>

                        <tr class="border-b border-rule">
                            <th scope="row" class="px-3 py-2 text-left font-medium text-ink">
                                Parent B ancestors
                            </th>
                            <td
                                v-for="column in columns"
                                :key="column.run_id"
                                class="px-3 py-2 text-ink"
                            >
                                <span v-if="ancestorsOf(column, 'parent_b')">
                                    {{ ancestorsOf(column, 'parent_b') }}
                                </span>
                                <span
                                    v-else
                                    class="text-ink-muted"
                                title="No ancestors are recorded for Parent B." >N/A <span class="text-xs">· none recorded for Parent B</span></span>
                            </td>
                            <td class="px-3 py-2">
                                <span :class="verdictPill(verdictBy['Parent B ancestors'])">{{ verdictBy['Parent B ancestors'] }}</span>
                            </td>
                        </tr>

                        <!-- Rented, per parent. A recorded boolean, not a derived one. -->
                        <tr class="border-b border-rule">
                            <th scope="row" class="px-3 py-2 text-left font-medium text-ink">
                                Rented from a friend
                            </th>
                            <td
                                v-for="column in columns"
                                :key="column.run_id"
                                class="px-3 py-2 text-ink"
                            >
                                <span v-if="parent(column, 'parent_a')?.is_guest || parent(column, 'parent_b')?.is_guest">
                                    {{ parent(column, 'parent_a')?.is_guest ? 'A' : '' }}{{
                                        parent(column, 'parent_a')?.is_guest && parent(column, 'parent_b')?.is_guest ? ' + ' : ''
                                    }}{{ parent(column, 'parent_b')?.is_guest ? 'B' : '' }}
                                </span>
                                <span v-else class="text-ink-muted">
                                    No
                                </span>
                            </td>
                            <td class="px-3 py-2">
                                <span :class="verdictPill(verdictBy['Rented from a friend'])">{{ verdictBy['Rented from a friend'] }}</span>
                            </td>
                        </tr>

                        <!-- Per-kind Spark counts, one row each. Never summed into a single "Sparks:
                             7" figure, because a total across kinds is a number this tool would be
                             inventing. The brief's `~10% ★★★` is absent for the reason the builder's
                             note gives: no sourced star-roll table is held here. -->
                        <tr
                            v-for="(label, kind) in sparkKinds"
                            :key="kind"
                            class="border-b border-rule"
                        >
                            <th scope="row" class="px-3 py-2 text-left font-medium text-ink">
                                {{ label }} Sparks
                            </th>
                            <td
                                v-for="column in columns"
                                :key="column.run_id"
                                class="px-3 py-2 font-mono tabular-nums text-ink"
                            >
                                <span>
                                    {{ sparkCount(column, 'parent_a', kind) + sparkCount(column, 'parent_b', kind) }}
                                    <span class="block font-sans text-xs font-normal text-ink-muted">
                                        {{ sparkCount(column, 'parent_a', kind) }} from Parent A,
                                        {{ sparkCount(column, 'parent_b', kind) }} from Parent B.
                                    </span>
                                </span>
                            </td>
                            <td class="px-3 py-2">
                                <span :class="verdictPill(verdictBy[`${label} Sparks`])">{{ verdictBy[`${label} Sparks`] }}</span>
                            </td>
                        </tr>

                        <tr class="border-b border-rule">
                            <th scope="row" class="px-3 py-2 text-left font-medium text-ink">
                                Spark chance
                            </th>
                            <td
                                v-for="column in columns"
                                :key="column.run_id"
                                class="px-3 py-2 font-mono tabular-nums text-ink"
                            >
                                <span class="text-ink-muted">
                                    N/A
                                    <span class="block font-sans text-xs font-normal">
                                        {{ parent(column, 'parent_a')?.probability.title }}
                                    </span>
                                </span>
                            </td>
                            <td class="px-3 py-2">
                                <span :class="verdictPill(verdictBy['Spark chance'])">{{ verdictBy['Spark chance'] }}</span>
                            </td>
                        </tr>

                        <tr class="border-b border-rule">
                            <th scope="row" class="px-3 py-2 text-left font-medium text-ink">Tags</th>
                            <td v-for="column in columns" :key="column.run_id" class="px-3 py-2 text-ink">
                                <span v-if="column.tags.length > 0">{{ column.tags.join(', ') }}</span>
                                <AbsenceValue v-else compact reason="No tags are recorded for this run." />
                            </td>
                            <td class="px-3 py-2">
                                <span :class="verdictPill(verdictBy['Tags'])">{{ verdictBy['Tags'] }}</span>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row" class="px-3 py-2 text-left font-medium text-ink">Notes</th>
                            <td v-for="column in columns" :key="column.run_id" class="px-3 py-2 text-ink">
                                <span v-if="column.notes">{{ column.notes }}</span>
                                <AbsenceValue v-else compact reason="No note is recorded for this run." />
                            </td>
                            <td class="px-3 py-2">
                                <span :class="verdictPill(verdictBy['Notes'])">{{ verdictBy['Notes'] }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- The verdict is the LAST COLUMN of the table above (`legacy-compare.html:70-83`), so the
                 separate table this section used to draw is gone. The fold is the slice working as
                 written and not a divergence: the wireframe's comparison is one table with a
                 Difference column. The wider row set kept under R2 is the divergence, and
                 SCREEN_SPEC's register carries it. -->

            <p class="mt-3 text-xs text-ink-muted">
                Affinity is stored per run and is not priced by this tool.
            </p>

            <!-- The wireframe's starting-cap cards (`legacy-compare.html:90-115`): one card per column.
                 The run's entered ceiling overrides the scenario's published cap; Wit has no entered
                 column, so it prints the scenario's cap and says so. -->
            <div class="mt-4 grid gap-4 g-2">
                <section
                    v-for="(column, index) in columns"
                    :key="column.run_id"
                    class="card"
                    :aria-labelledby="`legacy-caps-${column.run_id}`"
                >
                    <h2 :id="`legacy-caps-${column.run_id}`">
                        Starting caps · legacy {{ String.fromCharCode(65 + index) }}
                    </h2>
                    <table class="t mt-2">
                        <tbody>
                            <tr v-for="cap in column.caps" :key="cap.stat" class="border-b border-rule last:border-b-0">
                                <th scope="row" class="py-2 pr-3 text-left font-medium text-ink">{{ cap.label }}</th>
                                <td class="py-2 text-right n font-mono tabular-nums text-ink">
                                    <template v-if="cap.value !== null">{{ cap.value }}</template>
                                    <AbsenceValue
                                        v-else
                                        compact
                                        :reason="cap.note ?? 'No ceiling is recorded for this stat.'"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>
            </div>
            <p class="mt-2 text-xs text-ink-muted">
                An entered ceiling overrides the scenario's published cap. Wit prints the scenario's cap:
                this build stores no entered Wit ceiling.
            </p>
        </template>
    </AppLayout>
</template>
