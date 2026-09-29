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
        0 => ['Early' => [['state' => 'goal', 'label' => 'Fuwa Fuji Taima Stakes']]],
        3 => ['Late' => [['state' => 'fan_locked', 'label' => 'Tenno Sho', 'fans_needed' => 12000]]],
        4 => ['Early' => [['state' => 'maiden_locked', 'label' => 'Naruta Kinpa Cup']]],
        5 => ['Early' => [['state' => 'open', 'label' => 'Entry open']]],
        1 => ['Late' => [['state' => 'current', 'label' => 'Next']]],
    ];

    $cells = [];

    for ($month = 0; $month < 12; $month++) {
        $cells[] = [
            'halves' => [
                'Early' => ['slots' => $states[$month]['Early'] ?? []],
                'Late' => ['slots' => $states[$month]['Late'] ?? []],
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

/**
 * The half-month captions, in the order the grid lays them out.
 *
 * Read off the rendered text rather than a marker attribute: the caption is what the
 * Trainer uses to find a cell once the month header row is gone, so its wording and
 * its order are the contract.
 *
 * @return list<string>
 */
function captionsOf(string $html): array
{
    preg_match_all(
        '#>((?:Early|Late) (?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec))</span>#',
        $html,
        $matches,
    );

    return $matches[1];
}

it('draws the client grid: four cells across, six rows, one row per month pair', function (): void {
    $html = renderCalendar('ura_finale', calendarCells());

    // The client's own panel (docs/game-screenshots/Screenshot 2026-07-17 230755.png) is four
    // cells wide and six rows tall, each row the Early and Late halves of two consecutive months,
    // with the half-month caption under the box. The build was the same 24 slots transposed into
    // twelve columns and two rows, which is why it needed a scroll band a desktop tool cannot
    // overflow at (`docs/UX Behavior Specification - Umamusume Trainer Companion.md` line 18).
    expect($html)->toContain('grid-cols-4')
        ->and($html)->not->toContain('grid-cols-12')
        ->and($html)->not->toContain('min-w-224')
        ->and(substr_count($html, 'w-full rounded-md border px-1'))->toBe(24);

    // Row order is the turn order: a Trainer reading down the left column walks the year.
    $captions = captionsOf($html);

    expect($captions)->toHaveCount(24)
        ->and(array_slice($captions, 0, 4))->toBe(['Early Jan', 'Late Jan', 'Early Feb', 'Late Feb'])
        ->and(array_slice($captions, 4, 4))->toBe(['Early Mar', 'Late Mar', 'Early Apr', 'Late Apr'])
        ->and(array_slice($captions, 20, 4))->toBe(['Early Nov', 'Late Nov', 'Early Dec', 'Late Dec']);
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
    $cells[4]['halves']['Early']['slots'][0]['fans_needed'] = 0;

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
    preg_match_all('/class="(w-full rounded-md border[^"]*)"/', $html, $matches);

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
    // The accessible name leads with the same caption the cell is wearing, so what a screen
    // reader says and what the Trainer reads are the same words in the same order.
    expect($html)
        ->toContain('aria-label="Late Apr: Fan gate, Tenno Sho"')
        ->toContain('aria-label="Early May: Maiden rule, Naruta Kinpa Cup"')
        ->toContain('aria-label="Early Jan: Mandatory goal, Fuwa Fuji Taima Stakes"')
        ->toContain('aria-label="Late Sep: No race, No race"');
});

it('renders a red Goal pennant on mandatory goal cells', function (): void {
    $html = renderCalendar('ura_finale', calendarCells());

    // D-181 / DESIGN.md §6.9: "A goal race announces itself with a `Goal` pennant,
    // a heavier warm outline and greater height", and the pennant is the client's
    // own red Goal flag. The treatment, not a sentence, carries the mandate.
    expect($html)
        ->toContain('aria-label="Early Jan: Mandatory goal, Fuwa Fuji Taima Stakes"')
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
    // The first cell carrying the empty-cell plus — Jan Late, since Jan Early is the
    // goal. "No race" now lives in the accessible name, so the plus is what marks the
    // box as the empty one.
    $empty = cellClassFor($html, '+');

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

    expect(trim($html))->toBe('')->not->toContain('Race calendar')->not->toContain('w-full rounded-md border');
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
    $cells[7]['halves']['Early'] = ['slots' => [['state' => 'teleported']]];

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

it('renders multiple slots in one half-month cell as a stack, not one-per-cell', function (): void {
    $cells = calendarCells();
    // Replace August Early with three races (the Early August triple).
    $cells[7]['halves']['Early'] = ['slots' => [
        ['state' => 'open', 'label' => 'Cosmos Sho'],
        ['state' => 'open', 'label' => 'Dahlia Sho'],
        ['state' => 'open', 'label' => 'Phoenix Sho'],
    ]];

    $html = renderCalendar('ura_finale', $cells);

    expect($html)
        ->toContain('Cosmos Sho')
        ->toContain('Dahlia Sho')
        ->toContain('Phoenix Sho')
        // All three are inside one cell, so they share one aria-label.
        ->toContain('3 races: Cosmos Sho, Dahlia Sho, Phoenix Sho');
});

it('renders an empty cell when a half-month has no slots', function (): void {
    $cells = calendarCells();
    // September Late is already empty in the fixture.
    $html = renderCalendar('ura_finale', $cells);

    expect($html)->toContain('Late Sep: No race, No race');
});

it('labels manual (Trainer-entered) rows distinctly from seeded ones', function (): void {
    $cells = calendarCells();
    $cells[6]['halves']['Late'] = ['slots' => [
        ['state' => 'past', 'label' => 'Local Stakes (Trainer-entered)'],
    ]];

    $html = renderCalendar('ura_finale', $cells);

    expect($html)->toContain('Trainer-entered');
});

it('draws an empty half-month as the client does: a grey box and a muted plus, no words', function (): void {
    $html = renderCalendar('ura_finale', calendarCells());
    $empty = cellClassFor($html, '+');

    // The measured empty cell is #D0D1D0 (DESIGN.md §6.20), which is this tool's
    // `disabled` token. The words left the box because twenty-four of them describing
    // nothing is the grid talking about itself; the state still travels in the
    // accessible name, which is where a screen reader meets it.
    expect($empty)->toContain('bg-disabled')
        ->and($html)->toContain('aria-label="Late Jan: No race, No race"')
        ->and($html)->not->toMatch('/>\s*No race\s*</');
});

it('draws the current turn in the client pale yellow with a warm outline', function (): void {
    $html = renderCalendar('ura_finale', calendarCells());
    $current = cellClassFor($html, 'Next');

    // "Pale yellow fill with a warm outline" is the measured treatment, and `pick` is
    // the pair this tool already uses for the selected year tab, so the fill and its
    // ink come from one place rather than a new hex.
    expect($current)
        ->toContain('bg-pick')
        ->toContain('text-on-pick')
        ->toContain('border-pick-line')
        ->and($html)->toContain('aria-label="Late Feb: Next, Next"');
});

it('marks a race this run has put on a slot with the client Scheduled pill, undimmed', function (): void {
    $cells = calendarCells();
    $cells[2]['halves']['Early'] = ['slots' => [
        ['state' => 'past', 'label' => 'Oka Sho'],
    ]];

    $html = renderCalendar('ura_finale', $cells);
    $cell = cellClassFor($html, 'Oka Sho');

    // UX §2.11: completed and entered races use the client's own pink Scheduled pill
    // and are never dimmed. The visible word and the spoken one are the same word, so
    // the state list does not call this cell something the Trainer cannot see.
    expect($html)->toContain('>Scheduled</span>')
        ->and($html)->toContain('aria-label="Early Mar: Scheduled, Oka Sho"')
        ->and($cell)->not->toMatch('/opacity-\d/');
});

it('still shows the Scheduled pill when a second race in the same half-month owns the border', function (): void {
    $cells = calendarCells();
    $cells[2]['halves']['Early'] = ['slots' => [
        ['state' => 'open', 'label' => 'Asahi Hai'],
        ['state' => 'past', 'label' => 'Oka Sho'],
    ]];

    $html = renderCalendar('ura_finale', $cells);

    // The border follows the stronger claim on the cell; the pill follows the fact about
    // the race. Keying the pill to the cell state made an entered race vanish from a
    // shared half-month the moment anything else landed in it, which is the case the
    // multiplicity fix exists to support.
    expect($html)->toContain('>Scheduled</span>')
        ->and($html)->toContain('Early Mar: Entry open, 2 races: Asahi Hai, Oka Sho; one entered');
});
