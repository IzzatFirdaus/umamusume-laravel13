<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * The facility levels a turn was trained at, as the client's own five-level ladder shows them.
 *
 * **The route writes them now.** `TrainingRunController::turnAttributes()` used to whitelist the
 * columns a turn write accepts and drop these five, because that file carried a concurrent session's
 * staged hunks and the list could not be extended in the same pass. The cutover landed the file, the
 * whitelist names the five columns, and a POST through `runs.turns.store` persists them; the case
 * below is the route's own contract rather than the model's. `updateTurn` writes
 * `$request->validated()` whole, so the two turn writes agree on the key set.
 *
 * The timeline's read half is asserted too: the renderer prints a level only when the row has one and
 * a single named absence otherwise, so what it needs from the server is the labelled list or null.
 */
function facilityRun(): TrainingRun
{
    return TrainingRun::factory()->create([
        'status' => RunStatus::Active,
        'scenario' => 'unity_cup',
        'umamusume_id' => Umamusume::factory()->create()->id,
    ]);
}

it('persists all five facility levels a turn was trained at', function (): void {
    $run = facilityRun();

    $entry = TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 9,
        'facility_speed' => 4,
        'facility_stamina' => 4,
        'facility_power' => 4,
        'facility_guts' => 5,
        'facility_wit' => 4,
    ]);

    $read = TurnEntry::query()->findOrFail($entry->id);

    expect($read->facility_speed)->toBe(4)
        ->and($read->facility_stamina)->toBe(4)
        ->and($read->facility_power)->toBe(4)
        ->and($read->facility_guts)->toBe(5)
        ->and($read->facility_wit)->toBe(4);
});

it('persists a partial reading, leaving an unread facility absent rather than zeroed', function (): void {
    $run = facilityRun();

    $entry = TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 4,
        'facility_speed' => 3,
        'facility_guts' => 5,
    ]);

    $read = TurnEntry::query()->findOrFail($entry->id);

    expect($read->facility_speed)->toBe(3)
        ->and($read->facility_guts)->toBe(5)
        ->and($read->facility_stamina)->toBeNull()
        ->and($read->facility_power)->toBeNull()
        ->and($read->facility_wit)->toBeNull();
});

it('leaves every facility absent on a turn logged from the rail', function (): void {
    $run = facilityRun();

    $entry = TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 2]);

    $read = TurnEntry::query()->findOrFail($entry->id);

    expect($read->facility_speed)->toBeNull()
        ->and($read->facility_wit)->toBeNull();
});

it('refuses a facility level the client ladder does not have, at the request boundary', function (int $level): void {
    $run = facilityRun();

    test()->post(route('runs.turns.store', $run), [
        'turn' => 3,
        'speed' => 600,
        'stamina' => 500,
        'power' => 500,
        'guts' => 500,
        'wit' => 500,
        'facility_speed' => $level,
    ])->assertSessionHasErrors(['facility_speed']);
})->with([0, 6]);

it('persists the five facility levels through the turn route', function (): void {
    $run = facilityRun();

    test()->post(route('runs.turns.store', $run), [
        'turn' => 5,
        'speed' => 600,
        'stamina' => 500,
        'power' => 500,
        'guts' => 500,
        'wit' => 500,
        'facility_speed' => 4,
        'facility_stamina' => 3,
        'facility_power' => 2,
        'facility_guts' => 5,
        'facility_wit' => 1,
    ])->assertRedirect();

    $entry = TurnEntry::query()
        ->where('training_run_id', $run->id)
        ->where('turn', 5)
        ->firstOrFail();

    expect($entry->facility_speed)->toBe(4)
        ->and($entry->facility_stamina)->toBe(3)
        ->and($entry->facility_power)->toBe(2)
        ->and($entry->facility_guts)->toBe(5)
        ->and($entry->facility_wit)->toBe(1);
});

it('carries the recorded levels to the timeline, and one absence when a turn read none', function (): void {
    $run = facilityRun();

    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 9,
        'facility_speed' => 4,
        'facility_guts' => 5,
    ]);

    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 10]);

    // Only the levels that were read travel, in the ladder's own stat order, and a turn that read
    // none sends null rather than five zeroes.
    test()->get(route('runs.timeline', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Timeline')
            ->where('entries.0.turn', 9)
            ->where('entries.0.facilities', [
                ['label' => 'Speed', 'level' => 4],
                ['label' => 'Guts', 'level' => 5],
            ])
            ->where('entries.1.turn', 10)
            ->where('entries.1.facilities', null));
});
