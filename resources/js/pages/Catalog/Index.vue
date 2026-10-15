<script setup lang="ts">
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
        <Head title="Umamusume catalog" />
        <template #eyebrow>Compare</template>
        <template #title>Database</template>

        <TraineeCatalog
            :umamusumes="props.umamusumes"
            :statuses="props.statuses"
            :current-status="props.currentStatus"
            :search="props.search"
            :show-all-status="props.showAllStatus"
            :show-unconfirmed="props.showUnconfirmed"
            :all-statuses-label="props.allStatusesLabel"
            filter-path="/umamusume"
        />
    </AppLayout>
</template>
