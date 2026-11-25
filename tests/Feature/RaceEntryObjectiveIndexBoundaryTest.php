<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\TrainingRun;

/*
 * The Result Point period on a race entry belongs to the scenario's own Grade Point panel.
 *
 * `RaceEntry`'s saving guard has always refused a period on a run whose scenario composes none;
 * this pins that the HTTP boundary refuses it first. A hand-made POST that reached the guard used
 * to answer 500 where the form shows a field error, which is the failure mode the `circles` rule in
 * `StoreRaceEntryRequest` was moved to the request to avoid.
 */

it('refuses a Result Point period on a scenario that composes none', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $slot = ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'goal_race',
        'title' => 'Test Race',
        'tier' => 'G1',
    ]);

    $this->from(route('runs.show', $run))
        ->post(route('runs.races.store', $run), [
            'entry_mode' => 'calendar',
            'scenario_slot_id' => $slot->id,
            'status' => RaceEntryStatus::Completed->value,
            'placement' => 1,
            'objective_index' => 2,
        ])
        ->assertSessionHasErrors('objective_index');

    expect(RaceEntry::where('training_run_id', $run->id)->count())->toBe(0);
});

it('accepts the same period on a scenario that composes them', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'trackblazer']);
    $slot = ScenarioSlot::factory()->create([
        'scenario_key' => 'trackblazer',
        'kind' => 'goal_race',
        'title' => 'Test Race',
        'tier' => 'G1',
    ]);

    $this->from(route('runs.show', $run))
        ->post(route('runs.races.store', $run), [
            'entry_mode' => 'calendar',
            'scenario_slot_id' => $slot->id,
            'status' => RaceEntryStatus::Completed->value,
            'placement' => 1,
            'objective_index' => 2,
        ])
        ->assertRedirect(route('runs.show', $run));

    expect(RaceEntry::where('training_run_id', $run->id)->sole()->objective_index)->toBe(2);
});
