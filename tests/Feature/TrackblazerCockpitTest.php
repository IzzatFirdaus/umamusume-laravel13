<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Enums\TurnEventType;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\TrainingRun;
use App\Models\TurnEvent;
use App\Models\TurnEvents\ShopPurchasePayload;
use App\Models\Umamusume;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * G6, the shop ledger (`scenario.shop.ledger`).
 *
 * Two components the brief listed were dropped before they were built, because the Pro Shop arm already
 * renders what they would have carried (correction 27): the drawer's catalogue, purchases list and
 * purchase form are all in that arm, and the Epithet routes arm already lists every route with its state
 * and its outstanding races. What survives is the part the arm lacks: a held count per item against the
 * cap, the one publisher's worth ratings, and the Summer reserve.
 *
 * The provenance is the point of these tests, not a byproduct:
 *
 *  - a held count is `confirmed` because it is counted from recorded purchase rows;
 *  - the ratings and the reserve are `estimated`, because they are one guide's opinions rather than rules;
 *  - the inflow is `calculated`, arithmetic on entered rows and a published payout;
 *  - and the ledger prints no coin balance and no projected cost, so it neither repeats the arm's own
 *    absence nor invents a figure nothing can source.
 */

function g6Run(array $state = []): TrainingRun
{
    return TrainingRun::factory()->create([
        'scenario' => 'trackblazer',
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->create()->id,
        ...$state,
    ]);
}

function g6Buy(TrainingRun $run, string $item, int $cost, string $effect, int $turn = 5): TurnEvent
{
    return TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => $turn,
        'event_type' => TurnEventType::Scenario,
        'source_name' => 'Shop',
        'deltas' => ShopPurchasePayload::fromArray([
            'item' => $item,
            'cost' => $cost,
            'effect' => $effect,
        ], $run)->toArray(),
    ]);
}

function g6Entered(TrainingRun $run, string $title, int $turn, ?int $objective = 2): RaceEntry
{
    $slot = RaceCatalogSlot::factory()->create([
        'scenario_key' => 'trackblazer',
        'year' => RaceCatalogSlot::YEAR_JUNIOR,
        'turn' => $turn,
        'title' => $title,
        'tier' => 'G2',
    ]);

    return RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Entered,
        'objective_index' => $objective,
    ]);
}

it('counts what this run holds against the cap the catalogue states', function (): void {
    $run = g6Run();

    g6Buy($run, 'Speed Scroll', 30, '+15 Speed', 5);
    g6Buy($run, 'Speed Scroll', 30, '+15 Speed', 9);
    g6Buy($run, 'Good-Luck Charm', 40, 'Training failure rate 0% for 1 turn', 11);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('scenario.shop.ledger.items', 2)
            ->where('scenario.shop.ledger.items.0.name', 'Speed Scroll')
            ->where('scenario.shop.ledger.items.0.held', 2)
            ->where('scenario.shop.ledger.items.0.cap', 5)
            ->where('scenario.shop.ledger.items.1.name', 'Good-Luck Charm')
            ->where('scenario.shop.ledger.items.1.held', 1));
});

it('carries the publisher ratings as estimated, with the source in the title', function (): void {
    $run = g6Run();
    g6Buy($run, 'Speed Scroll', 30, '+15 Speed');

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.shop.ledger.items.0.worth', 'Must Buy')
            ->where('scenario.shop.ledger.items.0.worth_badge', 'estimated')
            ->where('scenario.shop.ledger.items.0.worth_title', fn (string $title): bool => str_contains($title, 'SCENARIO-PUBLISHER-REFERENCES.md')
                && str_contains($title, '199-222')));
});

