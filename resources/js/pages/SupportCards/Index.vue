<script setup lang="ts">
/*
 * The support-card catalog, rebuilt to `support-cards.html` (plan Slice 8).
 *
 * One row per card, because the detail page owns the level ladder: the index is for comparison, so it
 * carries the type, the rarity, the friendship bonus and the card's key effects at cap. Effect chips
 * are words and never icons — "Initial Friendship Gauge" is the label a Trainer recognises in the
 * client. A Pal card's missing friendship bonus is printed as the word None with its reason, because
 * for that type the absence is the deciding fact.
 *
 * Two catalogue-wide facts sit under the table, where no single row can carry them: the seven types
 * with their per-rarity and Global SSR counts (`support-cards.html:152-167`), and the five effects that
 * decide a deck slot (`:169-184`). The second card's closing note states how many dictionary effects no
 * card here carries, so a documented mechanism with zero coverage is named rather than silently
 * omitted.
 */
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import DatabaseNav from '../../components/database/DatabaseNav.vue';
import ArtworkSlot from '../../components/ArtworkSlot.vue';
import Pagination from '../../components/ui/Pagination.vue';
import RarityChip from '../../components/RarityChip.vue';
import SupportTypeMark from '../../components/support/SupportTypeMark.vue';
import BaseSelect from '../../components/Form/BaseSelect.vue';
import BaseInput from '../../components/Form/BaseInput.vue';
import BaseButton from '../../components/Form/BaseButton.vue';
import { LinkButton } from '../../components/Form';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Effect {
    effect_id: number;
    name: string | null;
    display: string;
}

interface Card {
    id: number;
    name: string;
    char_name: string;
    title: string | null;
    url: string;
    artworkURL: string | null;
    rarity_label: string;
    rarity_stars: string;
    rarity_word: string;
    type: string;
    type_label: string;
    release_status: string;
    effects: Effect[];
    /** The friendship bonus at cap, or null when this card states none. */
    friendship: { display: string } | null;
}

interface Paginator<T> {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

interface TypeCount {
    type: string;
    label: string;
    r: number;
    sr: number;
    ssr: number;
    global_ssr: number;
}

interface DecidingEffect {
    effect_id: number;
    name: string;
    description: string;
}

interface UncoveredEffect {
    effect_id: number;
    name: string | null;
}

const props = defineProps<{
    cards: Paginator<Card>;
    q: string | null;
    rarity: string | null;
    type: string | null;
    status: string | null;
    /** The selected server, Global or JP; the wireframe defaults to Global. */
    server: string;
    servers: string[];
    /** How many cards the selected server holds, counted on the facet alone. */
    serverCount: number;
    sort: string | null;
    rarityWords: Record<string, string>;
    typeWords: Record<string, string>;
    availabilities: string[];
    sorts: Record<string, string>;
    typeCounts: TypeCount[];
    decidingEffects: DecidingEffect[];
    uncoveredEffects: UncoveredEffect[];
    totalCount: number;
    askedFor: string;
}>();

const page = usePage();
const errors = computed(() => (page.props.errors as Record<string, string | undefined> | undefined) ?? {});

const q = ref(props.q ?? '');
const rarity = ref(props.rarity ?? '');
const type = ref(props.type ?? '');
const server = ref(props.server);
const sort = ref(props.sort ?? '');
const loading = ref(false);

function params(): Record<string, string | number | undefined> {
    return {
        q: q.value || undefined,
        rarity: rarity.value || undefined,
        type: type.value || undefined,
        server: server.value || undefined,
        sort: sort.value || undefined,
    };
}

function applyFilters(): void {
    router.get('/support-cards', params(), {
        preserveState: true,
        preserveScroll: true,
        onStart: () => {
            loading.value = true;
        },
        onFinish: () => {
            loading.value = false;
        },
    });
}

function goToPage(page: number): void {
    router.get('/support-cards', { ...params(), page }, { preserveState: true, preserveScroll: true });
}

const fieldLabel = 'text-xs font-semibold text-ink-muted';
const th = 'border-b border-rule px-2 py-2 text-left text-xs font-semibold text-ink-strong';
const thNum = 'border-b border-rule px-2 py-2 text-right text-xs font-semibold text-ink-strong';
const td = 'border-b border-rule px-2 py-3 align-top text-sm text-ink';
const tdNum = 'border-b border-rule px-2 py-3 text-right align-top font-mono text-sm text-ink';

/*
 * A Pal card states no friendship bonus at all, and that absence is the reason the type exists in a
 * deck comparison. So the word printed is the client's own "None", and the sentence beside it names
 * the cause; no other card type gets that word, because for a stat card a missing bonus would be a
 * data gap rather than a property of the type.
 */
const isPal = (card: Card): boolean => card.type === 'friend';
</script>

<template>
    <AppLayout :breadcrumb="[{ label: 'Library' }, { label: 'Support cards' }]">
        <Head title="Support cards" />
        <template #head>
            <PageHeader
                eyebrow="Support cards"
                title="Every support card"
                :description="`${props.totalCount} catalogued. The type counts and the Global split sit below the table, because a deck from a JP guide can name a card Global has not received.`"
            />
        </template>

