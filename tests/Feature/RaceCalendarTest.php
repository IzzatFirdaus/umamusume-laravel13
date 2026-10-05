<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * G-28 / D-173, run over the Race Calendar component, after the Inertia port (ADR-0020 Â§1).
 *
 * The gate is "distinguishable without reading the text", so the two lock states are
 * asserted on their *treatment* â€” border style, fill, and the number â€” not only on
 * their labels. Asserting the words alone would pass a component that draws both
 * locks identically and relies on the text to tell them apart, which is exactly the
 * failure D-173 calls a review failure.
 *
 * The calendar is a Vue component now (`resources/js/components/RaceCalendar.vue`) and PHP cannot mount
 * one, so this file reads the three maps it owns â€” state to treatment, state to spoken word, and the
 * priority that picks a cell's border â€” straight out of the source and asserts them per entry. That
 * is a stronger reading than the rendered-HTML regexes this file replaces: "the cell that says Tenno
 * Sho is drawn how" is now a fact about the `fan_locked` entry alone, where before it was a
 * substring that any cell in the document could have satisfied.
 *
 * One case is gone, with the reason. "Refuses a scenario it has no descriptor for" was a render-time
 * throw on a `scenario` prop the Vue calendar is never handed (G-33): the page decides whether the
 * panel exists at all, from the resolved matrix, and sends `show` plus the cells. The two claims that
 * depended on the component owning that decision are asserted instead as `calendar.show` and
 * `calendar.cells` on the page, which is where the decision lives now.
 */

/**
 * The component's own source. Three maps in its script block are the whole contract D-173 is about,
 * and the template is what carries them onto the page.
 */
function calendarSource(): string
{
    return (string) file_get_contents(base_path('resources/js/components/RaceCalendar.vue'));
}

/**
 * State to treatment, read per entry.
 *
 * @return array<string, string>
 */
function calendarStateClasses(): array
{
    preg_match('/const stateClass: Record<string, string> = \{(.*?)\n\};/s', calendarSource(), $block);
    preg_match_all("/^\s{4}(\w+):\s*'([^']*)',/m", $block[1] ?? '', $rows, PREG_SET_ORDER);

    return array_column($rows, 2, 1);
}

/**
 * State to the word a screen reader hears, read per entry.
 *
 * @return array<string, string>
 */
function calendarStateWords(): array
{
    preg_match('/const stateWord: Record<string, string> = \{(.*?)\n\};/s', calendarSource(), $block);
    preg_match_all("/^\s{4}(\w+):\s*'([^']*)',/m", $block[1] ?? '', $rows, PREG_SET_ORDER);

    return array_column($rows, 2, 1);
}

/**
 * Which state wins a cell that holds more than one, read per entry.
 *
 * @return array<string, int>
 */
function calendarPriority(): array
{
    preg_match('/const priority: Record<string, number> = \{(.*?)\n\};/s', calendarSource(), $block);
    preg_match_all('/^\s{4}(\w+):\s*(\d+),/m', $block[1] ?? '', $rows, PREG_SET_ORDER);

    $priority = [];

    foreach ($rows as $row) {
        $priority[$row[1]] = (int) $row[2];
    }

    return $priority;
}

/**
 * A run in the scenario a case is about, with one logged turn so the calendar is not pre-career.
 */
function calendarRun(string $scenario = 'ura_finale'): TrainingRun
{
    $run = TrainingRun::factory()->create(['scenario' => $scenario]);

    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1, 'fans' => 20000]);

    return $run->fresh();
}

/**
 * The calendar's own payload, as the page ships it.
 */
function calendarPage(TrainingRun $run, array $query = []): Collection
{
    $page = test()->get('/training-runs/'.$run->id.$query)->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page->component('Runs/Show'));

    return $page;
}

