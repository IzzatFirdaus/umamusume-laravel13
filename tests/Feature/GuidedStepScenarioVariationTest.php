<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

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
 *
 * Since the Inertia port (ADR-0020 §1) the step the rail is on is server state, and
 * the server only ever says `training` or `outcome`: the shop and team-race blocks are
 * component state the page cannot currently enter. So each case below is split the way
 * the migration pattern splits them — the sourced numbers and the ownership flags are
 * asserted on the payload and the config, and the panel's own copy and gating are read
 * out of the component, with the unreachable-today fact stated rather than hidden.
 */

/**
 * A run on the scenario that owns the panel.
 */
function variationRun(string $scenario): TrainingRun
{
    return TrainingRun::factory()->create(['scenario' => $scenario])->fresh();
}

/**
 * The rail component's source, which owns the two optional panels.
 */
function variationSource(): string
{
    return (string) file_get_contents(base_path('resources/js/components/GuidedStep.vue'));
}

/**
 * The body of one `v-if` block in the component, so "the guard" and "the copy inside it"
 * are read separately rather than by one substring that could match either.
 */
function variationBlock(string $guardNeedle): string
{
    preg_match(
        '/<div\s+v-if="'.preg_quote($guardNeedle, '/').'".*?<\/div>\s*(?=<div|<\/template|<form)/s',
        variationSource(),
        $match,
    );

    return $match[0] ?? '';
}

// ---------------------------------------------------------------- shop step

it('places the shop between the activity and the outcome, from config order', function (): void {
    $run = variationRun('trackblazer');

    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('rail.def.steps', ['training', 'shop', 'outcome', 'skill'])
        // The panel flag and the config the block reads are both delivered, so the step is one
        // `current` away rather than missing.
        ->where('rail.def.panels.shop', true)
        ->where('rail.def.shop.rotation_turns', 6)
        ->where('rail.def.shop.max_copies_per_item', 5)
        ->where('rail.def.shop.locked_until_debut', true));

    // The step name comes from the label map, and the position from the rail's own `flow` — not from
    // `def.steps`, which is the scenario's longer turn vocabulary (D7).
    expect(variationSource())
        ->toContain("shop: 'Spend Shop Coins',")
        ->toContain('const found = props.flow.indexOf(props.current);')
        ->toContain('Step {{ index + 1 }} of {{ flow.length }} · {{ STEP_LABELS[current] ?? current }}');
});

it('shows the rotation timer as the shop primary number, not a balance', function (): void {
    // D-232: unspent coins die with the run, so "how long until the lineup changes"
    // is the decision-relevant figure. A balance is shown only as unrecorded.
    $run = variationRun('trackblazer');

    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('rail.def.shop.rotation_turns', 6));

    $block = variationBlock("current === 'shop' && def.panels.shop === true && def.shop");

    expect($block)
        ->toContain('Rotation resets in {{ def.shop.rotation_turns }} turns')
        ->toContain('Shop Coins: not yet recorded')
        // A balance would have to be interpolated, and nothing in the panel is.
        ->not->toContain('Shop Coins: {{');
});

it('states the holding limit and the debut lock', function (): void {
    $run = variationRun('trackblazer');

    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('rail.def.shop.max_copies_per_item', 5)
        ->where('rail.def.shop.locked_until_debut', true));

    $block = variationBlock("current === 'shop' && def.panels.shop === true && def.shop");

    // Both are in config: max_copies_per_item 5, locked_until_debut true, and the lock line is
    // itself gated on the flag rather than printed because the scenario has a shop.
    expect($block)
        ->toContain('Up to {{ def.shop.max_copies_per_item }} copies of one item')
        ->toContain('v-if="def.shop.locked_until_debut === true"');
});

it('prices the catalogue from config rather than a literal in the view', function (): void {
    $run = variationRun('trackblazer');

    // D-232's catalogue is a long list; a handful is rendered as the static stand-in
    // the directive allows. Each row carries the sourced cost, and the omission is
    // stated rather than implied.
    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('rail.def.shop_items', fn (Collection $items): bool => $items->contains(
            static fn (array $item): bool => $item['name'] === 'Empowering Megaphone'
                && $item['cost'] === 70
                && $item['effect'] === 'Training bonus +60% for 2 turns'
        )));

    $block = variationBlock("current === 'shop' && def.panels.shop === true && def.shop");

    // The name and the effect are printed; the cost is read off the row, so a config change
    // moves the number rather than leaving a literal behind.
    expect($block)
        ->toContain('v-for="item in def.shop_items ?? []"')
        ->toContain('{{ item.name }}')
        ->toContain('{{ item.effect }}')
        ->toContain('{{ item.cost }}c')
        ->toContain('the rows above are the item catalogue rather');
});

