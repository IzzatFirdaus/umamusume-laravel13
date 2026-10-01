<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\TrainingRun;
use Illuminate\Support\Facades\Schema;

/*
 * KI-10's schema half has existed since Slice 7: a finish knows which period it counts toward.
 * What was missing is the figure itself. `TrainingRun::gradeEarnedFor()` computed Grade Points on
 * every read from a slot's tier and the finish's placement, so nothing on the race row said what
 * the race paid, and a period holding one unpriced finish withheld its total with no way to see
 * which finish caused it or what the priced ones were worth.
 *
 * `race_entries.grade_points_earned` is that figure, stored per row.
 *
 * The price table is `config('scenarios.*.grade_point_by_grade')`, transcribed from
 * docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md section "Grade Points and Shop Coins - Exact Values", which
 * prices a 1st place and nothing else. Every placement below first is therefore null here, not
 * zero: KI-10's ratio half is still open, and the same page's 100/60/30/0 table is Shop Coins,
 * which that document says explicitly "do not depend on race grade at all". Borrowing it would be
 * a wrong number wearing a real citation.
 *
 * The tier is read from `race_catalog_slots` first and `scenario_slots` second, per the owner's
 * ruling for this session: the career calendar is where a tier is published per race, and the
 * slot table carries the seeded URA schedule and the Trainer-typed free races.
 */

it('stores grade points earned on race_entries', function (): void {
    expect(Schema::hasColumn('race_entries', 'grade_points_earned'))->toBeTrue();
});

it('prices a 1st place at the grade table figure for each tier', function (string $tier, int $points): void {
    $entry = gpEntry(tier: $tier, placement: 1);

    expect($entry->fresh()->grade_points_earned)->toBe($points);
})->with([
    ['G1', 100],
    ['G2', 80],
    ['G3', 60],
    ['OP', 40],
    ['Pre-OP', 20],
]);

it('reads the tier off the career calendar slot when the entry has one', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'trackblazer']);
    $catalog = RaceCatalogSlot::factory()->create(['tier' => 'G2', 'grade_code' => 200]);

    $entry = RaceEntry::create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $catalog->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
    ]);

    expect($entry->grade_points_earned)->toBe(80);
});

it('prefers the career calendar tier over a differing scenario slot tier', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'trackblazer']);
    $catalog = RaceCatalogSlot::factory()->create(['tier' => 'G3', 'grade_code' => 300]);
    $slot = ScenarioSlot::factory()->create(['scenario_key' => 'trackblazer', 'kind' => 'goal_race', 'tier' => 'G1']);

    $entry = RaceEntry::create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $catalog->id,
        'scenario_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
    ]);

    // The calendar is the per-race publisher, so it wins. The order is a ruling, not a accident of
    // which relation happens to be loaded.
    expect($entry->grade_points_earned)->toBe(60);
});

it('stores null for a placement the source does not price', function (?int $placement): void {
    // 2nd in a G1 is unpriced for the same reason 4th is: the ratio half of KI-10 is open, and no
    // source names the scaling below first.
    expect(gpEntry(tier: 'G1', placement: $placement)->fresh()->grade_points_earned)->toBeNull();
})->with([[2], [3], [4], [5], [12]]);

it('stores null when the Trainer left the placement blank', function (): void {
    expect(gpEntry(tier: 'G1', placement: null)->fresh()->grade_points_earned)->toBeNull();
});

it('stores null when the race carries no tier, which is most rows since R75', function (): void {
    expect(gpEntry(tier: null, placement: 1)->fresh()->grade_points_earned)->toBeNull();
});

it('stores null for a tier the price table has no entry for', function (): void {
    // `Debut` is a real tier on the career calendar and is not a Grade Point grade.
    expect(gpEntry(tier: 'Debut', placement: 1)->fresh()->grade_points_earned)->toBeNull();
});

it('keeps a figure the Trainer entered over the derived one', function (): void {
    // The row is entered-first: the tool prices what it can and the Trainer corrects it with what
    // the client actually paid. A guard that always recomputed would erase that correction.
    $entry = gpEntry(tier: 'G1', placement: 1, gradePoints: 75);

    expect($entry->fresh()->grade_points_earned)->toBe(75);
});