it('draws the client grid: four cells across, six rows, one row per month pair', function (): void {
    $source = calendarSource();

    // The client's own panel is four cells wide and six rows tall, each row the Early and Late halves
    // of two consecutive months, with the half-month caption under the box. The build was the same 24
    // slots transposed into twelve columns and two rows, which is why it needed a scroll band a
    // desktop tool cannot overflow at (docs/UX Behavior Specification - Umamusume Trainer
    // Companion.md line 18).
    expect($source)->toContain('grid grid-cols-4 items-start')
        ->not->toContain('grid-cols-12')
        ->not->toContain('min-w-224')
        // The cell count is derived from the month list rather than written out twice, so the grid
        // and the "24 turn slots" caption cannot disagree.
        ->toContain('const slotCount = computed(() => props.monthLabels.length * 2)')
        ->toContain('Array.from({ length: 24 }');

    // Row order is the turn order: a Trainer reading down the left column walks the year. Each cell is
    // built from its slot index by halving it into a month and taking the remainder as the half, and
    // the caption prints the two words in that same order.
    expect($source)->toContain('const monthIndex = Math.floor(slotIndex / 2)')
        ->toContain("const half = slotIndex % 2 === 0 ? 'Early' : 'Late'")
        ->toContain('{{ cell.half }} {{ cell.month }}');
});

it('draws a fan lock as a number you can work toward', function (): void {
    $fan = calendarStateClasses()['fan_locked'] ?? '';

    // D-173 / G-16a: the figure is the cell's content, not a padlock. The treatment is a solid 2px
    // border over a sunken fill, which is the treatment the maiden lock must not reuse.
    expect($fan)
        ->toContain('border-2')
        ->toContain('border-solid')
        ->toContain('bg-sunken')
        // The figure itself, read off the formatted string the page sends rather than re-formatted
        // here: a fan figure is a display value, and a second formatter is a second place to be wrong.
        ->and(calendarSource())->toContain('{{ cell.fans }} fans');
});

it('draws the maiden lock differently from the fan lock, not just differently in words', function (): void {
    $classes = calendarStateClasses();
    $fan = $classes['fan_locked'] ?? '';
    $maiden = $classes['maiden_locked'] ?? '';

    // D-173: the maiden gate uses a dashed outline, because its remedy is an event rather than a
    // quantity. `bg-sunken` vs `bg-raised` alone is not a distinguishable treatment, it is a shade.
    expect($maiden)
        ->toContain('border-2')
        ->toContain('border-dashed')
        ->not->toContain('border-solid')
        ->not->toBe($fan);
});

it('prints no fan figure on a maiden cell, because a maiden gate is not a target', function (): void {
    $run = calendarRun();
    RaceCatalogSlot::factory()->create([
        'scenario_key' => null, 'year' => 1, 'month' => 5, 'half' => 'Early', 'turn' => 9,
        'title' => 'Naruta Kinpa Cup', 'is_maiden_gated' => true, 'fans_needed' => 0,
    ]);

    // Both halves of the claim, and they are different halves. The model withholds the key entirely,
    // so the page ships no figure to print; the component reads a figure only on a fan gate, so even a
    // payload that carried one would not put it on a maiden cell.
    $cells = $run->fresh()->calendarCells(1);
    expect($cells[4]['halves']['Early']['slots'][0]['state'])->toBe('maiden_locked')
        ->and($cells[4]['halves']['Early']['slots'][0])->not->toHaveKey('fans');

    expect(calendarSource())->toContain("state === 'fan_locked' ? (slots[0]?.fans ?? null) : null");
});

it('keeps the two locks separable when the state word is stripped', function (): void {
    $classes = calendarStateClasses();

    // Same assertion the reviewer makes: hide the labels, and the two cells must still differ by
    // border style alone. Read from the class strings themselves rather than from a rendered page,
    // which is what makes it independent of the words.
    expect($classes['maiden_locked'])->toContain('border-dashed')
        ->and($classes['fan_locked'])->not->toContain('border-dashed')
        ->and($classes['fan_locked'])->toContain('border-solid')
        // And every state that draws a border names one, so no lock is the absence of a treatment.
        ->and(collect($classes))->toHaveCount(7);
});

it('names each cell with its month, half and state, not its race name alone', function (): void {
    // A labelled cell's state lives in its border and tint, so a screen reader hearing only "Tenno Sho"
    // would not know the race is gated. D-181 keeps the visual signal; this is the same fact in the
    // accessible name, and the name leads with the same caption the cell is wearing, so what a screen
    // reader says and what the Trainer reads are the same words in the same order.
    expect(calendarSource())
        ->toContain(':aria-label="`${cell.half} ${cell.month}: ${stateWord[cell.state]}, ${cell.ariaText}`"')
        // `role="img"` is what makes the label a name at all: a label on a bare div is a property most
        // technologies do not expose.
        ->toContain('role="img"');

    expect(calendarStateWords())->toMatchArray([
        'fan_locked' => 'Fan gate',
        'maiden_locked' => 'Maiden rule',
        'goal' => 'Mandatory goal',
        'empty' => 'No race',
    ]);
});

