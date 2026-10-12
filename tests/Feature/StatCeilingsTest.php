<?php

declare(strict_types=1);

use App\Domain\Training\StatCeilings;
use App\Models\TrainingRun;
use App\Services\ScenarioCaps;

it('reports the scenario ceiling and the hard cap as two distinct levels for a run that names one', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);

    $ceilings = ScenarioCaps::ceilingsForRun($run);

    expect($ceilings->knownCap['Speed'])->toBe(1300)
        ->and($ceilings->potentialCap['Speed'])->toBe(2000)
        ->and($ceilings->knownCap['Wit'])->toBe(1800)
        ->and($ceilings->potentialCap['Wit'])->toBe(2000)
        ->and($ceilings->stats())->toBe(['Speed', 'Stamina', 'Power', 'Guts', 'Wit']);
});

it('reports a known ceiling of the bare base and no potential at all for a run with no scenario', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => null]);

    $ceilings = ScenarioCaps::ceilingsForRun($run);

    expect($ceilings->knownCap['Speed'])->toBe(1200)
        ->and($ceilings->potentialCap['Speed'])->toBeNull()
        ->and($ceilings->potentialCap['Wit'])->toBeNull();
});

it('keeps the two levels apart for every stat it names, so neither level can silently borrow the other', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);

    $ceilings = ScenarioCaps::ceilingsForRun($run);

    foreach ($ceilings->stats() as $stat) {
        expect($ceilings->knownCap[$stat])->toBeLessThan($ceilings->potentialCap[$stat])
            ->and($ceilings->knownCap[$stat])->toBeLessThanOrEqual(ScenarioCaps::hardCap());
    }
});

it('refuses a pair whose two levels name different stats', function (): void {
    new StatCeilings(['Speed' => 1300], ['Speed' => 2000, 'Wit' => 2000]);
})->throws(InvalidArgumentException::class, 'A ceiling pair must name the same stats for both levels');

it('answers the same ceilings for no run at all as for a run that names no scenario', function (): void {
    $ceilings = ScenarioCaps::ceilingsForRun(null);

    expect($ceilings->knownCap['Speed'])->toBe(1200)
        ->and($ceilings->potentialCap['Speed'])->toBeNull();
});

/*
 * The entered ceiling (`ADR-0025`). These pin the read path alone: a factory writes the column straight,
 * past every Form Request, so what `forRun()` does with a stored figure is tested apart from what may be
 * stored. `EnteredStatCapsTest` pins the boundary that fills the columns.
 */

it('reads an entered ceiling in place of the scenario ceiling for the stats it names', function (): void {
    $run = TrainingRun::factory()->create([
        'scenario' => 'unity_cup',
        'cap_speed' => 1325,
        'cap_guts' => 1308,
    ]);

    $caps = ScenarioCaps::forRun($run);

    expect($caps['Speed'])->toBe(1325)
        ->and($caps['Guts'])->toBe(1308)
        // A stat nobody entered keeps the scenario's own figure.
        ->and($caps['Stamina'])->toBe(1300)
        // Wit has no entered column at all, so no stored row can move it.
        ->and($caps['Wit'])->toBe(1800);
});

it('applies an entered ceiling to a run that names no scenario', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => null, 'cap_power' => 1250]);

    expect(ScenarioCaps::forRun($run)['Power'])->toBe(1250)
        ->and(ScenarioCaps::forRun($run)['Speed'])->toBe(1200);
});

it('keeps the engine hard cap as the bound on an entered figure', function (): void {
    // A row above the engine ceiling cannot come from a form; the read clamps it the same way `caps()`
    // clamps a scenario bonus that would pass the hard cap.
    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup', 'cap_power' => 2500]);

    expect(ScenarioCaps::forRun($run)['Power'])->toBe(ScenarioCaps::hardCap());
});

it('reverts to the scenario ceiling when an entered ceiling is cleared', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup', 'cap_stamina' => 1450]);
    expect(ScenarioCaps::forRun($run)['Stamina'])->toBe(1450);

    $run->update(['cap_stamina' => null]);

    expect(ScenarioCaps::forRun($run->fresh())['Stamina'])->toBe(1300);
});

it('answers the published scenario ceiling when asked for it, entered figures aside', function (): void {
    // The cockpit's caps table prints base, bonus and their sum with a provenance line naming the matrix
    // as its source. An entered ceiling is the Trainer's observation, not the matrix's, so this read path
    // must keep stating the published arithmetic.
    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup', 'cap_speed' => 1325]);

    expect(ScenarioCaps::scenarioCaps($run)['Speed'])->toBe(1300)
        ->and(ScenarioCaps::forRun($run)['Speed'])->toBe(1325);
});
