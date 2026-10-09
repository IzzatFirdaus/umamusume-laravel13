<?php

declare(strict_types=1);

use App\Enums\PerformanceType;
use App\Enums\TurnEventType;
use App\Models\TrainingRun;
use App\Models\TurnEvent;
use App\Models\TurnEvents\PerformancePayload;

/*
 * The Performance foundation for Our Grand Concert (plan §9 E6's successor slice).
 *
 * The scenario pays a five-type resource alongside stats and spends it in the Lesson
 * menu, and `docs/scenarios/07-grand-concert.md` records the mechanics while marking
 * every magnitude unverified. So the storage gets a contract for the *fact* — which
 * type, and how much it changed — and nothing about how much a turn pays or a Lesson
 * costs. No UI here: the widget that reads this is a later slice's panel.
 *
 * The five names are Global client strings, not this tool's identifiers. Notice 905
 * prints "There are five types of Performance: Dance, Passion, Vocals, Visuals, and
 * Composure", so the two plurals and `Composure` are pinned below: a table that drifted
 * to "Vocal", "Visual" or GameTora's "Mental" would be printing a guide's word over the
 * client's (`docs/UMAMUSUME_REFERENCE.md` §7 row 48).
 */
function performanceRun(): TrainingRun
{
    return TrainingRun::factory()->create(['scenario' => 'our_grand_concert']);
}

it('carries exactly the five Performance types the client prints, in order', function (): void {
    $values = array_map(fn (PerformanceType $t): string => $t->value, PerformanceType::cases());

    expect($values)->toBe(['Dance', 'Passion', 'Vocals', 'Visuals', 'Composure']);
});

it('labels a type with the client word rather than a composed one', function (): void {
    // The value and the label agree by construction, which is the point: the client's
    // word is the storage token, so nothing has to translate it to print it.
    foreach (PerformanceType::cases() as $type) {
        expect($type->label())->toBe($type->value);
    }

    // The two spellings a guide gets wrong, pinned by name so a "correction" to the
    // singular or to GameTora's rendering fails here instead of on a screen.
    expect(PerformanceType::Vocals->label())->toBe('Vocals')
        ->and(PerformanceType::Visuals->label())->toBe('Visuals')
        ->and(PerformanceType::Composure->label())->toBe('Composure');
});

it('round-trips an acquired change through SQLite without losing the type or the sign', function (): void {
    $run = performanceRun();

    $event = TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => 7,
        'event_type' => TurnEventType::Scenario,
        'source_name' => 'Dance Lesson',
        'deltas' => PerformancePayload::make(PerformanceType::Dance, 12)->toArray(),
    ]);

    $fresh = TurnEvent::query()->findOrFail($event->id);

    // The raw column, read before any cast: this is the sentence SQLite holds, so a key
    // dropped on the way in and one dropped on the way out are the same failure here.
    expect($fresh->getRawOriginal('deltas'))->toBe('{"type":"Dance","delta":12}')
        ->and($fresh->performancePayload()?->type)->toBe(PerformanceType::Dance)
        ->and($fresh->performancePayload()?->delta)->toBe(12);
});

it('represents a spend as a negative delta on the same shape', function (): void {
    $run = performanceRun();

    $event = TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => 9,
        'event_type' => TurnEventType::Scenario,
        'source_name' => 'Lesson menu',
        'deltas' => PerformancePayload::make(PerformanceType::Composure, -40)->toArray(),
    ]);

    $payload = TurnEvent::query()->findOrFail($event->id)->performancePayload();

    expect($payload?->type)->toBe(PerformanceType::Composure)
        ->and($payload?->delta)->toBe(-40);
});

it('accepts the stored string form on the way back in, so a raw array is not a second shape', function (): void {
    $run = performanceRun();

    $event = TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => 4,
        'event_type' => TurnEventType::Scenario,
        'source_name' => 'Vocals training',
        'deltas' => ['type' => 'Vocals', 'delta' => 8],
    ]);

    expect(TurnEvent::query()->findOrFail($event->id)->performancePayload()?->type)
        ->toBe(PerformanceType::Vocals);
});

it('refuses a type the corpus does not name, including the guide renderings', function (string $rejected): void {
    $run = performanceRun();

    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('not one of the five Performance types');

    TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => 5,
        'event_type' => TurnEventType::Scenario,
        'source_name' => 'Training',
        'deltas' => ['type' => $rejected, 'delta' => 5],
    ]);
})->with(['Mental', 'Vocal', 'Visual', 'Dancing', 'dance', '']);

it('refuses a payload carrying a key it does not know', function (): void {
    $run = performanceRun();

    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('carries exactly [delta, type] keys');

    TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => 5,
        'event_type' => TurnEventType::Scenario,
        'source_name' => 'Training',
        'deltas' => ['type' => 'Dance', 'delta' => 5, 'cost' => 30],
    ]);
});

it('refuses a zero delta, because a payload records a change', function (): void {
    expect(fn () => PerformancePayload::make(PerformanceType::Dance, 0))
        ->toThrow(InvalidArgumentException::class, 'a zero delta is not one');
});

it('refuses a non-integer delta rather than coercing one', function (): void {
    $run = performanceRun();

    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('needs an integer delta');

    TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => 5,
        'event_type' => TurnEventType::Scenario,
        'source_name' => 'Training',
        'deltas' => ['type' => 'Dance', 'delta' => '12'],
    ]);
});

it('keeps a Performance payload out of the sibling payloads, and theirs out of it', function (): void {
    $run = performanceRun();

    $performance = TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => 6,
        'event_type' => TurnEventType::Scenario,
        'source_name' => 'Dance training',
        'deltas' => PerformancePayload::make(PerformanceType::Dance, 10)->toArray(),
    ]);

    $burst = TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => 6,
        'event_type' => TurnEventType::Scenario,
        'source_name' => 'Unity Training',
        'deltas' => ['teammate' => 'special_week', 'state' => 'Chargeable'],
    ]);

    expect($performance->performancePayload()?->delta)->toBe(10)
        ->and($performance->burstPayload())->toBeNull()
        ->and($burst->performancePayload())->toBeNull()
        ->and($burst->burstPayload()?->teammate)->toBe('special_week');
});
