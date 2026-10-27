<?php

declare(strict_types=1);

use App\Enums\IndependentTrainingPolicy;
use App\Models\IndependentTrainingRun;
use App\Models\TrainingRun;
use App\Services\IndependentTraining\Lifecycle;
use App\Services\IndependentTraining\Simulator;

/*
 * UMAMUSUME_REFERENCE.md §1.1.7: the completion simulation. The client does not publish the
 * per-turn algorithm, so the simulator is a calibrated approximation matching the mode's output
 * classes; two rules are pinned because the reference states them — growth rate is excluded from
 * stat gains, and the priority list (capped at ten) is exactly the hint set.
 */

it('excludes growth rate from stat gains', function (): void {
    $sim = new Simulator;

    $low = TrainingRun::factory()->create(['growth_rate' => ['Speed' => 0, 'Stamina' => 0, 'Power' => 0, 'Guts' => 0, 'Wit' => 0]]);
    $high = TrainingRun::factory()->create(['growth_rate' => ['Speed' => 20, 'Stamina' => 20, 'Power' => 20, 'Guts' => 20, 'Wit' => 20]]);

    $lowItr = IndependentTrainingRun::factory()->for($low, 'trainingRun')->create(['policy' => IndependentTrainingPolicy::Balanced]);
    $highItr = IndependentTrainingRun::factory()->for($high, 'trainingRun')->create(['policy' => IndependentTrainingPolicy::Balanced]);

    expect($sim->run($lowItr)['stat_gains'])->toBe($sim->run($highItr)['stat_gains']);
});

it('pays fans in the ~1.3M range and one sash', function (): void {
    $run = TrainingRun::factory()->create();
    $itr = IndependentTrainingRun::factory()->for($run, 'trainingRun')->create();

    $result = (new Simulator)->run($itr);

    expect($result['fans'])->toBeGreaterThanOrEqual(1_000_000)
        ->and($result['fans'])->toBeLessThanOrEqual(1_600_000)
        ->and($result['sashes'])->toBe(1);
});

it('returns exactly the priority skills as hints, none off the list', function (): void {
    $run = TrainingRun::factory()->create();
    $itr = IndependentTrainingRun::factory()->for($run, 'trainingRun')->create(['priority_skills' => [11, 22, 33]]);

    expect((new Simulator)->run($itr)['skill_hints'])->toBe([11, 22, 33]);
});

it('is reproducible for the same inputs', function (): void {
    $run = TrainingRun::factory()->create();
    $itr = IndependentTrainingRun::factory()->for($run, 'trainingRun')->create(['policy' => IndependentTrainingPolicy::Sprint]);

    $sim = new Simulator;

    expect($sim->run($itr))->toBe($sim->run($itr));
});

it('wires the simulation into complete() as the stored result', function (): void {
    $lifecycle = new Lifecycle;
    $run = TrainingRun::factory()->create();
    $itr = $lifecycle->start($run, ['policy' => 'balanced', 'priority_skills' => [7, 8]]);

    $lifecycle->complete($itr);

    $result = $itr->fresh()->result;

    expect($result)->not->toBeNull()
        ->and($result['fans'])->toBe(Simulator::FANS)
        ->and($result['skill_hints'])->toBe([7, 8])
        ->and($result['stat_gains'])->toBe(['Speed' => 100, 'Stamina' => 100, 'Power' => 100, 'Guts' => 100, 'Wit' => 100]);
});
