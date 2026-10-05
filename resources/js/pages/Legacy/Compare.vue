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
 */
import AppLayout from '../../layouts/AppLayout.vue';
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

interface CompareColumn {
    veteran_id: number;
    run_id: number;
    trainee: string;
    tags: string[];
    notes: string | null;
    hasSelection: boolean;
    graph: { trainee: { name: string | null }; parents: ParentNode[] };
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
        { 'runs[]': selected.value },
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
</script>

<template>
    <AppLayout>
        <Head title="Compare Legacy selections" />
        <template #title>Compare Legacy selections</template>

        <h2 class="text-2xl font-semibold text-ink-strong">Compare recorded selections</h2>

        <p
            role="note"
            class="mt-3 flex items-start gap-2 rounded-md border border-rule bg-sunken px-3 py-2 text-sm text-ink"
        >
            <span aria-hidden="true" class="font-mono font-bold text-ink-strong">i</span>
            <span>{{ notice }}</span>
        </p>

        <form class="mt-4" @submit.prevent="compare">
            <p class="text-sm font-medium text-ink">
                Choose up to {{ maxRuns }} runs that have a Legacy selection recorded
            </p>

            <!-- The checkboxes rather than a multi-select: a `<select multiple>` hides its options
                 behind a click and is awkward with a keyboard, and the cap is a real limit the
                 Trainer should see rather than discover from a rejected submit. -->
            <fieldset class="mt-2">
                <legend class="sr-only">Runs to compare</legend>

                <p v-if="comparable.length === 0" class="rounded-md border border-dashed border-rule bg-raised p-4 text-sm text-ink-muted">
                    No run has a Legacy selection recorded yet, so there is nothing to align. Record one
                    in the Legacy builder first.
                </p>

                <ul v-else class="flex flex-wrap gap-x-4 gap-y-1">
                    <li v-for="option in comparable" :key="option.id">
                        <label class="flex min-h-11 items-center gap-2 text-sm text-ink">
                            <input
                                type="checkbox"
                                :value="option.id"
                                :checked="isChecked(option.id)"
                                class="h-5 w-5 rounded border-rule"
                                @change="toggle(option.id)"
                            >
                            <span>{{ option.label }}</span>
                        </label>
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
                class="enamel mt-3 inline-flex min-h-11 items-center rounded-full bg-chrome px-4 font-semibold text-on-chrome disabled:opacity-60"
            >
                {{ loading ? 'Loading…' : 'Compare' }}
            </button>
        </form>

        <p
            v-if="columns.length === 0"
            class="mt-8 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted"
        >
            Nothing selected to compare. Pick at least one run above; a comparison needs two columns to
            be a comparison, and one column is the builder's own page.
        </p>

        <template v-else>
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
                <table class="w-full min-w-[40rem] border-collapse bg-raised text-sm">
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
                        </tr>
                    </thead>

                    <tbody>
                        <!-- One row per stored property, in the same order for every column, which is
                             the whole of §46's ask. -->
                        <tr class="border-b border-rule">
                            <th scope="row" class="px-3 py-2 text-left font-medium text-ink">Trainee</th>
                            <td v-for="column in columns" :key="column.run_id" class="px-3 py-2 text-ink">
                                {{ column.graph.trainee.name ?? 'N/A' }}
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
                                <span
                                    v-else
                                    class="text-ink-muted"
                                    title="No parent recorded in slot A for this run."
                                >N/A</span>
                            </td>
                        </tr>

                        <tr class="border-b border-rule">
                            <th scope="row" class="px-3 py-2 text-left font-medium text-ink">Parent A rank</th>
                            <td
                                v-for="column in columns"
                                :key="column.run_id"
                                class="px-3 py-2 font-mono tabular-nums text-ink"
                            >
                                <span
                                    :title="parent(column, 'parent_a')?.rank === null
                                        ? 'This Legacy’s own star count was not recorded.'
                                        : undefined"
                                >{{ parent(column, 'parent_a')?.rank ?? 'N/A' }}</span>
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
                                <span
                                    v-else
                                    class="text-ink-muted"
                                    title="No ancestors recorded for Parent A on this run."
                                >N/A</span>
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
                                    title="No parent recorded in slot B for this run."
                                >N/A</span>
                            </td>
                        </tr>

                        <tr class="border-b border-rule">
                            <th scope="row" class="px-3 py-2 text-left font-medium text-ink">Parent B rank</th>
                            <td
                                v-for="column in columns"
                                :key="column.run_id"
                                class="px-3 py-2 font-mono tabular-nums text-ink"
                            >
                                <span
                                    :title="parent(column, 'parent_b')?.rank === null
                                        ? 'This Legacy’s own star count was not recorded.'
                                        : undefined"
                                >{{ parent(column, 'parent_b')?.rank ?? 'N/A' }}</span>
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
                                    title="No ancestors recorded for Parent B on this run."
                                >N/A</span>
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
                                <span v-else class="text-ink-muted" title="Neither parent is marked rented.">
                                    No
                                </span>
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
                                <span :title="`${sparkCount(column, 'parent_a', kind)} from Parent A, ${sparkCount(column, 'parent_b', kind)} from Parent B.`">
                                    {{ sparkCount(column, 'parent_a', kind) + sparkCount(column, 'parent_b', kind) }}
                                </span>
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
                                <span
                                    class="text-ink-muted"
                                    :title="parent(column, 'parent_a')?.probability.title"
                                >N/A</span>
                            </td>
                        </tr>

                        <tr class="border-b border-rule">
                            <th scope="row" class="px-3 py-2 text-left font-medium text-ink">Tags</th>
                            <td v-for="column in columns" :key="column.run_id" class="px-3 py-2 text-ink">
                                <span v-if="column.tags.length > 0">{{ column.tags.join(', ') }}</span>
                                <span v-else class="text-ink-muted" title="This Veteran was saved with no tags.">
                                    N/A
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row" class="px-3 py-2 text-left font-medium text-ink">Notes</th>
                            <td v-for="column in columns" :key="column.run_id" class="px-3 py-2 text-ink">
                                <span v-if="column.notes">{{ column.notes }}</span>
                                <span v-else class="text-ink-muted" title="No note was written for this Veteran.">
                                    N/A
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p class="mt-3 text-xs text-ink-muted">
                Spark counts are per kind, as recorded. Affinity is stored per run and is not priced by
                this tool, so no column carries a total or a rank.
            </p>
        </template>
    </AppLayout>
</template>
