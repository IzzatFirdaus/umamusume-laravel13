<?php

declare(strict_types=1);

use App\Models\Legacy\LegacySelectionPayload;
use App\Services\Career\SetupDraft;
use App\Services\Legacy\AncestryGraph;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * The letter rank beside the star count.
 *
 * The client shows a Legacy's own standing as a letter and her star count as a number. Both are read
 * off the screen and stored as entered; a node prints the letter it was given, or names the absence,
 * and the star field is untouched by either.
 */
function rankPayload(?string $letter): LegacySelectionPayload
{
    return LegacySelectionPayload::fromArray([
        'legacies' => [
            ['rank' => 4, 'rank_letter' => $letter, 'is_guest' => false, 'ancestors' => [null, null], 'sparks' => []],
            ['rank' => null, 'is_guest' => false, 'ancestors' => [null, null], 'sparks' => []],
        ],
        'affinity' => null,
    ]);
}

it('renders a recorded letter rank and leaves the star field alone', function (): void {
    $graph = AncestryGraph::build(rankPayload('B+'), 'Trainee', 'Parent A', 'Parent B');

    expect($graph['parents'][0]['rank_label'])->toBe('Rank B+')
        ->and($graph['parents'][0]['rank_letter'])->toBe('B+')
        ->and($graph['parents'][0]['rank'])->toBe(4);
});

it('names the absence when no letter rank was read', function (): void {
    $graph = AncestryGraph::build(rankPayload(null), 'Trainee', 'Parent A', 'Parent B');

    expect($graph['parents'][0]['rank_label'])->toBe('Rank not recorded')
        ->and($graph['parents'][0]['rank_letter'])->toBeNull()
        // The star count is still whatever was entered.
        ->and($graph['parents'][0]['rank'])->toBe(4);
});

it('round-trips a letter rank through the stored payload', function (): void {
    $payload = rankPayload('B+');

    expect($payload->legacies[0]['rank_letter'])->toBe('B+')
        ->and(LegacySelectionPayload::fromArray($payload->toArray())->legacies[0]['rank_letter'])->toBe('B+');
});

it('renders the letter rank on the setup ancestry step', function (): void {
    SetupDraft::write(['legacy_selection' => rankPayload('B+')->toArray()]);

    $this->get(route('career.legacy'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/LegacySelect')
            ->where('graph.parents.0.rank_label', 'Rank B+')
            ->where('graph.parents.0.rank_letter', 'B+')
            ->has('rankLetters'));
});
