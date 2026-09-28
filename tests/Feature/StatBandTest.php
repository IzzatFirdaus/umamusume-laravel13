<?php

declare(strict_types=1);

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

function renderBand(array $values): string
{
    return Blade::render(
        '<x-stat-band :scenario="$scenario" :values="$values" />',
        ['scenario' => 'ura_finale', 'values' => $values],
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