it('re-prices when the placement is corrected and does not invent a price when it is downgraded', function (): void {
    $entry = gpEntry(tier: 'G1', placement: 2);
    expect($entry->grade_points_earned)->toBeNull();

    $entry->update(['placement' => 1]);
    expect($entry->fresh()->grade_points_earned)->toBe(100);

    $entry->update(['placement' => 3]);
    expect($entry->fresh()->grade_points_earned)->toBeNull();
});

it('sums the stored figures for the period the Trainer reports as live', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'trackblazer', 'current_objective_index' => 3]);

    gpEntry(tier: 'G1', placement: 1, run: $run, objectiveIndex: 3);
    gpEntry(tier: 'G3', placement: 1, run: $run, objectiveIndex: 3);

    expect($run->fresh()->gradeEarned())->toBe(160);
});

it('still withholds a period holding an unpriced finish, and names the figure that withheld it', function (): void {
    // KI-10's withholding rule is unchanged by the column: a partial sum shown as a total is the
    // false claim D-256 bans. What the column adds is that the unpriced row is now visible as a
    // row rather than only as a missing total.
    $run = TrainingRun::factory()->create(['scenario' => 'trackblazer', 'current_objective_index' => 2]);

    gpEntry(tier: 'G1', placement: 1, run: $run, objectiveIndex: 2);
    gpEntry(tier: 'G1', placement: 4, run: $run, objectiveIndex: 2);

    expect($run->fresh()->gradeEarned())->toBeNull()
        ->and($run->fresh()->gradeUnpricedCount())->toBe(1);
});

it('prices nothing for a scenario that composes no grade objectives', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $slot = ScenarioSlot::factory()->create(['scenario_key' => 'ura_finale', 'kind' => 'goal_race', 'tier' => 'G1']);

    $entry = RaceEntry::create([
        'training_run_id' => $run->id,
        'scenario_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
    ]);

    expect($entry->grade_points_earned)->toBeNull();
});

it('prices a win recorded through the race form, not only one written by hand', function (): void {
    // The guard runs on the model, so the form path gets it for free — which is exactly why the
    // write goes through RaceEntry::create rather than the factory. This reaches it anyway,
    // because a derivation nobody exercises through the real entry point is a derivation that
    // stops working quietly.
    $run = TrainingRun::factory()->create(['scenario' => 'trackblazer', 'current_objective_index' => 2]);
    $slot = ScenarioSlot::factory()->create([
        'scenario_key' => 'trackblazer',
        'kind' => 'goal_race',
        'title' => 'Arimura Kinen',
        'tier' => 'G1',
    ]);

    $this->post(route('runs.races.store', $run), [
        'entry_mode' => 'calendar',
        'scenario_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed->value,
        'placement' => 1,
        'objective_index' => 2,
    ])->assertRedirect(route('runs.show', $run));

    expect(RaceEntry::where('training_run_id', $run->id)->sole()->grade_points_earned)->toBe(100)
        ->and($run->fresh()->gradeEarned())->toBe(100);
});

/**
 * A completed entry on a Trackblazer run, priced against the tier given.
 */
function gpEntry(
    ?string $tier,
    ?int $placement,
    ?int $gradePoints = null,
    ?TrainingRun $run = null,
    ?int $objectiveIndex = null,
): RaceEntry {
    $run ??= TrainingRun::factory()->create(['scenario' => 'trackblazer']);

    $slot = $tier === null
        ? null
        : ScenarioSlot::factory()->create([
            'scenario_key' => $run->scenarioKey(),
            'kind' => 'goal_race',
            'tier' => $tier,
        ]);

    return RaceEntry::create(array_filter([
        'training_run_id' => $run->id,
        'scenario_slot_id' => $slot?->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => $placement,
        'objective_index' => $objectiveIndex,
        'grade_points_earned' => $gradePoints,
    ], fn (mixed $v): bool => $v !== null));
}
