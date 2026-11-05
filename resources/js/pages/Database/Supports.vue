<script setup lang="ts">
/*
 * The Database hub's Supports area (SCREEN-023, plan §8 D17).
 *
 * The support-card catalog, on the Database URL. The same shared list body the catalog mounts, behind
 * `SupportCardController::databaseIndex`. See `Trainees.vue` for why this is a shared screen rather
 * than a redirect to `/support-cards`.
 */
import AppLayout from '../../layouts/AppLayout.vue';
import DatabaseNav from '../../components/database/DatabaseNav.vue';
import SupportCardCatalog from '../../components/support/SupportCardCatalog.vue';
import { Head } from '@inertiajs/vue3';

interface Effect {
    effect_id: number;
    name: string | null;
    display: string;
}

/*
 * The row shape `SupportCardCatalog` consumes, declared to match it.
 *
 * `char_name`, `title`, `type` and `stages` are part of every index row
 * (`SupportCardController::mapCardForIndex()`), and the component's compare modal reads them. This
 * page's copy omitted them, so the two `Card` types were structurally different and passing
 * `props.cards` into the component could not typecheck even though the payload was identical.
 */
interface Card {
    id: number;
    name: string;
    char_name: string;
    title: string | null;
    type: string;
    stages: { effect_id: number; name: string | null; anchors: { level: number; display: string }[]; cap: { level: number; display: string } }[];
    url: string;
    artworkURL: string | null;
    rarity_label: string;
    rarity_stars: string;
    rarity_word: string;
    type_label: string;
    release_status: string;
    effects: Effect[];
}

/*
 * The paginator `SupportCardCatalog` consumes, declared with the same members it does.
 *
 * This page's copy was an older, thinner shape (`data`, `links`, `total`), so passing `props.cards`
 * into the component compared two unrelated `Paginator<Card>` types and failed. The component's
 * declaration is the one the pager renders from, and it matches `LengthAwarePaginator::toArray()`,
 * so this copy mirrors it rather than keeping its own shorter list.
 */
interface Paginator<T> {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<{
    cards: Paginator<Card>;
    rarity: string | null;
    type: string | null;
    status: string | null;
    sort: string | null;
    pageSize?: number | string | null;
    rarityWords: Record<string, string>;
    typeWords: Record<string, string>;
    availabilities: string[];
    sorts: Record<string, string>;
    totalCount: number;
    askedFor: string;
    /*
     * The D3 filter set. `SupportCardController::indexProps()` sends every one of these to *both*
     * pages it renders, but this page never declared or forwarded them, so on `/database/supports`
     * the trainee select, the effect select, "used in a deck" and "owned" rendered with no value and
     * no options: the component's required props fell through as undefined. The catalog URL has
     * always passed them; this is the Database URL catching up.
     */
    trainee: string | null;
    effects: number[] | null;
    usedInDeck: boolean | null;
    owned: boolean | null;
    traineeOptions: Record<string, string>;
    effectOptions: Record<number, string>;
}>();
</script>

<template>
    <AppLayout :breadcrumb="[{ label: 'Database', href: '/database' }, { label: 'Support Cards' }]">
        <Head title="Support Cards" />
        <template #title>Support Cards</template>

        <DatabaseNav />

        <SupportCardCatalog
            :cards="props.cards"
            :rarity="props.rarity"
            :type="props.type"
            :status="props.status"
            :sort="props.sort"
            :page-size="props.pageSize"
            :rarity-words="props.rarityWords"
            :type-words="props.typeWords"
            :availabilities="props.availabilities"
            :sorts="props.sorts"
            :total-count="props.totalCount"
            :asked-for="props.askedFor"
            :trainee="props.trainee"
            :effects="props.effects"
            :used-in-deck="props.usedInDeck"
            :owned="props.owned"
            :trainee-options="props.traineeOptions"
            :effect-options="props.effectOptions"
            filter-path="/database/supports"
        />
    </AppLayout>
</template>
