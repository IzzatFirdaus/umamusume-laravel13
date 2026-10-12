<?php

declare(strict_types=1);

use App\Models\Legacy\LegacySelectionPayload;
use App\Models\Umamusume;
use App\Services\Career\SetupDraft;
use App\Services\Legacy\AncestryGraph;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * R3-09: a grandparent takes a rank, not only a name and Sparks.
 *
 * The source's tree reads four ranks off the grandparents (B+, A, UG, UG) while the screen offered the
 * two `Own rank` fields on the parents only, so four of the six ranks had nowhere to go. The two fields
 * are stored as lists aligned by index with `ancestors`, exactly as a grandparent's Sparks already are,
 * and an ancestor nobody read a rank for stores nothing.
 *
 * A payload builder per file: a focused run loads only the file it names, so a fixture reached across
 * test files is undefined there.
 */
function grandparentRankDraft(?array $letters = null, ?array $ranks = null): array
{
    $legacy = [
        'legacy_id' => null,
        'rank' => null,
        'is_guest' => false,
        'ancestors' => ['Daiwa Scarlet', 'Seiun Sky'],
        'ancestors_sparks' => [[], []],
        'sparks' => [],
    ];

    // Posted only when the case names them, so an absent key and an all-blank list are each exercised
    // rather than one of them simulated by an empty array.
    if ($letters !== null) {
        $legacy['ancestors_rank_letters'] = $letters;
    }

    if ($ranks !== null) {
        $legacy['ancestors_rank'] = $ranks;
    }

    return [
        'affinity' => null,
        'legacies' => [
            $legacy,
            [
                'legacy_id' => null,
                'rank' => null,
                'is_guest' => false,
                'ancestors' => [null, null],
                'sparks' => [],
            ],
        ],
    ];
}

function grandparentRankSetup(): void
{
    SetupDraft::write([
        'scenario' => 'unity_cup',
        'umamusume_id' => Umamusume::factory()->create(['name' => 'Rice Shower'])->id,
    ]);
}

it('stores a grandparent letter and star count and reads both back on the contract', function (): void {
    grandparentRankSetup();

    $this->put(route('career.legacy.store'), grandparentRankDraft(['B+', 'UG'], [null, 3]))
        ->assertRedirect(route('career.legacy'));

    $stored = SetupDraft::legacySelection();

    expect($stored['legacies'][0]['ancestors_rank_letters'])->toBe(['B+', 'UG'])
        ->and($stored['legacies'][0]['ancestors_rank'])->toBe([null, 3]);

    $this->get(route('career.preflight'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('contract.build.legacy.members.0.ancestors.0.rank_label', 'Rank B+')
            ->where('contract.build.legacy.members.0.ancestors.0.rank', null)
            ->where('contract.build.legacy.members.0.ancestors.1.rank_label', 'Rank UG')
            ->where('contract.build.legacy.members.0.ancestors.1.rank', 3));
});

it('states a grandparent letter with no star count when only the letter was read', function (): void {
    $graph = AncestryGraph::build(
        LegacySelectionPayload::fromArray([
            'legacies' => [
                [
                    'rank' => null,
                    'is_guest' => false,
                    'ancestors' => ['Daiwa Scarlet', 'Seiun Sky'],
                    'ancestors_rank_letters' => ['A', null],
                    'sparks' => [],
                ],
                ['rank' => null, 'is_guest' => false, 'ancestors' => [null, null], 'sparks' => []],
            ],
            'affinity' => null,
        ]),
        'Trainee',
        'Parent A',
        'Parent B',
    );

    $first = $graph['parents'][0]['ancestors'][0];
    $second = $graph['parents'][0]['ancestors'][1];

    expect($first['rank_letter'])->toBe('A')
        ->and($first['rank_label'])->toBe('Rank A')
        // The absence says so in words rather than borrowing a zero or a dash.
        ->and($second['rank_letter'])->toBeNull()
        ->and($second['rank_label'])->toBe('Rank not recorded');
});

it('omits both grandparent rank keys when no ancestor rank was recorded', function (): void {
    $record = LegacySelectionPayload::fromArray([
        'legacies' => [
            [
                'rank' => null,
                'is_guest' => false,
                'ancestors' => ['Daiwa Scarlet', 'Seiun Sky'],
                'ancestors_rank_letters' => [null, null],
                'ancestors_rank' => [null, null],
                'sparks' => [],
            ],
            ['rank' => null, 'is_guest' => false, 'ancestors' => [null, null], 'sparks' => []],
        ],
        'affinity' => null,
    ])->legacies[0];

    expect($record)->not->toHaveKey('ancestors_rank_letters')
        ->and($record)->not->toHaveKey('ancestors_rank');
});

it('accepts UG on a grandparent and refuses a letter outside the client set', function (): void {
    grandparentRankSetup();

    $this->put(route('career.legacy.store'), grandparentRankDraft(['UG', null], [null, null]))
        ->assertSessionMissing('errors');

    grandparentRankSetup();

    $this->put(route('career.legacy.store'), grandparentRankDraft(['SS', null], [null, null]))
        ->assertSessionHasErrors('legacies.0.ancestors_rank_letters.0');
});
