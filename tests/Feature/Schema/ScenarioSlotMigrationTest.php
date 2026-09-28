<?php

declare(strict_types=1);

use App\Models\ScenarioSlot;
use Illuminate\Support\Facades\Schema;

/*
 * TDD for D-221 / ADR-0003: generalise scenario_races → scenario_slots
 *
 * The old `scenario_races` table was URA-shaped: every row was a race with
 * fan gates and maiden gates. Trackblazer has no race goals, and its finale
 * is a points league (not a bracket). Unity Cup has Team Races. Our Grand
 * Concert has no guide at all. The new `scenario_slots` table must carry a
 * `kind` discriminator so every scenario's timeline is composed from data,
 * not from branches in the code (D-240).
 */

it('has a scenario_slots table with the required columns', function (): void {
    expect(Schema::hasTable('scenario_slots'))->toBeTrue();

    $columns = Schema::getColumnListing('scenario_slots');

    expect($columns)
        ->toContain('id')
        ->toContain('scenario_key')
        ->toContain('kind')
        ->toContain('source_key')
        ->toContain('slot_label')
        ->toContain('title')
        ->toContain('description')
        ->toContain('month')
        ->toContain('half')
        ->toContain('tier')
        ->toContain('fans_needed')
        ->toContain('is_mandatory')
        ->toContain('is_maiden_gated')
        ->toContain('sort_order')
        ->toContain('source_url')
        ->toContain('snapshot_path')
        ->toContain('fetched_at')
        ->toContain('source_timezone')
        ->toContain('is_manual')
        ->toContain('created_at')
        ->toContain('updated_at');
});

it('stores kind as a constrained enum with the five canonical values', function (): void {
    // The enum is enforced at the application layer (model casts) and by a
    // CHECK constraint in the migration. The five kinds cover every timeline
    // slot type across all scenarios, plus Trainer-entered free races (R61).
    $kinds = ['goal_race', 'team_race', 'grade_deadline', 'scripted_event', 'free_race'];

    foreach ($kinds as $kind) {
        $slot = ScenarioSlot::factory()->create(['kind' => $kind]);
        expect($slot->kind)->toBe($kind);
    }
});

it('rejects an invalid kind at the model level', function (): void {
    ScenarioSlot::factory()->create(['kind' => 'invalid_kind']);
})->throws(InvalidArgumentException::class, 'Invalid kind');

it('supports a goal race (URA/Unity Cup mandatory races with fan gates)', function (): void {
    $slot = ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'goal_race',
        'slot_label' => 'G1',
        'title' => 'Tenno Sho (Spring)',
        'month' => 4,
        'half' => 'Late',
        'tier' => 'G1',
        'fans_needed' => 12000,
        'is_mandatory' => true,
        'is_maiden_gated' => false,
    ]);

    expect($slot->isGoalRace())->toBeTrue()
        ->and($slot->isTeamRace())->toBeFalse()
        ->and($slot->isGradeDeadline())->toBeFalse()
        ->and($slot->isScriptedEvent())->toBeFalse()
        ->and($slot->fans_needed)->toBe(12000)
        ->and($slot->is_mandatory)->toBeTrue();
});

it('supports a fan-locked race (URA/Unity Cup non-mandatory races with fan gates)', function (): void {
    $slot = ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'goal_race',
        'slot_label' => 'OP',
        'title' => 'Nakayama Kinen',
        'month' => 2,
        'half' => 'Early',
        'tier' => 'OP',
        'fans_needed' => 3500,
        'is_mandatory' => false,
        'is_maiden_gated' => false,
    ]);

    expect($slot->isGoalRace())->toBeTrue()
        ->and($slot->is_mandatory)->toBeFalse()
        ->and($slot->fans_needed)->toBe(3500);
});

it('supports a maiden-gated race', function (): void {
    $slot = ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'goal_race',
        'slot_label' => 'OP',
        'title' => 'Naruta Kinpa Cup',
        'month' => 5,
        'half' => 'Early',
        'tier' => 'OP',
        'fans_needed' => 0,
        'is_mandatory' => false,
        'is_maiden_gated' => true,
    ]);

    expect($slot->isGoalRace())->toBeTrue()
        ->and($slot->is_maiden_gated)->toBeTrue()
        ->and($slot->fans_needed)->toBe(0);
});

it('supports a Unity Cup Team Race slot', function (): void {
    $slot = ScenarioSlot::factory()->create([
        'scenario_key' => 'unity_cup',
        'kind' => 'team_race',
        'slot_label' => 'Round 1',
        'title' => 'Team Race · Round 1',
        'month' => 4,
        'half' => 'Late',
        'tier' => null,
        'fans_needed' => null,
        'is_mandatory' => false,
        'is_maiden_gated' => false,
    ]);

    expect($slot->isTeamRace())->toBeTrue()
        ->and($slot->isGoalRace())->toBeFalse()
        ->and($slot->fans_needed)->toBeNull();
});

