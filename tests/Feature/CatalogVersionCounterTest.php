<?php

declare(strict_types=1);

use App\Services\DataPipeline\PipelineRunner;
use Database\Seeders\UmamusumeRosterSeeder;
use Illuminate\Support\Facades\Cache;

/**
 * F-10 / N-4: `catalog:version` was a counter with an expiry. The writer did `Cache::add(key, 0, 3600)`
 * then `Cache::increment(key)`, and the reader did `Cache::remember(key, 3600, fn () => 0)`. Page cache
 * keys embed the version number, so an hour after the last promotion the key is gone, `add` writes a new
 * zero, and version 1 of hour two reuses the key namespace of version 1 of hour one: the app serves a
 * cached page and calls it fresh.
 *
 * A counter is not a cache entry. Neither call should carry a TTL.
 */
function runPipeline(string $sourceKey): array
{
    $config = config('uma.sources.'.$sourceKey);
    $relative = 'seeders/data/'.$config['seed_file'];

    return app(PipelineRunner::class)->run(
        $sourceKey,
        $config,
        (string) file_get_contents(database_path($relative)),
        $relative,
    );
}

it('keeps the version it reached instead of starting the numbering over an hour later', function (): void {
    Cache::flush();

    // The card branch resolves each record against the roster, so on an empty `umamusume` table every card
    // is unmatched, nothing is created, and the counter is never touched. The seeder is the precondition the
    // promotion actually needs, not scaffolding around it.
    (new UmamusumeRosterSeeder)->run();

    // Two card promotions, because the writer's shape is `add(key, 0)` then `increment` on the failure:
    // the first bumping run installs the counter at zero and the second is what moves it. `updated` is
    // non-zero on a repeat of the same body, so the gate is met both times.
    runPipeline('gametora-character-cards');
    runPipeline('gametora-character-cards');

    $version = (int) Cache::get('catalog:version');

    expect($version)->toBe(1);

    test()->travel(3601)->seconds();

    // The counter outliving its own TTL is the whole finding: page cache keys embed this number, so a key
    // that quietly returns to zero lets hour two's version 1 read hour one's cached page ids.
    expect((int) Cache::get('catalog:version'))->toBe($version);

    test()->travelBack();
});

it('lets the reader see the counter without becoming the thing that creates it', function (): void {
    Cache::flush();

    expect(Cache::has('catalog:version'))->toBeFalse();

    test()->get(route('catalog.index'))->assertOk();

    // The page read the counter through `Cache::get`, so it is still absent: a read must not install an
    // expiring zero that later stands in for the promoted version.
    expect(Cache::has('catalog:version'))->toBeFalse();
});
