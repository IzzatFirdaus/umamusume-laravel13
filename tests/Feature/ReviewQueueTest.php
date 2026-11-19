<?php

declare(strict_types=1);

use App\Actions\ResolveMatchCandidate;
use App\Enums\AliasLanguage;
use App\Enums\CandidateStatus;
use App\Models\MatchCandidate;
use App\Models\Umamusume;
use App\Models\UmamusumeAlias;
use Illuminate\Support\Facades\DB;
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

/*
 * S1.11: the three verdicts the queue offered and could not honour.
 *
 * Each of these used to answer success while writing nothing, which is worse than a refusal: the row
 * left the queue and the Trainer had no reason to look again.
 */

it('refuses a Pending verdict, because Pending is the absence of a verdict', function (): void {
    $candidate = MatchCandidate::factory()->create(['status' => CandidateStatus::Pending]);

    // `handle()` mapped Pending to `null` work and then stamped and saved the row, so this posted a
    // success flash for a request that decided nothing. Reopen is the door back to Pending.
    test()->post("/review/{$candidate->id}", ['status' => CandidateStatus::Pending->value])
        ->assertSessionHasErrors('status');

    expect($candidate->fresh()->status)->toBe(CandidateStatus::Pending);
});

it('refuses an Aliased verdict when there is no trainee to hang the alias on', function (): void {
    // No suggestion and no id posted: `alias()` returned early while `handle()` stamped the row
    // `Aliased`, so the queue's only non-Reject door for these rows reported a success that recorded
    // no alias anywhere.
    $candidate = MatchCandidate::factory()->create([
        'status' => CandidateStatus::Pending,
        'suggested_umamusume_id' => null,
    ]);

    test()->withHeader('referer', url('/review'))
        ->followingRedirects()
        ->post("/review/{$candidate->id}", [
            'status' => CandidateStatus::Aliased->value,
            'alias_language' => AliasLanguage::English->value,
        ])
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('errors.umamusume_id'));

    // Still in the queue, still unaliased.
    expect($candidate->fresh()->status)->toBe(CandidateStatus::Pending)
        ->and(UmamusumeAlias::query()->count())->toBe(0);
});

/*
 * The plan's third S1.11 item is measured false, and this case records that rather than pretending.
 *
 * S1.11 read `ReviewController::reopen()` as permitting a duplicate promotion: "Reopen then Confirm
 * creates a second `umamusume` row and a second provenance row". Measured through the real action
 * (`ResolveMatchCandidate::handle()` called twice on one candidate, inside a rolled-back transaction):
 *
 *   data_sources before=133  after first Confirm=134  after Reopen + Confirm=134
 *   umamusume rows named the trainee = 1
 *
 * `PromoteMatchedRecord` upserts by normalized `match_key` and `data_sources` is keyed on
 * (entity, key, field), so a second Confirm rewrites the same rows instead of adding them. There is no
 * duplicate to prevent, and adding a refusal in `reopen()` would have blocked a harmless action.
 *
 * The case stays because the PROPERTY is the thing worth protecting: if a future promotion path ever
 * starts inserting instead of upserting, this is where it shows up.
 */
it('re-promoting after a reopen rewrites the same catalog and provenance rows', function (): void {
    $umamusume = Umamusume::factory()->create(['name' => 'Rice Shower']);
    $candidate = MatchCandidate::factory()->create([
        'status' => CandidateStatus::Pending,
        'proposed_name' => 'Rice Shower',
        'payload' => ['name' => 'Rice Shower', 'url' => 'https://example.test/rice'],
        'suggested_umamusume_id' => $umamusume->id,
    ]);

    $action = app(ResolveMatchCandidate::class);
    $trainessBefore = Umamusume::query()->count();
    $provenanceBefore = DB::table('data_sources')->count();

    $action->handle($candidate, ['status' => CandidateStatus::Confirmed->value, 'umamusume_id' => $umamusume->id]);

    $trainessAfter = Umamusume::query()->count();
    $provenanceAfter = DB::table('data_sources')->count();

    // Reopen is the door back to Pending, then Confirm again on the same candidate.
    $candidate->status = CandidateStatus::Pending;
    $candidate->save();

    $action->handle($candidate->fresh(), ['status' => CandidateStatus::Confirmed->value, 'umamusume_id' => $umamusume->id]);

    // Upsert, not insert: both counts are unchanged by the second promotion.
    expect(Umamusume::query()->count())->toBe($trainessAfter)
        ->and($trainessAfter)->toBe($trainessBefore)
        ->and(DB::table('data_sources')->count())->toBe($provenanceAfter)
        ->and($provenanceAfter)->toBe($provenanceBefore + 1);
});

it('continues at the next pending case on the page of the manual verdict', function (): void {
    $candidates = MatchCandidate::factory()->count(28)->create([
        'status' => CandidateStatus::Pending,
        'suggested_umamusume_id' => null,
        'created_by_fetch_at' => now()->subDay(),
    ]);
    $selected = $candidates[2];
    $next = $candidates[1];
    $queueUrl = route('review.index', ['page' => 2]);
    $detailUrl = route('review.detail', ['candidate' => $selected->id, 'page' => 2], absolute: false);

    test()->get($queueUrl)
        ->assertInertia(fn (Assert $page) => $page
            ->where('candidates.total', 28)
            ->has('candidates.data', 3)
            ->where('candidates.data.0.review_url', $detailUrl)
            ->where('nextReviewUrl', null));

    test()->get($detailUrl)
        ->assertInertia(fn (Assert $page) => $page->where('queueUrl', '/review?page=2'));

    test()->post(route('review.resolve', ['candidate' => $selected->id, 'page' => 2]), [
        'status' => CandidateStatus::Rejected->value,
    ])->assertRedirect($queueUrl)->assertSessionHas('status', 'Candidate resolved.');

    test()->get($queueUrl)
        ->assertInertia(fn (Assert $page) => $page
            ->where('candidates.total', 27)
            ->has('candidates.data', 2)
            ->where('resolvedCount', 1)
            ->where('resolvedToday', 1)
            ->where('nextReviewUrl', route('review.detail', ['candidate' => $next->id, 'page' => 2], absolute: false)));

    expect($next->fresh()->status)->toBe(CandidateStatus::Pending);
});
