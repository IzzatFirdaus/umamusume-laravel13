<?php

declare(strict_types=1);

use App\Models\MatchCandidate;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * F14 for the review queue's verdict form, now split by the Vue port (ADR-0020 §1).
 *
 * Each of the three controls must carry an accessible name that identifies its own candidate
 * (a generic "Status" on two cards is technically named and useless). The controls render
 * client-side now, so the accessible-name assertions - a DOM relation between a <label> and its
 * control - live in tests/browser/review.spec.ts, where the browser computes the names. This
 * file asserts the server side: the page receives the candidate names the controls are labelled
 * from, and the alias languages the third control offers.
 */

it('passes the queue its candidates and the alias languages the controls need', function (): void {
    MatchCandidate::factory()->create(['proposed_name' => 'Unmatched One']);
    MatchCandidate::factory()->create(['proposed_name' => 'Unmatched Two']);

    test()->get('/review')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Review/Index')
            ->has('candidates.data', 2)
            ->has('aliasLanguages'));
});
