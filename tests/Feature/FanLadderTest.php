<?php

declare(strict_types=1);

use App\Services\FanLadder;

/**
 * The fan class ladder, filled from the client's nine-step pyramid
 * (`docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md:474`, SR §1.15 frame-verified): each tier's
 * published threshold is the count that REACHES it, so a band spans from its own threshold to the
 * next tier's. The audited reading (209,245 fans -> `Star`, 30,755 to `Top Star`) is unchanged by
 * the fill - that is the regression case below.
 *
 * The source's two non-numeric tiers (`Beginner` = "1st place", `Debut / Maiden` = entry class)
 * have no fan count to key on, so counts below the documented 5,000 answer null rather than a
 * class nobody sourced; the renderer prints the bare number.
 */
it('places the audited fan count in the Star band', function (): void {
    expect(FanLadder::classFor(209_245))->toBe('Star');
});

it('names the Top Star threshold as the end of that band', function (): void {
    expect(FanLadder::nextThreshold(209_245))->toBe(240_000);
});

it('names the class the next threshold opens, so the gap can be stated against a name', function (): void {
    // The reading ships as `Star (30,755 to Top Star)`: the class the count holds, the gap, and the
    // name the gap counts toward. The name is the ladder's own (`config('uma.fan_ladder')`), never a
    // string the renderer carries, so one band's class cannot travel beside another band's name.
    expect(FanLadder::nextClassName(209_245))->toBe('Top Star');
});

it('measures the documented gap to the next threshold', function (): void {
    expect(FanLadder::gapToNext(209_245))->toBe(30_755);
});

it('places a count at each documented threshold in that threshold\'s class', function (int $fans, string $class): void {
    expect(FanLadder::classFor($fans))->toBe($class);
})->with([
    [5_000, 'Bronze'], [19_999, 'Bronze'],
    [20_000, 'Silver'], [49_999, 'Silver'],
    [50_000, 'Gold'], [99_999, 'Gold'],
    [100_000, 'Platinum'], [159_999, 'Platinum'],
    [160_000, 'Star'], [209_245, 'Star'], [239_999, 'Star'],
    [240_000, 'Top Star'], [319_999, 'Top Star'],
    [320_000, 'Legend'], [396_191, 'Legend'],
]);

it('answers nothing below the first fan-counted tier rather than inventing a class', function (): void {
    // `Beginner` and `Debut / Maiden` are keyed on "1st place" and an entry class, not a fan count
    // (`docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md:474`). A count under Bronze has no class this
    // tool can claim, so it answers null instead of borrowing Bronze's name.
    expect(FanLadder::classFor(0))->toBeNull()
        ->and(FanLadder::classFor(4_999))->toBeNull()
        ->and(FanLadder::nextThreshold(4_999))->toBeNull()
        ->and(FanLadder::gapToNext(4_999))->toBeNull();
});

it('keeps the audited reading unchanged after the ladder filled', function (): void {
    // The one reading the corpus states: 209,245 fans, class Star, 30,755 to Top Star.
    expect(FanLadder::fromValue(209_245))->toBe([
        'class' => 'Star',
        'nextThreshold' => 240_000,
        'gap' => 30_755,
        'nextClass' => 'Top Star',
    ]);
});

it('closes the ceiling with Legend and names no gap past it', function (): void {
    // `Legend` is the last documented threshold, so its band runs to PHP_INT_MAX and the gap above
    // it is null - the renderer keys the gap suffix on `gap`, so a Legend run prints the class
    // alone, never a gap to a class nobody published.
    expect(FanLadder::classFor(320_000))->toBe('Legend')
        ->and(FanLadder::nextThreshold(320_000))->toBeNull()
        ->and(FanLadder::gapToNext(320_000))->toBeNull()
        ->and(FanLadder::nextClassName(320_000))->toBeNull();
});
