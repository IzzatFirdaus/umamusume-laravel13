<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, nextTick, ref } from 'vue';
import AppLayout from '../../layouts/AppLayout.vue';
import ArtworkSlot from '../../components/ArtworkSlot.vue';
import DeckAnalysis from '../../components/support/DeckAnalysis.vue';
import SupportSlot from '../../components/support/SupportSlot.vue';
import SupportTypeMark from '../../components/support/SupportTypeMark.vue';

/*
 * The deck builder (SCREEN-007, `SCR-CAR-007`).
 *
 * The one structural decision on this page: **the six selections live in the query string, not in
 * component state.** Every picker action is a `router.get` onto this same route, so a filter change, a
 * page change or an equip would otherwise wipe the five slots the Trainer had not submitted yet. The
 * URL is what carries them, which is the same move `DeckPanel.vue` makes for one slot at a time with
 * `open_url`, lifted to all six, and it is what lets the picker be a real paginated query instead of
 * five hundred option nodes in a `<select>`.
 *
 * That is also why replacement has no drag and no per-slot select. The picker row's Equip button writes
 * `deck[<slot>]` into the URL, the server re-renders, and focus comes back to the slot row that
 * changed. A drag would be a mouse-only path to the same write, and the deck is six ordered positions,
 * not a set to be rearranged.
 */
interface Effect {
    effect_id: number;
    name: string | null;
    display: string;
    value: number;
    symbol: string | null;
    calc: string | null;
}

interface Card {
    id: number;
    name: string;
    name_ja: string | null;
    title_ja: string | null;
    url: string;
    artworkURL: string | null;
    rarity_word: string;
    type: string;
    type_label: string;
    scenario_link: 'linked' | 'not_linked' | 'unknown';
    scenario_link_note: string | null;
    effects: Effect[];
}

interface Slot {
    position: number;
    label: string;
    is_friend: boolean;
    selected: string;
    card: Card | null;
    ownership: 'OWNED';
}

interface Paginator<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
}

interface EffectLine {
    effect_id: number;
    name: string | null;
    mode: 'flat' | 'additive' | 'multiplicative';
    cards: { card_name: string; display: string }[];
    total: { value: number; display: string; basis: string } | null;
}

const props = defineProps<{
    run: {
        id: number;
        name: string | null;
        status_label: string;
        scenario: string | null;
        scenario_label: string | null;
        run_url: string;
    };
    slots: Slot[];
    picker: {
        page: Paginator<Card>;
        offered: number;
        type: string | null;
        rarity: string | null;
        status: string | null;
        query: string | null;
        fillingSlot: number;
        askedFor: string;
    };
    types: { key: string; label: string }[];
    rarities: Record<string, string>;
    availabilities: string[];
    analysis: {
        covered: number;
        categories: Record<string, { label: string; blank: boolean; effects: EffectLine[] }>;
        uncategorised: EffectLine[];
        strengths: string[];
        weaknesses: string[];
    };
    action: string;
}>();

const page = usePage();
const errors = computed(
    () => (page.props.errors as Record<string, string | undefined> | undefined) ?? {},
);

/**
 * The ownership flags. Client-side only, and the page says so under the deck: `deck_slots` has no
 * column for the flag, and adding one needs the owner's approval, so the value a slot ships with is the
 * only value the run can be read as. Held here rather than in a prop so the toggle responds at once
 * without a round trip that would have nowhere to store the answer.
 */
const ownership = ref<Record<number, 'OWNED' | 'RENTED'>>(
    Object.fromEntries(props.slots.map((slot) => [slot.position, slot.ownership])),
);

const type = ref(props.picker.type ?? '');
const rarity = ref(props.picker.rarity ?? '');
const status = ref(props.picker.status ?? '');
const search = ref(props.picker.query ?? '');
const loading = ref(false);

/** The deck the form posts: one entry per position, blank meaning "not equipped". */
const form = useForm({
    deck: Object.fromEntries(
        props.slots.map((slot) => [slot.position, { support_card_id: slot.selected }]),
    ) as Record<number, { support_card_id: string }>,
});

