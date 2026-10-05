<?php

declare(strict_types=1);

use App\Models\CharacterCard;
use App\Models\DataSource;
use App\Models\SupportCard;
use App\Services\DataPipeline\ArtworkMirror;
use App\Services\DataPipeline\SourceFetcher;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/*
 * `ADR-0021` authorizes a local artwork mirror and `DESIGN.md` §4.7 says what an absent file
 * looks like. This is the build half of that decision: the four properties the ADR's
 * Verification section names as the things that would falsify it, each proven without touching
 * the network (`Http::fake`, per AGENTS.md §9 and the standing no-network test rule).
 *
 * The path is derived from the id a catalog row already carries. A miss is answered by the host
 * with 27,150 bytes of HTML at HTTP 404, so "a body came back" is not evidence of anything and
 * the status is the only test that matters. Nothing is stored in the database, so nothing here
 * asserts a column. And no id ever arrives from a request: the path is config plus an integer, and
 * the shapes that would break that are refused at the seam that would have to enforce it.
 */

const ARTWORK_BASE = 'https://media.gametora.com/umamusume/';

function portraitPath(int $cardId): string
{
    return "characters/portrait/trainee/256/{$cardId}.png";
}

function supportPath(int $supportId): string
{
    return "supports/full/small/{$supportId}.png";
}

beforeEach(function (): void {
    Storage::fake('local');

    // The declared delay is politeness toward a third-party host, and a test does not need it.
    config(['uma.sources.gametora-artwork.delay_ms' => 0]);
});

it('mirrors every kind config declares, at the path its own id derives', function (): void {
    CharacterCard::factory()->create(['card_id' => 100101]);
    SupportCard::factory()->create(['support_id' => 10001]);

    Http::fake([ARTWORK_BASE.'*' => Http::response('THE-BYTES', 200, ['Content-Type' => 'image/png'])]);

    // No --kind, so the default walk; both shapes come from config, not from this test.
    $this->artisan('uma:fetch-art')->assertExitCode(0);

    expect(Storage::disk('local')->get('artwork/'.portraitPath(100101)))->toBe('THE-BYTES')
        ->and(Storage::disk('local')->get('artwork/'.supportPath(10001)))->toBe('THE-BYTES');

    Http::assertSentCount(2);
    Http::assertSent(fn (Request $request): bool => $request->url() === ARTWORK_BASE.portraitPath(100101));
    Http::assertSent(fn (Request $request): bool => $request->url() === ARTWORK_BASE.supportPath(10001));
});

it('records url, sha256 and fetched-at for what it stored, and stores nothing in the database', function (): void {
    CharacterCard::factory()->create(['card_id' => 100101]);

    Http::fake([ARTWORK_BASE.'*' => Http::response('THE-BYTES', 200)]);

    $this->artisan('uma:fetch-art', ['--kind' => 'card_portrait'])->assertExitCode(0);

    $entry = app(ArtworkMirror::class)->manifest()[portraitPath(100101)];

    expect(array_keys($entry))->toBe(['url', 'sha256', 'fetched_at'])
        ->and($entry['url'])->toBe(ARTWORK_BASE.portraitPath(100101))
        ->and($entry['sha256'])->toBe(hash('sha256', 'THE-BYTES'));

    // The manifest entry is the audit trail. A `data_sources` row is for an engine-owned fact about
    // the catalog and a file is not a fact (ADR-0021 Decision 3), so this pass writes no table at all.
    expect(DataSource::query()->count())->toBe(0);
});

it('treats a 404 that carries a body as no file, which is exactly how this host answers a miss', function (): void {
    CharacterCard::factory()->create(['card_id' => 100102]);

    // The body is the thing a naive check would accept: this host answers a miss with a full HTML
    // document, and ADR-0021 records its size as measured on 2026-10-05.
    Http::fake([ARTWORK_BASE.'*' => Http::response(str_repeat('<html>miss</html>', 2000), 404)]);

    $this->artisan('uma:fetch-art', ['--kind' => 'card_portrait'])->assertExitCode(0);

    expect(Storage::disk('local')->exists('artwork/'.portraitPath(100102)))->toBeFalse()
        ->and(app(ArtworkMirror::class)->manifest())->toBe([]);
});

