<?php

declare(strict_types=1);

use App\Actions\CreateSnapshotRun;
use App\Models\Advisor\BuildTargetPayload;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * The training decision plans against the run's imported position and stats.
 *
 * A snapshot at Speed 607 with a target of 1300 must state a Speed deficit of 693, whether the run
 * carries that reading as a turn entry (its writer recorded a complete set) or only on its stored
 * position (a partial set that could not form a turn row).
 */
function snapshotTargets(): array
{
    return [
        'purpose' => 'StoryClear',
        'distance' => 'Medium',
        'surface' => 'Turf',
        'style' => 'Pace Chaser',
        'targets' => ['Speed' => 1300, 'Stamina' => 500, 'Power' => 500, 'Guts' => 500, 'Wit' => 500],
        'skill_priorities' => [],
    ];
}

it('plans against the stats a snapshot stored on its position when it logged no turn', function (): void {
    $run = TrainingRun::factory()->create([
        'scenario' => 'unity_cup',
        'career_position' => [
            'year' => 3,
            'month' => 10,
            'phase' => 'Early',
            'turn_index' => 67,
            'field_values' => [
                'speed' => 607, 'stamina' => 500, 'power' => 500, 'guts' => 500, 'wit' => 500, 'energy' => 66,
            ],
        ],
        'build_target' => BuildTargetPayload::fromArray(snapshotTargets())->toArray(),
    ]);

    $this->get(route('runs.training', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/TrainingDetail')
            ->where('careerPosition.turn_index', 67)
            ->where('currentStats.Speed', 607)
            ->where('options.0.key', 'Speed')
            ->where('options.0.current', 607)
            ->where('options.0.target', 1300)
            ->where('options.0.deficit', 693));
});

it('plans against the turn entry a snapshot writer recorded', function (): void {
    $trainee = Umamusume::factory()->create();

    $run = (new CreateSnapshotRun)->handle([
        'umamusume_id' => $trainee->id,
        'scenario' => 'unity_cup',
        'career_year' => 3,
        'career_month' => 10,
        'career_phase' => 'Early',
        'speed' => 607, 'stamina' => 500, 'power' => 500, 'guts' => 500, 'wit' => 500,
        'energy' => 66, 'fans' => 12000, 'skill_points' => 40,
    ]);

    $run->update(['build_target' => BuildTargetPayload::fromArray(snapshotTargets())->toArray()]);

    $this->get(route('runs.training', $run->fresh()))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('options.0.deficit', 693)
            // The next turn the form writes is one past the imported position, not turn 1.
            ->where('write.turn', 68));
});
