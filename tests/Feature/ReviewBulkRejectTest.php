<?php

declare(strict_types=1);

use App\Enums\CandidateStatus;
use App\Enums\MatchTier;
use App\Models\MatchCandidate;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * N2.8: the 304-row Pending race backlog has to be disposable through the UI. A race candidate can
 * only be rejected (`ResolveMatchCandidate` refuses promotion), so bulk is Reject-only by contract;
 * this pins that no bulk path can promote, and that a failure rolls the whole batch back.
 */
it('rejects only the named candidates and promotes nothing', function (): void {
    $trainee = Umamusume::factory()->create();
    $ids = MatchCandidate::factory()->count(3)->create([
        'status' => CandidateStatus::Pending,
        'match_tier' => MatchTier::Fuzzy,
        'suggested_umamusume_id' => $trainee->id,
        'payload' => ['name' => 'Rice Shower', 'entity' => 'umamusume'],
    ])->pluck('id')->all();
    $kept = MatchCandidate::factory()->create(['status' => CandidateStatus::Pending]);

    $before = Umamusume::query()->count();

    test()->post(route('review.bulk'), ['ids' => $ids, 'status' => CandidateStatus::Rejected->value])
        ->assertRedirect(route('review.index'));

    foreach ($ids as $id) {
        expect(MatchCandidate::findOrFail($id)->status)->toBe(CandidateStatus::Rejected);
    }
    expect($kept->fresh()->status)->toBe(CandidateStatus::Pending)
        ->and(Umamusume::query()->count())->toBe($before);
});

it('refuses a bulk verdict that is not Reject', function (): void {
    $candidate = MatchCandidate::factory()->create(['status' => CandidateStatus::Pending]);

    test()->post(route('review.bulk'), [
        'ids' => [$candidate->id],
        'status' => CandidateStatus::Confirmed->value,
    ])->assertSessionHasErrors('status');

    expect($candidate->fresh()->status)->toBe(CandidateStatus::Pending);
});

it('rejects the whole batch when any id does not exist, changing nothing', function (): void {
    $first = MatchCandidate::factory()->create(['status' => CandidateStatus::Pending]);

    test()->post(route('review.bulk'), [
        'ids' => [$first->id, 999999],
        'status' => CandidateStatus::Rejected->value,
    ])->assertSessionHasErrors('ids.*');

    expect($first->fresh()->status)->toBe(CandidateStatus::Pending);
});

it('filters the queue by entity and match tier and echoes them in pagination links', function (): void {
    MatchCandidate::factory()->create([
        'status' => CandidateStatus::Pending,
        'source_key' => 'gametora-races',
        'match_tier' => MatchTier::Fuzzy,
        'payload' => ['entity' => 'race'],
    ]);
    MatchCandidate::factory()->create([
        'status' => CandidateStatus::Pending,
        'source_key' => 'gametora-characters',
        'match_tier' => MatchTier::None,
        'payload' => ['entity' => 'umamusume'],
    ]);

    test()->get(route('review.index', ['entity' => 'race', 'match_tier' => 'Fuzzy']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.entity', 'race')
            ->where('filters.match_tier', 'Fuzzy')
            ->where('candidates.total', 1));
});