it('asks once per file, and asks again only when told to', function (): void {
    CharacterCard::factory()->create(['card_id' => 100103]);

    Http::fake([ARTWORK_BASE.'*' => Http::response('THE-BYTES', 200)]);

    $this->artisan('uma:fetch-art', ['--kind' => 'card_portrait'])->assertExitCode(0);
    $this->artisan('uma:fetch-art', ['--kind' => 'card_portrait'])->assertExitCode(0);

    Http::assertSentCount(1);

    $this->artisan('uma:fetch-art', ['--kind' => 'card_portrait', '--refetch' => true])->assertExitCode(0);

    Http::assertSentCount(2);
});

it('sends nothing on --dry-run, so a first look at the cost costs no request', function (): void {
    CharacterCard::factory()->create(['card_id' => 100104]);

    Http::fake([ARTWORK_BASE.'*' => Http::response('THE-BYTES', 200)]);

    $this->artisan('uma:fetch-art', ['--dry-run' => true])->assertExitCode(0);

    Http::assertNothingSent();

    expect(Storage::disk('local')->exists('artwork/'.portraitPath(100104)))->toBeFalse()
        ->and(Storage::disk('local')->exists('artwork/manifest.json'))->toBeFalse();
});

it('refuses an unknown kind instead of guessing one', function (): void {
    Http::fake([ARTWORK_BASE.'*' => Http::response('THE-BYTES', 200)]);

    $this->artisan('uma:fetch-art', ['--kind' => 'profile_pose'])
        ->expectsOutputToContain('Declared: card_portrait, support_thumb')
        ->assertExitCode(1);

    Http::assertNothingSent();
});

it('keeps uma:fetch off the asset host, which has no document to parse', function (): void {
    Http::fake();

    $this->artisan('uma:fetch', ['source' => 'gametora-artwork'])
        ->expectsOutputToContain('uma:fetch-art')
        ->assertExitCode(1);

    Http::assertNothingSent();
});

it('builds no request from a path that is not a plain relative one', function (): void {
    Http::fake();

    $fetcher = app(SourceFetcher::class);

    // The current caller passes config plus an integer, so these are the two shapes a later caller
    // could reach by accident: an absolute URL, and a step out of the base directory.
    expect($fetcher->fetchAsset('gametora-artwork', 'https://evil.test/x.png'))->toBeNull()
        ->and($fetcher->fetchAsset('gametora-artwork', '../../private/snapshots/index.json'))->toBeNull()
        ->and($fetcher->fetchAsset('gametora-artwork', ''))->toBeNull()
        ->and($fetcher->fetchAsset('gametora-nowhere', 'characters/portrait/trainee/256/100101.png'))->toBeNull();

    Http::assertNothingSent();
});

it('reports a mirrored file as existing', function (): void {
    Storage::disk('local')->put(
        'artwork/characters/portrait/trainee/256/100101.png',
        'BYTES'
    );

    expect(app(ArtworkMirror::class)
        ->exists('card_portrait', 100101))->toBeTrue();
});

it('reports an absent file as not existing', function (): void {
    expect(app(ArtworkMirror::class)
        ->exists('card_portrait', 100102))->toBeFalse();
});

it('returns the named-route url when mirrored', function (): void {
    Storage::disk('local')->put(
        'artwork/characters/portrait/trainee/256/100101.png',
        'BYTES'
    );

    expect(app(ArtworkMirror::class)
        ->url('card_portrait', 100101))->toBe(route('artwork.show', ['kind' => 'card_portrait', 'id' => 100101]));
});

it('returns null when the file is not mirrored', function (): void {
    expect(app(ArtworkMirror::class)
        ->url('card_portrait', 100103))->toBeNull();
});
