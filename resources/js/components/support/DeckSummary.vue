<script setup lang="ts">
/*
 * The assigned-card summary of the career setup wizard's deck step, `SCR-CAR-009`.
 *
 * **What this answers, and what it refuses to answer.** Two questions, read apart: which cards the deck
 * holds and whether each one is a copy this Trainer owns or one rented from a friend, and what a card of
 * that rarity is worth at each limit-break tier from unbroken to Max Limit Break. It prints no break count
 * for the Trainer's own copy, because there is none to print: the draft stores a level and nothing else
 * (`ADR-0029` D1, a numerator), and `ADR-0014` declines the card collection. The tier a level is read
 * against is the published ladder for that rarity, so `Lvl 45` typed on an R card names itself as past
 * that card's four-break ceiling instead of being quietly accepted.
 *
 * **It updates as the level is typed, not as it is saved.** The step writes on equip, on flag and on
 * clear, but a slot's level field has no writer of its own until `Continue to preflight` posts the deck
 * (`DeckSelect.vue`'s override note beside that door). Reading the live form row is the difference between
 * a summary that tracks the keystroke and one that trails it by a save, and nothing is invented to fill
 * the gap: the ladder arrives from the server, and the only figure worked out in the browser is which
 * tier's cap first holds a number already on screen.
 *
 * **No score, and no coverage panel.** `ADR-0020` §3 rules a deck rating out and Phase E moved the effect
 * coverage panel to the run-scoped builder. This section states identity, ownership and the stated values
 * at each tier, and draws no conclusion from them.
 *
 * **Every figure is an anchor the export states.** Each tier level (30, 35, 40, 45, 50 for an SSR among
 * others) is one of the eleven anchors `UMAMUSUME_REFERENCE.md` §1.4.7 records, so a cell is a published
 * value rather than an interpolation. Where a card states nothing above its own last anchor the value
 * carries up, which is the rule `SupportCardEffects::atCap()` already applies and the reason a
 * four-break cell can equal a three-break one.
 */
import AbsenceValue from '../AbsenceValue.vue';
import SupportTypeMark from './SupportTypeMark.vue';
import { computed } from 'vue';

interface LadderEffect {
    effect_id: number;
    name: string | null;
    display: string;
}

interface Tier {
    breaks: number;
    is_maximum: boolean;
    level: number;
    effects: LadderEffect[];
}

interface SlotCard {
    id: number;
    name: string;
    url: string;
    rarity_word: string;
    type: string;
    type_label: string;
    ladder: Tier[];
}

interface Slot {
    position: number;
    label: string;
    is_friend: boolean;
    ownership: 'OWNED' | 'RENTED' | null;
    card: SlotCard | null;
}

const props = defineProps<{
    /** The six positions, exactly as the step's own slot list carries them. */
    slots: Slot[];
    /**
     * The form's deck rows, live. The level a Trainer is typing sits here before any save moves it into
     * the draft, which is what lets the summary track the keystroke.
     */
    liveLevels: Record<number, { card_level: string }>;
}>();

/**
 * One entry per assigned card with its level, its tier and its ceiling read out once.
 *
 * Built here rather than in the template because the three readings are one decision: what number is on
 * screen, which tier's cap first holds it, and whether it clears every cap this rarity has.
 */
const entries = computed(() => props.slots.flatMap((slot) => {
    if (slot.card === null) {
        return [];
    }

    const raw = props.liveLevels[slot.position]?.card_level ?? '';
    const parsed = raw === '' ? Number.NaN : Number(raw);
    const level = Number.isInteger(parsed) && parsed > 0 ? parsed : null;
    const top = slot.card.ladder[slot.card.ladder.length - 1];

    return [{
        position: slot.position,
        slot,
        card: slot.card,
        level,
        tier: level === null ? null : slot.card.ladder.find((tier: Tier) => level <= tier.level) ?? null,
        top,
        above: level !== null && top !== undefined && level > top.level,
    }];
}));

