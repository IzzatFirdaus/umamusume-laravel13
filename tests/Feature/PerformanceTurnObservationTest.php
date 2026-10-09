<?php

declare(strict_types=1);

use App\Enums\PerformanceType;
use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\TurnEvent;
use App\Models\TurnEvents\PerformancePayload;

/*
 * Performance reaches a real turn (Our Grand Concert).
 *
 * Slice 3 gave the fact a contract; this proves the contract is reachable from the write
 * the guided rail already uses, rather than only from a payload class in isolation. The
 * amount is the Trainer's own reading of the turn and is never derived: what a turn pays
 * and what a Lesson costs are unverified, so the write stores what it was handed and
 * computes nothing. There is deliberately no running total anywhere -- the stored payload
 * has exactly two keys and `turn_entries` stays the record of absolute values.
 */
function performanceTurnRun(): TrainingRun
{
    return TrainingRun::factory()->create([
        'scenario' => 'our_grand_concert',
        'status' => RunStatus::Active,
    ]);
}

/**
 * The turn the guided rail posts, with a Performance observation attached.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function performanceTurnPayload(array $overrides = []): array
{
    return array_merge([
        'turn' => 1,
        'speed' => 600,
        'stamina' => 600,
        'power' => 600,
        'guts' => 600,
        'wit' => 600,
        'stage' => 'confirm',
        'previewed' => '1',
        'choice' => 'training-Dance',
        'outcome' => 'Success',
    ], $overrides);
}

it('persists a positive Performance observation through the canonical turn write', function (): void {
    $run = performanceTurnRun();

    $this->post(route('runs.turns.store', $run), performanceTurnPayload([
        'performance' => ['type' => 'Dance', 'delta' => 12],
    ]))->assertRedirect();

    $event = TurnEvent::query()->where('training_run_id', $run->id)->where('turn', 1)->sole();

    // The raw column, before any cast: this is the sentence SQLite holds, so a key dropped
    // on the way in and one dropped on the way out are the same failure here.
    expect($event->getRawOriginal('deltas'))->toBe('{"type":"Dance","delta":12}')
        ->and($event->event_type->value)->toBe('Scenario')
        ->and($event->source_name)->toBe('training-Dance')
        ->and($event->performancePayload())->toBeInstanceOf(PerformancePayload::class)
        ->and($event->performancePayload()?->type)->toBe(PerformanceType::Dance)
        ->and($event->performancePayload()?->delta)->toBe(12);
});

it('round-trips a spend as a negative delta on the same path', function (): void {
    $run = performanceTurnRun();

    $this->post(route('runs.turns.store', $run), performanceTurnPayload([
        'performance' => ['type' => 'Composure', 'delta' => -30],
    ]))->assertRedirect();

    $payload = TurnEvent::query()->where('training_run_id', $run->id)->sole()->performancePayload();

    expect($payload?->type)->toBe(PerformanceType::Composure)
        ->and($payload?->delta)->toBe(-30);
});

it('keeps each observation its own delta, so nothing accumulates into a total', function (): void {
    $run = performanceTurnRun();

    $this->post(route('runs.turns.store', $run), performanceTurnPayload([
        'turn' => 1,
        'performance' => ['type' => 'Dance', 'delta' => 12],
    ]))->assertRedirect();

    $this->post(route('runs.turns.store', $run), performanceTurnPayload([
        'turn' => 2,
        'performance' => ['type' => 'Dance', 'delta' => -5],
    ]))->assertRedirect();

    $payloads = TurnEvent::query()
        ->where('training_run_id', $run->id)
        ->orderBy('turn')
        ->get()
        ->map(fn (TurnEvent $event): ?PerformancePayload => $event->performancePayload());

    // Two events, two independent readings: +12 and -5. Neither is a balance, and nothing
    // anywhere stores their sum, which is the point of keeping the fact a delta.
    expect($payloads)->toHaveCount(2)
        ->and($payloads[0]?->delta)->toBe(12)
        ->and($payloads[1]?->delta)->toBe(-5);

    foreach ($payloads as $payload) {
        expect($payload?->toArray())->toHaveKeys(['type', 'delta'])
            ->and(array_keys((array) $payload?->toArray()))->toBe(['type', 'delta']);
    }
});

it('leaves a turn with no Performance observation exactly as it was', function (): void {
    $run = performanceTurnRun();

    $this->post(route('runs.turns.store', $run), performanceTurnPayload())->assertRedirect();

    expect(TurnEvent::query()->where('training_run_id', $run->id)->count())->toBe(0);
});

it('keeps the sibling payloads working, and keeps Performance out of them', function (): void {
    $run = performanceTurnRun();

    // The failure branch of the same write, which is the one event this path already wrote.
    $this->post(route('runs.turns.store', $run), performanceTurnPayload([
        'turn' => 1,
        'outcome' => 'Failure',
        'penalty_kind' => 'energy',
        'performance' => ['type' => 'Dance', 'delta' => 12],
    ]))->assertRedirect();

    $failure = TurnEvent::query()
        ->where('training_run_id', $run->id)
        ->where('turn', 1)
        ->where('event_type', 'Failure')
        ->sole();

    $performance = TurnEvent::query()
        ->where('training_run_id', $run->id)
        ->where('turn', 1)
        ->where('event_type', 'Scenario')
        ->sole();

    expect($failure->friendshipPayload())->toBeNull()
        ->and($failure->burstPayload())->toBeNull()
        ->and($failure->performancePayload())->toBeNull()
        ->and($performance->friendshipPayload())->toBeNull()
        ->and($performance->burstPayload())->toBeNull()
        ->and($performance->performancePayload()?->delta)->toBe(12);
});

it('refuses a Performance observation the contract does not name, without writing a turn', function (array $performance): void {
    $run = performanceTurnRun();

    $this->post(route('runs.turns.store', $run), performanceTurnPayload(['performance' => $performance]))
        ->assertSessionHasErrors();

    expect(TurnEvent::query()->where('training_run_id', $run->id)->count())->toBe(0)
        ->and($run->turnEntries()->count())->toBe(0);
})->with([
    'guide rendering' => [['type' => 'Mental', 'delta' => 5]],
    'singular spelling' => [['type' => 'Vocal', 'delta' => 5]],
    'zero change' => [['type' => 'Dance', 'delta' => 0]],
    'fractional delta' => [['type' => 'Dance', 'delta' => '12.5']],
    'unknown key' => [['type' => 'Dance', 'delta' => 5, 'cost' => 30]],
    'type without delta' => [['type' => 'Dance']],
]);

it('names the field it refused, so the error is not a blank', function (): void {
    $run = performanceTurnRun();

    $this->post(route('runs.turns.store', $run), performanceTurnPayload([
        'performance' => ['type' => 'Mental', 'delta' => 5],
    ]))->assertSessionHasErrors(['performance.type']);
});

it('takes the delta as the form sends it and stores an integer', function (): void {
    $run = performanceTurnRun();

    // A form field arrives as a string, the way `speed` and `energy` do, so the write casts it
    // rather than demanding a typed value the browser cannot produce. What lands in the column
    // is still an integer, which is what the payload contract requires.
    $this->post(route('runs.turns.store', $run), performanceTurnPayload([
        'performance' => ['type' => 'Passion', 'delta' => '12'],
    ]))->assertRedirect();

    $event = TurnEvent::query()->where('training_run_id', $run->id)->sole();

    expect($event->getRawOriginal('deltas'))->toBe('{"type":"Passion","delta":12}')
        ->and($event->performancePayload()?->delta)->toBe(12);
});
