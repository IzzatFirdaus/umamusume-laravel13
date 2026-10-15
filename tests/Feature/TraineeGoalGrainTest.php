<?php

declare(strict_types=1);

use App\Actions\StoreTraineeGoals;
use App\Models\RaceCatalogSlot;
use App\Models\TraineeGoal;
use App\Models\Umamusume;
use Illuminate\Database\QueryException;

test('allows two rows differing only in source_race_choice (branch case)', function (): void {
    $trainee = Umamusume::factory()->create();
    $a = TraineeGoal::factory()->create([
        'umamusume_id' => $trainee->id,
        'source_cond_id' => 81,
        'turn' => 54,
        'source_race_choice' => 1,
    ]);
    $b = TraineeGoal::factory()->create([
        'umamusume_id' => $trainee->id,
        'source_cond_id' => 81,
        'turn' => 54,
        'source_race_choice' => 2,
    ]);

    expect(TraineeGoal::query()->count())->toBe(2)
        ->and($a->source_race_choice)->not->toBe($b->source_race_choice);
});

test('allows two rows differing only in turn', function (): void {
    $trainee = Umamusume::factory()->create();
    TraineeGoal::factory()->create([
        'umamusume_id' => $trainee->id,
        'source_cond_id' => 81,
        'turn' => 54,
        'source_race_choice' => 0,
    ]);
    TraineeGoal::factory()->create([
        'umamusume_id' => $trainee->id,
        'source_cond_id' => 81,
        'turn' => 55,
        'source_race_choice' => 0,
    ]);

    expect(TraineeGoal::query()->count())->toBe(2);
});

test('allows two rows differing only in source_cond_id', function (): void {
    $trainee = Umamusume::factory()->create();
    TraineeGoal::factory()->create([
        'umamusume_id' => $trainee->id,
        'source_cond_id' => 81,
        'turn' => 54,
        'source_race_choice' => 0,
    ]);
    TraineeGoal::factory()->create([
        'umamusume_id' => $trainee->id,
        'source_cond_id' => 82,
        'turn' => 54,
        'source_race_choice' => 0,
    ]);

    expect(TraineeGoal::query()->count())->toBe(2);
});

test('enforces unique constraint on (umamusume_id, source_cond_id, turn, source_race_choice)', function (): void {
    $trainee = Umamusume::factory()->create();
    TraineeGoal::factory()->create([
        'umamusume_id' => $trainee->id,
        'source_cond_id' => 81,
        'turn' => 54,
        'source_race_choice' => 0,
    ]);

    expect(fn (): TraineeGoal => TraineeGoal::factory()->create([
        'umamusume_id' => $trainee->id,
        'source_cond_id' => 81,
        'turn' => 54,
        'source_race_choice' => 0,
    ]))->toThrow(QueryException::class);
});

test('cascades when umamusume is deleted', function (): void {
    $trainee = Umamusume::factory()->create();
    TraineeGoal::factory()->create([
        'umamusume_id' => $trainee->id,
        'source_cond_id' => 81,
        'turn' => 54,
        'source_race_choice' => 0,
    ]);

    expect(TraineeGoal::query()->count())->toBe(1);
    $trainee->delete();
    expect(TraineeGoal::query()->count())->toBe(0);
});

test('sets race_catalog_slot_id to null when slot is deleted (nullOnDelete)', function (): void {
    $trainee = Umamusume::factory()->create();
    $slot = RaceCatalogSlot::factory()->create();
    $goal = TraineeGoal::factory()->create([
        'umamusume_id' => $trainee->id,
        'race_catalog_slot_id' => $slot->id,
        'source_cond_id' => 81,
        'turn' => 54,
        'source_race_choice' => 0,
    ]);

    expect($goal->race_catalog_slot_id)->not->toBeNull();
    $slot->delete();
    $goal->refresh();
    expect($goal->race_catalog_slot_id)->toBeNull();
});

test('does not overwrite manual rows when StoreTraineeGoals runs', function (): void {
    $trainee = Umamusume::factory()->create(['external_ref' => 'gametora:char:1030']);

    $record = [
        'char_ref' => 'gametora:char:1030',
        'goal_type' => 'Placement',
        'required_placement' => 3,
        'fan_threshold' => null,
        'career_year' => 3,
        'turn' => 54,
        'source_order' => 5,
        'source_race_choice' => 0,
        'source_cond_id' => 81,
        'source_cond_type' => 1,
        'source_cond_value' => 3,
        'source_cond_value_2' => 0,
        'source_target_type' => 1,
        'source_race_type' => 0,
        'source_race_name' => null,
        'global_unreleased' => false,
        'race_slot' => null,
    ];

    app(StoreTraineeGoals::class)->handle([$record], 'https://example.com/x.json', 'snapshots/x.json', 'Asia/Tokyo');
    $goal = TraineeGoal::query()->firstOrFail();
    $goal->update(['is_manual' => true, 'required_placement' => 1]);

    $counts = app(StoreTraineeGoals::class)->handle([$record], 'https://example.com/x.json', 'snapshots/x.json', 'Asia/Tokyo');

    expect($counts['skipped'])->toBe(1)
        ->and(TraineeGoal::find($goal->id)->required_placement)->toBe(1);
});