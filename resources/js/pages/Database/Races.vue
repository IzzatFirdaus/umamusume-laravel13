<script setup lang="ts">
/*
 * The race database (SCREEN-023, plan §8 D17). Every slot of the shared [Global] career calendar, read
 * straight off `race_catalog_slots` and paginated server-side.
 *
 * The calendar is the one reference area that can genuinely be empty: nothing seeds it and no seeder
 * touches it, so a fresh database shows the offline state below. The state names what is missing and
 * what fills it, and the table renders no placeholder race, because an invented row in a reference
 * table is a lie the whole career workflow would build on.
 *
 * Layout (`database-races.html`): one card holding the six-column table with the pager inside it, the
 * filter row above, and the wireframe's two reference cards below. Density is the Database area's
 * recorded exemption (plan §13, "Deliberately traded"), so the table uses the house `.t` grammar.
 *
 * HFW-OVERRIDE (column: course) - the wireframe's second column is Course, and this build stores only
 * the source's numeric `track_id` with no name mapping anywhere, so a Course column would print an id.
 * The drawing has no Year, Turn or obligation column either: those fold into the Race cell's second
 * line, because the calendar holds 410 rows under 304 titles and a row without its position is
 * indistinguishable from its sibling.
 */
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AbsenceValue from '../../components/AbsenceValue.vue';
import BaseButton from '../../components/Form/BaseButton.vue';
import BaseInput from '../../components/Form/BaseInput.vue';
import BaseSelect from '../../components/Form/BaseSelect.vue';
import DatabaseNav from '../../components/database/DatabaseNav.vue';
import SourceNote from '../../components/database/SourceNote.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import Pagination from '../../components/ui/Pagination.vue';
import AppLayout from '../../layouts/AppLayout.vue';

type RaceSort = 'name' | 'grade' | 'distance' | 'fans';

interface Slot {
    id: number;
    title: string;
    year_label: string;
    turn: number | null;
    tier: string | null;
    distance: number | null;
    distance_band: string | null;
    surface: string | null;
    fans_needed: number | null;
    is_mandatory: boolean;
    is_special_race: boolean;
}

