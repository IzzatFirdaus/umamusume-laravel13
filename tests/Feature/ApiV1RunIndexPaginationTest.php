<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\Umamusume;

/*
 * C2: pagination on GET /api/v1/training-runs, which had no test.
 *
 * `ApiV1Test.php` covers the umamusume index's pagination shape and the run *detail*
 * endpoint, and nothing at all for the run *index* - a route that returns a paginated
 * collection with a hand-rolled envelope and its own clamp. So the run list's documented
 * `{ data, pagination }` contract is the one contract in this API that no test states.
 *
 * The clamp is the part most likely to rot: `min(100, max(1, (int) $query))` means a
 * consumer asking for 500 gets 100 and a consumer asking for 0 gets 1, and neither is an
 * error. Both boundaries are asserted, because a change that made either one a 422 would
 * break every existing caller silently.
 *
 * `pageSize` is the query parameter name, not Laravel's `per_page`: this controller reads
 * the request directly, so the documented name is the one asserted.
 */
it('returns the run list in the documented envelope', function (): void {
    TrainingRun::factory()->count(2)->create();

    test()->getJson('/api/v1/training-runs')
        ->assertOk()
        ->assertJsonStructure([
            // `turns` and `skills` are deliberately absent on this endpoint - the index
            // eager-loads the trainee only. The test below states that, so the structure
            // here must not ask for them.
            'data' => [['id', 'umamusume', 'scenario', 'status', 'notes']],
            'pagination' => ['page', 'pageSize', 'totalItems', 'totalPages'],
        ])
        ->assertJsonPath('pagination.totalItems', 2)
        ->assertJsonPath('pagination.page', 1)
        ->assertJsonPath('pagination.pageSize', 25)
        ->assertJsonPath('pagination.totalPages', 1)
        // The index resolves the collection, so `data` is an array and not a resource
        // envelope wrapping another one.
        ->assertJsonCount(2, 'data');
});

it('names the trainee on each row rather than an id alone', function (): void {
    $umamusume = Umamusume::factory()->create(['name' => 'Special Week']);
    TrainingRun::factory()->create(['umamusume_id' => $umamusume->id, 'scenario' => 'ura_finale']);

    test()->getJson('/api/v1/training-runs')
        ->assertOk()
        ->assertJsonPath('data.0.umamusume.name', 'Special Week')
        ->assertJsonPath('data.0.scenario', 'ura_finale');
});

it('leaves turns and skills out of the index, which only resolves what it eager loads', function (): void {
    $run = TrainingRun::factory()->create();
    $run->turnEntries()->create([
        'turn' => 1, 'speed' => 100, 'stamina' => 100, 'power' => 100, 'guts' => 100, 'wit' => 100,
    ]);

    $response = test()->getJson('/api/v1/training-runs')->assertOk();

    // The index eager-loads the trainee only, so the nested relations are absent rather
    // than empty. An empty array here would claim the run has no turns, which is false.
    expect(array_key_exists('turns', $response->json('data.0')))->toBeFalse()
        ->and(array_key_exists('skills', $response->json('data.0')))->toBeFalse();
});

it('honours a page size the caller asks for', function (): void {
    TrainingRun::factory()->count(30)->create();

    test()->getJson('/api/v1/training-runs?pageSize=10')
        ->assertOk()
        ->assertJsonPath('pagination.pageSize', 10)
        ->assertJsonPath('pagination.totalItems', 30)
        ->assertJsonPath('pagination.totalPages', 3)
        ->assertJsonCount(10, 'data');
});

it('clamps a page size above the ceiling to a hundred', function (): void {
    TrainingRun::factory()->count(2)->create();

    test()->getJson('/api/v1/training-runs?pageSize=500')
        ->assertOk()
        ->assertJsonPath('pagination.pageSize', 100);
});

it('clamps a page size below the floor to one', function (): void {
    TrainingRun::factory()->count(3)->create();

    test()->getJson('/api/v1/training-runs?pageSize=0')
        ->assertOk()
        ->assertJsonPath('pagination.pageSize', 1)
        ->assertJsonCount(1, 'data');
});

it('walks to the second page and reports the page it is on', function (): void {
    TrainingRun::factory()->count(30)->create();

    $first = test()->getJson('/api/v1/training-runs?pageSize=10')->assertOk();
    $second = test()->getJson('/api/v1/training-runs?pageSize=10&page=2')->assertOk();

    expect($first->json('pagination.page'))->toBe(1)
        ->and($second->json('pagination.page'))->toBe(2)
        // Disjoint pages: the boundary is a real split, not a repeated first page.
        ->and(array_intersect(
            array_column($first->json('data'), 'id'),
            array_column($second->json('data'), 'id'),
        ))->toBeEmpty();
});

it('returns the newest run first', function (): void {
    $older = TrainingRun::factory()->create(['created_at' => now()->subWeek()]);
    $newer = TrainingRun::factory()->create(['created_at' => now()]);

    $ids = array_column(test()->getJson('/api/v1/training-runs')->json('data'), 'id');

    expect(array_search($newer->id, $ids, true))->toBeLessThan(array_search($older->id, $ids, true));
});