/**
 * The current deck as the query string carries it, so a picker action can change one entry and keep the
 * other five. Read from the slots rather than from `form` because the form's state is the *submitted*
 * deck, and the point of the query string is the picks that are not submitted yet.
 */
function deckQuery(): Record<string, string> {
    const deck: Record<string, string> = {};

    for (const slot of props.slots) {
        if (slot.selected !== '') {
            deck[slot.position] = slot.selected;
        }
    }

    return deck;
}

function filterQuery(): Record<string, string | undefined> {
    return {
        type: props.picker.type ?? undefined,
        rarity: props.picker.rarity ?? undefined,
        status: props.picker.status ?? undefined,
        query: props.picker.query ?? undefined,
    };
}

/**
 * The one navigation on this page. `deck` is always the whole six-slot selection, so a filter change, a
 * page change and an equip all leave the other five positions exactly where the Trainer put them, and
 * `focusSlot` is where focus lands afterwards.
 */
function go(
    params: Record<string, string | number | undefined>,
    deck: Record<string, string>,
    focusSlot: number,
): void {
    router.get(
        `/training-runs/${props.run.id}/deck`,
        { ...params, deck },
        {
            preserveState: true,
            preserveScroll: true,
            onStart: () => {
                loading.value = true;
            },
            onFinish: () => {
                loading.value = false;
                void nextTick(() => {
                    document.getElementById(`deck-slot-${focusSlot}`)?.focus();
                });
            },
        },
    );
}

function applyFilters(): void {
    go(
        {
            type: type.value || undefined,
            rarity: rarity.value || undefined,
            status: status.value || undefined,
            query: search.value || undefined,
        },
        deckQuery(),
        pickerSlot(),
    );
}

/** The Trainer's first click on a Replace button decides which slot the picker writes to. */
function startReplace(position: number): void {
    go({ ...filterQuery(), slot: position }, deckQuery(), position);
}

/** Equip writes one position and leaves the filters and the other five exactly where they were. */
function equip(cardId: number): void {
    const slot = pickerSlot();

    go(
        { ...filterQuery(), slot },
        { ...deckQuery(), [slot]: String(cardId) },
        slot,
    );
}

function clearSlot(position: number): void {
    const deck = deckQuery();
    delete deck[position];

    go({ ...filterQuery(), slot: position }, deck, position);
}

function pickerSlot(): number {
    return props.picker.fillingSlot;
}

function save(): void {
    form.post(props.action);
}
</script>

