<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

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
</script>

<template>
    <AppLayout>
        <Head title="Training runs" />
        <template #title>Training runs</template>

        <!-- Import sits beside New run rather than in a menu: it creates a run too, and a Trainer
             with a finished career in a file has to find it on the same glance. -->
        <div class="flex flex-wrap items-center gap-3">
            <a
                href="/training-runs/import"
                class="inline-flex min-h-11 items-center text-sm text-ink-muted hover:underline"
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
                    class="flex flex-wrap items-baseline justify-between gap-2 px-4 py-3 text-sm"
                >
                    <a :href="run.url" class="font-semibold text-ink hover:underline">
                        {{ run.name }}
                        <span v-if="run.scenario_label !== null" class="font-normal text-ink-muted">
                            · {{ run.scenario_label }}
                        </span>
                    </a>
                    <span class="font-mono text-xs tabular-nums text-ink-muted">
                        {{ run.status_label }} · {{ run.created_date }}
                    </span>
                </li>
            </ul>

            <nav v-if="runs.links.length > 3" aria-label="Pagination" class="mt-4 flex flex-wrap gap-2 text-sm">
                <template v-for="(link, index) in runs.links" :key="index">
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
