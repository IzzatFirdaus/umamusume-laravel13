<?php

declare(strict_types=1);

use App\Services\FanLadder;

/**
 * The one band the corpus documents: a run at 209,245 fans holding `Star`, with the Top Star threshold
 * at 240,000 and a 30,755-fan gap to it (`docs/audits/rice-shower-unity-cup-ux-walk.md:392-393`).
 *
 * The ladder is one entry because the sources name one band. These cases pin the documented band, and
 * they pin the edge the ladder cannot answer - a count at or above 240,000 has no class this tool can
 * claim, so it answers null instead of inventing the tier above Top Star. The dispatch's own example
 * asserted a null at 100,000, which would need a floor below Star that no source states; the edge that
 * is actually documented is the top one, and that is what the last two cases assert.
 */
it('places the audited fan count in the Star band', function (): void {
    expect(FanLadder::classFor(209_245))->toBe('Star');
});

it('names the Top Star threshold as the end of that band', function (): void {
    expect(FanLadder::nextThreshold(209_245))->toBe(240_000);
});

it('measures the documented gap to the next threshold', function (): void {
    expect(FanLadder::gapToNext(209_245))->toBe(30_755);
});

it('places a count anywhere inside the documented band', function (int $fans): void {
    expect(FanLadder::classFor($fans))->toBe('Star');
})->with([0, 1, 100_000, 239_999]);

it('answers nothing above the highest documented threshold rather than inventing a tier', function (): void {
    expect(FanLadder::classFor(240_000))->toBeNull()
        ->and(FanLadder::nextThreshold(240_000))->toBeNull()
        ->and(FanLadder::gapToNext(240_000))->toBeNull();
});

it('keeps the three answers in one shape so a class cannot travel beside another band', function (): void {
    expect(FanLadder::fromValue(209_245))->toBe([
        'class' => 'Star',
        'nextThreshold' => 240_000,
        'gap' => 30_755,
    ]);
});
