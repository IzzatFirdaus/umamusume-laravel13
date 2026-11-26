<?php

use App\Enums\RunStatus;
use App\Models\RosterMember;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/*
 * D-220 observed-member-count acceptance.
 *
 * `observed_member_count` is the Trainer's own reading of the Unity Cup panel, stored per run.
 * It is independent of the `roster` rows: twenty observed beside eighteen recorded is an honest
 * gap the panel names rather than collapses. Null means unrecorded, never inferred from rows.
 * Zero means "this turn". The scenario gate replaces any rotation: only a `team_race` scenario
 * has a team to observe.
 */
final class ObservedMemberCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_observed_count_of_twenty_beside_eighteen_roster_rows_is_independent(): void
    {
        $run = TrainingRun::factory()->create([
            'scenario' => 'unity_cup',
            'status' => RunStatus::Active,
            'observed_member_count' => 20,
        ]);

        // Record eighteen roster members; they must not alter the observed count.
        foreach (range(1, 18) as $n) {
            RosterMember::factory()->create([
                'training_run_id' => $run->id,
                'name' => 'Roster Member '.$n,
                'position' => $n,
            ]);
        }

        expect($run->fresh()->rosterMembers)->toHaveCount(18)
            ->and($run->fresh()->observed_member_count)->toBe(20);

        $this->get(route('runs.team', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Career/TeamEdit')
                ->where('team.observed_member_count', 20));
    }

    public function test_null_observed_member_count_is_unrecorded_not_zero(): void
    {
        $run = TrainingRun::factory()->create([
            'scenario' => 'unity_cup',
            'status' => RunStatus::Active,
        ]);

        expect($run->observed_member_count)->toBeNull();

        $this->get(route('runs.team', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Career/TeamEdit')
                ->where('team.observed_member_count', null));
    }

    public function test_zero_observed_member_count_is_a_reading_not_an_absence(): void
    {
        $run = TrainingRun::factory()->create([
            'scenario' => 'unity_cup',
            'status' => RunStatus::Active,
            'observed_member_count' => 0,
        ]);

        $this->get(route('runs.team', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Career/TeamEdit')
                ->where('team.observed_member_count', 0));

        $this->assertDatabaseHas('training_runs', [
            'id' => $run->id,
            'observed_member_count' => 0,
        ]);
    }

    public function test_observed_member_count_persists_through_unrelated_partial_update(): void
    {
        $run = TrainingRun::factory()->create([
            'scenario' => 'unity_cup',
            'status' => RunStatus::Active,
            'observed_member_count' => 20,
            'notes' => 'original notes',
        ]);

        $this->put(route('runs.update', $run), [
            'umamusume_id' => $run->umamusume_id,
            'scenario' => 'unity_cup',
            'status' => 'Active',
            'notes' => 'updated notes',
        ])->assertSessionHasNoErrors();

        expect($run->fresh()->observed_member_count)->toBe(20)
            ->and($run->fresh()->notes)->toBe('updated notes');
    }

    public function test_roster_crud_does_not_modify_observed_member_count(): void
    {
        $run = TrainingRun::factory()->create([
            'scenario' => 'unity_cup',
            'status' => RunStatus::Active,
            'observed_member_count' => 20,
        ]);

        $member = RosterMember::factory()->create([
            'training_run_id' => $run->id,
            'name' => 'Added Member',
            'position' => 1,
        ]);

        $this->assertDatabaseHas('training_runs', [
            'id' => $run->id,
            'observed_member_count' => 20,
        ]);

        // Update the member — observed count must stay untouched.
        $this->put(route('runs.roster.update', [$run, $member]), [
            'name' => 'Renamed Member',
        ])->assertSessionHasNoErrors();

        expect($run->fresh()->observed_member_count)->toBe(20);

        // Remove the member — observed count must stay untouched.
        $this->delete(route('runs.roster.destroy', [$run, $member]))
            ->assertSessionHasNoErrors();

        expect($run->fresh()->observed_member_count)->toBe(20);
    }

    public function test_scenario_without_team_race_refuses_observed_member_count(): void
    {
        $run = TrainingRun::factory()->create([
            'scenario' => 'ura_finale',
            'status' => RunStatus::Active,
        ]);

        $this->put(route('runs.update', $run), [
            'umamusume_id' => $run->umamusume_id,
            'scenario' => 'ura_finale',
            'status' => 'Active',
            'observed_member_count' => 20,
        ])->assertSessionHasErrors('observed_member_count');
    }

    public function test_negative_observed_member_count_is_rejected(): void
    {
        $run = TrainingRun::factory()->create([
            'scenario' => 'unity_cup',
            'status' => RunStatus::Active,
        ]);

        $this->put(route('runs.update', $run), [
            'umamusume_id' => $run->umamusume_id,
            'scenario' => 'unity_cup',
            'status' => 'Active',
            'observed_member_count' => -5,
        ])->assertSessionHasErrors('observed_member_count');
    }

    public function test_non_integer_observed_member_count_is_rejected(): void
    {
        $run = TrainingRun::factory()->create([
            'scenario' => 'unity_cup',
            'status' => RunStatus::Active,
        ]);

        $this->put(route('runs.update', $run), [
            'umamusume_id' => $run->umamusume_id,
            'scenario' => 'unity_cup',
            'status' => 'Active',
            'observed_member_count' => 'twenty',
        ])->assertSessionHasErrors('observed_member_count');
    }

    public function test_update_clears_observed_member_count_when_switching_to_no_team_scenario(): void
    {
        $run = TrainingRun::factory()->create([
            'scenario' => 'unity_cup',
            'status' => RunStatus::Active,
            'observed_member_count' => 20,
        ]);

        $this->put(route('runs.update', $run), [
            'umamusume_id' => $run->umamusume_id,
            'scenario' => 'ura_finale',
            'status' => 'Active',
        ])->assertSessionHasNoErrors();

        expect($run->fresh()->observed_member_count)->toBeNull();
    }

    public function test_observed_member_count_is_null_on_non_team_scenario_panel(): void
    {
        $run = TrainingRun::factory()->create([
            'scenario' => 'ura_finale',
            'status' => RunStatus::Active,
            'observed_member_count' => null,
        ]);

        // `ura_finale` composes no `team_race` panel, so the team panel reports null.
        $this->get(route('runs.team', $run))
            ->assertRedirect(route('runs.cockpit', $run));

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Career/Cockpit')
                ->where('team_race_countdown', null));
    }
}