it('rates every catalogue item today, and names the branch that cannot be reached', function (): void {
    $rated = (array) config('scenarios.scenarios.trackblazer.shop_worth');
    $catalogue = (array) config('scenarios.scenarios.trackblazer.shop_items');

    $unrated = [];

    foreach ($catalogue as $row) {
        if (! array_key_exists((string) $row['name'], $rated)) {
            $unrated[] = (string) $row['name'];
        }
    }

    // The invariant worth pinning: no catalogue row is silently unrated. It is what makes the
    // component's `worth === null` branch unreachable today, and this assertion is the thing that
    // fails if a future row is added without a rating, so the branch is not dead code being carried
    // blind. That branch is therefore **untested**: it exists for exactly the config change this
    // assertion forbids, and no fixture can reach it while the set is complete.
    expect($unrated)->toBe([], 'a catalogue item with no publisher rating appeared: '.implode(', ', $unrated));

    expect(array_keys($rated))->toHaveCount(count($catalogue));

    $run = g6Run();
    g6Buy($run, 'Speed Scroll', 30, '+15 Speed');

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.shop.ledger.items.0.worth', fn (string $worth): bool => $worth !== '')
            ->where('scenario.shop.ledger.items.0.worth_badge', 'estimated'));
});

it('carries the Summer reserve as the publisher tip, never as a rule', function (): void {
    $run = g6Run();

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.shop.ledger.reserve.badge', 'estimated')
            ->where('scenario.shop.ledger.reserve.value', '100')
            ->where('scenario.shop.ledger.reserve.label', fn (string $label): bool => str_contains($label, 'Summer'))
            ->where('scenario.shop.ledger.reserve.title', fn (string $title): bool => str_contains($title, 'line 273')));
});

it('projects an inflow from the entered races at the published payout', function (): void {
    $run = g6Run();

    g6Entered($run, 'Junior Mile Cup', 20);
    g6Entered($run, 'Junior Turf Trial', 22);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // Two entered races at the published 100 coins for a 1st place.
            ->where('scenario.shop.ledger.inflow', '200')
            ->where('scenario.shop.ledger.inflow_badge', 'calculated')
            ->where('scenario.shop.ledger.inflow_title', fn (string $title): bool => str_contains($title, 'line 372')));
});

it('leaves the inflow absent while no race is entered', function (): void {
    $run = g6Run();

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.shop.ledger.inflow', null)
            ->where('scenario.shop.ledger.inflow_badge', null)
            ->where('scenario.shop.ledger.inflow_title', fn (string $title): bool => str_contains($title, 'No race is entered')));
});

it('prints no balance and no projected cost, so the arm stays the only surface for both', function (): void {
    $run = g6Run();
    g6Buy($run, 'Speed Scroll', 30, '+15 Speed');
    g6Entered($run, 'Junior Mile Cup', 20);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.shop.ledger', fn (Collection $ledger): bool => array_keys($ledger->all()) === [
                'items', 'items_absence', 'cap', 'inflow', 'inflow_badge', 'inflow_title',
                'reserve', 'rated_count', 'rated_title',
            ]));
});

it('keeps the ledger a subset of the shop arm rather than a second shop surface', function (): void {
    $run = g6Run();

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // The arm's own keys are untouched apart from the one append, and the catalogue and form
            // still live there alone.
            ->where('scenario.shop.catalogue', fn (Collection $catalogue): bool => $catalogue->count() > 0)
            ->where('scenario.shop.fill', fn (string $fill): bool => str_contains($fill, '/purchases'))
            ->has('scenario.shop.ledger'));
});

it('prints no recommendation of its own and no banned gloss', function (): void {
    $run = g6Run();
    g6Buy($run, 'Speed Scroll', 30, '+15 Speed');

    $displayed = '';

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(function (Assert $page) use (&$displayed): void {
            $displayed = strtolower(json_encode($page->toArray()['props']['scenario']['shop']['ledger']));
        });

    foreach (['recommended purchase', 'best value', 'we recommend', 'strength', 'gacha', 'jewel'] as $word) {
        expect($displayed)->not->toContain($word);
    }

    $source = strtolower((string) file_get_contents(
        base_path('resources/js/components/scenario/TrackblazerResourceLedger.vue')
    ));

    foreach (['recommended purchase', 'best value', 'we recommend'] as $word) {
        expect($source)->not->toContain($word);
    }
});

it('answers another scenario with no ledger at all', function (): void {
    $run = TrainingRun::factory()->create([
        'scenario' => 'ura_finale',
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->create()->id,
    ]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('scenario.shop', null));
});
