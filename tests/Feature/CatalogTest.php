<?php

declare(strict_types=1);

use App\Enums\ReleaseStatus;
use App\Models\DataSource;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Catalog list, filter, search, detail and empty state.
 *
 * The Phase 3A mapping named this file's concern `CatalogFilterEdgeTest` - a file that
 * does not exist, and creating it would have split one surface's coverage across two
 * files that could then drift. It resolves here: the release-status filter, the search
 * normalisation, and the empty state are the three cases below, and they are already the
 * edges - a filter that drops the japan-only rows, a search that has to normalise before
 * it can match, and a search that matches nothing at all.
 */

it('lists only global released umamusume when filtered by release status', function (): void {
    $released = Umamusume::factory()->create(['name' => 'Released One', 'slug' => 'released-one']);
    Umamusume::factory()->japanOnly()->create(['name' => 'Japan Only One', 'slug' => 'japan-only-one']);

    $response = test()->get('/umamusume?status=GlobalReleased');

    $response->assertOk()
        ->assertSee('Released One')
        ->assertDontSee('Japan Only One');

    expect($released->release_status)->toBe(ReleaseStatus::GlobalReleased);
});

it('shows a detail page with Japanese name and provenance', function (): void {
    $umamusume = Umamusume::factory()->create([
        'name' => 'Special Week',
        'slug' => 'special-week',
        'name_ja' => 'スペシャルウィーク',
    ]);

    DataSource::factory()->create([
        'umamusume_id' => $umamusume->id,
        'url' => 'https://example.test/special-week',
        'fetched_at' => now(),
    ]);

    test()->get('/umamusume/special-week')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Catalog/Show')
            ->where('trainee.japanese_name', 'スペシャルウィーク')
            ->where('provenance.0.url', 'https://example.test/special-week'));
});

it('labels japan-only entries as not yet released on global', function (): void {
    Umamusume::factory()->japanOnly()->create([
        'name' => 'Unseen One',
        'slug' => 'unseen-one',
        'release_status' => ReleaseStatus::JapanOnly,
    ]);

    // The notice copy is client-rendered (tests/browser/catalog-detail.spec.ts); the server
    // contract is the flag it binds.
    test()->get('/umamusume/unseen-one')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('trainee.is_japan_only', true));
});

it('sends an empty list when nothing matches the search', function (): void {
    // The empty-state copy is client-rendered now; the server contract is an empty page.
    // The words ("No Umamusume match") are asserted in tests/browser/catalog.spec.ts.
    test()->get('/umamusume?search=nothinghere')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('umamusumes.data', 0));
});

it('finds an umamusume by normalized search text', function (): void {
    Umamusume::factory()->create([
        'name' => 'Special Week',
        'slug' => 'special-week',
        'match_key' => 'specialweek',
    ]);

    test()->get('/umamusume?search=SPECIAL WEEK')
        ->assertOk()
        ->assertSee('Special Week');
});

// KI-29: the catalog controls are sized to the design contract's 44px (h-11). They render
// client-side now, so the rendered heights are asserted in tests/browser/catalog.spec.ts.
