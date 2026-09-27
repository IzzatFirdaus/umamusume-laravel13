<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\View\ViewException;

/*
 * G-28 / D-173, run over the Race Calendar component.
 *
 * The gate is "distinguishable without reading the text", so the two lock states are
 * asserted on their *treatment* — border style, fill, and the number — not only on
 * their labels. Asserting the words alone would pass a component that draws both
 * locks identically and relies on the text to tell them apart, which is exactly the
 * failure D-173 calls a review failure.
 */

/**
 * Twelve months, Early and Late, with one cell of each state the client can show.
 *
 * @return list<array{halves: array<string, array<string, mixed>>}>
 */
function calendarCells(): array
{
    $states = [
        0 => ['Early' => ['state' => 'goal', 'label' => 'Fuwa Fuji Taima Stakes']],
        3 => ['Late' => ['state' => 'fan_locked', 'label' => 'Tenno Sho', 'fans_needed' => 12000]],
        4 => ['Early' => ['state' => 'maiden_locked', 'label' => 'Naruta Kinpa Cup']],
        5 => ['Early' => ['state' => 'open', 'label' => 'Entry open']],
        1 => ['Late' => ['state' => 'current', 'label' => 'Next']],
    ];

    $cells = [];

    for ($month = 0; $month < 12; $month++) {
        $cells[] = [
            'halves' => [
                'Early' => $states[$month]['Early'] ?? ['state' => 'empty'],
                'Late' => $states[$month]['Late'] ?? ['state' => 'empty'],
            ],
        ];
    }

    return $cells;
}

function renderCalendar(string $scenario, array $cells = [], array $overrides = []): string
{
    return Blade::render(
        '<x-race-calendar :scenario="$scenario" :cells="$cells" />',
        array_merge(['scenario' => $scenario, 'cells' => $cells], $overrides),
    );
}

/**
 * The class of the single cell whose body carries $needle.
 *
 * Asserting "the HTML contains bg-sunken" would pass even if the sunken cell were
 * the wrong one, so the reviewer's real question is per-cell: the cell that says
 * Tenno Sho is drawn how, and the cell that says Naruta Kinpa Cup is drawn how.
 * Cells contain no nested divs, so the first div whose contents reach the needle
 * without crossing another div is that cell.
 */
function cellClassFor(string $html, string $needle): string
{
    preg_match('/<div class="([^"]*)"[^>]*>((?:(?!<div)[\s\S])*?'.preg_quote($needle, '/').')/', $html, $matches);

    return $matches[1] ?? '';
}

it('draws twenty-four turn slots, Early and Late for each of twelve months', function (): void {
    $html = renderCalendar('ura_finale', calendarCells());

    // The grid is the structure the Trainer plans against, so its shape is the
    // contract: twelve headers, then two rows of twelve. Counting the half labels
    // with their tags avoids matching the caption, which also names both halves.
    expect(substr_count($html, '>Early<'))->toBe(12)
        ->and(substr_count($html, '>Late<'))->toBe(12)
        ->and(substr_count($html, 'col-span-1 rounded-md border'))->toBe(24);

    foreach (['Jan', 'Jun', 'Dec'] as $month) {
        expect($html)->toContain($month);
    }
});

it('draws a fan lock as a number you can work toward', function (): void {
    $html = renderCalendar('ura_finale', calendarCells());
    $cell = cellClassFor($html, 'Tenno Sho');

    // D-173 / G-16a: the figure is the cell's content, not a padlock. The treatment
    // is a solid 2px border over a sunken fill, which is the treatment the maiden
    // lock must not reuse.
    expect($html)->toContain('12,000 fans')
        ->and($cell)
        ->toContain('border-2')
        ->toContain('border-solid')
        ->toContain('bg-sunken');
});

it('draws the maiden lock differently from the fan lock, not just differently in words', function (): void {
    $html = renderCalendar('ura_finale', calendarCells());
    $fan = cellClassFor($html, 'Tenno Sho');
    $maiden = cellClassFor($html, 'Naruta Kinpa Cup');

    // D-173: the maiden gate uses a dashed outline, because its remedy is an event
    // rather than a quantity. `bg-sunken` vs `bg-panel` alone is not a
    // distinguishable treatment, it is a shade.
    expect($maiden)
        ->toContain('border-2')
        ->toContain('border-dashed')
        ->not->toContain('border-solid')
        ->and($maiden)->not->toBe($fan);
});

it('prints no fan figure on a maiden cell, because a maiden gate is not a target', function (): void {
    $cells = calendarCells();
    // The design-preview sample used to pass fans_needed 0 here, which rendered
    // "0 fans" and told the Trainer to grind toward a number that decides nothing.
    $cells[4]['halves']['Early']['fans_needed'] = 0;

    $html = renderCalendar('ura_finale', $cells);

    expect($html)
        ->toContain('Naruta Kinpa Cup')
        // \b so this cannot match the "0 fans" inside "12,000 fans" two cells over.
        ->not->toMatch('/\b0 fans/');
});

