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
import CardPicker from '../../components/support/CardPicker.vue';
import SupportTypeMark from '../../components/support/SupportTypeMark.vue';
import AbsenceValue from '../../components/AbsenceValue.vue';
import { BaseInput, BaseSelect, BaseButton } from '../../components/Form';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, ref } from 'vue';

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
    /** The steps this session has opened, the step bar's completed marking reads them. */
    visited: number[];
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
const picker = ref<InstanceType<typeof CardPicker> | null>(null);

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

/** The card the picker's target slot currently holds, or null when the slot is empty. */
const fillingSlotCard = computed(() => props.slots.find((slot) => slot.position === filling.value)?.card ?? null);

/** How many slots hold a card, counted from the server's own slot list. */
const equippedCount = computed(() => props.slots.filter((slot) => slot.card !== null).length);

/**
 * A refusal names the slot in the words the slot already carries on this screen. The validation key is a
 * field path (`deck.3.ownership`, `deck.2.support_card_id`), and a Trainer reading it learns the shape of
 * the request rather than which of the six slots to fix. `deck` itself is the whole-deck refusal — the
 * duplicate pair — and it has no slot to name.
 */
function errorLabel(field: string): string {
    const position = field.split('.')[1];
    const slot = props.slots.find((row) => String(row.position) === position);

    return slot?.label ?? `Slot ${position}`;
}

/**
 * The one navigation that is not a save: aiming the picker. The draft already holds every pick, so a
 * filter change has nothing to carry and a GET cannot lose one — which is the whole reason this step can
 * keep its slots out of the URL. It carries the current filter too, because retargeting the picker is not
 * a reason to drop a Type or a name the Trainer just narrowed the list with.
 */
