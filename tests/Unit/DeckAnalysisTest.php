<?php

declare(strict_types=1);

use App\Services\DeckAnalysis;

/**
 * The six deck-analysis categories. Every figure is an anchor the source states, or an integer added
 * up from those anchors, so these tests fix the two things that can go wrong without touching the data:
 * which effects land where, and which of them may be added across cards at all.
 *
 * No database. `atCap()` has already resolved the anchors by the time they reach `DeckAnalysis`, and
 * the resolver is pinned in `SupportCardEffectsTest.php`.
 */

/**
 * One equipped card for the service: its name and its at-cap effects, in the shape the controller maps.
 *
 * @param  array<int, array{effect_id: int, name: string|null, display: string, value: int, symbol: string|null, calc: string|null}>  $effects
 * @return array{card_name: string, effects: array<int, array{effect_id: int, name: string|null, display: string, value: int, symbol: string|null, calc: string|null}>}
 */
function deckCard(string $name, array $effects): array
{
    return ['card_name' => $name, 'effects' => $effects];
}

/**
 * `false` stands for "name this after its id", which is what the rest of the suite wants; an explicit
 * `null` is a dictionary gap and must not be filled in by the helper.
 *
 * @return array{effect_id: int, name: string|null, display: string, value: int, symbol: string|null, calc: string|null}
 */
function deckEffect(int $id, string $display, int $value, string $symbol = 'percent', ?string $calc = null, string|false|null $name = false): array
{
    return [
        'effect_id' => $id,
        'name' => $name === false ? 'Effect '.$id : $name,
        'display' => $display,
        'value' => $value,
        'symbol' => $symbol,
        'calc' => $calc,
    ];
}

/**
 * @param  array<string, mixed>  $categories
 * @return array<string, mixed>
 */
function deckEffectLine(array $categories, string $category, int $effectId): array
{
    return collect($categories[$category]['effects'])->firstWhere('effect_id', $effectId);
}

it('shelves every one of the thirty-five published effects exactly once, in a category or in the named gap', function (): void {
    $shelved = 0;

    foreach (DeckAnalysis::CATEGORIES as $ids) {
        $shelved += count($ids);
    }

    $all = [];

    foreach (DeckAnalysis::CATEGORIES as $ids) {
        foreach ($ids as $id) {
            $all[] = $id;
        }
    }

    sort($all);
    $all = array_unique($all);

    // Each id appears once, which is what makes the categories a partition of the effects the export
    // publishes rather than a set of overlapping lists a Trainer could read two ways. The nine
    // zero-coverage ids sit in no category at all and are named separately.
    expect(count($all))->toBe(26)
        ->and(DeckAnalysis::LABELS)->toHaveCount(6)
        ->and(array_keys(DeckAnalysis::LABELS))->toBe(array_keys(DeckAnalysis::CATEGORIES))
        ->and(count($all) + count(DeckAnalysis::UNCATEGORISED))->toBe(35);
});

it('groups the race-day effects into one category with the others kept out', function (): void {
    $cards = [
        deckCard('Tokai Teio [Dream Big!]', [deckEffect(15, '8%', 8, name: 'Race Bonus')]),
        deckCard('Gold Ship [Tracen Academy]', [deckEffect(16, '10%', 10, name: 'Fan Bonus')]),
        deckCard('Special Week [Tracen Academy]', [deckEffect(3, '146', 146, symbol: 'none', name: 'Speed Bonus')]),
    ];

    $analysis = DeckAnalysis::build($cards);

    expect($analysis['categories']['race_bonus']['blank'])->toBeFalse()
        ->and($analysis['categories']['race_bonus']['effects'])->toHaveCount(2)
        ->and($analysis['categories']['training_power']['blank'])->toBeFalse()
        ->and($analysis['categories']['training_power']['effects'])->toHaveCount(1)
        ->and($analysis['categories']['early_run']['blank'])->toBeTrue()
        ->and($analysis['categories']['early_run']['effects'])->toBe([]);
});

it('adds a flat amount across the cards that carry it and names the basis', function (): void {
    $cards = [
        deckCard('Tokai Teio [Dream Big!]', [deckEffect(3, '100', 100, symbol: 'none', name: 'Speed Bonus')]),
        deckCard('Special Week [Tracen Academy]', [deckEffect(3, '146', 146, symbol: 'none', name: 'Speed Bonus')]),
        deckCard('Curlin [Just Keep Going]', [deckEffect(6, '50', 50, symbol: 'none', name: 'Guts Bonus')]),
    ];

    $speed = deckEffectLine(DeckAnalysis::build($cards)['categories'], 'training_power', 3);

    expect($speed['mode'])->toBe('flat')
        ->and($speed['total']['value'])->toBe(246)
        ->and($speed['total']['display'])->toBe('246')
        ->and($speed['total']['basis'])->toBe('summed')
        ->and($speed['cards'])->toHaveCount(2);
});

