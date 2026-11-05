<?php

declare(strict_types=1);

use App\Models\SupportCard;
use App\Models\SupportEffect;
use App\Services\SupportCardEffects;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Vocabulary consistency (D11): both surfaces that render effect names resolve them
 * from SupportCardEffects::dictionary() using the same effect_id keys.
 *
 * Surface 1: SupportCards/Show.vue (detail) — 'effectStages' prop built by
 *             SupportCardEffects::stages() with name from dictionary.
 * Surface 2: Career/DeckSelect.vue (deck step) — 'slots.*.card.ladder.*.effects'
 *             built by DeckSelectController::levelLadder() using
 *             SupportCardEffects::atLevel() with the shared dictionary.
 */
it('both surfaces resolve effect names from SupportCardEffects::dictionary()', function (): void {
    SupportEffect::factory()->create(['effect_id' => 10, 'name_en' => 'Speed Bonus', 'symbol' => 'percent', 'calc' => 'add']);
    SupportEffect::factory()->create(['effect_id' => 20, 'name_en' => 'Friendship Bonus', 'symbol' => 'percent', 'calc' => 'add']);

    $anchorsTen = [10, -1, -1, -1, -1, -1, -1, 15, -1, -1, 20, -1];
    $anchorsTwenty = [20, -1, -1, -1, -1, -1, -1, 15, -1, -1, 20, -1];
    $card = SupportCard::factory()->ssr()->create(['effects' => [$anchorsTen, $anchorsTwenty]]);

    $dictionary = SupportCardEffects::dictionary();

    // Surface 1: detail page
    test()->get(route('support-cards.show', $card))->assertInertia(fn (Assert $page) => $page
        ->component('SupportCards/Show')
        ->has('effectStages', 2)
        ->where('effectStages.0.effect_id', 10)
        ->where('effectStages.0.name', $dictionary[10]['name'])
        ->where('effectStages.1.effect_id', 20)
        ->where('effectStages.1.name', $dictionary[20]['name'])
    );

    // Surface 2: deck step ladder
    test()->put(route('career.deck.store'), [
        'deck' => [1 => ['support_card_id' => (string) $card->id, 'ownership' => 'OWNED', 'card_level' => 30]],
    ])->assertSessionHasNoErrors();

    test()->get(route('career.deck'))->assertInertia(fn (Assert $page) => $page
        ->component('Career/DeckSelect')
        ->has('slots.0.card.ladder', 5)
        ->where('slots.0.card.ladder.0.effects.0.effect_id', 10)
        ->where('slots.0.card.ladder.0.effects.0.name', $dictionary[10]['name'])
        ->where('slots.0.card.ladder.0.effects.1.effect_id', 20)
        ->where('slots.0.card.ladder.0.effects.1.name', $dictionary[20]['name'])
    );

    // Cross-check: effect_id sets on both surfaces are equal and present in dictionary
    expect(array_keys($dictionary))->toContain(10);
    expect(array_keys($dictionary))->toContain(20);
});

it('a card with no effects yields empty effect sets on both surfaces', function (): void {
    $card = SupportCard::factory()->ssr()->create(['effects' => []]);

    test()->get(route('support-cards.show', $card))->assertInertia(fn (Assert $page) => $page
        ->component('SupportCards/Show')
        ->where('effectStages', [])
    );

    test()->put(route('career.deck.store'), [
        'deck' => [1 => ['support_card_id' => (string) $card->id, 'ownership' => 'OWNED', 'card_level' => '']],
    ])->assertSessionHasNoErrors();

    test()->get(route('career.deck'))->assertInertia(fn (Assert $page) => $page
        ->component('Career/DeckSelect')
        ->where('slots.0.card.ladder.0.effects', [])
    );
});
