<script setup lang="ts">
import ArtworkSlot from '../ArtworkSlot.vue';
import SupportTypeMark from './SupportTypeMark.vue';

/*
 * One of the six deck slots (SCREEN-007).
 *
 * Three facts live here and nowhere else: which card the slot holds, whether the scenario badges that
 * card a Scenario Link, and whether the Trainer owns or rents it. Each is read from the payload and
 * rendered, and this component decides no value of its own.
 *
 * **The Scenario Link badge is three states, not two.** The server sends `linked`, `not_linked` or
 * `unknown`, and `unknown` prints `N/A` with the reason as its `title`. The reason the triple exists is
 * that `SupportCard::isScenarioLink()` answers `false` for two unrelated things: a character that is
 * genuinely not on the scenario's linked list, and a card with no list to compare against. A boolean
 * would render the second as a confident "not a link", which is the one thing this screen must not do.
 *
 * **The ownership flag is a two-button group, not a checkbox.** A checkbox hides its state behind a
 * filled box, which is state through a mark rather than through words; two buttons with words on them
 * state the value in text as well as position, and one of them is reachable with a single keystroke.
 * The flag is stored, one value per slot (`ADR-0023`): the run-scoped write puts it on the
 * `deck_slots.ownership` column, and the wizard carries it in the session draft until Preflight creates
 * the row. Neither surface defaults it, so an unclassified slot says so in words rather than letting a
 * control imply a record it does not hold.
 */
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

const props = defineProps<{
    position: number;
    label: string;
    is_friend: boolean;
    card: SlotCard | null;
    /**
     * The flag, or null when nothing has recorded it.
     *
     * The run-scoped builder passes `'OWNED'`, the one value its table can be read as, and the markup is
     * unchanged by the wider type. The setup wizard's deck step passes null for a slot that holds no card,
     * because "owned or rented" describes nothing when nothing sits in the position, and an unrecorded value
     * renders as `N/A` with a `title` rather than as a default (AGENTS.md §5).
     */
    ownership: 'OWNED' | 'RENTED' | null;
    /** ADR-0029 D1's per-run numerator: the level the card was at in this run, or null. */
    cardLevel: number | string | null;
    /** Which slot the picker's Equip buttons write to, for the button's own label. */
    fillingSlot: number;
    /**
     * This slot's own refusal, keyed `deck.{position}.*` on the write. Optional: only the run-scoped
     * builder passes it. The whole-deck refusals stay in the page's own alert band, because they name
     * the deck and not a position.
     */
    error?: string;
}>();

const emit = defineEmits<{
    /** The Trainer asked to replace this slot's card. The page turns it into a query-string change. */
    (event: 'replace', position: number, opener: Event): void;
    (event: 'ownership', position: number, ownership: 'OWNED' | 'RENTED'): void;
    /** The Trainer asked to empty this slot; the page writes the blank position. */
    (event: 'clear', position: number): void;
    /** The Trainer entered the level the card was at in this run. */
    (event: 'level', position: number, value: string): void;
}>();

function onLevel(event: Event): void {
    emit('level', props.position, (event.target as HTMLInputElement).value);
}

/**
 * The focus target a replacement returns to, so a keyboard user is put back on the row they just
 * changed rather than at the top of the document. `tabindex="-1"` because the row is not a control in
 * its own right; it is a place to resume from.
 */
function focusRow(): void {
    const row = document.getElementById(`deck-slot-${props.position}`);
    row?.focus();
}
</script>