/** The words a tier answers to, with the client's own ceiling named. */
function tierWord(tier: Tier): string {
    if (tier.is_maximum) {
        return 'Max Limit Break';
    }

    if (tier.breaks === 0) {
        return 'Unbroken';
    }

    return `${tier.breaks} break${tier.breaks === 1 ? '' : 's'}`;
}

/**
 * The client's four diamonds, one filled per completed break (`UMAMUSUME_REFERENCE.md` §1.4.2). Drawn
 * beside the words and never instead of them, which is the rule `DESIGN.md` §6 sets for a glyph.
 */
function diamonds(card: SlotCard, filled: number): string {
    return '◆'.repeat(filled) + '◇'.repeat(card.ladder.length - 1 - filled);
}

/**
 * One row per effect, with that effect's value under each tier.
 *
 * The effect set is the same at every tier: `SupportCardEffects::atLevel()` drops a row that states no
 * anchor at all, and that does not depend on the level asked, so zipping the tiers positionally cannot
 * shift a value into the wrong row.
 */
function ladderRows(card: SlotCard): { key: number; name: string; cells: (string | undefined)[] }[] {
    const first = card.ladder[0];

    if (first === undefined) {
        return [];
    }

    return first.effects.map((effect, index) => ({
        key: effect.effect_id,
        // D-20: an absent dictionary row is marked by the source's own id, never with an invented word.
        name: effect.name ?? `[Unverified] effect ${effect.effect_id}`,
        cells: card.ladder.map((tier) => tier.effects[index]?.display),
    }));
}
</script>

