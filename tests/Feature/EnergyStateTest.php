<?php

declare(strict_types=1);

use App\Models\Advisor\BuildTargetPayload;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Energy as a state: exact, a named band, or not recorded.
 *
 * The audit found the tool collapsing every absence into a bare N/A. A band is a reading the Trainer
 * took off the client, so the advisor reasons from it and names it rather than declining; "not
 * recorded" is explained as the absence it is, and the options are still offered.
 */
function energyRun(string $state, ?string $band = null, ?int $energy = null): TrainingRun
{
    $run = TrainingRun::factory()->create([
        'scenario' => 'unity_cup',
        'build_target' => BuildTargetPayload::fromArray([
            'purpose' => 'StoryClear',
            'distance' => 'Medium',
            'surface' => 'Turf',
            'style' => 'Pace Chaser',
            'targets' => ['Speed' => 1300, 'Stamina' => 500, 'Power' => 500, 'Guts' => 500, 'Wit' => 500],
            'skill_priorities' => [],
        ])->toArray(),
    ]);

    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 1,
        'speed' => 607, 'stamina' => 500, 'power' => 500, 'guts' => 500, 'wit' => 500,
        'energy' => $energy,
        'energy_state' => $state,
        'energy_band' => $band,
    ]);

    return $run;
}

it('renders a low band and advises from it rather than declining', function (): void {
    $run = energyRun('band', 'low');

    $this->get(route('runs.training', $run))
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $page->component('Career/TrainingDetail');

            $props = $page->toArray()['props'];

            expect($props['energy']['label'])->toBe('Energy: Low band')
                ->and($props['energy']['band'])->toBe('low')
                ->and($props['advisor']['absence'])->toBeNull()
                ->and($props['advisor']['action'])->not->toBeNull()
                ->and($props['advisor']['reason'])->toBeString()->toContain('low band');
        });
});

it('renders an unknown reading and explains the absence without refusing', function (): void {
    $run = energyRun('unknown');

    $this->get(route('runs.training', $run))
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $props = $page->toArray()['props'];

            expect($props['energy']['label'])->toBe('Energy: Not recorded')
                ->and($props['energy']['state'])->toBe('unknown')
                ->and($props['advisor']['absence'])->toBeString()->toContain('not recorded')
                // The options are still offered: the absence is explained, not refused.
                ->and($props['options'])->not->toBeEmpty();
        });
});

it('renders an exact reading as its figure', function (): void {
    $run = energyRun('exact', null, 66);

    $this->get(route('runs.training', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('energy.label', 'Energy: 66')
            ->where('energy.value', 66));
});
