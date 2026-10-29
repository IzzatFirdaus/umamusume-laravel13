<script setup lang="ts">
/*
 * The race database (SCREEN-023, plan §8 D17). Every slot of the shared [Global] career calendar,
 * read straight off `race_catalog_slots` and paginated server-side.
 *
 * The calendar is the second new area of this slice, and it is the one that can genuinely be empty:
 * unlike the three catalog areas, nothing seeds it and no seeder touches it, so a fresh database shows
 * the offline state below. The state names what is missing and what fills it, and the table renders no
 * placeholder race, because an invented row in a reference table is a lie the whole career workflow
 * would build on.
 *
 * Density is allowed on this screen only (plan §13, "Deliberately traded"): a reference table is dense
 * by nature. The table still scrolls inside its own container at 320px, never the page.
 *
 * HFW-OVERRIDE (column: course) - the wireframe's lead column is Course, and this build stores only
 * the source's numeric `track_id` with no name mapping anywhere, so a Course column would print an id.
 * The distance band the same design asks for *is* stored (`distance_band`) and prints beside Distance.
 */
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import DatabaseNav from '../../components/database/DatabaseNav.vue';
import Pagination from '../../components/ui/Pagination.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

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

const gradeOptions = ['G1', 'G2', 'G3', 'OP', 'Maiden', 'Debut'];
const surfaceOptions = ['Turf', 'Dirt'];
const distanceOptions = ['Sprint', 'Mile', 'Medium', 'Long'];

function apply(): void {
    router.get(
        window.location.pathname,
        {
            q: q.value || undefined,
            grade: grade.value || undefined,
            surface: surface.value || undefined,
            distance: distance.value || undefined,
        },
        { preserveState: true, preserveScroll: true },
    );
}

function clear(): void {
    q.value = '';
    grade.value = '';
    surface.value = '';
    distance.value = '';
    apply();
}