<template>
    <section class="card mt-4" aria-labelledby="deck-summary-heading">
        <h2 id="deck-summary-heading">
            Deck summary
            <span class="text-xs font-semibold text-ink-muted">{{ entries.length }} assigned</span>
        </h2>

        <p class="mt-1 text-xs text-ink-muted">
            Every card the deck holds, whether this Trainer owns it or rented it from a friend, and what a
            card of that rarity is worth at each level from unbroken to Max Limit Break. The level column
            follows what is typed in the slot above it, before the deck is saved.
        </p>

        <p v-if="entries.length === 0" class="mt-3 text-sm text-ink-muted">
            Nothing assigned yet. Fill a slot above and this names it here, with its ownership and its
            ladder.
        </p>

        <template v-else>
            <table class="mt-3 w-full text-sm">
                <caption class="sr-only">The assigned support cards, how each is held, and its level</caption>
                <thead>
                    <tr class="text-left text-xs text-ink-muted">
                        <th scope="col" class="pb-1 pr-4 font-semibold">Slot</th>
                        <th scope="col" class="pb-1 pr-4 font-semibold">Card</th>
                        <th scope="col" class="pb-1 pr-4 font-semibold">Type</th>
                        <th scope="col" class="pb-1 pr-4 font-semibold">Rarity</th>
                        <th scope="col" class="pb-1 pr-4 font-semibold">Held as</th>
                        <th scope="col" class="pb-1 pr-4 font-semibold">Level</th>
                        <th scope="col" class="pb-1 font-semibold">Breaks the level needs</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-rule">
                    <tr v-for="entry in entries" :key="entry.position">
                        <td class="py-2 pr-4 align-top text-ink-muted">{{ entry.slot.label }}</td>
                        <td class="py-2 pr-4 align-top">
                            <a :href="entry.card.url" class="font-semibold text-ink hover:underline">{{ entry.card.name }}</a>
                        </td>
                        <td class="py-2 pr-4 align-top">
                            <SupportTypeMark :type="entry.card.type" :label="entry.card.type_label" />
                        </td>
                        <td class="py-2 pr-4 align-top font-mono text-xs">{{ entry.card.rarity_word }}</td>
                        <td class="py-2 pr-4 align-top">
                            <!-- The two words the slot's own control prints, so one stored value has one
                                 rendering on the page. -->
                            <span v-if="entry.slot.ownership !== null" class="pill pill-neutral">
                                {{ entry.slot.ownership === 'OWNED' ? 'Owned' : 'Rented' }}
                            </span>
                            <AbsenceValue
                                v-else
                                compact
                                reason="Not recorded. This slot holds a card but no ownership has been chosen for it yet, and the draft stores no default: the two answers are the Owned and Rented buttons on the slot itself."
                            />
                        </td>
                        <td class="py-2 pr-4 align-top font-mono">
                            <template v-if="entry.level !== null">{{ entry.level }}</template>
                            <AbsenceValue
                                v-else
                                compact
                                reason="No level entered. The box beside this slot holds it, and the step writes it when you continue to preflight."
                            />
                        </td>
                        <td class="py-2 align-top">
                            <template v-if="entry.tier !== null">
                                <span aria-hidden="true" class="font-mono text-ink-faint">{{ diamonds(entry.card, entry.tier.breaks) }}</span>
                                <span class="ml-1">{{ tierWord(entry.tier) }}</span>
                                <span class="text-ink-muted"> · cap Lvl {{ entry.tier.level }}</span>
                            </template>
                            <span v-else-if="entry.above" class="block text-risk">
                                Above the ladder
                                <span class="block text-xs font-normal text-ink-muted">
                                    No break takes an {{ entry.card.rarity_word }} card past Lvl
                                    {{ entry.top?.level }}.
                                </span>
                            </span>
                            <AbsenceValue
                                v-else
                                compact
                                reason="Cannot be read. The tier is the one whose level cap first holds the level on screen, and no level is on screen."
                            />
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- One ladder per assigned card. The values are the anchors the source states at each tier's
                 level, so a column is the catalogue's figure for a card of that rarity and not a reading of
                 the copy in the slot: this tool has never seen anyone's copies (`ADR-0014`). -->
            <section
                v-for="entry in entries"
                :key="`ladder-${entry.position}`"
                class="mt-4"
                :aria-labelledby="`deck-ladder-${entry.position}`"
            >
                <h3 :id="`deck-ladder-${entry.position}`" class="text-sm font-semibold text-ink-strong">
                    {{ entry.card.name }}
                    <span class="font-mono text-xs text-ink-muted">{{ entry.card.rarity_word }}</span>
                </h3>

                <table class="mt-1 w-full text-sm">
                    <caption class="sr-only">
                        {{ entry.card.name }} at each level from unbroken to Max Limit Break
                    </caption>
                    <thead>
                        <tr class="text-left text-xs text-ink-muted">
                            <th scope="col" class="pb-1 pr-4 font-semibold">Effect</th>
                            <th
                                v-for="tier in entry.card.ladder"
                                :key="tier.breaks"
                                scope="col"
                                class="pb-1 pr-4 font-semibold"
                            >
                                {{ tierWord(tier) }}
                                <span class="block font-mono font-normal">Lvl {{ tier.level }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-rule">
                        <tr v-for="row in ladderRows(entry.card)" :key="row.key">
                            <th scope="row" class="py-1.5 pr-4 text-left font-normal text-ink-muted">{{ row.name }}</th>
                            <td v-for="(cell, index) in row.cells" :key="index" class="py-1.5 pr-4 font-mono">
                                {{ cell }}
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p v-if="ladderRows(entry.card).length === 0" class="mt-1 text-xs text-ink-muted">
                    The source states no effect anchor for this card, so there is no ladder to print.
                </p>
            </section>
        </template>

        <!-- The figures come from the published ladder and the stated anchors:
             `UMAMUSUME_REFERENCE.md` §1.4.2 (the per-rarity caps) and §1.4.7 (the anchor levels). A
             section reference is kept here rather than in the sentence, because `UiHygieneTest` refuses
             to print an identifier the Trainer does not hold. -->
        <p class="note mt-3 text-xs text-ink-muted">
            <b>The ladder is the upgrade model, not a reading of your collection.</b> A Global card takes
            four breaks by consuming copies of itself and each break adds five levels, so an SSR reaches
            Lvl 30 unbroken and Lvl 50 at Max Limit Break, an SR 25 to 45, an R 20 to 40. The figures above
            are the ones the source states at those levels, and where a card states no higher anchor its
            last value carries up.
        </p>
    </section>
</template>
