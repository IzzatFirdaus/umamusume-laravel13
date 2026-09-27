<?php

declare(strict_types=1);

use App\Models\Umamusume;
use Illuminate\Support\Facades\Cache;

/*
 * KI-2: `GET /umamusume` returned 500 with "Attempt to read property "slug" on
 * string" from catalog/index.blade.php, while the suite stayed green.
 *
 * The suite was not testing the page the Trainer sees. phpunit.xml pins
 * CACHE_STORE=array (line 25) and the app runs CACHE_STORE=database (.env), and
 * the catalog caches its paginator rows — so the array store hands back the live
 * models it was given, and only the database store round-trips them through
 * serialisation. A list that survives one and not the other is invisible to
 * every test that never changes the store.
 *
 * Both visits are asserted because the first one writes the cache and the second
 * one reads it back; the failure was in the read.
 */

it('renders the catalog through the database cache store, not only the array one', function (): void {
    Umamusume::factory()->count(3)->create();

    config()->set('cache.default', 'database');
    Cache::flush();

    expect(config('cache.default'))->toBe('database')
        ->and(test()->get('/umamusume')->getStatusCode())->toBe(200);

    // The cold request populated the cache; this one deserialises it.
    expect(Cache::has('catalog:version'))->toBeTrue()
        ->and(test()->get('/umamusume')->assertOk()->getContent())
        ->toContain('Umamusume');

    Cache::flush();
});

it('renders a filtered catalog page through the same store', function (): void {
    Umamusume::factory()->count(2)->create(['release_status' => 'GlobalReleased']);

    config()->set('cache.default', 'database');
    Cache::flush();

    test()->get('/umamusume?status=GlobalReleased')->assertOk();
    test()->get('/umamusume?status=GlobalReleased')->assertOk();

    Cache::flush();
});

it('renders one detail page through the same store, twice', function (): void {
    $umamusume = Umamusume::factory()->create(['slug' => 'special-week']);

    config()->set('cache.default', 'database');
    Cache::flush();

    // The detail page caches the model itself, so the second visit is the one
    // that reads an object back out of a persistent store.
    test()->get('/umamusume/'.$umamusume->slug)->assertOk();

    $html = test()->get('/umamusume/'.$umamusume->slug)->assertOk()->getContent();

    expect($html)->toContain($umamusume->name)
        ->and($html)->not->toContain('__PHP_Incomplete_Class');

    Cache::flush();
});
