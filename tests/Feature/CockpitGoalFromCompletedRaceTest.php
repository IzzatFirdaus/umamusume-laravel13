<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RunStatus;
use App\Enums\TraineeGoalType;
use App\Models\RaceCatalogSlot;
use App\Models\TraineeGoal;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * B-0.2 (R4-07): a completed race recorded through the run's own write path clears the trainee goal
 * it satisfies, because `TraineeGoal` derives progress from `race_entries` rather than storing a
 * goal-state column.
 *
 * The route already accepted `Completed` as a status; this pins the derivation the owner asked for —
 * record the race, and `Goals Achieved` moves — so the write path and the ladder are proven together.
 */
final class CockpitGoalFromCompletedRaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_recorded_completed_race_clears_a_placement_goal(): void
    {
        $trainee = Umamusume::factory()->create();
        $slot = RaceCatalogSlot::factory()->create(['title' => 'Nikkei Sho']);

        TraineeGoal::factory()->create([
            'umamusume_id' => $trainee->id,
            'race_catalog_slot_id' => $slot->id,
            'goal_type' => TraineeGoalType::Placement,
            'required_placement' => 1,
            'career_year' => $slot->year,
            'turn' => $slot->turn,
            'source_order' => 1,
        ]);

        $run = TrainingRun::factory()->create([
            'umamusume_id' => $trainee->id,
            'status' => RunStatus::Active,
            'scenario' => 'unity_cup',
        ]);

        // Nothing recorded yet: the trainee goals group reads 0 of 1.
        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('scenario.objectives.0.summary', 'Goals Achieved 0 of 1'));

        // Record the race as completed, first place, through the run's own race write path.
        $this->post(route('runs.races.store', $run), [
            'entry_mode' => 'calendar',
            'race_catalog_slot_id' => $slot->id,
            'status' => 'Completed',
            'placement' => 1,
            'turn_entry_id' => null,
        ])->assertRedirect();

        // The derivation reads the completed entry, so the goal is now met.
        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('scenario.objectives.0.summary', 'Goals Achieved 1 of 1'));
    }
}
