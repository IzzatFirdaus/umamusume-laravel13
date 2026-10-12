<?php

declare(strict_types=1);

use App\Models\Advisor\BuildTargetPayload;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * R3-01: a turn recorded as an Energy band has to read back as that band.
 *
 * `turn_entries.energy` holds a number and `energy_state`/`energy_band` hold the coarse level the client
 * showed instead of one. Until the reading was resolved in one place, every display that read only the
 * number printed `Energy N/A` over a band the Trainer had already entered. `EnergyStateTest` covers the
 * training screen; these cases cover the other two, and the shape the Cockpit header carries.
 *
 * The props are read rather than the markup because Inertia renders these screens client-side: the
 * response carries no Vue output to assert against, and the rendered copy is the browser suite's job.
 */
/**
 * One turn carrying one Energy reading, on a Unity Cup career with a target.
 *
 * A builder per file rather than a shared import: Pest loads only the file a focused run names, so a
 * fixture reached across files fails when the file is run alone (`AGENTS.md` §9 Level 1). This mirrors
 * `EnergyStateTest`'s own `energyRun()`, which builds the same row for the training screen.
 */
function energyBandRun(string $state, ?string $band = null, ?int $energy = null): TrainingRun
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

function bandDisplayProps(string $route, TrainingRun $run): array
{
    $props = [];

    test()->get(route($route, $run))
        ->assertOk()
        ->assertInertia(function (Assert $page) use (&$props): void {
            $props = $page->toArray()['props'];
        });

    return $props;
}

/**
 * @return array{key: string, label: string, value: int|string|null, band_text: string|null}
 */
function energyMetaRow(array $props): array
{
    /** @var array{key: string, label: string, value: int|string|null, band_text: string|null} $row */
    $row = collect($props['state']['meta'])->firstWhere('key', 'energy');

    return $row;
}

it('names a recorded Energy band on the Cockpit and the event screen (R3-01)', function (): void {
    $run = energyBandRun('band', 'low');

    $cockpit = bandDisplayProps('runs.cockpit', $run);
    $events = bandDisplayProps('runs.events.decision', $run);

    expect($cockpit['header']['energy_text'])->toBe('Low band')
        // The strip carries the band beside a null number, so the gauge prints the level instead of an absence.
        ->and(energyMetaRow($cockpit)['band_text'])->toBe('Low band')
        ->and(energyMetaRow($cockpit)['value'])->toBeNull()
        ->and(energyMetaRow($events)['band_text'])->toBe('Low band')
        ->and(energyMetaRow($events)['value'])->toBeNull();
});

it('still prints the exact figure where a number was recorded', function (): void {
    $run = energyBandRun('exact', null, 71);

    $cockpit = bandDisplayProps('runs.cockpit', $run);
    $events = bandDisplayProps('runs.events.decision', $run);

    expect($cockpit['header']['energy_text'])->toBe('71')
        ->and($cockpit['header']['energy'])->toBe(71)
        ->and(energyMetaRow($cockpit)['value'])->toBe(71)
        ->and(energyMetaRow($cockpit)['band_text'])->toBeNull()
        ->and(energyMetaRow($events)['value'])->toBe(71)
        ->and(energyMetaRow($events)['band_text'])->toBeNull();
});

it('names the absence when neither a number nor a band was recorded', function (): void {
    $run = energyBandRun('unknown');

    $cockpit = bandDisplayProps('runs.cockpit', $run);

    expect($cockpit['header']['energy_text'])->toBeNull()
        ->and(energyMetaRow($cockpit)['value'])->toBeNull()
        ->and(energyMetaRow($cockpit)['band_text'])->toBeNull();
});
