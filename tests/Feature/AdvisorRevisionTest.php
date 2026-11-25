<?php

use App\Enums\EnergyState;
use App\Enums\RunStatus;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Services\Advisor\AdvisorAnalysis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/*
 * Workstream C verification: the revision fingerprint's inputs and the cache behaviour that
 * depends on it. This file was created by Hermes' 2026-11-23 support session to pin the
 * guarantee DSH's stale-response protection leans on: which mutations change the revision and
 * which do not, and that an in-flight cached payload is never served after a decision-relevant
 * edit within the same turn.
 */

/**
 * A run with a full build target and one recorded turn — the shape a real analysis has.
 */
function revisionRun(array $turn = [], array $attributes = []): TrainingRun
{
    $run = TrainingRun::factory()->create(array_merge([
        'scenario' => 'unity_cup',
        'build_target' => [
            'purpose' => 'StoryClear',
            'distance' => 'Medium',
            'surface' => 'Turf',
            'style' => 'Pace Chaser',
            'targets' => ['Speed' => 900, 'Stamina' => 800, 'Power' => 700, 'Guts' => 600, 'Wit' => 500],
            'skill_priorities' => [],
            'hint_levels' => [],
        ],
    ], $attributes));

    TurnEntry::factory()->create(array_merge([
        'training_run_id' => $run->id,
        'turn' => 1,
        'speed' => 500,
        'stamina' => 500,
        'power' => 500,
        'guts' => 500,
        'wit' => 500,
        'energy' => 60,
        'energy_state' => EnergyState::Exact,
        'mood' => 'NORMAL',
        'sp' => 300,
    ], $turn));

    return $run->fresh(['turnEntries']);
}

function latestTurn(TrainingRun $run): TurnEntry
{
    return $run->turnEntries->sortByDesc('turn')->first();
}

it('keeps the revision stable while nothing decision-relevant changes', function (): void {
    $run = revisionRun();

    $first = app(AdvisorAnalysis::class)->analyze($run, latestTurn($run));
    $second = app(AdvisorAnalysis::class)->analyze($run, latestTurn($run));

    expect($second['career_revision'])->toBe($first['career_revision'])
        // A second call is served from the cache entry the first wrote, so the byte-level
        // `generated_at` stamp is identical too.
        ->and($second['generated_at'])->toBe($first['generated_at']);
});

it('changes the revision when a decision-relevant reading is edited within the same turn', function (): void {
    $run = revisionRun();
    $before = app(AdvisorAnalysis::class)->analyze($run, latestTurn($run));

    // The same turn number, edited reading: the case a turn-number check alone cannot catch.
    latestTurn($run)->update(['energy' => 35]);

    $fresh = $run->fresh(['turnEntries']);
    $after = app(AdvisorAnalysis::class)->analyze($fresh, latestTurn($fresh));

    expect($after['career_revision'])->not->toBe($before['career_revision'])
        ->and($after['turn'])->toBe($before['turn']);
});

it('changes the revision when the build target changes within the same turn', function (): void {
    $run = revisionRun();
    $before = app(AdvisorAnalysis::class)->analyze($run, latestTurn($run));

    $run->update(['build_target' => [
        'purpose' => 'StoryClear',
        'distance' => 'Medium',
        'surface' => 'Turf',
        'style' => 'Pace Chaser',
        'targets' => ['Speed' => 900, 'Stamina' => 800, 'Power' => 700, 'Guts' => 600, 'Wit' => 900],
        'skill_priorities' => [],
        'hint_levels' => [],
    ]]);

    $after = app(AdvisorAnalysis::class)->analyze($run->fresh(), latestTurn($run));

    expect($after['career_revision'])->not->toBe($before['career_revision']);
});

it('changes the revision when the run status changes', function (): void {
    $run = revisionRun();
    $baseline = app(AdvisorAnalysis::class)->analyze($run, latestTurn($run));

    $run->update(['status' => RunStatus::Completed]);
    $statusChanged = app(AdvisorAnalysis::class)->analyze($run->fresh(), latestTurn($run));

    expect($statusChanged['career_revision'])->not->toBe($baseline['career_revision']);
});

