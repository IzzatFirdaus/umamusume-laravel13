<?php

declare(strict_types=1);

use App\Actions\CreateSnapshotRun;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;

/**
 * C05 — Scenario immutability after first recorded turn.
 *
 * Once a career records its first turn, its scenario becomes immutable. The guard sits at two
 * boundaries: the model's saving event (catches all writes) and StoreTrainingRunRequest (catches
 * HTTP writes before the model). Before any turn exists, the scenario remains editable.
 */
it('allows scenario change before any turn is recorded', function (): void {
    $trainee = Umamusume::factory()->create();
    $run = TrainingRun::factory()->create([
        'umamusume_id' => $trainee->id,
        'scenario' => 'ura_finale',
    ]);

    // No turn entries yet → scenario can change.
    expect($run->turnEntries()->count())->toBe(0);
    expect($run->canChangeScenario())->toBeTrue();
});

it('blocks scenario change after first turn via model guard', function (): void {
    $trainee = Umamusume::factory()->create();
    $run = TrainingRun::factory()->create([
        'umamusume_id' => $trainee->id,
        'scenario' => 'ura_finale',
    ]);

    // Record one turn.
    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 1,
    ]);

    // Now scenario is immutable.
    $run->refresh();
    expect($run->canChangeScenario())->toBeFalse();

    // Direct model write to change scenario should throw.
    expect(fn () => $run->update(['scenario' => 'unity_cup']))
        ->toThrow(RuntimeException::class, 'Cannot change scenario on run');
});

it('blocks scenario change via HTTP update after first turn', function (): void {
    $trainee = Umamusume::factory()->create();
    $run = TrainingRun::factory()->create([
        'umamusume_id' => $trainee->id,
        'scenario' => 'ura_finale',
    ]);

    // Record one turn.
    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 1,
    ]);

    // Attempt to change scenario via HTTP PUT.
    $response = $this->put(route('runs.update', $run), [
        'umamusume_id' => $trainee->id,
        'scenario' => 'unity_cup',
        'status' => 'Active',
    ]);

    // Should fail validation.
    $response->assertSessionHasErrors('scenario');
    $response->assertSessionHasErrors([
        'scenario' => 'The scenario cannot be changed after the first turn is recorded.',
    ]);

    // Original scenario unchanged.
    expect($run->fresh()->scenario)->toBe('ura_finale');
});

it('allows keeping same scenario via HTTP update after first turn', function (): void {
    $trainee = Umamusume::factory()->create();
    $run = TrainingRun::factory()->create([
        'umamusume_id' => $trainee->id,
        'scenario' => 'ura_finale',
    ]);

    // Record one turn.
    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 1,
    ]);

    // Re-post the same scenario (no change).
    $response = $this->put(route('runs.update', $run), [
        'umamusume_id' => $trainee->id,
        'scenario' => 'ura_finale',
        'status' => 'Active',
    ]);

    // Should succeed.
    $response->assertSessionHasNoErrors();
    expect($run->fresh()->scenario)->toBe('ura_finale');
});

it('exposes can_change_scenario in cockpit props', function (): void {
    $trainee = Umamusume::factory()->create();
    $run = TrainingRun::factory()->create([
        'umamusume_id' => $trainee->id,
        'scenario' => 'unity_cup',
    ]);

    // Before turn: can change.
    $this->get(route('runs.cockpit', $run))
        ->assertInertia(fn ($page) => $page
            ->component('Career/Cockpit')
            ->where('run.can_change_scenario', true)
        );

    // Record a turn.
    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 1,
    ]);

    // After turn: cannot change.
    $this->get(route('runs.cockpit', $run))
        ->assertInertia(fn ($page) => $page
            ->component('Career/Cockpit')
            ->where('run.can_change_scenario', false)
        );
});

it('snapshot run created with a scenario cannot change scenario after its first turn', function (): void {
    $trainee = Umamusume::factory()->create();

    // A complete snapshot writes one turn entry at the position the Trainer read. `CreateSnapshotRun`
    // is the writer, so the scenario is locked the moment the run exists with that entry.
    $run = app(CreateSnapshotRun::class)->handle([
        'umamusume_id' => $trainee->id,
        'scenario' => 'unity_cup',
        'career_year' => 3,
        'career_month' => 10,
        'career_phase' => 'Early',
        'speed' => 800,
        'stamina' => 900,
        'power' => 700,
        'guts' => 600,
        'wit' => 500,
        'field_states' => [
            'speed' => 'known',
            'stamina' => 'known',
            'power' => 'known',
            'guts' => 'known',
            'wit' => 'known',
        ],
    ]);

    expect($run->turnEntries()->count())->toBeGreaterThanOrEqual(1);
    expect($run->canChangeScenario())->toBeFalse();
});
