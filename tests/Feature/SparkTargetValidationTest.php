<?php

declare(strict_types=1);

/**
 * Typed Spark targets.
 *
 * The target category is a transient form field: the payload stores the target string verbatim
 * (`ADR-0010`), but the write refuses a target that does not belong to the category the Trainer picked,
 * so `Stat -> Long` cannot be stored at the boundary.
 */
function sparkTargetPayload(string $category, string $target): array
{
    return [
        'affinity' => null,
        'legacies' => [
            [
                'legacy_id' => null,
                'rank' => null,
                'rank_letter' => null,
                'is_guest' => false,
                'ancestors' => [null, null],
                'ancestors_sparks' => [[], []],
                'sparks' => [
                    ['kind' => 'blue', 'category' => $category, 'target' => $target, 'stars' => 1],
                ],
            ],
            [
                'legacy_id' => null,
                'rank' => null,
                'rank_letter' => null,
                'is_guest' => false,
                'ancestors' => [null, null],
                'ancestors_sparks' => [[], []],
                'sparks' => [],
            ],
        ],
    ];
}

it('refuses a Stat Spark whose target is not a stat', function (): void {
    $this->put(route('career.legacy.store'), sparkTargetPayload('Stat', 'Long'))
        ->assertSessionHasErrors(['legacies.0.sparks.0.target']);
});

it('accepts a Stat Spark naming a stat', function (): void {
    $this->put(route('career.legacy.store'), sparkTargetPayload('Stat', 'Speed'))
        ->assertSessionHasNoErrors();
});

it('accepts an Aptitude Spark naming an aptitude', function (): void {
    $this->put(route('career.legacy.store'), sparkTargetPayload('Aptitude', 'Long'))
        ->assertSessionHasNoErrors();
});

it('refuses an Aptitude Spark whose target is not an aptitude', function (): void {
    $this->put(route('career.legacy.store'), sparkTargetPayload('Aptitude', 'Speed'))
        ->assertSessionHasErrors(['legacies.0.sparks.0.target']);
});
