<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use Illuminate\Support\Facades\DB;

/*
 * The cockpit read, pinned as a query budget (QUALITY roadmap S5.7).
 *
 * S5.7 asks for a measured ceiling on the two reads a Trainer makes constantly: the catalogue index
 * (`CatalogRosterTreeTest`) and this screen. The catalogue side already pins four shape queries; the
 * cockpit side is the one this file owns. Like the catalogue pin, it asserts a *shape* rather than a
 * duration: CONSTRAINTS.md keeps C-6's 200 ms as a manual benchmark, so no millisecond figure is
 * invented here.
 *
 * What the pin catches: `CockpitController::show()` eager-loads every relation the strip and the
 * advisor read (`umamusume`, `characterCard`, `turnEntries`, `raceEntries` with their three links,
 * and `deckSlots.supportCard`). Should any of those regress to a per-row load, the count below grows
 * with the number of turns or races on the run, and the second case — the same budget at 40 turns as
 * at 12 — fails while the absolute ceiling might still pass.
 */

/**
 * A run with `$turns` logged turns and a short race log.
 *
 * `scenario` defaults to Unity Cup because it composes the widest panel set the cockpit reads:
 * its `team_rank_ladder` panel and its `spirit_bursts` widget both make `show()` join `turnEvents`,
 * so the fixture exercises the fullest load path rather than the narrowest one.
 */
function budgetRun(int $turns = 0, string $scenario = 'unity_cup'): TrainingRun
{
    $run = TrainingRun::factory()->create([
        'scenario' => $scenario,
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->state(['name' => 'Rice Shower', 'name_ja' => 'ライスシャワー']),
    ]);

    for ($turn = 1; $turn <= $turns; $turn++) {
        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => $turn]);
    }

    // The catalogue's shared grain is (scenario_key, year, month, half, turn, title), so two fixtures
    // in one test need distinct titles rather than the factory's own 'Oka Sho'.
    $slot = RaceCatalogSlot::factory()->create(['title' => 'Budget Stakes '.$run->id]);

    foreach ([1, 2, 3] as $index) {
        $turnEntry = TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 100 + $index]);

        RaceEntry::factory()->create([
            'training_run_id' => $run->id,
            'race_catalog_slot_id' => $slot->id,
            'turn_entry_id' => $turnEntry->id,
            'status' => RaceEntryStatus::Entered,
            'placement' => $index,
        ]);
    }

    return $run;
}

/**
 * The queries the shell owns, excluded exactly as `CatalogRosterTreeTest` excludes them: the stored
 * theme and settings the Blade shell and `HandleInertiaRequests` read on every screen, and the
 * active-career count beside them. The cockpit must not grow one for any of them.
 */
function shellQuery(array $query): bool
{
    return str_contains($query['query'], 'from "preferences"')
        || str_contains($query['query'], 'from "training_runs" where "status"');
}

it('reads the cockpit in a fixed number of queries, not one per turn or race', function (): void {
    $run = budgetRun(turns: 12);

    // Warm the read once: the first request pays whatever compiles and caches, and the budget is the
    // steady state a Trainer sees on every turn.
    $this->get(route('runs.cockpit', $run))->assertOk();

    DB::flushQueryLog();
    DB::enableQueryLog();
    $this->get(route('runs.cockpit', $run))->assertOk();
    $log = DB::getQueryLog();

    $cockpit = array_values(array_filter(
        $log,
        static fn (array $query): bool => ! shellQuery($query),
    ));

    // Twenty-nine cockpit-owned queries at twelve logged turns, and the same number at forty: the
    // run fetch, the eager loads (`umamusume`, `characterCard`, `turnEntries`, `race_entries` and its
    // three links, `deck_slots`), the joined `turn_events`, the calendar/goal/position passes behind
    // the scenario section, and the C05 `can_change_scenario` existence check the run section adds.
    // None of them keys off how many turns or races the run holds, which is the regression this file
    // exists to catch.
    expect(count($cockpit))->toBe(29)
        // Four belong to the shell — three preference reads and the active-career count — exactly as
        // `CatalogRosterTreeTest` records for the catalogue index. A page that grew a turn must not
        // grow one of them either.
        ->and(count($log))->toBe(33);
})->group('perf');

it('holds the same query budget when the turn log is longer', function (): void {
    $short = budgetRun(turns: 12);
    $long = budgetRun(turns: 40);

    $this->get(route('runs.cockpit', $short))->assertOk();
    $this->get(route('runs.cockpit', $long))->assertOk();

    DB::flushQueryLog();
    DB::enableQueryLog();
    $this->get(route('runs.cockpit', $short))->assertOk();
    $shortQueries = array_values(array_filter(
        DB::getQueryLog(),
        static fn (array $query): bool => ! shellQuery($query),
    ));

    DB::flushQueryLog();
    DB::enableQueryLog();
    $this->get(route('runs.cockpit', $long))->assertOk();
    $longQueries = array_values(array_filter(
        DB::getQueryLog(),
        static fn (array $query): bool => ! shellQuery($query),
    ));

    // Twelve turns and forty land on the same twenty-nine, so no part of the read scales with the
    // length of the log rather than merely with the number of races on it.
    expect(count($shortQueries))->toBe(29)
        ->and(count($longQueries))->toBe(29);
})->group('perf');
