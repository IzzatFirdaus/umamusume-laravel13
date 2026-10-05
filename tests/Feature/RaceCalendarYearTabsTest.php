<?php

declare(strict_types=1);

use App\Models\RaceCatalogSlot;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Services\DataPipeline\Parsers\GametoraRaceCatalogParser;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The grid gained a year dimension when it moved onto race_catalog_slots: the
 * catalogue holds three years of slots and the client shows one at a time behind
 * three tabs. These cover the derivation, the per-year scoping, and the highlight
 * the component has defined since 70248b3 but never had a way to reach.
 *
 * After the Inertia port (ADR-0020 §1) the year is server state, not a prop a test
 * hands to a component: `careerYearForTab()` resolves the query parameter, the
 * controller builds the tabs because they are navigations, and `nextTurn` is null
 * unless the turn being decided is in the year on screen. The cases below therefore read
 * the resolved payload for each year and let the component's own source answer the
 * treatment questions; `RaceCalendarTest` owns the cell map itself.
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
 * The calendar component's own source, which owns the tab markup and the outline.
 */
function calendarTabsSource(): string
{
    return (string) file_get_contents(base_path('resources/js/components/RaceCalendar.vue'));
}

/**
 * Which state outranks which, read out of the map the cell loop ranks candidates with.
 *
 * @return array<string, int>
 */
function calendarTabsPriority(): array
{
    preg_match('/const priority: Record<string, number> = \{(.*?)\n\};/s', calendarTabsSource(), $block);
    preg_match_all('/^\s{4}(\w+):\s*(\d+),/m', $block[1] ?? '', $rows, PREG_SET_ORDER);

    $priority = [];

    foreach ($rows as $row) {
        $priority[$row[1]] = (int) $row[2];
    }

    return $priority;
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
    // One catalogued race, so the panel is the grid rather than its "nothing entered" message. The
    // tabs are built server-side because they are navigations, and the selection is the address:
    // a Trainer can hand over "her Classic spring" as a URL.
    RaceCatalogSlot::factory()->create(['year' => 2, 'month' => 4, 'half' => 'Early', 'turn' => 31, 'title' => 'Classic Race']);
    $run = runWithTurn(30);

    $this->get('/training-runs/'.$run->id.'?year=2')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('calendar.year', 2)
        ->where('calendar.yearWord', 'Classic')
        ->where('calendar.yearTabs', fn (Collection $tabs): bool => $tabs->pluck('label')->all() === [
            'Junior Year', 'Classic Year', 'Senior Year',
        ]));

    // The tablist is a tablist and each tab is a tab, which is what makes the selected one announced.
    // The finale block is deliberately absent from the tab list: it is not a year you can train through.
    expect(calendarTabsSource())
        ->toContain('role="tablist"')
        ->toContain('role="tab"')
        ->toContain(":aria-selected=\"tab.year === year ? 'true' : 'false'\"")
        ->not->toContain('Finale');
});

it('carries the year in the tab links so the view is addressable', function (): void {
    RaceCatalogSlot::factory()->create(['year' => 1, 'month' => 4, 'half' => 'Early', 'turn' => 7, 'title' => 'Junior Race']);
    $run = runWithTurn(1);

    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('calendar.yearTabs', fn (Collection $tabs): bool => $tabs->every(
            fn (array $tab): bool => str_contains((string) $tab['url'], 'year='.$tab['year'])
        ))
        // Each tab carries its own year in the address, and the run's identifier survives beside it,
        // so a tab link is a page rather than a fragment.
        ->where('calendar.yearTabs', fn (Collection $tabs): bool => $tabs->contains(
            fn (array $tab): bool => $tab['year'] === 2 && str_contains((string) $tab['url'], '/training-runs/'.$run->id)
        )));

    expect(calendarTabsSource())->toContain(':href="tab.url"');
});

it('names the year in the region label so a screen reader is not told twelve months of nothing', function (): void {
    RaceCatalogSlot::factory()->create(['year' => 3, 'month' => 4, 'half' => 'Early', 'turn' => 55, 'title' => 'Senior Race']);
    $run = runWithTurn(50);

    $this->get('/training-runs/'.$run->id.'?year=3')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('calendar.yearWord', 'Senior'));

    // The region name leads with the panel, then the year word, then the slot count, so a screen
    // reader is told which career year it is looking at rather than "Race calendar".
    expect(calendarTabsSource())
        ->toContain('`Race calendar, ${props.yearWord !== null ?')
        ->toContain('year, ` : \'\'}${slotCount.value} turn slots`');
});

