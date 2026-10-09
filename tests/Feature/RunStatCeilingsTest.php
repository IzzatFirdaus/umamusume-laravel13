<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The run's own stat ceilings, stored on the run (A6.4-p persistence).
 *
 * A JSON bag keyed by the stat matrix, each entry a nullable ceiling. Entered, never derived:
 * `ScenarioCaps` owns the scenario's ceiling, but the run's real caps sit above it once the card's
 * sparks and any support-card Max-Stat layer are counted, and this repository holds neither input.
 * So a stored bag is the only place the composed ceiling comes from, and a run that stated none
 * stores null rather than an invented row of zeroes. Keys are restricted to the matrix; the value is
 * the Trainer's own arithmetic, so only the lower bound is enforced.
 *
 * Class-based with private helpers, matching `RunGrowthRateTest`: a top-level function in
 * `tests/Feature` shares one namespace, and a duplicate name fatals the whole suite at load time.
 */
final class RunStatCeilingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_persists_the_ceilings_the_form_submits(): void
    {
        $trainee = Umamusume::factory()->create();

        $this->post(route('runs.store'), [
            'umamusume_id' => $trainee->id,
            'status' => RunStatus::Active->value,
            'stat_ceilings' => ['Speed' => 1400, 'Stamina' => 1200, 'Power' => 1000, 'Guts' => 800, 'Wit' => 1100],
        ])->assertSessionHasNoErrors();

        expect($this->latestRun()->stat_ceilings)->toBe([
            'Speed' => 1400, 'Stamina' => 1200, 'Power' => 1000, 'Guts' => 800, 'Wit' => 1100,
        ]);
    }

    public function test_leaves_the_ceilings_unstated_when_the_form_carries_none(): void
    {
        $trainee = Umamusume::factory()->create();

        $this->post(route('runs.store'), [
            'umamusume_id' => $trainee->id,
            'status' => RunStatus::Active->value,
        ])->assertSessionHasNoErrors();

        // Absent is not a row of zeroes: a run that never stated the ceilings stores null, which the
        // cockpit render shows as the N/A disclosure rather than a default.
        expect($this->latestRun()->stat_ceilings)->toBeNull();
    }

    public function test_refuses_a_negative_ceiling(): void
    {
        $trainee = Umamusume::factory()->create();

        $this->post(route('runs.store'), [
            'umamusume_id' => $trainee->id,
            'status' => RunStatus::Active->value,
            'stat_ceilings' => ['Speed' => -1],
        ])->assertSessionHasErrors('stat_ceilings.Speed');

        expect(TrainingRun::query()->count())->toBe(0);
    }

    public function test_refuses_a_stat_outside_the_matrix(): void
    {
        $trainee = Umamusume::factory()->create();

        // A sixth stat has no home in the read path, so the bag refuses it at the boundary rather
        // than storing a key the render would silently drop.
        $this->post(route('runs.store'), [
            'umamusume_id' => $trainee->id,
            'status' => RunStatus::Active->value,
            'stat_ceilings' => ['Speed' => 1400, 'Unknown' => 1400],
        ])->assertSessionHasErrors('stat_ceilings');

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
            'stat_ceilings' => ['Wit' => 1300],
        ])->assertSessionHasNoErrors();

        expect($run->refresh()->stat_ceilings)->toBe(['Wit' => 1300]);
    }

    private function latestRun(): TrainingRun
    {
        return TrainingRun::query()->latest('id')->firstOrFail();
    }
}
