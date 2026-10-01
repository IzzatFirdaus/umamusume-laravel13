<?php

declare(strict_types=1);

use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\TurnEvent;
use Illuminate\Database\Eloquent\Model;

/*
 * F-7: two controller writes each land two rows with no transaction, so a failure on the
 * second row leaves the first behind. ARCHITECTURE.md:64 and PRD NFR-4 ask for a transaction
 * when multiple writes must be atomic, and AGENTS.md:121 restates it. `store` at
 * TrainingRunController.php:146 and `syncDeck` at :727 already wrap; these two do not.
 *
 * storeTurn, :624 then :639: a turn logged as a Failure writes the turn row and then its
 * failure event. An orphan turn row with no event is the half state.
 * storeRace, :528 then :546: the manual path creates a `free_race` slot and then the entry
 * that names it. An orphan slot is a calendar row pointing at a race nobody recorded.
 */

/**
 * @param  class-string<Model>  $model
 */
function failOnInsert(string $model): void
{
    $model::creating(function (): void {
        throw new RuntimeException('forced second-write failure');
    });
}

function atomicityRun(): TrainingRun
{
    return TrainingRun::factory()->create(['scenario' => 'ura_finale']);
}

it('rolls the turn row back when its failure event cannot be written', function (): void {
    $run = atomicityRun();

    failOnInsert(TurnEvent::class);

    try {
        $this->post("/training-runs/{$run->id}/turns", [
            'turn' => 1,
            'speed' => 120,
            'stamina' => 110,
            'power' => 130,
            'guts' => 100,
            'wit' => 95,
            'sp' => 20,
            'energy' => 70,
            'mood' => 'GREAT',
            'fans' => 1200,
            'choice' => 'training-Speed',
            'outcome' => 'Failure',
            'penalty_kind' => 'energy',
            'stage' => 'confirm',
            'previewed' => '1',
        ]);
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('forced second-write failure');
    }

    // F-7: the turn row is committed today, so this is 1 and not 0.
    expect(TurnEntry::count())->toBe(0)
        ->and(TurnEvent::count())->toBe(0)
        ->and($run->turnEntries()->count())->toBe(0);
});

it('rolls the manual race slot back when its entry cannot be written', function (): void {
    $run = atomicityRun();

    $slotsBefore = ScenarioSlot::count();

    failOnInsert(RaceEntry::class);

    try {
        $this->post("/training-runs/{$run->id}/races", [
            'entry_mode' => 'manual',
            'status' => 'Completed',
            'title' => 'Hand Entered Race',
            'month' => 4,
            'half' => 'Early',
        ]);
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('forced second-write failure');
    }

    // F-7: the slot is committed today, so the delta is 1 and not 0.
    expect(RaceEntry::count())->toBe(0)
        ->and(ScenarioSlot::count())->toBe($slotsBefore)
        ->and($run->raceEntries()->count())->toBe(0);
});

it('records both rows when nothing fails, the canary for the two above', function (): void {
    $run = atomicityRun();

    $this->post("/training-runs/{$run->id}/turns", [
        'turn' => 1,
        'speed' => 120,
        'stamina' => 110,
        'power' => 130,
        'guts' => 100,
        'wit' => 95,
        'sp' => 20,
        'energy' => 70,
        'mood' => 'GREAT',
        'fans' => 1200,
        'choice' => 'training-Speed',
        'outcome' => 'Failure',
        'penalty_kind' => 'energy',
        'stage' => 'confirm',
        'previewed' => '1',
    ])->assertRedirect(route('runs.show', $run));

    // Without this, a test that only asserts zero rows would also pass on a write path that
    // never writes anything at all.
    expect(TurnEntry::count())->toBe(1)
        ->and(TurnEvent::count())->toBe(1);
});

it('records both rows of a manual race when nothing fails, the canary for the slot test above', function (): void {
    $run = atomicityRun();
    $slotsBefore = ScenarioSlot::count();

    $this->post("/training-runs/{$run->id}/races", [
        'entry_mode' => 'manual',
        'status' => 'Completed',
        'title' => 'Hand Entered Race',
        'month' => 4,
        'half' => 'Early',
    ])->assertRedirect(route('runs.show', $run));

    // The race test above passed while validation rejected its payload and nothing was
    // written at all. This pins that the manual path does reach both inserts.
    expect(ScenarioSlot::count())->toBe($slotsBefore + 1)
        ->and(RaceEntry::count())->toBe(1);
});
