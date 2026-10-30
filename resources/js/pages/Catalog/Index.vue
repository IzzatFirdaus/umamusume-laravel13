<script setup lang="ts">
/*
 * The catalogue page, rebuilt to `catalog.html` (plan Slice 8).
 *
 * This file is the shell: the head band, the Database strip, and the mount. The list body - the
 * filter row, the card grid and the pager - is `TraineeCatalog.vue`, shared with the Database hub's
 * Trainees area (`/database/trainees`), so the catalogue has one implementation at two URLs.
 *
 * The card grammar the wireframe asks for lives in that component: cards rather than a table, three
 * aptitude letters per card, the debut form's own rarity beside her name, the release status as a
 * word, and a growth row that states the catalogue holds no growth figure.
 */
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import DatabaseNav from '../../components/database/DatabaseNav.vue';
import TraineeCatalog from '../../components/catalog/TraineeCatalog.vue';
import { Head } from '@inertiajs/vue3';

interface DefaultForm {
    title: string;
    rarity_label: string;
    rarity_stars: string;
    rarity_word: string;
    is_debut_form: boolean;
}

interface AptitudeLetter {
    axis: 'surface' | 'distance' | 'style';
    label: string;
    letter: string;
}

interface Trainee {
    id: number;
    slug: string;
    name: string;
    name_ja: string | null;
    /** The stored enum value. The page branches on this, never on the display word. */
    release_status: string;
    release_status_label: string;
    max_rarity: { label: string; stars: string } | null;
    artworkURL: string | null;
    /** The form the portrait shows, or null when the catalogue holds no form for her. */
    default_form: DefaultForm | null;
    /** The strongest letter per axis, or null when the source published no aptitudes. */
    aptitude_summary: AptitudeLetter[] | null;
    form_count: number;
}

interface Paginator<T> {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<{
    umamusumes: Paginator<Trainee>;
    totalCount: number;
    askedFor: string;
    statuses: { value: string; label: string }[];
    currentStatus: string | null;
    /**
     * The three aptitude facets as the URL carries them, echoed so a reload keeps the selection.
     * Each is an AXIS key (`turf`, `medium`, `pace_chaser`), validated server-side by
     * `CatalogSearchRequest`; null means no filter chosen on that axis.
     */
    currentSurface: string | null;
    currentDistance: string | null;
    currentStyle: string | null;
    search: string | null;
    showAllStatus: boolean;
    showUnconfirmed: boolean;
    allStatusesLabel: string;
}>();

</script>

<template>
    <AppLayout :breadcrumb="[{ label: 'Database', href: '/database' }, { label: 'Trainees' }]">
        <Head title="Umamusume catalog" />
        <template #head>
            <PageHeader
                eyebrow="Catalog"
                title="Umamusume catalog"
                description="Every aptitude letter here is the catalog's. Each card names three letters, the strongest per axis; the detail page owns the full set."
            />
        </template>

        <DatabaseNav />

        <TraineeCatalog
            :umamusumes="props.umamusumes"
            :total-count="props.totalCount"
            :asked-for="props.askedFor"
            :statuses="props.statuses"
            :current-status="props.currentStatus"
            :current-surface="props.currentSurface"
            :current-distance="props.currentDistance"
            :current-style="props.currentStyle"
            :search="props.search"
            :show-all-status="props.showAllStatus"
            :show-unconfirmed="props.showUnconfirmed"
            :all-statuses-label="props.allStatusesLabel"
            filter-path="/umamusume"
        />

</AppLayout>
</template>
