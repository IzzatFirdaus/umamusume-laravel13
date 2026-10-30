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
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AbsenceValue from '../../components/AbsenceValue.vue';
import BaseButton from '../../components/Form/BaseButton.vue';
import BaseInput from '../../components/Form/BaseInput.vue';
import BaseSelect from '../../components/Form/BaseSelect.vue';
import DatabaseNav from '../../components/database/DatabaseNav.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import Pagination from '../../components/ui/Pagination.vue';
import AppLayout from '../../layouts/AppLayout.vue';

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
    /** The facets the request carried, echoed so each picker paints what the Trainer chose. */
    filters: {
        q: string | null;
        grade: string | null;
        surface: string | null;
        distance: string | null;
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

function apply(): void {
    router.get(
        window.location.pathname,
        {
            q: q.value || undefined,
            grade: grade.value || undefined,
            surface: surface.value || undefined,
            distance: distance.value || undefined,
        },
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

/**
 * The canonical pager owns the range, so the page owns the request. The pager it replaces was a set
 * of Inertia links built from the server's own paginator, and those links carried the query string
 * with them, so this rebuilds the same query with the new page number rather than replacing it.
 */
function goToPage(page: number): void {
    const query = new URLSearchParams(window.location.search);
    query.set('page', String(page));

    router.get(`${window.location.pathname}?${query.toString()}`, {}, {
        preserveState: true,
        preserveScroll: true,
    });
}
</script>

<template>
    <AppLayout :breadcrumb="[{ label: 'Database', href: '/database' }, { label: 'Races' }]">
        <Head title="Races" />
        <template #head>
            <PageHeader
                eyebrow="Races"
                title="Race catalog"
                description="Grade, surface, distance band and the fan threshold an obligation reads. Filter to the races a trainee's aptitude actually permits."
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
            class="mt-4 flex flex-wrap items-end gap-3"
            :aria-busy="loading ? 'true' : null"
            @submit.prevent="apply"
        >
            <label class="flex min-w-0 flex-1 basis-56 flex-col gap-1 text-xs font-semibold text-ink-muted">
                Search
                <BaseInput
                    id="race-q"
                    v-model="q"
                    type="search"
                    name="q"
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

        <p v-if="loading" role="status" class="mt-3 text-sm text-ink-muted">Loading results…</p>

        <section
            v-if="props.totalCount === 0"
            aria-labelledby="races-offline"
            class="mt-8 rounded-md border border-dashed border-rule bg-panel p-6"
        >
            <h3 id="races-offline" class="text-lg font-semibold text-ink-strong">
                The race database is offline
            </h3>

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
                    class="relative overflow-x-auto"
                    role="region"
                    tabindex="0"
                    aria-label="Race slots, scrollable"
                >
                    <table class="t min-w-[40rem]">
                        <caption class="sr-only">
                            Career race slots: race, grade, surface, distance, distance band and fan threshold
                        </caption>
                        <thead>
                            <tr>
                                <th scope="col">Race</th>
                                <th scope="col">Grade</th>
                                <th scope="col">Surface</th>
                                <th scope="col" class="n">Distance</th>
                                <th scope="col">Band</th>
                                <th scope="col" class="n">Fans</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="slot in props.slots.data" :key="slot.id">
                                <th scope="row" class="font-semibold text-ink-strong">
                                    <b>{{ slot.title }}</b>
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
                                <!-- Keybox 1: the asterisk marks the derived edge the sources dispute; the
                                     cell's aria-label carries the reason for a screen reader, because an
                                     absolutely positioned `sr-only` span inside the scroll region escapes its
                                     clip and widened the document at 320px (measured 88px). The `relative` on
                                     the scroll region now contains it, so AbsenceValue's sr-only twin is safe. -->
                                <td
                                    :aria-label="slot.distance === 1400
                                        ? `${slot.distance_band}, on the disputed 1400 m boundary; this catalogue's cut-off reads it as ${slot.distance_band} and the export reads it as Mile`
                                        : undefined"
                                >
                                    <AbsenceValue v-if="slot.distance_band === null" compact reason="Not recorded" />
                                    <span v-else>{{ slot.distance_band }}<template v-if="slot.distance === 1400">*</template></span>
                                </td>
                                <td class="n">
                                    <AbsenceValue v-if="slot.fans_needed === null" compact reason="Not recorded" />
                                    <span v-else class="font-mono tabular-nums">{{ group(slot.fans_needed) }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="note mt-3">
                    <span class="g" aria-hidden="true">○</span>
                    <span class="flex-1">
                        <b>*1400 m is an open conflict.</b> This catalogue's cut-off puts it in Sprint, while
                        the data export codes those courses as <span class="font-mono">distance_type==2</span>,
                        which reads as Mile, and the client's "(Mile)"-tagged skills gate on that type; the row
                        is marked and no winner is encoded. Band is derived from this row's Distance through
                        the catalogue's cut-offs below, so it is shown as derived, not as a stored measurement.
                        N/A marks a field the source records nothing for. It is not a zero, and no column here
                        fills a gap with a guess.
                    </span>
                </p>
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

        <!-- The wireframe's two reference cards (`database-races.html:102-132`). The bands are the
             cut-offs the parser and the Distance facet use; the payout is the per-grade first-place
             figure the reference guide carries for both career families. -->
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
                    <span class="flex-1">The 1400 m boundary is the source of the conflict noted above.</span>
                </p>
            </section>

            <section class="card" aria-labelledby="race-payout">
                <h2 id="race-payout">What a race pays</h2>
                <table class="t">
                    <thead>
                        <tr>
                            <th scope="col">Grade</th>
                            <th scope="col" class="n">Stat</th>
                            <th scope="col" class="n">Skill Pt</th>
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
            </section>
        </div>
    </AppLayout>
</template>