it('warns that a weaker purchase overwrites a stronger one, because the order decides', function (): void {
    expect(variationBlock("current === 'shop' && def.panels.shop === true && def.shop"))
        ->toContain('overwrites the active one')
        ->toContain('cannot be used again while active');
});

it('tells the Trainer where a Sale and a Limited flag would appear', function (): void {
    $block = variationBlock("current === 'shop' && def.panels.shop === true && def.shop");

    // The rotation is not modelled, so no offer is flagged yet. The positions are
    // still stated, because the client puts them in fixed corners and a Trainer
    // scans those corners.
    expect($block)
        ->toContain('Sale top-left')
        ->toContain('Limited top-right')
        ->toContain('no offer is flagged');
});

it('renders a Sale flag and a Limited flag only on the offers that carry them', function (): void {
    // The rotation is unmodelled, so the flag contract is proven on the component's own two
    // gates rather than by faking a real offer into config: each flag is read off its own row
    // field, so a flag that leaked onto another row is not expressible.
    $block = variationBlock("current === 'shop' && def.panels.shop === true && def.shop");

    expect($block)
        ->toContain('v-if="item.sale === true"')
        ->toContain('v-if="item.limited === true"')
        ->toContain('>Sale</span>')
        ->toContain('>Limited</span>')
        // Neither flag has a default in the panel, so a row that omits the key prints neither.
        ->toContain('{{ item.name }}')
        ->not->toContain('item.sale ??');
});

it('shows no held count, because none is entered yet', function (): void {
    $block = variationBlock("current === 'shop' && def.panels.shop === true && def.shop");

    expect($block)->toContain('held: not yet recorded')
        // Nothing interpolates a held figure, so there is no number to print.
        ->not->toContain('held: {{');
});

it('renders no shop on a scenario that has no shop', function (string $scenario): void {
    // The rail is driven by config steps, so a URA turn cannot even reach `shop`. This guards the
    // panel flag too: a fifth scenario with a `shop` step and no shop panel would otherwise render
    // an empty till, because the block's own guard is `current === 'shop' && panels.shop && shop`.
    $run = variationRun($scenario);

    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('rail.def.panels.shop', false)
        // Absent rather than null: a scenario with no shop has no shop key in the matrix at all,
        // so the panel flag is the only thing that can refuse to render.
        ->missing('rail.def.shop')
        ->missing('rail.def.shop_items'));

    expect(config("scenarios.scenarios.{$scenario}.steps"))->not->toContain('shop');

    // The block's own guard needs all three: the step, the panel flag, and the config. Two of the
    // three can be true on a scenario that does not own a shop, and then there is no till.
    expect(variationBlock("current === 'shop' && def.panels.shop === true && def.shop"))
        ->toContain("current === 'shop' && def.panels.shop === true && def.shop");
})->with(['ura_finale', 'unity_cup', 'our_grand_concert']);

// ----------------------------------------------------------- team race step

it('offers three opponents strongest to weakest, as the client does', function (): void {
    $run = variationRun('unity_cup');

    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('rail.def.panels.team_race', true)
        ->where('rail.def.team_race.opponent_count', 3)
        ->where('rail.def.team_race.opponents', fn (Collection $opponents): bool => $opponents
            ->pluck('tier')->all() === ['strongest', 'middle', 'weakest']));

    $block = variationBlock("current === 'team_race' && def.panels.team_race === true && def.team_race");

    expect($block)
        ->toContain('Opponent · one of {{ def.team_race.opponent_count }}')
        ->toContain('v-for="opponent in def.team_race.opponents"')
        ->toContain('{{ opponent.name }}')
        ->toContain('{{ opponent.tier }}');
});

