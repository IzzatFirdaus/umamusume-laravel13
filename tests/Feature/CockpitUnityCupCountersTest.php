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

    public function test_puts_the_combined_bursts_on_the_header_strip_when_both_tallies_are_recorded(): void
    {
        // KI-97: `ResourceStrip` maps the spirit_bursts widget to the `bursts` header value and captions it
        // "normal plus Extreme", so the combined total has to ride the header payload and not only the panel.
        $run = $this->unityCupRun([
            'spirit_bursts_count' => 6,
            'extreme_bursts_count' => 5,
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('header.values.bursts', 11));

        // Half a count is still not a count: with one tally unstated the strip keeps its absence.
        $partial = $this->unityCupRun(['spirit_bursts_count' => 6]);

        $this->get(route('runs.cockpit', $partial))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('header.values.bursts', null));
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

    public function test_the_band_block_names_its_carrier_and_its_low_band_caveat(): void
    {
        // The reference (conflict row 57) records GameTora printing overlapping first two bands -- "4 to 9"
        // and "7 to 9" -- and rules that "no tool should print a band the run cannot be placed in, which is
        // why config/scenarios.php renders the caveat rather than a placement". The panel carried a
        // provenance badge about publishing disagreement and an AlertRow about three conflicts, but neither
        // named the carrier of the figures nor stated the low-band overlap, so a reader could not tell
        // whose bands these are.
        $run = $this->unityCupRun([
            'spirit_bursts_count' => 6,
            'extreme_bursts_count' => 5,
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(function (Assert $page): void {
                $team = $page->toArray()['props']['scenario']['team'];

                // The payload carries the carrier, so the renderer never has to invent one.
                expect($team)->toHaveKey('bands_source');
                expect($team['bands_source'])->toContain('config');

                // And the low-band caveat, which is what makes the table a reference rather than a
                // placement. It names the overlap rather than saying "may differ".
                expect($team)->toHaveKey('bands_caveat');
                expect($team['bands_caveat'])->toContain('4');
                expect($team['bands_caveat'])->toContain('9');
            });
    }

    public function test_the_band_reward_names_the_stat_its_thirty_applies_to(): void
    {
        // Two unrelated +30 figures share this panel: the band's "+30 matching stat" and the team ranking
        // bonus's "All Attributes +30". The band's reward string said "matching stat", which is this
        // tool's paraphrase; the reward names the matching stat, and the label now says so, so the two
        // thirties cannot be read as one figure.
        $run = $this->unityCupRun([
            'spirit_bursts_count' => 6,
            'extreme_bursts_count' => 5,
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(function (Assert $page): void {
                $bands = $page->toArray()['props']['scenario']['team']['bands'];

                expect($bands)->not->toBeEmpty();

                foreach ($bands as $band) {
                    // The band's own +30 must name its target, and must not read as the league bonus.
                    if (str_contains((string) $band['reward'], '+30')) {
                        expect($band['reward'])->toContain('to the matching stat');
                        expect($band['reward'])->not->toContain('All Attributes');
                    }
                }
            });
    }
}
