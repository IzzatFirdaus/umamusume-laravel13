<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import StatusPill from '../../components/StatusPill.vue';
import SubCell from '../../components/SubCell.vue';

interface Run {
    id: number;
    name: string;
    scenario_label: string | null;
    status_label: string;
    created_date: string;
    url: string;
}

interface Paginator<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
}

defineProps<{
    runs: Paginator<Run>;
}>();

const statusVariant = (status: string): 'info' | 'success' | 'warning' | 'neutral' => {
    switch (status) {
        case 'Active': return 'info';
        case 'Completed': return 'success';
        case 'Retired': return 'warning';
        default: return 'neutral';
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Training runs" />
        <template #eyebrow>Overview</template>
        <template #title>Training runs</template>

        <!-- Import sits beside New run rather than in a menu: it creates a run too, and a Trainer
             with a finished career in a file has to find it on the same glance. -->
        <div class="flex flex-wrap items-center gap-3">
            <a
                href="/training-runs/import"
                class="inline-flex min-h-11 items-center text-sm text-ink-muted hover:underline transition-colors duration-150 ease-out"
            >
                Import a historical run
            </a>
            <a
                href="/career/setup/scenario"
                class="enamel inline-flex min-h-11 items-center rounded-full bg-chrome px-4 text-sm font-bold text-on-chrome"
            >
                New run
            </a>
        </div>

        <!-- Row count line -->
        <p class="mt-4 text-sm text-ink-muted">
            {{ runs.total }} {{ runs.total === 1 ? 'run' : 'runs' }} total
        </p>

        <!-- An empty list is a real state, not a failure, and it has to say what happens next.
             "No data" alone would leave the Trainer guessing. -->
        <div
            v-if="runs.data.length === 0"
            class="mt-6 rounded-md border border-dashed border-rule bg-panel p-6"
        >
            <p class="text-sm font-semibold text-ink-strong">No runs yet</p>
            <p class="mt-1 text-sm text-ink-muted">
                A run holds the turns you log against one Umamusume in one scenario, so there is
                nothing to list until you start one.
            </p>
        </div>

        <template v-else>
            <ul class="mt-6 divide-y divide-rule rounded-md border border-rule bg-panel">
                <li
                    v-for="run in runs.data"
                    :key="run.id"
                    class="flex flex-wrap items-baseline justify-between gap-4 px-4 py-4"
                >
                    <a
                        :href="run.url"
                        class="font-semibold text-ink hover:underline transition-colors duration-150 ease-out min-w-0 flex-1"
                        :aria-label="`${run.name} · ${run.scenario_label ?? 'no scenario'} · ${run.status_label} · ${run.created_date}`"
                    >
                        {{ run.name }}
                    </a>

                    <!-- Sub-cells for scenario, status, date -->
                    <div class="flex flex-wrap items-center gap-3 text-sm shrink-0">
                        <SubCell
                            label="Scenario"
                            :value="run.scenario_label ?? 'N/A'"
                            value-class="font-mono text-base tabular-nums text-ink-strong"
                            :title="run.scenario_label === null ? 'No scenario is set for this run.' : undefined"
                        />
                        <StatusPill :label="run.status_label" :variant="statusVariant(run.status_label)" size="sm" />
                        <SubCell
                            label="Started"
                            :value="run.created_date"
                            value-class="font-mono text-base tabular-nums"
                        />
                    </div>

                    <!-- Progress bar for Active runs -->
                    <div v-if="run.status_label === 'Active'" class="w-full mt-2 lg:mt-0 lg:w-48 shrink-0">
                        <div class="h-2 w-full overflow-hidden rounded-md bg-sunken" aria-hidden="true">
                            <span
                                class="block h-full bg-lime"
                                style="width: 45%"
                            ></span>
                        </div>
                        <p class="mt-1 text-xs text-ink-muted text-center">Turn ~11 / 24</p>
                    </div>
                </li>
            </ul>

            <nav v-if="runs.links.length > 3" aria-label="Pagination" class="mt-4 flex flex-wrap gap-2 text-sm">
                <template v-for="(link, index) in runs.links" :key="index">
                    <a
                        v-if="link.url"
                        :href="link.url"
                        :aria-current="link.active ? 'page' : undefined"
                        class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-ink hover:bg-raised transition-colors duration-150 ease-out"
                        :class="link.active ? 'bg-raised font-bold text-ink-strong' : ''"
                        v-html="link.label"
                    />
                    <span v-else class="inline-flex min-h-11 items-center px-3 text-ink-muted" v-html="link.label" />
                </template>
            </nav>
        </template>
    </AppLayout>
</template>
