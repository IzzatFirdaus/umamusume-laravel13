<?php

declare(strict_types=1);

use App\Services\DataPipeline\SourceFetcher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/*
 * F-5: snapshot idempotency is keyed on the day as well as the content.
 * SourceFetcher.php:57 composes `snapshots/{source}/{YYYY-MM-DD}/{sha256}.html`, so a byte
 * identical body fetched tomorrow lands on a path that does not exist yet, misses the
 * short-circuit at :59, and is stored a second time and re-parsed.
 *
 * The hash is already content-derived, so the date segment carries no information the hash
 * does not. ARCHITECTURE.md:268 describes the snapshot as the unit of idempotency.
 *
 * UmaReparse.php:42-53 reads the directory with allFiles() and sorts by lastModified, so it
 * does not parse the date segment and needs no change. FetchPipelineTest.php:140 composes a
 * date path by hand; that fixture is updated to the new shape in the same commit.
 */

const SNAPSHOT_FETCH_URL = 'https://source.test/snapshot-doc';

function declareSnapshotSource(): void
{
    config([
        'uma.sources.snapshot-test' => [
            'url' => SNAPSHOT_FETCH_URL,
            'delay_ms' => 0,
            'timeout_s' => 5,
            'timezone' => 'Asia/Tokyo',
        ],
    ]);
}

function fetchSnapshotSource(): ?array
{
    return app(SourceFetcher::class)->fetch('snapshot-test', config('uma.sources.snapshot-test'));
}

beforeEach(function (): void {
    Storage::fake('local');
    declareSnapshotSource();
});

it('treats a byte identical body fetched on a later day as unchanged', function (): void {
    $body = json_encode([['name' => 'Special Week']], JSON_THROW_ON_ERROR);
    $hash = hash('sha256', $body);

    Http::fake([
        'source.test/snapshot-doc' => Http::response($body, 200),
    ]);

    Carbon::setTestNow('2026-09-27 10:00:00');

    $first = fetchSnapshotSource();
    expect($first)->not->toBeNull()
        ->and($first['unchanged'])->toBeFalse()
        ->and($first['hash'])->toBe($hash);

    // Same bytes, next day. F-5: the date inside the path makes the existence test miss.
    Carbon::setTestNow('2026-09-28 10:00:00');

    $second = fetchSnapshotSource();

    expect($second)->not->toBeNull()
        ->and($second['unchanged'])->toBeTrue()
        ->and($second['hash'])->toBe($hash)
        ->and($second['snapshot_path'])->toBe($first['snapshot_path'])
        ->and(Storage::disk('local')->allFiles('snapshots/snapshot-test'))->toHaveCount(1);
});

it('still stores a second file when the body actually changes, the canary above', function (): void {
    Http::fake([
        SNAPSHOT_FETCH_URL => Http::sequence()
            ->push(json_encode([['name' => 'Special Week']], JSON_THROW_ON_ERROR))
            ->push(json_encode([['name' => 'Silence Suzuka']], JSON_THROW_ON_ERROR)),
    ]);

    Carbon::setTestNow('2026-09-27 10:00:00');

    $first = fetchSnapshotSource();
    $second = fetchSnapshotSource();

    // A fetcher that always reported unchanged would pass the test above by accident and fail
    // here, which is what makes the first assertion worth having.
    expect($first)->not->toBeNull()
        ->and($second)->not->toBeNull()
        ->and($second['unchanged'])->toBeFalse()
        ->and($second['hash'])->not->toBe($first['hash'])
        ->and(Storage::disk('local')->allFiles('snapshots/snapshot-test'))->toHaveCount(2);
});
