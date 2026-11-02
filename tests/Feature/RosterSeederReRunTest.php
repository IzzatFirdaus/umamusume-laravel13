<?php

declare(strict_types=1);

use App\Models\MatchCandidate;
use App\Models\Umamusume;
use Database\Seeders\UmamusumeRosterSeeder;

/**
 * KI-56: `db:seed` was not re-runnable. The roster seeder now promotes every trainee
 * (including JapanOnly), so no candidates are queued on a fresh run. A re-run must
 * leave the roster count unchanged and must not collide with any unique index.
 */
it('leaves the roster count unchanged on a second run', function (): void {
    (new UmamusumeRosterSeeder)->run();

    $rosterAfterFirstRun = Umamusume::query()->count();

    (new UmamusumeRosterSeeder)->run();

    expect(Umamusume::query()->count())->toBe($rosterAfterFirstRun);
});

it('does not create duplicate candidates on a re-run', function (): void {
    (new UmamusumeRosterSeeder)->run();

    $candidateCountAfterFirstRun = MatchCandidate::query()
        ->where('source_key', UmamusumeRosterSeeder::SOURCE_KEY)
        ->count();

    (new UmamusumeRosterSeeder)->run();

    expect(MatchCandidate::query()
        ->where('source_key', UmamusumeRosterSeeder::SOURCE_KEY)
        ->count())->toBe($candidateCountAfterFirstRun);
});
