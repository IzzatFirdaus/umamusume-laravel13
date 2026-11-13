<?php

declare(strict_types=1);

use App\Services\Career\TrackblazerVariant;

/*
 * Ruling F1's derivation, in isolation. No database: every case states the six aptitude letters and
 * asserts the classification the ordered rule produces. `tests/Unit` runs without `RefreshDatabase`,
 * which is why the roster-wide distribution lives in the Feature suite instead — it needs the seeded
 * catalogue.
 */

/**
 * @return array{classification: string|null, track: string, reason: string, evidence: list<array{label: string, letter: string}>}
 */
function variantOf(string $turf, string $dirt, string $sprint, string $mile, string $medium, string $long): array
{
    return TrackblazerVariant::derive($turf, $dirt, $sprint, $mile, $medium, $long);
}

it('classifies a dirt-dominant trainee as dirt_leaning on the dirt track', function (string $turf, string $dirt): void {
    // Haru Urara's own reading (turf G / dirt A) is the source's named example, and the ladder is
    // ordered so any dirt letter above the turf letter selects it — not just the two extremes.
    $variant = variantOf($turf, $dirt, 'A', 'B', 'G', 'G');

    expect($variant['classification'])->toBe(TrackblazerVariant::DIRT_LEANING)
        ->and($variant['track'])->toBe(TrackblazerVariant::DIRT_LEANING)
        ->and($variant['reason'])->toContain('outranks turf');
})->with([
    ['G', 'A'],
    ['F', 'A'],
    ['E', 'B'],
    ['G', 'B'],
]);

it('classifies a turf trainee whose only strong distance is sprint as limited_turf_range', function (): void {
    // Curren Chan's own reading (turf A / dirt F / sprint A / mile D / medium G / long G).
    $variant = variantOf('A', 'F', 'A', 'D', 'G', 'G');

    expect($variant['classification'])->toBe(TrackblazerVariant::LIMITED_TURF_RANGE)
        ->and($variant['track'])->toBe(TrackblazerVariant::LIMITED_TURF_RANGE)
        ->and($variant['reason'])->toContain('sprint A is the only distance');
});

it('refuses the limited-turf-range track when a second distance is also strong', function (string $mile, string $medium, string $long): void {
    // The predicate is "sprint is the ONLY strong distance". A mile B alongside it is a broad-range
    // trainee, not a short-range one, so the standard track applies.
    $variant = variantOf('A', 'F', 'A', $mile, $medium, $long);

    expect($variant['classification'])->toBe(TrackblazerVariant::STANDARD)
        ->and($variant['track'])->toBe(TrackblazerVariant::STANDARD);
})->with([
    ['B', 'G', 'G'],
    ['A', 'G', 'G'],
    ['G', 'B', 'G'],
    ['G', 'G', 'B'],
]);

it('classifies both-strong surfaces as ambiguous rather than choosing one', function (string $turf, string $dirt): void {
    // Ruling F1's core case: turf >= B and dirt >= B with no published tie-break. The classification
    // is `ambiguous` and the RENDERED track is `standard` — the two differ here on purpose.
    //
    // Note the third row: turf A / dirt B is ambiguous, not dirt_leaning, because dirt does not
    // OUTRANK turf there. The predicate order is why, and it is what makes "both strong" a distinct
    // case rather than a subset of the dirt one.
    $variant = variantOf($turf, $dirt, 'F', 'A', 'A', 'G');

    expect($variant['classification'])->toBe(TrackblazerVariant::AMBIGUOUS)
        ->and($variant['track'])->toBe(TrackblazerVariant::STANDARD)
        ->and($variant['reason'])->toContain('no published source breaks the tie');
})->with([
    ['A', 'A'],
    ['A', 'B'],
    ['B', 'B'],
]);

it('names the exact-tie case as an equality rather than a preference', function (): void {
    // Agnes Digital, Inari One and Victoire Pisa all read A/A. No field ordering separates them, and
    // the reason must say so rather than implying the higher letter was chosen.
    $variant = variantOf('A', 'A', 'F', 'B', 'A', 'A');

    expect($variant['classification'])->toBe(TrackblazerVariant::AMBIGUOUS)
        ->and($variant['reason'])->toContain('exactly equal')
        ->and($variant['reason'])->toContain('no field ordering separates them');
});

