<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The run's team identity under Unity Cup, stored on the run (A6.6 persistence).
 *
 * Four entered columns: the team's name and motto, its league placement, and the preseason rounds won.
 * No table in this repository holds a team, so a stored run is the only place these come from, and a
 * run that stated none stores null rather than an invented default. Placement is a rank, so zero is
 * refused; the preseason tally is bounded to the four rounds.
 *
 * Class-based with private helpers, matching `RunGrowthRateTest`: a top-level function in
 * `tests/Feature` shares one namespace, and a duplicate name fatals the whole suite at load time.
 */
final class RunTeamStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_persists_the_team_identity_the_form_submits(): void
    {
        $trainee = Umamusume::factory()->create();

        $this->post(route('runs.store'), [
            'umamusume_id' => $trainee->id,
            'status' => RunStatus::Active->value,
            'team_name' => 'Blue Bloom',
            'team_motto' => 'Dreaming Big',
            'team_league_placement' => 8,
            'team_preseason_wins' => 4,
        ])->assertSessionHasNoErrors();

        $run = $this->latestRun();

        expect($run->team_name)->toBe('Blue Bloom')
            ->and($run->team_motto)->toBe('Dreaming Big')
            ->and($run->team_league_placement)->toBe(8)
            ->and($run->team_preseason_wins)->toBe(4);
    }

    public function test_leaves_the_identity_unstated_when_the_form_carries_none(): void
    {
        $trainee = Umamusume::factory()->create();

        $this->post(route('runs.store'), [
            'umamusume_id' => $trainee->id,
            'status' => RunStatus::Active->value,
        ])->assertSessionHasNoErrors();

        // Absent is not a default: a run that never stated the team stores null on all four, which
        // the cockpit render shows as the N/A disclosure.
        $run = $this->latestRun();

        expect($run->team_name)->toBeNull()
            ->and($run->team_motto)->toBeNull()
            ->and($run->team_league_placement)->toBeNull()
            ->and($run->team_preseason_wins)->toBeNull();
    }

    public function test_refuses_a_placement_that_is_not_a_rank(): void
    {
        $trainee = Umamusume::factory()->create();

        foreach ([0, 100] as $placement) {
            $this->post(route('runs.store'), [
                'umamusume_id' => $trainee->id,
                'status' => RunStatus::Active->value,
                'team_league_placement' => $placement,
            ])->assertSessionHasErrors('team_league_placement');
        }

        expect(TrainingRun::query()->count())->toBe(0);
    }

    public function test_refuses_a_preseason_tally_outside_the_four_rounds(): void
    {
        $trainee = Umamusume::factory()->create();

        foreach ([-1, 5] as $wins) {
            $this->post(route('runs.store'), [
                'umamusume_id' => $trainee->id,
                'status' => RunStatus::Active->value,
                'team_preseason_wins' => $wins,
            ])->assertSessionHasErrors('team_preseason_wins');
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
            'team_name' => 'Blue Bloom',
            'team_league_placement' => 3,
            'team_preseason_wins' => 2,
        ])->assertSessionHasNoErrors();

        expect($run->refresh()->team_name)->toBe('Blue Bloom')
            ->and($run->team_league_placement)->toBe(3)
            ->and($run->team_preseason_wins)->toBe(2);
    }

    private function latestRun(): TrainingRun
    {
        return TrainingRun::query()->latest('id')->firstOrFail();
    }
}
