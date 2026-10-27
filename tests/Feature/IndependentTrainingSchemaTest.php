<?php

declare(strict_types=1);

use App\Enums\IndependentTrainingPolicy;
use App\Models\IndependentTrainingRun;
use App\Models\TrainingRun;
use Illuminate\Support\Facades\Schema;

/*
 * UMAMUSUME_REFERENCE.md §1.1.7: the idle mode's schema and the run-level accessors.
 *
 * One `independent_training_runs` row overlays a `training_runs` row with the three inputs fixed at
 * start (policy, rotation, priority skills) and the 50-minute window. `isIndependent()` and
 * `isInProgress()` read that overlay so the cockpit, dashboard and preflight can branch on it.
 */

it('creates the independent training table with the 50-minute window columns', function (): void {
    expect(Schema::hasTable('independent_training_runs'))->toBeTrue();

    $columns = Schema::getColumnListing('independent_training_runs');

    expect($columns)
        ->toContain('training_run_id')
        ->toContain('policy')
        ->toContain('priority_skills')
        ->toContain('rotation')
        ->toContain('started_at')
        ->toContain('completes_at')
        ->toContain('completed_at')
        ->toContain('abandoned_at');
});

it('reads the policy as its enum and the run overlays through the relationship', function (): void {
    $run = TrainingRun::factory()->create();
    $itr = IndependentTrainingRun::factory()->for($run, 'trainingRun')->create([
        'policy' => IndependentTrainingPolicy::Stamina,
    ]);

    expect($itr->policy)->toBe(IndependentTrainingPolicy::Stamina)
        ->and($itr->policy->label())->toBe('Stamina focus')
        ->and($run->independentTrainingRun->is($itr))->toBeTrue()
        ->and($run->isIndependent())->toBeTrue()
        ->and($run->isInProgress())->toBeTrue();
});

it('is not independent and not in progress for a normal career run', function (): void {
    $run = TrainingRun::factory()->create();

    expect($run->independentTrainingRun)->toBeNull()
        ->and($run->isIndependent())->toBeFalse()
        ->and($run->isInProgress())->toBeFalse();
});

it('is not in progress once the window has ended', function (): void {
    $run = TrainingRun::factory()->create();
    IndependentTrainingRun::factory()->for($run, 'trainingRun')->expired()->create();

    expect($run->isIndependent())->toBeTrue()
        ->and($run->isInProgress())->toBeFalse();
});