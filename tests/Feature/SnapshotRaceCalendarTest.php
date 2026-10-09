<?php

declare(strict_types=1);

use App\Models\RaceCatalogSlot;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * The race surfaces read the turn they decide about from the run's own position.
 *
 * A snapshot at Senior Early October must show the Senior calendar at that position, not a Junior
 * January one; a new-career run keeps `nextTurnToPlay()`, which points at the turn after the last one
 * logged.
 */
it('renders a snapshot race decision at the imported Senior position', function (): void {
    $run = TrainingRun::factory()->create([
        'scenario' => 'ura_finale',
        'career_position' => ['year' => 3, 'month' => 10, 'phase' => 'Early', 'turn_index' => 67],
    ]);

    RaceCatalogSlot::factory()->create([
        'scenario_key' => null,
        'year' => RaceCatalogSlot::YEAR_SENIOR,
        'month' => 10,
        'half' => 'Early',
        'turn' => 19,
        'title' => 'Tenno Sho (Autumn)',
        'sort_order' => 1,
    ]);

    $this->get(route('runs.races.decision', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/RaceDecision')
            ->where('nextTurn.year_label', 'Senior Year')
            ->where('nextTurn.turn', 19)
            ->where('nextTurn.position_label', 'Early October')
            ->has('races', 1)
            ->where('races.0.title', 'Tenno Sho (Autumn)'));
});

it('renders a snapshot race planner at the imported Senior position', function (): void {
    $run = TrainingRun::factory()->create([
        'scenario' => 'ura_finale',
        'career_position' => ['year' => 3, 'month' => 10, 'phase' => 'Early', 'turn_index' => 67],
    ]);

    RaceCatalogSlot::factory()->create([
        'scenario_key' => null,
        'year' => RaceCatalogSlot::YEAR_SENIOR,
        'month' => 10,
        'half' => 'Early',
        'turn' => 19,
        'title' => 'Tenno Sho (Autumn)',
        'sort_order' => 1,
    ]);

    $this->get(route('runs.races.planner', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/RacePlanner')
            ->where('nextTurn.year_label', 'Senior Year')
            ->where('nextTurn.turn', 19));
});

it('still reads a new-career race decision from the turn after the last logged', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1]);

    RaceCatalogSlot::factory()->create([
        'scenario_key' => null,
        'year' => RaceCatalogSlot::YEAR_JUNIOR,
        'month' => 1,
        'half' => 'Late',
        'turn' => 2,
        'title' => 'Junior January Race',
        'sort_order' => 1,
    ]);

    $this->get(route('runs.races.decision', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('nextTurn.year_label', 'Junior Year')
            ->where('nextTurn.turn', 2)
            ->has('races', 1));
});
