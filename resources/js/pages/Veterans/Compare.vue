<script setup lang="ts">
/*
 * SCREEN-022, the Veteran comparison (`SCR-VET-004`, plan §8 D16). Up to six filed careers, one property
 * per row, `design-2.0` §46's aligned shape.
 *
 * **Why this screen exists beside the Legacy Lab's compare.** That one compares the *configurations* two
 * runs recorded and refuses a run with no Legacy read-back, because a column of absences looks compared and
 * is not. This one compares *careers*, and the career a Trainer most wants beside another is usually the one
 * they just filed and have built no Legacy on, so refusing it here would delete the subject of the screen.
 * The eligibility rule differs, so the selector does too; the row itself is still `VeteranRow`, the same shape
 * the library list and the detail screen print.
 *
 * **Recorded values only, and the rows the brief asks for that are not recorded are named, not scored.**
 * `SCREEN-022`'s correction row wants inheritance usefulness, a compatibility calculation and a factor
 * comparison. `ADR-0020` §3 keeps those out of the record screens and no table in this repository prices
 * them, so they appear under NOT IN THIS BUILD with their reason. A number in one of those rows would be an
 * invention, and the comparison's whole value is that the Trainer can read six real careers side by side.
 *
 * The selection lives in the query string, so the view is shareable and survives a reload with no client
 * state to rehydrate. The cap is the server's rule alone now: `VeteranCompareRequest` refuses a
 * seventh id, and the picker that used to warn about it here lives on the library instead.
 */
import AppLayout from '../../layouts/AppLayout.vue';
import AbsenceValue from '../../components/AbsenceValue.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import { veteranResultClause } from '../../domain/veteranNote';
import { Head } from '@inertiajs/vue3';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

interface Aptitude {
    key: string;
    label: string;
    letter: string | null;
}

interface Column {
    id: number;
    run_id: number;
    label: string;
    trainee: string;
    trainee_ja: string | null;
    scenario_label: string;
    status_label: string;
    saved_at: string | null;
    blue: number;
    pink: number;
    unique: number;
    tags: string[];
    notes: string | null;
    hasSelection: boolean;
    builder_url: string | null;
    veteran_url: string;
    career: { turns: number; energy: number | null; fans: number | null };
    stats: Record<string, number | null>;
    counts: { skills: number; races: number };
    aptitudes: Aptitude[];
}

const props = defineProps<{
    columns: Column[];
    selected: number[];
    library_url: string;
    empty: { message: string; library_url: string } | null;
    aptitude_axes: { key: string; label: string }[];
    notice: string;
    absences: { label: string; reason: string }[];
}>();

const hoveredColumn = ref<number | null>(null);

/**
 * The career the narrow-viewport pager is showing, an index into `orderedColumns`. At `lg` and up the
 * matrix renders whole and this is unused; below `lg` the table is clipped to the `Property` column plus
 * one career, and the pager moves which career that is.
 */
const activeCareer = ref(0);

/** The rows of the table, in the order a Trainer reads a career: who, where, how far, then the numbers. */
const statKeys = ['Speed', 'Stamina', 'Power', 'Guts', 'Wit'];

/**
 * One career's letter on one axis, read by key rather than by position. An index here would be a claim that
 * every career publishes the same axes in the same order, and a career whose source published nothing has an
 * empty list, so the position would silently shift every letter one row up.
 */
function letterFor(column: Column, key: string): string | null {
    return column.aptitudes.find((aptitude) => aptitude.key === key)?.letter ?? null;
}

/**
 * The columns in the order the Trainer ticked them. The order arrives in the query string, which the
 * library builds from its own checkboxes, and `VeteranCompareRequest::veteranIds()` returns the ids in
 * the order the URL named them — so this page holds no selection state of its own. It used to carry a
 * picker of its own, which meant choosing careers and reading the comparison shared one screen, and the
 * screen could not be linked to without also shipping a form.
 */
const orderedColumns = computed<Column[]>(() => {
    const order = new Map(props.selected.map((id, index) => [String(id), index]));

    return [...props.columns].sort(
        (a, b) =>
            (order.get(String(a.id)) ?? Number.MAX_SAFE_INTEGER) -
            (order.get(String(b.id)) ?? Number.MAX_SAFE_INTEGER),
    );
});

/** Every value the selected careers share, so the table can say so rather than repeating itself silently. */
const isEmptyDiff = computed<boolean>(() => {
    const columns = orderedColumns.value;

    if (columns.length < 2) {
        return false;
    }

    const first = columns[0];

    return columns.every(
        (column) =>
            column.scenario_label === first.scenario_label &&
            column.status_label === first.status_label &&
            column.career.turns === first.career.turns &&
            column.career.energy === first.career.energy &&
            column.career.fans === first.career.fans &&
            statKeys.every((key) => column.stats[key] === first.stats[key]) &&
            column.counts.skills === first.counts.skills &&
            column.counts.races === first.counts.races &&
            JSON.stringify(column.tags) === JSON.stringify(first.tags) &&
            column.notes === first.notes &&
            column.builder_url === first.builder_url,
    );
});

