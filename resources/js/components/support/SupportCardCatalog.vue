<script setup lang="ts">
/*
 * The support-card catalog list, shared by the catalog (`/support-cards`) and the Database hub's
 * Supports area (`/database/supports`, SCREEN-023, plan §8 D17).
 *
 * One list body, two URLs. The page that mounts it owns the shell and the heading; `filterPath` is
 * the address the filter form and the pager write back to, so a Trainer filtering from the Database
 * URL stays on it.
 */
import ArtworkSlot from '../ArtworkSlot.vue';
import RarityChip from '../RarityChip.vue';
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Effect {
    effect_id: number;
    name: string | null;
    display: string;
}

interface Card {
    id: number;
    name: string;
    url: string;
    /** The row thumbnail's loopback URL, keyed on the publisher's `support_id`. Null when unmirrored. */
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
    /** Where the filter form and the pager write back to. Required: a caller that omits it would
     *  silently filter from the Database URL back to `/support-cards`. */
    filterPath: string;
}>();

const page = usePage();
const errors = computed(() => (page.props.errors as Record<string, string | undefined> | undefined) ?? {});

const rarity = ref(props.rarity ?? '');
const type = ref(props.type ?? '');
const status = ref(props.status ?? '');
const sort = ref(props.sort ?? '');
const loading = ref(false);