it('highlights the turn to play, which the component ranked but never received', function (): void {
    // 70248b3 put `current` at priority 5 in its own map; nothing fed it, so the state was defined and
    // unreachable. `calendar.nextTurn` is the feed now, and it carries the turn being decided rather
    // than the one just logged.
    RaceCatalogSlot::factory()->create(['year' => 1, 'month' => 6, 'half' => 'Late', 'turn' => 12, 'title' => 'Junior Make Debut']);
    $run = runWithTurn(11);

    // Turn 11 is logged and turn 12 is the one being asked about: Late June, where the debut sits.
    $this->get('/training-runs/'.$run->id.'?year=1')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('calendar.nextTurn', 12));

    // The spoken words name the new semantics instead of the old ones, and the outline is one cell's
    // border rather than a class anywhere in the document.
    expect(calendarTabsSource())
        ->toContain('; next turn to play')
        ->toContain('const isNext = props.nextTurn !== null && props.nextTurn === slotIndex + 1')
        ->toContain("...(isNext ? ['current'] : [])");
});

it('shows the outline on the year that holds the turn to play, not the year last logged', function (): void {
    // Junior Late December is turn 24, so the turn to play is Classic Early January. The derivation
    // carries its own year precisely so the tab that does not hold the turn stays plain instead of
    // lighting up Early January of the wrong year.
    RaceCatalogSlot::factory()->create(['year' => 2, 'month' => 1, 'half' => 'Early', 'turn' => 25, 'title' => 'Classic Opener']);
    $run = runWithTurn(24);
    $next = $run->nextTurnToPlay();

    expect($next)->toBe(['year' => 2, 'turn' => 1]);

    // The year on screen decides, not the year last logged: the same run lights up on the tab holding
    // the turn and stays plain on the tab that does not.
    $this->get('/training-runs/'.$run->id.'?year=2')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('calendar.year', 2)
        ->where('calendar.nextTurn', 1));

    $this->get('/training-runs/'.$run->id.'?year=1')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('calendar.year', 1)
        ->whereNull('calendar.nextTurn'));
});

it('highlights nothing when the career has no turn left to play', function (): void {
    // The edge the semantics change has to answer: a finished career shows no outline at all, rather
    // than falling back to the last turn the Trainer played.
    RaceCatalogSlot::factory()->create(['year' => 3, 'month' => 4, 'half' => 'Early', 'turn' => 55, 'title' => 'Senior Race']);
    $run = runWithTurn(72);

    expect($run->nextTurnToPlay())->toBeNull();

    $this->get('/training-runs/'.$run->id.'?year=3')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->whereNull('calendar.nextTurn'));

    // The component's own guard, because a null `nextTurn` must produce no outline rather than an
    // outline on slot zero.
    expect(calendarTabsSource())->toContain('const isNext = props.nextTurn !== null && props.nextTurn === slotIndex + 1');
});

it('ranks a goal above the next-turn outline', function (): void {
    // Priority is a component concern, so it is read from the map the cell loop ranks candidates with
    // rather than driven through the model. There is no trainee_goals source yet, so no run can
    // produce a goal cell; the map is what a goals source will reach, and it has to be right now.
    $priority = calendarTabsPriority();

    expect($priority)->toHaveKeys(['goal', 'current', 'fan_locked', 'maiden_locked', 'open', 'past'])
        ->and($priority['goal'])->toBeGreaterThan($priority['current'])
        ->and($priority['current'])->toBeGreaterThan($priority['fan_locked'])
        ->and($priority['past'])->toBeLessThan($priority['open']);

    // The loop only replaces the state on a strict improvement, so a tie cannot silently demote the
    // earlier candidate, and the current turn enters the same loop as a candidate rather than
    // overwriting the result afterwards.
    expect(calendarTabsSource())
        ->toContain('if ((priority[candidate] ?? 0) > (priority[state] ?? 0))')
        ->toContain('const candidates = [...slots.map((slotItem) => slotItem.state), ...(isNext ? [\'current\'] : [])]');
});

it('keeps the career year when the entry mode is switched, which the mode form used to drop', function (): void {
    // The year tabs are links built through `request()->fullUrlWithQuery`, so they carry `entry_mode`
    // for free. The mode buttons were the mirror image and were not the mirror fix: the submitted fields
    // of a GET form replace the action's query string whole, so pressing "Race not on the calendar"
    // from the Classic tab sent `entry_mode` and nothing else, and the panel answered with the year the
    // run has actually reached rather than the tab the Trainer was sitting in. The port keeps carrying
    // the year on the switch, and `careerYearForTab` resolves it server-side.
    $run = runWithTurn(1);

    // Through the screen rather than a bare component render: the year arrives the way a Trainer's
    // does, as a query parameter, and the panel reads `$errors` — which only exists on a request.
    $this->get(route('runs.show', $run).'?year=2')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        // Both mode switches carry the resolved tab year rather than the run's actual one, and the
        // panel answered with the Classic branch: the same fact seen from the heading it prints.
        ->where('calendar.year', 2)
        ->where('calendar.yearWord', 'Classic')
        ->where('racePanel.year', 2)
        ->where('racePanel.yearLabel', 'Classic')
        ->where('racePanel.entryMode', 'calendar'));
});
