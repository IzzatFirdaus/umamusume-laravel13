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
import { Head } from '@inertiajs/vue3';

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
    links: { url: string | null; label: string; active: boolean }[];
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
</script>

<template>
    <AppLayout>
        <Head title="Races" />
        <template #title>Races</template>

        <h2 class="text-2xl font-semibold text-ink-strong">Race database</h2>

        <p class="mt-4 max-w-3xl text-sm text-ink-muted">
            Every slot of the shared [Global] career calendar, one row per race per turn. The run's race
            calendar, the Race Decision screen and the rows a Trainer records against a race all read from
            this table.
        </p>

        <section
            v-if="props.totalCount === 0"
            aria-labelledby="races-offline"
            class="mt-8 rounded-md border border-dashed border-rule bg-raised p-6"
        >
            <h3 id="races-offline" class="text-base font-semibold text-ink-strong">
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
            <p class="mt-6 text-sm text-ink-muted">{{ props.slots.total }} of {{ props.totalCount }} slots</p>

            <div
                class="mt-2 overflow-x-auto rounded-md border border-rule bg-raised"
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
                                <span v-if="slot.turn === null" title="The source records no turn number for this slot.">N/A</span>
                                <span v-else>{{ slot.turn }}</span>
                            </td>
                            <td class="py-1.5 px-3 text-ink">
                                <span v-if="slot.tier === null" title="The source records no tier for this slot.">N/A</span>
                                <span v-else>{{ slot.tier }}</span>
                            </td>
                            <td class="py-1.5 px-3 text-ink">
                                <span v-if="slot.distance === null" title="The source records no distance for this slot.">N/A</span>
                                <span v-else>{{ slot.distance }} m</span>
                            </td>
                            <td class="py-1.5 px-3 text-ink">
                                <span v-if="slot.surface === null" title="The source records no surface for this slot.">N/A</span>
                                <span v-else>{{ slot.surface }}</span>
                            </td>
                            <td class="py-1.5 px-3 text-ink">
                                <span v-if="slot.fans_needed === null" title="The source records no fan gate for this slot.">N/A</span>
                                <span v-else>{{ slot.fans_needed }}</span>
                            </td>
                            <td class="py-1.5 px-3">
                                <span
                                    v-if="gates(slot).length === 0"
                                    title="No entry gate: open to a trainee at this point of the career."
                                >
                                    N/A
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
            </div>

            <nav v-if="props.slots.links.length > 3" aria-label="Pagination" class="mt-4 flex flex-wrap gap-2 text-sm">
                <template v-for="(link, index) in props.slots.links" :key="index">
                    <a
                        v-if="link.url"
                        :href="link.url"
                        :aria-current="link.active ? 'page' : undefined"
                        class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-ink hover:bg-raised"
                        :class="link.active ? 'bg-raised font-bold text-ink-strong' : ''"
                        v-html="link.label"
                    />
                    <span v-else class="inline-flex min-h-11 items-center px-3 text-ink-muted" v-html="link.label" />
                </template>
            </nav>
        </template>
    </AppLayout>
</template>
