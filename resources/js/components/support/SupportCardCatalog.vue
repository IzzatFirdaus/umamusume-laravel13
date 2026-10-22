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
import Pagination from '../ui/Pagination.vue';
import RarityChip from '../RarityChip.vue';
import ProvenanceBadge from '../ProvenanceBadge.vue';
import SparkChip from '../SparkChip.vue';
import BaseSelect from '../Form/BaseSelect.vue';
import BaseButton from '../Form/BaseButton.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
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

/**
 * The canonical pager owns the range, so the page owns the request. The pager it replaces was a set
 * of Inertia links built from the server's own paginator; this asks for the same list one page
 * further, carrying the filters the form holds, so a reader who filtered and then paged keeps both.
 */
function goToPage(page: number): void {
    router.get(
        props.filterPath,
        {
            rarity: rarity.value || undefined,
            type: type.value || undefined,
            status: status.value || undefined,
            sort: sort.value || undefined,
            page,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
}
</script>

<template>
    <!-- The heading names the catalog this component lists, and it must differ from the page's own
         h1: the hosts are titled "Support cards" and "Support Cards", and a screen that says the
         same thing twice is the defect `tests/browser/ui-hygiene.spec.ts` reads every route for. -->
    <h2 class="text-lg font-semibold text-ink-strong">Support card catalog</h2>

    <!-- Control height is `h-11`, the value DESIGN.md §6.14 fixes for a form input and the one
         skills/index already carries, so the two filter surfaces measure the same in a browser. -->
    <div class="mt-4 rounded-md border border-rule bg-raised p-4">
        <form
            class="flex flex-wrap items-end gap-3 text-sm"
            :aria-busy="loading ? 'true' : null"
            @submit.prevent="applyFilters"
        >
            <BaseSelect
                v-model="rarity"
                id="support-card-rarity"
                name="rarity"
                label="Rarity"
                :options="[{ value: '', label: 'All' }, ...Object.entries(rarityWords).map(([value, word]) => ({ value, label: word }))]"
                placeholder="All"
                :error="errors.rarity"
            />
            <BaseSelect
                v-model="type"
                id="support-card-type"
                name="type"
                label="Type"
                :options="[{ value: '', label: 'All' }, ...Object.entries(typeWords).map(([value, word]) => ({ value, label: word }))]"
                placeholder="All"
                :error="errors.type"
            />
            <BaseSelect
                v-model="status"
                id="support-card-status"
                name="status"
                label="Availability"
                :options="[{ value: '', label: 'All' }, ...availabilities.map((option) => ({ value: option, label: option }))]"
                placeholder="All"
                :error="errors.status"
            />
            <BaseSelect
                v-model="sort"
                id="support-card-sort"
                name="sort"
                label="Sort by"
                :options="[{ value: '', label: 'Name' }, ...Object.entries(sorts).map(([value, label]) => ({ value, label }))]"
                placeholder="Name"
                :error="errors.sort"
            />
            <BaseButton type="submit" variant="primary" size="md" :loading="loading">
                Filter
            </BaseButton>
        </form>

    </div>

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
            The support-card catalog holds no rows yet. Run <code>php artisan uma:fetch gametora-support-cards</code>
            to fill it, or <code>php artisan uma:reparse gametora-support-cards</code> if a fetch says the document
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

                <!-- The hit area is the link's own: the row is not a target (no `rowHref`, no row
                     handler), so the name link is the only way into the detail. `min-h-11` grows that
                     target to the house 44px bar (DESIGN.md §12, KI-29) without touching the type. -->
                <Link :href="card.url" class="inline-flex min-h-11 items-center font-semibold text-ink-strong hover:underline transition-colors duration-150 ease-out">{{ card.name }}</Link>

                <RarityChip :label="card.rarity_label" :stars="card.rarity_stars" />

                <SparkChip :label="card.rarity_word" size="sm" />
                <SparkChip :label="card.type_label" variant="stat" size="sm" />

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

        <Pagination
            :page="props.cards.current_page"
            :pages="props.cards.last_page"
            :from="props.cards.from ?? 0"
            :to="props.cards.to ?? 0"
            :total="props.cards.total"
            noun="cards"
            @navigate="goToPage"
        />
    </template>

    <p class="mt-6 max-w-3xl text-xs text-ink-muted">
        Effect figures are each effect's highest stated anchor, not an interpolation, and the anchor a card
        stops at is not always level 50. The type word is the client's: the source's key for Wit is
        <code>intelligence</code> and for Pal is <code>friend</code>.
    </p>
</template>
