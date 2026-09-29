<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\View\ViewException;

/*
 * D-220 / D-240 for `x-resource-strip`.
 *
 * D-220's failure mode is not a missing widget, it is a present-but-empty one: a
 * Team Rank box on a URA run states that the run has a team system. So the
 * assertions are negative as well as positive — the widget a scenario has no
 * mechanic for must not appear in the markup at all, not merely be disabled.
 *
 * D-240's failure mode is a scenario name in a view. The last test greps the
 * component for one, so adding a fifth scenario cannot smuggle a branch back in.
 */

/**
 * Per scenario: the widget labels its strip must carry, and the ones it must not.
 * Positional, because Pest's `with()` turns an associative value into named args.
 *
 * @return array<string, array{string, list<string>, list<string>}>
 */
function stripExpectations(): array
{
    $teamOnly = ['Team Rank', 'Spirit Bursts'];
    $shopOnly = ['Grade Points', 'Shop Coins'];
    $baseline = ['Turn', 'Energy', 'Fans'];

    return [
        'ura_finale' => ['ura_finale', $baseline, [...$teamOnly, ...$shopOnly]],
        'our_grand_concert' => ['our_grand_concert', $baseline, [...$teamOnly, ...$shopOnly]],
        'unity_cup' => ['unity_cup', [...$baseline, ...$teamOnly], $shopOnly],
        'trackblazer' => ['trackblazer', [...$baseline, ...$shopOnly], $teamOnly],
    ];
}

it('renders only the widgets the scenario actually has', function (string $scenario, array $present, array $absent): void {
    $html = Blade::render(
        '<x-resource-strip :scenario="$scenario" :run="$run" />',
        [
            'scenario' => $scenario,
            'run' => ['turn' => 27, 'energy' => 78, 'fans' => 41250, 'bursts' => 8, 'grade_points' => 240, 'shop_coins' => 185],
        ],
    );

    foreach ($present as $label) {
        expect($html)->toContain($label);
    }

    foreach ($absent as $label) {
        expect($html)->not->toContain($label);
    }
})->with(stripExpectations());

it('gives the baseline scenarios the sparsest strip in the matrix', function (): void {
    $baseline = count(config('scenarios.scenarios.ura_finale.widgets'));
    $richest = max(array_map(
        static fn (array $def): int => count($def['widgets']),
        config('scenarios.scenarios'),
    ));

    // G-33 asks a reviewer to see URA as the sparsest at a glance. If the baseline
    // ever grows to match a richer scenario, the strip has stopped being composed
    // by mechanic and this test is the thing that says so.
    expect($baseline)->toBeLessThan($richest)
        ->and($baseline)->toBe(3);
});

it('never prints a fan-gated event on a scenario with no race calendar', function (): void {
    // Trackblazer has no calendar and therefore no fan-gated event. The caption is a
    // claim about the run, so it is composed from the same panel flag the widget
    // list is composed from, not from the scenario's name.
    $html = Blade::render(
        '<x-resource-strip scenario="trackblazer" :run="$run" />',
        ['run' => ['turn' => 34, 'energy' => 41, 'fans' => 128400, 'grade_points' => 240, 'shop_coins' => 185]],
    );

    expect($html)->toContain('farmed by racing here')->not->toContain('next event gate');

    $ura = Blade::render(
        '<x-resource-strip scenario="ura_finale" :run="$run" />',
        ['run' => ['turn' => 27, 'energy' => 78, 'fans' => 41250]],
    );

    expect($ura)->toContain('next event gate 60,000');
});

it('contains no scenario name anywhere in the component', function (): void {
    // Comments are stripped first: the component's own prose names `unity_cup` in
    // the D-240 note explaining why it does not branch on it. The gate is about
    // executable code, not documentation.
    $source = (string) file_get_contents(
        resource_path('views/components/resource-strip.blade.php'),
    );

    $code = (string) preg_replace(['#\{\{--.*?--\}\}#s', '#/\*.*?\*/#s'], '', $source);

    foreach (array_keys(config('scenarios.scenarios')) as $key) {
        expect($code)->not->toContain($key);
    }
});

it('refuses a scenario it has no descriptor for rather than rendering a guess', function (): void {
    // The Blade compiler wraps render-time throws, so the assertion is on the
    // ViewException carrying the original message, not on InvalidArgumentException.
    Blade::render('<x-resource-strip scenario="jp_only_event" />');
})->throws(ViewException::class, 'Unknown scenario [jp_only_event] for x-resource-strip.');
