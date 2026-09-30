<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Services\ScenarioCaps;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\ViewException;

/*
 * The stat band's grade badge (KI-8, ruling R13).
 *
 * The band was the only component with no test file, which is how it shipped a
 * crash: `config/scenarios.php`'s `grade_banding.labels` carries seventeen entries
 * including half-steps like `B+`, while the badge's class map has keys for the nine
 * base letters only. Any stat landing on a half-step threw
 * "Undefined array key "B+"" and took `/design-preview` — the only route that
 * renders this band — down with it.
 *
 * R13 settles it: the fill is keyed on the base letter with the modifier stripped,
 * and the badge shows the full letter. A `B+` is a B's colour, and the difference
 * the `+` carries is the text's job, not the tint's.
 */

/**
 * The badge letters the band actually printed, in order.
 *
 * Matched on the badge span rather than as a substring: "B" appears inside "B+" and
 * inside class names, so a plain contains() would pass a band that printed the wrong
 * letter or none at all.
 *
 * @return list<string>
 */
function badgeLabels(string $html): array
{
    preg_match_all(
        '/class="[^"]*bg-grade-[a-z]+[^"]*"[^>]*>\s*([A-Z]+[+]?)\s*</',
        $html,
        $matches,
    );

    return $matches[1];
}

function renderBand(array $values, ?string $scenario = 'ura_finale'): string
{
    // Caps arrive the way the controller sends them now: from ScenarioCaps, not derived inside
    // the component. Passing the scenario alone used to be enough because the band re-derived,
    // and that re-derivation is what KI-47 was.
    $caps = $scenario === null
        ? ScenarioCaps::forRun(new TrainingRun(['scenario' => null]))
        : ScenarioCaps::caps($scenario);

    return Blade::render(
        '<x-stat-band :scenario="$scenario" :caps="$caps" :values="$values" />',
        [
            'scenario' => $scenario,
            'caps' => $caps,
            'values' => $values,
        ],
    );
}

it('renders a badge for every label the banding can produce', function (): void {
    // A dataset was tried first and cannot work here: Pest collects datasets before
    // the app is booted, so config() is empty at that point. The loop collects every
    // failure instead, so a break names the labels rather than stopping at the first.
    $band = config('scenarios.grade_banding');
    $stats = config('scenarios.stat_order');
    $broken = [];

    foreach ($band['labels'] as $index => $label) {
        $value = $index * $band['step'];

        try {
            $html = renderBand(array_fill_keys($stats, $value));
        } catch (Throwable $e) {
            $broken[$label] = $e->getMessage();

            continue;
        }

        if (! in_array($label, badgeLabels($html), true)) {
            $broken[$label] = 'badge printed '.implode('/', badgeLabels($html) ?: ['nothing']);
        }
    }

    // KI-8: nine base letters worked and the eight half-steps threw.
    expect($broken)->toBe([]);
});

it('does not throw for a half-step grade', function (): void {
    // The exact value that produced KI-8: index 11 of the label list is `B+`.
    $band = config('scenarios.grade_banding');
    $value = 11 * $band['step'];

    expect(fn (): string => renderBand(['Speed' => $value]))->not->toThrow(ViewException::class);
});

it('tints a half-step with its base letter fill and prints the full letter', function (): void {
    $html = renderBand(['Speed' => 550, 'Stamina' => 550, 'Power' => 550, 'Guts' => 550, 'Wit' => 550]);

    // `B+` is a B's colour and a B+ 's text. Asserting both halves matters: a fix
    // that rendered "B" to keep the map honest would pass a fill-only assertion.
    expect(badgeLabels($html))->toBe(['B+', 'B+', 'B+', 'B+', 'B+'])
        ->and($html)->toContain('bg-grade-b')
        ->not->toContain('bg-grade-b+');
});

it('says the grade is derived and not read from the client', function (): void {
    $html = renderBand(['Speed' => 550]);

    // D-256 with G-46: the banding is this tool's own reading of pixels, not a
    // client string, and a badge that looks like game data has to say otherwise.
    expect($html)->toContain('Derived from the entered value, not read from the client');
});

it('renders the base cap when told there is no scenario, and says so in the footer', function (): void {
    $html = renderBand(['Speed' => 600, 'Stamina' => 600, 'Power' => 600, 'Guts' => 600, 'Wit' => 600], null);

    // The component used to reach for `config('scenarios')` by itself and was handed a resolved
    // key, so this is the state it could never express: a band with no bonus row at all. Both
    // halves matter — the ceiling the bar ends at, and the arithmetic line that explains it.
    expect($html)->toContain('/ 1,200')
        ->and($html)->toContain('no scenario set: every ceiling here is the base cap and no bonus applies.');
});

it('refuses to draw a band it was given no ceilings for', function (): void {
    // A caller that forgets `caps` must not get a rendered page. The band once derived its own
    // numbers, which is how a wrong one survived every passing test beside it; a silent fallback
    // here would reintroduce the same failure with a friendlier error rate.
    expect(fn () => Blade::render(
        '<x-stat-band :scenario="$scenario" :values="$values" />',
        ['scenario' => 'ura_finale', 'values' => ['Speed' => 600]],
    ))->toThrow(ViewException::class, 'x-stat-band requires a ceiling for every rated stat');
});

it('still refuses a scenario that was named and does not exist', function (): void {
    // Caps are supplied for a scenario that does exist so the service cannot be what throws:
    // the message has to come from this component's own guard, and asserting the suffix proves
    // which file raised it. Rendering the helper instead would have passed by testing
    // ScenarioCaps, which is a different unit with a similar error.
    expect(fn () => Blade::render(
        '<x-stat-band :scenario="$scenario" :caps="$caps" :values="$values" />',
        [
            'scenario' => 'grand_masters',
            'caps' => ScenarioCaps::caps('ura_finale'),
            'values' => ['Speed' => 600],
        ],
    ))->toThrow(ViewException::class, 'Unknown scenario [grand_masters] for x-stat-band');
});
