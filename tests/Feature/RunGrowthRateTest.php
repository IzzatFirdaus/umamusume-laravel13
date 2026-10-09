<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The trainee's growth-rate row, stored on the run (A6.3 persistence).
 *
 * A JSON bag keyed by the stat matrix, each entry a nullable percentage. Entered, never derived: no
 * catalogue holds a growth figure, so a stored bag is the only place this number comes from, and a
 * run that stated none stores null rather than an invented row of zeroes. The range is enforced at
 * the boundary, and the keys are restricted to the matrix so a hand-made POST cannot store a sixth
 * stat the read path would have no home for.
 *
 * Class-based with private helpers, matching `TraineeRarityPotentialTest`: a top-level function in
 * `tests/Feature` shares one namespace, and a duplicate name fatals the whole suite at load time.
 */
final class RunGrowthRateTest extends TestCase
{
    use RefreshDatabase;

    public function test_persists_the_growth_row_the_form_submits(): void
    {
        $trainee = Umamusume::factory()->create();

        $this->post(route('runs.store'), [
            'umamusume_id' => $trainee->id,
            'status' => RunStatus::Active->value,
            // The Rice Shower row the UX walk names: +0/+10/+0/+20/+0 across Speed..Wit.
            'growth_rate' => ['Speed' => 0, 'Stamina' => 10, 'Power' => 0, 'Guts' => 20, 'Wit' => 0],
        ])->assertSessionHasNoErrors();

        expect($this->latestRun()->growth_rate)->toBe([
            'Speed' => 0, 'Stamina' => 10, 'Power' => 0, 'Guts' => 20, 'Wit' => 0,
        ]);
    }

    public function test_leaves_the_row_unstated_when_the_form_carries_none(): void
    {
        $trainee = Umamusume::factory()->create();

        $this->post(route('runs.store'), [
            'umamusume_id' => $trainee->id,
            'status' => RunStatus::Active->value,
        ])->assertSessionHasNoErrors();

        // Absent is not a row of zeroes: a run that never stated the row stores null, which the
        // cockpit render shows as the N/A disclosure rather than a default.
        expect($this->latestRun()->growth_rate)->toBeNull();
    }

    public function test_refuses_a_growth_outside_the_zero_to_thirty_the_source_uses(): void
    {
        $trainee = Umamusume::factory()->create();

        foreach ([-1, 31] as $growth) {
            $this->post(route('runs.store'), [
                'umamusume_id' => $trainee->id,
                'status' => RunStatus::Active->value,
                'growth_rate' => ['Speed' => $growth],
            ])->assertSessionHasErrors('growth_rate.Speed');
        }

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
            'growth_rate' => ['Speed' => 10, 'Unknown' => 10],
        ])->assertSessionHasErrors('growth_rate');

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
            'growth_rate' => ['Power' => 15],
        ])->assertSessionHasNoErrors();

        expect($run->refresh()->growth_rate)->toBe(['Power' => 15]);
    }

    private function latestRun(): TrainingRun
    {
        return TrainingRun::query()->latest('id')->firstOrFail();
    }
}
