<script setup lang="ts">
/*
 * The Umamusume roster tree, shared by the catalog index (`/umamusume`) and the Database hub's
 * Trainees area (`/database/trainees`, SCREEN-023, plan §8 D17).
 *
 * One list body, two URLs. The page that mounts it owns the shell, the heading and the props, and
 * passes the address the filter form writes back to, so a Trainer filtering from the Database URL
 * stays on it rather than being carried to the URL this query was first written for.
 */
import ArtworkSlot from '../ArtworkSlot.vue';
import RarityChip from '../RarityChip.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

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
    /** Where the filter form and the pager write back to. Required: a caller that omits it would
     *  silently filter from the Database URL back to `/umamusume`. */
    filterPath: string;
}>();

const search = ref(props.search ?? '');
const status = ref(props.showAllStatus ? 'all' : (props.currentStatus ?? ''));
const showUnconfirmed = ref(props.showUnconfirmed);
const loading = ref(false);

function applyFilters(): void {
    router.get(
        props.filterPath,
        {
            search: search.value || undefined,
            status: status.value || undefined,
            show_unconfirmed: showUnconfirmed.value ? 1 : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onStart: () => {
                loading.value = true;
            },
            onFinish: () => {
                loading.value = false;
            },
        },
    );
}

const formCountLabel = (count: number): string => (count === 1 ? 'form' : 'forms');
</script>

<template>
    <h2 class="text-2xl font-semibold text-ink-strong">Umamusume catalog</h2>

    <form class="mt-4 flex flex-wrap items-end gap-3 text-sm" @submit.prevent="applyFilters">
        <label class="flex flex-col gap-1">
            <span class="text-ink-muted">Search</span>
            <input
                v-model="search"
                type="text"
                name="search"
                placeholder="Trainee or card name"
                class="h-11 rounded-md border border-rule bg-raised px-2 text-ink"
            >
        </label>
        <label class="flex flex-col gap-1">
            <span class="text-ink-muted">Release status</span>
            <select v-model="status" name="status" class="h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                <option value="all">{{ allStatusesLabel }}</option>
                <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
            </select>
        </label>
        <label class="flex items-center gap-2 pb-1.5">
            <input v-model="showUnconfirmed" type="checkbox" name="show_unconfirmed" value="1" class="rounded border-rule">
            <span class="text-ink-muted">Show unconfirmed cards</span>
        </label>
        <button type="submit" class="enamel h-11 rounded-full bg-chrome px-3 py-1.5 font-semibold text-on-chrome">
            Filter
        </button>
    </form>

    <p v-if="loading" role="status" class="mt-4 text-sm text-ink-muted">Loading results…</p>

    <p
        v-if="umamusumes.data.length === 0"
        class="mt-8 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted"
    >
        No Umamusume match. The catalog is filled by seed data or `php artisan uma:fetch`.
    </p>

    <template v-else>
        <ul class="mt-6 space-y-4">
            <li v-for="trainee in umamusumes.data" :key="trainee.id" class="rounded-md border border-rule bg-raised">
                <h3 class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 px-4 py-3">
                    <!-- Frame and name are one group, not two siblings of a `justify-between` row.
                         With three children that class distributed the leftover space and floated
                         the name to the row's centre: 89px from its own portrait, and at a
                         different x in every row (measured 411 to 447), which broke the Law of
                         Proximity and left no scanning column on a list meant to be scanned.
                         `reserve` holds the cell so the name starts at one x whether or not the
                         mirror happens to hold the file.

                         The header already prints her name beside the frame, so the image is
                         decorative and takes `alt=""` (DESIGN.md §4.7); a screen reader reads the
                         name once, from the text. No `href`: §45a makes the row's own name link
                         the destination, so a second link to the same page would be a duplicate
                         control. -->
                    <span class="flex items-center gap-3">
                        <ArtworkSlot :url="trainee.artworkURL" alt="" size="size-12" reserve />
                        <a :href="`/umamusume/${trainee.slug}`" class="font-semibold text-ink-strong hover:underline">
                            {{ trainee.name }}
                            <span v-if="trainee.name_ja" lang="ja" class="ml-2 text-sm font-normal text-ink-muted">
                                {{ trainee.name_ja }}
                            </span>
                        </a>
                    </span>
                    <span class="flex items-baseline gap-3 text-xs text-ink-muted">
                        <template v-if="trainee.max_rarity">
                            <RarityChip :label="trainee.max_rarity.label" :stars="trainee.max_rarity.stars" />
                            <span>{{ trainee.form_count }} {{ formCountLabel(trainee.form_count) }}</span>
                        </template>
                        <span v-else>no forms recorded</span>
                        <span>{{ trainee.release_status_label }}</span>
                    </span>
                </h3>

                <ul v-if="trainee.cards.length > 0" class="divide-y divide-rule border-t border-rule">
                    <li
                        v-for="card in trainee.cards"
                        :key="card.id"
                        class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 px-4 py-2"
                    >
                        <!-- The form's own portrait, keyed on its card id, at the `size-10` geometry
                             §45a records for a costume-form row. Grouped with the title for the same
                             reason the header is: three siblings in a `justify-between` row centre
                             the middle one. A `div` rather than a `span`, because this group holds an
                             `h4`, and flow content is not allowed inside a `span`. Decorative like
                             the header frame: this row prints the form title beside it, and the file
                             is a trainee portrait, so the name lives in the header, not here. -->
                        <div class="flex items-center gap-3">
                            <ArtworkSlot :url="card.artworkURL" alt="" size="size-10" reserve />
                            <h4 class="text-sm font-medium text-ink">{{ card.title }}</h4>
                        </div>
                        <span class="flex items-baseline gap-3 text-xs text-ink-muted">
                            <RarityChip :label="card.rarity_label" :stars="card.rarity_stars" />
                            <span v-if="card.is_debut_form">debut form</span>
                            <span v-if="card.unconfirmed" class="font-semibold text-risk">
                                Not confirmed by two sources
                            </span>
                            <time v-if="card.global_release_date" :datetime="card.global_release_date">
                                Released (Global) {{ card.global_release_date_display }}
                            </time>
                        </span>
                    </li>
                </ul>
            </li>
        </ul>

        <nav v-if="umamusumes.links.length > 3" aria-label="Pagination" class="mt-4 flex flex-wrap gap-2 text-sm">
            <template v-for="(link, index) in umamusumes.links" :key="index">
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
</template>
