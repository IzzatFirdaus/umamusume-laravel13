<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Enums\TurnEventType;
use App\Models\TrainingRun;
use App\Models\TurnEvent;
use App\Models\TurnEvents\ShopPurchasePayload;
use App\Models\Umamusume;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * SCREEN-016, the Trackblazer scenario panel (plan §9 E4). The §E4 contract: the shell registers
 * `TrackblazerPanel` against `grade_objectives`, `shop` and `epithet_routes`; the component reads
 * `scenario.grade`, `scenario.shop` and `scenario.epithet`, never a scenario key. Other scenarios
 * carry the same keys with null values, so the section's shape stays uniform across the four
 * Global scenarios (the ScenarioPanelTest pin it inherits).
 *
 * The acceptance rules the plan names:
 *
 *  - the catalogue is `config('scenarios.scenarios.trackblazer.shop_items')` in its own order;
 *  - the catalogue carries no `recommended` field; the rotation is not modelled;
 *  - a recorded purchase reads back through the same payload path the Blade form used
 *    (`ShopPurchasePayload::fromArray()`), with no second table;
 *  - a rejected purchase (off-catalogue item, wrong price) returns a field-bound error rather
 *    than a 500;
 *  - the epithet checklist renders with the three states `earned`, `open`, `unverifiable` from
 *    `epithetProgress()`;
 *  - the grade-points meter carries `current` and `earned` as null while nothing is recorded,
 *    and the unpriced / unassigned counts render zero rather than fabricating a figure.
 *
 * The `Twinkle Star Climax` finale name lives as a named absence with a `title`; the absence
 * string is asserted here and the value is never printed under test.
 */

function trackblazerRun(array $state = []): TrainingRun
{
    return TrainingRun::factory()->create([
        'scenario' => 'trackblazer',
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->create()->id,
        ...$state,
    ]);
}

function trackblazerRecorded(TrainingRun $run, int $turn, array $deltas): TurnEvent
{
    return TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => $turn,
        'event_type' => TurnEventType::Scenario,
        'source_name' => 'Shop',
        'deltas' => $deltas,
    ]);
}

it('exposes the panel sections a Trackblazer panel renderer reads', function (): void {
    $run = trackblazerRun();

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('scenario.grade')
            ->has('scenario.shop')
            ->has('scenario.epithet')
            ->has('scenario.rival')
            ->has('scenario.finale_official_title_absence')
            // The Trackblazer matrix flips three flags on; the others stay off.
            ->where('scenario.panels.grade_objectives.on', true)
            ->where('scenario.panels.shop.on', true)
            ->where('scenario.panels.epithet_routes.on', true)
            ->where('scenario.panels.race_calendar.on', false));
});

it('prints the catalogue in config order with name, cost and effect, no recommendation field', function (): void {
    $run = trackblazerRun();

    $expected = collect((array) config('scenarios.scenarios.trackblazer.shop_items'))
        ->map(fn (array $row): array => [
            'name' => (string) $row['name'],
            'cost' => (int) $row['cost'],
            'effect' => (string) $row['effect'],
        ])
        ->values()
        ->all();

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // Order and content both come from config; an "Inheritance Rate" sort or any other
            // permutation would land a row where the client does not.
            ->where('scenario.shop.catalogue', fn (Collection $catalogue): bool => $catalogue->all() === $expected)
            // The rotation is not modelled; no row carries a best-value flag, on-sale flag or
            // any field outside the source catalogue.
            ->where('scenario.shop', fn (Collection $shop): bool => array_keys($shop->all()) === [
                'catalogue', 'purchases', 'rotation_turns', 'rotation_resets_in',
                'max_copies', 'spend_total', 'fill', 'select_action',
            ])
        );
});

it('reads a recorded purchase back through the same payload path the Blade form used', function (): void {
    $run = trackblazerRun();

    $payload = ShopPurchasePayload::fromArray([
        'item' => 'Speed Scroll',
        'cost' => 30,
        'effect' => '+15 Speed',
    ], $run);

    trackblazerRecorded($run, 7, $payload->toArray());

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.shop.purchases', fn (Collection $purchases): bool => $purchases->count() === 1
                && $purchases->first()['item'] === 'Speed Scroll'
                && $purchases->first()['cost'] === 30
                && $purchases->first()['effect'] === '+15 Speed'
                && $purchases->first()['turn'] === 7));
});