it('renders a red Goal pennant on mandatory goal cells', function (): void {
    $source = calendarSource();

    // D-181 / DESIGN.md Â§6.9: "A goal race announces itself with a `Goal` pennant, a heavier warm
    // outline and greater height", and the pennant is the client's own red Goal flag. The treatment,
    // not a sentence, carries the mandate â€” and it is on the goal state alone, not on every cell.
    expect($source)->toContain("v-if=\"cell.state === 'goal'\"")
        // A triangle needs the two dead sides transparent and only the filled edge coloured.
        // `border-green` on its own sets border-color on all four sides, and which of it and
        // `border-transparent` wins is Tailwind's sheet order, not the class order â€” so the edges
        // are named one at a time.
        ->toContain('border-t-3 border-b-3 border-l-5 border-t-transparent border-b-transparent border-l-goal')
        ->toContain('aria-hidden="true"');
});

it('renders a mandatory goal heavier, never dimmer', function (): void {
    $classes = calendarStateClasses();
    $goal = $classes['goal'] ?? '';
    $empty = $classes['empty'] ?? '';

    // D-173: a goal race must not read as a weaker version of an empty slot. The contract names a
    // `raised` fill, a warm outline and greater height, so all three are asserted â€” and the dimming
    // that would break "never dimmer" is ruled out rather than trusted.
    expect($goal)
        ->toContain('bg-raised')
        ->toContain('border-2')
        ->toContain('border-goal-line')
        ->toContain('text-ink-strong')
        ->toContain('py-3')
        // The empty cell is the one carrying the muted plus, and it must not reach the goal's height.
        ->and($empty)->not->toContain('py-3')
        // Cells are aligned to the top so a taller goal cell stays taller than its row, which is what
        // carries "greater height" in a grid.
        ->and(calendarSource())->toContain('items-start');

    // The dimming is ruled out across the component, not just on this one state: an `opacity-` utility
    // anywhere in the calendar would be a state the client never dims.
    expect(calendarSource())->not->toMatch('/opacity-\d/');
});

it('renders nothing at all for a scenario with no race calendar', function (string $scenario): void {
    // D-221 / G-34: Trackblazer has no mandatory race goals. An empty grid would still claim the
    // scenario has a calendar, so the page sends `show: false` and no cells, and the component's
    // absence is the page's decision rather than the component's own lookup.
    $run = calendarRun($scenario);

    test()->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('calendar.show', false)
        ->where('calendar.cells', []));
})->with(['trackblazer', 'our_grand_concert']);

it('renders the calendar for both scenarios that own race goals', function (string $scenario): void {
    $run = calendarRun($scenario);

    // One catalogued race is what turns the panel from its "nothing entered" message into the grid,
    // so it is seeded here: the cells are not twelve empty months the model invents, they are the
    // twelve months the catalogue gives it.
    RaceCatalogSlot::factory()->create([
        'scenario_key' => null, 'year' => 1, 'month' => 4, 'half' => 'Early', 'turn' => 7, 'title' => 'Oka Sho',
    ]);

    test()->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('calendar.show', true)
        // Twelve months January-first, so the grid's own month index is the cell's index and the
        // component can index into the list without a lookup.
        ->where('calendar.cells', fn (Collection $cells): bool => $cells->count() === 12
            && $cells->keys()->all() === range(0, 11)));
})->with(['ura_finale', 'unity_cup']);

it('names the cause and the next action when the run has no races entered', function (): void {
    $run = calendarRun();

    // antislop R-27: "No data" tells the Trainer nothing. The scenario owns the calendar either way,
    // so the grid stays â€” what is missing is the Trainer's own entries, and the panel has to say which
    // and what to do. The scenario flag and the empty cells are both on the payload, so the branch is
    // the page's to take and the component's to honour.
    test()->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('calendar.show', true)
        ->where('calendar.cells', []));

    expect(calendarSource())
        ->toContain('No races entered for this run yet')
        ->toContain('{{ slotCount }} turn slots');
});

