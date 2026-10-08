<script setup lang="ts">
/*
 * The Database hub (SCREEN-023, plan §8 D17; completed by `SCR-SYS-008` to `010`).
 *
 * The eight areas SCREEN-023 lists. The first five landed with D17; the last three (Events, Shop
 * Items and Sparks) were a "Not in this build" section, and that section is now deleted rather than
 * softened: the sections are in the brief and the source data exists, so a row saying otherwise would
 * be false. Each area is a live link, and none is a dead one.
 *
 * No area counts on this page. Each area states how many rows it holds, and the catalog's first screen
 * is filtered to [Global] while `umamusume` also holds JP-only rows, so a figure here would be one no
 * area corroborates on arrival.
 *
 * The sidebar's Database entry points at this hub rather than at `/umamusume`, so the hub has an
 * inbound click path without adding an eleventh destination to the nav.
 */
import AppLayout from '../../layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    areas: { label: string; url: string }[];
}>();
</script>

<template>
    <AppLayout>
        <Head title="Database" />
        <template #title>Database</template>

        <p class="mt-4 max-w-3xl text-sm text-ink-muted">
            Reference data this tool fetched, plus the tables transcribed from the reference guide.
            Reading any of it never touches a run: an area opens the catalog behind it, and nothing
            here writes.
        </p>

        <nav aria-label="Database areas" class="mt-6">
            <ul class="divide-y divide-rule rounded-md border border-rule bg-raised">
                <li v-for="area in props.areas" :key="area.label">
                    <Link
                        :href="area.url"
                        :prefetch="'hover'"
                        class="flex min-h-11 items-center px-4 text-sm font-medium text-ink-strong hover:bg-raised"
                    >
                        {{ area.label }}
                    </Link>
                </li>
            </ul>
        </nav>
    </AppLayout>
</template>
