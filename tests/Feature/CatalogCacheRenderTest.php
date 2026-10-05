<?php

declare(strict_types=1);

use App\Models\Umamusume;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

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

    // The cold request populated the cache; this one deserialises it. The page group key is the evidence,
    // not `catalog:version`: that counter belongs to the promotion and a page read no longer writes it
    // (F-10 / N-4), so asserting it here would pin the defect this file used to rely on.
    expect(DB::table('cache')->where('key', 'like', '%catalog:list:v%')->count())->toBeGreaterThan(0);

    // The read-back renders the cached rows through Inertia (ADR-0020 §1): the tree arrives in the props.
    test()->get('/umamusume')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('umamusumes.data', 3));

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

    // The detail page is not cached (KI-2), so both visits are plain renders under a
    // persistent store: the name now travels in the Inertia payload, not a cached model graph.
    test()->get('/umamusume/'.$umamusume->slug)->assertOk();

    $html = test()->get('/umamusume/'.$umamusume->slug)->assertOk()->getContent();

    expect($html)->toContain($umamusume->name)
        ->and($html)->not->toContain('__PHP_Incomplete_Class');

    Cache::flush();
});
