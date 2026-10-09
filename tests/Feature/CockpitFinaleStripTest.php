<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Slice 22, the finale inside the cockpit's race strip (`docs/Our-Grand-Concert-plan.md` §9 E7,
 * `SCREEN_SPEC.md` SCR-CAR-011).
 *
 * The strip was not built here. D14a built it, and `RunRaceStripTest` already owns the three regions,
 * the ordering, the goal-race absence and the no-prediction pin, including one case that puts a
 * `YEAR_FINALE` row in `ahead`. What nothing pinned is the one thing this scenario added: the cockpit
 * now carries the finale twice over, once as a row in `raceStrip` and once as `scenario.finale_state`
 * read through `FinaleReader`, and the two are produced by two different queries over the same table.
 * These cases hold them to the same race.
 *
 * They match by catalogue id rather than by title on purpose. KI-82 records that the Grand Concert
 * row's stored title is a borrowed URA name, and KI-87 that the reader prints one client noun for all
 * four scenarios, so a title assertion here would be an assertion about a known defect and would have
 * to move when either closes. The id is the fact both surfaces agree on today.
 */

/** A run on `$scenario` with `$turns` logged turns, so `nextTurnToPlay()` reads the turn after. */
function finaleStripRun(int $turns = 12, string $scenario = 'unity_cup'): TrainingRun
{
    $run = TrainingRun::factory()->create([
        'scenario' => $scenario,
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->state(['name' => 'Rice Shower', 'name_ja' => 'ライスシャワー']),
    ]);

    for ($turn = 1; $turn <= $turns; $turn++) {
        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => $turn]);
    }

    return $run->fresh();
}

/**
 * One mandatory finale-year catalogue row. `scenario_key` null is the shared case: the seed corpus
 * holds two of those (`URA Finals Qualifier` and `URA Finals Semifinal`), and the strip's
 * `forScenario` scope reads them while `FinaleReader` does not.
 */
function finaleStripSlot(?string $scenarioKey, string $title): RaceCatalogSlot
{
    return RaceCatalogSlot::factory()->create([
        'scenario_key' => $scenarioKey,
        'year' => RaceCatalogSlot::YEAR_FINALE,
        'month' => null,
        'half' => $scenarioKey === null ? 'Early' : null,
        'turn' => null,
        'title' => $title,
        'tier' => 'G1',
        'distance' => 2400,
        'distance_band' => 'Long',
        'surface' => 'Turf',
        'is_mandatory' => true,
        'sort_order' => 1,
    ]);
}

it('lists the finale row the reader names in Still to come, and calls it reached in neither', function (): void {
    $run = finaleStripRun();
    $finale = finaleStripSlot('unity_cup', 'URA Finals Final (Aoharu)');

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('raceStrip.shown', true)
            ->has('raceStrip.ahead', 1)
            ->where('raceStrip.ahead.0.id', $finale->id)
            ->where('raceStrip.ahead.0.year_label', 'Finale')
            // One race, one verdict: the strip still has it coming and the reader agrees it has not
            // arrived. Either surface reading this the other way is the disagreement these cases guard.
            ->where('scenario.finale_state.reached', false)
            ->where('scenario.finale_state.outcome', null));
});

it('keeps the strip and the reader on one row once the finale is recorded', function (): void {
    $run = finaleStripRun();
    $finale = finaleStripSlot('unity_cup', 'URA Finals Final (Aoharu)');
    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $finale->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
        'turn_entry_id' => $run->turnEntries()->where('turn', 12)->firstOrFail()->id,
    ]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // It left Still to come the moment it was entered, and it is in Run exactly once.
            ->has('raceStrip.ahead', 0)
            ->has('raceStrip.run', 1)
            ->where('raceStrip.run.0.id', $finale->id)
            ->where('raceStrip.run.0.status_label', RaceEntryStatus::Completed->label())
            ->where('raceStrip.run.0.placement', '1st')
            ->where('raceStrip.run.0.turn', 12)
            // The reader reports the same row's same outcome, placement and turn.
            ->where('scenario.finale_state.outcome', RaceEntryStatus::Completed->label())
            ->where('scenario.finale_state.placement', '1st')
            ->where('scenario.finale_state.turn', 12));
});