        <DatabaseNav />

        <!-- One inline filter row, in the wireframe's order: the name search first, then Type and
             Rarity, then Server and Sort (`support-cards.html:72-78`). Every control the server
             answers is here; the query's own `q` is a grouped match on the three stored name fields.
             The Type/Rarity pair was reversed until R6 while this comment claimed the wireframe's
             order — corrected in both places together. -->
        <form
            class="flex flex-wrap items-end gap-3"
            :aria-busy="loading ? 'true' : null"
            @submit.prevent="applyFilters"
        >
            <BaseInput
                id="support-card-q"
                v-model="q"
                name="q"
                type="search"
                label="Search"
                placeholder="Card name"
                :error="errors.q"
            />
            <!-- Type BEFORE Rarity, which is the wireframe's order (`support-cards.html:73-77`:
                 Search, Type, Rarity, Server, Clear). The page shipped them the other way round while
                 its own comment claimed it was "in the wireframe's order" — the comment was the
                 defect, so the order is corrected here and the comment with it. -->
            <BaseSelect
                id="support-card-type"
                v-model="type"
                name="type"
                label="Type"
                :options="[{ value: '', label: 'All' }, ...Object.entries(props.typeWords).map(([value, word]) => ({ value, label: word }))]"
                placeholder="All"
                :error="errors.type"
            />
            <BaseSelect
                id="support-card-rarity"
                v-model="rarity"
                name="rarity"
                label="Rarity"
                :options="[{ value: '', label: 'All' }, ...Object.entries(props.rarityWords).map(([value, word]) => ({ value, label: word }))]"
                placeholder="All"
                :error="errors.rarity"
            />
            <!-- The wireframe's Server facet: Global or JP, Global selected, no all-servers option.
                 The table then does not repeat availability on every row, because the list is
                 already one server's. -->
            <BaseSelect
                id="support-card-server"
                v-model="server"
                name="server"
                label="Server"
                :options="props.servers.map((option) => ({ value: option, label: option }))"
                placeholder="Global"
                :error="errors.server"
            />
            <BaseSelect
                id="support-card-sort"
                v-model="sort"
                name="sort"
                label="Sort by"
                :options="[{ value: '', label: 'Name' }, ...Object.entries(props.sorts).map(([value, label]) => ({ value, label }))]"
                placeholder="Name"
                :error="errors.sort"
            />
            <BaseButton type="submit" variant="primary" size="md" :loading="loading">
                Filter
            </BaseButton>
            <a
                href="/support-cards"
                class="inline-flex min-h-11 items-center px-2 text-sm text-ink-muted hover:underline transition-colors duration-150 ease-out"
            >Clear</a>
        </form>

        <p v-if="loading" role="status" class="mt-4 text-sm text-ink-muted">Loading results…</p>

        <div v-if="props.cards.data.length === 0" class="mt-6 rounded-md border border-dashed border-rule bg-panel p-6">
            <p class="text-sm font-semibold text-ink-strong">
                <template v-if="props.totalCount === 0">The support-card catalog holds no rows yet</template>
                <template v-else>Nothing matches {{ props.askedFor }}</template>
            </p>
            <p class="mt-1 text-sm text-ink-muted">
                <template v-if="props.totalCount === 0">
                    Fetch the support-card document to fill it. Until that fetch runs, this screen has
                    nothing to compare.
                </template>
                <template v-else>
                    Clear the filters to see every card in the catalogue, or widen one of them. A card
                    you expect and cannot find is a gap in the fetched data rather than a mistake in
                    the query.
                </template>
            </p>
        </div>

        <template v-else>
            <div class="card mt-4 overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr>
                            <th :class="th">Card</th>
                            <th :class="th">Type</th>
                            <th :class="th">Rarity</th>

                            <th :class="th">Friendship bonus</th>
                            <th :class="th">Key effects</th>
                            <th :class="thNum"><span class="sr-only">Open the card</span></th>
                        </tr>
                    </thead>
                    <!-- The row id is the spec's and the pager's anchor: the table body is the list. -->
                    <tbody id="support-card-results">
                        <tr v-for="card in props.cards.data" :key="card.id" class="last:border-b-0">
                            <td :class="td">
                                <span class="flex items-center gap-2">
                                    <!-- The one clickable frame of the ported pages: it leads to the same
                                         place the name beside it does, and its accessible name is the row's
                                         printed name verbatim (WCAG 2.5.3). A file the mirror lacks paints
                                         nothing and reserves the cell. -->
                                    <ArtworkSlot
                                        :url="card.artworkURL"
                                        alt=""
                                        size="size-12"
                                        :href="card.url"
                                        :link-label="card.name"
                                        reserve
                                    />
                                    <span class="min-w-0">
                                        <Link
                                            :href="card.url"
                                            class="inline-flex min-h-11 items-center font-semibold text-ink-strong hover:underline transition-colors duration-150 ease-out"
                                        >{{ card.char_name }}</Link>
                                        <span v-if="card.title" class="block text-xs text-ink-muted">{{ card.title }}</span>
                                    </span>
                                </span>
                            </td>
                            <td :class="td">
                                <SupportTypeMark :type="card.type" :label="card.type_label" />
                            </td>
                            <td :class="td">
                                <span class="flex items-baseline gap-2">
                                    <RarityChip :label="card.rarity_label" :stars="card.rarity_stars" />
                                    <span>{{ card.rarity_word }}</span>
                                </span>
                            </td>
                            <td :class="td">
                                <span v-if="card.friendship" class="font-mono">{{ card.friendship.display }}</span>
                                <template v-else-if="isPal(card)">
                                    <span class="pill pill-caution">None</span>
                                    <span class="mt-1 block text-xs text-ink-muted">A Pal card provides no friendship bonus.</span>
                                </template>
                                <!-- A stat card with no bonus is a data gap, so the marker says which
                                     absence this is: the tooltip and its screen-reader twin are one
                                     statement in two media (DESIGN.md §5). -->
                                <template v-else>
                                    <span title="The source states no friendship bonus for this card.">N/A</span><span class="sr-only">: the source states no friendship bonus for this card.</span>
                                </template>
                            </td>
                            <td :class="td">
                                <span v-if="card.effects.length > 0" class="flex flex-wrap gap-1.5">
                                    <span v-for="effect in card.effects" :key="effect.effect_id" class="tag auto">
                                        {{ effect.name ?? `Unnamed effect ${effect.effect_id}` }} {{ effect.display }}
                                    </span>
                                </span>
                                <span v-else class="text-xs text-ink-muted">The source states no effect anchor for this card.</span>
                            </td>
                            <td :class="[td, 'text-right']">
                                <LinkButton :href="card.url" variant="secondary" size="sm">Open</LinkButton>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-rule px-2 py-2">
                    <p class="text-xs text-ink-muted" data-field="server-scope">
                        {{ props.serverCount }} cards on [{{ props.server }}]
                    </p>
                    <p class="text-xs text-ink-muted">{{ props.cards.total }} shown</p>
                </div>

                <!-- The wireframe puts the pager inside the table card, bottom-right
                     (`support-cards.html:150-163`), rather than as a band under it. -->
                <div class="px-2 pb-2">
                    <Pagination
                        :page="props.cards.current_page"
                        :pages="props.cards.last_page"
                        :from="props.cards.from ?? 0"
                        :to="props.cards.to ?? 0"
                        :total="props.cards.total"
                        noun="cards"
                        @navigate="goToPage"
                    />
                </div>
            </div>

            <div class="mt-4 grid gap-4 g-2">
                <div class="card">
                    <h2>The seven types</h2>
                    <table class="w-full border-collapse">
                        <thead>
                            <tr>
                                <th :class="th">Type</th>
                                <th :class="thNum">JP R/SR/SSR</th>
                                <th :class="thNum">Global SSR</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in props.typeCounts" :key="row.type">
                                <td :class="td">
                                    <SupportTypeMark :type="row.type" :label="row.label" />
                                </td>
                                <td :class="tdNum">{{ row.r }} / {{ row.sr }} / {{ row.ssr }}</td>
                                <td :class="tdNum">{{ row.global_ssr }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="note mt-3">
                        <span class="g" aria-hidden="true">○</span>
                        <span>
                            Counts are records in the export; Global counts cards carrying a Global
                            release date, so the two columns together are the JP/Global asymmetry.
                        </span>
                    </p>
                </div>

                <div class="card">
                    <h2>Effects that decide a deck slot</h2>
                    <table class="w-full border-collapse">
                        <tbody>
                            <tr v-for="effect in props.decidingEffects" :key="effect.effect_id">
                                <td :class="td">{{ effect.name }}</td>
                                <td :class="td">{{ effect.description }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="note mt-3">
                        <span class="g" aria-hidden="true">○</span>
                        <span>
                            <b>Cap-setting effects exist in the vocabulary but are carried by zero of the
                            {{ props.totalCount }} cards.</b>
                            {{ props.uncoveredEffects.length }} effects in the dictionary have no card behind
                            them here, so no screen offers a line for a cap raised by support cards.
                        </span>
                    </p>
                </div>
            </div>
        </template>

        <p class="mt-6 max-w-3xl text-xs text-ink-muted">
            Effect figures are each effect's highest stated anchor, not an interpolation, and the anchor a
            card stops at is not always level 50. The type word is the client's: the source's key for Wit
            is intelligence and for Pal is friend.
        </p>
    </AppLayout>
</template>
