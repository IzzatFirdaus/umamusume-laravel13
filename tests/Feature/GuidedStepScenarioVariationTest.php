<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

/*
 * Screen B for the two scenarios that own an extra step (owner directive).
 *
 * Both variations are the same test-shaped claim as the baseline file, one step
 * each:
 *
 *   1. The step exists only where the scenario owns the panel. Trackblazer's shop
 *      must not be reachable from a Unity Cup turn, and Unity Cup's team race must
 *      not be reachable from a Trackblazer turn.
 *   2. Every number on the step is sourced. Coin costs and the rotation timer come
 *      from config, which is fed from docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md. The win-odds circles are
 *      the one thing that is *not* printed as a number, because the estimate is the
 *      client's and computing it would be simulation (Planner Rule 1).
 *
 * The other half is absence. A shop step that renders for a scenario with no shop
 * is D-220 stated in the middle of a turn.
 */

function renderShopStep(array $overrides = []): string
{
    return Blade::render(
        '<x-guided-step :scenario="$scenario" :current="$current" :energy="$energy" />',
        array_merge(['scenario' => 'trackblazer', 'current' => 'shop', 'energy' => 61], $overrides),
    );
}

function renderTeamRaceStep(array $overrides = []): string
{
    return Blade::render(
        '<x-guided-step :scenario="$scenario" :current="$current" :energy="$energy" />',
        array_merge(['scenario' => 'unity_cup', 'current' => 'team_race', 'energy' => 55], $overrides),
    );
}

/**
 * The whole $tag element whose body mentions $needle.
 *
 * Scope matters for the negative assertions here: "no circle count is printed" is
 * only true of an opponent row, not of the whole step, because the source guidance
 * legitimately reads "at least 3 circles". Checking the row keeps both true.
 */
function markupFor(string $html, string $tag, string $needle): string
{
    preg_match(
        '/<'.$tag.'\b[^>]*>(?:(?!<\/'.$tag.'>)[\s\S])*?'.preg_quote($needle, '/').'(?:(?!<\/'.$tag.'>)[\s\S])*?<\/'.$tag.'>/',
        $html,
        $matches,
    );

    return $matches[0] ?? '';
}

// ---------------------------------------------------------------- shop step

it('places the shop between the activity and the outcome, from config order', function (): void {
    $html = renderShopStep();

    expect(config('scenarios.scenarios.trackblazer.steps'))
        ->toBe(['training', 'shop', 'outcome', 'skill'])
        ->and($html)
        ->toContain('Step 2 of 4')
        ->toContain('Spend Shop Coins');
});

it('shows the rotation timer as the shop primary number, not a balance', function (): void {
    $html = renderShopStep();

    // D-232: unspent coins die with the run, so "how long until the lineup changes"
    // is the decision-relevant figure. A balance is shown only as unrecorded.
    expect($html)
        ->toContain('Rotation resets in 6 turns')
        ->toContain('Shop Coins: not yet recorded')
        ->not->toMatch('/Shop Coins:\s*[\d,]+/');
});

it('states the holding limit and the debut lock', function (): void {
    $html = renderShopStep();

    // Both are in config: max_copies_per_item 5, locked_until_debut true.
    expect($html)
        ->toContain('Up to 5 copies of one item')
        ->toContain('Locked until debut');
});

it('prices the catalogue from config rather than a literal in the view', function (): void {
    $items = config('scenarios.scenarios.trackblazer.shop_items');
    $html = renderShopStep();

    // D-232's catalogue is a long list; a handful is rendered as the static stand-in
    // the directive allows. Each row carries the sourced cost, and the omission is
    // stated rather than implied.
    expect($items)->toBeArray()->not->toBeEmpty();
    expect($html)->toContain('Empowering Megaphone', '70', 'Training bonus +60% for 2 turns');
});

it('warns that a weaker purchase overwrites a stronger one, because the order decides', function (): void {
    $html = renderShopStep();

    expect($html)
        ->toContain('overwrites the active one')
        ->toContain('cannot be used again while active');
});

it('tells the Trainer where a Sale and a Limited flag would appear', function (): void {
    $html = renderShopStep();

    // The rotation is not modelled, so no offer is flagged yet. The positions are
    // still stated, because the client puts them in fixed corners and a Trainer
    // scans those corners.
    expect($html)
        ->toContain('Sale top-left')
        ->toContain('Limited top-right')
        ->toContain('no offer is flagged');
});

it('renders a Sale flag and a Limited flag only on the offers that carry them', function (): void {
    // The rotation is unmodelled, so the flag contract is proven with a synthetic
    // offer rather than by faking a real one.
    config()->set('scenarios.scenarios.trackblazer.shop_items', [
        ['name' => 'Plain Cupcake', 'cost' => 30, 'effect' => 'Mood +1', 'sale' => true],
        ['name' => 'Glow Sticks', 'cost' => 15, 'effect' => 'Race fan gain +50%, 1 turn', 'limited' => true],
    ]);

    $html = renderShopStep();
    $sale = markupFor($html, 'li', 'Plain Cupcake');
    $limited = markupFor($html, 'li', 'Glow Sticks');

    // One flag per row, on the row that claims it. A flag that leaked onto the
    // wrong offer would be worse than no flag, because the Trainer buys on it.
    expect($sale)
        ->toContain('Sale')
        ->not->toContain('Limited')
        ->and($limited)
        ->toContain('Limited')
        ->not->toContain('Sale');
});

