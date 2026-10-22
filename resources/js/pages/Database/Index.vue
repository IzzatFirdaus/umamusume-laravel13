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
 * area corroborates on arrival. What each row does carry is its source arm — Fetched, Config or
 * Transcribed — the three the subtitle names, moved from each view's own provenance onto the door.
 *
 * The sidebar's Database entry points at this hub rather than at `/umamusume`, so the hub has an
 * inbound click path without adding an eleventh destination to the nav.
 */
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import Card from '../../components/ui/Card.vue';
import SubCell from '../../components/SubCell.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    areas: { label: string; url: string; source: string }[];
}>();

const sourceReason: Record<string, string> = {
    Fetched: 'Fetched from the engine sources and read straight off the catalog tables.',
    Config: 'Read from config/scenarios.php, the only place scenario facts live (D-240).',
    Transcribed: 'Transcribed from docs/UMAMUSUME_REFERENCE.md into config/reference.php.',
};
</script>

<template>
    <AppLayout>
        <Head title="Database" />
        <template #head>
            <PageHeader
                eyebrow="Data Quality"
                title="Database"
                description="Reference data this tool fetched, plus the tables transcribed from the reference guide. Reading any of it never touches a run: an area opens the catalog behind it, and nothing here writes."
            />
        </template>

        <!-- The reference-data sentence moved into the PageHeader's description, so it is stated
             once at the top rather than as a loose paragraph that reads as a second title. -->

        <nav aria-label="Database areas" class="mt-6">
            <!-- One card per area, so each dataset is a destination of its own rather than a row
                 in a list that has to be scanned to find the one you want. The card is the list
                 item, so the list a screen reader hears is the list the page shows. -->
            <ul class="grid gap-3 @2xl:grid-cols-2 @4xl:grid-cols-3">
                <Card
                    v-for="area in props.areas"
                    :key="area.label"
                    as="li"
                    class="flex items-center gap-x-4"
                >
                    <!-- The source cell sits beside the link, not inside it: a link's accessible name is
                         its contents, and the hub's rows are addressed by their exact label elsewhere
                         (`database.spec.ts`); "Events Source Transcribed" would be a different name. -->
                    <Link
                        :href="area.url"
                        :prefetch="'hover'"
                        class="flex min-h-11 flex-1 items-center text-sm font-medium text-ink-strong hover:bg-panel transition-colors duration-150 ease-out"
                    >
                        {{ area.label }}
                    </Link>
                    <SubCell
                        label="Source"
                        :value="area.source"
                        value-class="font-mono text-sm text-ink-strong"
                        :title="sourceReason[area.source]"
                    />
                </Card>
            </ul>
        </nav>
    </AppLayout>
</template>