interface Paginator<T> {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<{
    slots: Paginator<Slot>;
    totalCount: number;
    /** Which transcribed dataset the payout card's figures came from, and when the guide was read. */
    source: { section: string; anchor: string };
    recheck: { text: string; url: string };
    /** The facets the request carried, echoed so each picker paints what the Trainer chose. */
    filters: {
        q: string | null;
        grade: string | null;
        surface: string | null;
        distance: string | null;
        /** The ordering token, or `null` for the calendar's own order. */
        sort: RaceSort | 'calendar' | null;
        direction: 'asc' | 'desc' | null;
    };
}>();

/**
 * The filter row the wireframe draws over this table (`database-races.html:72-77`), reading only
 * values the table stores: the search matches the race title, and the three selects read `tier`,
 * `surface` and `distance_band`. Clear returns to the un-narrowed table.
 */
const q = ref(props.filters.q ?? '');
const grade = ref(props.filters.grade ?? '');
const surface = ref(props.filters.surface ?? '');
const distance = ref(props.filters.distance ?? '');
const loading = ref(false);
const raceReference = ref<HTMLDetailsElement | null>(null);

const columns: { label: string; sort?: RaceSort; numeric?: boolean }[] = [
    { label: 'Race', sort: 'name' },
    { label: 'Grade', sort: 'grade' },
    { label: 'Surface' },
    { label: 'Distance', sort: 'distance', numeric: true },
    { label: 'Band' },
    { label: 'Fans', sort: 'fans', numeric: true },
];

const ariaSort = (key?: RaceSort): 'ascending' | 'descending' | undefined => {
    if (!key || props.filters.sort !== key) {
        return undefined;
    }

    return props.filters.direction === 'desc' ? 'descending' : 'ascending';
};

const orderLabel = computed(() => {
    const column = columns.find(column => column.sort === props.filters.sort);
    return column === undefined ? 'Calendar order' : `${column.label} ${ariaSort(column.sort)}`;
});

/** Every stored tier is selectable: the database holds seven, the drawing lists four. */
const gradeOptions = ['G1', 'G2', 'G3', 'OP', 'Pre-OP', 'Maiden', 'Debut'];
const surfaceOptions = ['Turf', 'Dirt'];
const distanceOptions = ['Sprint', 'Mile', 'Medium', 'Long'];

/**
 * `BaseSelect` answers the empty choice with `null`, which the native select paints as no selection
 * at all. The facets are strings, where '' is the "Any" option's own value, so each update lands back
 * on that string and the picker keeps painting the choice the request carried.
 */
const facet = (value: string | number | null): string => (value === null ? '' : String(value));

function visit(query: Record<string, string | number | undefined>): void {
    router.get('/database/races', query, {
        preserveState: true,
        preserveScroll: true,
        onStart: () => { loading.value = true; },
        onFinish: () => { loading.value = false; },
    });
}

function appliedQuery(): URLSearchParams {
    return new URLSearchParams(window.location.search);
}

function apply(): void {
    visit({
        q: q.value || undefined,
        grade: grade.value || undefined,
        surface: surface.value || undefined,
        distance: distance.value || undefined,
        sort: props.filters.sort ?? undefined,
        direction: props.filters.direction ?? undefined,
        pageSize: appliedQuery().get('pageSize') ?? undefined,
    });
}

function sortBy(key?: RaceSort): void {
    const query = appliedQuery();
    query.delete('page');
    query.delete('sort');
    query.delete('direction');

    if (key !== undefined) {
        query.set('sort', key);
        const descending = props.filters.sort === key
            ? props.filters.direction === 'asc'
            : key === 'distance' || key === 'fans';
        query.set('direction', descending ? 'desc' : 'asc');
    }

    visit(Object.fromEntries(query));
}

function openReference(): void {
    if (raceReference.value !== null) {
        raceReference.value.open = true;
    }
}

const gates = (slot: Slot): string[] =>
    [
        slot.is_mandatory ? 'Mandatory' : null,
        slot.is_special_race ? 'Special race' : null,
    ].filter((mark): mark is string => mark !== null);

/**
 * The race cell's second line: the calendar position and the obligation words the wireframe does not
 * spend columns on. An empty result prints no line, so a row with neither keeps the title alone.
 */
const slotLine = (slot: Slot): string =>
    [slot.year_label, slot.turn === null ? null : `turn ${slot.turn}`, ...gates(slot)]
        .filter((part): part is string => part !== null)
        .join(' · ');

/** The wireframe prints its mono figures grouped ("2,000 m"), not as raw digits. */
const group = (value: number): string => value.toLocaleString('en-US');

function goToPage(page: number): void {
    const query = appliedQuery();
    query.set('page', String(page));
    visit(Object.fromEntries(query));
}
</script>

<template>
    <AppLayout :breadcrumb="[{ label: 'Database', href: '/database' }, { label: 'Races' }]">
        <Head title="Races" />
        <template #head>
            <PageHeader
                eyebrow="Races"
                title="Race catalog"
                description="Search by race name and filter by grade, surface or distance band."
            />
        </template>

        <DatabaseNav />

        <!--
            The wireframe's filter row (`database-races.html:72-77`). Every control reads a column
            this table stores, so a facet here narrows the same rows the table prints rather than
            becoming a second surface over them. The pending state is the sibling catalogs' own
            (ADR-0007): the request is a user-initiated async action.
        -->
        <form
            v-if="props.totalCount > 0"
            class="mt-4 flex flex-wrap items-end gap-3 rounded-md border border-rule-soft bg-panel p-3"
            :aria-busy="loading"
            @submit.prevent="apply"
        >
            <label class="flex min-w-0 flex-1 basis-56 flex-col gap-1 text-xs font-semibold text-ink-muted">
                Search
                <BaseInput
                    id="race-q"
                    v-model="q"
                    type="search"
                    name="q"
                    autocorrect="off"
                    autocapitalize="none"
                    :spellcheck="false"
                    enterkeyhint="search"
                    placeholder="Race name"
                />
            </label>
            <label class="flex flex-col gap-1 text-xs font-semibold text-ink-muted">
                Grade
                <BaseSelect
                    id="race-grade"
                    :model-value="grade"
                    name="grade"
                    :options="[{ value: '', label: 'Any' }, ...gradeOptions.map((option) => ({ value: option, label: option }))]"
                    @update:model-value="grade = facet($event)"
                />
            </label>
            <label class="flex flex-col gap-1 text-xs font-semibold text-ink-muted">
                Surface
                <BaseSelect
                    id="race-surface"
                    :model-value="surface"
                    name="surface"
                    :options="[{ value: '', label: 'Any' }, ...surfaceOptions.map((option) => ({ value: option, label: option }))]"
                    @update:model-value="surface = facet($event)"
                />
            </label>
            <label class="flex flex-col gap-1 text-xs font-semibold text-ink-muted">
                Distance
                <BaseSelect
                    id="race-distance"
                    :model-value="distance"
                    name="distance"
                    :options="[{ value: '', label: 'Any' }, ...distanceOptions.map((option) => ({ value: option, label: option }))]"
                    @update:model-value="distance = facet($event)"
                />
            </label>
            <BaseButton type="submit" variant="primary" size="md" :loading="loading">
                Filter
            </BaseButton>
            <a
                href="/database/races"
                class="inline-flex min-h-11 items-center px-2 text-sm text-ink-muted hover:underline transition-colors duration-150 ease-out"
            >Clear</a>
            <span v-if="props.slots.total === 0" class="basis-full text-sm text-ink-muted">
                No race matches these facets. Clear returns the full calendar.
            </span>
        </form>

