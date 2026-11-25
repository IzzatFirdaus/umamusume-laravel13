<?php

declare(strict_types=1);

use App\Models\RosterMember;
use App\Models\TrainingRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * D02: the manually recorded Team Rank is independent of the roster.
 *
 * ADR-0034 makes `team_rank` the Trainer's own recorded league letter — not a value the app derives.
 * D01 added a second write surface (the roster) beside the team page, so the risk is that a roster
 * edit resets or recomputes the rank as a side effect. These cases pin the opposite: across add,
 * edit, and remove, a recorded letter — including `S+` and the unknown `null` — survives untouched.
 *
 * The proof is behavioural: a real HTTP roster write against a run whose `team_rank` is set, then a
 * re-read of the stored column. `$fillable` and request-rule inspection back this up but do not
 * replace it.
 */
final class RosterDoesNotTouchTeamRankTest extends TestCase
{
    use RefreshDatabase;

    private function rankedRun(?string $rank): TrainingRun
    {
        return TrainingRun::factory()->create([
            'scenario' => 'unity_cup',
            'team_rank' => $rank,
        ]);
    }

    public function test_adding_a_member_leaves_the_recorded_rank_alone(): void
    {
        $run = $this->rankedRun('A');

        $this->post(route('runs.roster.store', $run), ['name' => 'Haru Urara'])
            ->assertSessionHasNoErrors();

        expect($run->fresh()->team_rank)->toBe('A');
    }

    public function test_editing_a_member_leaves_the_recorded_rank_alone(): void
    {
        $run = $this->rankedRun('S+');
        $member = RosterMember::factory()->for($run)->create();

        $this->put(route('runs.roster.update', [$run, $member]), [
            'name' => 'Renamed',
            'speed' => 150,
        ])->assertSessionHasNoErrors();

        expect($run->fresh()->team_rank)->toBe('S+');
    }

    public function test_removing_a_member_leaves_the_recorded_rank_alone(): void
    {
        $run = $this->rankedRun('B');
        $member = RosterMember::factory()->for($run)->create();

        $this->delete(route('runs.roster.destroy', [$run, $member]))
            ->assertSessionHasNoErrors();

        expect($run->fresh()->team_rank)->toBe('B');
    }

    public function test_an_unrecorded_rank_stays_unknown_through_roster_writes(): void
    {
        // null is the unknown, never a zero or a derived floor — a roster write must not invent one.
        $run = $this->rankedRun(null);

        $this->post(route('runs.roster.store', $run), ['name' => 'Guest'])
            ->assertSessionHasNoErrors();

        expect($run->fresh()->team_rank)->toBeNull();
    }

    public function test_a_full_roster_edit_preserves_the_rank(): void
    {
        $run = $this->rankedRun('S');
        $member = RosterMember::factory()->for($run)->create(['name' => 'Before']);

        // A realistic edit: rename, reassign, and crown.
        $this->put(route('runs.roster.update', [$run, $member]), [
            'name' => 'After',
            'distance_assignment' => 'Mile',
            'is_ace' => true,
            'grade' => 'S',
        ])->assertSessionHasNoErrors();

        expect($run->fresh()->team_rank)->toBe('S');
    }
}
