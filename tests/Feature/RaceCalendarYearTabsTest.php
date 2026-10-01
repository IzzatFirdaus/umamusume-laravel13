<?php

declare(strict_types=1);

use App\Models\RaceCatalogSlot;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Services\DataPipeline\Parsers\GametoraRaceCatalogParser;
use Illuminate\Support\Facades\Blade;

/*
 * The grid gained a year dimension when it moved onto race_catalog_slots: the
 * catalogue holds three years of slots and the client shows one at a time behind
 * three tabs. These cover the derivation, the per-year scoping, and the highlight
 * the component has defined since 70248b3 but never had a way to reach.
 */

function runWithTurn(?int $turn, string $scenario = 'ura_finale'): TrainingRun
{
    $run = TrainingRun::factory()->create(['scenario' => $scenario]);

    if ($turn !== null) {
        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => $turn, 'fans' => 5000]);
    }

    return $run->fresh();
}

/**
 * Twelve empty months with one goal race, so a priority question can be asked of
 * the component without a Goals source behind the model.
 *
 * @return list<array{halves: array<string, array<string, mixed>>}>
 */
function calendarCellsWithGoal(int $monthIndex, string $half, string $label): array
{
    $cells = [];

    for ($m = 0; $m < 12; $m++) {
        $cells[$m] = ['halves' => ['Early' => ['slots' => []], 'Late' => ['slots' => []]]];
    }

    $cells[$monthIndex]['halves'][$half] = ['slots' => [['state' => 'goal', 'label' => $label]]];

    return $cells;
}

it('derives the career year from a monotonic turn counter', function (int $turn, int $year): void {
    expect(TrainingRun::careerYearForTurn($turn))->toBe($year);
})->with([
    // 1, Junior Early Jan            12, Junior Late Jun (the debut)
    [1, 1], [12, 1], [24, 1],
    // 25, Classic Early Mar would be turn 25 = Classic Early Jan
    [25, 2], [48, 2],
    [49, 3], [72, 3],
    // A logged turn past the career has no fourth year to land in.
    [73, 3], [400, 3],
]);

it('puts the debut at Junior Late June, where the client panel shows it', function (): void {
    // Two independent halves have to agree here: the parser's turn arithmetic and
    // the year derivation. 09 corroborates the result against a [Global] capture —
    // the debut is turn 12, and turn 12 is only Late June if the year starts in
    // Early January.
    $debut = (new GametoraRaceCatalogParser)->parse(json_encode([[
        'id' => 'debut', 'year' => 1, 'month' => 6, 'half' => 2,
        'details' => ['name_en' => 'Junior Make Debut', 'grade' => 900,
            'distance' => 99999, 'terrain' => 99999, 'track' => 99999],
    ]], JSON_THROW_ON_ERROR))[0];

    expect($debut['turn'])->toBe(12)
        ->and($debut['slot_label'])->toBe('Late June')
        ->and(TrainingRun::careerYearForTurn($debut['turn']))->toBe(RaceCatalogSlot::YEAR_JUNIOR)
        ->and(TrainingRun::careerYearForTurn(12))->toBe(1);
});

it('reports no turn to play for a run that has logged nothing', function (): void {
    // The pin survives the semantics change with its reason intact: the next turn of
    // an untouched run is arithmetically turn 1, and highlighting Early January would
    // tell a Trainer who has not started that they are standing in it (D-220).
    expect(runWithTurn(null)->nextTurnToPlay())->toBeNull();
});

