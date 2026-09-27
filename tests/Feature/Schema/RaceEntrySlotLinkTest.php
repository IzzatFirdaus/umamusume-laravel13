<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Models\RaceEntry;
use App\Models\ScenarioRace;
use App\Models\ScenarioSlot;
use App\Models\TrainingRun;
use Illuminate\Support\Facades\Schema;

/*
 * D-221 / ADR-0003: the link between what a Trainer did in one turn and the
 * timeline slot it belonged to.
 *
 * `scenario_slot_id` stays nullable, and these tests are the reason. Trackblazer
 * has no fixed race list — the Trainer picks the races and the Grade Points
 * derive from that log — so a free-form race entry has no calendar slot to point
 * at. A NOT NULL column would make the only kind of race entry that scenario
 * can produce impossible to store.
 */

it('mass-assigns a scenario slot onto a race entry', function (): void {
    $run = TrainingRun::factory()->create();
    $slot = ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'goal_race',
        'month' => 4,
        'half' => 'Late',
    ]);

    // Built through the attributes a caller holds, not the factory: factories
    // create inside Model::unguarded(), so a factory never proves a column is
    // writable by the code that will actually write it.
    $entry = RaceEntry::create([
        'training_run_id' => $run->id,
        'scenario_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Entered,
    ]);

    expect($entry->fresh()->scenario_slot_id)->toBe($slot->id)
        ->and($entry->scenarioSlot->title)->toBe($slot->title);
});

it('links a race entry to a slot without fabricating a retired calendar race', function (): void {
    $run = TrainingRun::factory()->create();
    $slot = ScenarioSlot::factory()->create();

    RaceEntry::create([
        'training_run_id' => $run->id,
        'scenario_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Entered,
    ]);

    expect(ScenarioRace::count())->toBe(0);
});

it('stores a Trackblazer race the Trainer picked, with no slot and no calendar race', function (): void {
    $run = TrainingRun::factory()->create();

    $entry = RaceEntry::create([
        'training_run_id' => $run->id,
        'scenario_slot_id' => null,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
        'fans_gain' => 350,
    ]);

    expect($entry->fresh()->scenario_slot_id)->toBeNull()
        ->and($entry->scenarioSlot)->toBeNull()
        ->and($entry->placement)->toBe(1);
});

it('cannot backfill scenario_races into scenario_slots because the old table has no month, half or kind', function (): void {
    // The three columns scenario_slots needs as NOT NULL do not exist on the
    // table being retired. `slot_label` is free text such as
    // 'Classic Year Late May'; reading a month and a half out of it would be
    // inventing client data (D-20), and guessing a `kind` would decide the
    // discriminator the new table exists to carry.
    $columns = Schema::getColumnListing('scenario_races');

    expect($columns)->not->toContain('month')
        ->not->toContain('half')
        ->not->toContain('kind')
        ->and($columns)->toContain('slot_label');
});

it('leaves scenario_races unpopulated in a seeded install, so a backfill would move nothing', function (): void {
    // Every seeder this app runs is listed in DatabaseSeeder. None writes a
    // scenario_races row, so real installs hold zero rows to migrate.
    $seeders = glob(base_path('database/seeders/*.php')) ?: [];

    $writesScenarioRaces = array_filter($seeders, function (string $path): bool {
        return (bool) preg_match('/scenario_races|ScenarioRace/', (string) file_get_contents($path));
    });

    expect($writesScenarioRaces)->toBe([]);
});
