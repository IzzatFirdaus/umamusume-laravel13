<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * The Unity Cup progression counters on the Cockpit's Team panel (A6.7 render): the Unity Trainings
 * tally, the Spirit and Extreme Spirit Burst tallies, and the combined total the burst-count band
 * table reads to place the run.
 *
 * The rendered copy is asserted in the browser spec; this file proves the wire the renderer reads. The
 * combined total is drawn only when both burst tallies are recorded, because an unrecorded tally is
 * not a zero (D-220): a run that stated one of the two gets no combined figure and no band rather than
 * half a count. The band is the label of the config row the total falls in, so the panel marks the row
 * it already renders.
 *
 * Class-based with private helpers, matching `RunGrowthRateTest`: a top-level function in
 * `tests/Feature` shares one namespace, and a duplicate name fatals the whole suite at load time.
 */
final class CockpitUnityCupCountersTest extends TestCase
{
    use RefreshDatabase;

    public function test_reads_the_counters_and_places_the_run_in_its_combined_band(): void
    {
        $run = $this->unityCupRun([
            'unity_trainings_count' => 55,
            'spirit_bursts_count' => 6,
            'extreme_bursts_count' => 5,
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('scenario.team.unity_trainings_count', 55)
                ->where('scenario.team.spirit_bursts_count', 6)
                ->where('scenario.team.extreme_bursts_count', 5)
                // 6 Spirit + 5 Extreme = 11, inside the config's 10–12 gold band.
                ->where('scenario.team.combined_bursts', 11)
                ->where('scenario.team.burst_band', '10–12'));
    }

    public function test_reads_the_counters_as_absent_when_not_recorded(): void
    {
        $run = $this->unityCupRun();

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('scenario.team.unity_trainings_count', null)
                ->where('scenario.team.spirit_bursts_count', null)
                ->where('scenario.team.extreme_bursts_count', null)
                ->where('scenario.team.combined_bursts', null)
                ->where('scenario.team.burst_band', null));
    }

    public function test_places_no_band_when_only_one_burst_tally_is_recorded(): void
    {
        // Half a count is not a count: with the Extreme tally unstated, the combined total and the band
        // are both null, so the panel keeps its absence rather than treating the missing tally as zero.
        $run = $this->unityCupRun([
            'unity_trainings_count' => 55,
            'spirit_bursts_count' => 6,
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('scenario.team.unity_trainings_count', 55)
                ->where('scenario.team.spirit_bursts_count', 6)
                ->where('scenario.team.extreme_bursts_count', null)
                ->where('scenario.team.combined_bursts', null)
                ->where('scenario.team.burst_band', null));
    }

    /**
     * R2-14: the Cockpit's team editor writes the counters the panel already printed.
     *
     * The read half is pinned above; these two pin the write half, through the same `runs.update` route
     * and `StoreTrainingRunRequest` the header forms use.
     */
    public function test_records_the_counters_through_the_cockpit_and_reads_them_back(): void
    {
        $run = $this->unityCupRun();

        $this->put(route('runs.update', $run), [
            'umamusume_id' => $run->umamusume_id,
            'status' => RunStatus::Active->value,
            'unity_trainings_count' => 55,
            'spirit_bursts_count' => 6,
            'extreme_bursts_count' => 5,
        ])->assertRedirect(route('runs.cockpit', $run));

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('scenario.team.unity_trainings_count', 55)
                ->where('scenario.team.spirit_bursts_count', 6)
                ->where('scenario.team.extreme_bursts_count', 5)
                ->where('scenario.team.combined_bursts', 11)
                ->where('scenario.team.burst_band', '10–12'));
    }

    public function test_keeps_a_zero_counter_apart_from_an_unstated_one(): void
    {
        $run = $this->unityCupRun();

        // `0` is a tally the Trainer read and a blank was never stated, so the two must not collapse:
        // the combined band is drawn from the two burst tallies, and a stored zero would place the run.
        $this->put(route('runs.update', $run), [
            'umamusume_id' => $run->umamusume_id,
            'status' => RunStatus::Active->value,
            'unity_trainings_count' => 0,
            'spirit_bursts_count' => 0,
            'extreme_bursts_count' => null,
        ])->assertSessionHasNoErrors();

        $fresh = $run->fresh();

        expect($fresh->unity_trainings_count)->toBe(0)
            ->and($fresh->spirit_bursts_count)->toBe(0)
            ->and($fresh->extreme_bursts_count)->toBeNull();

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('scenario.team.combined_bursts', null)
                ->where('scenario.team.burst_band', null));
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function unityCupRun(array $state = []): TrainingRun
    {
        return TrainingRun::factory()->create([
            'scenario' => 'unity_cup',
            'status' => RunStatus::Active,
            'umamusume_id' => Umamusume::factory()->create()->id,
            ...$state,
        ]);
    }
}
