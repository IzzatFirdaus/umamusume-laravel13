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
