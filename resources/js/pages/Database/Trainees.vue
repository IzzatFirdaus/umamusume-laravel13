<script setup lang="ts">
/*
 * The Database hub's Trainees area (SCREEN-023, plan §8 D17).
 *
 * The Umamusume catalogue, on the Database URL. It renders the same shared body the catalogue index
 * mounts (`TraineeCatalog.vue`), and the controller behind this route is
 * `CatalogController::databaseIndex`, which runs that controller's own query. A redirect to
 * `/umamusume` was the smaller diff and is refused by the slice brief, because no owner ruling
 * authorises one.
 *
 * The area page names the area as its `h1` and gives the catalogue its own `h2`, the same shape the
 * sibling areas carry ("Support Cards" over "Support card catalog"). The list body is the wireframe's
 * `catalog.html`: cards rather than a table, three aptitude letters per card, the debut form's own
 * rarity, the release status as a word, and the growth row stating that the catalogue holds no growth
 * figure.
 */
import AppLayout from '../../layouts/AppLayout.vue';
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
    /** The stored enum value. The card branches on this, never on the display word. */
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
    /** The three aptitude facets as the URL carries them, echoed so a reload keeps the selection. */
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
        <Head title="Trainees" />
        <template #title>Trainees</template>

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
            heading="Umamusume catalog"
            filter-path="/database/trainees"
        />

        <!-- The wireframe's lede (`catalog.html`): the catalogue's own figures, and the one scale this
             screen never prints. It sits under the area's own name, because the area page's h1 is the
             area and the catalogue's h2 is the list. -->
        <p class="mt-6 max-w-3xl text-sm text-ink-muted">
            Every aptitude letter here is the catalogue's. Each card names three letters, the strongest
            on each axis, and stat grades are a separate, provisional scale that is never printed under
            the same label.
        </p>
    </AppLayout>
</template>
