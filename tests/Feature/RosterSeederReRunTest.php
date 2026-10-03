<?php

declare(strict_types=1);

use App\Enums\CandidateStatus;
use App\Models\MatchCandidate;
use App\Models\Umamusume;
use Database\Seeders\UmamusumeRosterSeeder;

/**
 * KI-56: `db:seed` was not re-runnable. The roster seeder filed every out-of-scope trainee with
 * `MatchCandidate::create()`, and `2026_10_01_124051` put a unique index on exactly the triple it
 * writes `(source_key, IFNULL(external_ref, ''), proposed_match_key)`. A first run against an empty
 * database inserted; a second run hit the index, aborted the seeder, and left the three seeders that
 * follow it in `DatabaseSeeder` unrun while the command exited non-zero.
 */
it('queues the same candidates on a second run instead of colliding with the unique index', function (): void {
    (new UmamusumeRosterSeeder)->run();

    $queuedAfterFirstRun = MatchCandidate::query()->count();
    $rosterAfterFirstRun = Umamusume::query()->count();

    expect($queuedAfterFirstRun)->toBeGreaterThan(0);

    (new UmamusumeRosterSeeder)->run();

    expect(MatchCandidate::query()->count())->toBe($queuedAfterFirstRun)
        ->and(Umamusume::query()->count())->toBe($rosterAfterFirstRun);
});

it('refreshes a queued candidate instead of leaving two rows for one source record', function (): void {
    (new UmamusumeRosterSeeder)->run();

    $candidate = MatchCandidate::query()
        ->where('source_key', UmamusumeRosterSeeder::SOURCE_KEY)
        ->whereNotNull('external_ref')
        ->firstOrFail();

    (new UmamusumeRosterSeeder)->run();

    expect(MatchCandidate::query()
        ->where('source_key', UmamusumeRosterSeeder::SOURCE_KEY)
        ->where('external_ref', $candidate->external_ref)
        ->count())->toBe(1);
});

it('keeps a review decision the Trainer already made on a re-run', function (): void {
    (new UmamusumeRosterSeeder)->run();

    $candidate = MatchCandidate::query()->firstOrFail();
    $candidate->update(['status' => CandidateStatus::Rejected]);

    (new UmamusumeRosterSeeder)->run();

    expect($candidate->fresh()->status)->toBe(CandidateStatus::Rejected);
});