it('adds an effect the export marks additive and says so rather than calling it a sum', function (): void {
    $cards = [deckCard('Mejiro McQueen [Piece of Mind]', [deckEffect(19, '25', 25, symbol: 'none', calc: 'add', name: 'Specialty Priority')])];

    $effect = deckEffectLine(DeckAnalysis::build($cards)['categories'], 'training_power', 19);

    expect($effect['mode'])->toBe('additive')
        ->and($effect['total']['value'])->toBe(25)
        ->and($effect['total']['display'])->toBe('25')
        ->and($effect['total']['basis'])->toBe('added');
});

it('refuses to total an effect the export marks multiplicative', function (): void {
    $cards = [
        deckCard('Tokai Teio [Dream Big!]', [deckEffect(1, '18%', 18, calc: 'mult', name: 'Friendship Bonus')]),
        deckCard('Super Creek [Piece of Mind]', [deckEffect(1, '20%', 20, calc: 'mult', name: 'Friendship Bonus')]),
    ];

    $effect = deckEffectLine(DeckAnalysis::build($cards)['categories'], 'training_power', 1);

    // 38% is not a figure the client states for two cards held together, so the category stops at the
    // two values and the mode carries the reason instead.
    expect($effect['mode'])->toBe('multiplicative')
        ->and($effect['total'])->toBeNull()
        ->and($effect['cards'])->toHaveCount(2)
        ->and($effect['cards'][0]['display'])->toBe('18%')
        ->and($effect['cards'][1]['display'])->toBe('20%');
});

it('marks an effect the dictionary has no row for by its own id', function (): void {
    // Id 32 has no name in any language (UMAMUSUME_REFERENCE.md §1.4.8), and appears on fourteen
    // cards, so this is a real gap rather than a contrivance.
    $cards = [deckCard('Quiet Star [Tracen Academy]', [deckEffect(32, '5', 5, symbol: 'none', name: null)])];

    $effect = deckEffectLine(DeckAnalysis::build($cards)['categories'], 'skills', 32);

    expect($effect['name'])->toBeNull()
        ->and($effect['total']['display'])->toBe('5');
});

it('states the empty deck as six blank categories rather than six zeros', function (): void {
    $analysis = DeckAnalysis::build([]);

    expect($analysis['covered'])->toBe(0)
        ->and(collect($analysis['categories'])->filter(fn (array $category): bool => ! $category['blank'])->all())->toBe([])
        ->and($analysis['weaknesses'])->toHaveCount(6)
        ->and($analysis['strengths'])->toBe([])
        ->and($analysis['uncategorised'])->toBe([]);
});

it('counts each card once per category even when it carries three effects in it', function (): void {
    $cards = [deckCard('Curlin [Just Keep Going]', [
        deckEffect(4, '100', 100, symbol: 'none', name: 'Stamina Bonus'),
        deckEffect(6, '50', 50, symbol: 'none', name: 'Guts Bonus'),
        deckEffect(7, '20', 20, symbol: 'none', name: 'Wit Bonus'),
    ])];

    $analysis = DeckAnalysis::build($cards);

    expect($analysis['strengths'])->toBe(['Training power: 1 of the 1 cards carry an effect here.'])
        ->and($analysis['weaknesses'])->toHaveCount(5);
});

it('writes the strengths and weaknesses as counts over the cards equipped', function (): void {
    $cards = [
        deckCard('Tokai Teio [Dream Big!]', [deckEffect(15, '8%', 8, name: 'Race Bonus')]),
        deckCard('Super Creek [Piece of Mind]', [deckEffect(1, '20%', 20, calc: 'mult', name: 'Friendship Bonus')]),
    ];

    $analysis = DeckAnalysis::build($cards);

    expect($analysis['strengths'])->toBe([
        'Training power: 1 of the 2 cards carry an effect here.',
        'Race bonus: 1 of the 2 cards carry an effect here.',
    ])
        ->and($analysis['weaknesses'])->toBe([
            'Early run: no card in the deck carries an effect here.',
            'Safety: no card in the deck carries an effect here.',
            'Events: no card in the deck carries an effect here.',
            'Skills: no card in the deck carries an effect here.',
        ]);
});

it('keeps an effect in no category from going silent', function (): void {
    $cards = [deckCard('Quiet Star [Tracen Academy]', [deckEffect(53, '40', 40, symbol: 'none', name: 'A new effect')])];

    $analysis = DeckAnalysis::build($cards);

    expect($analysis['uncategorised'])->toHaveCount(1)
        ->and($analysis['uncategorised'][0]['effect_id'])->toBe(53)
        ->and($analysis['uncategorised'][0]['name'])->toBe('A new effect')
        ->and($analysis['uncategorised'][0]['mode'])->toBe('flat');
});

it('does not let an effect the zero-coverage list names go silent', function (): void {
    // Id 20 is one of the nine §1.4.8 records as carried by no card. If a re-fetch ever changed that,
    // the deck holding it would still have to say so rather than disappear under no category.
    $cards = [deckCard('Quiet Star [Tracen Academy]', [deckEffect(20, '150', 150, symbol: 'none', name: 'Max Speed')])];

    $analysis = DeckAnalysis::build($cards);

    expect($analysis['uncategorised'])->toHaveCount(1)
        ->and($analysis['uncategorised'][0]['effect_id'])->toBe(20)
        ->and(collect($analysis['categories'])->filter(fn (array $category): bool => ! $category['blank'])->all())->toBe([]);
});