function applyFilters(): void {
    router.get(
        props.filterPath,
        {
            rarity: rarity.value || undefined,
            type: type.value || undefined,
            status: status.value || undefined,
            sort: sort.value || undefined,
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
</script>

<template>
    <h2 class="text-2xl font-semibold text-ink-strong">Support cards</h2>

    <!-- Control height is `h-11`, the value DESIGN.md §6.14 fixes for a form input and the one
         skills/index already carries, so the two filter surfaces measure the same in a browser. -->
    <form class="mt-4 flex flex-wrap items-end gap-3 text-sm" @submit.prevent="applyFilters">
        <label class="flex flex-col gap-1">
            <span class="text-ink">Rarity</span>
            <select
                v-model="rarity"
                name="rarity"
                class="h-11 rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green"
            >
                <option value="">All</option>
                <!-- PHP keys this map on ints and JSON hands them back as text, so `value` is the
                     query string's own '3' and the picker selects without a cast. -->
                <option v-for="(word, value) in rarityWords" :key="value" :value="value">{{ word }}</option>
            </select>
        </label>

        <label class="flex flex-col gap-1">
            <span class="text-ink">Type</span>
            <select
                v-model="type"
                name="type"
                class="h-11 rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green"
            >
                <option value="">All</option>
                <option v-for="(word, value) in typeWords" :key="value" :value="value">{{ word }}</option>
            </select>
        </label>

        <label class="flex flex-col gap-1">
            <span class="text-ink">Availability</span>
            <select
                v-model="status"
                name="status"
                class="h-11 rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green"
            >
                <option value="">All</option>
                <option v-for="option in availabilities" :key="option" :value="option">{{ option }}</option>
            </select>
        </label>

        <label class="flex flex-col gap-1">
            <span class="text-ink">Sort by</span>
            <select
                v-model="sort"
                name="sort"
                class="h-11 rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green"
            >
                <option value="">Name</option>
                <option v-for="(label, value) in sorts" :key="value" :value="value">{{ label }}</option>
            </select>
        </label>

        <button type="submit" class="enamel h-11 rounded-full bg-chrome px-3 py-1.5 font-semibold text-on-chrome">
            Filter
        </button>
    </form>

    <p v-if="errors.rarity" class="mt-2 text-sm text-risk">Rarity: {{ errors.rarity }}</p>
    <p v-if="errors.type" class="mt-2 text-sm text-risk">Type: {{ errors.type }}</p>
    <p v-if="errors.status" class="mt-2 text-sm text-risk">Availability: {{ errors.status }}</p>
    <p v-if="errors.sort" class="mt-2 text-sm text-risk">Sort by: {{ errors.sort }}</p>

    <p v-if="loading" role="status" class="mt-4 text-sm text-ink-muted">Loading results…</p>

    <template v-if="cards.data.length === 0">
        <!-- The state a Trainer meets before a fetch, stated as itself rather than as an empty result.
             Both commands are named because one of them is a trap: the snapshot short-circuit keys on
             the document's hash under today's date in a disk shared by every database in the working
             tree, so a second database asked the same day is told "unchanged" and stays empty. KI-27. -->
        <p
            v-if="totalCount === 0"
            class="mt-8 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted"
        >
            The support-card catalog holds no rows yet. Run `php artisan uma:fetch gametora-support-cards`
            to fill it, or `php artisan uma:reparse gametora-support-cards` if a fetch says the document
            is unchanged.
        </p>
        <p
            v-else
            class="mt-8 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted"
        >
            Nothing matches {{ askedFor }}. If a card you expect is not in the support-card catalog,
            that is a gap in the fetched data rather than a mistake in the query.
        </p>
    </template>

    <template v-else>
        <p class="mt-6 text-sm text-ink-muted">{{ cards.total }} of {{ totalCount }} support cards</p>

        <ul id="support-card-results" class="mt-2 divide-y divide-rule rounded-md border border-rule bg-raised">
            <li
                v-for="card in cards.data"
                :key="card.id"
                class="flex flex-wrap items-baseline gap-x-2 gap-y-1 px-4 py-3 text-sm"
            >
                <!-- The row thumbnail (`ADR-0021` read half, `design-2.0` §45a "Support-card index, card
                 row"): a `size-12` leading cell before the card's name link.

                 Clickable, because §45a gives this slot the click action *navigates to
                 support-card detail*. The destination is the row's own `url` — the local primary
                 key, not the `support_id` the frame is keyed on — so the frame and the name beside
                 it lead to the same place, which is what makes WCAG 2.5.3 satisfiable at all: the
                 link's accessible name has to contain the visible label, so `linkLabel` is the
                 same `card.name` this row prints. `alt` is empty because that name is already
                 printed beside the frame, so a screen reader hears it once, from the link label,
                 rather than twice.

                 This is the one slot of the five that is genuinely a second control pointing at
                 the row's destination. §45a sanctions it and the name-bearing label is what keeps
                 it findable rather than anonymous; a frame with `alt=""` and no label would be an
                 unnamed link and fail 4.1.2.

                 Renders a transparent reserved cell when the mirror holds no file, so the name
                 link stays at one x rather than sliding 56px left. Nothing is painted, so
                 `DESIGN.md` §4.7's absence rule still holds: no frame, no grey box, no glyph. -->
                <ArtworkSlot
                    :url="card.artworkURL"
                    alt=""
                    size="size-12"
                    :href="card.url"
                    :link-label="card.name"
                    reserve
                />

                <a :href="card.url" class="font-semibold text-ink-strong hover:underline">{{ card.name }}</a>

                <RarityChip :label="card.rarity_label" :stars="card.rarity_stars" />

                <span class="rounded-full bg-sunken px-2 py-0.5 text-xs text-ink-strong">{{ card.rarity_word }}</span>
                <span class="rounded-full bg-sunken px-2 py-0.5 text-xs text-ink-strong">{{ card.type_label }}</span>

                <!-- The word carries the state on its own, so no colour role and no tooltip: the
                     column already states it in the client's own terms. -->
                <span class="ml-auto text-xs text-ink-muted">{{ card.release_status }}</span>

                <span
                    v-if="card.effects.length > 0"
                    class="flex w-full flex-wrap gap-x-3 gap-y-0.5 font-mono text-xs text-ink-muted"
                >
                    <!-- A dictionary row that is absent is marked, not labelled: the id is the
                         source's own number, so naming it states a fact rather than inventing a word. -->
                    <span v-for="effect in card.effects" :key="effect.effect_id">
                        {{ effect.name ?? `[Unverified] effect ${effect.effect_id}` }} {{ effect.display }}
                    </span>
                </span>
            </li>
        </ul>

        <nav v-if="cards.links.length > 3" aria-label="Pagination" class="mt-4 flex flex-wrap gap-2 text-sm">
            <template v-for="(link, index) in cards.links" :key="index">
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

    <p class="mt-6 max-w-3xl text-xs text-ink-muted">
        Effect figures are each effect's highest stated anchor, not an interpolation, and the anchor a card
        stops at is not always level 50. The type word is the client's: the source's key for Wit is
        `intelligence` and for Pal is `friend`.
    </p>
</template>
