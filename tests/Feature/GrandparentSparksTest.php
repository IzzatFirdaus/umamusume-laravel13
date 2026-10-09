<?php

declare(strict_types=1);

use App\Models\Legacy\LegacySelectionPayload;
use App\Services\Legacy\AncestryGraph;

/**
 * Sparks on a grandparent.
 *
 * A parent brings two ancestors of her own, and the client can show a Spark on any of the six nodes.
 * The grandparents' Sparks are stored as a list aligned with the ancestor names, read back onto the
 * node, and named as an absence when there are none.
 */
function grandparentPayload(array $ancestorSparks): LegacySelectionPayload
{
    return LegacySelectionPayload::fromArray([
        'legacies' => [
            [
                'rank' => null,
                'is_guest' => false,
                'ancestors' => ['Gold Ship', 'Vodka'],
                'ancestors_sparks' => $ancestorSparks,
                'sparks' => [],
            ],
            ['rank' => null, 'is_guest' => false, 'ancestors' => [null, null], 'sparks' => []],
        ],
        'affinity' => null,
    ]);
}

it('carries sparks recorded on a grandparent onto the node', function (): void {
    $graph = AncestryGraph::build(
        grandparentPayload([
            [['kind' => 'pink', 'target' => 'Late Surger', 'stars' => 2]],
            [],
        ]),
        'Trainee',
        'Parent A',
        'Parent B',
    );

    $ancestor = $graph['parents'][0]['ancestors'][0];

    expect($ancestor['name'])->toBe('Gold Ship')
        ->and($ancestor['sparks'])->toHaveCount(1)
        ->and($ancestor['sparks'][0]['kind'])->toBe('pink')
        ->and($ancestor['sparks'][0]['target'])->toBe('Late Surger')
        ->and($ancestor['sparks'][0]['stars'])->toBe(2)
        ->and($ancestor['sparks_label'])->toBe('1 Spark')
        ->and($graph['parents'][0]['ancestors'][1]['sparks_label'])->toBe('No sparks recorded');
});

it('names the absence when a grandparent carries no sparks', function (): void {
    $graph = AncestryGraph::build(
        grandparentPayload([[], []]),
        'Trainee',
        'Parent A',
        'Parent B',
    );

    expect($graph['parents'][0]['ancestors'][0]['sparks'])->toBe([])
        ->and($graph['parents'][0]['ancestors'][0]['sparks_label'])->toBe('No sparks recorded');
});

it('round-trips grandparent sparks through the stored payload', function (): void {
    $payload = grandparentPayload([
        [['kind' => 'pink', 'target' => 'Late Surger', 'stars' => 2]],
        [],
    ]);

    $read = LegacySelectionPayload::fromArray($payload->toArray());

    expect($read->legacies[0]['ancestors_sparks'][0][0]['target'])->toBe('Late Surger')
        ->and($read->legacies[0]['ancestors_sparks'][1])->toBe([]);
});
