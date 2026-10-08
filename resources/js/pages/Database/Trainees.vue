<script setup lang="ts">
/*
 * The Database hub's Trainees area (SCREEN-023, plan §8 D17).
 *
 * The Umamusume catalog, on the Database URL. It renders the same shared list body the catalog index
 * mounts, and the controller behind this route is `CatalogController::databaseIndex`, which runs that
 * controller's own query. A redirect to `/umamusume` was the smaller diff and is refused by the slice
 * brief, because no owner ruling authorises one.
 */
import AppLayout from '../../layouts/AppLayout.vue';
import TraineeCatalog from '../../components/catalog/TraineeCatalog.vue';
import { Head } from '@inertiajs/vue3';

interface Card {
    id: number;
    title: string;
    rarity_label: string;
    rarity_stars: string;
    is_debut_form: boolean;
    unconfirmed: boolean;
    global_release_date: string | null;
    global_release_date_display: string | null;
    artworkURL: string | null;
}

interface Trainee {
    id: number;
    slug: string;
    name: string;
    name_ja: string | null;
    release_status_label: string;
    max_rarity: { label: string; stars: string } | null;
    artworkURL: string | null;
    form_count: number;
    cards: Card[];
}

interface Paginator<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
}

const props = defineProps<{
    umamusumes: Paginator<Trainee>;
    statuses: { value: string; label: string }[];
    currentStatus: string | null;
    search: string | null;
    showAllStatus: boolean;
    showUnconfirmed: boolean;
    allStatusesLabel: string;
}>();
</script>

<template>
    <AppLayout>
        <Head title="Trainees" />
        <template #title>Trainees</template>

        <TraineeCatalog
            :umamusumes="props.umamusumes"
            :statuses="props.statuses"
            :current-status="props.currentStatus"
            :search="props.search"
            :show-all-status="props.showAllStatus"
            :show-unconfirmed="props.showUnconfirmed"
            :all-statuses-label="props.allStatusesLabel"
            filter-path="/database/trainees"
        />
    </AppLayout>
</template>
