<?php

declare(strict_types=1);

use App\Actions\CreateSnapshotRun;
use App\Enums\RunMode;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;

/**
 * The snapshot's write path. The two shapes a Trainer arrives with: a full reading of their client,
 * and the minimum a career can be entered with.
 *
 * The current state rides the run's turn log - the entry is at the position's own turn - so the run
 * needs no new read path, and its next turn is the one after the position.
 */
function snapshotPayload(array $overrides = []): array
{
    return $overrides + [
        'umamusume_id' => Umamusume::factory()->create()->id,
        'scenario' => 'unity_cup',
        'career_year' => 3,
        'career_month' => 10,
        'career_phase' => 'Early',
        'speed' => 607,
        'stamina' => 500,
        'power' => 500,
        'guts' => 500,
        'wit' => 500,
        'energy' => 66,
        'fans' => 12000,
        'skill_points' => 40,
    ];
}

it('records a full snapshot at the position it names, with the state the client shows', function (): void {
    $run = (new CreateSnapshotRun)->handle(snapshotPayload());

    expect($run->mode)->toBe(RunMode::Snapshot)
        ->and($run->isSnapshot())->toBeTrue()
        ->and($run->hasImportedPosition())->toBeTrue()
        ->and($run->careerPosition()->turnIndex)->toBe(67)
        ->and($run->careerPosition()->year->name)->toBe('Senior')
        ->and($run->careerPosition()->month)->toBe(10)
        ->and($run->careerPosition()->phase->value)->toBe('Early');

    $entry = TurnEntry::query()->where('training_run_id', $run->id)->sole();

    expect($entry->turn)->toBe(67)
        ->and($entry->speed)->toBe(607)
        ->and($entry->stamina)->toBe(500)
        ->and($entry->wit)->toBe(500)
        ->and($entry->energy)->toBe(66)
        ->and($entry->fans)->toBe(12000)
        ->and($entry->sp)->toBe(40);
});

it('advances the run\'s next turn past the position, so it continues rather than restarting', function (): void {
    $run = (new CreateSnapshotRun)->handle(snapshotPayload());

    expect($run->fresh()->nextTurnNumber())->toBe(68);
});

it('records a scenario and a position alone, with no turn entry and no invented numbers', function (): void {
    $run = (new CreateSnapshotRun)->handle(snapshotPayload([
        'speed' => null, 'stamina' => null, 'power' => null, 'guts' => null, 'wit' => null,
        'energy' => null, 'fans' => null, 'skill_points' => null,
    ]));

    expect($run->mode)->toBe(RunMode::Snapshot)
        ->and($run->careerPosition()->turnIndex)->toBe(67)
        ->and($run->hasImportedPosition())->toBeTrue()
        ->and(TurnEntry::query()->where('training_run_id', $run->id)->exists())->toBeFalse()
        ->and($run->nextTurnNumber())->toBe(68);
});

it('refuses a position the calendar does not name', function (): void {
    (new CreateSnapshotRun)->handle(snapshotPayload(['career_month' => 13]));
})->throws(InvalidArgumentException::class, 'A career month is 1 to 12; [13] is not.');

it('leaves the new-career path alone: a form without a mode still creates a new career', function (): void {
    $trainee = Umamusume::factory()->create();

    test()->post('/training-runs', [
        'umamusume_id' => $trainee->id,
        'status' => 'Active',
        'scenario' => 'unity_cup',
    ])->assertRedirect();

    $run = TrainingRun::query()->latest('id')->first();

    expect($run->mode)->toBe(RunMode::NewCareer)
        ->and($run->isSnapshot())->toBeFalse()
        ->and($run->hasImportedPosition())->toBeFalse();
});

it('refuses a snapshot form that names no position, at the boundary', function (): void {
    $trainee = Umamusume::factory()->create();

    test()->post('/career/snapshot', [
        'umamusume_id' => $trainee->id,
        'scenario' => 'unity_cup',
        'career_year' => 3,
        'career_month' => 10,
        // the half is absent
    ])->assertSessionHasErrors(['career_phase']);
});

it('records through the route and lands on the cockpit of the run it created', function (): void {
    $trainee = Umamusume::factory()->create();

    $response = test()->post('/career/snapshot', snapshotPayload([
        'umamusume_id' => $trainee->id,
        'notes' => 'Autumn of Senior Year, mid-deck.',
    ]));

    $run = TrainingRun::query()->latest('id')->first();

    $response->assertRedirect(route('runs.cockpit', $run));

    expect($run->umamusume_id)->toBe($trainee->id)
        ->and($run->notes)->toBe('Autumn of Senior Year, mid-deck.')
        ->and($run->career_position_source)->toBe('imported');
});
