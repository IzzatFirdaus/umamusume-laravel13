<?php

declare(strict_types=1);

use App\Models\SupportCard;
use App\Models\SupportEffect;
use App\Services\DeckAnalysis;
use App\Services\SupportCardEffects;

/*
 * A-0.4 (ADR-0029 D2): a run's reached effect values are derived from the card level, never stored.
 *
 * The anchor vector is [effect_id, v1, v5, ..., v50]; this fixture is a linear +2 ladder so an
 * interpolated value is distinguishable from either bracketing anchor.
 */

/**
 * @param  list<int>  $anchors  the eleven stated values at levels 1, 5, ..., 50
 */
function reachedEffectCard(array $anchors = [10, 12, 14, 16, 18, 20, 22, 24, 26, 28, 30]): SupportCard
{
    SupportEffect::factory()->create([
        'effect_id' => 1,
        'name_en' => 'Friendship Bonus',
        'calc' => 'mult',
        'symbol' => 'percent',
    ]);

    return SupportCard::factory()->create([
        'effects' => [[1, ...$anchors]],
    ]);
}

it('interpolates between the bracketing anchors and clamps at both ends', function (): void {
    $card = reachedEffectCard();

    $value = fn (int $level): int => SupportCardEffects::forLevel($card->id, $level)[0]['value'];

    expect($value(1))->toBe(10)
        ->and($value(0))->toBe(10)
        ->and($value(30))->toBe(22)
        // 22 at level 30, 24 at 35: 22 + (3/5) * 2 = 23.2, rounded to 23.
        ->and($value(33))->toBe(23)
        ->and($value(35))->toBe(24)
        ->and($value(50))->toBe(30)
        ->and($value(99))->toBe(30);
});

it('matches atCap at the top and marks the basis in the deck analysis', function (): void {
    $card = reachedEffectCard();
    $dictionary = SupportCardEffects::dictionary();

    expect(SupportCardEffects::forLevel($card->id, 50))->toBe(SupportCardEffects::atCap($card, $dictionary));

    $catalog = DeckAnalysis::build(DeckAnalysis::inputFor(collect([$card])));
    $reached = DeckAnalysis::build(DeckAnalysis::inputFor(collect([$card]), [$card->id => 35]));

    $find = static function (array $analysis): array {
        foreach ($analysis['categories'] as $category) {
            foreach ($category['effects'] as $effect) {
                if ($effect['effect_id'] === 1) {
                    return $effect;
                }
            }
        }

        return [];
    };

    $catalogLine = $find($catalog);
    $reachedLine = $find($reached);

    expect($catalogLine['cards'][0]['display'])->toBe('30%')
        ->and($catalogLine['cards'][0]['basis'])->toBe('catalog')
        ->and($catalogLine['cards'][0]['level'])->toBeNull()
        ->and($reachedLine['cards'][0]['display'])->toBe('24%')
        ->and($reachedLine['cards'][0]['basis'])->toBe('level')
        ->and($reachedLine['cards'][0]['level'])->toBe(35);
});
