<?php

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/*
 * D03: the recurring team-race countdown contract.
 *
 * `team_race_countdown` is record-only: the Trainer's observed value is stored,
 * null is "not observed" (not zero), and zero means "this turn". No published
 * rotation exists to bound it against, so no upper bound is asserted anywhere.
 * The scenario gate replaces the shop's "no rotation" refusal: a scenario that
 * does not compose `team_race` has no countdown to record.
 *
 * `finals_countdown` rides the advisor's `finaleContext()` (`turns_away`) and is
 * a different fact from the team-race countdown — a calendar-month event vs the
 * 72-turn grid's end. They must never be conflated.
 */

final class TeamRaceCountdownTest extends TestCase
{
    use RefreshDatabase;

    public function test_known_team_race_countdown_persists_and_reads_on_cockpit(): void
    {
        $run = TrainingRun::factory()->create([
            'scenario' => 'unity_cup',
            'status' => RunStatus::Active,
            'team_race_countdown' => 3,
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Career/Cockpit')
                ->where('team_race_countdown', 3));

        $this->assertDatabaseHas('training_runs', [
            'id' => $run->id,
            'team_race_countdown' => 3,
        ]);
    }

    public function test_zero_team_race_countdown_is_a_reading_not_an_absence(): void
    {
        $run = TrainingRun::factory()->create([
            'scenario' => 'unity_cup',
            'status' => RunStatus::Active,
            'team_race_countdown' => 0,
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Career/Cockpit')
                ->where('team_race_countdown', 0));
    }

    public function test_null_team_race_countdown_is_unknown_not_zero(): void
    {
        $run = TrainingRun::factory()->create([
            'scenario' => 'unity_cup',
            'status' => RunStatus::Active,
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Career/Cockpit')
                ->where('team_race_countdown', null));
    }

    public function test_scenario_without_team_race_refuses_the_countdown(): void
    {
        // `ura_finale` composes no `team_race` panel.
        $run = TrainingRun::factory()->create([
            'scenario' => 'ura_finale',
            'status' => RunStatus::Active,
        ]);

        $this->put(route('runs.update', $run), [
            'umamusume_id' => $run->umamusume_id,
            'scenario' => 'ura_finale',
            'status' => 'Active',
            'team_race_countdown' => 5,
        ])->assertSessionHasErrors('team_race_countdown');
    }

    public function test_negative_team_race_countdown_is_rejected(): void
    {
        $run = TrainingRun::factory()->create([
            'scenario' => 'unity_cup',
            'status' => RunStatus::Active,
        ]);

        $this->put(route('runs.update', $run), [
            'umamusume_id' => $run->umamusume_id,
            'scenario' => 'unity_cup',
            'status' => 'Active',
            'team_race_countdown' => -1,
        ])->assertSessionHasErrors('team_race_countdown');
    }

    public function test_non_integer_team_race_countdown_is_rejected(): void
    {
        $run = TrainingRun::factory()->create([
            'scenario' => 'unity_cup',
            'status' => RunStatus::Active,
        ]);

        $this->put(route('runs.update', $run), [
            'umamusume_id' => $run->umamusume_id,
            'scenario' => 'unity_cup',
            'status' => 'Active',
            'team_race_countdown' => 'not-a-number',
        ])->assertSessionHasErrors('team_race_countdown');
    }

    public function test_finals_countdown_is_distinct_from_team_race_countdown(): void
    {
        $run = TrainingRun::factory()->create([
            'scenario' => 'unity_cup',
            'status' => RunStatus::Active,
            'team_race_countdown' => 7,
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Career/Cockpit')
                ->where('team_race_countdown', 7)
                ->where('finals_countdown', null)
                ->where('scenarioCountdown', null));
    }

    public function test_spirit_burst_countdown_unaffected_by_team_race_countdown(): void
    {
        $run = TrainingRun::factory()->create([
            'scenario' => 'unity_cup',
            'status' => RunStatus::Active,
            'team_race_countdown' => 4,
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Career/Cockpit')
                ->where('team_race_countdown', 4)
                ->where('scenario.team.burst_countdown', null));
    }

    public function test_team_race_countdown_is_null_on_non_team_scenario_cockpit(): void
    {
        $run = TrainingRun::factory()->create([
            'scenario' => 'ura_finale',
            'status' => RunStatus::Active,
            'team_race_countdown' => null,
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Career/Cockpit')
                ->where('team_race_countdown', null));
    }

    public function test_update_clears_team_race_countdown_when_switching_to_no_team_scenario(): void
    {
        $run = TrainingRun::factory()->create([
            'scenario' => 'unity_cup',
            'status' => RunStatus::Active,
            'team_race_countdown' => 6,
        ]);

        $this->put(route('runs.update', $run), [
            'umamusume_id' => $run->umamusume_id,
            'scenario' => 'ura_finale',
            'status' => 'Active',
        ])->assertSessionHasNoErrors();

        expect($run->fresh()->team_race_countdown)->toBeNull();
    }
}
