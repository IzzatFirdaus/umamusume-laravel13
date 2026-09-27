<?php

declare(strict_types=1);

use App\Enums\MoodTier;
use App\Enums\RaceEntryStatus;
use App\Enums\TurnEventType;
use App\Models\RaceEntry;
use App\Models\ScenarioRace;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\TurnEvent;

/*
 * ADR-0003 schema expansion: energy/mood/fans on turn_entries, plus the
 * turn_events, scenario_races and race_entries tables. The behaviours pinned
 * here are the ones the ADR calls load-bearing, not merely that the columns
 * exist: absolute totals over deltas, a stored Skipped row, provenance on
 * reference data, and enums rather than free text.
 */

it('stores energy, mood and fans as end-of-turn totals on a logged turn', function (): void {
    $run = TrainingRun::factory()->create();

    test()->post("/training-runs/{$run->id}/turns", [
        'turn' => 1, 'speed' => 100, 'stamina' => 90, 'power' => 110, 'guts' => 80, 'wit' => 95,
        'energy' => 78, 'mood' => 'GOOD', 'fans' => 41250,
    ])->assertRedirect();

    $entry = TurnEntry::sole();

    expect($entry->energy)->toBe(78)
        ->and($entry->mood)->toBe(MoodTier::Good)
        ->and($entry->fans)->toBe(41250);
});

it('leaves energy, mood and fans null when a run is logged without them', function (): void {
    $entry = TurnEntry::factory()->create();

    expect($entry->fresh()->energy)->toBeNull()
        ->and($entry->fresh()->mood)->toBeNull()
        ->and($entry->fresh()->fans)->toBeNull();
});

it('exposes exactly the five mood strings the client prints', function (): void {
    expect(array_map(static fn (MoodTier $tier): string => $tier->value, MoodTier::cases()))
        ->toBe(['GREAT', 'GOOD', 'NORMAL', 'BAD', 'AWFUL']);
});

it('rejects a mood the client never displays', function (): void {
    $run = TrainingRun::factory()->create();

    test()->post("/training-runs/{$run->id}/turns", [
        'turn' => 1, 'speed' => 10, 'stamina' => 10, 'power' => 10, 'guts' => 10, 'wit' => 10,
        'mood' => 'Peak',
    ])->assertSessionHasErrors('mood');
});

it('caps energy at the hundred point gauge rather than a stat ceiling', function (): void {
    $run = TrainingRun::factory()->create();

    test()->post("/training-runs/{$run->id}/turns", [
        'turn' => 1, 'speed' => 10, 'stamina' => 10, 'power' => 10, 'guts' => 10, 'wit' => 10,
        'energy' => 101,
    ])->assertSessionHasErrors('energy');
});

it('keeps the condition column working alongside mood', function (): void {
    $run = TrainingRun::factory()->create();

    test()->post("/training-runs/{$run->id}/turns", [
        'turn' => 1, 'speed' => 10, 'stamina' => 10, 'power' => 10, 'guts' => 10, 'wit' => 10,
        'condition' => 'Recovered after an injury', 'mood' => 'NORMAL',
    ])->assertRedirect();

    $entry = TurnEntry::sole();

    expect($entry->condition)->toBe('Recovered after an injury')
        ->and($entry->mood)->toBe(MoodTier::Normal);
});

it('casts a turn event type and its observed deltas', function (): void {
    $event = TurnEvent::factory()->supportCard()->create(['deltas' => ['energy' => -5, 'speed' => 12]]);

    expect($event->event_type)->toBe(TurnEventType::SupportCard)
        ->and($event->deltas)->toBe(['energy' => -5, 'speed' => 12]);
});

it('records a declined race as a stored row rather than an absent one', function (): void {
    $run = TrainingRun::factory()->create();
    $slot = ScenarioRace::factory()->create();

    RaceEntry::factory()->skipped()->create(['training_run_id' => $run->id, 'scenario_race_id' => $slot->id]);

    expect(RaceEntry::where('training_run_id', $run->id)->count())->toBe(1)
        ->and(RaceEntry::sole()->status)->toBe(RaceEntryStatus::Skipped);
});

it('allows a free-form race with no calendar slot', function (): void {
    $entry = RaceEntry::factory()->create(['scenario_race_id' => null]);

    expect($entry->fresh()->scenario_race_id)->toBeNull();
});

it('drops a run\'s events and race entries with it', function (): void {
    $run = TrainingRun::factory()->create();
    TurnEvent::factory()->create(['training_run_id' => $run->id]);
    RaceEntry::factory()->create(['training_run_id' => $run->id]);

    $run->delete();

    expect(TurnEvent::count())->toBe(0)
        ->and(RaceEntry::count())->toBe(0);
});

it('carries provenance on calendar reference data', function (): void {
    $slot = ScenarioRace::factory()->create();

    expect($slot->source_url)->toStartWith('https://')
        ->and($slot->fetched_at)->not->toBeNull()
        ->and($slot->source_timezone)->toBe('Asia/Tokyo');
});

it('survives deleting a calendar slot by clearing the reference', function (): void {
    $slot = ScenarioRace::factory()->create();
    $entry = RaceEntry::factory()->create(['scenario_race_id' => $slot->id]);

    $slot->delete();

    expect($entry->fresh()->scenario_race_id)->toBeNull();
});

it('lists scenario races for a run without needing a catalog row', function (): void {
    $run = TrainingRun::factory()->create();
    RaceEntry::factory()->create(['training_run_id' => $run->id, 'scenario_race_id' => null]);

    expect($run->raceEntries)->toHaveCount(1);
});