it('falls back to the state word when a cell carries no label', function (): void {
    $source = calendarSource();

    // Two places print a cell's name and both fall back the same way: the list row and the single
    // label. A fallback in one and not the other would print "Entry open" in the box and the race's
    // name nowhere.
    expect($source)
        ->toContain('{{ slotItem.label ?? stateWord[cell.state] }}')
        ->toContain('{{ cell.slots[0]?.label ?? stateWord[cell.state] }}');
});

it('treats an unrecognised cell state as an empty slot rather than rendering nothing', function (): void {
    $source = calendarSource();

    // The maps are the only guard: a state nothing knows has no class and no word, so it is folded
    // onto `empty` before either is read. Without the guard the class binding would resolve to
    // nothing and the cell would lose its box.
    expect($source)->toContain('if (!(state in stateClass)) {')
        ->toContain("state = 'empty';");
});

it('contains no scenario name anywhere in the component', function (): void {
    // Comments are stripped first: the component's own prose names the scenarios whose ports retired a
    // Blade file. The gate is about executable code, not documentation.
    $code = (string) preg_replace(
        ['#<!--.*?-->#s', '#/\*.*?\*/#s', '#^\s*//.*$#m'],
        '',
        calendarSource(),
    );

    foreach (array_keys(config('scenarios.scenarios')) as $key) {
        expect($code)->not->toContain($key);
    }
});

it('renders multiple slots in one half-month cell as a stack, not one-per-cell', function (): void {
    $run = calendarRun();
    RaceCatalogSlot::factory()->create([
        'scenario_key' => null, 'year' => 1, 'month' => 8, 'half' => 'Early', 'turn' => 15, 'title' => 'Cosmos Sho',
    ]);
    RaceCatalogSlot::factory()->create([
        'scenario_key' => null, 'year' => 1, 'month' => 8, 'half' => 'Early', 'turn' => 16, 'title' => 'Dahlia Sho',
    ]);

    // All three rows share one cell, so the model must put them in one half-month rather than one per
    // cell, and the component must render a list when there is more than one.
    $cells = $run->fresh()->calendarCells(1);
    expect($cells[7]['halves']['Early']['slots'])->toHaveCount(2)
        ->and(array_column($cells[7]['halves']['Early']['slots'], 'label'))->toBe(['Cosmos Sho', 'Dahlia Sho']);

    $source = calendarSource();
    expect($source)
        ->toContain('v-else-if="cell.multi"')
        ->toContain('v-for="(slotItem, slotIndex) in cell.slots"')
        // One accessible name for the whole cell, naming every race in it and how many there are.
        ->toContain('${slots.length} races: ${slots.map((slotItem) => slotItem.label ?? \'\').join(\', \')}');
});

it('renders an empty cell when a half-month has no slots', function (): void {
    $run = calendarRun();

    // One catalogued race is enough to build the grid; the other twenty-three half-months stay empty
    // and have to exist anyway, because the grid is 24 slots whatever this run has entered.
    RaceCatalogSlot::factory()->create([
        'scenario_key' => null, 'year' => 1, 'month' => 4, 'half' => 'Early', 'turn' => 7, 'title' => 'Oka Sho',
    ]);

    $cells = $run->fresh()->calendarCells(1);
    expect($cells)->toHaveCount(12)
        // April Early holds the only race, so April Late beside it is the empty cell this is about.
        ->and($cells[3]['halves']['Early']['slots'])->toHaveCount(1)
        ->and($cells[3]['halves']['Late']['slots'])->toBe([])
        ->and($cells[8]['halves']['Late']['slots'])->toBe([]);

    expect(calendarSource())->toContain('v-if="cell.slots.length === 0"');
});

it('labels manual (Trainer-entered) rows distinctly from seeded ones', function (): void {
    $run = calendarRun();
    ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale', 'kind' => 'free_race', 'title' => 'Local Stakes',
        'slot_label' => 'Local Stakes', 'month' => 9, 'half' => 'Late', 'is_manual' => true,
    ]);

    // The marker is the model's flag and the label is the component's word for it; neither half proves
    // the other, so both are asserted here and the model's own retention of the flag is
    // RaceSlotPanelComposerTest's.
    $cells = $run->fresh()->calendarCells(1);
    expect($cells[8]['halves']['Late']['slots'][0])
        ->toMatchArray(['label' => 'Local Stakes', 'manual' => true]);

    expect(calendarSource())->toContain('manual: slots.some((slotItem) => slotItem.manual === true)')
        ->toContain('Trainer-entered');
});