it('keeps the two locks separable when the state word is stripped', function (): void {
    $html = renderCalendar('ura_finale', calendarCells());

    // Same assertion the reviewer makes: hide the labels, and the cells must still
    // differ by border style alone.
    $borders = [];
    preg_match_all('/class="(col-span-1 rounded-md border[^"]*)"/', $html, $matches);

    foreach ($matches[1] as $class) {
        $borders[] = str_contains($class, 'border-dashed') ? 'dashed' : 'solid';
    }

    expect($borders)->toContain('dashed')->toContain('solid');
});

it('names each cell with its month, half and state, not its race name alone', function (): void {
    $html = renderCalendar('ura_finale', calendarCells());

    // A labelled cell's state lives in its border and tint, so a screen reader
    // hearing only "Tenno Sho" would not know the race is gated. D-181 keeps the
    // visual signal; this is the same fact in the accessible name.
    expect($html)
        ->toContain('aria-label="Apr Late: Fan gate, Tenno Sho"')
        ->toContain('aria-label="May Early: Maiden rule, Naruta Kinpa Cup"')
        ->toContain('aria-label="Jan Early: Mandatory goal, Fuwa Fuji Taima Stakes"')
        ->toContain('aria-label="Sep Late: No race"');
});

it('renders a red Goal pennant on mandatory goal cells', function (): void {
    $html = renderCalendar('ura_finale', calendarCells());

    // D-181 / DESIGN.md §6.9: "A goal race announces itself with a `Goal` pennant,
    // a heavier warm outline and greater height", and the pennant is the client's
    // own red Goal flag. The treatment, not a sentence, carries the mandate.
    expect($html)
        ->toContain('aria-label="Jan Early: Mandatory goal, Fuwa Fuji Taima Stakes"')
        // A triangle needs the two dead sides transparent and only the filled edge
        // coloured. `border-green` on its own sets border-color on all four sides,
        // and which of it and `border-transparent` wins is Tailwind's sheet order,
        // not the class order — so the edges are named one at a time.
        ->toMatch('/border-t-transparent/')
        ->toMatch('/border-b-transparent/')
        ->toMatch('/border-l-goal/');
});

it('renders a mandatory goal heavier, never dimmer', function (): void {
    $html = renderCalendar('ura_finale', calendarCells());
    $goal = cellClassFor($html, 'Fuwa Fuji Taima Stakes');
    // The first cell that reads "No race" — Jan Late, since Jan Early is the goal.
    $empty = cellClassFor($html, 'No race');

    // D-173: a goal race must not read as a weaker version of an empty slot. The
    // contract names a `raised` fill, a warm outline, and greater height, so all
    // three are asserted — and the dimming that would break "never dimmer" is
    // ruled out rather than trusted.
    expect($goal)
        ->toContain('bg-raised')
        ->toContain('border-2')
        ->toContain('border-goal-line')
        ->toContain('text-ink-strong')
        ->not->toMatch('/opacity-\d/')
        // Greater height, carried by padding because the row is a grid: cells are
        // aligned to the top so a taller goal cell stays taller than its row.
        ->and($goal)->toContain('py-3')
        ->and($empty)->not->toContain('py-3')
        ->and($html)->toContain('items-start');
});

it('renders nothing at all for a scenario with no race calendar', function (string $scenario): void {
    // D-221 / G-34: Trackblazer has no mandatory race goals. An empty grid would
    // still claim the scenario has a calendar, so the component returns before its
    // markup — absence, not a disabled shell.
    $html = renderCalendar($scenario, calendarCells());

    expect(trim($html))->toBe('')->not->toContain('Race calendar')->not->toContain('col-span-1');
})->with(['trackblazer', 'our_grand_concert']);

it('renders the calendar for both scenarios that own race goals', function (string $scenario): void {
    expect(renderCalendar($scenario, calendarCells()))->toContain('Race calendar');
})->with(['ura_finale', 'unity_cup']);

it('names the cause and the next action when the run has no races entered', function (): void {
    $html = renderCalendar('ura_finale', []);

    // antislop R-27: "No data" tells the Trainer nothing. The scenario owns the
    // calendar either way, so the grid stays — what is missing is the Trainer's
    // own entries, and the panel has to say which and what to do.
    expect($html)
        ->toContain('No races entered for this run yet')
        ->toContain('24 turn slots');
});

it('falls back to the state word when a cell carries no label', function (): void {
    $html = renderCalendar('ura_finale', array_merge(calendarCells(), []));

    expect($html)->toContain('No race');
});

it('treats an unrecognised cell state as an empty slot rather than rendering nothing', function (): void {
    $cells = calendarCells();
    $cells[7]['halves']['Early'] = ['state' => 'teleported'];

    $html = renderCalendar('ura_finale', $cells);

    expect($html)->toContain('No race')->not->toContain('teleported');
});

it('refuses a scenario it has no descriptor for', function (): void {
    renderCalendar('jp_only_event', calendarCells());
})->throws(ViewException::class, 'Unknown scenario [jp_only_event] for x-race-calendar.');

it('contains no scenario name anywhere in the component', function (): void {
    $source = (string) file_get_contents(resource_path('views/components/race-calendar.blade.php'));
    $code = (string) preg_replace(['#\{\{--.*?--\}\}#s', '#/\*.*?\*/#s'], '', $source);

    foreach (array_keys(config('scenarios.scenarios')) as $key) {
        expect($code)->not->toContain($key);
    }
});