<template>
    <li
        :id="`deck-slot-${position}`"
        tabindex="-1"
        class="card flex flex-wrap items-start gap-x-4 gap-y-2 text-sm focus-visible:outline-2 focus-visible:outline-offset-2 transition-colors duration-150 ease-out"
    >
        <ArtworkSlot
            v-if="card"
            :url="card.artworkURL"
            alt=""
            size="size-12"
            :href="card.url"
            :link-label="card.name"
            reserve
        />

        <div class="flex min-w-0 flex-1 flex-col gap-1">
            <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                <span class="font-semibold text-[var(--color-ink-strong)]">{{ label }}</span>
                <span v-if="is_friend" class="pill pill-neutral">
                    Friend slot
                </span>
            </div>

            <!-- An empty slot is a deliberate statement a Trainer makes, so it reads as a statement
                 rather than as a blank control: the card is not equipped, and here is where to put it. -->
            <p v-if="!card" class="text-[var(--color-ink-muted)]">
                Not equipped.
            </p>

            <template v-else>
                <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                    <a :href="card.url" class="font-semibold text-[var(--color-ink)] hover:underline transition-colors duration-150 ease-out">{{ card.name }}</a>
                    <span class="font-mono text-xs text-[var(--color-ink-muted)]">{{ card.rarity_word }}</span>
                    <SupportTypeMark :type="card.type" :label="card.type_label" />
                </div>

                <!-- The three states, each carrying its meaning in words. A badge that only showed a
                     colour would read as state through colour alone, which §17 forbids. -->
                <p class="flex flex-wrap items-baseline gap-x-2 text-xs">
                    <span
                        v-if="card.scenario_link === 'linked'"
                        class="pill pill-accent"
                    >
                        Scenario Link
                    </span>
                    <span
                        v-else-if="card.scenario_link === 'not_linked'"
                        class="text-[var(--color-ink-muted)]"
                    >
                        Not a Scenario Link
                    </span>
                    <span
                        v-else
                        class="text-[var(--color-ink-muted)]"
                        :title="card.scenario_link_note ?? 'The Scenario Link badge cannot be derived for this card.'"
                    >
                        Scenario Link: N/A
                    </span>
                </p>

                <p
                    v-if="card.effects.length > 0"
                    class="flex flex-wrap gap-x-3 gap-y-0.5 font-mono text-xs text-[var(--color-ink-muted)]"
                >
                    <!-- D-20: an absent dictionary row is marked by the source's own id, never labelled
                         with an invented word. -->
                    <span v-for="effect in card.effects" :key="effect.effect_id">
                        {{ effect.name ?? `[Unverified] effect ${effect.effect_id}` }} {{ effect.display }}
                    </span>
                </p>
            </template>
        </div>

        <div class="flex shrink-0 flex-col items-end gap-2">
            <!-- ADR-0029 D1: the level the card was at in this run, a numerator only. The cap and the
                 limit-break count are collection state ADR-0014 declines and this control never asks for. -->
            <div v-if="card" class="flex flex-col gap-1">
                <span class="text-xs text-ink-muted">Level</span>
                <input
                    type="number"
                    min="1"
                    max="50"
                    class="min-h-11 w-20 rounded-[var(--radius-md)] border border-rule bg-raised px-2 text-sm text-ink"
                    :value="cardLevel ?? ''"
                    :aria-label="`Level for ${label}`"
                    @input="onLevel"
                />
            </div>

            <!-- Ownership: two buttons, each with its word on it. `aria-pressed` carries the state for a
                 screen reader while the words carry it for everyone, and neither is a colour. -->
            <div
                class="flex flex-col gap-1"
                role="group"
                :aria-label="`Ownership of ${label}`"
            >
                <span class="text-xs text-[var(--color-ink-muted)]">Ownership</span>
                <!-- Neither button pressed and the value named as absent: the state is in words, not in an
                     unfilled control a Trainer has to interpret. -->
                <span
                    v-if="ownership === null"
                    class="text-xs text-[var(--color-ink-muted)]"
                    title="No ownership has been recorded for this slot yet."
                >N/A</span>
                <div class="flex gap-1">
                    <button
                        v-for="option in (['OWNED', 'RENTED'] as const)"
                        :key="option"
                        type="button"
                        class="min-h-11 rounded-[var(--radius-md)] border border-[var(--color-rule)] px-3 text-xs font-semibold"
                        :class="
                            ownership === option
                                ? 'bg-[var(--color-raised)] text-[var(--color-ink-strong)] underline decoration-2 underline-offset-4'
                                : 'bg-transparent text-[var(--color-ink-muted)] hover:bg-[var(--color-sunken)]'
                        "
                        :aria-pressed="ownership === option"
                        @click="emit('ownership', position, option)"
                    >
                        {{ option === 'OWNED' ? 'Owned' : 'Rented' }}
                    </button>
                </div>
            </div>

            <button
                type="button"
                class="min-h-11 rounded-[var(--radius-pill)] border border-[var(--color-rule)] px-3 text-sm font-semibold text-[var(--color-ink)] hover:bg-[var(--color-sunken)] focus-visible:outline-2 focus-visible:outline-offset-2 transition-colors duration-150 ease-out"
                :aria-label="card ? `Replace the card in ${label}` : `Choose a card for ${label}`"
                @click="emit('replace', position, $event)"
            >
                {{ card ? 'Replace' : 'Choose a card' }}
                <span class="sr-only"> in {{ label }}</span>
            </button>

            <button
                v-if="card"
                type="button"
                class="min-h-11 rounded-[var(--radius-pill)] border border-[var(--color-rule)] px-3 text-sm font-semibold text-[var(--color-ink)] hover:bg-[var(--color-sunken)] focus-visible:outline-2 focus-visible:outline-offset-2 transition-colors duration-150 ease-out"
                :aria-label="`Set ${label} to not equipped`"
                @click="emit('clear', position)"
            >
                Remove
            </button>
        </div>

        <p v-if="error" role="alert" class="w-full text-xs text-[var(--color-risk)]">{{ error }}</p>
    </li>
</template>
