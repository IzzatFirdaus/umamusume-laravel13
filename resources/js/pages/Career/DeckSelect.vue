<script setup lang="ts">
/*
 * HFW-OVERRIDE (computed/rect: the wizard-wide class) — `deck-diff-{1280,390}.json` measures the
 * app's DESIGN.md tokens against the wireframe's mock CSS and the shell wrapper's offset. Tokens
 * outrank the mock palette (`AGENTS.md` §2) and the wrapper moves every box, so both buckets carry
 * entries by construction; the page is not restyled to chase the mock's values.
 */
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
 * **No page-local submit, and no analysis panel (Phase E).** `career-deck.html` draws neither a `Save
 * deck` control nor the effect-coverage panel: equipping, flagging and clearing already write as they
 * happen, and the forward door writes again before it navigates, so the step's one submit-shaped control
 * is the head band's `Continue to preflight`. The coverage panel belongs to the run's own deck screen
 * (`resources/js/pages/Support/Builder.vue`), where the effect anchors have a run to measure against.
 */
import SetupLayout from '../../layouts/SetupLayout.vue';
import SupportSlot from '../../components/support/SupportSlot.vue';
import CardPicker from '../../components/support/CardPicker.vue';
import SupportTypeMark from '../../components/support/SupportTypeMark.vue';
import AbsenceValue from '../../components/AbsenceValue.vue';
import { BaseInput, BaseSelect, BaseButton } from '../../components/Form';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
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
    card_level: number | null;
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

const props = defineProps<{
    /** The steps this session has opened, the step bar's completed marking reads them. */
    visited: number[];
    /** The steps that have data stored in the draft (`SetupDraft::saved()`), read apart from `visited`. */
    saved: number[];
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
            card_level: slot.card_level === null ? '' : String(slot.card_level),
        }]),
    ) as Record<number, { support_card_id: string; ownership: string; card_level: string }>,
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

/** The cards in the deck, in slot order. One list feeds every diagnostic below. */
const deckCards = computed(() =>
    props.slots.flatMap((slot) => (slot.card === null ? [] : [slot.card])),
);

/**
 * The duplicate check. The client spends a duplicate on a limit break rather than equipping it twice,
 * and `StoreDraftDeckRequest` refuses a save that repeats a card — so this row states the rule the save
 * already enforces rather than adding a second, client-side one.
 */
const duplicateCount = computed(() => deckCards.value.length - new Set(deckCards.value.map((card) => card.id)).size);

/**
 * Cards whose stated effects include a friendship bonus. Read from the effect name the export uses
 * (`Friendship Bonus`), because that is the source of truth for the mechanic — not a type rule: the
 * friend card carries none, which is exactly why that row prints the absence instead of a blank.
 */
const friendshipCount = computed(
    () => deckCards.value.filter((card) => card.effects.some((effect) => effect.name === 'Friendship Bonus')).length,
);

/** Cards the draft's chosen scenario links to. Zero when no scenario has been chosen yet. */
const linkedCount = computed(() => deckCards.value.filter((card) => card.scenario_link === 'linked').length);

/** The five stat-type marks, in the server's own order, for the first row of the card-types table. */
const statTypes = computed(() => props.types.filter((type) => !['friend', 'group'].includes(type.key)));

/** A type's client word, read off the server's own list rather than typed into this page. */
const typeLabel = (key: string): string => props.types.find((type) => type.key === key)?.label ?? key;

/** Aim the picker at the first empty slot, or the current one when all six are taken. */
function addCard(event: Event): void {
    replace(props.slots.find((slot) => slot.card === null)?.position ?? filling.value, event);
}

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
 * The forward door writes before it leaves. A slot's per-level field has no writer of its own on this
 * step, so the write here is also the only guarantee that a typed level reaches the draft when the
 * Trainer did nothing else on the step. The focus move `save()` owns is skipped: the navigation that
 * follows replaces the whole screen.
 */