it('refuses a scenario change on a run that has recorded a turn (C05)', function (): void {
    // C05: scenario is immutable once a turn is recorded (`TrainingRun::booted()`
    // saving guard + `ScenarioImmutabilityTest`). The revision fingerprint reads
    // the scenario label, so a scenario change *would* move it — but that branch is
    // now unreachable on a run with progress. This pins the guard rather than the
    // old "scenario changed, revision moved" behaviour the immutability rule superseded.
    $run = revisionRun();

    expect(fn () => $run->update(['scenario' => 'ura_finale']))
        ->toThrow(RuntimeException::class, 'Cannot change scenario on run');
});

it('does not change the revision when only a non-decision field like notes is edited', function (): void {
    $run = revisionRun();
    $before = app(AdvisorAnalysis::class)->analyze($run, latestTurn($run));

    $run->update(['notes' => 'A note the Trainer wrote mid-run.']);

    $after = app(AdvisorAnalysis::class)->analyze($run->fresh(), latestTurn($run));

    expect($after['career_revision'])->toBe($before['career_revision']);
});

it('does not change the revision when a race entry is recorded against the calendar', function (): void {
    // Obligations reach the analysis through the controller's calendar read, not the run row, so a
    // recorded race does not move the fingerprint. This is the same-turn limitation: the revision
    // covers the run row and the latest turn's readings only.
    $run = revisionRun();
    $before = app(AdvisorAnalysis::class)->analyze($run, latestTurn($run));

    // A recorded race against a real calendar slot. The slot is seeded here rather than skipped
    // when absent, so the assertion always runs instead of degrading to a no-op skip.
    $slot = RaceCatalogSlot::factory()->create();

    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $slot->id,
    ]);

    $after = app(AdvisorAnalysis::class)->analyze($run->fresh(['turnEntries', 'raceEntries']), latestTurn($run));

    expect($after['career_revision'])->toBe($before['career_revision']);
});

it('serves the cached payload only while its revision still matches the snapshot', function (): void {
    config(['advisor_ai.enabled' => false]);

    $run = revisionRun();
    $first = app(AdvisorAnalysis::class)->analyze($run, latestTurn($run));

    // Mutate the turn in place: the new fingerprint must miss the old cache entry by construction.
    latestTurn($run)->update(['energy' => 20]);

    $fresh = $run->fresh(['turnEntries']);
    $second = app(AdvisorAnalysis::class)->analyze($fresh, latestTurn($fresh));

    // The revision is the freshness guarantee: the edited turn must not be served the old entry.
    expect($second['career_revision'])->not->toBe($first['career_revision']);

    // The old entry stays as an untouchable artifact keyed by its own revision; the new state has
    // its own entry. No expiry or invalidation write exists to race with. (The two payloads can
    // share `generated_at` when produced within the same second — the revision, not the stamp,
    // is the freshness guarantee.)
    $key = 'advisor-ai:'.AdvisorAnalysis::CONTRACT_VERSION.":{$run->id}:{$first['career_revision']}";
    expect(Cache::get($key)['career_revision'])->toBe($first['career_revision']);
});

it('answers a turn-2 analysis for turn 1 state with the turn-1 revision when a newer turn exists', function (): void {
    // The snapshot analyses the *next* turn against the latest readings: `turn` is
    // nextTurnNumber(). A turn logged after the analysis started is exactly the case the
    // frontend must reject by comparing `career_revision`/`turn`, because an in-flight response
    // was computed for the older turn and the backend will not retract it.
    $run = revisionRun();
    Http::fake();
    config(['advisor_ai.embeddings.enabled' => false]);

    $before = app(AdvisorAnalysis::class)->analyze($run, latestTurn($run));
    expect($before['turn'])->toBe(2);

    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 2,
        'speed' => 520,
        'energy' => 55,
        'energy_state' => EnergyState::Exact,
        'mood' => 'NORMAL',
    ]);

    $fresh = $run->fresh(['turnEntries']);
    $after = app(AdvisorAnalysis::class)->analyze($fresh, latestTurn($fresh));

    expect($after['turn'])->toBe(3)
        ->and($after['career_revision'])->not->toBe($before['career_revision']);
});
