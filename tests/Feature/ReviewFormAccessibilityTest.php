<?php

declare(strict_types=1);

use App\Enums\CandidateStatus;
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
 *
 * The languages belong to the detail page only: the queue stopped rendering a verdict form in
 * FQ-10, so passing its options to the list was a prop no component read.
 */

it('passes the queue its candidates and the resolved-today count', function (): void {
    MatchCandidate::factory()->create(['proposed_name' => 'Unmatched One']);
    MatchCandidate::factory()->create(['proposed_name' => 'Unmatched Two']);

    test()->get('/review')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Review/Index')
            ->has('candidates.data', 2)
            ->where('resolvedToday', 0)
            // The verdict form's options are the detail page's; the queue renders no select.
            ->missing('aliasLanguages'));
});

it('counts a decided candidate as resolved today', function (): void {
    MatchCandidate::factory()->create([
        'proposed_name' => 'Decided One',
        'status' => CandidateStatus::Confirmed,
    ]);
    MatchCandidate::factory()->create(['proposed_name' => 'Still Pending']);

    test()->get('/review')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('resolvedToday', 1)
            ->where('candidates.total', 1));
});

it('passes the detail page the one candidate the verdict form is about', function (): void {
    $candidate = MatchCandidate::factory()->create([
        'proposed_name' => 'Unmatched One',
        'source_key' => 'fixture',
    ]);

    test()->get('/review/'.$candidate->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Review/Detail')
            // The shape the inline form used to receive from the queue row, now delivered by the route:
            // `CandidateForm` reads `id`, `proposed_name` and `suggested_umamusume_id` off it, and the
            // page's own header reads the rest.
            ->where('candidate.id', $candidate->id)
            ->where('candidate.proposed_name', 'Unmatched One')
            ->where('candidate.source_key', 'fixture')
            ->has('candidate.match_tier')
            ->has('candidate.match_tier_label')
            ->has('candidate.suggested_umamusume_id')
            ->has('aliasLanguages'));
});