it('shows no held count, because none is entered yet', function (): void {
    $html = renderShopStep();

    expect($html)
        ->toContain('held: not yet recorded')
        ->not->toMatch('/held:\s*\d/');
});

it('renders no shop on a scenario that has no shop', function (string $scenario): void {
    // The rail is driven by config steps, so a URA turn cannot even reach `shop`.
    // This guards the panel flag: a fifth scenario with a `shop` step and no shop
    // panel would otherwise render an empty till.
    config()->set("scenarios.scenarios.{$scenario}.steps", ['training', 'shop', 'outcome', 'skill']);

    $html = Blade::render(
        '<x-guided-step :scenario="$scenario" current="shop" />',
        ['scenario' => $scenario],
    );

    expect($html)
        ->toContain('Spend Shop Coins')
        ->not->toContain('Rotation resets in')
        ->not->toContain('Shop Coins:');
})->with(['ura_finale', 'unity_cup', 'our_grand_concert']);

// ----------------------------------------------------------- team race step

it('offers three opponents strongest to weakest, as the client does', function (): void {
    $opponents = config('scenarios.scenarios.unity_cup.team_race.opponents');
    $html = renderTeamRaceStep();

    expect($opponents)->toHaveCount(3)
        ->and(array_column($opponents, 'tier'))->toBe(['strongest', 'middle', 'weakest'])
        ->and($html)
        ->toContain('strongest', 'weakest');
});

it('records the circle estimate instead of computing it', function (): void {
    $html = renderTeamRaceStep();

    // Planner Rule 1: the circles are the game's own display. The tool stores what
    // the Trainer saw. Printing "5 circles" on a row would be the tool doing the
    // maths the client does — the one thing the flow spec forbids. The guidance
    // below the rows may still say "at least 3 circles", because that is sourced
    // advice rather than a per-opponent figure.
    foreach (config('scenarios.scenarios.unity_cup.team_race.opponents') as $opponent) {
        expect(markupFor($html, 'li', $opponent['name']))
            ->toContain('circles not yet recorded')
            ->not->toMatch('/\b[1-5] circles/');
    }

    expect($html)->toContain('the game shows a circle-based win-odds estimate');
});

it('carries the source guidance for choosing an opponent', function (): void {
    $html = renderTeamRaceStep();

    // D-225: this advice is correct here and harmful in Trackblazer, so it may only
    // render on a scenario that owns the team race. The claim is "at least 3
    // circles as a margin", not "3 of 5 wins the round".
    expect($html)
        ->toContain('at least 3 circles')
        ->toContain('a margin, not a win condition')
        ->toContain('a loss lowers league rank');
});

it('offers the Alarm Clock retry, which the rework added', function (): void {
    $html = renderTeamRaceStep();

    expect($html)->toContain('retried with an Alarm Clock item');
});

it('marks the opponent names as sample data', function (): void {
    $html = renderTeamRaceStep();

    // G-16: the client names its own teams. These are placeholders standing in for
    // names this tool has no source for.
    expect($html)->toContain('Opponent names are sample data');
});

it('does not print a round number it has no source for', function (): void {
    $html = renderTeamRaceStep();

    // The cadence is sourced (every 6 months); the current round is run state this
    // build does not hold. "round 3 of 5" is prototype furniture.
    expect($html)
        ->toContain('every 6 months')
        ->not->toMatch('/round \d+ of \d+/');
});

it('renders no team race on a scenario that has no team race', function (string $scenario): void {
    config()->set("scenarios.scenarios.{$scenario}.steps", ['training', 'team_race', 'outcome', 'skill']);

    $html = Blade::render(
        '<x-guided-step :scenario="$scenario" current="team_race" />',
        ['scenario' => $scenario],
    );

    expect($html)
        ->toContain('Step 2 of 4')
        ->not->toContain('circles')
        ->not->toContain('league rank');
})->with(['ura_finale', 'trackblazer', 'our_grand_concert']);

// --------------------------------------------------------------- composition

it('adds the team race step to Unity Cup rail order', function (): void {
    $html = renderTeamRaceStep();

    expect(config('scenarios.scenarios.unity_cup.steps'))
        ->toBe(['facility', 'training', 'team_race', 'outcome', 'skill'])
        ->and($html)
        ->toContain('Step 3 of 5');
});

it('keeps the scenario rail itself free of scenario names', function (): void {
    $source = (string) file_get_contents(resource_path('views/components/guided-step.blade.php'));
    $code = (string) preg_replace(['#\{\{--.*?--\}\}#s', '#/\*.*?\*/#s'], '', $source);

    foreach (array_keys(config('scenarios.scenarios')) as $key) {
        expect($code)->not->toContain($key);
    }
});
