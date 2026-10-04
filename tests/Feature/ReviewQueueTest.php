<?php

declare(strict_types=1);

use App\Enums\AliasLanguage;
use App\Enums\CandidateStatus;
use App\Models\MatchCandidate;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * D-2 / D-56 for the review queue's verdict form.
 *
 * The form validates three fields: `status`, `umamusume_id` (must exist) and
 * `alias_language` (required when the verdict is Aliased). Two failures a Trainer can reach -
 * an alias with no language, and an id not in the catalog - used to fail silently.
 *
 * After the Vue port (ADR-0020 §1) the message reaches the page as Inertia's shared `errors`
 * prop. That the prop is populated is asserted here; that it renders, and that every control
 * carries an accessible name, is asserted in tests/browser/review.spec.ts.
 */

it('reports the missing alias language on an Aliased verdict', function (): void {
    $candidate = MatchCandidate::factory()->create();

    test()->post("/review/{$candidate->id}", ['status' => CandidateStatus::Aliased->value])
        ->assertSessionHasErrors('alias_language');

    test()->withHeader('referer', url('/review'))
        ->followingRedirects()
        ->post("/review/{$candidate->id}", ['status' => CandidateStatus::Aliased->value])
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('errors.alias_language'));
});

it('stacks the unknown id and the missing language rather than showing one', function (): void {
    $candidate = MatchCandidate::factory()->create();

    test()->withHeader('referer', url('/review'))
        ->followingRedirects()
        ->post("/review/{$candidate->id}", [
            'status' => CandidateStatus::Aliased->value,
            'umamusume_id' => 999999,
        ])
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('errors.umamusume_id')
            ->has('errors.alias_language'));
});

it('reports the enum failure on a verdict that is not one of the three', function (): void {
    $candidate = MatchCandidate::factory()->create();

    test()->post("/review/{$candidate->id}", ['status' => 'Merged'])
        ->assertSessionHasErrors('status');

    test()->withHeader('referer', url('/review'))
        ->followingRedirects()
        ->post("/review/{$candidate->id}", ['status' => 'Merged'])
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('errors.status'));
});

it('accepts a complete Aliased verdict with no errors', function (): void {
    $umamusume = Umamusume::factory()->create();
    $candidate = MatchCandidate::factory()->create();

    test()->post("/review/{$candidate->id}", [
        'status' => CandidateStatus::Aliased->value,
        'umamusume_id' => $umamusume->id,
        'alias_language' => AliasLanguage::Japanese->value,
    ])->assertSessionHasNoErrors();
});
