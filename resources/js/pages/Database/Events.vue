<script setup lang="ts">
/*
 * The Events reference view (SCREEN-023, `SCR-SYS-008`). The recurring event types from
 * `docs/UMAMUSUME_REFERENCE.md` §4.4, printed as the reference guide states them.
 *
 * **Recurring types, not a live schedule.** §4.1 to §4.3 record what was running on one day and rot;
 * §4.4 is a cadence table read from the local event exports, which is what a reference view can print
 * honestly. Nothing here is a countdown and nothing predicts the next edition.
 *
 * **Level 3, informational** (design-2.0 §4): muted text on secondary cards, no Level 1 emphasis. A
 * reference row is not a decision, so no row carries a primary action.
 *
 * **State is never colour-only** (§5, §42): the server tag is bracketed text, and the provenance badge
 * is glyph plus word with the word as its accessible name. The row's own detail opens in the shared
 * drawer (§32), never in a modal (§31).
 */
import AppLayout from '../../layouts/AppLayout.vue';
import DatabaseNav from '../../components/database/DatabaseNav.vue';
import DataTable, { type DataTableColumn } from '../../components/ui/DataTable.vue';
import ProvenanceBadge from '../../components/ProvenanceBadge.vue';
import ReferenceDrawer from '../../components/database/ReferenceDrawer.vue';
import SourceNote from '../../components/database/SourceNote.vue';
import { Head } from '@inertiajs/vue3';
import { computed, nextTick, ref } from 'vue';
import type { ProvenanceState } from '../../types';

type Badge = ProvenanceState;

interface EventRow {
    name: string;
    servers: string[];
    cadence: string;
    mechanics: string;
    state: Badge;
    source_title: string;
}

const props = defineProps<{
    rows: EventRow[];
    recheck: { text: string; url: string };
    source: { section: string; anchor: string };
    totalCount: number;
}>();

const selected = ref<EventRow | null>(null);
const trigger = ref<HTMLElement | null>(null);

/*
 * The event types are a table, not a list of cards: a reader compares a cadence against another
 * cadence, and a table says which column a figure sits in. The server tags stay chips because the
 * server is the fact two event types are compared on, and the name cell stays the drawer's trigger
 * so the row still opens its own detail.
 */
const eventColumns: DataTableColumn[] = [
    { key: 'name', label: 'Event' },
    { key: 'servers', label: 'Servers' },
    { key: 'cadence', label: 'Cadence' },
    { key: 'mechanics', label: 'Mechanics' },
    { key: 'state', label: 'Source' },
];

const eventRows = computed(() =>
    props.rows.map((row) => ({
        name: row.name,
        servers: row.servers,
        cadence: row.cadence,
        mechanics: row.mechanics,
        state: row.state,
        source_title: row.source_title,
    })),
);

const open = (row: EventRow, event: MouseEvent): void => {
    trigger.value = event.currentTarget as HTMLElement;
    selected.value = row;
};

// Focus returns to the row that opened the drawer, after the drawer unmounts. Restoring it before the
// DOM settles would drop focus on a removed node, which is the trap the career screens recorded.
const close = async (): Promise<void> => {
    selected.value = null;
    await nextTick();
    trigger.value?.focus();
};
</script>

<template>
    <AppLayout :breadcrumb="[{ label: 'Database', href: '/database' }, { label: 'Events' }]">
        <Head title="Events" />
        <template #title>Events</template>

        <DatabaseNav />

        <h2 class="text-lg font-semibold text-ink-strong">Recurring event types</h2>

        <p class="mt-4 max-w-3xl text-sm text-ink-muted">
            The event families that recur on the Global and JP servers, with the cadence the reference
            guide read from the local event exports. This is the cadence reference, not a live schedule:
            the guide's dated snapshot of what was running on one day is in its own section, and it goes
            stale the day it is written.
        </p>

        <SourceNote
            :section="props.source.section"
            :anchor="props.source.anchor"
            :recheck="props.recheck"
        />

        <p class="mt-6 text-sm text-ink-muted">{{ props.totalCount }} recurring types</p>

        <section
            v-if="props.rows.length === 0"
            aria-labelledby="events-empty"
            class="mt-4 rounded-md border border-dashed border-rule bg-raised p-6"
        >
            <h3 id="events-empty" class="text-lg font-semibold text-ink-strong">
                No recurring event types are sourced
            </h3>
            <p class="mt-2 text-sm text-ink-muted">
                The reference guide's table of recurring event types is what this view prints, and it is
                empty here. Without it there is no cadence plan to read around and no way to tell a
                monthly event from a seasonal one.
            </p>
            <p class="mt-2 text-sm text-ink">
                Re-read the guide's event-type table, or the event exports its cadence column is computed from.
            </p>
        </section>

        <DataTable
            v-else
            class="mt-2"
            caption="Recurring event types"
            :columns="eventColumns"
            :rows="eventRows"
        >
            <!-- The name cell is the drawer trigger, so the row still opens its own detail. The
                 server tags are chips because the server is the fact a reader compares on. -->
            <template #cell-name="{ row }">
                <button
                    type="button"
                    class="flex min-h-11 items-center text-left text-sm font-medium text-ink-strong hover:underline"
                    :aria-expanded="selected?.name === row.name"
                    @click="open(row, $event)"
                >
                    {{ row.name }}
                </button>
            </template>
            <template #cell-servers="{ row }">
                <span class="flex flex-wrap items-center gap-1">
                    <span
                        v-for="server in row.servers"
                        :key="server"
                        class="rounded-full bg-sunken px-2 py-0.5 text-xs font-semibold text-ink-strong"
                    >
                        [{{ server }}]
                    </span>
                </span>
            </template>
            <template #cell-state="{ row }">
                <ProvenanceBadge :state="row.state" :title="row.source_title" />
            </template>
        </DataTable>

        <ReferenceDrawer
            v-if="selected"
            class="mt-3"
            :title="selected.name"
            @close="close"
        >
            <p>{{ selected.mechanics }}</p>
            <p class="text-ink-muted">Cadence: {{ selected.cadence }}</p>
            <p class="text-xs text-ink-muted">{{ selected.source_title }}</p>
        </ReferenceDrawer>
    </AppLayout>
</template>
