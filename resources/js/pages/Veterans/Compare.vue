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
 * state to rehydrate. The cap is enforced in the picker as a message and on the server as the rule.
 */
import AppLayout from '../../layouts/AppLayout.vue';
import AbsenceValue from '../../components/AbsenceValue.vue';
import { BaseButton, BaseCheckbox } from '../../components/Form';
import { veteranResultClause } from '../../domain/veteranNote';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

interface Aptitude {
    key: string;
    label: string;
    letter: string | null;
}

interface Column {
    id: number;
    run_id: number;
    trainee: string;
    trainee_ja: string | null;
    scenario_label: string;
    status_label: string;
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
    max: number;
    comparable: { id: number; label: string }[];
    empty: { message: string; library_url: string } | null;
    aptitude_axes: { key: string; label: string }[];
    notice: string;
    absences: { label: string; reason: string }[];
}>();

const page = usePage();
const errors = computed(() => (page.props.errors as Record<string, string | undefined> | undefined) ?? {});

const picked = ref<string[]>(props.selected.map((id) => String(id)));
const loading = ref(false);
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

function isChecked(id: number): boolean {
    return picked.value.includes(String(id));
}

function toggle(id: number): void {
    const value = String(id);

    picked.value = isChecked(id)
        ? picked.value.filter((selected) => selected !== value)
        : // The cap is enforced here as a message, not as authority: a seventh tick does nothing rather than
          // posting a selection that comes back as a field error. `VeteranCompareRequest` still decides.
          picked.value.length >= props.max
            ? picked.value
            : [...picked.value, value];
}

function clearAll(): void {
    picked.value = [];
}

function compare(): void {
    router.get(
        '/veterans/compare',
        { veterans: picked.value },
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

/**
 * The columns in the order the Trainer ticked them, not the order the server found them in. The first
 * column is the first selected career; an unticked career drops out and a re-ticked one appends to the end.
 */
const orderedColumns = computed<Column[]>(() => {
    const order = new Map(picked.value.map((id, index) => [id, index]));

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
        <template #title>Compare Veterans</template>

        <p v-if="loading" role="status" class="mb-4 text-sm text-ink-muted">Loading…</p>

        <p
            role="note"
            class="mt-3 flex items-start gap-2 rounded-md border border-rule bg-sunken px-3 py-2 text-sm text-ink"
        >
            <span aria-hidden="true" class="font-mono font-bold text-ink-strong">i</span>
            <span>{{ props.notice }}</span>
        </p>

        <fieldset class="mt-4 rounded-md border border-rule bg-panel p-4">
            <legend class="text-lg font-semibold text-ink-strong">Choose up to {{ props.max }}</legend>
            <p id="picker-help" class="text-xs text-ink-muted">
                Only careers already filed in the library can be compared. A run that has not been saved has
                no row here to name.
            </p>

            <p v-if="props.comparable.length === 0" class="mt-2 text-sm text-ink" role="status">
                The library is empty, so there is nothing to choose. File a career from its Result screen
                first.
            </p>

            <div v-else class="mt-2">
                <p class="text-sm text-ink" role="status" aria-live="polite">
                    {{ picked.length }} of {{ props.max }} selected
                </p>

                <ul class="mt-2 grid gap-1 sm:grid-cols-2">
                    <li v-for="option in props.comparable" :key="option.id">
                        <!-- Boolean model mode, not the `modelValue === value` group mode: the group mode
                             has to agree on the id's type on both sides, and a mismatch renders every
                             picker row as already ticked, so a tick changes nothing and Compare submits
                             the one career the deep link landed on. Binding the boolean makes the state
                             the single source. -->
                        <BaseCheckbox
                            :model-value="isChecked(option.id)"
                            :value="option.id"
                            :id="`pick-${option.id}`"
                            :name="`pick-${option.id}`"
                            :label="option.label"
                            :disabled="!isChecked(option.id) && picked.length >= props.max"
                            @update:model-value="toggle(option.id)"
                        />
                    </li>
                </ul>
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-3">
                <BaseButton
                    type="button"
                    variant="primary"
                    :disabled="picked.length === 0"
                    @click="compare"
                >
                    Compare
                </BaseButton>
                <BaseButton
                    type="button"
                    variant="secondary"
                    :disabled="picked.length === 0"
                    @click="clearAll"
                >
                    Clear all
                </BaseButton>
                <p v-if="picked.length === 0" class="text-xs text-ink-muted">
                    Select at least one career to compare.
                </p>
                <p v-else-if="picked.length >= props.max" class="text-xs text-ink-muted">
                    Up to {{ props.max }} careers fit in this view. Select fewer to see each value at a wider column.
                </p>
            </div>

            <ul v-if="Object.keys(errors).length > 0" class="mt-2 space-y-1">
                <li
                    v-for="(message, field) in errors"
                    :key="field"
                    role="alert"
                    class="rounded-md border border-risk bg-raised px-3 py-1 text-sm text-risk"
                >
                    {{ field }}: {{ message }}
                </li>
            </ul>
        </fieldset>

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
            <div class="comparison-wrap mt-2">
                <div
                    ref="regionEl"
                    role="region"
                    tabindex="0"
                    aria-label="Career comparison, scrollable"
                    class="comparison-region overflow-x-auto"
                    @scroll="syncFromRegion"
                >
                <table ref="tableEl" class="comparison-matrix w-full min-w-max border-collapse text-sm">
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
                                <a :href="column.veteran_url" class="font-semibold text-ink underline">{{ column.trainee }}</a>
                                <span v-if="column.trainee_ja" lang="ja" class="block text-xs text-ink-muted">{{ column.trainee_ja }}</span>
                                <span class="block text-xs text-ink-muted">{{ column.scenario_label }}</span>
                            </th>
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
                                <span v-else class="text-xs text-ink-muted" title="The Trainer filed this career without tagging it.">No tags recorded</span>
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
                                <span v-else title="This career records no Legacy configuration, so it has no graph to open.">None recorded</span>
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

            <!-- `Note` moved out of the table: a long-form paragraph does not belong in a comparison cell
                 where it cannot wrap and reads as the same text repeated once per column. Each selected
                 career gets its own note card below the matrix, in the same column order. -->
            <div v-if="orderedColumns.length > 0" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <article
                    v-for="column in orderedColumns"
                    :key="`note-${column.id}`"
                    class="rounded-md border border-rule bg-raised p-3 text-sm"
                >
                    <header class="mb-1 flex flex-wrap items-baseline gap-2">
                        <a :href="column.veteran_url" class="font-semibold text-ink-strong hover:underline transition-colors duration-150 ease-out">{{ column.trainee }}</a>
                        <span v-if="column.trainee_ja" lang="ja" class="text-xs text-ink-muted">{{ column.trainee_ja }}</span>
                    </header>
                    <p v-if="veteranResultClause(column.notes) !== null" class="whitespace-pre-wrap text-ink">{{ veteranResultClause(column.notes) }}</p>
                    <p v-else class="text-xs text-ink-muted" title="No note was recorded on this career.">No note recorded for this career.</p>
                </article>
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
