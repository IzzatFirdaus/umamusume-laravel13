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
 */
import AppLayout from '../../layouts/AppLayout.vue';
import DatabaseNav from '../../components/database/DatabaseNav.vue';
import Pagination from '../../components/ui/Pagination.vue';
import { Head, router } from '@inertiajs/vue3';

interface Slot {
    id: number;
    title: string;
    year_label: string;
    turn: number | null;
    tier: string | null;
    distance: number | null;
    surface: string | null;
    fans_needed: number | null;
    is_mandatory: boolean;
    is_maiden_gated: boolean;
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
}>();

const gates = (slot: Slot): string[] =>
    [
        slot.is_mandatory ? 'Mandatory' : null,
        slot.is_maiden_gated ? 'Maiden-gated' : null,
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
    <AppLayout>
        <Head title="Races" />
        <template #title>Races</template>

        <DatabaseNav />

        <h2 class="text-lg font-semibold text-ink-strong">Race database</h2>

        <p class="mt-4 max-w-3xl text-sm text-ink-muted">
            Every slot of the shared [Global] career calendar, one row per race per turn. The run's race
            calendar, the Race Decision screen and the rows a Trainer records against a race all read from
            this table.
        </p>

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
                        Career race slots: race, year, turn, tier, distance, surface, fan gate and entry gate
                    </caption>
                    <thead>
                        <tr class="border-b border-rule bg-sunken text-left text-xs text-ink-muted">
                            <th scope="col" class="py-1.5 px-3 font-semibold">Race</th>
                            <th scope="col" class="py-1.5 px-3 font-semibold">Year</th>
                            <th scope="col" class="py-1.5 px-3 font-semibold">Turn</th>
                            <th scope="col" class="py-1.5 px-3 font-semibold">Tier</th>
                            <th scope="col" class="py-1.5 px-3 font-semibold">Distance</th>
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
                <p class="mt-2 text-xs text-ink-muted">
                    N/A marks a field the source records nothing for. It is not a zero, and no column
                    here fills a gap with a guess.
                </p>
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
        </template>
    </AppLayout>
</template>
