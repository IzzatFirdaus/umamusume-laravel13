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
