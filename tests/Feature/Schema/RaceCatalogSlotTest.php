<?php

declare(strict_types=1);

use App\Models\RaceCatalogSlot;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

/**
 * Schema and model coverage for the race catalogue read path.
 *
 * The grain test below is the reason this table exists rather than a column being
 * added to `scenario_slots`: one turn carries many races, and Early December in the
 * Classic year carries twelve of them (docs/scenarios/09-global-race-calendar.md).
 *
 * Measurement note, because it cost a false report once: Laravel's `Builder::where()`
 * mutates in place. Reusing one builder across a year loop scopes every later count
 * by the earlier `where`, which reads as "zero rows for year 3" against good data.
 * Build a fresh query per assertion.
 */
it('creates race_catalog_slots with the columns the grid and picker both read', function (): void {
    expect(Schema::hasTable('race_catalog_slots'))->toBeTrue()
        ->and(Schema::hasColumns('race_catalog_slots', [
            'scenario_key', 'year', 'month', 'half', 'turn', 'slot_label',
            'title', 'tier', 'grade_code', 'distance', 'distance_band', 'surface',
            'track_id', 'fans_needed', 'fans_gain_curve', 'is_mandatory',
            'source_url', 'snapshot_path', 'fetched_at', 'source_timezone', 'is_manual',
        ]))->toBeTrue();
});

it('stores scenario_key as null for a shared slot, not as an empty string', function (): void {
    $slot = RaceCatalogSlot::factory()->create();

    expect($slot->fresh()->scenario_key)->toBeNull();
});

it('collapses duplicate shared rows even though the unique index spans a null column', function (): void {
    RaceCatalogSlot::factory()->create(['title' => 'Oka Sho', 'year' => 2, 'month' => 4, 'half' => 'Early']);

    // SQLite treats nulls as distinct inside a plain unique index, so the index is
    // built over IFNULL() expressions instead. Without that, this insert would
    // silently succeed and the "one race per turn per scenario" rule would not exist.
    expect(fn (): array => RaceCatalogSlot::factory()->create([
        'title' => 'Oka Sho', 'year' => 2, 'month' => 4, 'half' => 'Early',
    ])->toArray())->toThrow(QueryException::class);
});

it('lets the same race sit in two career years at the same turn', function (): void {
    // Fourteen G1s recur across Classic and Senior at an identical month and half.
    // A key without `year` would reject the second one.
    RaceCatalogSlot::factory()->create(['title' => 'Yasuda Kinen', 'year' => 2, 'month' => 6, 'half' => 'Early', 'turn' => 11]);
    RaceCatalogSlot::factory()->create(['title' => 'Yasuda Kinen', 'year' => 3, 'month' => 6, 'half' => 'Early', 'turn' => 11]);

    expect(RaceCatalogSlot::where('title', 'Yasuda Kinen')->count())->toBe(2);
});

it('holds every race of a turn that the game fills with more than one', function (): void {
    foreach (range(1, 12) as $n) {
        RaceCatalogSlot::factory()->create([
            'title' => "December Race {$n}",
            'year' => 2,
            'month' => 12,
            'half' => 'Early',
            'turn' => 23,
        ]);
    }

    expect(RaceCatalogSlot::onTurn(2, 23)->count())->toBe(12);
});

it('separates a scenario final from the shared rows without duplicating it', function (): void {
    RaceCatalogSlot::factory()->scenarioFinal('ura_finale')->create(['external_ref' => 'final']);
    RaceCatalogSlot::factory()->scenarioFinal('unity_cup')->create(['external_ref' => 'final_aoharu']);

    expect(RaceCatalogSlot::forScenario('ura_finale')->where('year', 4)->pluck('scenario_key')->all())
        ->toBe(['ura_finale'])
        ->and(RaceCatalogSlot::forScenario('unity_cup')->where('year', 4)->count())->toBe(1);
});

it('offers a shared slot to every scenario and a final to only its own', function (): void {
    $shared = RaceCatalogSlot::factory()->create(['title' => 'Oka Sho']);
    $uraFinal = RaceCatalogSlot::factory()->scenarioFinal('ura_finale')->create();
    RaceCatalogSlot::factory()->scenarioFinal('trackblazer')->create();

    $forUnityCup = RaceCatalogSlot::forScenario('unity_cup')->pluck('id')->all();

    expect($forUnityCup)->toContain($shared->id)
        ->not->toContain($uraFinal->id);
});

it('refuses a dated row outside the finale block with no month', function (): void {
    expect(fn (): array => RaceCatalogSlot::factory()->create(['month' => null])->toArray())
        ->toThrow(InvalidArgumentException::class);
});

it('refuses an unknown career year', function (): void {
    expect(fn (): array => RaceCatalogSlot::factory()->create(['year' => 9])->toArray())
        ->toThrow(InvalidArgumentException::class);
});

it('leaves a runtime-decided distance null so no caller reads it as a measurement', function (): void {
    $debut = RaceCatalogSlot::factory()->debut()->create();

    expect($debut->distance)->toBeNull()
        ->and($debut->distance_band)->toBeNull()
        ->and($debut->surface)->toBeNull()
        ->and($debut->distanceLabel())->toBe('Decided by your most-run types')
        ->and($debut->is_mandatory)->toBeTrue();
});

it('reports fan gain as unknown rather than zero for the nine unresolvable slots', function (): void {
    $slot = RaceCatalogSlot::factory()->withoutKnownFanGain()->create();

    // The curve id is present; it is [Global]'s 1-50 payout table that stops short
    // of it. A caller writing `fans_gain ?? 0` would turn "the export cannot say"
    // into "this race earns nothing", so the predicate lives on the model.
    expect($slot->fans_gain_curve)->toBe(54)
        ->and($slot->hasKnownFanGain())->toBeFalse()
        ->and($slot->hasFanGate())->toBeTrue()
        ->and($slot->fans_needed)->toBe(1000);
});

it('reports fan gain as known for a curve [Global] publishes a row for', function (): void {
    $slot = RaceCatalogSlot::factory()->create([
        'fans_gain_curve' => RaceCatalogSlot::MAX_RESOLVED_GLOBAL_PAYOUT_CURVE,
    ]);

    expect($slot->hasKnownFanGain())->toBeTrue()
        ->and(RaceCatalogSlot::factory()->make(['fans_gain_curve' => 51])->hasKnownFanGain())->toBeFalse();
});

it('treats a zero fan gate as a real answer, not an absent one', function (): void {
    $slot = RaceCatalogSlot::factory()->create(['fans_needed' => 0]);

    expect($slot->fans_needed)->toBe(0)->and($slot->hasFanGate())->toBeFalse();
});
