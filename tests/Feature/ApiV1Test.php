<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;

it('returns the catalog list with the documented pagination shape', function (): void {
    Umamusume::factory()->count(2)->create();

    $response = test()->getJson('/api/v1/umamusume');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [['id', 'slug', 'name', 'releaseStatus']],
            'pagination' => ['page', 'pageSize', 'totalItems', 'totalPages'],
        ])
        ->assertJsonPath('pagination.totalItems', 2)
        ->assertJsonPath('pagination.page', 1);
});

it('returns the not found error shape for an unknown slug', function (): void {
    test()->getJson('/api/v1/umamusume/does-not-exist')
        ->assertNotFound()
        ->assertJsonPath('error.code', 'NOT_FOUND');
});

it('includes aliases and sources on the detail endpoint', function (): void {
    $umamusume = Umamusume::factory()->create(['slug' => 'detail-check']);
    $umamusume->aliases()->create(['alias' => 'ディテール', 'language' => 'Japanese']);
    $umamusume->dataSources()->create([
        'url' => 'https://example.test/detail',
        'source_key' => 'test',
        'fetched_at' => now(),
    ]);

    test()->getJson('/api/v1/umamusume/detail-check')
        ->assertOk()
        ->assertJsonPath('data.aliases.0.alias', 'ディテール')
        ->assertJsonPath('data.sources.0.sourceKey', 'test');
});

it('returns a run detail with turns and skills', function (): void {
    $run = TrainingRun::factory()->create();
    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 5, 'speed' => 400]);

    test()->getJson("/api/v1/training-runs/{$run->id}")
        ->assertOk()
        ->assertJsonPath('data.turns.0.turn', 5)
        ->assertJsonPath('data.turns.0.speed', 400);
});

it('returns a validation error shape when the API receives bad JSON expectations', function (): void {
    // Unknown route under /api must render the same error envelope, not HTML.
    test()->getJson('/api/v1/nope')
        ->assertNotFound()
        ->assertJsonStructure(['error' => ['code', 'message']]);
});
