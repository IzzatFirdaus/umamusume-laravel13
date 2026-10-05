<script setup lang="ts">
/*
 * The deck analysis (SCREEN-007).
 *
 * The brief sketches five rows of bar chart. What ships instead is the number beside its label, and
 * the reason is in `DeckAnalysis`: a bar without a printed figure is a score wearing a chart costume,
 * and there is no score to print. A deck has no rating, no normalised percentage and no composite
 * index, and `ADR-0001` §3 rules both out as unsourced. So every line here is either a figure the
 * source states or an integer this screen added to those figures, and the two are labelled apart.
 *
 * **The provenance label is a word, not a glyph.** `ADR-0020` §2 makes `ProvenanceBadge.vue` the only
 * place the four trust glyphs live, and the plan builds that component in D4. Printing glyphs here
 * would put a second set on the page and take the single-owner decision away from the slice that owns
 * it, so this renders the word in a text chip and D4 replaces it with the badge. The claim is the same
 * one the badge would carry: a stated anchor is Confirmed, and the total under it is Calculated.
 *
 * **A multiplicative effect is never totalled.** The export marks four effects as combining in a
 * stated mode, and adding three cards' Friendship Bonuses into one percentage would be a figure the
 * client does not state for a deck held together. Those lines list each card's value and say why there
 * is no total.
 *
 * A category the deck carries nothing in says so in a sentence naming the group, which is what lets a
 * Trainer tell "no card has this" from "I have not looked yet".
 */
interface EffectLine {
    effect_id: number;
    name: string | null;
    mode: 'flat' | 'additive' | 'multiplicative';
    cards: { card_name: string; display: string }[];
    total: { value: number; display: string; basis: string } | null;
}

interface Category {
    label: string;
    blank: boolean;
    effects: EffectLine[];
}

defineProps<{
    covered: number;
    categories: Record<string, Category>;
    uncategorised: EffectLine[];
    strengths: string[];
    weaknesses: string[];
}>();

/**
 * The word the arithmetic is, beside a total. `Confirmed` is the word for the card's own anchor and
 * `Calculated` for the sum, so a reader can tell which of the two figures the tool did arithmetic on.
 */
const trustLabel = { total: 'Calculated' } as const;
</script>

