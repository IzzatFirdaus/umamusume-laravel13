<?php

declare(strict_types=1);

use App\Models\Advisor\BuildTargetPayload;
use App\Models\RaceEntry;
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
 * R6-12: the Timeline is the fifth surface that shows a turn's Energy reading.
 *
 * The page read `entry.after?.energy` and `entry.after?.sp`, and the controller sent `before` and
 * `actual` only, so both cells printed `N/A` on every row. The two facets are independent: SP was
 * silenced by a property-name mismatch, Energy by the absence of any band projection. One case per
 * facet, so a one-facet repair cannot pass.
 *
 * @return array{energy: list<string>, sp: list<string>}
 */
function timelineReadings(TrainingRun $run): array
{
    $props = [];

    test()->get(route('runs.timeline', $run))
        ->assertOk()
        ->assertInertia(function (Assert $page) use (&$props): void {
            $props = $page->toArray()['props'];
        });

    $energy = [];
    $sp = [];

    foreach ($props['entries'] as $entry) {
        $energy[] = $entry['energy_reading']['text'] ?? 'N/A';
        $sp[] = $entry['actual']['sp'] === null ? 'N/A' : (string) $entry['actual']['sp'];
    }

    return ['energy' => $energy, 'sp' => $sp];
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

/**
 * R6-12. A run whose turns each carry their own reading, so a row cannot borrow a neighbour's.
 *
 * @param  list<array{state: string|null, band: string|null, energy: int|null, sp: int|null}>  $turns
 */
function timelineReadingRun(array $turns): TrainingRun
{
    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);

    foreach ($turns as $index => $turn) {
        TurnEntry::factory()->create([
            'training_run_id' => $run->id,
            'turn' => $index + 1,
            'energy' => $turn['energy'],
            'energy_state' => $turn['state'],
            'energy_band' => $turn['band'],
            'sp' => $turn['sp'],
        ]);
    }

    return $run;
}

it('projects each Timeline turn its own Energy reading and its own SP (R6-12)', function (): void {
    // Two turns with distinct bands: a latest-turn substitution cannot satisfy both, and neither can a
    // constant. The third turn carries no reading at all, so the absence must stay row-scoped too.
    $run = timelineReadingRun([
        ['state' => 'band', 'band' => 'low', 'energy' => null, 'sp' => 173],
        ['state' => 'band', 'band' => 'mid', 'energy' => null, 'sp' => 0],
        ['state' => 'unknown', 'band' => null, 'energy' => 50, 'sp' => null],
    ]);

    $readings = timelineReadings($run);

    // Facet (a) — SP. The server already sends `actual.sp`, so this half is green here and the failure
    // the walk saw lives on the client's property name; `career-timeline.spec.ts` pins that half.
    expect($readings['sp'])->toBe(['173', '0', 'N/A']);

    // Facet (b) — the band projection. Two distinct bands, so a latest-turn substitution cannot pass,
    // and a third row with no reading at all, so the absence stays row-scoped too.
    expect($readings['energy'])->toBe(['Low band', 'Mid band', 'N/A'])
        // `unknown` outranks a stale figure on the same row: 50 is not a reading the Trainer took.
        ->and($readings['energy'])->not->toContain('50');
});

it('projects an exact zero Energy reading as zero, not as an absence (R6-12)', function (): void {
    // `energy = 0` is a figure the Trainer read, so the cell prints 0 and carries no absence title.
    // `energyReading()` already answers this; the Timeline read a key the server never sent.
    $run = timelineReadingRun([
        ['state' => 'exact', 'band' => null, 'energy' => 0, 'sp' => 0],
    ]);

    $props = [];
    $this->get(route('runs.timeline', $run))
        ->assertOk()
        ->assertInertia(function (Assert $page) use (&$props): void {
            $props = $page->toArray()['props'];
        });

    $reading = $props['entries'][0]['energy_reading'];

    expect($reading['kind'])->toBe('exact')
        ->and($reading['text'])->toBe('0')
        ->and($reading['value'])->toBe(0);
});

it('leaves the Energy reading null on a row that is not a turn (R6-12)', function (): void {
    // A race row must not borrow the neighbouring turn's reading. The key travels as null so the page
    // prints one named absence rather than a figure from the row above.
    $run = timelineReadingRun([
        ['state' => 'exact', 'band' => null, 'energy' => 71, 'sp' => 40],
    ]);

    $turn = TurnEntry::query()->where('training_run_id', $run->id)->firstOrFail();

    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn_entry_id' => $turn->id,
    ]);

    $props = [];
    $this->get(route('runs.timeline', $run))
        ->assertOk()
        ->assertInertia(function (Assert $page) use (&$props): void {
            $props = $page->toArray()['props'];
        });

    $race = collect($props['entries'])->firstWhere('kind', 'race');

    expect($race)->not->toBeNull()
        ->and($race['energy_reading'])->toBeNull();
});
