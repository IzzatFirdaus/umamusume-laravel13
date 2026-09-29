<?php

declare(strict_types=1);

use App\Models\Umamusume;
use App\Models\UmamusumeProfile;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

/*
 * The profile block: a sibling table at 1:1 with `umamusume`, carrying its own provenance
 * because `umamusume` carries none (ADR-0003 Amendment R3). These tests pin the grain, the
 * identity rule a re-fetch depends on, the delete rule, and the exact column list the
 * migration's own reasoning enumerates.
 */

it('creates a profile, links it to its trainee and casts its numbers', function (): void {
    $umamusume = Umamusume::factory()->create();
    $profile = UmamusumeProfile::factory()->create(['umamusume_id' => $umamusume->id]);

    expect($profile->umamusume->id)->toBe($umamusume->id)
        ->and($umamusume->profile->id)->toBe($profile->id)
        ->and($profile->height)->toBe(158)
        ->and($profile->birth_year)->toBe(1995)
        ->and($profile->three_sizes_b)->toBe(81)
        ->and($profile->is_manual)->toBeFalse();
});

it('keeps one profile per trainee so a re-fetch cannot accumulate a second', function (): void {
    $umamusume = Umamusume::factory()->create();
    UmamusumeProfile::factory()->create(['umamusume_id' => $umamusume->id]);

    expect(fn () => UmamusumeProfile::factory()->create(['umamusume_id' => $umamusume->id]))
        ->toThrow(QueryException::class);
});

it('accepts a trainee with no profile at all', function (): void {
    // The source is not fetched in every deployment, and 28 of its 163 rows name trainees this
    // catalog does not track. A trainee without a profile is a normal state, not a broken one.
    $umamusume = Umamusume::factory()->create();

    expect($umamusume->profile)->toBeNull();
});

it('deletes a trainee\'s profile with her', function (): void {
    $umamusume = Umamusume::factory()->create();
    UmamusumeProfile::factory()->create(['umamusume_id' => $umamusume->id]);

    $umamusume->delete();

    expect(UmamusumeProfile::query()->count())->toBe(0);
});

it('stores a partial profile rather than refusing the row', function (): void {
    // Measured: va_en and three_sizes are each absent on 10 of the 135 roster rows, and
    // birth_year on a further set. A NOT NULL column would have made the parser invent a value
    // or drop the trainee, so every field is nullable and absence is stored as absence.
    $profile = UmamusumeProfile::factory()->partial()->create();
    $withoutYear = UmamusumeProfile::factory()->withoutBirthYear()->create();

    expect($profile->va_en)->toBeNull()
        ->and($profile->hasThreeSizes())->toBeFalse()
        ->and($profile->va_ja)->toBe('和氣あず未')
        ->and($withoutYear->birth_year)->toBeNull()
        ->and($withoutYear->hasFullBirthday())->toBeFalse();
});

it('reports a complete three_sizes and a complete birthday as complete', function (): void {
    $profile = UmamusumeProfile::factory()->create();

    expect($profile->hasThreeSizes())->toBeTrue()
        ->and($profile->hasFullBirthday())->toBeTrue();
});

it('ships exactly the columns the migration enumerates', function (): void {
    // An exact pin rather than a `toContain` sweep, for the reason
    // CharacterCardSchemaTest gives: a column added later has to be a decision someone made
    // visible here, not a column that appeared because nothing was looking.
    $expected = [
        'id',
        'umamusume_id',
        'name_ja',
        'va_ja',
        'va_en',
        'birth_year',
        'birth_month',
        'birth_day',
        'height',
        'three_sizes_b',
        'three_sizes_h',
        'three_sizes_w',
        'source_url',
        'snapshot_path',
        'fetched_at',
        'source_timezone',
        'is_manual',
        'created_at',
        'updated_at',
    ];

    expect(Schema::getColumnListing('umamusume_profiles'))->toBe($expected);
});

it('carries no column for sex or race', function (): void {
    // The document has both on every row. Neither is one of the six fields the block shows, and
    // CONSTRAINTS.md C-4 governs what this tool calls these characters — so a column nothing
    // renders is where that rule would be easiest to breach.
    $listing = Schema::getColumnListing('umamusume_profiles');

    expect($listing)->not->toContain('sex')
        ->and($listing)->not->toContain('race');
});
