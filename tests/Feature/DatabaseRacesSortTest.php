<?php

declare(strict_types=1);

use App\Models\RaceCatalogSlot;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The calendar's ordering tokens, which were named and then neither defined nor applied.
 *
 * `races()` gained a `$sort` read that clamped against `self::SORTS` while no such constant existed, so
 * any request carrying `?sort=` was a fatal on a name that is not there (`phpstan` caught it;
 * `--filter=Database` did not, because no feature test ever posted the parameter). And once the
 * constant is restored the clamp still has to do something: a token that is accepted and then ignored
 * is a promise the page does not keep.
 *
 * These cases pin both halves, so neither the constant nor its effect can go missing unnoticed again.
 */
it('clamps an unknown sort token to the calendar order instead of refusing the page', function (): void {
    RaceCatalogSlot::factory()->create(['title' => 'Late Race', 'fans_needed' => 1000, 'year' => 3, 'turn' => 10]);
    RaceCatalogSlot::factory()->create(['title' => 'Early Race', 'fans_needed' => 500, 'year' => 1, 'turn' => 20]);

    // Both spellings of "not a token we offer": a value, and no value at all. Neither may 500.
    foreach (['nonsense', ''] as $token) {
        $this->get(route('database.races', ['sort' => $token]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Database/Races')
                ->where('filters.sort', null)
                ->where('slots.data.0.title', 'Early Race'));
    }
});

it('orders by name when the token asks for it', function (): void {
    RaceCatalogSlot::factory()->create(['title' => 'Zephyr Stakes', 'year' => 1, 'turn' => 5]);
    RaceCatalogSlot::factory()->create(['title' => 'Arlington Cup', 'year' => 3, 'turn' => 5]);

    // Alphabetical, not calendar: the later race sorts first because its title does.
    $this->get(route('database.races', ['sort' => 'name']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.sort', 'name')
            ->where('slots.data.0.title', 'Arlington Cup')
            ->where('slots.data.1.title', 'Zephyr Stakes'));
});

it('orders by fan threshold when the token asks for it', function (): void {
    RaceCatalogSlot::factory()->create(['title' => 'Cheap Race', 'fans_needed' => 100, 'year' => 1, 'turn' => 5]);
    RaceCatalogSlot::factory()->create(['title' => 'Rich Race', 'fans_needed' => 9000, 'year' => 2, 'turn' => 5]);

    $this->get(route('database.races', ['sort' => 'fans']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Database/Races')
            // Echoed so a restored URL paints the ordering it applied.
            ->where('filters.sort', 'fans')
            // And applied, which is the half that was missing: the clamp accepted the token and the
            // query still ran in calendar order.
            ->where('slots.data.0.title', 'Rich Race')
            ->where('slots.data.1.title', 'Cheap Race'));
});

it('echoes the applied order and keeps calendar as a reset', function (): void {
    // `calendar` echoes as itself rather than null: it is an offered token naming the default order,
    // and a select that paints "Calendar order" after a reset is stating the truth. Only a token the
    // table does not offer clamps to null, which the first case covers.
    foreach ([['calendar', null], ['name', 'asc'], ['grade', 'asc'], ['distance', 'desc'], ['fans', 'desc']] as [$token, $direction]) {
        test()->get(route('database.races', ['sort' => $token]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.sort', $token)
                ->where('filters.direction', $direction));
    }
});

it('sorts the complete filtered race set before applying pagination', function (): void {
    RaceCatalogSlot::factory()->create(['title' => 'First by calendar', 'surface' => 'Turf', 'distance' => 2000, 'year' => 1]);
    RaceCatalogSlot::factory()->create(['title' => 'Shortest', 'surface' => 'Turf', 'distance' => 1200, 'year' => 3]);
    RaceCatalogSlot::factory()->create(['title' => 'Unrecorded distance', 'surface' => 'Turf', 'distance' => null, 'year' => 1]);
    RaceCatalogSlot::factory()->create(['title' => 'Dirt sprint', 'surface' => 'Dirt', 'distance' => 1000, 'year' => 2]);

    $filters = ['surface' => 'Turf', 'sort' => 'distance', 'direction' => 'asc', 'pageSize' => 1];

    foreach (['Shortest', 'First by calendar', 'Unrecorded distance'] as $index => $title) {
        test()->get(route('database.races', [...$filters, 'page' => $index + 1]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('slots.total', 3)
                ->where('filters.direction', 'asc')
                ->where('slots.data.0.title', $title));
    }
});

it('sorts grades on their stored order instead of their labels', function (): void {
    RaceCatalogSlot::factory()->create(['title' => 'Debut', 'tier' => 'Debut', 'grade_code' => 900, 'year' => 1]);
    RaceCatalogSlot::factory()->create(['title' => 'Grade One', 'tier' => 'G1', 'grade_code' => 100, 'year' => 2]);

    test()->get(route('database.races', ['sort' => 'grade', 'direction' => 'asc']))
        ->assertInertia(fn (Assert $page) => $page->where('slots.data.0.title', 'Grade One'));
    test()->get(route('database.races', ['sort' => 'grade', 'direction' => 'desc']))
        ->assertInertia(fn (Assert $page) => $page->where('slots.data.0.title', 'Debut'));
});