<template>
    <AppLayout>
        <Head title="Support deck" />
        <template #title>Support deck</template>

        <h2 class="text-2xl font-semibold text-ink-strong">Support deck</h2>

        <p class="mt-1 text-sm text-ink-muted">
            <template v-if="run.name">
                {{ run.name }} &middot; {{ run.status_label }} &middot;
            </template>
            <template v-if="run.scenario_label">
                {{ run.scenario_label }}
            </template>
            <template v-else> No scenario chosen</template>
            &middot;
            <a :href="run.run_url" class="underline">Open the run</a>
        </p>

        <p v-if="errors.deck" class="mt-3 rounded border border-goal-line bg-green-tint px-3 py-2 text-sm text-ink" role="alert">
            {{ errors.deck }}
        </p>

        <div class="mt-5 grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]">
            <!-- The six slots -->
            <section aria-labelledby="deck-slots-heading">
                <h3 id="deck-slots-heading" class="text-lg font-semibold text-ink-strong">The six slots</h3>

                <p class="mt-1 text-sm text-ink-muted">
                    A deck is six cards. Position six is the friend slot, and that role belongs to the
                    position: any card may sit in it.
                </p>

                <ul class="mt-3 space-y-2">
                    <SupportSlot
                        v-for="slot in slots"
                        :key="slot.position"
                        v-bind="slot"
                        :ownership="ownership[slot.position] ?? 'OWNED'"
                        :filling-slot="picker.fillingSlot"
                        @replace="startReplace"
                        @ownership="(position, value) => (ownership[position] = value)"
                    />
                </ul>

                <!-- The ownership flag has nowhere to go yet, and a control that looked like it saved
                     would be a lie. Said here, in words, next to the six toggles it describes. -->
                <p class="mt-3 rounded-md border border-dashed border-rule bg-raised p-3 text-xs text-ink-muted">
                    The run record stores no field for the owned or rented flag yet, so the six toggles
                    above hold their choice on this page only and a reload returns them to Owned. Nothing
                    is posted for them, so nothing is claimed to have been saved.
                </p>

                <form class="mt-4 flex flex-wrap items-center gap-3" @submit.prevent="save">
                    <button
                        type="submit"
                        class="enamel h-11 rounded-full bg-chrome px-5 font-semibold text-on-chrome"
                        :disabled="form.processing"
                    >
                        Confirm deck
                    </button>
                    <p v-if="form.processing" role="status" class="text-sm text-ink-muted">Saving the deck…</p>
                    <span v-if="analysis.covered > 0" class="text-xs text-ink-muted">
                        Confirming replaces the whole deck: slots left empty are cleared.
                    </span>
                </form>
            </section>

            <!-- The picker -->
            <section aria-labelledby="deck-picker-heading">
                <h3 id="deck-picker-heading" class="text-lg font-semibold text-ink-strong">Choose a card</h3>

                <p class="mt-1 text-sm text-ink-muted">
                    Choose a slot on the left, then equip a card here. A card already in another slot
                    cannot be equipped twice, because duplicates are spent on breaking it.
                </p>

                <form class="mt-3 flex flex-wrap items-end gap-3 text-sm" @submit.prevent="applyFilters">
                    <label class="flex flex-col gap-1">
                        <span class="text-ink">Type</span>
                        <select
                            v-model="type"
                            name="type"
                            class="h-11 rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green"
                        >
                            <option value="">All</option>
                            <option v-for="option in types" :key="option.key" :value="option.key">
                                {{ option.label }}
                            </option>
                        </select>
                    </label>

                    <label class="flex flex-col gap-1">
                        <span class="text-ink">Rarity</span>
                        <select
                            v-model="rarity"
                            name="rarity"
                            class="h-11 rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green"
                        >
                            <option value="">All</option>
                            <option v-for="(word, value) in rarities" :key="value" :value="value">{{ word }}</option>
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
                        <span class="text-ink">Name contains</span>
                        <input
                            v-model="search"
                            name="query"
                            type="search"
                            class="h-11 rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green"
                        />
                    </label>

                    <button
                        type="submit"
                        class="enamel h-11 rounded-full bg-chrome px-3 py-1.5 font-semibold text-on-chrome"
                    >
                        Filter
                    </button>
                </form>

                <p v-if="errors.type" class="mt-2 text-sm text-risk">Type: {{ errors.type }}</p>
                <p v-if="errors.rarity" class="mt-2 text-sm text-risk">Rarity: {{ errors.rarity }}</p>
                <p v-if="errors.status" class="mt-2 text-sm text-risk">Availability: {{ errors.status }}</p>
                <p v-if="errors.query" class="mt-2 text-sm text-risk">Name: {{ errors.query }}</p>

                <p v-if="loading" role="status" class="mt-3 text-sm text-ink-muted">Loading cards…</p>

                <p class="mt-3 text-sm text-ink-muted">
                    {{ picker.page.total }} of {{ picker.offered }} cards available
                </p>

                <p
                    v-if="picker.page.data.length === 0"
                    class="mt-3 rounded-md border border-dashed border-rule bg-raised p-5 text-sm text-ink-muted"
                >
                    <template v-if="picker.offered === 0">
                        The support-card catalog holds no rows yet. Run
                        <code>php artisan uma:fetch gametora-support-cards</code> to fill it, or
                        <code>php artisan uma:reparse gametora-support-cards</code> if a fetch says the
                        document is unchanged.
                    </template>
                    <template v-else>
                        Nothing matches {{ picker.askedFor }}. A card you expect to be missing is a gap in
                        the fetched data rather than a mistake in the filter.
                    </template>
                </p>

                <template v-else>
                    <ul id="deck-picker-results" class="mt-2 divide-y divide-rule rounded-md border border-rule bg-raised">
                        <li
                            v-for="card in picker.page.data"
                            :key="card.id"
                            class="flex flex-wrap items-baseline gap-x-3 gap-y-1 px-3 py-3 text-sm"
                        >
                            <!-- The row thumbnail, keyed on the publisher's `support_id` and absent-safe
                                 (`ADR-0021` read half, `design-2.0` §45a "Support-card index, card row").
                                 `alt=""` because the row prints the name beside it, and `linkLabel`
                                 carries the anchor's accessible name so 4.1.2 and 2.5.3 both hold. -->
                            <ArtworkSlot
                                :url="card.artworkURL"
                                alt=""
                                size="size-12"
                                :href="card.url"
                                :link-label="card.name"
                                reserve
                            />

                            <a :href="card.url" class="font-semibold text-ink-strong hover:underline">{{ card.name }}</a>
                            <span class="font-mono text-xs text-ink-muted">{{ card.rarity_word }}</span>
                            <SupportTypeMark :type="card.type" :label="card.type_label" />
                            <span v-if="card.scenario_link === 'linked'" class="rounded-full bg-green-tint px-2 py-0.5 text-xs font-semibold text-ink-strong">
                                Scenario Link
                            </span>

                            <p
                                v-if="card.effects.length > 0"
                                class="flex w-full flex-wrap gap-x-3 gap-y-0.5 font-mono text-xs text-ink-muted"
                            >
                                <span v-for="effect in card.effects" :key="effect.effect_id">
                                    {{ effect.name ?? `[Unverified] effect ${effect.effect_id}` }} {{ effect.display }}
                                </span>
                            </p>

                            <div class="ml-auto flex shrink-0 items-center gap-1">
                                <button
                                    type="button"
                                    class="min-h-11 rounded-full border border-rule px-3 text-sm font-semibold text-ink hover:bg-sunken focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green"
                                    @click="equip(card.id)"
                                >
                                    Equip to {{ slots.find((slot) => slot.position === picker.fillingSlot)?.label }}
                                    <span class="sr-only">: {{ card.name }}</span>
                                </button>
                                <button
                                    v-if="slots.some((slot) => slot.selected === String(card.id))"
                                    type="button"
                                    class="min-h-11 rounded-full border border-rule px-3 text-sm font-semibold text-ink hover:bg-sunken focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green"
                                    @click="clearSlot(slots.find((slot) => slot.selected === String(card.id))!.position)"
                                >
                                    Clear
                                    <span class="sr-only"> {{ card.name }} from the slot it is in</span>
                                </button>
                            </div>
                        </li>
                    </ul>

                    <nav v-if="picker.page.links.length > 3" aria-label="Card pages" class="mt-3 flex flex-wrap gap-2 text-sm">
                        <template v-for="(link, index) in picker.page.links" :key="index">
                            <a
                                v-if="link.url"
                                :href="link.url"
                                :aria-current="link.active ? 'page' : undefined"
                                class="min-h-11 rounded-md border border-rule px-3 py-2 text-ink hover:bg-raised"
                                :class="link.active ? 'bg-raised font-bold text-ink-strong' : ''"
                                v-html="link.label"
                            />
                            <span v-else class="px-3 py-2 text-ink-muted" v-html="link.label" />
                        </template>
                    </nav>
                </template>
            </section>
        </div>

        <DeckAnalysis
            :covered="analysis.covered"
            :categories="analysis.categories"
            :uncategorised="analysis.uncategorised"
            :strengths="analysis.strengths"
            :weaknesses="analysis.weaknesses"
        />
    </AppLayout>
</template>
