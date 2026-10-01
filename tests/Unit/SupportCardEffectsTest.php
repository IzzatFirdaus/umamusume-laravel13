<?php

declare(strict_types=1);

use App\Models\SupportCard;
use App\Services\SupportCardEffects;

/**
 * The dictionary shape the service takes: effect id to its published name and unit word.
 *
 * @return array<int, array{name: string, symbol: string|null}>
 */
function effectDictionary(array $rows): array
{
    $out = [];

    foreach ($rows as $id => $row) {
        $out[$id] = ['name' => $row[0], 'symbol' => $row[1]];
    }

    return $out;
}

it('takes the highest stated anchor as the cap, not the last column', function (): void {
    $card = new SupportCard(['effects' => [[1, 10, -1, -1, -1, -1, 20, 20, -1, -1, 25, -1]]]);

    $rows = SupportCardEffects::atCap($card, effectDictionary([1 => ['Friendship Bonus', 'percent']]));

    expect($rows)->toHaveCount(1)
        ->and($rows[0]['name'])->toBe('Friendship Bonus')
        ->and($rows[0]['display'])->toBe('25%');
});

it('renders a flat amount without a unit and a level with the client word', function (): void {
    $card = new SupportCard(['effects' => [
        [14, -1, 10, -1, -1, -1, 20, -1, -1, -1, 25, -1],
        [17, -1, 1, -1, -1, -1, 2, -1, -1, -1, 2, -1],
    ]]);

    $rows = SupportCardEffects::atCap($card, effectDictionary([
        14 => ['Initial Friendship Gauge', 'none'],
        17 => ['Hint Levels', 'level'],
    ]));

    expect($rows[0]['display'])->toBe('25')
        ->and($rows[1]['display'])->toBe('Lv 2');
});

it('drops an effect with no value at any level', function (): void {
    $card = new SupportCard(['effects' => [[2, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1]]]);

    expect(SupportCardEffects::atCap($card, effectDictionary([2 => ['Mood Effect', 'percent']])))->toBe([]);
});

it('keeps the value and names the gap when the dictionary has no row', function (): void {
    $card = new SupportCard(['effects' => [[77, -1, -1, -1, -1, -1, -1, -1, -1, -1, 5, -1]]]);

    $rows = SupportCardEffects::atCap($card, effectDictionary([]));

    expect($rows)->toHaveCount(1)
        ->and($rows[0]['effect_id'])->toBe(77)
        ->and($rows[0]['name'])->toBeNull()
        ->and($rows[0]['display'])->toBe('5');
});

it('ignores a stored row that is not the eleven anchors plus the id', function (): void {
    $card = new SupportCard(['effects' => [
        [1, 10, -1],
        [1, 10, -1, -1, -1, -1, 20, 20, -1, -1, 25, -1],
    ]]);

    $rows = SupportCardEffects::atCap($card, effectDictionary([1 => ['Friendship Bonus', 'percent']]));

    expect($rows)->toHaveCount(1)->and($rows[0]['display'])->toBe('25%');
});

it('returns nothing for a card that stores no effects', function (): void {
    expect(SupportCardEffects::atCap(new SupportCard(['effects' => []]), effectDictionary([])))->toBe([]);
});