it('never resolves an A/A tie to a variant track', function (): void {
    // The failure mode ruling F1 names: the naive threshold rule assigns all 8 ambiguous trainees to
    // dirt_leaning. This asserts the A/A pair produces neither variant track, under every sprint
    // and distance reading that could otherwise tip it.
    foreach ([['A', 'D', 'G', 'G'], ['B', 'B', 'B', 'B'], ['F', 'A', 'A', 'A']] as [$sprint, $mile, $medium, $long]) {
        expect(variantOf('A', 'A', $sprint, $mile, $medium, $long)['classification'])
            ->toBe(TrackblazerVariant::AMBIGUOUS);
    }
});

it('classifies a trainee matching neither variant as standard', function (): void {
    // Turf outranks dirt, sprint is strong, but long is ALSO strong — a broad turf trainee. Neither
    // the dirt nor the limited-range condition holds.
    $variant = variantOf('A', 'G', 'A', 'D', 'G', 'A');

    expect($variant['classification'])->toBe(TrackblazerVariant::STANDARD)
        ->and($variant['track'])->toBe(TrackblazerVariant::STANDARD)
        ->and($variant['reason'])->toContain('neither variant');
});

it('derives no classification when an aptitude letter is missing', function (): void {
    // Missing is not B and not G: the predicate that would have read it cannot run, so the
    // classification is null and the track falls back to standard.
    $variant = TrackblazerVariant::derive('A', null, 'A', 'D', 'G', 'G');

    expect($variant['classification'])->toBeNull()
        ->and($variant['track'])->toBe(TrackblazerVariant::STANDARD)
        ->and($variant['reason'])->toContain('not recorded on Dirt');
});

it('names every missing axis when the aptitude set is entirely unrecorded', function (): void {
    // The six fabricated roster rows read null on all six aptitudes. The reason lists them rather than
    // reporting one, which is what a Trainer needs to know to go and record them.
    $variant = TrackblazerVariant::derive(null, null, null, null, null, null);

    expect($variant['classification'])->toBeNull()
        ->and($variant['track'])->toBe(TrackblazerVariant::STANDARD)
        ->and($variant['evidence'])->toBe([]);

    foreach (['Turf', 'Dirt', 'Sprint', 'Mile', 'Medium', 'Long'] as $axis) {
        expect($variant['reason'])->toContain($axis);
    }
});

it('does not treat an unrecognised aptitude letter as a missing one', function (): void {
    // `S` belongs to the team-rank ladder, not the aptitude ladder. Reporting it as "not recorded"
    // would be a false statement about a column that holds a value.
    $variant = TrackblazerVariant::derive('A', 'S', 'A', 'D', 'G', 'G');

    expect($variant['classification'])->toBeNull()
        ->and($variant['reason'])->toContain('does not recognise')
        ->and($variant['reason'])->toContain('Dirt');
});

it('carries the six aptitude letters as evidence, in display order', function (): void {
    $variant = variantOf('A', 'F', 'A', 'D', 'G', 'G');

    expect(array_map(static fn (array $row): string => $row['label'], $variant['evidence']))
        ->toBe(['Turf', 'Dirt', 'Sprint', 'Mile', 'Medium', 'Long'])
        ->and(array_map(static fn (array $row): string => $row['letter'], $variant['evidence']))
        ->toBe(['A', 'F', 'A', 'D', 'G', 'G']);
});

it('derives the same classification for every ordering of its three predicates on disjoint inputs', function (): void {
    // On today's roster the three predicate sets are disjoint, which is why the ORDER is written down
    // rather than left to whichever `if` came first. This pins the two inputs whose predicates could
    // otherwise both fire, and asserts the documented order wins.
    $dirtBeatsAmbiguity = variantOf('A', 'A', 'A', 'D', 'G', 'G');
    expect($dirtBeatsAmbiguity['classification'])->toBe(TrackblazerVariant::AMBIGUOUS);

    // Ambiguity is checked before limited-range: turf A / dirt B with sprint A and nothing else strong
    // would satisfy BOTH the both-strong reading and the short-range reading. The surface question is
    // unsettled, so ambiguity wins and the range is never asked.
    $ambiguityBeatsLimitedRange = variantOf('A', 'B', 'A', 'D', 'G', 'G');
    expect($ambiguityBeatsLimitedRange['classification'])->toBe(TrackblazerVariant::AMBIGUOUS);

    // Dirt outranks turf whatever the letters: turf B / dirt A is dirt_leaning even though B is a
    // strong surface, because the predicate reads the COMPARISON and not a threshold.
    $dirtBeatsEverything = variantOf('B', 'A', 'A', 'D', 'G', 'G');
    expect($dirtBeatsEverything['classification'])->toBe(TrackblazerVariant::DIRT_LEANING);
});