function saveAndContinue(): void {
    form.put(props.action, {
        preserveScroll: true,
        onSuccess: () => {
            router.get('/career/setup/preflight');
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

    form.deck[filling.value] = { support_card_id: String(cardId), ownership, card_level: '' };
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
function clearSlot(position: number): void {
    filling.value = position;
    form.deck_slot = position;
    form.deck[position] = { support_card_id: '', ownership: '', card_level: '' };
    save(position);
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
        <Head title="Build the six-card deck" />
        <template #eyebrow>New career</template>
        <template #title>Build the six-card deck</template>

        <!--
          The HFW's head-band pair (`career-deck.html:61-68`). The step's two doors used to sit in a row at
          the foot of the page, after the six slots, the diagnostics table and the card-type table, so the
          only way out of the step was below everything it draws.

          The forward door writes and then navigates (see the override note beside it). The write has no
          control of its own inside the page any more: the six slots save as they are edited.
        -->
        <template #actions>
            <Link
                href="/career/setup/legacy"
                class="inline-flex min-h-11 items-center justify-center rounded-md border border-rule bg-raised px-4 font-semibold text-ink hover:bg-panel"
            >
                Back
            </Link>
            <!--
              HFW-OVERRIDE (write-before-leave): the wireframe's `Continue to preflight` is a plain link
              (`career-deck.html:67`), because a mock holds no state. This step writes state, and the
              per-slot level field is the one value that has no immediate writer of its own since the
              step carries no submit button (`career-deck.html:166-169` draws none), so the forward door
              writes the deck first and navigates on success. On an empty form the put round-trips what
              the draft already holds.
            -->
            <button
                type="button"
                class="inline-flex min-h-11 items-center justify-center rounded-md border border-primary-action bg-primary-action px-4 font-semibold text-on-primary-action hover:bg-primary-action/90 disabled:cursor-wait disabled:opacity-60"
                :disabled="form.processing"
                @click="saveAndContinue()"
            >
                Continue to preflight
            </button>
        </template>

        <!-- No breadcrumb: "← Back to deck" pointed at this same URL, and its `Link` was never
             imported, so it rendered an empty `<nav aria-label="Breadcrumb">` and a resolve warning.
             The step bar above lists all six steps, so nothing is lost. Steps 1 and 2 carry the same
             note. -->

        <!-- The position is carried by the eyebrow in the title band above, so this block only adds
             the subject line. -->
        <header class="mb-4">
            <p class="mt-1 text-sm text-ink-muted">A deck is the six cards equipped before the career starts.</p>
        </header>

        <p class="max-w-3xl text-sm text-ink-muted">
            Six slots. Every card earns its place by the training it boosts, the events it brings, or the
            friendship gauge it starts with. Position six is the friend slot, and that role belongs to the
            position: any card may sit in it.
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

        <!-- `g-side`: the deck on the left, the checks and the type table on the right. Page-level
             grids use viewport media queries, not `@container`: the shell's content box is 1088px at a
             1440px viewport, so an `@3xl` page grid would collapse a column at a width the page reads as
             desktop (`frontend-architecture-plan.md`, container-query note). -->
        <div class="mt-5 grid gap-5 min-[1081px]:grid-cols-[minmax(0,1fr)_22rem]">
            <section class="card" aria-labelledby="draft-slots-heading">
                <h2 id="draft-slots-heading" class="text-lg font-semibold text-ink-strong">
                    Deck
                    <span class="text-xs font-semibold text-ink-muted">{{ equippedCount }} of 6 slots filled</span>
                </h2>

                <p class="mt-1 text-xs text-ink-muted">
                    Not equipped. The six cards are the deck the run starts with, so an empty slot here is a
                    gap the Trainer can still fill. Slot 6 is the friend slot: it takes a borrowed card, and
                    the client draws it apart from the five owned positions.
                </p>

                <!--
                  DOMAIN OVERRIDE (DM-8): the sixth slot is a borrowed guest, and the HFW does not draw it
                  distinct. The client separates a rented friend card from the five owned positions, so the
                  slot states its ownership rather than leaving the Trainer to infer it from the card name.
                -->
                <ul class="mt-3 grid list-none grid-cols-1 gap-4 min-[761px]:grid-cols-2">
                    <SupportSlot
                        v-for="slot in props.slots"
                        :key="slot.position"
                        :position="slot.position"
                        :label="slot.label"
                        :is_friend="slot.is_friend"
                        :card="slot.card"
                        :ownership="slot.ownership"
                        :card-level="form.deck[slot.position].card_level"
                        :filling-slot="filling"
                        @replace="replace"
                        @ownership="setOwnership"
                        @clear="clearSlot"
                        @level="(position, value) => (form.deck[position].card_level = value)"
                    />
                </ul>

                <div class="mt-3 flex flex-wrap gap-3">
                    <button
                        type="button"
                        class="inline-flex min-h-11 items-center justify-center rounded-md border border-rule bg-raised px-4 font-semibold text-ink hover:bg-panel"
                        @click="addCard"
                    >
                        Add a card
                    </button>
                </div>

                <!-- The deck screen's own multiplier rule, stated where the six slots it describes
                     are: HFW carries it inside this card, under the add action. The arithmetic is
                     shown as multiplication, because "+55%" teaches a model the client does not use
                     and the wrong model costs a run. -->
                <p class="note mt-3 text-sm text-ink-muted">
                    <b>Two rainbow cards on one tile is the largest multiplier in a turn.</b> Bonuses
                    multiply rather than add, so a +25% and a +30% card give 1.25 × 1.30 = <b>1.625</b>,
                    not +55% (UMAMUSUME_REFERENCE.md §1.1.3).
                </p>

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
            </section>

            <div class="flex flex-col gap-4">
                <section class="card" aria-labelledby="deck-diagnostics-heading">
                    <h2 id="deck-diagnostics-heading" class="text-lg font-semibold text-ink-strong">Deck diagnostics</h2>

                    <!-- Checks, not a score: there is no deck rating in any source, and a bar without a
                         printed figure is a score wearing a chart costume. Every row states a fact the
                         page can read, and a row the draft cannot answer says so rather than showing 0. -->
                    <table class="mt-3 w-full text-sm">
                        <caption class="sr-only">Deck checks and their state</caption>
                        <thead>
                            <tr class="text-left text-xs text-ink-muted">
                                <th scope="col" class="pb-1 font-semibold">Check</th>
                                <th scope="col" class="pb-1 text-right font-semibold">State</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-rule">
                            <tr>
                                <td class="py-1.5">Six slots filled</td>
                                <td class="py-1.5 text-right">
                                    <span :class="equippedCount === 6 ? 'pill pill-positive' : 'pill pill-caution'">
                                        {{ equippedCount }} of 6
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="py-1.5">Duplicate card in the deck</td>
                                <td class="py-1.5 text-right">
                                    <span :class="duplicateCount === 0 ? 'pill pill-positive' : 'pill pill-caution'">
                                        {{ duplicateCount === 0 ? 'None' : `${duplicateCount} duplicate${duplicateCount === 1 ? '' : 's'}` }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="py-1.5">Cards already at orange gauge</td>
                                <td class="py-1.5 text-right">
                                    <AbsenceValue reason="Not recorded. The setup draft has no friendship-gauge value per slot, and this step does not read the client's in-run gauge." />
                                </td>
                            </tr>
                            <tr>
                                <td class="py-1.5">Cards carrying a friendship bonus</td>
                                <td class="py-1.5 text-right font-mono">{{ friendshipCount }} of {{ equippedCount }}</td>
                            </tr>
                            <tr>
                                <td class="py-1.5">Scenario link on a card</td>
                                <td class="py-1.5 text-right font-mono">{{ linkedCount }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <p class="note mt-3 text-xs text-ink-muted">
                        A card reaches friendship training at gauge <b>80</b>, which the client draws as the
                        fourth mark and colours orange. The gauge rises about +5 per joint training, so a card
                        with no head start needs most of a run to get there — which is why the "Initial
                        Friendship Gauge" effect is the one that matters.
                    </p>
                </section>

                <section class="card" aria-labelledby="deck-types-heading">
                    <h2 id="deck-types-heading" class="text-lg font-semibold text-ink-strong">Card types</h2>

                    <table class="mt-3 w-full text-sm">
                        <caption class="sr-only">The support-card types and what each does</caption>
                        <thead>
                            <tr class="text-left text-xs text-ink-muted">
                                <th scope="col" class="pb-1 font-semibold">Type</th>
                                <th scope="col" class="pb-1 font-semibold">What it does</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-rule">
                            <tr>
                                <td class="py-2 align-top">
                                    <span
                                        v-for="(type, index) in statTypes"
                                        :key="type.key"
                                        class="inline-flex"
                                        :class="index > 0 ? 'ml-2' : ''"
                                    >
                                        <SupportTypeMark :type="type.key" :label="type.label" />
                                    </span>
                                </td>
                                <td class="py-2 align-top text-ink-muted">Boosts its own training tile; carries the friendship kit.</td>
                            </tr>
                            <tr>
                                <td class="py-2 align-top">
                                    <SupportTypeMark type="friend" :label="typeLabel('friend')" />
                                </td>
                                <td class="py-2 align-top text-ink-muted">No friendship bonus. Earns its slot on event chains: energy return, mood, failure protection.</td>
                            </tr>
                            <tr>
                                <td class="py-2 align-top">
                                    <SupportTypeMark type="group" :label="typeLabel('group')" />
                                </td>
                                <td class="py-2 align-top text-ink-muted">SSR only. Full friendship kit plus a friendship recovery; several trainees behind one card.</td>
                            </tr>
                        </tbody>
                    </table>

                    <p class="mt-3 text-xs text-ink-muted">
                        Seven types in total, and {{ typeLabel('group') }} is a type rather than a flag on another one.
                        Global holds 115 SSR cards against JP's 312, so the two catalogues are not interchangeable.
                    </p>
                </section>
            </div>
        </div>

        <!-- The deck screen's own rule statements: the multiplier lives inside the deck card,
             beside the slots it describes. The duplicate rule stays here because it governs the
             picker, not the slots. -->
        <p class="note mt-4 text-sm text-ink-muted">
            <b>Two identical cards cannot sit in one deck.</b> The client spends a duplicate on a limit
            break instead, and the picker does not offer a card the deck already holds.
        </p>

        <!-- Back and "Continue to preflight" moved to the head band above (`#actions`), where the HFW draws
             them. The row that stood here printed the same two doors after the whole page. -->

        <!-- The wireframe draws no coverage panel on this step (`career-deck.html:73-231` is the slots,
             the diagnostics and the card types). The panel lives on the run-scoped deck screen, where the
             effect anchors have a run to measure against. -->
    </SetupLayout>
</template>
