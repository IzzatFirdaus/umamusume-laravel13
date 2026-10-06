<script setup lang="ts">
/*
 * The career setup wizard's support-deck step, `SCR-CAR-009` (PRD FR-A-4, `ADR-0020` §1, under
 * `ADR-0014`). Step 5 of six.
 *
 * **Six slots, the ownership flag, the seven types, and one option list.** The slot rows are the run
 * screen's own `SupportSlot.vue`, so the friend role, the Scenario Link's three states and the at-cap
 * effect lines read identically on both surfaces. A deck is six ordered positions and position six is the
 * friend slot whatever card sits in it (`ADR-0014` correction 1), so the label comes from the server and the
 * page holds no position arithmetic.
 *
 * **Every choice saves.** The run-scoped builder keeps its six picks in the query string because a run
 * screen has a draft in flight; here the session bag *is* the draft, so equipping, marking a card rented
 * and clearing a slot are each one write and one read-back. Nothing sits in a component that a reload could
 * lose, which is also what carries a value entered on step 4 through this step and back intact.
 *
 * **No assignment is dragged** (WCAG 2.2 SC 2.5.7): a card is chosen from a labelled `<select>` and put in
 * the slot the picker is aimed at, and a keyboard Trainer reaches every one of those controls in the tab
 * order with nothing hidden behind a pointer gesture.
 *
 * **The analysis is the counts `DeckAnalysis` already makes**, from `SupportCardEffects` anchor values, and
 * nothing is added to it: no score, no ranking, no recommended replacement (`ADR-0020` §3, PRD FR-G-4).
 */
import SetupLayout from '../../layouts/SetupLayout.vue';
import DeckAnalysis from '../../components/support/DeckAnalysis.vue';
import SupportSlot from '../../components/support/SupportSlot.vue';
import SupportTypeMark from '../../components/support/SupportTypeMark.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, nextTick, ref } from 'vue';

interface Effect {
    effect_id: number;
    name: string | null;
    display: string;
}

interface SlotCard {
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
    ownership: 'OWNED' | 'RENTED' | null;
    card: SlotCard | null;
}

interface Option {
    id: number;
    name: string;
    label: string;
    type: string;
    type_label: string;
    rarity_word: string;
}

interface EffectLine {
    effect_id: number;
    name: string | null;
    mode: 'flat' | 'additive' | 'multiplicative';
    cards: { card_name: string; display: string }[];
    total: { value: number; display: string; basis: string } | null;
}

const props = defineProps<{
    slots: Slot[];
    types: { key: string; label: string }[];
    picker: {
        options: Option[];
        type: string | null;
        query: string | null;
        fillingSlot: number;
    };
    scenarioLabel: string | null;
    scenarioPending: boolean;
    analysis: {
        covered: number;
        categories: Record<string, { label: string; blank: boolean; effects: EffectLine[] }>;
        uncategorised: EffectLine[];
        strengths: string[];
        weaknesses: string[];
    };
    action: string;
}>();

/**
 * The six slots, posted whole. A blank position is a deliberate clear rather than a missing field, which is
 * what `StoreDeckRequest::prepareForValidation()` reads, and the ownership flag travels with the card it
 * describes: an empty position sends nothing.
 */
const form = useForm({
    deck: Object.fromEntries(
        props.slots.map((slot) => [slot.position, {
            support_card_id: slot.selected,
            ownership: slot.ownership ?? '',
        }]),
    ) as Record<number, { support_card_id: string; ownership: string }>,
    deck_slot: props.picker.fillingSlot,
});

/** Which slot the equip buttons write to. Server-seeded, then moved in place so a Replace is one keystroke. */
const filling = ref(props.picker.fillingSlot);

const type = ref(props.picker.type ?? '');
const search = ref(props.picker.query ?? '');

/**
 * The field-level refusals, minus the whole-deck one the banner above the slots already prints. Rendered
 * beside the picker because a card the catalogue does not hold is the picker's problem, not the slot's.
 */
const fieldErrors = computed(() =>
    Object.entries(form.errors).filter(([field]) => field !== 'deck'),
);

const fillingLabel = (): string =>
    props.slots.find((slot) => slot.position === filling.value)?.label ?? `Slot ${filling.value}`;

/**
 * The one navigation that is not a save: narrowing the picker. The draft already holds every pick, so a
 * filter change has nothing to carry and a GET cannot lose one — which is the whole reason this step can
 * keep its slots out of the URL.
 */
function filter(): void {
    router.get(
        '/career/setup/deck',
        {
            type: type.value || undefined,
            q: search.value || undefined,
            deck_slot: filling.value,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                void nextTick(() => document.getElementById('deck-pick')?.focus());
            },
        },
    );
}

