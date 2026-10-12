<?php

declare(strict_types=1);

use App\Models\Advisor\BuildTargetPayload;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * R3-01's regression guard: one turn's Energy reading, four surfaces.
 *
 * `TurnEntry::energyReading()` is the only owner of the resolution, and four props are built from it: the
 * Cockpit header row, the Cockpit state strip, the event screen's career state, and the training screen's
 * stated line. Each is pinned against the other three and against its own literal: an equality check alone
 * would pass with all four identically wrong, and a literal alone does not name which surface drifted.
 *
 * `CockpitEnergyBandDisplayTest` covers the band defect itself; this covers the agreement.
 */
/**
 * One turn carrying one Energy reading, built the same way `EnergyStateTest` builds its row: a builder per
 * file, because a focused run loads only the file it names and a fixture reached across files is undefined
 * there.
 */
function energySurfaceRun(string $state, ?string $band = null, ?int $energy = null): TrainingRun
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

function energySurfaces(TrainingRun $run): array
{
    $read = function (string $route, string $surface) use ($run): array {
        $props = [];

        test()->get(route($route, $run))
            ->assertOk()
            ->assertInertia(function (Assert $page) use (&$props): void {
                $props = $page->toArray()['props'];
            });

        if ($surface === 'header') {
            return [$props['header']['energy'], $props['header']['energy_text']];
        }

        if ($surface === 'training') {
            return [$props['energy']['value'], $props['energy']['label']];
        }

        $row = collect($props['state']['meta'])->firstWhere('key', 'energy');

        return [$row['value'], $row['band_text']];
    };

    return [
        'header' => $read('runs.cockpit', 'header'),
        'strip' => $read('runs.cockpit', 'strip'),
        'events' => $read('runs.events.decision', 'events'),
        'training' => $read('runs.training', 'training'),
    ];
}

/**
 * The words a surface prints for one reading: the band text when the turn recorded a band, the figure when
 * it recorded one, and the named absence when it recorded neither.
 */
function printedReading(array $surface): string
{
    [$value, $text] = $surface;

    return (string) ($text ?? $value ?? 'N/A');
}

it('states the same Energy reading on every surface that shows one', function (
    string $state,
    ?string $band,
    ?int $energy,
    string $reading,
    string $stated,
): void {
    $surfaces = energySurfaces(energySurfaceRun($state, $band, $energy));

    $header = printedReading($surfaces['header']);
    $strip = printedReading($surfaces['strip']);
    $events = printedReading($surfaces['events']);
    // The training screen states the reading with its own caption, so it is compared to its own literal.
    $training = printedReading($surfaces['training']);

    expect($header)->toBe($reading)
        ->and($strip)->toBe($reading)
        ->and($events)->toBe($reading)
        ->and($training)->toBe($stated)
        ->and($strip)->toBe($header)
        ->and($events)->toBe($header);
})->with([
    ['exact', null, 71, '71', 'Energy: 71'],
    ['band', 'low', null, 'Low band', 'Energy: Low band'],
    ['band', 'high', null, 'High band', 'Energy: High band'],
    ['unknown', null, null, 'N/A', 'Energy: Not recorded'],
]);

it('keeps the gauge number and the band words on separate fields', function (): void {
    // The strip's value is the figure the gauge draws, and its band text is what prints in place of a
    // gauge. The write path keeps them exclusive, and the surfaces must not blur the two readings.
    $exact = energySurfaces(energySurfaceRun('exact', null, 71));
    $band = energySurfaces(energySurfaceRun('band', 'low'));

    expect($exact['strip'][0])->toBe(71)
        ->and($exact['strip'][1])->toBeNull()
        ->and($band['strip'][0])->toBeNull()
        ->and($band['strip'][1])->toBe('Low band');
});
