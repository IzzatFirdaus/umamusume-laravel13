<?php

declare(strict_types=1);

use App\Enums\ReleaseStatus;
use App\Models\DataSource;
use App\Models\Umamusume;

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
        ->assertSee('スペシャルウィーク')
        ->assertSee('https://example.test/special-week');
});

it('labels japan-only entries as not yet released on global', function (): void {
    Umamusume::factory()->japanOnly()->create([
        'name' => 'Unseen One',
        'slug' => 'unseen-one',
        'release_status' => ReleaseStatus::JapanOnly,
    ]);

    test()->get('/umamusume/unseen-one')
        ->assertOk()
        ->assertSee('Not yet released on Global');
});

it('renders the empty state when nothing matches the search', function (): void {
    test()->get('/umamusume?search=nothinghere')
        ->assertOk()
        ->assertSee('No Umamusume match');
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
