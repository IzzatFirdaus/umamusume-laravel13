<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\TrainingRun;

/*
 * R56: the free-race writer. A Trainer-reported race with no calendar row
 * creates a manual `scenario_slots` row (entered title, manual flag), then a
 * `race_entries` link. Manual rows render on the calendar labelled
 * Trainer-entered, never as client data.
 *
 * R61: `free_race` is the fifth kind. The predicate for rendering and labelling
 * a row as manual is `source_key === null`, because that is the provenance
 * statement; `is_manual` remains the overwrite-protection flag.
 */

it('creates a manual scenario_slot and a linked race_entry in one request', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    // The write returns to the page the form was posted from, so the run screen is seeded as the
    // referer: the same URL the panel lives on, and the same target as before the Race Decision
    // screen arrived (`SCR-CAR-013`).
    $response = $this->from(route('runs.show', $run))->post(route('runs.races.store', $run), [
        'entry_mode' => 'manual',
        'title' => 'My Custom Race',
        'month' => 6,
        'half' => 'Early',
        'tier' => 'G2',
        'status' => RaceEntryStatus::Completed->value,
        'placement' => 1,
    ]);

    $response->assertRedirect(route('runs.show', $run));

    $slot = ScenarioSlot::where('scenario_key', 'ura_finale')
        ->where('kind', 'free_race')
        ->where('title', 'My Custom Race')
        ->first();

    expect($slot)->not->toBeNull()
        ->and($slot->is_manual)->toBeTrue()
        ->and($slot->source_key)->toBeNull()
        ->and($slot->source_url)->toBeNull()
        ->and($slot->snapshot_path)->toBeNull()
        ->and($slot->fetched_at)->toBeNull()
        ->and($slot->source_timezone)->toBeNull()
        ->and($slot->month)->toBe(6)
        ->and($slot->half)->toBe('Early')
        ->and($slot->tier)->toBe('G2');

    $entry = RaceEntry::where('training_run_id', $run->id)
        ->where('scenario_slot_id', $slot->id)
        ->first();

    expect($entry)->not->toBeNull()
        ->and($entry->status)->toBe(RaceEntryStatus::Completed)
        ->and($entry->placement)->toBe(1);
});

it('rejects an empty title on the manual path with a field error', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    $response = $this->post(route('runs.races.store', $run), [
        'entry_mode' => 'manual',
        'title' => '',
        'month' => 6,
        'half' => 'Early',
        'status' => RaceEntryStatus::Completed->value,
    ]);

    $response->assertSessionHasErrors('title');
});

it('rejects month 13 on the manual path', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    $response = $this->post(route('runs.races.store', $run), [
        'entry_mode' => 'manual',
        'title' => 'Bad Month Race',
        'month' => 13,
        'half' => 'Early',
        'status' => RaceEntryStatus::Completed->value,
    ]);

    $response->assertSessionHasErrors('month');
});

it('rejects half "Mid" on the manual path', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    $response = $this->post(route('runs.races.store', $run), [
        'entry_mode' => 'manual',
        'title' => 'Bad Half Race',
        'month' => 6,
        'half' => 'Mid',
        'status' => RaceEntryStatus::Completed->value,
    ]);

    $response->assertSessionHasErrors('half');
});

it('stores the override when tier differs from a seeded slot prefill', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $slot = ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'goal_race',
        'title' => 'Tenno Sho (Spring)',
        'month' => 4,
        'half' => 'Late',
        'tier' => 'G1',
    ]);

    $response = $this->from(route('runs.show', $run))->post(route('runs.races.store', $run), [
        'entry_mode' => 'calendar',
        'scenario_slot_id' => $slot->id,
        'tier_override' => 'G2',
        'status' => RaceEntryStatus::Completed->value,
        'placement' => 2,
    ]);

    $response->assertRedirect(route('runs.show', $run));

    $entry = RaceEntry::where('training_run_id', $run->id)
        ->where('scenario_slot_id', $slot->id)
        ->first();

    expect($entry)->not->toBeNull()
        ->and($entry->placement)->toBe(2);
});

it('reaches the 422 branch when the manual path has no title (R56 standing criterion)', function (): void {
    // Per R56: every error-handling branch owes a test that reaches it, cited to
    // the bootstrap 500 incident. This test exercises the validation failure path
    // that would have been a 500 before StoreRaceEntryRequest was extended.
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    $response = $this->post(route('runs.races.store', $run), [
        'entry_mode' => 'manual',
        'title' => '   ',
        'month' => 6,
        'half' => 'Early',
        'status' => RaceEntryStatus::Completed->value,
    ]);

    $response->assertSessionHasErrors('title');
});

it('accepts free_race as a valid kind on ScenarioSlot (R61)', function (): void {
    $slot = ScenarioSlot::factory()->create([
        'kind' => 'free_race',
        'source_key' => null,
        'is_manual' => true,
    ]);

    expect($slot->kind)->toBe('free_race')
        ->and($slot->isFreeRace())->toBeTrue();
});

it('places a free_race sort_order after the scenario seeded rows', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'goal_race',
        'sort_order' => 10,
        'month' => 4,
        'half' => 'Late',
    ]);

    $this->post(route('runs.races.store', $run), [
        'entry_mode' => 'manual',
        'title' => 'Trainer Race',
        'month' => 6,
        'half' => 'Early',
        'status' => RaceEntryStatus::Completed->value,
        'placement' => 1,
    ]);

    $manual = ScenarioSlot::where('scenario_key', 'ura_finale')
        ->where('kind', 'free_race')
        ->first();

    expect($manual->sort_order)->toBeGreaterThan(10);
});