/**
 * Every write goes through here so a Trainer who equips four cards in a row never has to find the save
 * control, and the focus lands back on the slot row that changed (`SupportSlot.vue` puts the `id` on the
 * row itself, which is the focusable element).
 */
function save(after?: number): void {
    const slot = after ?? filling.value;

    form.put(props.action, {
        preserveScroll: true,
        onSuccess: () => {
            nextTick(() => document.getElementById(`deck-slot-${slot}`)?.focus());
        },
    });
}

function equip(ownership: 'OWNED' | 'RENTED'): void {
    const picked = document.getElementById('deck-pick') as HTMLSelectElement | null;
    const cardId = picked?.value ?? '';

    if (cardId === '') {
        return;
    }

    form.deck[filling.value] = { support_card_id: cardId, ownership };
    save();
}

function clearSlot(): void {
    form.deck[filling.value] = { support_card_id: '', ownership: '' };
    save();
}

function replace(position: number): void {
    filling.value = position;
    form.deck_slot = position;
    void nextTick(() => document.getElementById('deck-pick')?.focus());
}

function setOwnership(position: number, ownership: 'OWNED' | 'RENTED'): void {
    form.deck[position] = { ...form.deck[position], ownership };
    save(position);
}
</script>

<template>
    <SetupLayout :step="5">
        <Head title="Support Cards" />
        <template #title>Support Cards</template>

        <p class="max-w-2xl text-sm text-ink-muted">
            A deck is the six cards equipped before the career starts. Position six is the friend slot, and
            that role belongs to the position: any card may sit in it.
        </p>

        <p class="mt-3 text-sm text-ink-muted">
            <template v-if="props.scenarioLabel">{{ props.scenarioLabel }}</template>
            <span
                v-else
                title="No scenario has been chosen in this setup draft yet, so a card's Scenario Link cannot be checked against a linked list."
            >Scenario: N/A</span>
        </p>

        <p
            v-if="form.errors.deck"
            role="alert"
            class="mt-3 rounded border border-risk bg-raised px-3 py-2 text-sm text-risk"
        >
            {{ form.errors.deck }}
        </p>

        <div class="mt-5 grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]">
            <section aria-labelledby="draft-slots-heading">
                <h2 id="draft-slots-heading" class="text-lg font-semibold text-ink-strong">The six slots</h2>

                <ul class="mt-3 space-y-2">
                    <SupportSlot
                        v-for="slot in props.slots"
                        :key="slot.position"
                        :position="slot.position"
                        :label="slot.label"
                        :is-friend="slot.is_friend"
                        :card="slot.card"
                        :ownership="slot.ownership"
                        :filling-slot="filling"
                        @replace="replace"
                        @ownership="setOwnership"
                    />
                </ul>

                <p
                    v-if="form.errors.deck && !form.processing"
                    class="mt-3 rounded-md border border-dashed border-rule bg-raised p-3 text-xs text-ink-muted"
                >
                    A slot that holds a card has to say whether you own it or rented it. Nothing is stored
                    until you choose one of the two.
                </p>

                <!-- Where the flag does and does not go, said next to the six toggles it describes: the
                     draft keeps it, the deck table has no column for it, and inventing one is the owner's
                     call (`ADR-0014`). -->
                <p class="mt-3 rounded-md border border-dashed border-rule bg-raised p-3 text-xs text-ink-muted">
                    The owned or rented flag is kept in this setup draft. The deck table has no column for it
                    yet, so Preflight writes the six cards it can store and the flag stays here until one
                    exists.
                </p>

                <form class="mt-4 flex flex-wrap items-center gap-3" @submit.prevent="save()">
                    <button
                        type="submit"
                        class="enamel h-11 rounded-full bg-chrome px-5 font-semibold text-on-chrome disabled:cursor-wait disabled:bg-disabled"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Saving…' : 'Save deck' }}
                    </button>
                    <p v-if="form.processing" role="status" class="text-sm text-ink-muted">Saving the deck…</p>
                </form>
            </section>

            <section aria-labelledby="draft-pick-heading">
                <h2 id="draft-pick-heading" class="text-lg font-semibold text-ink-strong">Choose a card</h2>

                <p class="mt-1 text-sm text-ink-muted">
                    The picker writes to {{ fillingLabel() }}. Say which it is as you equip it: a card you own
                    or one you rented.
                </p>

                <p class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-ink-muted">
                    <span class="text-xs font-semibold uppercase tracking-wide">The seven support types</span>
                    <span v-for="kind in props.types" :key="kind.key" class="inline-flex items-center gap-1">
                        <SupportTypeMark :type="kind.key" :label="kind.label" />
                    </span>
                </p>

                <form class="mt-3 flex flex-wrap items-end gap-3 text-sm" @submit.prevent="filter">
                    <label class="flex flex-col gap-1">
                        <span class="text-ink">Type</span>
                        <select
                            v-model="type"
                            name="type"
                            class="h-11 rounded-md border border-rule bg-raised px-2 text-ink"
                        >
                            <option value="">All</option>
                            <option v-for="kind in props.types" :key="kind.key" :value="kind.key">
                                {{ kind.label }}
                            </option>
                        </select>
                    </label>

                    <label class="flex flex-col gap-1">
                        <span class="text-ink">Name contains</span>
                        <input
                            v-model="search"
                            name="q"
                            type="search"
                            class="h-11 rounded-md border border-rule bg-raised px-2 text-ink"
                        >
                    </label>

                    <button
                        type="submit"
                        class="enamel h-11 rounded-full bg-chrome px-3 py-1.5 font-semibold text-on-chrome"
                    >
                        Filter
                    </button>
                </form>

                <p v-if="props.picker.type || props.picker.query" class="mt-2 text-xs text-ink-muted">
                    {{ props.picker.options.length }} card{{ props.picker.options.length === 1 ? '' : 's' }}
                    match {{ props.picker.type ? props.types.find((kind) => kind.key === props.picker.type)?.label : 'any type' }}<template
                        v-if="props.picker.query"> and a name containing {{ props.picker.query }}</template>.
                </p>

                <p
                    v-if="props.picker.options.length === 0"
                    class="mt-3 rounded-md border border-dashed border-rule bg-raised p-5 text-sm text-ink-muted"
                >
                    The support-card catalog holds no row matching that. Run
                    <code>php artisan uma:fetch gametora-support-cards</code> to fill it, or
                    <code>php artisan uma:reparse gametora-support-cards</code> if a fetch says the document
                    is unchanged. A card you expect and cannot find is a gap in the fetched data, not a
                    mistake in the filter.
                </p>

                <div v-else class="mt-3 flex flex-wrap items-end gap-3">
                    <label class="flex min-w-0 flex-1 flex-col gap-1 text-sm">
                        <span class="text-ink">Card for {{ fillingLabel() }}</span>
                        <select
                            id="deck-pick"
                            class="h-11 rounded-md border border-rule bg-raised px-2 text-ink"
                        >
                            <option value="">Not equipped</option>
                            <option v-for="card in props.picker.options" :key="card.id" :value="String(card.id)">
                                {{ card.label }}
                            </option>
                        </select>
                    </label>

                    <button
                        type="button"
                        class="min-h-11 rounded-full border border-rule px-3 text-sm font-semibold text-ink hover:bg-sunken"
                        @click="equip('OWNED')"
                    >
                        Equip owned to {{ fillingLabel() }}
                        <span class="sr-only"> the card selected in the picker</span>
                    </button>

                    <button
                        type="button"
                        class="min-h-11 rounded-full border border-rule px-3 text-sm font-semibold text-ink hover:bg-sunken"
                        @click="equip('RENTED')"
                    >
                        Equip rented to {{ fillingLabel() }}
                        <span class="sr-only"> the card selected in the picker</span>
                    </button>

                    <button
                        type="button"
                        class="min-h-11 rounded-full border border-rule px-3 text-sm font-semibold text-ink hover:bg-sunken"
                        @click="clearSlot"
                    >
                        Set {{ fillingLabel() }} to not equipped
                    </button>
                </div>

                <ul class="mt-2 space-y-1">
                    <li
                        v-for="(message, field) in fieldErrors"
                        :key="field"
                        class="text-sm text-risk"
                    >
                        {{ field }}: {{ message }}
                    </li>
                </ul>
            </section>
        </div>

        <DeckAnalysis
            :covered="props.analysis.covered"
            :categories="props.analysis.categories"
            :uncategorised="props.analysis.uncategorised"
            :strengths="props.analysis.strengths"
            :weaknesses="props.analysis.weaknesses"
        />

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <a
                href="/career/setup/legacy"
                class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised"
            >
                Back: Legacy
            </a>
            <span
                class="inline-flex min-h-11 items-center rounded-md px-3 text-sm text-ink-muted"
                title="Preflight is the wizard's own slice (D7) and is not built yet, so there is nothing to continue to from here."
            >
                Preflight: not built
            </span>
        </div>
    </SetupLayout>
</template>
