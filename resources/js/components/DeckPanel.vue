<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// Ported from x-deck-panel. The deck is run-scoped, not scenario-composed: every scenario
// has six slots, so this panel carries no `panels.*` self-gate.

// The established server shape: SupportCardController maps SupportCardEffects::atCap()
// straight through, `display` already formatted. Reused verbatim.
interface DeckEffect {
    effect_id: number;
    name: string | null;
    display: string;
}

interface EquippedSlot {
    position: number;
    card_url: string;
    card_name: string;
    // Composed server-side: position 6 reads 'Friends', the rest 'Slot N' (ADR-0014
    // correction 1: six is the friend slot whatever card sits in it).
    slot_word: string;
    // 'rarityWord typeLabel', composed server-side.
    rarity_type: string;
    scenario_link: boolean;
    effects: DeckEffect[];
}

interface DeckSlotForm {
    position: number;
    // 'Slot 3', or 'Slot 6 · Friends' on the picker label and the closed read-back.
    label: string;
    // old() ?? stored card id, resolved server-side; '' for Not equipped.
    selected: string;
    // The chosen card's display name for the closed read-back, null when none resolves.
    selected_name: string | null;
    // fullUrlWithQuery(['deck_slot' => position]) for this slot, built server-side so the
    // other query parameters survive.
    open_url: string;
}

interface DeckOption {
    id: number;
    // 'displayName · rarityWord typeLabel', composed server-side.
    label: string;
}

const props = defineProps<{
    equipped: EquippedSlot[];
    // All six DeckSlot::POSITIONS, in order, whether or not a card sits in them.
    slots: DeckSlotForm[];
    // Global releases plus any card this run already uses, sorted type/char_name/title_en.
    options: DeckOption[];
    // Server-resolved, like Blade: the requested slot, else the first errored one, else the
    // first. A local ref could not survive the redirect back from a rejected submit, which
    // is exactly when the slot has to reopen.
    openSlot: number;
    action: string;
}>();

const page = usePage();
const errors = computed(() => (page.props.errors as Record<string, string | undefined> | undefined) ?? {});

// One option list, not six: each slot once carried the whole catalogue, measured as 1,512
// `option` nodes. Closed slots still post their own value, through the form data rather than
// a hidden input now that a script is guaranteed to be running.
const form = useForm({
    deck: Object.fromEntries(
        props.slots.map((slot) => [slot.position, { support_card_id: slot.selected }]),
    ) as Record<number, { support_card_id: string }>,
});

function save(): void {
    form.post(props.action);
}
</script>

<template>
    <div class="rounded-md border border-rule bg-panel p-3">
        <!-- No chrome-bar title: the run screen already carries an `h2 Support deck` above
             this component, and a second one inside reads as two sections with the same name. -->
        <p v-if="equipped.length === 0" class="text-sm text-ink-muted" role="status">
            No support cards recorded for this run. The deck is the six cards equipped before the run started,
            and it decides training yield as much as the turns do, so a logged run with no deck cannot be
            re-read later. Choose a card for each slot below, or leave a slot on "Not equipped" if the Trainer
            does not remember it.
        </p>
        <ul v-else class="mb-4 flex flex-col gap-1.5" aria-label="Equipped support cards">
            <li
                v-for="slot in equipped"
                :key="slot.position"
                class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1 rounded-md border border-rule bg-raised px-3 py-2 text-sm"
            >
                <a :href="slot.card_url" class="font-semibold text-ink-strong hover:underline">{{ slot.card_name }}</a>
                <span class="flex shrink-0 flex-wrap items-baseline gap-3 font-mono text-xs text-ink-muted">
                    <span>{{ slot.slot_word }}</span>
                    <span>{{ slot.rarity_type }}</span>
                    <span v-if="slot.scenario_link" class="font-bold text-ink-strong">Scenario Link</span>
                </span>
                <!-- Effect facts sit on the record of the choice, not in the picker; a card whose
                     vector states nothing renders no line, because an empty line is a control that
                     says nothing. -->
                <span
                    v-if="slot.effects.length > 0"
                    class="flex w-full flex-wrap gap-x-3 gap-y-0.5 font-mono text-xs text-ink-muted"
                >
                    <!-- D-20: an absent dictionary row is marked by the source's own id, not labelled
                         with an invented word. -->
                    <span v-for="effect in slot.effects" :key="effect.effect_id">
                        {{ effect.name ?? `[Unverified] effect ${effect.effect_id}` }} {{ effect.display }}
                    </span>
                </span>
            </li>
        </ul>

        <form class="max-w-3xl space-y-3 rounded-md border border-rule bg-raised p-4 text-sm" @submit.prevent="save">
            <div v-for="slot in slots" :key="slot.position" class="flex flex-wrap items-end gap-3">
                <div class="flex min-w-0 flex-1 flex-col gap-1">
                    <template v-if="slot.position === openSlot">
                        <!-- KI-36: one accessible name per control, paired by id rather than wrapped;
                             a wrapping label over a group leaves sibling controls unnamed. -->
                        <label :for="`deck-slot-${slot.position}`" class="text-ink-muted">{{ slot.label }}</label>
                        <select
                            :id="`deck-slot-${slot.position}`"
                            v-model="form.deck[slot.position].support_card_id"
                            :name="`deck[${slot.position}][support_card_id]`"
                            class="min-h-11 rounded-md border border-rule bg-raised px-2 py-1 text-ink"
                        >
                            <option value="">Not equipped</option>
                            <option v-for="card in options" :key="card.id" :value="String(card.id)">{{ card.label }}</option>
                        </select>
                    </template>
                    <!-- The value posts and the name is readable, which is all a closed slot owes:
                         the catalogue is one click away, and it is a link rather than a control so
                         the screen has one option list in it at a time. -->
                    <p v-else class="flex flex-wrap items-baseline gap-x-2 text-sm">
                        <span class="text-ink-muted">{{ slot.label }}:</span>
                        <span class="text-ink">{{ slot.selected_name ?? 'Not equipped' }}</span>
                        <Link :href="slot.open_url" class="inline-flex min-h-11 items-center underline">
                            Change slot {{ slot.position }}
                        </Link>
                    </p>
                </div>
                <p v-if="errors[`deck.${slot.position}.support_card_id`]" class="w-full text-risk">
                    {{ errors[`deck.${slot.position}.support_card_id`] }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="enamel rounded-full bg-chrome px-3 py-1.5 font-semibold text-on-chrome">
                    Save deck
                </button>
                <!-- A deck is replaced whole, so clearing needs its own explicit sentence rather
                     than being what happens after a page misread. -->
                <span v-if="equipped.length > 0" class="text-xs text-ink-muted">
                    Set every slot to "Not equipped" to clear the deck.
                </span>
            </div>
            <p v-if="errors.deck" class="w-full text-risk">{{ errors.deck }}</p>
        </form>

        <p class="mt-3 font-mono text-xs text-ink-muted">
            {{ options.length }} card{{ options.length === 1 ? '' : 's' }} offered · Global releases plus any
            card this run already uses · hard limit of one copy per card, so a duplicate is refused rather than
            silently kept.
            <!-- D-256: a displayed figure names the rule behind it. -->
            Effect figures are each card's highest stated anchor, the value the source publishes at its top
            level, not a figure for a level this run records.
        </p>
    </div>
</template>
