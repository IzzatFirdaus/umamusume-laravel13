<?php

declare(strict_types=1);

use App\Models\Umamusume;

/**
 * C09 — Complete filter persistence in the trainee picker.
 *
 * The trainee picker supports search, sorting (sortBy/direction), and three aptitude facets
 * (surface/distance/style). After selecting a trainee, the redirect back to the picker must
 * preserve all six filter keys so the Trainer returns to the narrowed roster instead of the
 * full 67-row list. The filter keys ride as query parameters on the write, the way
 * `TraineeSelect.vue::choose()` now sends them. Empty and absent values are dropped, so a clear
 * still lands on the bare route.
 */
it('carries all six filters through the trainee store redirect', function (): void {
    $trainee = Umamusume::factory()->create();

    $this->put(route('career.trainee.store', [
        'search' => 'gold',
        'sortBy' => 'turf',
        'direction' => 'desc',
        'surface' => 'S',
        'distance' => 'A',
        'style' => 'B',
    ]), [
        'umamusume_id' => $trainee->id,
    ])->assertRedirect(route('career.trainee').'?search=gold&sortBy=turf&direction=desc&surface=S&distance=A&style=B');
});

it('drops empty filters from the redirect query', function (): void {
    $trainee = Umamusume::factory()->create();

    $this->put(route('career.trainee.store', [
        'sortBy' => 'name',
        'direction' => 'asc',
        'distance' => 'S',
    ]), [
        'umamusume_id' => $trainee->id,
    ])->assertRedirect(route('career.trainee').'?sortBy=name&direction=asc&distance=S');
});

it('preserves search and sortBy through multiple selections', function (): void {
    $first = Umamusume::factory()->create(['name' => 'Gold Ship']);
    $second = Umamusume::factory()->create(['name' => 'Special Week']);

    $expected = route('career.trainee').'?search=gold&sortBy=name&direction=asc';

    $this->put(route('career.trainee.store', [
        'search' => 'gold',
        'sortBy' => 'name',
        'direction' => 'asc',
    ]), ['umamusume_id' => $first->id])->assertRedirect($expected);

    // Second selection (returning to picker, selecting another) keeps the same query.
    $this->put(route('career.trainee.store', [
        'search' => 'gold',
        'sortBy' => 'name',
        'direction' => 'asc',
    ]), ['umamusume_id' => $second->id])->assertRedirect($expected);
});

it('allows changing sort direction through repeated selections', function (): void {
    $trainee = Umamusume::factory()->create();

    $this->put(route('career.trainee.store', ['sortBy' => 'turf', 'direction' => 'asc']), [
        'umamusume_id' => $trainee->id,
    ])->assertRedirect(route('career.trainee').'?sortBy=turf&direction=asc');

    $this->put(route('career.trainee.store', ['sortBy' => 'turf', 'direction' => 'desc']), [
        'umamusume_id' => $trainee->id,
    ])->assertRedirect(route('career.trainee').'?sortBy=turf&direction=desc');
});

it('preserves facets through the store redirect', function (): void {
    $trainee = Umamusume::factory()->create();

    $this->put(route('career.trainee.store', ['surface' => 'A', 'distance' => 'S', 'style' => 'B']), [
        'umamusume_id' => $trainee->id,
    ])->assertRedirect(route('career.trainee').'?surface=A&distance=S&style=B');
});

it('drops an empty facet so a clear lands on the bare route', function (): void {
    $trainee = Umamusume::factory()->create();

    // No facet, search or sort in the query: every key is dropped and the redirect is the bare route.
    $this->put(route('career.trainee.store'), [
        'umamusume_id' => $trainee->id,
    ])->assertRedirect(route('career.trainee'));
});