it('holds the reader to the scenario\'s own finale row while the strip carries the shared ones too', function (): void {
    $run = finaleStripRun();
    $shared = finaleStripSlot(null, 'URA Finals Qualifier');
    $finale = finaleStripSlot('unity_cup', 'URA Finals Final (Aoharu)');

    // The shared row is a real obligation and the strip is right to list it: `forScenario` reads
    // `scenario_key IS NULL OR scenario_key = ?`. The reader asks `scenario_key = ?` alone, so this
    // run can finish the shared race and still be told its finale has no outcome. Pinned as it stands,
    // because the two readings are a scope decision (`ADR-0015`) and not this slice's to make; KI-87
    // carries the question of which one the finale is.
    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $shared->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 3,
        'turn_entry_id' => $run->turnEntries()->where('turn', 12)->firstOrFail()->id,
    ]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('raceStrip.ahead', 1)
            ->where('raceStrip.ahead.0.id', $finale->id)
            ->has('raceStrip.run', 1)
            ->where('raceStrip.run.0.id', $shared->id)
            // The recorded race and the finale the reader names are different rows, and the payload
            // says so without contradiction: the strip's Run region holds the shared row, and the
            // finale state stays empty because its own row was never entered.
            ->where('scenario.finale_state.outcome', null)
            ->where('scenario.finale_state.reached', false));
});

it('reaches the finale on an exhausted grid while the strip reports no turn being decided', function (): void {
    // Seventy-two logged turns is the whole grid, so `nextTurnToPlay()` answers null. The reader
    // substitutes the grid's last instant and calls the block arrived; the strip's own sentence says
    // nothing can be placed ahead of a run with no turn to decide. Both readings are the documented
    // ones and this case is what keeps them from being silently unified by a refactor.
    $run = finaleStripRun(turns: TrainingRun::TURNS_PER_YEAR * 3);
    $finale = finaleStripSlot('unity_cup', 'URA Finals Final (Aoharu)');

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('raceStrip.this_turn', null)
            ->has('raceStrip.ahead', 0)
            ->where('raceStrip.empty.ahead', fn (string $line): bool => str_contains($line, 'no turn being decided'))
            ->where('scenario.finale_state.reached', true)
            ->where('scenario.finale_state.outcome', null)
            // Pinned as a defect, not as a target: this is a Unity Cup career and the word the three
            // finale surfaces print is Our Grand Concert's noun, because `FinaleReader` returns one
            // label for every scenario (`FinaleReader.php:69`, its own `ponytail:` note). The day
            // KI-87 closes this line moves to the row's own title and nothing else here changes.
            ->where('scenario.finale_state.label', 'Grand Concert')
            // The row the reader is describing is still the scenario's own, and it is not in Run: a
            // reached finale with nothing recorded is the middle answer, not a completed one.
            ->has('raceStrip.run', 0));
});

it('carries a finale state on a scenario whose matrix closes the race calendar', function (): void {
    // Our Grand Concert composes no race calendar, so the strip is switched off and says why, while
    // the catalogue still holds its finale row and the baseline row still prints it. One is a
    // composition decision in `config/scenarios.php` and the other a read of `race_catalog_slots`;
    // the payload keeps both, which is what stops the shell from having to guess.
    $run = finaleStripRun(scenario: 'our_grand_concert');
    finaleStripSlot('our_grand_concert', 'URA Finals Final (Grand Live)');

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('raceStrip.shown', false)
            ->has('raceStrip.ahead', 0)
            ->where('raceStrip.notice', fn (string $line): bool => str_contains($line, 'composes no race calendar'))
            ->where('scenario.finale_state.reached', false)
            ->where('scenario.finale_state.label', 'Grand Concert')
            // The two `finale` keys on one payload are not the same question, and only one of them is
            // answered here: `scenario.finale` is what the config declares the scenario composes, and
            // `ura_finale` alone declares it (`config/scenarios.php:358`). The strip's absence is
            // therefore never read as the finale's absence, because the state that does describe this
            // run's finale is the other key. On the Career Result payload the same reader output is
            // called `finale`, which is the collision Slice 23 renames rather than a defect to keep.
            ->where('scenario.finale', null));
});