it('draws an empty half-month as the client does: a grey box and a muted plus, no words', function (): void {
    $empty = calendarStateClasses()['empty'] ?? '';

    // The measured empty cell is #D0D1D0 (DESIGN.md Â§6.20), which is this tool's `disabled` token. The
    // words left the box because twenty-four of them describing nothing is the grid talking about itself;
    // the state still travels in the accessible name, which is where a screen reader meets it.
    expect($empty)->toContain('bg-disabled')
        ->and(calendarStateWords()['empty'] ?? '')->toBe('No race')
        ->and(calendarSource())
        // The plus is decorative and hidden from assistive tech, because the name already said "No race"
        // and a bare "+" read aloud would be a second, worse sentence.
        ->toContain('text-base leading-none text-ink-faint" aria-hidden="true">+</span>')
        // The word is reachable only through the accessible name and the label fallback, both of which
        // the empty branch never renders.
        ->toContain('{{ cell.slots[0]?.label ?? stateWord[cell.state] }}');
});

it('draws the current turn in the client pale yellow with a warm outline', function (): void {
    $current = calendarStateClasses()['current'] ?? '';

    // "Pale yellow fill with a warm outline" is the measured treatment, and `pick` is the pair this tool
    // already uses for the selected year tab, so the fill and its ink come from one place rather than a
    // new hex.
    expect($current)
        ->toContain('bg-pick')
        ->toContain('text-on-pick')
        ->toContain('border-pick-line')
        ->and(calendarSource())->toContain("'; next turn to play'");
});

it('marks a race this run has put on a slot with the client Scheduled pill, undimmed', function (): void {
    $run = calendarRun();
    $slot = RaceCatalogSlot::factory()->create([
        'scenario_key' => null, 'year' => 1, 'month' => 3, 'half' => 'Early', 'turn' => 6, 'title' => 'Oka Sho',
    ]);
    RaceEntry::create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 3,
    ]);

    // UX Â§2.11: completed and entered races use the client's own pink Scheduled pill and are never
    // dimmed. The model keeps the state `past`, because that is the fact about the entry, and the pill
    // is drawn from that fact rather than from the border.
    $cells = $run->fresh()->calendarCells(1);
    expect($cells[2]['halves']['Early']['slots'][0]['state'])->toBe('past');

    $source = calendarSource();
    expect($source)
        ->toContain("const entered = slots.some((slotItem) => slotItem.state === 'past');")
        ->toContain('v-if="cell.entered"')
        ->toContain('Scheduled')
        // Undimmed means no opacity utility on the pill, which the component-wide dimming rule above
        // already covers; the pair's own fills are asserted so a renamed ink would show up here.
        ->toContain('bg-mood-great px-1.5 py-0.5 font-mono text-[10px] font-bold text-on-mood');
});

it('still shows the Scheduled pill when a second race in the same half-month owns the border', function (): void {
    $run = calendarRun();
    RaceCatalogSlot::factory()->create([
        'scenario_key' => null, 'year' => 1, 'month' => 3, 'half' => 'Early', 'turn' => 6, 'title' => 'Asahi Hai',
    ]);
    $entered = RaceCatalogSlot::factory()->create([
        'scenario_key' => null, 'year' => 1, 'month' => 3, 'half' => 'Early', 'turn' => 6, 'title' => 'Oka Sho',
    ]);
    RaceEntry::create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $entered->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
    ]);

    // The border follows the stronger claim on the cell; the pill follows the fact about the race.
    // Keying the pill to the cell state made an entered race vanish from a shared half-month the
    // moment anything else landed in it, which is the case the multiplicity fix exists to support.
    $slots = $run->fresh()->calendarCells(1)[2]['halves']['Early']['slots'];
    expect($slots)->toHaveCount(2)
        ->and(array_column($slots, 'state'))->toBe(['open', 'past']);

    // The two are computed from different inputs and neither reads the other: `state` comes out of the
    // priority loop over the slot states, `entered` out of a `some()` over the same slots.
    $priority = calendarPriority();
    expect($priority['open'])->toBeGreaterThan($priority['past'])
        ->and(calendarSource())->toContain('v-if="cell.entered"');
});
