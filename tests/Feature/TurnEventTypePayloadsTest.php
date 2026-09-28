<?php

declare(strict_types=1);

use App\Enums\SpiritBurstState;
use App\Enums\TurnEventType;
use App\Models\TrainingRun;
use App\Models\TurnEvent;
use App\Models\TurnEvents\NpcFriendshipPayload;
use App\Models\TurnEvents\SpiritBurstPayload;

/*
 * D-226 says friendship bars and Spirit Burst state are scenario-specific per-turn
 * facts, so they live in `turn_events.deltas` rather than in columns on the shared
 * `turn_entries` table. That decision is only paid for if the json has a shape: an
 * untyped blob is the same `akikawa_bars` column problem with worse error messages,
 * and a key that silently vanishes on the way into SQLite is a fact the Trainer
 * entered and lost.
 *
 * Two payloads, both recorded as entered and never derived (D-270):
 *   npc friendship   -> `npc` key, `bars` as entered
 *   burst state      -> `teammate` key, one of the six D-223 states
 *
 * No UI here. This slice gives the storage a contract; the widget that reads it is a
 * later slice's Unity Cup or Trackblazer panel.
 */
function eventRun(): TrainingRun
{
    return TrainingRun::factory()->create(['scenario' => 'unity_cup']);
}

it('carries exactly the six Spirit Burst states D-223 names', function (): void {
    $values = array_map(fn (SpiritBurstState $s): string => $s->value, SpiritBurstState::cases());

    expect($values)->toBe([
        'Chargeable',
        'Charged',
        'Held',
        'NormalBurstSpent',
        'ExtremeChargeable',
        'ExtremeSpent',
    ]);
});

it('has no seventh state, and says so instead of guessing one', function (): void {
    expect(SpiritBurstState::tryFrom('ExtremeSpentTwice'))->toBeNull();

    // `from()` is called inside the closure: evaluated at the assertion line it would
    // raise before Pest can catch it, which reads as a broken test rather than a
    // rejected value.
    expect(fn () => SpiritBurstState::from('Spent'))->toThrow(ValueError::class);
});

it('round-trips a friendship payload through SQLite without losing a key', function (): void {
    $run = eventRun();

    $event = TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => 7,
        'event_type' => TurnEventType::Character,
        'source_name' => 'Director Akikawa',
        'deltas' => NpcFriendshipPayload::make('akikawa', 3)->toArray(),
    ]);

    $fresh = TurnEvent::query()->findOrFail($event->id);

    // The raw stored column, read before any cast touches it: this is the sentence
    // SQLite actually holds, so a key dropped on the way in and a key dropped on the
    // way out are the same failure here and different ones above.
    expect($fresh->getRawOriginal('deltas'))->toBe('{"npc":"akikawa","bars":3}')
        ->and($fresh->friendshipPayload()?->npc)->toBe('akikawa')
        // 3 bars stays an int. A payload that comes back as "3" would compare unequal
        // everywhere it is read, which is the silent half of silent key loss.
        ->and($fresh->friendshipPayload()?->bars)->toBe(3);
});

it('round-trips a burst payload and returns the state as the enum', function (): void {
    $run = eventRun();

    $event = TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => 12,
        'event_type' => TurnEventType::Scenario,
        'source_name' => 'Unity Training',
        'deltas' => SpiritBurstPayload::make('special_week', SpiritBurstState::ExtremeChargeable)->toArray(),
    ]);

    $payload = TurnEvent::query()->findOrFail($event->id)->burstPayload();

    expect($payload?->teammate)->toBe('special_week')
        ->and($payload?->state)->toBe(SpiritBurstState::ExtremeChargeable);
});

it('refuses to store a burst payload whose state is not one of the six', function (): void {
    $run = eventRun();

    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('not one of the six Spirit Burst states');

    TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => 12,
        'event_type' => TurnEventType::Scenario,
        'source_name' => 'Unity Training',
        'deltas' => ['teammate' => 'special_week', 'state' => 'Spent'],
    ]);
});

it('refuses a friendship payload with a key it does not know', function (): void {
    $run = eventRun();

    $this->expectException(InvalidArgumentException::class);

    TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => 7,
        'event_type' => TurnEventType::Character,
        'source_name' => 'Director Akikawa',
        'deltas' => ['npc' => 'akikawa', 'bars' => 3, 'bond' => 80],
    ]);
});

it('leaves the failure payload this tool already writes alone', function (): void {
    $run = eventRun();

    $event = TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => 3,
        'event_type' => TurnEventType::Failure,
        'source_name' => 'Guts',
        'deltas' => ['penalty_kind' => 'energy', 'recorded' => ['energy']],
    ]);

    expect($event->friendshipPayload())->toBeNull()
        ->and($event->burstPayload())->toBeNull()
        ->and($event->fresh()->deltas)->toBe(['penalty_kind' => 'energy', 'recorded' => ['energy']]);
});