        <div v-if="props.totalCount > 0" class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1">
            <p role="status" aria-atomic="true" class="text-sm text-ink-muted">
                <template v-if="loading">Loading results…</template>
                <template v-else>{{ props.slots.total }} matching slots · {{ orderLabel }}</template>
            </p>
            <BaseButton v-if="props.filters.direction !== null" variant="ghost" :disabled="loading" @click="sortBy()">
                Restore calendar order
            </BaseButton>
        </div>

        <section
            v-if="props.totalCount === 0"
            aria-labelledby="races-offline"
            class="mt-8 rounded-md border border-dashed border-rule bg-panel p-6"
        >
            <h2 id="races-offline" class="text-lg font-semibold text-ink-strong">
                The race database is offline
            </h2>

            <p class="mt-2 text-sm text-ink-muted">
                No rows have been fetched from the race calendar source, and the calendar is what the
                run's race screen, the Race Decision screen and the race record all read from. Without it
                there is no turn number, no distance, no surface and no fan gate to plan against.
            </p>

            <p class="mt-2 text-sm text-ink">
                Run <code class="font-mono text-xs">php artisan uma:fetch gametora-race-catalog</code> to
                fill it, or <code class="font-mono text-xs">php artisan uma:reparse gametora-race-catalog</code>
                if a fetch says the document is unchanged.
            </p>
        </section>

