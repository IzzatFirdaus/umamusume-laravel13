<?php

declare(strict_types=1);

use App\Models\RaceCatalogSlot;
use App\Models\Skill;
use App\Models\SupportCard;
use App\Models\Umamusume;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Slice 9.1 — the hub's nine area counts, and the one query that produces the four live ones.
 *
 * The hub is the screen every other Database area is reached from, so it is the one place where a
 * count per area has to be cheap: four `count()` calls are four round trips for four integers. The
 * controller reads them from one `UNION ALL`, and this file pins both halves — the numbers the areas
 * carry, and the query count the request costs.
 *
 * The five areas that print a word instead of a number are asserted as `null` rather than as `0`: a
 * zero would be a claim that a table holds no rows, and those areas hold no table at all.
 */
it('sends a live count for every area that has a table, and null where the rows are transcribed', function (): void {
    Umamusume::factory()->count(3)->create(['external_ref' => 'test-ref']);
    SupportCard::factory()->count(2)->create();
    Skill::factory()->count(4)->create();
    // One slot, not five: `race_catalog_slots` carries a shared-grain unique index, so a factory loop
    // would collide on the second insert rather than measuring the count.
    RaceCatalogSlot::factory()->create();

    $this->get('/database')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('areas', 9)
            ->where('areas.0.label', 'Trainees')
            ->where('areas.0.count', 3)
            ->where('areas.1.label', 'Support Cards')
            ->where('areas.1.count', 2)
            ->where('areas.2.label', 'Skills')
            ->where('areas.2.count', 4)
            ->where('areas.3.label', 'Races')
            ->where('areas.3.count', 1)
            // The scenario matrix reads its count off `config/scenarios.php`, not off a table.
            ->where('areas.5.label', 'Scenarios')
            ->where('areas.5.count', count((array) config('scenarios.scenarios', [])))
            // Four areas have no table to count, and they say so with a word on the tile.
            ->where('areas.4.count', null)
            ->where('areas.6.count', null)
            ->where('areas.7.count', null)
            ->where('areas.8.count', null));
});

it('reads the four live counts in one query rather than four', function (): void {
    $queries = [];
    DB::listen(static function ($query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    $this->get('/database')->assertOk();

    // The page also reads its filter vocabulary and the scenario matrix, so the request is never
    // literally one statement. What this pins is that the four AREA counts are not four additional
    // round trips: at most two statements may mention the four catalog tables.
    $countQueries = array_values(array_filter(
        $queries,
        static fn (string $sql): bool => str_contains($sql, 'count(')
            && (str_contains($sql, 'umamusume') || str_contains($sql, 'support_cards')
                || str_contains($sql, 'skills') || str_contains($sql, 'race_catalog_slots')),
    ));

    expect($countQueries)->toHaveCount(1)
        ->and($countQueries[0])->toContain('union all');
});
