<?php

declare(strict_types=1);

use App\Models\SupportCard;
use App\Models\SupportEffect;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The assigned-card summary of the setup wizard's deck step (`SCR-CAR-009`).
 *
 * Two things are pinned here, and they are the two the section claims. The first is the ladder: the level a
 * card of each rarity reaches at every break tier, 0 unbroken to 4 Max Limit Break, and the value the source
 * states at each of those levels (`UMAMUSUME_REFERENCE.md` §1.4.2, §1.4.7). The second is that the ladder is
 * a property of the RARITY and not a reading of anyone's collection: the draft carries a level and an
 * ownership flag and nothing else (`ADR-0029` D1, `ADR-0014`), so no slot may arrive carrying a break count
 * of its own.
 *
 * The fixture is the reference guide's own worked example: Friendship Bonus stated as 15 at level 30 and 20
 * at level 45, which puts 16 at 35 and 18 at 40 (§1.4.7's floor rule).
 */

/**
 * The export's row for one effect: id, then eleven values at levels 1, 5, 10 … 50.
 *
 * @var list<int>
 */
const DECK_SUMMARY_ANCHORS = [1, -1, -1, -1, -1, -1, -1, 15, -1, -1, 20, -1];

/**
 * A card whose only effect is the §1.4.7 anchor pair, at the named factory state or the R default.
 *
 * @param  'ssr'|'sr'|'friend'|null  $state
 */
function deckSummaryCard(?string $state = null): SupportCard
{
    SupportEffect::factory()->create([
        'effect_id' => 1,
        'name_en' => 'Friendship Bonus',
        'calc' => 'mult',
        'symbol' => 'percent',
    ]);

    $factory = SupportCard::factory();

    return $state === null
        ? $factory->create(['effects' => [DECK_SUMMARY_ANCHORS]])
        : $factory->{$state}()->create(['effects' => [DECK_SUMMARY_ANCHORS]]);
}

it('sends an SSR card the five tiers from Lvl 30 unbroken to Lvl 50 at Max Limit Break', function (): void {
    $card = deckSummaryCard('ssr');

    test()->put(route('career.deck.store'), [
        'deck' => [1 => ['support_card_id' => (string) $card->id, 'ownership' => 'OWNED', 'card_level' => 30]],
    ])->assertSessionHasNoErrors();

    test()->get(route('career.deck'))->assertInertia(fn (Assert $page) => $page
        ->component('Career/DeckSelect')
        ->where('slots.0.card.ladder.0.breaks', 0)
        ->where('slots.0.card.ladder.0.level', 30)
        ->where('slots.0.card.ladder.0.effects.0.display', '15%')
        // 15 + 5·5/15 = 16.67, floored: §1.4.7's rule is truncation, and 17 would be the wrong model.
        ->where('slots.0.card.ladder.1.level', 35)
        ->where('slots.0.card.ladder.1.effects.0.display', '16%')
        ->where('slots.0.card.ladder.2.level', 40)
        ->where('slots.0.card.ladder.2.effects.0.display', '18%')
        ->where('slots.0.card.ladder.3.level', 45)
        ->where('slots.0.card.ladder.3.effects.0.display', '20%')
        // Past its last stated anchor the value carries up, the rule `atCap()` already applies.
        ->where('slots.0.card.ladder.4.is_maximum', true)
        ->where('slots.0.card.ladder.4.level', 50)
        ->where('slots.0.card.ladder.4.effects.0.display', '20%')
    );
});

it('ladders each rarity from its own base, so an R card tops out at 40 and an SR at 45', function (): void {
    $r = deckSummaryCard();
    $sr = SupportCard::factory()->sr()->create(['effects' => [DECK_SUMMARY_ANCHORS]]);

    test()->put(route('career.deck.store'), [
        'deck' => [
            1 => ['support_card_id' => (string) $r->id, 'ownership' => 'OWNED', 'card_level' => ''],
            2 => ['support_card_id' => (string) $sr->id, 'ownership' => 'OWNED', 'card_level' => ''],
        ],
    ])->assertSessionHasNoErrors();

    test()->get(route('career.deck'))->assertInertia(fn (Assert $page) => $page
        ->component('Career/DeckSelect')
        ->where('slots.0.card.rarity_word', 'R')
        ->where('slots.0.card.ladder.0.level', 20)
        ->where('slots.0.card.ladder.1.level', 25)
        ->where('slots.0.card.ladder.4.level', 40)
        ->where('slots.0.card.ladder.4.is_maximum', true)
        ->where('slots.1.card.rarity_word', 'SR')
        ->where('slots.1.card.ladder.0.level', 25)
        ->where('slots.1.card.ladder.3.level', 40)
        ->where('slots.1.card.ladder.4.level', 45)
    );
});

it('carries the ownership flag and the recorded level beside the ladder and no break count', function (): void {
    $card = deckSummaryCard('ssr');

    test()->put(route('career.deck.store'), [
        'deck' => [6 => ['support_card_id' => (string) $card->id, 'ownership' => 'RENTED', 'card_level' => 35]],
    ])->assertSessionHasNoErrors();

    test()->get(route('career.deck'))->assertInertia(fn (Assert $page) => $page
        ->component('Career/DeckSelect')
        ->where('slots.5.label', 'Slot 6 · Friends')
        ->where('slots.5.is_friend', true)
        ->where('slots.5.ownership', 'RENTED')
        ->where('slots.5.card_level', 35)
        ->where('slots.5.card.ladder.1.level', 35)
        // The section reads a level against the published ladder, so nothing sends it a count to print.
        ->missing('slots.5.breaks')
        ->missing('slots.5.card.breaks')
        ->missing('slots.5.card.limit_break')
    );
});

it('leaves the ladder with the card, so an empty slot carries nothing to summarise', function (): void {
    test()->get(route('career.deck'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/DeckSelect')
            ->where('slots.0.card', null)
            ->where('slots.0.ownership', null)
            ->where('slots.0.card_level', null)
            ->has('slots', 6)
        );
});
