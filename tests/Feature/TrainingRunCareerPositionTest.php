<?php

declare(strict_types=1);

use App\Domain\Career\CareerPosition;
use App\Enums\CareerPhase;
use App\Enums\CareerYear;
use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\TurnEntry;

/**
 * The three states `TrainingRun::careerPosition()` distinguishes: stored, derived, and absent.
 *
 * The stored case is a snapshot's answer and wins over derivation; the derived case is every
 * new-career run's answer and comes from the same calendar the value object tests; the absent case is
 * a run with nothing logged and nothing stored, which claims no position rather than pointing at
 * Early January (D-220).
 */
it('derives the position from the run\'s own latest turn when nothing is stored', function (): void {
    $run = TrainingRun::factory()->create(['status' => RunStatus::Active]);

    for ($turn = 1; $turn <= 20; $turn++) {
        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => $turn]);
    }

    $position = $run->careerPosition();

    expect($position)->toBeInstanceOf(CareerPosition::class)
        ->and($position->year)->toBe(CareerYear::Junior)
        // Turn 20 is the Late half of the tenth month: 19 turns before it put eleven in Late and nine
        // in Early halves, so the example in the dispatch's brief ("turn 20 is Early November") does
        // not match the calendar it asked this accessor to serve. Turn 21 is Junior Early November.
        ->and($position->month)->toBe(10)
        ->and($position->phase)->toBe(CareerPhase::Late)
        ->and($position->turnIndex)->toBe(20)
        ->and($run->hasImportedPosition())->toBeFalse();
});

it('derives Junior Early November from turn 21, the case the dispatch named', function (): void {
    $run = TrainingRun::factory()->create();

    for ($turn = 1; $turn <= 21; $turn++) {
        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => $turn]);
    }

    $position = $run->careerPosition();

    expect($position->year)->toBe(CareerYear::Junior)
        ->and($position->month)->toBe(11)
        ->and($position->phase)->toBe(CareerPhase::Early);
});

it('returns the stored position as stored, and calls it imported', function (): void {
    $run = TrainingRun::factory()->create([
        'career_position' => ['year' => 3, 'month' => 10, 'phase' => 'Early', 'turn_index' => 67, 'scenario_countdown' => 5],
    ]);

    $position = $run->fresh()->careerPosition();

    expect($position)->toBeInstanceOf(CareerPosition::class)
        ->and($position->year)->toBe(CareerYear::Senior)
        ->and($position->month)->toBe(10)
        ->and($position->phase)->toBe(CareerPhase::Early)
        ->and($position->turnIndex)->toBe(67)
        ->and($position->scenarioCountdown)->toBe(5)
        ->and($run->fresh()->hasImportedPosition())->toBeTrue();
});

it('lets the stored position win over a longer turn count, because the snapshot named it', function (): void {
    $run = TrainingRun::factory()->create([
        'career_position' => ['year' => 1, 'month' => 1, 'phase' => 'Early', 'turn_index' => 1],
    ]);

    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 30]);

    expect($run->fresh()->careerPosition()->turnIndex)->toBe(1)
        ->and($run->fresh()->hasImportedPosition())->toBeTrue();
});

it('claims no position for a run with nothing logged and nothing stored', function (): void {
    $run = TrainingRun::factory()->create();

    expect($run->careerPosition())->toBeNull()
        ->and($run->hasImportedPosition())->toBeFalse();
});

it('claims no position from a turn count the calendar cannot name, rather than clamping one', function (): void {
    $run = TrainingRun::factory()->create();

    for ($turn = 1; $turn <= 73; $turn++) {
        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => $turn]);
    }

    expect($run->careerPosition())->toBeNull();
});

it('writes through the cast and reads back the same position', function (): void {
    $position = CareerPosition::fromTurnIndex(67, 5);

    $run = TrainingRun::factory()->create(['career_position' => $position]);

    $read = $run->fresh()->career_position;

    expect($read)->toBeInstanceOf(CareerPosition::class)
        ->and($read->toArray())->toBe($position->toArray())
        ->and($run->fresh()->hasImportedPosition())->toBeTrue();
});
