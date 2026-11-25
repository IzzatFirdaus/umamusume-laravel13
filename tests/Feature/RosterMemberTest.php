<?php

declare(strict_types=1);

use App\Enums\RosterDistance;
use App\Models\DeckSlot;
use App\Models\RosterMember;
use App\Models\TrainingRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * D01: the roster write path — create, read, update, delete, and the constraints that bound it.
 *
 * `CockpitRosterTest` covers the read-out; this covers the write doors the Team Edit panel posts to.
 * Every case drives the real HTTP route and asserts the persisted row, not just that the route is
 * registered — a passing test here means a member the Trainer added is the member the database holds.
 *
 * The two invariants that matter most:
 *  - The 20-member ceiling is the client's own (§1.8 `Members: 20 / 20`), enforced at the model
 *    boundary so no writer slips past it, and surfaced to the form as a validation error.
 *  - A member added by name has no fabricated readings: every figure stays null (D-220).
 */
final class RosterMemberTest extends TestCase
{
    use RefreshDatabase;

    private function unityRun(): TrainingRun
    {
        return TrainingRun::factory()->create(['scenario' => 'unity_cup']);
    }

    public function test_store_persists_a_named_member_with_null_readings(): void
    {
        $run = $this->unityRun();

        $this->post(route('runs.roster.store', $run), [
            'name' => 'Haru Urara',
        ])
            ->assertRedirect(route('runs.team', $run))
            ->assertSessionHasNoErrors();

        $member = RosterMember::query()->where('training_run_id', $run->id)->firstOrFail();

        expect($member->name)->toBe('Haru Urara')
            ->and($member->speed)->toBeNull()
            // A cap is a reading like any other: a member the Trainer typed in has no ceiling
            // observed yet, and 0 here would say she can gain nothing (D-220).
            ->and($member->speed_cap)->toBeNull()
            ->and($member->wit_cap)->toBeNull()
            ->and($member->grade)->toBeNull()
            ->and($member->distance_assignment)->toBeNull()
            ->and($member->is_ace)->toBeFalse()
            ->and($member->is_trainee)->toBeFalse()
            ->and($member->position)->toBe(1);
    }

    public function test_store_persists_every_recorded_figure_and_aptitude(): void
    {
        $run = $this->unityRun();

        $this->post(route('runs.roster.store', $run), [
            'name' => 'Tokai Teio',
            'grade' => 'A',
            'speed' => 120,
            'stamina' => 110,
            'power' => 105,
            'guts' => 99,
            'wit' => 115,
            'speed_cap' => 1080,
            'stamina_cap' => 860,
            'power_cap' => 960,
            'guts_cap' => 880,
            'wit_cap' => 850,
            'distance_assignment' => 'Medium',
            'is_ace' => true,
        ])->assertSessionHasNoErrors();

        $member = RosterMember::query()->firstOrFail();

        expect($member->grade)->toBe('A')
            ->and($member->speed)->toBe(120)
            ->and($member->stamina)->toBe(110)
            ->and($member->power)->toBe(105)
            ->and($member->guts)->toBe(99)
            ->and($member->wit)->toBe(115)
            ->and($member->speed_cap)->toBe(1080)
            ->and($member->stamina_cap)->toBe(860)
            ->and($member->power_cap)->toBe(960)
            ->and($member->guts_cap)->toBe(880)
            ->and($member->wit_cap)->toBe(850)
            ->and($member->distance_assignment)->toBe(RosterDistance::Medium)
            ->and($member->is_ace)->toBeTrue();
    }

    public function test_update_changes_the_correct_member(): void
    {
        $run = $this->unityRun();
        $member = RosterMember::factory()->for($run)->create(['name' => 'Old Name']);

        $this->put(route('runs.roster.update', [$run, $member]), [
            'name' => 'New Name',
            'speed' => 200,
        ])->assertRedirect(route('runs.team', $run))->assertSessionHasNoErrors();

        expect($member->fresh()->name)->toBe('New Name')
            ->and($member->fresh()->speed)->toBe(200);
    }