        <template v-else>
            <div class="card mt-4">
                <div
                    class="relative overflow-x-auto scroll-p-2"
                    role="region"
                    tabindex="0"
                    aria-label="Race slots, scrollable"
                >
                    <table class="t min-w-[40rem]">
                        <caption class="sr-only">
                            Career race slots. Activate a sortable column header to change the order.
                        </caption>
                        <thead>
                            <tr>
                                <th
                                    v-for="column in columns"
                                    :key="column.label"
                                    scope="col"
                                    :class="{ n: column.numeric }"
                                    :aria-sort="ariaSort(column.sort)"
                                >
                                    <!-- No glyph in the header cell. Direction is stated twice without
                                         it: `aria-sort` for a screen reader, and the `orderLabel` line
                                         above the table ("Distance descending") for everyone. A glyph
                                         here would break `database.spec.ts:402`, which pins every
                                         columnheader's text to the six column labels exactly. -->
                                    <button
                                        v-if="column.sort"
                                        type="button"
                                        class="inline-flex min-h-11 min-w-11 items-center gap-1 rounded-sm px-1 hover:bg-sunken hover:text-ink-strong"
                                        :aria-disabled="loading ? 'true' : undefined"
                                        @click="!loading && sortBy(column.sort)"
                                    >
                                        {{ column.label }}
                                    </button>
                                    <template v-else>{{ column.label }}</template>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="slot in props.slots.data" :key="slot.id">
                                <th scope="row" class="font-semibold text-ink-strong">
                                    <Link :href="`/database/races/${slot.id}`" class="inline-flex min-h-11 items-center text-ink-strong underline">
                                        {{ slot.title }}
                                    </Link>
                                    <span
                                        v-if="slotLine(slot) !== ''"
                                        class="block text-xs font-normal text-ink-muted"
                                    >{{ slotLine(slot) }}</span>
                                </th>
                                <td>
                                    <AbsenceValue v-if="slot.tier === null" compact reason="Not recorded" />
                                    <span v-else>{{ slot.tier }}</span>
                                </td>
                                <td>
                                    <AbsenceValue v-if="slot.surface === null" compact reason="Not recorded" />
                                    <span v-else>{{ slot.surface }}</span>
                                </td>
                                <td class="n">
                                    <AbsenceValue v-if="slot.distance === null" compact reason="Not recorded" />
                                    <span v-else class="font-mono tabular-nums">{{ group(slot.distance) }} m</span>
                                </td>
                                <td>
                                    <AbsenceValue v-if="slot.distance_band === null" compact reason="Not recorded" />
                                    <span v-else>{{ slot.distance_band }}<template v-if="slot.distance === 1400">*</template></span>
                                    <a
                                        v-if="slot.distance === 1400"
                                        href="#race-reference"
                                        class="flex min-h-11 items-center text-xs text-ink underline"
                                        @click="openReference"
                                    >Source conflict</a>
                                </td>
                                <td class="n">
                                    <AbsenceValue v-if="slot.fans_needed === null" compact reason="Not recorded" />
                                    <span v-else class="font-mono tabular-nums">{{ group(slot.fans_needed) }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination
                    :page="props.slots.current_page"
                    :pages="props.slots.last_page"
                    :from="props.slots.from ?? 0"
                    :to="props.slots.to ?? 0"
                    :total="props.slots.total"
                    noun="slots"
                    @navigate="goToPage"
                />
            </div>
        </template>

        <details ref="raceReference" class="mt-4 rounded-md border border-rule-soft bg-panel p-3">
            <summary id="race-reference" class="min-h-11 cursor-pointer py-2 text-lg font-semibold text-ink-strong">
                Race reference
            </summary>
            <p class="mt-2 text-sm text-ink-muted">
                <strong class="text-ink-strong">*1,400 m source conflict.</strong>
                The catalog cutoff places it in Sprint. The source export codes it as Mile
                (<code class="font-mono">distance_type==2</code>). N/A marks a field the source records nothing for.
            </p>
            <div class="mt-4 grid gap-4 g-2">
                <section class="card" aria-labelledby="race-bands">
                    <h2 id="race-bands">Distance bands</h2>
                    <table class="t">
                        <thead>
                            <tr>
                                <th scope="col">Band</th>
                                <th scope="col" class="n">Metres</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="row" class="font-medium">Sprint</th>
                                <td class="n font-mono tabular-nums">≤ 1400</td>
                            </tr>
                            <tr>
                                <th scope="row" class="font-medium">Mile</th>
                                <td class="n font-mono tabular-nums">1401-1800</td>
                            </tr>
                            <tr>
                                <th scope="row" class="font-medium">Medium</th>
                                <td class="n font-mono tabular-nums">1801-2400</td>
                            </tr>
                            <tr>
                                <th scope="row" class="font-medium">Long</th>
                                <td class="n font-mono tabular-nums">≥ 2401</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="note mt-3">
                        <span class="g" aria-hidden="true">○</span>
                        <span class="flex-1">The 1,400 m boundary is disputed.</span>
                    </p>
                </section>

                <section class="card" aria-labelledby="race-payout">
                    <h2 id="race-payout">What a race pays</h2>
                    <table class="t">
                        <thead>
                            <tr>
                                <th scope="col">Grade</th>
                                <th scope="col" class="n">Stat</th>
                                <th scope="col" class="n">SP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="row" class="font-medium">G1</th>
                                <td class="n font-mono tabular-nums">+10</td>
                                <td class="n font-mono tabular-nums">+45</td>
                            </tr>
                            <tr>
                                <th scope="row" class="font-medium">G2 · G3</th>
                                <td class="n font-mono tabular-nums">+8</td>
                                <td class="n font-mono tabular-nums">+35</td>
                            </tr>
                            <tr>
                                <th scope="row" class="font-medium">OP · Pre-OP</th>
                                <td class="n font-mono tabular-nums">+5</td>
                                <td class="n font-mono tabular-nums">+30</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="note mt-3">
                        <span class="g" aria-hidden="true">○</span>
                        <span class="flex-1">
                            First place at 0 Race Bonus in the standard career families. <b>Trackblazer pays the
                            same stat gain at a lower SP figure</b> (G1 +35, G2-G3 +25, OP-Pre-OP +20). Second
                            and third are not published.
                        </span>
                    </p>
                    <!-- The figures above are not read from a table: they are transcribed, so the card says
                         which dataset they came from and when the guide was read, the same row every other
                         transcribed reference view carries. -->
                    <SourceNote
                        :section="props.source.section"
                        :anchor="props.source.anchor"
                        :recheck="props.recheck"
                    />
                </section>
            </div>
        </details>
    </AppLayout>
</template>
