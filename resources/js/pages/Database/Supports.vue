<script setup lang="ts">
/*
 * The Database hub's Supports area (SCREEN-023, plan §8 D17).
 *
 * The support-card catalog, on the Database URL. The same shared list body the catalog mounts, behind
 * `SupportCardController::databaseIndex`. See `Trainees.vue` for why this is a shared screen rather
 * than a redirect to `/support-cards`.
 */
import AppLayout from '../../layouts/AppLayout.vue';
import SupportCardCatalog from '../../components/support/SupportCardCatalog.vue';
import { Head } from '@inertiajs/vue3';

interface Effect {
    effect_id: number;
    name: string | null;
    display: string;
}

interface Card {
    id: number;
    name: string;
    url: string;
    artworkURL: string | null;
    rarity_label: string;
    rarity_stars: string;
    rarity_word: string;
    type_label: string;
    release_status: string;
    effects: Effect[];
}

interface Paginator<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
}

const props = defineProps<{
    cards: Paginator<Card>;
    rarity: string | null;
    type: string | null;
    status: string | null;
    sort: string | null;
    rarityWords: Record<string, string>;
    typeWords: Record<string, string>;
    availabilities: string[];
    sorts: Record<string, string>;
    totalCount: number;
    askedFor: string;
}>();
</script>

<template>
    <AppLayout>
        <Head title="Support Cards" />
        <template #title>Support Cards</template>

        <SupportCardCatalog
            :cards="props.cards"
            :rarity="props.rarity"
            :type="props.type"
            :status="props.status"
            :sort="props.sort"
            :rarity-words="props.rarityWords"
            :type-words="props.typeWords"
            :availabilities="props.availabilities"
            :sorts="props.sorts"
            :total-count="props.totalCount"
            :asked-for="props.askedFor"
            filter-path="/database/supports"
        />
    </AppLayout>
</template>
