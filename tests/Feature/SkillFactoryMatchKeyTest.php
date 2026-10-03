<?php

declare(strict_types=1);

use App\Models\Skill;
use App\Services\DataPipeline\NameNormalizer;

/**
 * KI-39: `SkillFactory` derived `match_key` with `Str::slug`, and production derives it with
 * `NameNormalizer::normalize()`. `Str::slug` transliterates punctuation away, the normalizer keeps it,
 * so 119 of 200 sampled Global skill names got a key the pipeline never writes. A search test seeded
 * through the factory was then asserting against a key production cannot produce.
 *
 * The faker's own names are two plain words, so the two algorithms agree on every name the factory
 * generates by itself. The divergence only becomes visible when a test states a name, which is exactly
 * what a search fixture does.
 */
it('writes the production key for a name the test states', function (): void {
    $skill = Skill::factory()->create(['name' => "Empress's Pride"]);

    expect($skill->match_key)
        ->toBe(app(NameNormalizer::class)->normalize("Empress's Pride"))
        ->and($skill->match_key)->toBe("empress'spride");
});

it('leaves a stated key alone, because a desynced key is how the alias tiers are tested', function (): void {
    $skill = Skill::factory()->create([
        'name' => 'Corner Adept',
        'match_key' => 'deliberatelyother',
    ]);

    expect($skill->match_key)->toBe('deliberatelyother');
});

it('agrees with the normalizer on a name the factory generates itself', function (): void {
    $skill = Skill::factory()->create();

    expect($skill->match_key)->toBe(app(NameNormalizer::class)->normalize($skill->name));
});