it('points at the turn being decided, not the turn just logged', function (int $logged, ?array $expected): void {
    expect(runWithTurn($logged)->nextTurnToPlay())->toBe($expected);
})->with([
    // Turns through 3 are done, so the calendar is asking about turn 4: Early February.
    [3, ['year' => 1, 'turn' => 4]],
    [1, ['year' => 1, 'turn' => 2]],
    // Junior Late December logged puts the next turn in Classic Early January. The
    // answer carries the year with it, because the turn to play is not always in the
    // year the last logged turn was.
    [24, ['year' => 2, 'turn' => 1]],
    // Turn 40 is Classic Late August; the turn to play is Late September.
    [40, ['year' => 2, 'turn' => 17]],
    // Senior Late December is turn 72. Nothing is left to take, so nothing is highlighted.
    [72, null],
    // A turn logged past the career has no next turn inside the grid either.
    [80, null],
]);

it('shows only the requested year of the catalogue in the grid', function (): void {
    RaceCatalogSlot::factory()->create(['year' => 1, 'month' => 4, 'half' => 'Early', 'turn' => 7, 'title' => 'Junior Only Race']);
    RaceCatalogSlot::factory()->create(['year' => 2, 'month' => 4, 'half' => 'Early', 'turn' => 7, 'title' => 'Classic Only Race']);

    $run = runWithTurn(1);

    $junior = $run->calendarCells(RaceCatalogSlot::YEAR_JUNIOR)[3]['halves']['Early']['slots'];
    $classic = $run->calendarCells(RaceCatalogSlot::YEAR_CLASSIC)[3]['halves']['Early']['slots'];

    expect($junior[0]['label'])->toBe('Junior Only Race')
        ->and($classic[0]['label'])->toBe('Classic Only Race');
});

it('defaults the grid to the year the run is actually in', function (): void {
    RaceCatalogSlot::factory()->create(['year' => 1, 'month' => 4, 'half' => 'Early', 'turn' => 7, 'title' => 'Junior Only']);
    RaceCatalogSlot::factory()->create(['year' => 2, 'month' => 4, 'half' => 'Early', 'turn' => 7, 'title' => 'Classic Only']);

    // Turn 30 is Classic.
    $labels = array_column(runWithTurn(30)->calendarCells()[3]['halves']['Early']['slots'], 'label');

    expect($labels)->toBe(['Classic Only']);
});

it('renders three year tabs and marks the viewed one selected', function (): void {
    $html = Blade::render(
        '<x-race-calendar scenario="ura_finale" :cells="$cells" :year="2" />',
        ['cells' => runWithTurn(1)->calendarCells(2)]
    );

    expect($html)->toContain('role="tablist"')
        ->and(substr_count($html, 'role="tab"'))->toBe(3)
        ->and($html)->toContain('aria-selected="true"')
        ->and($html)->toContain('Classic Year')
        // The finale block is not a year you can train through.
        ->and($html)->not->toContain('Finale Year');
});

it('carries the year in the tab links so the view is addressable', function (): void {
    $html = Blade::render(
        '<x-race-calendar scenario="ura_finale" :cells="$cells" :year="1" />',
        ['cells' => runWithTurn(1)->calendarCells(1)]
    );

    expect($html)->toMatch('/href="[^"]*year=2/')->and($html)->toMatch('/href="[^"]*year=3/');
});

it('names the year in the region label so a screen reader is not told twelve months of nothing', function (): void {
    $html = Blade::render(
        '<x-race-calendar scenario="ura_finale" :cells="$cells" :year="3" />',
        ['cells' => runWithTurn(1)->calendarCells(3)]
    );

    expect($html)->toContain('Senior year, 24 turn slots');
});

it('highlights the turn to play, which the component ranked but never received', function (): void {
    // 70248b3 put `current` at priority 5 in its own map; nothing fed it, so the
    // state was defined and unreachable. The prop is the feed, and it now carries the
    // turn being decided rather than the one just logged.
    $run = runWithTurn(11);

    $html = Blade::render(
        '<x-race-calendar scenario="ura_finale" :cells="$cells" :year="1" :next-turn="$turn" />',
        ['cells' => $run->calendarCells(1), 'turn' => $run->nextTurnToPlay()['turn']]
    );

    // Turn 11 is logged and turn 12 is the one being asked about: Late June, where the
    // debut sits. The spoken words name the new semantics instead of the old ones.
    expect($html)->toContain('aria-label="Late Jun: Next, Next; next turn to play"')
        ->and(substr_count($html, 'border-pick-line'))->toBe(1);
});