const gates = (slot: Slot): string[] =>
    [
        slot.is_mandatory ? 'Mandatory' : null,
        slot.is_special_race ? 'Special race' : null,
    ].filter((mark): mark is string => mark !== null);

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
            becoming a second surface over them.
        -->
        <form
            v-if="props.totalCount > 0"
            class="mt-4 flex flex-wrap items-end gap-3"
            @submit.prevent="apply"
        >
            <label class="field flex-1 basis-56">
                <span class="k">Search</span>
                <input
                    v-model="q"
                    name="q"
                    type="search"
                    placeholder="Race name"
                    class="min-h-11 w-full rounded-md border border-rule bg-panel px-3 text-sm text-ink"
                >
            </label>
            <label class="field basis-32">
                <span class="k">Grade</span>
                <select v-model="grade" name="grade" class="min-h-11 w-full rounded-md border border-rule bg-panel px-2 text-sm text-ink">
                    <option value="">Any</option>
                    <option v-for="option in gradeOptions" :key="option" :value="option">{{ option }}</option>
                </select>
            </label>
            <label class="field basis-32">
                <span class="k">Surface</span>
                <select v-model="surface" name="surface" class="min-h-11 w-full rounded-md border border-rule bg-panel px-2 text-sm text-ink">
                    <option value="">Any</option>
                    <option v-for="option in surfaceOptions" :key="option" :value="option">{{ option }}</option>
                </select>
            </label>
            <label class="field basis-36">
                <span class="k">Distance</span>
                <select v-model="distance" name="distance" class="min-h-11 w-full rounded-md border border-rule bg-panel px-2 text-sm text-ink">
                    <option value="">Any</option>
                    <option v-for="option in distanceOptions" :key="option" :value="option">{{ option }}</option>
                </select>
            </label>
            <button
                type="submit"
                class="inline-flex min-h-11 items-center rounded-md border border-rule bg-panel px-4 text-sm font-semibold text-ink"
            >
                Filter
            </button>
            <button
                type="button"
                class="inline-flex min-h-11 items-center rounded-md px-3 text-sm text-ink-muted underline"
                @click="clear"
            >
                Clear
            </button>
            <span v-if="props.slots.total === 0" class="basis-full text-sm text-ink-muted">
                No race matches these facets.
            </span>
        </form>

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
            <!-- The range and the total live with the pager, under the table: a table that holds one
                 page of rows cannot say "410 of 410" about itself without misleading the reader. -->

            <div
                 class="mt-6 overflow-x-auto rounded-md border border-rule bg-panel"
                role="region"
                tabindex="0"
                aria-label="Race slots, scrollable"
            >
                <table class="w-full min-w-[40rem] text-sm">
                    <caption class="sr-only">
                        Career race slots: race, year, turn, tier, distance, distance band, surface, fan gate and entry gate
                    </caption>
                    <thead>
                        <tr class="border-b border-rule bg-sunken text-left text-xs text-ink-muted">
                            <th scope="col" class="py-1.5 px-3 font-semibold">Race</th>
                            <th scope="col" class="py-1.5 px-3 font-semibold">Year</th>
                            <th scope="col" class="py-1.5 px-3 font-semibold">Turn</th>
                            <th scope="col" class="py-1.5 px-3 font-semibold">Tier</th>
                            <th scope="col" class="py-1.5 px-3 font-semibold">Distance</th>
                            <th scope="col" class="py-1.5 px-3 font-semibold">Band</th>
                            <th scope="col" class="py-1.5 px-3 font-semibold">Surface</th>
                            <th scope="col" class="py-1.5 px-3 font-semibold">Fans needed</th>
                            <th scope="col" class="py-1.5 px-3 font-semibold">Gate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-rule">
                        <tr v-for="slot in props.slots.data" :key="slot.id" class="align-top">
                            <th scope="row" class="py-1.5 px-3 text-left font-medium text-ink-strong">
                                {{ slot.title }}
                            </th>
                            <td class="py-1.5 px-3 text-ink">{{ slot.year_label }}</td>
                            <td class="py-1.5 px-3 text-ink">
                                <span v-if="slot.turn === null">N/A</span>
                                <span v-else>{{ slot.turn }}</span>
                            </td>
                            <td class="py-1.5 px-3 text-ink">
                                <span v-if="slot.tier === null">N/A</span>
                                <span v-else>{{ slot.tier }}</span>
                            </td>
                            <td class="py-1.5 px-3 text-ink">
                                <span v-if="slot.distance === null">N/A</span>
                                <span v-else>{{ slot.distance }} m</span>
                            </td>
                            <!-- Keybox 1: the asterisk marks the derived edge the sources dispute; the
                                 cell's aria-label carries the reason for a screen reader, because an
                                 absolutely positioned `sr-only` span inside the scroll region escapes its
                                 clip and widened the document at 320px (measured 88px). -->
                            <td
                                class="py-1.5 px-3 text-ink"
                                :aria-label="slot.distance === 1400
                                    ? `${slot.distance_band}, on the disputed 1400 m boundary; the export's band is printed`
                                    : undefined"
                            >
                                <span v-if="slot.distance_band === null">N/A</span>
                                <span v-else>{{ slot.distance_band }}<template v-if="slot.distance === 1400">*</template></span>
                            </td>
                            <td class="py-1.5 px-3 text-ink">
                                <span v-if="slot.surface === null">N/A</span>
                                <span v-else>{{ slot.surface }}</span>
                            </td>
                            <td class="py-1.5 px-3 text-ink">
                                <span v-if="slot.fans_needed === null">N/A</span>
                                <span v-else>{{ slot.fans_needed }}</span>
                            </td>
                            <td class="py-1.5 px-3">
                                <span v-if="gates(slot).length === 0">
                                    N/A <span class="text-xs text-ink-muted">· no entry gate</span>
                                </span>
                                <span v-else class="flex flex-wrap gap-1">
                                    <span
                                        v-for="mark in gates(slot)"
                                        :key="mark"
                                        class="rounded-full bg-sunken px-2 py-0.5 text-xs text-ink-strong"
                                    >
                                        {{ mark }}
                                    </span>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p class="mt-2 px-3 pb-2 text-xs text-ink-muted">
                    N/A marks a field the source records nothing for. It is not a zero, and no column
                    here fills a gap with a guess. A band marked * sits on the disputed 1400 m boundary.
                </p>
                <div class="px-3 pb-3">
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
            </div>
        </template>
        <!-- The wireframe's two reference cards (`database-races.html:103-127`). The years, turns and
             gates above stay as this build's own career-slot columns (the mock draws none), printed
             before these; the bands and the payout are published mechanics, so they are stated here. -->
        <div class="mt-4 grid gap-4 g-2">
            <section class="card" aria-labelledby="race-bands">
                <h2 id="race-bands">Distance bands</h2>
                <table class="mt-2 w-full border-collapse text-sm">
                    <thead>
                        <tr>
                            <th class="border-b border-rule px-2 py-2 text-left text-xs font-semibold text-ink-strong">Band</th>
                            <th class="border-b border-rule px-2 py-2 text-right text-xs font-semibold text-ink-strong">Metres</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-rule">
                            <th scope="row" class="py-2 pr-3 text-left font-medium text-ink">Sprint</th>
                            <td class="py-2 text-right font-mono tabular-nums text-ink">≤ 1400</td>
                        </tr>
                        <tr class="border-b border-rule">
                            <th scope="row" class="py-2 pr-3 text-left font-medium text-ink">Mile</th>
                            <td class="py-2 text-right font-mono tabular-nums text-ink">1401-1800</td>
                        </tr>
                        <tr class="border-b border-rule">
                            <th scope="row" class="py-2 pr-3 text-left font-medium text-ink">Medium</th>
                            <td class="py-2 text-right font-mono tabular-nums text-ink">1801-2400</td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-2 pr-3 text-left font-medium text-ink">Long</th>
                            <td class="py-2 text-right font-mono tabular-nums text-ink">≥ 2401</td>
                        </tr>
                    </tbody>
                </table>
                <p class="note mt-3">
                    <span class="g" aria-hidden="true">○</span>
                    <span>
                        The 1400 m boundary is an open conflict: one guide places it in Sprint, the export
                        files it under distance code 2, which reads as Mile. The catalogue prints the
                        export's band, marks a 1400 m row with *, and does not encode a winner.
                    </span>
                </p>
            </section>

            <section class="card" aria-labelledby="race-payout">
                <h2 id="race-payout">What a race pays</h2>
                <table class="mt-2 w-full border-collapse text-sm">
                    <thead>
                        <tr>
                            <th class="border-b border-rule px-2 py-2 text-left text-xs font-semibold text-ink-strong">Grade</th>
                            <th class="border-b border-rule px-2 py-2 text-right text-xs font-semibold text-ink-strong">Stat</th>
                            <th class="border-b border-rule px-2 py-2 text-right text-xs font-semibold text-ink-strong">Skill Pt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-rule">
                            <th scope="row" class="py-2 pr-3 text-left font-medium text-ink">G1</th>
                            <td class="py-2 text-right font-mono tabular-nums text-ink">+10</td>
                            <td class="py-2 text-right font-mono tabular-nums text-ink">+45</td>
                        </tr>
                        <tr class="border-b border-rule">
                            <th scope="row" class="py-2 pr-3 text-left font-medium text-ink">G2 · G3</th>
                            <td class="py-2 text-right font-mono tabular-nums text-ink">+8</td>
                            <td class="py-2 text-right font-mono tabular-nums text-ink">+35</td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-2 pr-3 text-left font-medium text-ink">OP · Pre-OP</th>
                            <td class="py-2 text-right font-mono tabular-nums text-ink">+5</td>
                            <td class="py-2 text-right font-mono tabular-nums text-ink">+30</td>
                        </tr>
                    </tbody>
                </table>
                <p class="note mt-3">
                    <span class="g" aria-hidden="true">○</span>
                    <span>
                        First place at 0 Race Bonus in the standard career families. <b>Trackblazer pays the
                        same stat gain at a lower SP figure</b> (G1 +35, G2-G3 +25, OP-Pre-OP +20). Second
                        and third are not published.
                    </span>
                </p>
            </section>
        </div>
    </AppLayout>
</template>