it('records the circle estimate instead of computing it', function (): void {
    $block = variationBlock("current === 'team_race' && def.panels.team_race === true && def.team_race");

    // Planner Rule 1: the circles are the game's own display. The tool stores what
    // the Trainer saw. Printing "5 circles" on a row would be the tool doing the
    // maths the client does — the one thing the flow spec forbids. The guidance
    // below the rows may still say "at least 3 circles", because that is sourced
    // advice rather than a per-opponent figure.
    expect($block)
        ->toContain('circles not yet recorded')
        ->not->toContain('{{ opponent.circles')
        ->toContain('the game shows a circle-based win-odds estimate');
});

it('carries the source guidance for choosing an opponent', function (): void {
    $run = variationRun('unity_cup');

    // D-225: this advice is correct here and harmful in Trackblazer, so it may only
    // render on a scenario that owns the team race. The claim is "at least 3
    // circles as a margin", not "3 of 5 wins the round".
    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('rail.def.team_race.circles_guidance', 3));

    $block = variationBlock("current === 'team_race' && def.panels.team_race === true && def.team_race");

    expect($block)
        ->toContain('at least {{ def.team_race.circles_guidance }} circles')
        ->toContain('circles in total as a margin,')
        ->toContain('not a win condition')
        ->toContain('a loss lowers league rank');
});

it('offers the Alarm Clock retry, which the rework added', function (): void {
    $run = variationRun('unity_cup');

    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('rail.def.team_race.loss_retryable_with_alarm_clock', true));

    $block = variationBlock("current === 'team_race' && def.panels.team_race === true && def.team_race");

    // The retry sentence is inside the flag's own template, so a scenario that sets the flag false
    // prints nothing rather than printing the sentence with a negation.
    expect($block)
        ->toContain('v-if="def.team_race.loss_retryable_with_alarm_clock === true"')
        ->toContain('retried with an Alarm Clock item');
});

it('marks the opponent names as sample data', function (): void {
    // G-16: the client names its own teams. These are placeholders standing in for
    // names this tool has no source for.
    expect(variationBlock("current === 'team_race' && def.panels.team_race === true && def.team_race"))
        ->toContain('Opponent names are sample data');
});

it('does not print a round number it has no source for', function (): void {
    $run = variationRun('unity_cup');

    // The cadence is sourced (every 6 months); the current round is run state this
    // build does not hold. "round 3 of 5" is prototype furniture.
    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('rail.def.team_race.occurs_every_months', 6));

    $block = variationBlock("current === 'team_race' && def.panels.team_race === true && def.team_race");

    expect($block)
        ->toContain('A Team Race comes every {{ def.team_race.occurs_every_months }} months.')
        ->not->toMatch('/round \d+ of \d+/');
});

it('renders no team race on a scenario that has no team race', function (string $scenario): void {
    $run = variationRun($scenario);

    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('rail.def.panels.team_race', false)
        ->missing('rail.def.team_race'));

    expect(config("scenarios.scenarios.{$scenario}.steps"))->not->toContain('team_race');

    // The block is gated on the panel flag as well as the step, so a scenario that listed the step
    // without the panel would get a heading and no opponent rows.
    expect(variationBlock("current === 'team_race' && def.panels.team_race === true && def.team_race"))
        ->toContain('circles not yet recorded');
})->with(['ura_finale', 'trackblazer', 'our_grand_concert']);

// --------------------------------------------------------------- composition

it('adds the team race step to Unity Cup rail order', function (): void {
    $run = variationRun('unity_cup');

    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('rail.def.steps', ['facility', 'training', 'team_race', 'outcome', 'skill']));

    expect(variationSource())->toContain("team_race: 'Choose opponent',");
});

it('states plainly that the two optional panels are unreachable today', function (): void {
    // The server names one stage, `training` or `outcome`, so neither panel can be entered from the
    // page as it stands. The blocks and their config are kept because a rail that can say `shop`
    // or `team_race` is a config change away, and deleting them would throw that work away. This case
    // exists so the gap is on the record rather than discovered later.
    $run = variationRun('trackblazer');

    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('rail.current', 'training'));

    expect(variationSource())
        ->toContain("current === 'shop' && def.panels.shop === true && def.shop")
        ->toContain("current === 'team_race' && def.panels.team_race === true && def.team_race");
});

it('keeps the scenario rail itself free of scenario names', function (): void {
    $code = (string) preg_replace(['#\{\{--.*?--\}\}#s', '#/\*.*?\*/#s'], '', variationSource());

    foreach (array_keys(config('scenarios.scenarios')) as $key) {
        expect($code)->not->toContain($key);
    }
});