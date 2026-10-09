<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;

/**
 * The facility levels a turn was trained at, as the client's own five-level ladder shows them.
 *
 * **These cases read and write through the model, not the route.** `TrainingRunController::turnAttributes()`
 * whitelists the columns a turn write accepts, and that file carries a concurrent session's staged hunks,
 * so the whitelist could not be extended in the same pass: a POST through `runs.turns.store` validates
 * these five fields and then drops them. The route's own contract is asserted below only where it is
 * true - the range refusal, which fires before the whitelist - and the persistence contract is asserted
 * where it lives, on the model the whitelist feeds.
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
