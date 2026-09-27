<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\TrainingRun;
use App\Models\TurnEntry;

/*
 * S2: the run-detail goal panels read from `scenario_slots` and this run's own
 * race entries, not from values written in the template.
 *
 * The shaping lives on the model beside `stripValues()` because it is the same
 * kind of thing: run state composed for a component. Keeping it in the view
 * would put a query and a config walk inside a template.
 */

function runWithFans(int $fans): TrainingRun
{
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1, 'fans' => $fans]);

    return $run->fresh();
}

it('places a scenario slot in the calendar at its own month and half', function (): void {
    $run = runWithFans(20000);
    ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'goal_race',
        'title' => 'Tenno Sho (Spring)',
        'month' => 4,
        'half' => 'Late',
        'is_mandatory' => true,
        'fans_needed' => 12000,
    ]);

    $cells = $run->calendarCells();

    // The component indexes cells from zero (Jan = 0); the table stores 1-12.
    expect($cells[3]['halves']['Late']['state'])->toBe('goal')
        ->and($cells[3]['halves']['Late']['label'])->toBe('Tenno Sho (Spring)')
        ->and($cells[0]['halves']['Early']['state'])->toBe('empty')
        ->and(count($cells))->toBe(12);
});

it('locks a fan-gated race until this run has the fans it asks for', function (): void {
    ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'goal_race',
        'title' => 'Oka Sho',
        'month' => 4,
        'half' => 'Early',
        'is_mandatory' => false,
        'fans_needed' => 15000,
    ]);

    $short = runWithFans(3000);
    $long = runWithFans(15000);

    // The figure travels with the lock: it is what the Trainer works toward (D-173).
    expect($short->calendarCells()[3]['halves']['Early']['state'])->toBe('fan_locked')
        ->and($short->calendarCells()[3]['halves']['Early']['fans_needed'])->toBe(15000)
        ->and($long->calendarCells()[3]['halves']['Early']['state'])->toBe('open');
});

it('shows a maiden-gated race as a maiden lock until this run has won', function (): void {
    ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'goal_race',
        'title' => 'Naruta Kinpa Cup',
        'month' => 5,
        'half' => 'Early',
        'is_mandatory' => false,
        'is_maiden_gated' => true,
        'fans_needed' => 0,
    ]);

    $winless = runWithFans(3000);

    expect($winless->calendarCells()[4]['halves']['Early']['state'])->toBe('maiden_locked')
        // No fan figure: a maiden gate is decided by an event, not a quantity.
        ->and($winless->calendarCells()[4]['halves']['Early'])->not->toHaveKey('fans_needed');

    RaceEntry::create([
        'training_run_id' => $winless->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
    ]);

    expect($winless->fresh()->calendarCells()[4]['halves']['Early']['state'])->toBe('open');
});

it('marks a slot this run has already raced as past', function (): void {
    $run = runWithFans(20000);
    $slot = ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'goal_race',
        'title' => 'Asahi Hai Futurity Stakes',
        'month' => 11,
        'half' => 'Late',
        'is_mandatory' => true,
    ]);

    RaceEntry::create([
        'training_run_id' => $run->id,
        'scenario_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 3,
    ]);

    expect($run->calendarCells()[10]['halves']['Late']['state'])->toBe('past');
});

it('composes the calendar only from the run own scenario', function (): void {
    $run = runWithFans(20000);
    ScenarioSlot::factory()->create([
        'scenario_key' => 'unity_cup',
        'kind' => 'goal_race',
        'month' => 4,
        'half' => 'Late',
    ]);

    // Another scenario's calendar is not this run's, and the panel says so in its
    // own words rather than drawing a grid of blanks (D-220).
    expect($run->calendarCells())->toBe([]);
});

it('names no scenario race for a run that has not chosen one', function (): void {
    // Found in the browser pass, not by the suite. `scenarioKey()` falls back to the
    // baseline so the resource strip always has something to compose — an owner
    // ruling about generic widgets. The goal panels are not generic: they name
    // Oka Sho and Tenno Sho. Feeding them the fallback made a run whose header says
    // "No scenario set" list URA's actual race schedule below it.
    ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'goal_race',
        'title' => 'Oka Sho',
        'month' => 4,
        'half' => 'Early',
    ]);

    $run = TrainingRun::factory()->create(['scenario' => null]);

    expect($run->calendarCells())->toBe([])
        ->and($run->gradeObjectives())->toBe([])
        ->and($run->gradeEarned())->toBeNull()
        ->and(test()->get("/training-runs/{$run->id}")->getContent())
        ->not->toContain('Race calendar')
        ->not->toContain('Grade Point')
        ->not->toContain('Oka Sho');
});

it('lists the Grade Point objectives in order, named from the matrix', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'trackblazer']);

    expect($run->gradeObjectives())->toBe([
        ['name' => 'Debut race', 'required' => 0],
        ['name' => 'End of Junior Year', 'required' => 60],
        ['name' => 'End of Classic Year', 'required' => 300],
        ['name' => 'End of Senior Year', 'required' => 300],
    ]);
});

it('sums Grade Points only when every completed race can be priced', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'trackblazer']);
    $slot = ScenarioSlot::factory()->create([
        'scenario_key' => 'trackblazer',
        'kind' => 'goal_race',
        'tier' => 'G1',
    ]);

    expect($run->gradeEarned())->toBeNull();

    RaceEntry::create([
        'training_run_id' => $run->id,
        'scenario_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
    ]);

    // 100 for a G1 win, from config's `grade_point_by_grade` (docs/scenarios/05 §Grade Points).
    expect($run->fresh()->gradeEarned())->toBe(100);

    // Below first the points scale down, and no ratio for that is in our corpus, so
    // the total is withheld rather than understated.
    RaceEntry::create([
        'training_run_id' => $run->id,
        'scenario_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 2,
    ]);

    expect($run->fresh()->gradeEarned())->toBeNull();
});
