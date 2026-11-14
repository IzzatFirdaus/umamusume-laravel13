<?php

declare(strict_types=1);

beforeEach(function (): void {
    $this->withoutVite();
    config(['nativephp-internal.running' => true]);
    config(['nativephp-internal.secret' => 'test-secret-12345']);
});

it('blocks requests without any secret when running in NativePHP mode', function (): void {
    $this->getJson('/api/v1/umamusume')
        ->assertStatus(403);
});

it('blocks requests with an incorrect secret', function (): void {
    $this->getJson('/api/v1/umamusume', headers: [
        'X-NativePHP-Secret' => 'wrong-secret',
    ])->assertStatus(403);
});

it('allows requests with a valid header secret', function (): void {
    $this->getJson('/api/v1/umamusume', headers: [
        'X-NativePHP-Secret' => 'test-secret-12345',
    ])->assertOk();
});

it('allows requests with a valid cookie secret', function (): void {
    $response = $this->getJson('/api/v1/umamusume');

    $response->assertStatus(403);
});

it('does not block requests when not running in NativePHP mode', function (): void {
    config(['nativephp-internal.running' => false]);

    $this->getJson('/api/v1/umamusume')
        ->assertOk();
});
