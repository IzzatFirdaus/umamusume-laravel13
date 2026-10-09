<?php

declare(strict_types=1);

use App\Enums\PerformanceType;
use App\Enums\RunStatus;
use App\Enums\SpiritBurstState;
use App\Enums\TurnEventType;
use App\Models\TrainingRun;
use App\Models\TurnEvent;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Performance reaches the read side (Our Grand Concert).
 *
 * Slice 4 put an observation on the write path; this proves `TrainingRun::performanceObservations()`
 * returns what was recorded, through the real turn endpoint rather than a hand-built row.
 *
 * The boundary this file exists to hold is that a read may expose history but not invent a balance.
 * The dispatch's own example is asserted literally: +12 then -5 must stay two rows, and the number 7
 * must appear nowhere, because the term it would start from — what a run begins with — is not
 * published in this corpus.
 */
function observationRun(string $scenario = 'our_grand_concert'): TrainingRun
{
    return TrainingRun::factory()->create([
        'scenario' => $scenario,
        'status' => RunStatus::Active,
    ]);
}

/**
 * Record one Performance observation the way the guided rail does: through the write endpoint.
 */
function recordObservation(TrainingRun $run, int $turn, string $type, int $delta): void
{
    test()->post(route('runs.turns.store', $run), [
        'turn' => $turn,
        'speed' => 600,
        'stamina' => 600,
        'power' => 600,
        'guts' => 600,
        'wit' => 600,
        'stage' => 'confirm',
        'previewed' => '1',
        'choice' => 'training-'.$type,
        'outcome' => 'Success',
        'performance' => ['type' => $type, 'delta' => $delta],
    ])->assertRedirect();
}

it('reads back through the run model the observation the turn write recorded', function (): void {
    $run = observationRun();
    recordObservation($run, 1, 'Dance', 12);

    // A fresh model instance: this reads what storage holds, not what the write left in memory.
    $fresh = TrainingRun::query()->findOrFail($run->id);
    $event = TurnEvent::query()->where('training_run_id', $run->id)->sole();

    expect($fresh->performanceObservations())->toBe([[
        'turn' => 1,
        'event_id' => (int) $event->id,
        'type' => PerformanceType::Dance,
        'delta' => 12,
    ]])
        ->and($fresh->performanceObservations()[0]['type'])->toBeInstanceOf(PerformanceType::class)
        ->and($fresh->performanceObservations()[0]['type'])->toBe(PerformanceType::Dance)
        ->and($fresh->performanceObservations()[0]['delta'])->toBe(12);
});

it('keeps +12 then -5 as two rows and prints no balance anywhere between them', function (): void {
    $run = observationRun();
    recordObservation($run, 1, 'Dance', 12);
    recordObservation($run, 2, 'Dance', -5);

    $observations = TrainingRun::query()->findOrFail($run->id)->performanceObservations();

    expect($observations)->toHaveCount(2)
        ->and($observations[0]['turn'])->toBe(1)
        ->and($observations[1]['turn'])->toBe(2)
        ->and($observations[0]['delta'])->toBe(12)
        ->and($observations[1]['delta'])->toBe(-5)
        // Two readings of the same type at two turns, each tied to its own event row.
        ->and($observations[0]['event_id'])->not->toBe($observations[1]['event_id']);

    // The sum the corpus cannot support must not appear as a value in anything the read returns.
    expect(array_column($observations, 'delta'))->not->toContain(7);
});

it('carries exactly the four facts a consumer needs and no total field', function (): void {
    $run = observationRun();
    recordObservation($run, 1, 'Composure', -30);

    $row = TrainingRun::query()->findOrFail($run->id)->performanceObservations()[0];

    expect(array_keys($row))->toBe(['turn', 'event_id', 'type', 'delta'])
        ->and($row['type'])->toBe(PerformanceType::Composure)
        ->and($row['delta'])->toBe(-30);

    // A spend stays a spend: the read does not fold the sign away into a magnitude.
    expect(abs($row['delta']))->toBe(30)
        ->and($row['delta'])->toBeLessThan(0);
});

it('adds no Performance figure to the representations a screen already reads', function (): void {
    $run = observationRun();
    recordObservation($run, 1, 'Dance', 12);

    $fresh = TrainingRun::query()->findOrFail($run->id);

    // The resource strip is where a current value would land if one were invented. It is keyed by
    // widget, and Performance is declared on no scenario's widget list, so it must be absent here.
    expect($fresh->stripValues())->not->toHaveKey('performance')
        ->and($fresh->stripValues())->not->toHaveKey('Dance');

    // And the cockpit's scenario payload gains nothing speculative either. `scenario.label` is
    // asserted in the same closure so the `missing()` line below is proven to be reading a payload
    // that actually exists rather than passing on an absent branch.
    test()->get(route('runs.cockpit', $run))->assertInertia(fn (Assert $page) => $page
        ->where('scenario.label', 'Our Grand Concert')
        ->missing('scenario.performance'));
});

it('leaves the sibling payload readers working on a run that also holds a Performance observation', function (): void {
    $run = observationRun('unity_cup');

    $run->turnEvents()->create([
        'turn' => 1,
        'event_type' => TurnEventType::Scenario,
        'source_name' => 'Unity Training',
        'deltas' => ['teammate' => 'special_week', 'state' => 'Chargeable'],
    ]);

    $run->turnEvents()->create([
        'turn' => 2,
        'event_type' => TurnEventType::Scenario,
        'source_name' => 'Dance training',
        'deltas' => ['type' => 'Dance', 'delta' => 9],
    ]);

    $fresh = TrainingRun::query()->with('turnEvents')->findOrFail($run->id);

    expect($fresh->spiritBurstRoster())->toBe([
        ['teammate' => 'special_week', 'state' => SpiritBurstState::Chargeable],
    ])
        ->and($fresh->performanceObservations())->toHaveCount(1)
        ->and($fresh->performanceObservations()[0]['turn'])->toBe(2)
        ->and($fresh->performanceObservations()[0]['delta'])->toBe(9);
});

it('returns an empty list rather than a zero for a run with no Performance observation', function (string $scenario): void {
    $run = observationRun($scenario);

    test()->post(route('runs.turns.store', $run), [
        'turn' => 1,
        'speed' => 600,
        'stamina' => 600,
        'power' => 600,
        'guts' => 600,
        'wit' => 600,
        'stage' => 'confirm',
        'previewed' => '1',
        'choice' => 'training-Speed',
        'outcome' => 'Success',
    ])->assertRedirect();

    // An empty list, not `0` and not a row invented to fill the gap: a turn that reported no
    // Performance said nothing about Performance (D-220).
    expect(TrainingRun::query()->findOrFail($run->id)->performanceObservations())->toBe([]);
})->with(['our_grand_concert', 'ura_finale', 'unity_cup', 'trackblazer']);
