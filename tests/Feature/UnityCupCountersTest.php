<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The run's Unity Cup progression counters, stored on the run (A6.7 persistence).
 *
 * Three entered tallies: Unity Trainings run, Spirit Bursts and Extreme Spirit Bursts earned. Nothing
 * in this repository counts a training or a burst, so a stored run is the only place these come from,
 * and a run that stated none stores null rather than a default of zero. Only the lower bound is
 * enforced; the column's own unsigned range is the ceiling.
 *
 * Class-based with private helpers, matching `RunGrowthRateTest`: a top-level function in
 * `tests/Feature` shares one namespace, and a duplicate name fatals the whole suite at load time.
 */
final class UnityCupCountersTest extends TestCase
{
    use RefreshDatabase;

    public function test_persists_the_counters_the_form_submits(): void
    {
        $trainee = Umamusume::factory()->create();

        $this->post(route('runs.store'), [
            'umamusume_id' => $trainee->id,
            'status' => RunStatus::Active->value,
            'unity_trainings_count' => 55,
            'spirit_bursts_count' => 6,
            'extreme_bursts_count' => 5,
        ])->assertSessionHasNoErrors();

        $run = $this->latestRun();

        expect($run->unity_trainings_count)->toBe(55)
            ->and($run->spirit_bursts_count)->toBe(6)
            ->and($run->extreme_bursts_count)->toBe(5);
    }

    public function test_leaves_the_counters_unstated_when_the_form_carries_none(): void
    {
        $trainee = Umamusume::factory()->create();

        $this->post(route('runs.store'), [
            'umamusume_id' => $trainee->id,
            'status' => RunStatus::Active->value,
        ])->assertSessionHasNoErrors();

        // Absent is not zero: a run that never stated a tally stores null, which the cockpit render
        // shows as the N/A disclosure rather than a default.
        $run = $this->latestRun();

        expect($run->unity_trainings_count)->toBeNull()
            ->and($run->spirit_bursts_count)->toBeNull()
            ->and($run->extreme_bursts_count)->toBeNull();
    }

    public function test_refuses_a_negative_tally(): void
    {
        $trainee = Umamusume::factory()->create();

        foreach (['unity_trainings_count', 'spirit_bursts_count', 'extreme_bursts_count'] as $field) {
            $this->post(route('runs.store'), [
                'umamusume_id' => $trainee->id,
                'status' => RunStatus::Active->value,
                $field => -1,
            ])->assertSessionHasErrors($field);
        }

        expect(TrainingRun::query()->count())->toBe(0);
    }

    public function test_persists_a_later_edit_through_the_run_update_write(): void
    {
        $trainee = Umamusume::factory()->create();
        $run = TrainingRun::factory()->create([
            'umamusume_id' => $trainee->id,
            'status' => RunStatus::Active,
        ]);

        $this->put(route('runs.update', $run), [
            'umamusume_id' => $trainee->id,
            'status' => RunStatus::Active->value,
            'unity_trainings_count' => 41,
            'spirit_bursts_count' => 3,
            'extreme_bursts_count' => 1,
        ])->assertSessionHasNoErrors();

        expect($run->refresh()->unity_trainings_count)->toBe(41)
            ->and($run->spirit_bursts_count)->toBe(3)
            ->and($run->extreme_bursts_count)->toBe(1);
    }

    private function latestRun(): TrainingRun
    {
        return TrainingRun::query()->latest('id')->firstOrFail();
    }
}