    public function test_destroy_removes_the_intended_member(): void
    {
        $run = $this->unityRun();
        $keep = RosterMember::factory()->for($run)->create(['name' => 'Keep']);
        $drop = RosterMember::factory()->for($run)->create(['name' => 'Drop']);

        $this->delete(route('runs.roster.destroy', [$run, $drop]))
            ->assertRedirect(route('runs.team', $run))
            ->assertSessionHasNoErrors();

        expect(RosterMember::query()->whereKey($drop->id)->exists())->toBeFalse()
            ->and(RosterMember::query()->whereKey($keep->id)->exists())->toBeTrue();
    }

    public function test_the_twenty_first_member_is_refused_with_a_validation_error(): void
    {
        $run = $this->unityRun();

        RosterMember::factory()->count(RosterMember::MAX_MEMBERS)->for($run)->create();

        $this->post(route('runs.roster.store', $run), ['name' => 'One Too Many'])
            ->assertSessionHasErrors('name');

        expect(RosterMember::query()->where('training_run_id', $run->id)->count())
            ->toBe(RosterMember::MAX_MEMBERS);
    }

    public function test_a_second_ace_in_one_distance_column_is_refused(): void
    {
        $run = $this->unityRun();
        RosterMember::factory()->for($run)->ace()->inColumn(RosterDistance::Long)->create();

        $this->post(route('runs.roster.store', $run), [
            'name' => 'Rival Long ACE',
            'distance_assignment' => 'Long',
            'is_ace' => true,
        ])->assertSessionHasErrors('distance_assignment');
    }

    public function test_an_unknown_distance_is_rejected(): void
    {
        $run = $this->unityRun();

        $this->post(route('runs.roster.store', $run), [
            'name' => 'Bad Distance',
            'distance_assignment' => 'Sky',
        ])->assertSessionHasErrors('distance_assignment');
    }

    public function test_a_member_on_another_runs_roster_is_a_404(): void
    {
        $own = $this->unityRun();
        $other = $this->unityRun();
        $member = RosterMember::factory()->for($other)->create();

        // The route nests the member under $own, but the member belongs to $other.
        $this->put(route('runs.roster.update', [$own, $member]), ['name' => 'Hijack'])
            ->assertNotFound();

        $this->delete(route('runs.roster.destroy', [$own, $member]))->assertNotFound();

        expect($member->fresh()->name)->not->toBe('Hijack');
    }

    public function test_roster_operations_are_refused_on_a_scenario_without_a_team(): void
    {
        // `ura_finale` composes no `team_rank_ladder`, so it has no roster to write.
        $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

        $this->post(route('runs.roster.store', $run), ['name' => 'Out of Place'])
            ->assertStatus(422);
    }

    public function test_the_deck_is_not_turned_into_roster_members(): void
    {
        $run = $this->unityRun();

        // A deck equipped (one slot), but the roster still holds only what was posted.
        DeckSlot::factory()->for($run)->create();

        expect(RosterMember::query()->where('training_run_id', $run->id)->count())->toBe(0);
    }

    public function test_team_edit_carries_the_roster_contract_with_write_urls(): void
    {
        $run = $this->unityRun();
        $member = RosterMember::factory()->for($run)->create(['name' => 'Tokai Teio']);

        $this->get(route('runs.team', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Career/TeamEdit')
                ->has('roster.members', 1)
                ->where('roster.members.0.name', 'Tokai Teio')
                ->where('roster.members.0.update_url', route('runs.roster.update', [$run, $member]))
                ->where('roster.members.0.destroy_url', route('runs.roster.destroy', [$run, $member]))
                ->where('roster.store_url', route('runs.roster.store', $run))
                ->where('roster.max_members', RosterMember::MAX_MEMBERS)
                ->where('roster.unrecorded', false));
    }

    public function test_team_edit_reports_an_unrecorded_roster(): void
    {
        $run = $this->unityRun();

        $this->get(route('runs.team', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('roster.members', [])
                ->where('roster.unrecorded', true)
                ->where('roster.store_url', route('runs.roster.store', $run))
                ->where('roster.max_members', RosterMember::MAX_MEMBERS));
    }
}
