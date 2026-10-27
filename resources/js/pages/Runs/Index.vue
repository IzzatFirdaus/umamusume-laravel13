<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import StatusPill from '../../components/StatusPill.vue';
import { LinkButton } from '../../components/Form';

interface Run {
    id: number;
    name: string;
    trainee_ja: string | null;
    card_title: string | null;
    rarity: number | null;
    scenario_label: string | null;
    /** The storage value; the page branches on this, never on the display word. */
    status: string;
    status_label: string;
    /** The latest turn the run has logged, 0 when it has logged none. */
    turn_index: number;
    /** The career's length on the client's calendar. */
    turn_count: number;
    /** Where the career stands, or null when it has nothing logged to stand on. */
    position_label: string | null;
    url: string;
    result_url: string;
}

interface Paginator<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
    current_page: number;
    last_page: number;
}

const props = defineProps<{
    runs: Paginator<Run>;
    /** The applied filter row, echoed back into the controls. */
    filters: { search: string | null; scenario: string | null; status: string | null; sort: string | null };
    scenarios: Record<string, string>;
    statuses: Record<string, string>;
}>();

const statusVariant = (status: string): 'info' | 'success' | 'warning' | 'neutral' => {
    switch (status) {
        case 'Active': return 'info';
        case 'Completed': return 'success';
        case 'Retired': return 'warning';
        default: return 'neutral';
    }
};

const isActive = (run: Run): boolean => run.status === 'Active';

// The line under the trainee's name: the client's own Japanese name, the costume-card title when the
// run started on one, and the rarity the Trainer read. Absent parts are simply not printed.
const metaLine = (run: Run): string =>
    [run.trainee_ja, run.card_title].filter((part): part is string => Boolean(part)).join(' · ');

// HFW runs-index annotation 3 and 4: a retired run is not a completed one, so its position says
// where it stopped rather than calling it finished. An active run prints the derived position, and
// a run with nothing logged keeps the cell and names the absence (annotation: absence is printed).
const positionText = (run: Run): string => {
    if (run.status === 'Retired') {
        return `Retired at turn ${run.turn_index}`;
    }

    if (run.status === 'Completed') {
        return 'Finished';
    }

    return run.position_label ?? 'N/A';
};

// A finished run's door is its Result screen; a live one's is the Cockpit.
const rowAction = (run: Run): { href: string; label: string } =>
    isActive(run) ? { href: run.url, label: 'Cockpit' } : { href: run.result_url, label: 'Result' };

const filtersActive = computed<boolean>(() =>
    Boolean(
        props.filters.search ||
        (props.filters.scenario !== null && props.filters.scenario !== 'all') ||
        (props.filters.status !== null && props.filters.status !== 'all'),
    ),
);

// The paginator's first link is Previous and its last is Next (Laravel's own order); the numbered
// links between them are not drawn, per the HFW's pager.
const prevLink = computed(() => props.runs.links[0] ?? null);
const nextLink = computed(() => props.runs.links[props.runs.links.length - 1] ?? null);

const fieldLabel = 'text-xs font-semibold text-ink-muted';
const fieldControl = 'min-h-11 rounded-md border border-rule bg-panel px-3 text-sm text-ink';
</script>