function filter(retarget = false): void {
    router.get(
        '/career/setup/deck',
        {
            type: type.value || undefined,
            q: search.value || undefined,
            deck_slot: filling.value,
        },
        {
            preserveScroll: true,
            // A retarget refreshes the picker's options under an open dialog, so the component has to
            // survive the visit. A filter the Trainer submitted has no dialog open and keeps the
            // ordinary remount.
            preserveState: retarget,
            // One entry per retarget would bury the step in history; the URL is stating which slot the
            // picker aims at, not recording a new place.
            replace: retarget,
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

/**
 * Equip a card chosen in the dialog.
 *
 * The dialog's own Equip button names the card it equips, so there is no `<select>` to read back: the
 * id arrives with the event. Ownership is the slot's existing flag where one is set, and Owned where
 * the Trainer has not said yet, because the slot needs an answer to save and the toggle beside it can
 * still change it afterwards.
 */
function equipCard(cardId: number): void {
    const ownership = form.deck[filling.value]?.ownership || 'OWNED';

    form.deck[filling.value] = { support_card_id: String(cardId), ownership };
    save();
}

/** Apply the dialog's filter through the page's one navigation, so the URL states it like the panel did. */
function applyPickerFilter(filters: { type: string; query: string }): void {
    type.value = filters.type;
    search.value = filters.query;
    // `retarget` preserves the component so the open dialog survives the visit and shows the refined
    // list, rather than closing on every search (R4-21).
    filter(true);
}

/**
 * A filter round trip remounts nothing when it preserves state, but a fresh arrival on a URL that
 * names both a slot and a filter reopens the dialog over that list. Without it a copied or reloaded
 * search URL lands on the slot list with the filter applied but no picker to see it in (R4-21).
 */
onMounted(() => {
    if (props.picker.query === null) {
        return;
    }

    void nextTick(() => {
        picker.value?.openAt(
            document.getElementById(`deck-slot-${filling.value}`),
            { type: props.picker.type ?? '', query: props.picker.query ?? '' },
        );
    });
});

/** A clear is a write of its own: the slot returns to its named empty text and the flag to `N/A`. */
function clearSlot(): void {
    form.deck[filling.value] = { support_card_id: '', ownership: '' };
    save();
}

/**
 * Retarget the picker. The query has to say the same slot the picker aims at: a Trainer who copies the
 * address bar between a Replace and the next equip was copying the slot the last server round trip wrote,
 * one behind the screen (R3-03). One navigation serves both, so the focus move the filter does is shared.
 */
function replace(position: number, opener: Event): void {
    filling.value = position;
    form.deck_slot = position;

    // Open first, in the click's own tick. Opening after the retarget visit was measured not to work:
    // the visit remounts this component, the dialog ref resets, and `showModal()` ran on a detached
    // element (`open: false` in the DOM dump). The dialog owns the focus return, which is why the
    // opener element is handed over rather than a ref.
    picker.value?.open(opener, { type: props.picker.type, query: props.picker.query });

    // Then retarget, keeping the component alive so the dialog above survives the round trip.
    filter(true);
}

function setOwnership(position: number, ownership: 'OWNED' | 'RENTED'): void {
    form.deck[position] = { ...form.deck[position], ownership };
    save(position);
}
</script>

<template>
    <SetupLayout :step="5" :visited="props.visited" :saved="props.saved">
        <Head title="Support deck" />
        <template #eyebrow>Setup</template>
        <template #title>Support deck</template>

        <!-- No breadcrumb: "← Back to deck" pointed at this same URL, and its `Link` was never
             imported, so it rendered an empty `<nav aria-label="Breadcrumb">` and a resolve warning.
             The step bar above lists all six steps, so nothing is lost. Steps 1 and 2 carry the same
             note. -->

        <!-- Header: eyebrow + subtitle. The page title is the layout's #title h1. -->
        <header class="mb-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-accent-ink">Step 5 of 6</p>
            <p class="mt-1 text-sm text-ink-muted">A deck is the six cards equipped before the career starts.</p>
        </header>

        <p class="max-w-3xl text-sm text-ink-muted">
            A deck is the six cards equipped before the career starts. Position six is the friend slot, and
            that role belongs to the position: any card may sit in it.
        </p>

        <div class="mt-3 text-sm text-ink-muted">
            <template v-if="props.scenarioLabel">{{ props.scenarioLabel }}</template>
            <template v-else>
                Scenario:
                <AbsenceValue reason="No scenario has been chosen in this setup draft yet, so a card's Scenario Link cannot be checked against a linked list." />
            </template>
        </div>

        <p
            v-if="form.errors.deck"
            role="alert"
            class="mt-3 rounded-md border border-risk bg-raised px-3 py-2 text-sm text-risk"
        >
            {{ form.errors.deck }}
        </p>

        <div class="mt-5 max-w-5xl">
            <section aria-labelledby="draft-slots-heading">
                <h2 id="draft-slots-heading" class="text-lg font-semibold text-ink-strong">The six slots</h2>

                <p class="mt-1 text-xs text-ink-muted">
                    Not equipped. The six cards are the deck the run starts with, so an empty slot here is a
                    gap the Trainer can still fill.
                </p>

                <ul class="mt-3 grid list-none grid-cols-1 gap-4 @3xl:grid-cols-2 @4xl:grid-cols-3">
                    <SupportSlot
                        v-for="slot in props.slots"
                        :key="slot.position"
                        :position="slot.position"
                        :label="slot.label"
                        :is_friend="slot.is_friend"
                        :card="slot.card"
                        :ownership="slot.ownership"
                        :filling-slot="filling"
                        @replace="replace"
                        @ownership="setOwnership"
                    />
                </ul>

                <CardPicker
                    ref="picker"
                    :slot-label="fillingLabel()"
                    :options="props.picker.options"
                    :types="props.types"
                    :type="props.picker.type"
                    :query="props.picker.query"
                    @equip="equipCard"
                    @filter="applyPickerFilter"
                />

                 <p
                     v-if="form.errors.deck && !form.processing"
                     class="mt-3 rounded-md border border-dashed border-rule bg-panel p-3 text-xs text-ink-muted"
                 >
                    A slot that holds a card has to say whether you own it or rented it. Nothing is stored
                    until you choose one of the two.
                </p>

                <!-- Where the flag goes, said next to the six toggles it describes: the draft holds it
                     while the setup is in flight, and Preflight writes it onto the run's deck row when
                     the career starts (`ADR-0023`, D3). -->
                <p class="mt-3 rounded-md border border-dashed border-rule bg-panel p-3 text-xs text-ink-muted">
                    The owned or rented flag is kept in this setup draft, and Preflight writes it onto the run
                    as one value per slot, where it stays after a reload. Equipping a card records it as Owned
                    unless you mark it Rented. A slot you have not classified reads as N/A rather than as Owned.
                </p>

                <form class="mt-4 flex flex-wrap items-center gap-3" @submit.prevent="save()">
                    <button
                        type="submit"
                        class="h-11 rounded-md bg-primary-action px-5 font-semibold text-on-primary-action disabled:cursor-wait disabled:bg-disabled"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Saving…' : 'Save deck' }}
                    </button>
                    <p v-if="form.processing" role="status" class="text-sm text-ink-muted">Saving the deck…</p>
                </form>
            </section>

        </div>

        <!--
          Hidden until at least one card is equipped (plan 3.7). The panel counts what the deck covers,
          so an empty draft would render six zeroes and a list of gaps, which reads as a verdict on a
          deck the Trainer has not built yet. `equippedCount` reads the same draft the analysis does,
          so the panel appears the moment the first card lands and never contradicts it.
        -->
        <DeckAnalysis
            v-if="equippedCount > 0"
            :covered="props.analysis.covered"
            :categories="props.analysis.categories"
            :uncategorised="props.analysis.uncategorised"
            :strengths="props.analysis.strengths"
            :weaknesses="props.analysis.weaknesses"
        />

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <a
                href="/career/setup/legacy"
                class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised transition-colors duration-150 ease-out"
            >
                Back: Legacy
            </a>
            <a
                href="/career/setup/preflight"
                class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised transition-colors duration-150 ease-out"
            >
                Next: Preflight
            </a>
        </div>
    </SetupLayout>
</template>