/*
 * The difference column (`veterans-compare.html`, keyboxes 1–4). One verdict per stored property:
 * `same` when every selected career agrees, a side-tagged gap when two numeric columns differ, and a
 * named count when text differs. Matching rows still print, so a reader can see the row was compared.
 * No aggregate winner is computed; the wireframe forbids a recommendation and ADR-0020 §3 keeps the
 * derived factor analysis out of the record screens.
 */
type Difference = { property: string; verdict: string };

function verdictOf(values: (string | number | null)[]): string {
    if (values.length < 2) {
        return 'single column';
    }

    const present = values.filter((value): value is string | number => value !== null && value !== '');

    if (present.length === 0) {
        return 'same';
    }

    if (present.length < values.length) {
        if (values.length === 2) {
            const only = values[0] !== null && values[0] !== ''
                ? 'A'
                : values[1] !== null && values[1] !== '' ? 'B' : null;

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

const pageTitle = computed((): string =>
    orderedColumns.value.length > 0
        ? [...new Set(orderedColumns.value.map((column) => column.trainee))].join(' · ')
        : 'Compare Veterans',
);

/**
 * The head band's sentence, with the real count: the wireframe draws three careers and prints "Three
 * records side by side", and a page that copied that word would state a number it did not measure.
 */
const comparisonLede = computed((): string => {
    const count = orderedColumns.value.length;

    return count === 0
        ? 'Choose careers to line up. Differences are marked so the eye goes to what changed, and matching rows still print.'
        : `${count} record${count === 1 ? '' : 's'} side by side. Differences are marked so the eye goes to what changed, and matching rows still print.`;
});

const differences = computed<Difference[]>(() => {
    const columns = orderedColumns.value;
    const of = (read: (column: Column) => string | number | null): (string | number | null)[] =>
        columns.map(read);

    return [
        { property: 'Scenario', verdict: verdictOf(of((column) => column.scenario_label)) },
        // The day each career was filed, straight from the library row's own timestamp.
        { property: 'Saved', verdict: verdictOf(of((column) => column.saved_at)) },
        // The recorded Spark set, counted per career. These are the rows the wireframe tints: they are
        // the comparison a Trainer actually reads. A career with no Legacy read-back answers null, not
        // zero, because `VeteranRow::sparkCount` returns 0 for a payload that was never written and a
        // row of zeroes would claim a Spark set the career does not have.
        { property: 'Blue factor', verdict: verdictOf(of((column) => (column.hasSelection ? column.blue : null))) },
        { property: 'Pink factor', verdict: verdictOf(of((column) => (column.hasSelection ? column.pink : null))) },
        { property: 'Unique factor', verdict: verdictOf(of((column) => (column.hasSelection ? column.unique : null))) },
        {
            property: 'Total factor grades',
            verdict: verdictOf(of((column) => (column.hasSelection ? column.blue + column.pink + column.unique : null))),
        },
        { property: 'Status', verdict: verdictOf(of((column) => column.status_label)) },
        { property: 'Turns logged', verdict: verdictOf(of((column) => column.career.turns)) },
        { property: 'Energy', verdict: verdictOf(of((column) => column.career.energy)) },
        { property: 'Fans', verdict: verdictOf(of((column) => column.career.fans)) },
        ...statKeys.map((key) => ({ property: key, verdict: verdictOf(of((column) => column.stats[key])) })),
        { property: 'Skills recorded', verdict: verdictOf(of((column) => column.counts.skills)) },
        { property: 'Races recorded', verdict: verdictOf(of((column) => column.counts.races)) },
        ...props.aptitude_axes.map((axis) => ({
            property: axis.label,
            verdict: verdictOf(of((column) => column.aptitudes.find((aptitude) => aptitude.key === axis.key)?.letter ?? null)),
        })),
        { property: 'Tags', verdict: verdictOf(of((column) => column.tags.join(', '))) },
        // The matrix's last row, and the one this array did not cover. Keyed on the read-back's own
        // presence rather than a constant, so a mixed pair answers `only A records it` instead of
        // claiming `same` on a row where one career has a configuration and the other does not.
        {
            property: 'Legacy read-back',
            verdict: verdictOf(of((column) => (column.builder_url === null ? null : 'recorded'))),
        },
    ];
});

/*
 * The fold's seam, copied from `/legacy/compare` (S5): the column reads one lookup, and `differences`
 * stays the single derivation — nothing re-computes a verdict per cell.
 *
 * ponytail: keyed on the property label, so the matrix's row headers and this array's `property`
 * strings must agree. They do not yet — the matrix draws `Legacy read-back`, which this array has no
 * entry for, and this array carries `Notes`, which the matrix no longer draws. Reconcile the two sets
 * before wiring the column; a missing key must render an absence, never a third verdict.
 */
const verdictBy = computed<Record<string, string>>(() =>
    Object.fromEntries(differences.value.map((row) => [row.property, row.verdict])),
);

const verdictPill = (verdict: string): string =>
    verdict === 'same' ? 'pill pill-neutral' : 'pill pill-caution';

/** Keep the pager on a career that still exists after a tick or untick changes the column set. */
watch(orderedColumns, (columns) => {
    if (activeCareer.value >= columns.length) {
        activeCareer.value = 0;
    }
});

function stepCareer(delta: number): void {
    const count = orderedColumns.value.length;

    if (count === 0) {
        return;
    }

    activeCareer.value = (activeCareer.value + delta + count) % count;
}

/**
 * Below `lg` the matrix is clipped to the `Property` column and one career; every other career column is
 * hidden until the pager selects it. Above `lg` the `lg:table-cell` restores the whole matrix, so the
 * breakpoint alone decides the layout and the pager only matters under it.
 */
function careerColumnClass(index: number): string {
    return index === activeCareer.value ? '' : 'hidden lg:table-cell';
}

/*
 * Slice 8.20: a horizontal scrollbar that stays reachable on a tall table.
 *
 * The scroll region's own horizontal scrollbar sits at the bottom of it, and the region is as tall as
 * the matrix, so reaching that bar means scrolling to the end of the table - by which point the reader
 * has already lost the property labels the bar exists to help them find (Law 11 Continuity). The mirror
 * is a second, short horizontal scroller pinned to the bottom of the wrapper while the matrix is on
 * screen, so the affordance is reachable from any part of the table. It mirrors `scrollLeft` in both
 * directions, so either bar can drive the view. Both colours are existing tokens: `sunken` track,
 * `raised` thumb; no new colour and no new token.
 */
const regionEl = ref<HTMLElement | null>(null);
const mirrorEl = ref<HTMLElement | null>(null);
const tableEl = ref<HTMLTableElement | null>(null);

const tableWidth = ref(0);
const viewportWidth = ref(0);

/** The mirror is only rendered when the matrix actually overflows; a second empty bar is a second lie. */
const hasHorizontalOverflow = computed(() => tableWidth.value > viewportWidth.value + 1);

function measureScroller(): void {
    const region = regionEl.value;

    if (region === null) {
        return;
    }

    viewportWidth.value = region.clientWidth;
    tableWidth.value = tableEl.value?.scrollWidth ?? 0;
}

function syncFromRegion(): void {
    const region = regionEl.value;
    const mirror = mirrorEl.value;

    if (region !== null && mirror !== null && mirror.scrollLeft !== region.scrollLeft) {
        mirror.scrollLeft = region.scrollLeft;
    }
}

function syncFromMirror(): void {
    const region = regionEl.value;
    const mirror = mirrorEl.value;

    if (region !== null && mirror !== null && region.scrollLeft !== mirror.scrollLeft) {
        region.scrollLeft = mirror.scrollLeft;
    }
}

onMounted(() => {
    measureScroller();
    window.addEventListener('resize', measureScroller);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', measureScroller);
});

// Re-measure when the column set changes: a career added or removed is the other reason the matrix
// crosses the viewport width, and the mirror has to appear and size with it.
watch(orderedColumns, () => {
    nextTick(measureScroller);
});
</script>

<template>
    <AppLayout>
        <Head title="Compare Veterans" />
        <!-- The wireframe titles the comparison with the selected careers, not with the screen's
             function ("Vodka · Daiwa Scarlet · Maruzensky"); the picker state keeps the function. -->
        <template #who>
            <!-- The `#who` slot contract (AppLayout, S8): exactly one pill, and it states the fact this
                 page is about. The wireframe prints "3 selected" with three careers drawn; this prints the
                 count the query actually carries, and renders nothing when the picker is empty. -->
            <span
                v-if="orderedColumns.length > 0"
                class="inline-flex shrink-0 items-center rounded-pill bg-panel px-2.5 py-1 text-xs font-extrabold text-ink"
                data-shell-selected-pill
            >{{ orderedColumns.length }} selected</span>
        </template>

        <PageHeader eyebrow="Veteran comparison" :title="pageTitle" :description="comparisonLede">
            <template #actions>
                <a
                    :href="props.library_url"
                    class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-sm font-medium text-ink transition-colors duration-150 ease-out hover:bg-sunken"
                >
                    Back to library
                </a>
            </template>
        </PageHeader>

        <section
            v-if="props.empty !== null"
            aria-labelledby="compare-empty"
            class="mt-4 rounded-md border border-rule bg-panel p-4"
        >
            <h2 id="compare-empty" class="text-lg font-semibold text-ink-strong">Nothing selected</h2>
            <p class="mt-1 text-sm text-ink">{{ props.empty.message }}</p>
            <a
                :href="props.empty.library_url"
                class="mt-3 inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-sm text-ink hover:bg-sunken transition-colors duration-150 ease-out"
            >
                Open the library
            </a>
        </section>

        <section v-else aria-labelledby="compare-table" class="mt-4">
            <h2 id="compare-table" class="text-lg font-semibold text-ink-strong">
                {{ orderedColumns.length }} career{{ orderedColumns.length === 1 ? '' : 's' }} side by side
            </h2>

            <p v-if="isEmptyDiff" class="mt-2 text-sm text-ink-muted" role="note">
                All selected careers share every value; nothing differs.
            </p>

            <!-- Below `lg` the matrix is clipped to the `Property` column and one career, so a pager moves
                 which career that is rather than leaving the reader to scroll the table sideways. -->
            <div v-if="orderedColumns.length > 1" class="mt-2 flex flex-wrap items-center gap-3 text-sm lg:hidden">
                <button
                    type="button"
                    class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised transition-colors duration-150 ease-out"
                    @click="stepCareer(-1)"
                >
                    Previous career
                </button>
                <span aria-live="polite" class="text-ink-muted">
                    {{ orderedColumns[activeCareer]?.trainee }} ({{ activeCareer + 1 }} of {{ orderedColumns.length }})
                </span>
                <button
                    type="button"
                    class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised transition-colors duration-150 ease-out"
                    @click="stepCareer(1)"
                >
                    Next career
                </button>
            </div>

            <!-- §46: identical properties aligned vertically, in one scrollable region, never two cards the
                 Trainer has to hold in memory beside each other. The first column and the header row are
                 sticky so a property label never scrolls away from the value it names. -->
            <!-- The wrapper is the sticky context: it carries no overflow of its own, so the mirror bar
                 below can pin against the viewport. The region keeps `overflow-x-auto` and the region
                 contract (`role`, `tabindex`, `aria-label`) exactly as it was. -->
            <div class="comparison-wrap card mt-2">
                <div
                    ref="regionEl"
                    role="region"
                    tabindex="0"
                    aria-label="Career comparison, scrollable"
                    class="comparison-region overflow-x-auto"
                    @scroll="syncFromRegion"
                >
                <table ref="tableEl" class="comparison-matrix w-full min-w-max border-collapse text-sm lg:min-w-0">
                    <caption class="pb-2 text-left text-xs text-ink-muted">
                        Recorded values only. A blank cell means the career never recorded it, and its tooltip says which.
                    </caption>
                    <thead>
                        <tr class="border-b border-rule">
                            <th scope="col" class="comparison-corner p-2 text-left text-xs font-semibold uppercase text-ink-muted">
                                Property
                            </th>
                            <th
                                v-for="(column, index) in orderedColumns"
                                :key="`head-${column.id}`"
                                scope="col"
                                class="comparison-head p-2 text-left align-bottom"
                                :class="careerColumnClass(index)"
                                @mouseenter="hoveredColumn = index"
                                @mouseleave="hoveredColumn = null"
                            >
                                <a :href="column.veteran_url" class="font-semibold text-ink underline">{{ column.label }}</a>
                                <span v-if="column.trainee_ja" lang="ja" class="block text-xs text-ink-muted">{{ column.trainee_ja }}</span>
                                <span class="block text-xs text-ink-muted">{{ column.scenario_label }}</span>
                            </th>
                            <!-- The verdict is the matrix's LAST column (`veterans-compare.html:70-84`).
                                 Below `lg` it hides with the career columns it summarises: a verdict
                                 over values paged out of view would be a comparison the reader cannot
                                 check. -->
                            <th scope="col" class="hidden p-2 text-left align-bottom lg:table-cell">Difference</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-rule">
                            <th scope="row" class="comparison-row-head p-2 text-left font-medium text-ink-muted">Scenario</th>
                            <td
                                v-for="(column, index) in orderedColumns"
                                :key="`scenario-${column.id}`"
                                class="p-2 text-ink"
                                :class="[careerColumnClass(index), { 'comparison-col-hover': hoveredColumn === index }]"
                            >
                                {{ column.scenario_label }}
                            </td>
                            <td class="hidden p-2 lg:table-cell">
                                <span :class="verdictPill(verdictBy['Scenario'])">{{ verdictBy['Scenario'] }}</span>
                            </td>
                        </tr>
                        <tr class="border-b border-rule">
                            <th scope="row" class="comparison-row-head p-2 text-left font-medium text-ink-muted">Saved</th>
                            <td
                                v-for="(column, index) in orderedColumns"
                                :key="`saved-${column.id}`"
                                class="p-2 text-ink"
                                :class="[careerColumnClass(index), { 'comparison-col-hover': hoveredColumn === index }]"
                            >
                                {{ column.saved_at }}
                            </td>
                            <td class="hidden p-2 lg:table-cell">
                                <span :class="verdictPill(verdictBy['Saved'])">{{ verdictBy['Saved'] }}</span>
                            </td>
                        </tr>
                        <!-- The Spark rows. `veterans-compare.html:65-66` tints exactly these two, and the
                             tint is the wireframe's Von Restorff mark for the rows a Trainer scans first, so
                             it rides the row rather than a cell. Unique and Total stay untinted because the
                             mock leaves them in the plain band. -->
                        <tr class="border-b border-rule bg-sunken">
                            <th scope="row" class="comparison-row-head p-2 text-left font-semibold text-ink-strong">Blue factor</th>
                            <td
                                v-for="(column, index) in orderedColumns"
                                :key="`blue-${column.id}`"
                                class="p-2 text-ink"
                                :class="[careerColumnClass(index), { 'comparison-col-hover': hoveredColumn === index }]"
                            >
                                <template v-if="column.hasSelection">{{ column.blue }}</template>
                                <AbsenceValue v-else compact reason="No Legacy read-back on this career, so its Spark set was never recorded." />
                            </td>
                            <td class="hidden p-2 lg:table-cell">
                                <span :class="verdictPill(verdictBy['Blue factor'])">{{ verdictBy['Blue factor'] }}</span>
                            </td>
                        </tr>
                        <tr class="border-b border-rule bg-sunken">
                            <th scope="row" class="comparison-row-head p-2 text-left font-semibold text-ink-strong">Pink factor</th>
                            <td
                                v-for="(column, index) in orderedColumns"
                                :key="`pink-${column.id}`"
                                class="p-2 text-ink"
                                :class="[careerColumnClass(index), { 'comparison-col-hover': hoveredColumn === index }]"
                            >
                                <template v-if="column.hasSelection">{{ column.pink }}</template>
                                <AbsenceValue v-else compact reason="No Legacy read-back on this career, so its Spark set was never recorded." />
                            </td>
                            <td class="hidden p-2 lg:table-cell">
                                <span :class="verdictPill(verdictBy['Pink factor'])">{{ verdictBy['Pink factor'] }}</span>
                            </td>
                        </tr>
                        <tr class="border-b border-rule">
                            <th scope="row" class="comparison-row-head p-2 text-left font-medium text-ink-muted">Unique factor</th>
                            <td
                                v-for="(column, index) in orderedColumns"
                                :key="`unique-${column.id}`"
                                class="p-2 text-ink"
                                :class="[careerColumnClass(index), { 'comparison-col-hover': hoveredColumn === index }]"
                            >
                                <template v-if="column.hasSelection">{{ column.unique }}</template>
                                <AbsenceValue v-else compact reason="No Legacy read-back on this career, so its Spark set was never recorded." />
                            </td>
                            <td class="hidden p-2 lg:table-cell">
                                <span :class="verdictPill(verdictBy['Unique factor'])">{{ verdictBy['Unique factor'] }}</span>
                            </td>
                        </tr>
                        <tr class="border-b border-rule">
                            <th scope="row" class="comparison-row-head p-2 text-left font-medium text-ink-muted">Total factor grades</th>
                            <td
                                v-for="(column, index) in orderedColumns"
                                :key="`total-${column.id}`"
                                class="p-2 text-ink"
                                :class="[careerColumnClass(index), { 'comparison-col-hover': hoveredColumn === index }]"
                            >
                                <template v-if="column.hasSelection">{{ column.blue + column.pink + column.unique }}</template>
                                <AbsenceValue v-else compact reason="No Legacy read-back on this career, so its Spark set was never recorded." />
                            </td>
                            <td class="hidden p-2 lg:table-cell">
                                <span :class="verdictPill(verdictBy['Total factor grades'])">{{ verdictBy['Total factor grades'] }}</span>
                            </td>
                        </tr>
                        <tr class="border-b border-rule">
                            <th scope="row" class="comparison-row-head p-2 text-left font-medium text-ink-muted">Status</th>
                            <td
                                v-for="(column, index) in orderedColumns"
                                :key="`status-${column.id}`"
                                class="p-2 text-ink"
                                :class="[careerColumnClass(index), { 'comparison-col-hover': hoveredColumn === index }]"
                            >
                                {{ column.status_label }}
                            </td>
                            <td class="hidden p-2 lg:table-cell">
                                <span :class="verdictPill(verdictBy['Status'])">{{ verdictBy['Status'] }}</span>
                            </td>
                        </tr>
                        <tr class="border-b border-rule">
                            <th scope="row" class="comparison-row-head p-2 text-left font-medium text-ink-muted">Turns logged</th>
                            <td
                                v-for="(column, index) in orderedColumns"
                                :key="`turns-${column.id}`"
                                class="p-2 text-ink"
                                :class="[careerColumnClass(index), { 'comparison-col-hover': hoveredColumn === index }]"
                            >
                                {{ column.career.turns }}
                            </td>
                            <td class="hidden p-2 lg:table-cell">
                                <span :class="verdictPill(verdictBy['Turns logged'])">{{ verdictBy['Turns logged'] }}</span>
                            </td>
                        </tr>
                        <tr class="border-b border-rule">
                            <th scope="row" class="comparison-row-head p-2 text-left font-medium text-ink-muted">Energy</th>
                            <td
                                v-for="(column, index) in orderedColumns"
                                :key="`energy-${column.id}`"
                                class="p-2 text-ink"
                                :class="[careerColumnClass(index), { 'comparison-col-hover': hoveredColumn === index }]"
                            >
                                <template v-if="column.career.energy !== null">{{ column.career.energy }}</template>
                                <AbsenceValue v-else compact reason="No logged turn on this career carries an end-of-turn Energy figure." />
                            </td>
                            <td class="hidden p-2 lg:table-cell">
                                <span :class="verdictPill(verdictBy['Energy'])">{{ verdictBy['Energy'] }}</span>
                            </td>
                        </tr>
                        <tr class="border-b border-rule">
                            <th scope="row" class="comparison-row-head p-2 text-left font-medium text-ink-muted">Fans</th>
                            <td
                                v-for="(column, index) in orderedColumns"
                                :key="`fans-${column.id}`"
                                class="p-2 text-ink"
                                :class="[careerColumnClass(index), { 'comparison-col-hover': hoveredColumn === index }]"
                            >
                                <template v-if="column.career.fans !== null">{{ column.career.fans }}</template>
                                <AbsenceValue v-else compact reason="No logged turn on this career carries an end-of-turn Fans figure." />
                            </td>
                            <td class="hidden p-2 lg:table-cell">
                                <span :class="verdictPill(verdictBy['Fans'])">{{ verdictBy['Fans'] }}</span>
                            </td>
                        </tr>

                        <tr v-for="key in statKeys" :key="`stat-${key}`" class="border-b border-rule">
                            <th scope="row" class="comparison-row-head p-2 text-left font-medium text-ink-muted">{{ key }}</th>
                            <td
                                v-for="(column, index) in orderedColumns"
                                :key="`stat-${key}-${column.id}`"
                                class="p-2 text-ink"
                                :class="[careerColumnClass(index), { 'comparison-col-hover': hoveredColumn === index }]"
                            >
                                <template v-if="column.stats[key] !== null">{{ column.stats[key] }}</template>
                                <AbsenceValue v-else compact reason="This career logged no turn, so it recorded no value for this stat." />
                            </td>
                            <td class="hidden p-2 lg:table-cell">
                                <span :class="verdictPill(verdictBy[key])">{{ verdictBy[key] }}</span>
                            </td>
                        </tr>

                        <tr class="border-b border-rule">
                            <!-- Counted, not filtered: the pivot holds Suggested and Skipped rows and
                                 `race_entries` holds NotOffered and Skipped rows, so these are the records a
                                 career carries rather than the skills it earned or the races it ran. See
                                 `SCR-CAR-018`, which rules a skipped race is not a race the career ran. -->
                            <th scope="row" class="comparison-row-head p-2 text-left font-medium text-ink-muted">Skills recorded</th>
                            <td
                                v-for="(column, index) in orderedColumns"
                                :key="`skills-${column.id}`"
                                class="p-2 text-ink"
                                :class="[careerColumnClass(index), { 'comparison-col-hover': hoveredColumn === index }]"
                            >
                                {{ column.counts.skills }}
                            </td>
                            <td class="hidden p-2 lg:table-cell">
                                <span :class="verdictPill(verdictBy['Skills recorded'])">{{ verdictBy['Skills recorded'] }}</span>
                            </td>
                        </tr>
                        <tr class="border-b border-rule">
                            <th scope="row" class="comparison-row-head p-2 text-left font-medium text-ink-muted">Races recorded</th>
                            <td
                                v-for="(column, index) in orderedColumns"
                                :key="`races-${column.id}`"
                                class="p-2 text-ink"
                                :class="[careerColumnClass(index), { 'comparison-col-hover': hoveredColumn === index }]"
                            >
                                {{ column.counts.races }}
                            </td>
                            <td class="hidden p-2 lg:table-cell">
                                <span :class="verdictPill(verdictBy['Races recorded'])">{{ verdictBy['Races recorded'] }}</span>
                            </td>
                        </tr>

                        <tr v-for="axis in props.aptitude_axes" :key="`apt-${axis.key}`" class="border-b border-rule">
                            <th scope="row" class="comparison-row-head p-2 text-left font-medium text-ink-muted">{{ axis.label }}</th>
                            <td
                                v-for="(column, index) in orderedColumns"
                                :key="`apt-${axis.key}-${column.id}`"
                                class="p-2 font-mono text-ink"
                                :class="[careerColumnClass(index), { 'comparison-col-hover': hoveredColumn === index }]"
                            >
                                <template v-if="letterFor(column, axis.key) !== null">{{ letterFor(column, axis.key) }}</template>
                                <AbsenceValue v-else compact reason="No source published this trainee's aptitude letter on this axis." />
                            </td>
                            <td class="hidden p-2 lg:table-cell">
                                <span :class="verdictPill(verdictBy[axis.label])">{{ verdictBy[axis.label] }}</span>
                            </td>
                        </tr>

                        <tr class="border-b border-rule">
                            <th scope="row" class="comparison-row-head p-2 text-left font-medium text-ink-muted">Tags</th>
                            <td
                                v-for="(column, index) in orderedColumns"
                                :key="`tags-${column.id}`"
                                class="p-2"
                                :class="[careerColumnClass(index), { 'comparison-col-hover': hoveredColumn === index }]"
                            >
                                <ul v-if="column.tags.length > 0" class="flex flex-wrap gap-1">
                                    <li v-for="tag in column.tags" :key="`${column.id}-${tag}`" class="rounded-full border border-rule bg-sunken px-2 text-xs text-ink">{{ tag }}</li>
                                </ul>
                                <span v-else class="text-xs text-ink-muted">No tags recorded</span>
                            </td>
                            <td class="hidden p-2 lg:table-cell">
                                <span :class="verdictPill(verdictBy['Tags'])">{{ verdictBy['Tags'] }}</span>
                            </td>
                        </tr>
                        <tr class="border-b border-rule">
                            <th scope="row" class="comparison-row-head p-2 text-left font-medium text-ink-muted">Legacy read-back</th>
                            <td
                                v-for="(column, index) in orderedColumns"
                                :key="`legacy-${column.id}`"
                                class="p-2 text-ink"
                                :class="[careerColumnClass(index), { 'comparison-col-hover': hoveredColumn === index }]"
                            >
                                <a v-if="column.builder_url" :href="column.builder_url" class="underline">Open its graph</a>
                                <span v-else>None recorded <span class="text-xs text-ink-muted">· no Legacy configuration on this career</span></span>
                            </td>
                            <td class="hidden p-2 lg:table-cell">
                                <span :class="verdictPill(verdictBy['Legacy read-back'])">{{ verdictBy['Legacy read-back'] }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>

                <!-- Slice 8.20: the horizontal affordance, pinned so it never requires reaching the end
                     of a tall table. It renders only while the matrix overflows, and either bar can
                     drive the view: `scrollLeft` is mirrored in both directions. -->
                <div
                    v-if="hasHorizontalOverflow"
                    ref="mirrorEl"
                    class="comparison-scroll-mirror"
                    @scroll="syncFromMirror"
                >
                    <div class="comparison-scroll-mirror-fill" :style="{ width: `${tableWidth}px` }" />
                </div>
            </div>

            <!-- The note below the matrix, where the wireframe puts its one prose paragraph. It states
                 what the Spark rows cannot say, rather than copying the mock's illustrative sentence:
                 a count is not a stat, and no column in this build records which stat a Spark rides. -->
            <p v-if="orderedColumns.length > 0" class="note mt-3 text-sm text-ink" role="note">
                The factor rows count the Sparks a career recorded; they do not say which stat each one
                rides, and no column in this build stores that. Two careers can agree on every count and
                still reinforce different stats.
            </p>

            <!-- The verdict is the matrix's LAST column (`veterans-compare.html:70-84`), so the separate
                 "Where they differ" card is gone. `differences` becomes a MATRIX-ALIGNED array: a verdict
                 for a row the matrix no longer draws (`Notes`) is dropped rather than given a cell. The
                 one reading with no cell of its own is this page-level note, kept because it is about the
                 whole comparison rather than any single row. -->
            <p v-if="isEmptyDiff" class="mt-3 text-sm text-ink" role="note">
                Nothing decisive: all selected careers share every value.
            </p>

            <!-- `Note` moved out of the table: a long-form paragraph does not belong in a comparison cell
                 where it cannot wrap and reads as the same text repeated once per column. Each selected
                 career gets its own note card below the matrix, in the same column order. -->
            <div v-if="orderedColumns.length > 0" class="mt-4 grid gap-4 g-3">
                <section
                    v-for="column in orderedColumns"
                    :key="`note-${column.id}`"
                    class="card text-sm"
                >
                    <h2 class="mb-1 flex flex-wrap items-baseline gap-2">
                        <a :href="column.veteran_url" class="font-semibold text-ink-strong hover:underline transition-colors duration-150 ease-out">{{ column.label }}</a>
                        <span v-if="column.trainee_ja" lang="ja" class="text-xs text-ink-muted">{{ column.trainee_ja }}</span>
                    </h2>
                    <dl class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1 text-sm">
                        <dt class="text-ink-muted">Blue</dt>
                        <dd class="text-right font-medium text-ink">
                            <template v-if="column.hasSelection">{{ column.blue }}</template>
                            <AbsenceValue v-else compact reason="No Legacy read-back on this career, so its Spark set was never recorded." />
                        </dd>
                        <dt class="text-ink-muted">Pink</dt>
                        <dd class="text-right font-medium text-ink">
                            <template v-if="column.hasSelection">{{ column.pink }}</template>
                            <AbsenceValue v-else compact reason="No Legacy read-back on this career, so its Spark set was never recorded." />
                        </dd>
                        <!-- HFW-OVERRIDE (data: the Best-for value renders as a named absence; no source
                             publishes a recommendation rule and `ADR-0020` §3 keeps pricing a parent
                             combination out of the record screens, so the row exists and the verdict does
                             not). -->
                        <dt class="text-ink-muted">Best for</dt>
                        <dd class="text-right font-medium text-ink">
                            <AbsenceValue compact reason="Nothing decisive: no table in this build prices a parent combination, so no career is named better for a stat or an aptitude." />
                        </dd>
                    </dl>
                    <p v-if="veteranResultClause(column.notes) !== null" class="whitespace-pre-wrap text-ink">{{ veteranResultClause(column.notes) }}</p>
                    <p v-else class="text-xs text-ink-muted">No note recorded for this career.</p>
                </section>
            </div>
        </section>

        <section aria-labelledby="compare-absences" class="mt-4 rounded-md border border-rule bg-panel p-4">
            <h2 id="compare-absences" class="text-lg font-semibold text-ink-strong">NOT IN THIS BUILD</h2>
            <ul class="mt-2 space-y-2 text-sm">
                <li v-for="absence in props.absences" :key="absence.label">
                    <span class="text-ink">{{ absence.label }}</span>: {{ absence.reason }}
                </li>
            </ul>
        </section>
    </AppLayout>
</template>

<style scoped>
/*
 * The sticky matrix. The `Property` column and the header row must scroll with the reader, or a value
 * loses the label that names it. `--color-raised` is the opaque table surface in both themes: a
 * transparent sticky cell would let the data scroll behind the labels and read as visual noise.
 */
.comparison-corner,
.comparison-row-head {
    position: sticky;
    left: 0;
    background-color: var(--color-raised);
    width: 180px;
    min-width: 180px;
    max-width: 180px;
}

.comparison-corner {
    top: 0;
    z-index: 30;
}

.comparison-row-head {
    z-index: 20;
}

.comparison-head {
    position: sticky;
    top: 0;
    z-index: 10;
    background-color: var(--color-raised);
    min-width: 160px;
    max-width: 220px;
}

.comparison-matrix {
    background-color: var(--color-raised);
}

/*
 * Column highlight on header hover: a value can be traced to its career without reading the header on
 * every row. It reuses the `sunken` step, so no new colour enters the system.
 */
.comparison-col-hover {
    background-color: var(--color-sunken);
}

/*
 * Row highlight on body hover, the same idea on the other axis: the eye can follow one property across
 * every career. The sticky `Property` cell takes it too, or the row's own label would sit outside the
 * highlight it belongs to.
 */
tbody tr:hover > td,
tbody tr:hover > .comparison-row-head {
    background-color: var(--color-sunken);
}

/*
 * Slice 8.20. The wrapper carries no overflow, which is what lets the mirror pin against the viewport
 * while the matrix is on screen; inside it the mirror is a real horizontal scroller whose content is
 * the table's own width, so it reports the same proportion and position as the region's native bar.
 * Both colours are existing tokens: `sunken` is the track, `raised` the thumb.
 */
.comparison-scroll-mirror {
    position: sticky;
    bottom: 0;
    z-index: 40;
    height: 10px;
    margin-top: 2px;
    overflow-x: auto;
    overflow-y: hidden;
    background-color: var(--color-sunken);
}

.comparison-scroll-mirror-fill {
    height: 100%;
    background-color: var(--color-raised);
}
</style>
