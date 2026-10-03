<?php

declare(strict_types=1);

use App\Services\DataPipeline\SourceFetcher;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * N-3: the fetch layer answered "no document" with silence. `SourceFetcher::send()` catches
 * `RequestException` and returns null, `->retry(..., throw: false)` means exhausted retries arrive there
 * without anything written, and a non-2xx returns null on the next line. A Trainer running `uma:fetch`
 * saw the run end and nothing in `storage/logs` named the source that failed, so the difference between
 * a withdrawn document, a timeout and a blocked host was invisible.
 *
 * Measured while writing this: on this framework version `ConnectionException` is a *sibling* of
 * `RequestException` under `HttpClientException`, so a DNS or timeout fault was never caught at all and
 * escaped `fetch()` as an unhandled exception. The catch is widened to the parent, and the first case
 * below is that regression test as much as the log line is.
 *
 * The config allowlist is what makes a fetch fail without a stack trace, so the log line is the only
 * report there is.
 */
function fetchOnce(string $sourceKey = 'gametora-characters'): ?array
{
    $config = config('uma.sources.'.$sourceKey);

    return app(SourceFetcher::class)->fetch($sourceKey, $config);
}

it('names the source and the reason when the request never completes', function (): void {
    $url = (string) config('uma.sources.gametora-characters.url');

    Http::fake(fn (Request $request) => throw new ConnectionException('connection refused'));

    Log::shouldReceive('warning')
        ->once()
        ->with(Mockery::on(fn (string $message): bool => str_contains($message, $url)
            && str_contains($message, 'connection refused')));

    expect(fetchOnce())->toBeNull();
});

it('names the status when the document answers a failure code', function (): void {
    Http::fake(['*' => Http::response('gone', 404)]);

    Log::shouldReceive('warning')
        ->once()
        ->with(Mockery::on(fn (string $message): bool => str_contains($message, '404')));

    expect(fetchOnce())->toBeNull();
});

it('says nothing when the document arrives, so the two lines above are not every path logging', function (): void {
    Storage::fake('local');
    Http::fake(['*' => Http::response('<html>roster</html>', 200)]);

    Log::shouldReceive('warning')->never();

    expect(fetchOnce())->not->toBeNull();
});
