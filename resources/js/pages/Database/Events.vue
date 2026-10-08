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
import ProvenanceBadge from '../../components/ProvenanceBadge.vue';
import ReferenceDrawer from '../../components/database/ReferenceDrawer.vue';
import SourceNote from '../../components/database/SourceNote.vue';
import { Head } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

interface EventRow {
    name: string;
    servers: string[];
    cadence: string;
    mechanics: string;
    state: 'confirmed' | 'calculated' | 'estimated' | 'unknown';
    source_title: string;
}

const props = defineProps<{
    rows: EventRow[];
    recheck: { text: string; url: string };
    source: { section: string; anchor: string; doc: string };
    totalCount: number;
}>();

const selected = ref<EventRow | null>(null);
const trigger = ref<HTMLElement | null>(null);

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
    <AppLayout>
        <Head title="Events" />
        <template #title>Events</template>

        <h2 class="text-2xl font-semibold text-ink-strong">Recurring event types</h2>

        <p class="mt-4 max-w-3xl text-sm text-ink-muted">
            The event families that recur on the Global and JP servers, with the cadence the reference
            guide read from the local event exports. This is the cadence reference, not a live schedule:
            the guide's dated snapshot of what was running on one day is in its own section, and it goes
            stale the day it is written.
        </p>

        <SourceNote
            :section="props.source.section"
            :anchor="props.source.anchor"
            :doc="props.source.doc"
            :recheck="props.recheck"
        />

        <p class="mt-6 text-sm text-ink-muted">{{ props.totalCount }} recurring types</p>

        <section
            v-if="props.rows.length === 0"
            aria-labelledby="events-empty"
            class="mt-4 rounded-md border border-dashed border-rule bg-raised p-6"
        >
            <h3 id="events-empty" class="text-base font-semibold text-ink-strong">
                No recurring event types are sourced
            </h3>
            <p class="mt-2 text-sm text-ink-muted">
                The reference guide's §4.4 table is what this view prints, and it is empty here. Without
                it there is no cadence to plan around and no way to tell a monthly event from a
                seasonal one.
            </p>
            <p class="mt-2 text-sm text-ink">
                Re-read the guide's §4.4 table, or the event exports its cadence column is computed from.
            </p>
        </section>

        <ul v-else class="mt-2 flex flex-col gap-3">
            <li
                v-for="row in props.rows"
                :key="row.name"
                class="rounded-md border border-rule bg-raised"
            >
                <button
                    type="button"
                    class="flex min-h-11 w-full flex-wrap items-baseline justify-between gap-x-4 gap-y-2 px-4 py-3 text-left hover:bg-panel"
                    :aria-expanded="selected?.name === row.name"
                    @click="open(row, $event)"
                >
                    <span class="text-sm font-medium text-ink-strong">{{ row.name }}</span>

                    <span class="flex flex-wrap items-center gap-2">
                        <span
                            v-for="server in row.servers"
                            :key="server"
                            class="rounded-full bg-sunken px-2 py-0.5 text-xs font-semibold text-ink-strong"
                        >
                            [{{ server }}]
                        </span>
                        <ProvenanceBadge :state="row.state" :title="row.source_title" />
                    </span>
                </button>

                <dl
                    class="grid grid-cols-1 gap-x-4 gap-y-1 border-t border-rule px-4 py-3 text-sm sm:grid-cols-[9rem_1fr]"
                >
                    <dt class="text-ink-muted">Cadence</dt>
                    <dd class="text-ink">{{ row.cadence }}</dd>
                    <dt class="text-ink-muted">Mechanics</dt>
                    <dd class="text-ink">{{ row.mechanics }}</dd>
                </dl>

                <div class="px-4 pb-3">
                    <ReferenceDrawer
                        v-if="selected?.name === row.name"
                        :title="row.name"
                        @close="close"
                    >
                        <p>{{ row.mechanics }}</p>
                        <p class="text-ink-muted">Cadence: {{ row.cadence }}</p>
                        <p class="text-xs text-ink-muted">{{ row.source_title }}</p>
                    </ReferenceDrawer>
                </div>
            </li>
        </ul>
    </AppLayout>
</template>