it('supports a Trackblazer Grade Point deadline', function (): void {
    $slot = ScenarioSlot::factory()->create([
        'scenario_key' => 'trackblazer',
        'kind' => 'grade_deadline',
        'slot_label' => 'Junior',
        'title' => 'End of Junior Year',
        'month' => 12,
        'half' => 'Late',
        'tier' => null,
        'fans_needed' => null,
        'is_mandatory' => true,
        'is_maiden_gated' => false,
    ]);

    expect($slot->isGradeDeadline())->toBeTrue()
        ->and($slot->isGoalRace())->toBeFalse()
        ->and($slot->is_mandatory)->toBeTrue(); // deadline is mandatory
});

it('supports a scripted event (e.g., URA April Unique Skill level-up)', function (): void {
    $slot = ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'scripted_event',
        'slot_label' => 'Event',
        'title' => 'Unique Skill Level-Up Check',
        'month' => 4,
        'half' => 'Early',
        'tier' => null,
        'fans_needed' => null,
        'is_mandatory' => false,
        'is_maiden_gated' => false,
    ]);

    expect($slot->isScriptedEvent())->toBeTrue()
        ->and($slot->isGoalRace())->toBeFalse();
});

it('has a source_key column for distinguishing multiple slots in the same month-half', function (): void {
    expect(Schema::hasColumn('scenario_slots', 'source_key'))->toBeTrue();
});

it('has a unique index on (scenario_key, month, half, kind, source_key) to allow multiple races per half-month', function (): void {
    $indexes = Schema::getIndexes('scenario_slots');
    $hasUnique = false;

    foreach ($indexes as $index) {
        $cols = $index['columns'] ?? [];
        if ($cols === ['scenario_key', 'month', 'half', 'kind', 'source_key'] && ($index['unique'] ?? false) === true) {
            $hasUnique = true;
            break;
        }
    }

    expect($hasUnique)->toBeTrue('Unique composite index missing on (scenario_key, month, half, kind, source_key)');
});

it('allows multiple goal_race slots in the same month and half when source_key differs', function (): void {
    $base = [
        'scenario_key' => 'ura_finale',
        'kind' => 'goal_race',
        'month' => 8,
        'half' => 'Early',
        'tier' => 'OP',
        'fans_needed' => 350,
        'is_mandatory' => false,
        'is_maiden_gated' => false,
    ];

    ScenarioSlot::factory()->create(array_merge($base, [
        'slot_label' => 'OP', 'title' => 'Cosmos Sho', 'source_key' => 'cosmos-sho',
    ]));
    ScenarioSlot::factory()->create(array_merge($base, [
        'slot_label' => 'OP', 'title' => 'Dahlia Sho', 'source_key' => 'dahlia-sho',
    ]));
    ScenarioSlot::factory()->create(array_merge($base, [
        'slot_label' => 'OP', 'title' => 'Phoenix Sho', 'source_key' => 'phoenix-sho',
    ]));

    expect(ScenarioSlot::where('scenario_key', 'ura_finale')
        ->where('month', 8)
        ->where('half', 'Early')
        ->count())->toBe(3);
});

it('seeding the same three Early August races twice yields exactly three rows (idempotency)', function (): void {
    $races = [
        ['slot_label' => 'OP', 'title' => 'Cosmos Sho', 'source_key' => 'cosmos-sho'],
        ['slot_label' => 'OP', 'title' => 'Dahlia Sho', 'source_key' => 'dahlia-sho'],
        ['slot_label' => 'OP', 'title' => 'Phoenix Sho', 'source_key' => 'phoenix-sho'],
    ];

    $base = [
        'scenario_key' => 'ura_finale',
        'kind' => 'goal_race',
        'month' => 8,
        'half' => 'Early',
        'tier' => 'OP',
        'fans_needed' => 350,
        'is_mandatory' => false,
        'is_maiden_gated' => false,
    ];

    // Seed twice
    foreach ([1, 2] as $pass) {
        foreach ($races as $race) {
            ScenarioSlot::updateOrCreate(
                array_merge($base, ['source_key' => $race['source_key']]),
                $race,
            );
        }
    }

    expect(ScenarioSlot::where('scenario_key', 'ura_finale')
        ->where('month', 8)
        ->where('half', 'Early')
        ->count())->toBe(3);
});

it('has a foreign key from race_entries to scenario_slots (replacing scenario_races)', function (): void {
    $foreignKeys = Schema::getForeignKeys('race_entries');
    $hasFk = false;

    foreach ($foreignKeys as $fk) {
        $cols = $fk['columns'] ?? [];
        $ftable = $fk['foreign_table'] ?? '';
        if ($cols === ['scenario_slot_id'] && $ftable === 'scenario_slots') {
            $hasFk = true;
            break;
        }
    }

    expect($hasFk)->toBeTrue('FK to scenario_slots missing on race_entries');
});
