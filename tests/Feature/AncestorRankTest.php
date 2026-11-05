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

it('keeps the letter rank in the payload the setup step stores, and reads it back', function (): void {
    /*
     * `career.legacy` stopped rendering a letter rank in Phase D (`74163b34`): the step became the
     * wireframe's two parent cards rather than the six-node graph, and the controller no longer
     * builds `graph` at all — `grep -n "'graph' =>" app/Http/Controllers/Career/LegacySelectController.php`
     * returns nothing. The card carries the star count (`parentA.stars`), and the letter is what the
     * step STORES for the graph surfaces that remain.
     *
     * So this case asserts the round trip through the step's own draft rather than a prop the step
     * no longer sends. The rendering rules for the letter are the three cases above, which exercise
     * `AncestryGraph::build()` directly and still hold.
     */
    SetupDraft::write(['legacy_selection' => rankPayload('B+')->toArray()]);

    $this->get(route('career.legacy'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/LegacySelect')
            ->where('payload.legacies.0.rank_letter', 'B+')
            // `parentA` is null for this fixture: the slot resolves from the veteran library, and
            // this payload names no `veteran_id`, so there is no card to carry a star count. That
            // resolution is `LegacySelectController::slots()`'s subject, not this case's.
            ->has('rankLetters'));
});

it('accepts the UG letter rank the client shows for a Legacy', function (): void {
    // The canonical Rice Shower ancestry reads Legacy 2 at rank UG, and both of her grandparents too
    // (`docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md:153-155`), so the write boundary must accept the
    // letter the client prints. It did not: `Rule::in(RANK_LETTERS)` stopped at `S+` and the refusal
    // never reached the screen (the Phase 1 walk of 2026-10-11), so one unlisted letter silently
    // discarded the whole Legacy save.
    $this->put(route('career.legacy.store'), [
        'affinity' => null,
        'legacies' => [
            [
                'legacy_id' => null,
                'rank' => null,
                'rank_letter' => 'UG',
                'is_guest' => false,
                'ancestors' => [null, null],
                'sparks' => [],
            ],
            [
                'legacy_id' => null,
                'rank' => null,
                'rank_letter' => null,
                'is_guest' => false,
                'ancestors' => [null, null],
                'sparks' => [],
            ],
        ],
    ])->assertSessionHasNoErrors();
});