it('rejects an off-catalogue purchase with a field-bound error, not a 500', function (): void {
    $run = trackblazerRun();

    $this->post(route('runs.purchases.store', $run), [
        'turn' => 5,
        'item' => 'Imaginary Item',
        'cost' => 0,
        'effect' => 'Anything',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors(['item']);
});

it('rejects a wrong-priced purchase with a field-bound error, not a 500', function (): void {
    $run = trackblazerRun();

    $this->post(route('runs.purchases.store', $run), [
        'turn' => 5,
        'item' => 'Speed Scroll',
        'cost' => 999,
        'effect' => '+15 Speed',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors(['cost']);
});

it('carries the same null trackblazer shape for non-trackblazer scenarios', function (): void {
    $expectedKeys = ['grade', 'shop', 'epithet', 'rival', 'finale_official_title_absence'];

    foreach (['ura_finale', 'unity_cup', 'our_grand_concert'] as $key) {
        $run = TrainingRun::factory()->create([
            'scenario' => $key,
            'status' => RunStatus::Active,
            'umamusume_id' => Umamusume::factory()->create()->id,
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(function (Assert $page) use ($key, $expectedKeys): void {
                $page->where('scenario', function (Collection $section) use ($key, $expectedKeys): bool {
                    $all = $section->all();

                    // The five §E4 keys are part of the section's uniform shape, so the
                    // ScenarioPanelTest pin holds for every scenario including the four.
                    foreach ($expectedKeys as $k) {
                        if (! array_key_exists($k, $all)) {
                            test()->fail("[{$key}] missing scenario.{$k}");
                        }
                    }

                    // The keys are null unless the scenario's config turns the flag on.
                    expect($all['grade'])->toBeNull();
                    expect($all['shop'])->toBeNull();
                    expect($all['epithet'])->toBeNull();
                    expect($all['rival'])->toBeNull();

                    return true;
                });
            });
    }
});

it('lists epithets from config with the run-recorded titles showing the open rows', function (): void {
    $run = trackblazerRun();

    $expectedCount = count((array) config('scenarios.scenarios.trackblazer.epithet_routes'));

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        // The matrix is the source of truth for the row count.
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.epithet.rows', fn (Collection $rows): bool => $rows->count() === $expectedCount
                // The first config row names the Lady epithet; with no races recorded it is
                // `open`, not `earned`.
                && $rows->first()['epithet'] === 'Lady'
                && $rows->first()['state'] === 'open'
                // A row whose condition this surface cannot see is `unverifiable`, never `open`,
                // and that is the load-bearing distinction.
                && $rows->contains(fn (array $row): bool => $row['epithet'] === 'Eat My Dust' && $row['state'] === 'unverifiable')));
});

it('prints a points-league finale and the official-title absence with a citation', function (): void {
    $run = trackblazerRun();

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.finale', function (Collection $finale): bool {
                $row = $finale->all();

                // The matrix supplies `kind` and `races` verbatim (config/scenarios.php line 358).
                return ($row['kind'] ?? null) === 'points_league'
                    && (int) ($row['races'] ?? 0) === 3;
            })
            // The brief bans the "Twinkle Star Climax" string under §7 conflict row 31. The
            // absence is rendered as a `title` citation so a screen reader hears it.
            ->where('scenario.finale_official_title_absence', fn (string $absence): bool => str_contains($absence, 'Twinkle Star Climax')
                && (str_contains($absence, 'UNVERIFIED') || str_contains($absence, 'reference guide'))));
});

it('prints the grade-points period without an invented total', function (): void {
    $run = trackblazerRun();

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.grade.current', null)
            ->where('scenario.grade.earned', null)
            ->where('scenario.grade.unpriced_count', 0)
            ->where('scenario.grade.unassigned_count', 0)
            // The four config rows land in declared order; the debut race asks for no points.
            ->where('scenario.grade.objectives', fn (Collection $objectives): bool => $objectives->count() === 4
                && $objectives->first()['name'] === 'Debut race'
                && $objectives->first()['required'] === 0
                && $objectives->last()['name'] === 'End of Senior Year'
                && $objectives->last()['required'] === 300)
            ->where('scenario.grade.periods', fn (Collection $periods): bool => $periods->count() === 4));
});

it('records a Trackblazer purchase and rejects exceeding the held-copies cap from the matrix', function (): void {
    $cap = (int) config('scenarios.scenarios.trackblazer.shop.max_copies_per_item');

    // Fill the cap with accepted items; the next write fails with a field-bound error.
    $run = trackblazerRun();

    for ($turn = 1; $turn <= $cap; $turn++) {
        trackblazerRecorded($run, $turn, ShopPurchasePayload::fromArray([
            'item' => 'Energy Drink MAX EX',
            'cost' => 50,
            'effect' => 'Max Energy +8',
        ], $run)->toArray());
    }

    $this->post(route('runs.purchases.store', $run), [
        'turn' => 12,
        'item' => 'Energy Drink MAX EX',
        'cost' => 50,
        'effect' => 'Max Energy +8',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors(['item']);
});
