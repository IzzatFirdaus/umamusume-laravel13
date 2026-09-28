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

it('reports no current turn for a run that has logged nothing', function (): void {
    expect(runWithTurn(null)->currentTurnNumber())->toBeNull();
});

it('counts the current turn within the year, not across the career', function (int $turn, int $expected): void {
    expect(runWithTurn($turn)->currentTurnNumber())->toBe($expected);
})->with([
    [1, 1],
    [24, 24],
    [25, 1],
    [40, 16],
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

it('highlights the current turn, which the component ranked but never received', function (): void {
    // 70248b3 put `current` at priority 5 in its own map; nothing fed it, so the
    // state was defined and unreachable. The prop is the feed.
    $html = Blade::render(
        '<x-race-calendar scenario="ura_finale" :cells="$cells" :year="1" :current-turn="12" />',
        ['cells' => runWithTurn(1)->calendarCells(1)]
    );

    expect($html)->toContain('current turn')
        ->and(substr_count($html, 'border-pick-line'))->toBe(1);
});

it('does not highlight a turn in a year the run is not in', function (): void {
    $html = Blade::render(
        '<x-race-calendar scenario="ura_finale" :cells="$cells" :year="2" :current-turn="$turn" />',
        ['cells' => runWithTurn(1)->calendarCells(2), 'turn' => null]
    );

    expect($html)->not->toContain('border-pick-line');
});

it('ranks a goal above the current-turn outline', function (): void {
    // Priority is a component concern, so it is tested at the component. Going
    // through the model would need a Goal source that does not exist yet.
    $html = Blade::render(
        '<x-race-calendar scenario="ura_finale" :cells="$cells" :year="2" :current-turn="20" />',
        ['cells' => calendarCellsWithGoal(9, 'Late', 'Tokyo Yushun (Japanese Derby)')]
    );

    expect($html)->toContain('border-goal-line')
        ->and(substr_count($html, 'border-pick-line'))->toBe(0);
});
