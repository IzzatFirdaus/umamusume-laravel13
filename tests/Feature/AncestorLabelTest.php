<?php

declare(strict_types=1);

use App\Models\Legacy\LegacySelectionPayload;

/*
 * The per-ancestor distinguishing label (plan `consolidated-ui-ux-and-remaining-work-2026-11-18.md` §C3).
 *
 * The client can spell two grandparents the same; the stored record has to let a Trainer say which is
 * which on read-back. The label is entered, never computed, and the payload keeps its exact shape when
 * no label was stated — the same exact-shape preservation `ancestors_meta` and the Spark lists follow.
 */

function labelledPayload(): LegacySelectionPayload
{
    return LegacySelectionPayload::fromArray([
        'legacies' => [
            [
                'rank' => 3,
                'is_guest' => false,
                'ancestors' => ['Daiwa Scarlet', 'Daiwa Scarlet'],
                'ancestors_labels' => ['Daiwa A', 'Daiwa B'],
                'sparks' => [],
            ],
            ['rank' => null, 'is_guest' => false, 'ancestors' => [null, null], 'sparks' => []],
        ],
        'affinity' => null,
    ]);
}

it('keeps two same-named ancestors distinguishable through the stored payload', function (): void {
    $payload = labelledPayload();

    expect($payload->legacies[0]['ancestors'])->toBe(['Daiwa Scarlet', 'Daiwa Scarlet'])
        ->and($payload->legacies[0]['ancestors_labels'])->toBe(['Daiwa A', 'Daiwa B'])
        // The round trip the acceptance names: save shape == reload shape.
        ->and(LegacySelectionPayload::fromArray($payload->toArray())->legacies[0]['ancestors_labels'])
        ->toBe(['Daiwa A', 'Daiwa B']);
});

it('drops an all-null label list so an unlabelled payload keeps its exact shape', function (): void {
    $payload = LegacySelectionPayload::fromArray([
        'legacies' => [
            [
                'rank' => 3,
                'is_guest' => false,
                'ancestors' => ['Daiwa Scarlet', null],
                'ancestors_labels' => [null, null],
                'sparks' => [],
            ],
            ['rank' => null, 'is_guest' => false, 'ancestors' => [null, null], 'sparks' => []],
        ],
        'affinity' => null,
    ]);

    expect($payload->legacies[0])->not->toHaveKey('ancestors_labels');
});

it('refuses a label list whose length does not match the ancestor names', function (): void {
    LegacySelectionPayload::fromArray([
        'legacies' => [
            [
                'rank' => 3,
                'is_guest' => false,
                'ancestors' => ['Daiwa Scarlet', 'Daiwa Scarlet'],
                'ancestors_labels' => ['Daiwa A'],
                'sparks' => [],
            ],
            ['rank' => null, 'is_guest' => false, 'ancestors' => [null, null], 'sparks' => []],
        ],
        'affinity' => null,
    ]);
})->throws(InvalidArgumentException::class);

it('refuses a label that exceeds the bound the request states', function (): void {
    LegacySelectionPayload::fromArray([
        'legacies' => [
            [
                'rank' => 3,
                'is_guest' => false,
                'ancestors' => ['Daiwa Scarlet', null],
                'ancestors_labels' => [str_repeat('a', 256), null],
                'sparks' => [],
            ],
            ['rank' => null, 'is_guest' => false, 'ancestors' => [null, null], 'sparks' => []],
        ],
        'affinity' => null,
    ]);
})->throws(InvalidArgumentException::class);
