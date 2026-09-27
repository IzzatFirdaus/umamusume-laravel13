<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\View\ViewException;

/*
 * The Grade Point meter (D-232, gate G-34).
 *
 * D-232's hard part is not the bar, it is the denominator. There are four objectives
 * and surplus never carries forward, so a running total would imply a banking
 * strategy the game does not have. A meter showing 540/900 is a different claim from
 * 240/300, and only the second one is true. The tests assert the *current* objective
 * is the only denominator on the surface, and that a banked surplus is visibly
 * dropped rather than quietly carried.
 */

/**
 * The four Trackblazer objectives: Debut race, then the end of each year. Standard
 * track; the aptitude-conditional variants live in the same config block.
 *
 * @return list<array{name: string, required: int}>
 */
function gradeObjectives(): array
{
    $standard = config('scenarios.scenarios.trackblazer.grade_objectives.standard');

    return [
        ['name' => 'Debut race', 'required' => 0],
        ['name' => 'End of Junior Year', 'required' => $standard['Junior']],
        ['name' => 'End of Classic Year', 'required' => $standard['Classic']],
        ['name' => 'End of Senior Year', 'required' => $standard['Senior']],
    ];
}

function renderMeter(array $overrides = []): string
{
    return Blade::render(
        '<x-grade-point-meter :scenario="$scenario" :objectives="$objectives" :current="$current" :earned="$earned" />',
        array_merge([
            'scenario' => 'trackblazer',
            'objectives' => gradeObjectives(),
            'current' => 2,
            'earned' => 240,
        ], $overrides),
    );
}

it('measures progress against the current objective only', function (): void {
    $html = renderMeter();

    // 240 of the Classic Year's 300. The other objectives' numbers must not appear
    // as a total to save against, or the meter reads as a bank.
    expect($html)
        ->toContain('240')
        ->toContain('300')
        ->not->toContain('660')   // 60 + 300 + 300 summed
        ->not->toContain('900');   // every objective summed
});

it('states that surplus does not carry over', function (): void {
    $html = renderMeter();

    expect($html)
        ->toContain('Surplus does not carry over')
        ->toContain('is not banked toward the next one');
});

it('shows a completed objective as complete and does not add its points to the bar', function (): void {
    $html = renderMeter(['current' => 3, 'earned' => 120]);

    // Junior (60) and Classic (300) are both met; Senior stands at 120/300. A
    // running total would read 480, which is the banking arithmetic D-232 forbids.
    expect($html)
        ->toContain('End of Junior Year')
        ->toContain('End of Classic Year')
        ->toContain('Complete')
        ->toContain('120')
        ->not->toContain('480');
});

it('draws every progress fill dark enough to see on its own track', function (): void {
    $html = renderMeter();

    // Found by the manual browser pass on 2026-09-28, not by a rule anyone had
    // written down. `bg-green` (#7FCC09) over the light `sunken` track (#E7E7EC)
    // measures 1.62:1 — WCAG 1.4.11 wants 3:1 for a graphic you need in order to
    // read the value, and the bar IS the value here. The same pair in dark measured
    // 8.84:1, so the defect only showed on the theme this app now treats as the
    // base. `bg-green-deep` is the theme-paired step of the same hue the "Complete"
    // word already uses: 4.20:1 on light sunken, 11.74:1 on dark sunken.
    expect($html)->toMatch('/<div class="h-full rounded-full bg-green-deep"/')
        ->and($html)->not->toMatch('/<div class="h-full rounded-full bg-green"/');

    // The stat band draws the same bar over the same track, so the correction is
    // asserted across both rather than left fixed in one (D-286). The guard reads
    // the sources because a bar fill is a treatment, not a value.
    foreach (['grade-point-meter', 'stat-band'] as $component) {
        $source = (string) file_get_contents(resource_path('views/components/'.$component.'.blade.php'));

        // `bg-green` must not appear as a whole class. A plain \b would break on
        // the hyphen in `bg-green-deep` and flag the fixed code.
        expect($source)->not->toMatch('/class="[^"]*\bbg-green(?![-\w])[^"]*"[^>]*style="width/')
            ->and($source)->toMatch('/bg-green-deep/');
    }
});

it('reports an over-achievement as spent rather than as progress past the cap', function (): void {
    $html = renderMeter(['earned' => 420]);

    // 420 against a 300 objective. Points past it are real points the Trainer
    // earned, and the meter has to say they go nowhere rather than draw a bar at
    // 140% or quietly top out at full.
    expect($html)
        ->toContain('120 over the objective')
        ->toContain('420')
        ->toContain('300')
        ->not->toContain('140%')
        ->not->toContain('720');
});

it('shows the objective ladder so the next deadline is legible', function (): void {
    $html = renderMeter();

    foreach (['End of Junior Year', 'End of Classic Year', 'End of Senior Year'] as $label) {
        expect($html)->toContain($label);
    }

    // The current one is marked, not merely present.
    expect($html)->toContain('aria-current="step"');
});

it('claims no figure when the run has entered none', function (): void {
    $html = renderMeter(['earned' => null]);

    // The deadlines are the scenario's and stay printed — they are the reason to
    // open the panel. The progress figure is the run's, and there isn't one, so a
    // zero would be a claim about a trainee that nobody entered.
    expect($html)
        ->toContain('End of Classic Year')
        ->toContain('300')
        ->toContain('not yet recorded')
        ->not->toMatch('/\b\d+ \/ \d+/')
        ->not->toContain('0 / 300');
});

it('names the first objective as the live one only because nothing is logged', function (): void {
    $html = renderMeter(['earned' => null, 'current' => 0]);

    // No objective is met, so the first one is unmet and therefore the live one.
    // That is a consequence of the empty log, not an assumption about the year.
    expect($html)
        ->toContain('Debut race')
        ->toContain('aria-current="step"')
        ->not->toContain('Complete');
});

it('renders nothing at all for a scenario with no Grade Point objectives', function (string $scenario): void {
    // D-221 / D-241: the meter substitutes for the calendar only where there are
    // objectives. On a URA or Unity Cup run it must be absent, not a disabled shell.
    $html = renderMeter(['scenario' => $scenario]);

    expect(trim($html))->toBe('')
        ->not->toContain('Grade Point')
        ->not->toContain('Surplus');
})->with(['ura_finale', 'unity_cup', 'our_grand_concert']);

it('shows the two countdowns the economy is actually driven by', function (): void {
    $html = renderMeter();

    // D-232 calls the rotation countdown the shop's primary number, not the balance.
    expect($html)->toContain('Rotation resets in');
});

it('does not invent a coin balance or a turn count', function (): void {
    $html = renderMeter();

    // The rotation is sourced and prints; the balance is run state and does not, so
    // it is named as missing rather than shown as 0 — a zero balance would read as
    // "you are broke" when the truth is "nothing entered yet".
    expect($html)
        ->toContain('not yet recorded')
        ->not->toMatch('/Shop Coins:\s*[\d,]+/');
});

it('refuses a scenario it has no descriptor for', function (): void {
    renderMeter(['scenario' => 'jp_only_event']);
})->throws(ViewException::class, 'Unknown scenario [jp_only_event] for x-grade-point-meter.');

it('contains no scenario name anywhere in the component', function (): void {
    $source = (string) file_get_contents(resource_path('views/components/grade-point-meter.blade.php'));
    $code = (string) preg_replace(['#\{\{--.*?--\}\}#s', '#/\*.*?\*/#s'], '', $source);

    foreach (array_keys(config('scenarios.scenarios')) as $key) {
        expect($code)->not->toContain($key);
    }
});
