<?php

declare(strict_types=1);

use App\Models\ScenarioSlot;
use Database\Seeders\ScenarioSlotSeeder;

it('seeds URA Finale goal_race rows from the committed client export', function (): void {
    $this->seed(ScenarioSlotSeeder::class);

    $slots = ScenarioSlot::where('scenario_key', 'ura_finale')
        ->where('kind', 'goal_race')
        ->get();

    expect($slots)->not->toBeEmpty()
        ->and($slots->count())->toBeGreaterThan(100);

    $first = $slots->first();
    expect($first->title)->not->toBeEmpty()
        ->and($first->month)->toBeBetween(1, 12)
        ->and($first->half)->toBeIn(['Early', 'Late'])
        ->and($first->tier)->not->toBeNull()
        ->and($first->source_key)->not->toBeNull()
        ->and($first->is_manual)->toBeFalse()
        ->and($first->source_url)->not->toBeNull()
        ->and($first->fetched_at)->not->toBeNull();
});

it('is idempotent: running twice produces the same row count', function (): void {
    $this->seed(ScenarioSlotSeeder::class);
    $firstCount = ScenarioSlot::where('scenario_key', 'ura_finale')->count();

    $this->seed(ScenarioSlotSeeder::class);
    $secondCount = ScenarioSlot::where('scenario_key', 'ura_finale')->count();

    expect($secondCount)->toBe($firstCount);
});

it('excludes races unreleased on Global English', function (): void {
    $this->seed(ScenarioSlotSeeder::class);

    $titles = ScenarioSlot::where('scenario_key', 'ura_finale')
        ->pluck('title')
        ->toArray();

    // Longchamp races are excluded from Global per calendar doc
    expect($titles)->not->toContain('Prix de l\'Arc de Triomphe');
});

it('excludes sentinel-month rows (month 99999)', function (): void {
    $this->seed(ScenarioSlotSeeder::class);

    $sentinels = ScenarioSlot::where('scenario_key', 'ura_finale')
        ->where('month', '>', 12)
        ->count();

    expect($sentinels)->toBe(0);
});

it('populates provenance columns on every seeded row', function (): void {
    $this->seed(ScenarioSlotSeeder::class);

    $missing = ScenarioSlot::where('scenario_key', 'ura_finale')
        ->where(function ($q): void {
            $q->whereNull('source_url')
                ->orWhereNull('snapshot_path')
                ->orWhereNull('fetched_at')
                ->orWhereNull('source_timezone');
        })
        ->count();

    expect($missing)->toBe(0);
});

it('seeds the Early August triple as three distinct rows', function (): void {
    $this->seed(ScenarioSlotSeeder::class);

    $augustEarly = ScenarioSlot::where('scenario_key', 'ura_finale')
        ->where('month', 8)
        ->where('half', 'Early')
        ->where('kind', 'goal_race')
        ->get();

    expect($augustEarly->count())->toBeGreaterThanOrEqual(3);

    $titles = $augustEarly->pluck('title')->toArray();
    expect($titles)->toContain('Cosmos Sho')
        ->and($titles)->toContain('Dahlia Sho')
        ->and($titles)->toContain('Phoenix Sho');
});
