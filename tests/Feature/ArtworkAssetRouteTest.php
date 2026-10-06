<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('local');
    config(['uma.sources.gametora-artwork.delay_ms' => 0]);
});

it('streams a mirrored portrait', function (): void {
    Storage::disk('local')->put(
        'artwork/characters/portrait/trainee/256/100101.png',
        'PNG-BYTES'
    );

    $response = $this->get('/artwork/card_portrait/100101');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'image/png');
    expect($response->getContent())->toBe('PNG-BYTES');
});

it('returns 404 when the file is not mirrored', function (): void {
    // File not seeded.
    $this->get('/artwork/card_portrait/100102')->assertNotFound();
});

it('rejects an unknown kind with 404', function (): void {
    Storage::disk('local')->put(
        'artwork/characters/portrait/trainee/256/100101.png',
        'PNG-BYTES'
    );

    $this->get('/artwork/profile_pose/100101')->assertNotFound();
});

it('answers a mirrored frame with a reusable lifetime and a validator', function (): void {
    // The missing pair this route shipped without: no `Cache-Control` meant the browser had nothing it
    // was allowed to keep, so every screen change that re-created an `<img>` asked the single-process
    // dev server again. A response with a lifetime but no validator would be the other half of the bug:
    // a re-mirrored file then has no cheap way to prove it changed.
    Storage::disk('local')->put(
        'artwork/characters/portrait/trainee/256/100101.png',
        'PNG-BYTES'
    );

    $response = $this->get('/artwork/card_portrait/100101');

    $response->assertOk();

    // Asserted as membership, not as the literal string: the framework normalises `Cache-Control` and
    // answers `max-age=300, public` regardless of the order it was set in, so an exact-string assertion
    // here would test a serializer rather than the freshness contract.
    $cacheControl = (string) $response->headers->get('Cache-Control');

    expect($cacheControl)->toContain('max-age=300')
        ->and($cacheControl)->toContain('public')
        ->and($response->headers->get('ETag'))->toMatch('/^W\/"[0-9a-f]+-[0-9a-f]+"$/')
        ->and($response->headers->get('Last-Modified'))->toBeString();
});

it('sends no body when the client already holds the current validator', function (): void {
    Storage::disk('local')->put(
        'artwork/characters/portrait/trainee/256/100101.png',
        'PNG-BYTES'
    );

    $etag = $this->get('/artwork/card_portrait/100101')->headers->get('ETag');

    // A conditional request that matches is a 304 with the body dropped: the frame is confirmed
    // unchanged, and the bytes cross the loopback once rather than once per visit.
    $this->withHeaders(['If-None-Match' => $etag])
        ->get('/artwork/card_portrait/100101')
        ->assertStatus(304);

    // A validator from a different file must not answer for this one.
    $this->withHeaders(['If-None-Match' => 'W/"deadbeef-1"'])
        ->get('/artwork/card_portrait/100101')
        ->assertOk()
        ->assertHeader('ETag', $etag);
});

it('changes the validator when the mirror replaces the file, without changing the url', function (): void {
    // The staleness ceiling is a max-age, not the validator, so the assertion that matters is that a
    // re-mirrored file is distinguishable: same route, same id, different tag.
    Storage::disk('local')->put(
        'artwork/characters/portrait/trainee/256/100101.png',
        'PNG-BYTES'
    );

    $before = $this->get('/artwork/card_portrait/100101')->headers->get('ETag');

    Storage::disk('local')->put(
        'artwork/characters/portrait/trainee/256/100101.png',
        'PNG-BYTES-REPLACED-BY-A-REFETCH'
    );

    expect($this->get('/artwork/card_portrait/100101')->headers->get('ETag'))
        ->not->toBe($before);
});

it('does not offer a lifetime for a frame the mirror has not fetched', function (): void {
    // Absence is a normal state here (`ADR-0021` Decision 5), and the mirror fills in over time, so a
    // cached "no file" would keep a frame missing after the file arrives. The framework's own
    // `Cache-Control` on a 404 is fine; a reusable max-age of ours is not.
    $response = $this->get('/artwork/card_portrait/999999')->assertNotFound();

    expect((string) $response->headers->get('Cache-Control'))->not->toContain('max-age=300');
});
