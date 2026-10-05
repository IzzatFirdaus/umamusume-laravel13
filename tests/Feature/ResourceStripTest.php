<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\TurnEntry;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * D-220 / D-240 for the Resources strip.
 *
 * D-220's failure mode is not a missing widget, it is a present-but-empty one: a
 * Team Rank box on a URA run states that the run has a team system. So the
 * assertions are negative as well as positive — the widget a scenario has no
 * mechanic for must not be in the list at all, not merely be disabled.
 *
 * D-240's failure mode is a scenario name in a view. The last test greps the
 * component for one, so adding a fifth scenario cannot smuggle a branch back in.
 *
 * Since the Inertia port (ADR-0020 §1) the widget list, the scenario label and the
 * grade-objectives flag are all server state read off the matrix by the controller,
 * so the absence half of the first case is asserted on the payload: the component
 * maps the list it is given and cannot invent a box the list does not name. The label
 * half is read out of the component, because the label is the one thing the server
 * does not send.
 */

/**
 * Per scenario: the widget keys its strip must carry, in order.
 * Positional, because Pest's `with()` turns an associative value into named args.
 *
 * @return array<string, array{string, list<string>}>
 */
function stripExpectations(): array
{
    $teamOnly = ['team_rank', 'spirit_bursts'];
    $shopOnly = ['grade_points', 'shop_coins'];
    $baseline = ['turn', 'energy', 'fans'];

    return [
        'ura_finale' => ['ura_finale', $baseline],
        'our_grand_concert' => ['our_grand_concert', $baseline],
        'unity_cup' => ['unity_cup', [...$baseline, ...$teamOnly]],
        'trackblazer' => ['trackblazer', [...$baseline, ...$shopOnly]],
    ];
}

/**
 * A run with one logged turn, so the strip has figures to print.
 */
function stripRun(string $scenario, int $energy = 78, int $fans = 41250): TrainingRun
{
    $run = TrainingRun::factory()->create(['scenario' => $scenario]);
    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1, 'energy' => $energy, 'fans' => $fans]);

    return $run->fresh();
}

/**
 * The strip component's source, which owns the labels and the caption.
 */
function stripSource(): string
{
    return (string) file_get_contents(base_path('resources/js/components/ResourceStrip.vue'));
}

/**
 * Widget key to the label the component prints, read out of its own switch rather than
 * retyped here, so a renamed label fails one place and the contract is still stated once.
 *
 * @return array<string, string>
 */
function stripLabels(): array
{
    preg_match_all("/case '(\w+)':\s*\n\s*label = '([^']+)';/", stripSource(), $rows, PREG_SET_ORDER);

    $labels = [];

    foreach ($rows as $row) {
        $labels[$row[1]] = $row[2];
    }

    return $labels;
}

it('renders only the widgets the scenario actually has', function (string $scenario, array $widgets): void {
    $run = stripRun($scenario);

    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        // The list is the whole absence half: the component maps exactly what it is given, so a
        // widget the scenario has no mechanic for is not rendered at all rather than disabled.
        ->where('strip.widgets', $widgets)
        ->where('strip.scenarioLabel', (string) config('scenarios.scenarios.'.$scenario.'.label')));

    // And the list only means something because each key has a label: a key the component cannot
    // present would throw on its default branch instead of quietly dropping out of the strip.
    $labels = stripLabels();

    foreach ($widgets as $widget) {
        expect($labels)->toHaveKey($widget);
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

it('never prints a fan-gated event on a scenario that does not gate fans', function (): void {
    // URA Final is the scenario whose Fans the Trainer is working toward an event gate on, so it is
    // the one whose caption may claim a gate. The caption is a claim about the run, so it is
    // composed from the matrix's own grade-objectives flag rather than from the scenario's name.
    $gated = stripRun('ura_finale');

    $this->get('/training-runs/'.$gated->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('strip.hasGradeObjectives', false)
        ->where('strip.values.fans', 41250)
        ->where('strip.values.energy', 78));

    // Trackblazer farms fans on its own races instead, so its caption must not name a gate. The
    // flag is the only difference the two payloads carry that this caption reads.
    $farmed = stripRun('trackblazer', 41, 128400);

    $this->get('/training-runs/'.$farmed->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('strip.hasGradeObjectives', true)
        ->where('strip.values.fans', 128400));

    // And the component's own branch, because deciding between the two captions is a component
    // concern and a wrong read would put a fan gate on a run that has none.
    expect(stripSource())
        ->toContain("sub = recorded ? (props.hasGradeObjectives ? 'farmed by racing here' : 'next event gate 60,000') : 'not yet recorded';");
});

it('prints no scenario label for a run that has not chosen one', function (): void {
    // `scenarioKey()` falls back to the baseline so the strip always has generic widgets,
    // and `declared` is what keeps that fallback from being printed as a fact about the run.
    $run = TrainingRun::factory()->create(['scenario' => null]);
    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1, 'energy' => 60, 'fans' => 9000]);

    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        // The widgets are the baseline's, so the strip is not empty, and the caption says why.
        ->where('strip.widgets', array_values((array) config('scenarios.scenarios.'.config('scenarios.baseline').'.widgets')))
        ->where('strip.declared', false));

    expect(stripSource())->toContain("sub = props.declared ? props.scenarioLabel : 'no scenario set';");
});

it('contains no scenario name anywhere in the component', function (): void {
    // Comments are stripped first: the component's own prose names `unity_cup` in
    // the D-240 note explaining why it does not branch on it. The gate is about
    // executable code, not documentation.
    $source = stripSource();

    $code = (string) preg_replace(['#\{\{--.*?--\}\}#s', '#/\*.*?\*/#s'], '', $source);

    foreach (array_keys(config('scenarios.scenarios')) as $key) {
        expect($code)->not->toContain($key);
    }
});

it('refuses a widget it has no presentation for rather than rendering an empty box', function (): void {
    // The scenario guard this file used to hold is gone with the port, and deliberately: the
    // component no longer takes a scenario name, so there is no unknown-scenario case left for it
    // to refuse. The remaining refusal is narrower and still real — a widget key the matrix adds
    // without the component learning to present it, which must throw rather than print a box with
    // no label. It is read from the component's default branch because it needs a widget key no
    // scenario in the matrix produces today.
    expect(stripSource())->toContain('throw new Error(`Unknown widget [${widget}] in scenario config.`);');
});
