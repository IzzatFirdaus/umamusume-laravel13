<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

beforeEach(function (): void {
    Route::middleware('web')->get('/native-secret-gate-probe', static fn () => response('ok'));

    config([
        'nativephp-internal.secret' => 'test-native-secret',
    ]);
});

it('allows regular browser requests outside native mode', function (): void {
    config(['nativephp-internal.running' => false]);

    $this->get('/native-secret-gate-probe')->assertOk();
});

it('rejects native mode requests that omit the native secret', function (): void {
    config(['nativephp-internal.running' => true]);

    $this->get('/native-secret-gate-probe')->assertForbidden();
});

it('rejects native mode requests with an invalid native secret', function (): void {
    config(['nativephp-internal.running' => true]);

    $this->withHeaders([
        'X-NativePHP-Secret' => 'not-the-secret',
    ])->get('/native-secret-gate-probe')->assertForbidden();
});

it('allows native mode requests with the native header secret', function (): void {
    config(['nativephp-internal.running' => true]);

    $this->withHeaders([
        'X-NativePHP-Secret' => 'test-native-secret',
    ])->get('/native-secret-gate-probe')->assertOk();
});

it('allows native mode requests with the native cookie secret', function (): void {
    config(['nativephp-internal.running' => true]);

    $this->withCookie('_php_native', 'test-native-secret')
        ->get('/native-secret-gate-probe')
        ->assertOk();
});
