<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\TrainingRun;

/*
 * B1: the circles guard reachable as a 500.
 *
 * `RaceEntry` refuses a circle count on any slot whose kind is not `team_race`, and it refuses it by
 * throwing. The refusal is right for a direct write, and wrong for the only path a Trainer can take:
 * `race-panel.blade.php` renders the circles control whenever the run composes the `team_race` panel,
 * which Unity Cup does, while the same panel's picker can only ever offer the career catalogue or a
 * `free_race` slot. So a Unity Cup Trainer who reads circles is posting a payload the model throws on,
 * and `0` throws too because the guard tests null rather than zero. A validation failure is recoverable
 * and a 500 is not, so the scenario gate moves into `StoreRaceEntryRequest`, where every other rule
 * about this payload already lives. The model guard stays: it is the last line for writes that never
 * pass through a Form Request.
 */

function circlesSlot(string $kind, string $scenarioKey): ScenarioSlot
{
    return ScenarioSlot::factory()->create([
        'scenario_key' => $scenarioKey,
        'kind' => $kind,
        'title' => 'Circles Slot '.$kind,
        'is_mandatory' => false,
    ]);
}

/**
 * The payload the rendered form sends: a calendar branch entry naming one slot, plus the circles
 * the Trainer read. Only `circles` is under test, so the rest is the minimum that validates.
 *
 * @return array<string, mixed>
 */
function circlesPayload(ScenarioSlot $slot, int|string|null $circles): array
{
    $payload = [
        'entry_mode' => 'calendar',
        'status' => RaceEntryStatus::Completed->value,
        'scenario_slot_id' => $slot->id,
    ];

    if ($circles !== null) {
        $payload['circles'] = $circles;
    }

    return $payload;
}

it('refuses a circle count on a free race slot with a field error rather than a crash', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
    $slot = circlesSlot('free_race', 'unity_cup');

    // Before the fix this line does not fail an assertion, it propagates the model's
    // InvalidArgumentException, which is the shape of the bug: the request never reaches a response.
    $this->post(route('runs.races.store', $run), circlesPayload($slot, 2))
        ->assertSessionHasErrors('circles');

    expect(RaceEntry::where('training_run_id', $run->id)->count())->toBe(0);
});

it('refuses zero circles on a non team race slot, because zero is a value and not an absence', function (): void {
    // The guard in the model reads `null` as "not recorded" and everything else as a claim, so 0 is a
    // claim. A Trainer who means "the race had no circles" is answered by the empty option, not by 0.
    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
    $slot = circlesSlot('free_race', 'unity_cup');

    $this->post(route('runs.races.store', $run), circlesPayload($slot, 0))
        ->assertSessionHasErrors('circles');
});

it('names the reason the circles were refused, where the Trainer is looking at it', function (): void {
    // The panel already carries a circles line in its error block
    // (`race-panel.blade.php:210`), which is the panel saying it expected this field to be refused
    // and never being given the refusal. Asserted on the rendered page rather than on the bag,
    // because a message nobody can read is not a recoverable failure either.
    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
    $slot = circlesSlot('free_race', 'unity_cup');

    $html = $this->followingRedirects()
        ->post(route('runs.races.store', $run), circlesPayload($slot, 3), [
            'HTTP_REFERER' => route('runs.show', $run),
        ])
        ->assertOk()
        ->content();

    // The panel has to be on the page at all before its message can be, and for a Unity Cup run that
    // is the `race-panel` early gate to clear, not a given.
    expect($html)->toContain('Record race')
        ->and($html)->toMatch('/Circles can only be read against a team race\./');

    // Negative control, so the match above cannot be satisfied by static copy on the page: the same
    // screen, reached by a submit that is allowed, carries no refusal at all.
    $teamSlot = circlesSlot('team_race', 'unity_cup');

    $clean = $this->followingRedirects()
        ->post(route('runs.races.store', $run), circlesPayload($teamSlot, 2), [
            'HTTP_REFERER' => route('runs.show', $run),
        ])
        ->assertOk()
        ->content();

    expect($clean)->not->toMatch('/Circles can only be read/');
});

it('still accepts a circle count against a team race slot, which is the case the guard exists for', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
    $slot = circlesSlot('team_race', 'unity_cup');

    $this->post(route('runs.races.store', $run), circlesPayload($slot, 4))
        ->assertSessionHasNoErrors();

    $entry = RaceEntry::where('training_run_id', $run->id)->first();

    expect($entry)->not->toBeNull()
        ->and($entry->circles)->toBe(4)
        ->and($entry->scenarioSlot?->kind)->toBe('team_race');
});

it('keeps the model guard standing, because a direct write bypasses the Form Request', function (): void {
    // The request rule is the reachable path's fix. This is the unreachable one, and it has to keep
    // throwing: a run created by a seeder or a console write never passes through validation, and the
    // alternative to a throw there is a silently stored claim the tool cannot support.
    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
    $slot = circlesSlot('free_race', 'unity_cup');

    expect(fn (): RaceEntry => RaceEntry::create([
        'training_run_id' => $run->id,
        'scenario_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'circles' => 2,
    ]))->toThrow(InvalidArgumentException::class);
});
