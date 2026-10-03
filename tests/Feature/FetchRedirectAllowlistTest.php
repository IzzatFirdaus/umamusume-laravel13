<?php

declare(strict_types=1);

use App\Services\DataPipeline\SourceFetcher;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * F-9: the fetch posture was "hosts come from config, never from user input or fetched content", and
 * `maxRedirects(2)` handed the second half of that sentence to Guzzle. A document on an allowlisted origin
 * that answers `302` to a host nobody declared got fetched, snapshotted and parsed under the declared
 * source's key, and the provenance row named the origin it was *asked* for, not the one that answered.
 *
 * Redirects are now followed inside `SourceFetcher::send()`, and each hop is checked against the hosts
 * `config('uma.sources')` names before anything is requested. `Http::fake` records each hop as its own
 * request, which is what makes the refusal observable at all.
 */
function probeFetch(): ?array
{
    return app(SourceFetcher::class)->fetch('redirect-probe', [
        'url' => 'https://gametora.com/data/umamusume/roster.json',
        'delay_ms' => 0,
    ]);
}

beforeEach(function (): void {
    Storage::fake('local');
    Log::shouldReceive('warning')->byDefault();
});

it('refuses a redirect that leaves the declared hosts, and never asks the new host', function (): void {
    Http::fake([
        'https://gametora.com/*' => Http::response('', 302, [
            'Location' => 'https://somewhere-else.test/stolen.json',
        ]),
        'https://somewhere-else.test/*' => Http::response('<h1>take the config</h1>', 200),
    ]);

    expect(probeFetch())->toBeNull();

    Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), 'somewhere-else.test'));
});

it('follows a relative redirect that stays on the declared host', function (): void {
    Http::fakeSequence('https://gametora.com/*')
        ->push('', 301, ['Location' => '/data/umamusume/moved.json'])
        ->push('<html>the roster</html>', 200);

    $result = probeFetch();

    expect($result)->not->toBeNull()
        ->and($result['body'])->toBe('<html>the roster</html>')
        ->and($result['url'])->toBe('https://gametora.com/data/umamusume/moved.json');
});

it('stops at the same two hops the old maxRedirects ceiling allowed', function (): void {
    Http::fakeSequence('https://gametora.com/*')
        ->push('', 302, ['Location' => '/a'])
        ->push('', 302, ['Location' => '/b'])
        ->push('', 302, ['Location' => '/c'])
        ->push('never read', 200);

    expect(probeFetch())->toBeNull();

    Http::assertSentCount(3);
});

it('says nothing about redirects when the document simply answers, so the three above are not every path redirecting', function (): void {
    Http::fake([
        'https://gametora.com/*' => Http::response('<html>plain</html>', 200),
    ]);

    $result = probeFetch();

    expect($result)->not->toBeNull()->and($result['body'])->toBe('<html>plain</html>');

    Http::assertSentCount(1);
});
