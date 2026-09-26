<?php

declare(strict_types=1);

use App\Enums\AliasLanguage;
use App\Enums\MatchTier;
use App\Models\Umamusume;
use App\Models\UmamusumeAlias;
use App\Services\DataPipeline\CrossReferenceMatcher;

function matcher(): CrossReferenceMatcher
{
    return app(CrossReferenceMatcher::class);
}

it('matches an exact normalized key ignoring case and punctuation', function (): void {
    Umamusume::factory()->create([
        'name' => 'Rice Shower',
        'slug' => 'rice-shower',
        'match_key' => 'riceshower',
    ]);

    $result = matcher()->match('RICE-SHOWER');

    expect($result['tier'])->toBe(MatchTier::Exact)
        ->and($result['umamusume'])->not->toBeNull();
});

it('matches through an alias row when no key equals', function (): void {
    $umamusume = Umamusume::factory()->create([
        'name' => 'Special Week',
        'slug' => 'special-week',
        'match_key' => 'specialweek',
    ]);

    UmamusumeAlias::factory()->create([
        'umamusume_id' => $umamusume->id,
        'alias' => 'スペシャルウィーク',
        'language' => AliasLanguage::Japanese,
    ]);

    $result = matcher()->match('スペシャルウィーク');

    expect($result['tier'])->toBe(MatchTier::Alias)
        ->and($result['umamusume']->id)->toBe($umamusume->id);
});

it('proposes the closest catalog row when similarity is above the threshold', function (): void {
    Umamusume::factory()->create([
        'name' => 'Special Week',
        'slug' => 'special-week',
        'match_key' => 'specialweek',
    ]);

    $result = matcher()->match('Special Weck');

    expect($result['tier'])->toBe(MatchTier::Fuzzy)
        ->and($result['umamusume'])->not->toBeNull()
        ->and($result['confidence'])->toBeGreaterThan(85.0);
});

it('routes an unrelated name to the none tier', function (): void {
    Umamusume::factory()->create([
        'name' => 'Rice Shower',
        'slug' => 'rice-shower',
        'match_key' => 'riceshower',
    ]);

    $result = matcher()->match('Qwertyuiopasdf');

    expect($result['tier'])->toBe(MatchTier::None)
        ->and($result['umamusume'])->toBeNull();
});