<template>
    <section class="mt-6 rounded-md border border-rule bg-panel p-4" aria-labelledby="deck-analysis-heading">
        <h3 id="deck-analysis-heading" class="text-lg font-semibold text-ink-strong">Deck analysis</h3>

        <p v-if="covered === 0" class="mt-3 rounded-md border border-dashed border-rule bg-raised p-4 text-sm text-ink-muted">
            Nothing to analyse yet. The analysis reads the effects the six cards state, so it is blank
            until the deck has at least one card in it. Fill a slot on the left and this fills in with it.
        </p>

        <template v-else>
            <p class="mt-2 text-sm text-ink-muted">
                Every figure is an effect's highest stated anchor, the value the source publishes at its
                top level, and never an interpolation for a level this deck holds.
            </p>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <section
                    v-for="(category, key) in categories"
                    :key="key"
                    :aria-labelledby="`deck-category-${key}`"
                    class="rounded-md border border-rule bg-raised p-3"
                >
                    <h4 :id="`deck-category-${key}`" class="font-semibold text-ink-strong">
                        {{ category.label }}
                    </h4>

                    <p v-if="category.blank" class="mt-2 text-sm text-ink-muted">
                        No card in the deck carries an effect here.
                    </p>

                    <ul v-else class="mt-2 space-y-3">
                        <li v-for="effect in category.effects" :key="effect.effect_id">
                            <p class="text-sm font-semibold text-ink">
                                {{ effect.name ?? `[Unverified] effect ${effect.effect_id}` }}
                            </p>

                            <!-- Each card's own figure, with the card it came from. The value is printed
                                 before the total, so the total is never the only number on the line. -->
                            <ul class="mt-1 space-y-0.5 text-xs text-ink-muted">
                                <li v-for="card in effect.cards" :key="card.card_name + card.display">
                                    <span class="font-mono">{{ card.display }}</span>
                                    <span> &middot; {{ card.card_name }}</span>
                                </li>
                            </ul>

                            <p v-if="effect.total" class="mt-1 text-sm text-ink">
                                <span class="font-mono font-semibold">{{ effect.total.display }}</span>
                                <span class="text-ink-muted">
                                    {{ effect.total.basis }} across the {{ effect.cards.length }} card{{
                                        effect.cards.length === 1 ? '' : 's'
                                    }} carrying it
                                </span>
                                <span
                                    class="ml-1 rounded-full bg-sunken px-2 py-0.5 text-xs font-semibold text-ink-strong"
                                    :title="`${trustLabel.total}: an integer this screen added to the stated anchors above.`"
                                >
                                    {{ trustLabel.total }}
                                </span>
                            </p>

                            <p v-else class="mt-1 text-xs text-ink-muted">
                                The export marks this effect as multiplicative, so the values above are
                                listed separately rather than added into a figure the client does not state.
                            </p>
                        </li>
                    </ul>
                </section>
            </div>

            <!-- An effect in no category is shown rather than dropped: a deck carrying one would
                 otherwise lose a number the source publishes. §1.4.8 records nine such effects as
                 carried by no card at all, so this is empty on every deck the catalogue holds today. -->
            <section v-if="uncategorised.length > 0" class="mt-4" aria-labelledby="deck-uncategorised">
                <h4 id="deck-uncategorised" class="font-semibold text-ink-strong">Effects outside the six categories</h4>
                <ul class="mt-2 space-y-2">
                    <li v-for="effect in uncategorised" :key="effect.effect_id" class="text-sm text-ink">
                        {{ effect.name ?? `[Unverified] effect ${effect.effect_id}` }}
                        <span class="font-mono">{{ effect.cards.map((card) => card.display).join(', ') }}</span>
                    </li>
                </ul>
            </section>

            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <section class="rounded-md border border-rule bg-raised p-3" aria-labelledby="deck-strengths">
                    <h4 id="deck-strengths" class="font-semibold text-ink-strong">Strengths</h4>
                    <ul v-if="strengths.length > 0" class="mt-2 list-disc space-y-1 pl-5 text-sm text-ink-muted">
                        <li v-for="line in strengths" :key="line">{{ line }}</li>
                    </ul>
                    <p v-else class="mt-2 text-sm text-ink-muted">
                        No card in the deck carries an effect in any of the six categories yet.
                    </p>
                </section>

                <section class="rounded-md border border-rule bg-raised p-3" aria-labelledby="deck-weaknesses">
                    <h4 id="deck-weaknesses" class="font-semibold text-ink-strong">Weaknesses</h4>
                    <ul v-if="weaknesses.length > 0" class="mt-2 list-disc space-y-1 pl-5 text-sm text-ink-muted">
                        <li v-for="line in weaknesses" :key="line">{{ line }}</li>
                    </ul>
                    <p v-else class="mt-2 text-sm text-ink-muted">
                        Every category has at least one card carrying an effect in it.
                    </p>
                </section>
            </div>

            <!-- The brief asks for a recommended replacement. It is not built, and saying so is the
                 honest rendering: naming a card to swap would be a judgement the corpus cannot source,
                 and a deck has no optimal arrangement this tool is permitted to claim. -->
            <section class="mt-4 rounded-md border border-dashed border-rule bg-raised p-3" aria-labelledby="deck-replacement">
                <h4 id="deck-replacement" class="font-semibold text-ink-strong">Recommended replacement</h4>
                <p class="mt-1 text-sm text-ink-muted">
                    Not built. No source states which card belongs in a slot, so a recommended replacement
                    here would be this tool's own opinion dressed as a fact. The category figures above are
                    what a Trainer has to weigh it against.
                </p>
            </section>
        </template>
    </section>
</template>
