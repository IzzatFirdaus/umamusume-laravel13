<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\TrainingRun;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * O-12: the run page has no goals surface, and the client's header is the first thing a
 * Trainer reads each turn. The Grade Point meter is a Trackblazer-only points ladder and the
 * Race calendar is a year grid, so neither is the line "Place 1st in Arima Kinen, entry
 * criteria met, 5 turns, three cleared behind it" that the audit names.
 *
 * `docs/research-scratch/AUDIT-AND-VERIFICATION.md`, section `## UIX-AUDIT-TRAINING-RUNS.md` O-12 fixes this with a list of mandatory or special
 * race entries on the run, each with a state word. The report's data shape is three cleared
 * and one active for the run-7 fixture; the tests below reproduce that shape in factories.
 *
 * The goal line reaches the page as the `goals` prop: one row per mandatory or special entry,
 * each `{title, state, year_label, turn}`, where `state` is the word the screen prints (Cleared,
 * Active, Failed). The Goals landmark is `v-if="goals.length > 0"`, so its presence is the
 * prop being non-empty and its absence the prop being empty; the `aria-label` itself is the
 * component's and lives in the browser spec.
 */

function goalsRun(): TrainingRun
{
    $run = TrainingRun::factory()->create(['scenario' => null]);

    $slots = [
        RaceCatalogSlot::factory()->debut()->create(['sort_order' => 1]),
        RaceCatalogSlot::factory()->create([
            'title' => 'Japanese Derby',
            'year' => RaceCatalogSlot::YEAR_CLASSIC,
            'turn' => 16,
            'is_mandatory' => true,
            'is_special_race' => true,
        ]),
        RaceCatalogSlot::factory()->create([
            'title' => 'Arima Kinen',
            'year' => RaceCatalogSlot::YEAR_SENIOR,
            'turn' => 24,
            'is_mandatory' => true,
            'is_special_race' => true,
        ]),
        RaceCatalogSlot::factory()->create([
            'title' => 'Tenno Sho (Autumn)',
            'year' => RaceCatalogSlot::YEAR_SENIOR,
            'turn' => 20,
            'is_mandatory' => true,
            'is_special_race' => true,
        ]),
    ];

    // Three cleared, one active. The active slot has the earliest Senior turn so it sorts
    // above Arima Kinen, which makes the order assertion below non-trivial.
    foreach ([$slots[0], $slots[1], $slots[2]] as $slot) {
        RaceEntry::factory()->create([
            'training_run_id' => $run->id,
            'race_catalog_slot_id' => $slot->id,
            'status' => RaceEntryStatus::Completed,
            'placement' => 1,
        ]);
    }
    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $slots[3]->id,
        'status' => RaceEntryStatus::Entered,
    ]);

    return $run;
}

it('renders the three cleared and one active goals from the run 7 shape', function (): void {
    $run = goalsRun();

    // Each state word is counted within the goals list, so the count is about the panel: an
    // Active option in a status select or a Failed turn chip is a different thing and stays out
    // of `goals[].state`. Four rows is what makes the Goals landmark mount at all.
    test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->has('goals', 4)
            ->where('goals', fn (Collection $goals): bool => $goals->where('state', 'Cleared')->count() === 3
                && $goals->where('state', 'Active')->count() === 1
                && $goals->where('state', 'Failed')->count() === 0));
});

it('lists every mandatory and special race entry but not an optional one', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => null]);

    $mandatory = RaceCatalogSlot::factory()->create([
        'title' => 'Oka Sho',
        'is_mandatory' => true,
        'is_special_race' => false,
    ]);
    $special = RaceCatalogSlot::factory()->create([
        'title' => 'Arima Kinen',
        'is_mandatory' => false,
        'is_special_race' => true,
    ]);
    $optional = RaceCatalogSlot::factory()->create([
        'title' => 'Some Handicap',
        'is_mandatory' => false,
        'is_special_race' => false,
    ]);

    foreach ([$mandatory, $special, $optional] as $slot) {
        RaceEntry::factory()->create([
            'training_run_id' => $run->id,
            'race_catalog_slot_id' => $slot->id,
            'status' => RaceEntryStatus::Completed,
        ]);
    }

    test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('goals', fn (Collection $goals): bool => $goals->pluck('title')->contains('Oka Sho')
                && $goals->pluck('title')->contains('Arima Kinen')
                && ! $goals->pluck('title')->contains('Some Handicap')));
});

it('names a skipped mandatory goal as Failed and an unentered one as Active', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => null]);

    $skippedSlot = RaceCatalogSlot::factory()->create([
        'title' => 'Skipped Classic G1',
        'year' => RaceCatalogSlot::YEAR_CLASSIC,
        'is_mandatory' => true,
        'is_special_race' => true,
    ]);
    $enteredSlot = RaceCatalogSlot::factory()->create([
        'title' => 'Entered Senior G1',
        'year' => RaceCatalogSlot::YEAR_SENIOR,
        'is_mandatory' => true,
        'is_special_race' => true,
    ]);

    RaceEntry::factory()->skipped()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $skippedSlot->id,
    ]);
    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $enteredSlot->id,
        'status' => RaceEntryStatus::Entered,
    ]);

    test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->has('goals', 2)
            ->where('goals', fn (Collection $goals): bool => $goals->where('state', 'Failed')->count() === 1
                && $goals->where('state', 'Active')->count() === 1
                && $goals->where('state', 'Cleared')->count() === 0));
});

it('omits the Goals section when the run has no mandatory or special race entries', function (): void {
    // A fresh run has no logged race entries. The Goals section is absent, not "No goals yet",
    // because a Trainer watching a run they have just started does not need a panel asserting
    // what is already true. On the Inertia page the section is `v-if="goals.length > 0"`, so an
    // empty `goals` prop is exactly that absence; the section reappears when the first mandatory
    // or special entry is logged.
    $run = TrainingRun::factory()->create(['scenario' => null]);

    test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->has('goals', 0));
});
