<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Services\ScenarioCaps;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The stat band's grade badge (KI-8, ruling R13), after the Inertia port (ADR-0020 §1).
 *
 * The band was the only component with no test file, which is how it shipped a
 * crash: `config/scenarios.php`'s `grade_banding.labels` carries seventeen entries
 * including half-steps like `B+`, while the badge's class map has keys for the nine
 * base letters only. Any stat landing on a half-step threw
 * "Undefined array key "B+"" and took `/design-preview` — the only route that
 * rendered this band — down with it.
 *
 * R13 settles it: the fill is keyed on the base letter with the modifier stripped,
 * and the badge shows the full letter. A `B+` is a B's colour, and the difference
 * the `+` carries is the text's job, not the tint's.
 *
 * Both owners are Vue components now, so this file reads the two things a PHP
 * assertion can still establish about them — that the badge's map covers every letter
 * the banding can produce, and that the page ships the numbers the badge maps — and
 * the rendered badge itself is browser evidence in `tests/browser/run-detail.spec.ts`.
 * What is gone is the component's own two guards: they existed because the Blade
 * component re-derived its numbers and could be handed a scenario key that did not
 * exist. The Vue band cannot be handed either, because it is not given a key at all
 * (G-33) and its columns come from the caps map the server resolved.
 */

/**
 * The run's own two payload halves: the caps the turn validator also uses, and the band the
 * five badges render. One logged turn, because five zeroes would be a claim about a trainee
 * nobody entered (D-220).
 */
function bandRun(?string $scenario = 'ura_finale'): TrainingRun
{
    $run = TrainingRun::factory()->create(['scenario' => $scenario]);

    TurnEntry::create([
        'training_run_id' => $run->id, 'turn' => 1, 'speed' => 350, 'stamina' => 280,
        'power' => 240, 'guts' => 210, 'wit' => 150, 'sp' => 120, 'energy' => 74,
        'mood' => 'GOOD', 'fans' => 4000,
    ]);

    return $run;
}

/**
 * The badge's fill map, read from the component that owns it: the keys are the base letters
 * the lookup resolves to, so a label the map cannot answer is the KI-8 crash.
 *
 * @return array<string, string>
 */
function gradeBadgeFills(): array
{
    $source = (string) file_get_contents(base_path('resources/js/components/GradeBadge.vue'));

    preg_match_all('/^\s{4}([A-Z]+):\s*\'(bg-grade-[a-z]+)\',/m', $source, $matches, PREG_SET_ORDER);

    $fills = [];

    foreach ($matches as $match) {
        $fills[$match[1]] = $match[2];
    }

    return $fills;
}

it('renders a badge for every label the banding can produce', function (): void {
    $run = bandRun();

    // The page hands the band the whole banding, so the badge's map is the only thing standing
    // between a seventeen-label config and an undefined key. The loop collects every failure
    // instead of stopping at the first, so a break names the letters rather than the label.
    test()->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('gradeBanding', (array) config('scenarios.grade_banding'))
            ->where('gradeBanding.labels', config('scenarios.grade_banding.labels'))
            ->where('baseCap', (int) config('scenarios.base_cap'))
            ->where('hardCap', (int) config('scenarios.hard_cap')));

    $fills = gradeBadgeFills();
    $broken = [];

    foreach (config('scenarios.grade_banding.labels') as $label) {
        $base = preg_replace('/[-+]+$/', '', (string) $label);

        if (! isset($fills[$base])) {
            $broken[$label] = 'no fill keyed on '.var_export($base, true);
        }
    }

    // KI-8: nine base letters worked and the eight half-steps threw.
    expect($broken)->toBe([])
        // An empty map would satisfy the loop above, so the census is asserted too.
        ->and($fills)->toHaveCount(9);
});

it('strips the modifier before the lookup, so a half-step can never miss the map', function (): void {
    $source = (string) file_get_contents(base_path('resources/js/components/GradeBadge.vue'));

    // Two halves, because either alone is a weaker guard: the strip is what makes `B+` resolve
    // to `B`, and the fallback is what a label nobody predicted still renders instead of throwing.
    expect($source)->toContain("replace(/[-+]+$/, '')")
        ->toContain("?? 'bg-grade-g'")
        // The badge prints the full letter the band resolved, modifier included.
        ->toContain('{{ grade }}');
});

it('tints a half-step with its base letter fill and prints the full letter', function (): void {
    // `B+` is a B's colour and a B+ 's text. Asserting both halves matters: a fix that rendered
    // "B" to keep the map honest would pass a fill-only assertion.
    expect(gradeBadgeFills())
        ->toHaveKey('B')
        ->and(gradeBadgeFills()['B'])->toBe('bg-grade-b')
        // R13: no fill is keyed on a half-step at all, so the `+` can never become a colour.
        ->and(array_keys(gradeBadgeFills()))->not->toContain('B+');
});

it('says the grade is derived and not read from the client', function (): void {
    $run = bandRun();

    // D-256 with G-46: the banding is this tool's own reading of pixels, not a client string,
    // and a badge that looks like game data has to say otherwise. The word rides on the badge as
    // its accessible name, and the page is what decides the band exists at all.
    test()->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('band.values.Speed', 350)
            // The bonus row is the scenario's own breakdown, the same one the ceilings are built
            // from, so the arithmetic line under the band cannot disagree with the bar.
            ->where('band.capBonus', (array) config('scenarios.scenarios.ura_finale.cap_bonus')));

    expect((string) file_get_contents(base_path('resources/js/components/StatBand.vue')))
        ->toContain('Derived from the entered value, not read from the client');
});

it('renders the base cap when told there is no scenario, and says so in the footer', function (): void {
    $run = bandRun(null);

    // The band used to reach for `config('scenarios')` by itself and was handed a resolved
    // key, so this is the state it could never express: a band with no bonus row at all. Both
    // halves matter — the ceiling the bar ends at, and the arithmetic line that explains it.
    test()->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('run.scenario', null)
            ->where('run.has_scenario', false)
            ->where('band.capBonus', null)
            ->where('baseCap', 1200));

    expect((string) file_get_contents(base_path('resources/js/components/StatBand.vue')))
        ->toContain('no scenario set: every ceiling here is the base cap and no bonus applies.');
});

it('takes every ceiling from the one owner, so the band cannot be handed a number it invented', function (): void {
    // KI-47, and the answer to the guard the Blade component used to raise: the band renders the
    // caps map and nothing else, and the page builds that map with the same call the turn
    // validator makes. A band rated against a bonus the form would reject is therefore not
    // reachable, which is a stronger claim than the exception it replaces.
    $run = bandRun();

    test()->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('caps', ScenarioCaps::forRun($run->fresh())));

    expect(ScenarioCaps::forRun($run->fresh()))
        ->toHaveKeys(['Speed', 'Stamina', 'Power', 'Guts', 'Wit'])
        ->and((string) file_get_contents(base_path('resources/js/components/StatBand.vue')))
        ->not->toContain('scenario:');
});

it('still refuses a scenario that was named and does not exist', function (): void {
    // The component no longer names a scenario, so the refusal that survives is the service's:
    // an unknown key has no cap row, and the page can never be handed one. Asserting the
    // message keeps the failure pointing at the file that raises it rather than at the band,
    // which is a different unit with a similar-sounding guard.
    expect(fn (): array => ScenarioCaps::caps('grand_masters'))
        ->toThrow(InvalidArgumentException::class, 'Unknown scenario [grand_masters] has no cap row.');
});