it('shows the outline on the year that holds the turn to play, not the year last logged', function (): void {
    // Junior Late December is turn 24, so the turn to play is Classic Early January.
    // The derivation carries its own year precisely so the tab that does not hold the
    // turn stays plain instead of lighting up Early January of the wrong year.
    $run = runWithTurn(24);
    $next = $run->nextTurnToPlay();

    $classic = Blade::render(
        '<x-race-calendar scenario="ura_finale" :cells="$cells" :year="$year" :next-turn="$turn" />',
        ['cells' => $run->calendarCells(RaceCatalogSlot::YEAR_CLASSIC), 'year' => $next['year'], 'turn' => $next['turn']]
    );
    $junior = Blade::render(
        '<x-race-calendar scenario="ura_finale" :cells="$cells" :year="1" :next-turn="null" />',
        ['cells' => $run->calendarCells(RaceCatalogSlot::YEAR_JUNIOR)]
    );

    expect($next)->toBe(['year' => 2, 'turn' => 1])
        ->and($classic)->toContain('Early Jan: Next, Next; next turn to play')
        ->and($junior)->not->toContain('border-pick-line');
});

it('highlights nothing when the career has no turn left to play', function (): void {
    // The edge the semantics change has to answer: a finished career shows no
    // outline at all, rather than falling back to the last turn the Trainer played.
    $run = runWithTurn(72);

    $html = Blade::render(
        '<x-race-calendar scenario="ura_finale" :cells="$cells" :year="3" :next-turn="$turn" />',
        ['cells' => $run->calendarCells(RaceCatalogSlot::YEAR_SENIOR), 'turn' => $run->nextTurnToPlay()['turn'] ?? null]
    );

    expect($run->nextTurnToPlay())->toBeNull()
        ->and($html)->not->toContain('border-pick-line')
        ->and($html)->not->toContain('next turn to play');
});

it('ranks a goal above the next-turn outline', function (): void {
    // Priority is a component concern, so it is tested at the component. Going
    // through the model would need a Goal source that does not exist yet.
    $html = Blade::render(
        '<x-race-calendar scenario="ura_finale" :cells="$cells" :year="2" :next-turn="20" />',
        ['cells' => calendarCellsWithGoal(9, 'Late', 'Tokyo Yushun (Japanese Derby)')]
    );

    expect($html)->toContain('border-goal-line')
        ->and(substr_count($html, 'border-pick-line'))->toBe(0);
});

it('keeps the career year when the entry mode is switched, which the mode form used to drop', function (): void {
    // The year tabs are links built through `request()->fullUrlWithQuery`, so they carry `entry_mode`
    // for free. The mode buttons are the mirror image and were not the mirror fix: the submitted fields
    // of a GET form replace the action's query string whole, so pressing "Race not on the calendar"
    // from the Classic tab sent `entry_mode` and nothing else, and the panel answered with the year the
    // run has actually reached rather than the tab the Trainer was sitting in. A GET form keeps state it
    // is not itself about by carrying it as a field, which is what the tabs cannot need and this can.
    $run = runWithTurn(1);

    // Through the screen rather than a bare component render: the panel reads `$errors`, which only
    // exists on a request, and the year arrives the way a Trainer's does, as a query parameter.
    $html = $this->get(route('runs.show', $run).'?year=2')->content();

    // Two mode buttons, so two carriers. The value is the resolved tab year rather than the raw
    // query parameter, because `careerYearForTab` is the one place that decides which year is in view.
    expect(substr_count($html, 'name="year" value="2"'))
        ->toBe(2)
        // The branch the panel actually drew is the Classic one, which is the same fact seen from the
        // heading the calendar path prints above its select.
        ->and($html)->toContain('Classic year');
});