<template>
    <AppLayout>
        <Head title="Careers" />
        <template #head>
            <PageHeader eyebrow="Careers" title="Training runs">
                <template #actions>
                    <LinkButton href="/career/setup/scenario" variant="primary">New career</LinkButton>
                    <LinkButton href="/training-runs/import" variant="secondary">Import a run</LinkButton>
                </template>
            </PageHeader>
        </template>

        <!-- HFW runs-index annotation 1: the filters are one inline form row, above the table. A
             plain GET form so the applied filter is the URL, shareable and reload-safe. -->
        <form
            method="get"
            action="/training-runs"
            role="search"
            aria-label="Filter careers"
            class="flex flex-wrap items-end gap-3"
        >
            <label class="flex min-w-0 flex-1 basis-64 flex-col gap-1" :class="fieldLabel">
                Search trainee
                <input
                    type="search"
                    name="search"
                    :value="props.filters.search ?? ''"
                    placeholder="Rice Shower, Maruzensky…"
                    :class="fieldControl"
                />
            </label>
            <label class="flex flex-col gap-1" :class="fieldLabel">
                Scenario
                <select name="scenario" :class="fieldControl">
                    <option value="all" :selected="props.filters.scenario === null || props.filters.scenario === 'all'">
                        All scenarios
                    </option>
                    <option
                        v-for="(label, key) in props.scenarios"
                        :key="key"
                        :value="key"
                        :selected="props.filters.scenario === key"
                    >{{ label }}</option>
                </select>
            </label>
            <label class="flex flex-col gap-1" :class="fieldLabel">
                Status
                <select name="status" :class="fieldControl">
                    <option value="all" :selected="props.filters.status === null || props.filters.status === 'all'">
                        All statuses
                    </option>
                    <option
                        v-for="(label, value) in props.statuses"
                        :key="value"
                        :value="value"
                        :selected="props.filters.status === value"
                    >{{ label }}</option>
                </select>
            </label>
            <label class="flex flex-col gap-1" :class="fieldLabel">
                Sort
                <select name="sort" :class="fieldControl">
                    <option value="newest" :selected="props.filters.sort === null || props.filters.sort === 'newest'">
                        Newest first
                    </option>
                    <option value="oldest" :selected="props.filters.sort === 'oldest'">Oldest first</option>
                    <option value="trainee" :selected="props.filters.sort === 'trainee'">Trainee A-Z</option>
                </select>
            </label>
            <button
                type="submit"
                class="inline-flex min-h-11 items-center rounded-md border border-rule px-4 text-sm font-semibold text-ink hover:bg-raised transition-colors duration-150 ease-out"
            >
                Apply filters
            </button>
            <a
                href="/training-runs"
                class="inline-flex min-h-11 items-center px-2 text-sm text-ink-muted hover:underline transition-colors duration-150 ease-out"
            >Clear</a>
        </form>

        <!-- An empty list is a real state, not a failure, and it has to say what happens next.
             "No data" alone would leave the Trainer guessing. A filtered-empty list says which
             state it is, so a Trainer does not read their own filter as an empty library. -->
        <div
            v-if="props.runs.data.length === 0"
            class="mt-6 rounded-md border border-dashed border-rule bg-panel p-6"
        >
            <template v-if="filtersActive">
                <p class="text-sm font-semibold text-ink-strong">No careers match these filters</p>
                <p class="mt-1 text-sm text-ink-muted">
                    Clear the filters to see every career you have started, played or imported.
                </p>
                <a href="/training-runs" class="mt-3 inline-flex min-h-11 items-center text-sm font-medium text-ink underline">
                    Clear filters
                </a>
            </template>
            <template v-else>
                <p class="text-sm font-semibold text-ink-strong">No runs yet</p>
                <p class="mt-1 text-sm text-ink-muted">
                    A run holds the turns you log against one Umamusume in one scenario, so there is
                    nothing to list until you start one.
                </p>
            </template>
        </div>

        <div v-else class="mt-4 overflow-x-auto rounded-md border border-rule bg-panel">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-rule text-left text-xs font-semibold uppercase tracking-wider text-ink-muted">
                        <th class="px-4 py-3">Trainee</th>
                        <th class="px-3 py-3">Scenario</th>
                        <th class="px-3 py-3 text-right">Turn</th>
                        <th class="px-3 py-3">Position</th>
                        <th class="px-3 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Annotation 2: the active run is the only row with a fill, and it carries the
                         HFW's own `is-current` class so the state is addressable by name. The pill's
                         word and the Position cell carry the same state, so it never rides on colour
                         alone (WCAG 1.4.1, DESIGN.md D-12). -->
                    <tr
                        v-for="run in props.runs.data"
                        :key="run.id"
                        class="border-b border-rule last:border-b-0"
                        :class="isActive(run) ? 'is-current bg-status-positive/10' : ''"
                    >
                        <td class="px-4 py-3">
                            <b class="text-ink-strong">{{ run.name }}</b>
                            <br />
                            <span class="text-xs text-ink-muted">
                                {{ metaLine(run) }}<template v-if="metaLine(run)"> · </template>#{{ run.id }}
                                <template v-if="run.rarity !== null"> · ★{{ run.rarity }}</template>
                            </span>
                        </td>
                        <td class="px-3 py-3">
                            <template v-if="run.scenario_label">{{ run.scenario_label }}</template>
                            <span v-else>
                                N/A<span class="sr-only">: no scenario is set for this run.</span>
                            </span>
                        </td>
                        <!-- Annotation 3: the turn always carries the career's length for a live run.
                             A turn past the 72-turn grid is the finale block (DM-1: the finale sits
                             outside the 72), so it prints as a plain number rather than an
                             N / 72 fraction that reads as an overflow. -->
                        <td class="px-3 py-3 text-right font-mono tabular-nums">
                            <template v-if="isActive(run) && run.turn_index <= run.turn_count">
                                {{ run.turn_index }}
                                <span class="text-xs text-ink-muted">/ {{ run.turn_count }}</span>
                            </template>
                            <template v-else>{{ run.turn_index }}</template>
                        </td>
                        <td class="px-3 py-3">
                            <template v-if="positionText(run) !== 'N/A'">{{ positionText(run) }}</template>
                            <span v-else>
                                N/A<span class="sr-only">: no turn has been logged on this run yet.</span>
                            </span>
                        </td>
                        <td class="px-3 py-3">
                            <StatusPill :label="run.status_label" :variant="statusVariant(run.status_label)" size="sm" />
                        </td>
                        <td class="px-4 py-3 text-right">
                            <!-- Annotation 5: no destructive action in the row. Delete stays on the
                                 cockpit behind its own disclosure. -->
                            <LinkButton :href="rowAction(run).href" variant="secondary" size="sm">
                                {{ rowAction(run).label }}
                            </LinkButton>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination sits under the table, inside its card (annotation 1). -->
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-rule px-4 py-3 text-xs text-ink-muted">
                <span>
                    {{ props.runs.total }} {{ props.runs.total === 1 ? 'career' : 'careers' }}
                    · page {{ props.runs.current_page }} of {{ props.runs.last_page }}
                </span>
                <span class="flex gap-2">
                    <LinkButton v-if="prevLink?.url" :href="prevLink.url" variant="secondary" size="sm">
                        Previous
                    </LinkButton>
                    <span
                        v-else
                        class="inline-flex min-h-9 items-center rounded-md border border-rule px-3 text-ink-muted"
                    >Previous</span>
                    <LinkButton v-if="nextLink?.url" :href="nextLink.url" variant="secondary" size="sm">
                        Next
                    </LinkButton>
                    <span
                        v-else
                        class="inline-flex min-h-9 items-center rounded-md border border-rule px-3 text-ink-muted"
                    >Next</span>
                </span>
            </div>
        </div>
    </AppLayout>
</template>
