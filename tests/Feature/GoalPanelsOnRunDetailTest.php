<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The two mutually exclusive goal panels, mounted on a real run.
 *
 * D-241 and G-34 are the same rule from two ends: the panel set follows the
 * scenario, and an absent cell is genuinely absent rather than a shell. A run
 * therefore never shows both a race calendar and a Grade Point meter — there is
 * no scenario with mandatory race goals *and* point deadlines, and a screen that
 * showed both would be claiming a structure no scenario has.
 *
 * The second job here is the harder one. This build stores no race entries and
 * no Grade Points, so both panels land on a run that has neither. The tests
 * pin that the panels then say what is missing instead of printing a zero,
 * because "0 fans" and "0 / 300" are claims about a trainee that nobody entered.
 *
 * The gating is server state on the Inertia page — `calendar.show` and a
 * non-empty `gradeMeter.objectives` are the mount flags — so it is asserted
 * from the payload rather than searched for as a heading in rendered HTML.
 */

function goalPanelPage(?string $scenario): TestResponse
{
    return test()
        ->get('/training-runs/'.TrainingRun::factory()->create(['scenario' => $scenario])->id)
        ->assertOk();
}

it('mounts the race calendar and not the meter where race goals exist', function (?string $scenario): void {
    goalPanelPage($scenario)->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('calendar.show', true)
        // Nothing is catalogued for these runs, so the grid itself is absent and the
        // panel names that instead of drawing 24 cells that all read "No race" (D-220).
        ->has('calendar.cells', 0)
        ->has('gradeMeter.objectives', 0));
})->with(['ura_finale', 'unity_cup']);

it('mounts neither panel for a run with no scenario chosen', function (): void {
    // `null` left the provider above when Slice 2 gave the panels real data: with slots loaded, the
    // `scenarioKey()` baseline fallback rendered the baseline's actual schedule (Oka Sho, Tenno Sho,
    // Asahi Hai) under a header reading "No scenario set", which is a race calendar invented for a run
    // that has chosen nothing. The fallback is a ruling about the resource strip, not about named races.
    goalPanelPage(null)->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('run.has_scenario', false)
        ->whereNull('run.scenario_label')
        ->where('calendar.show', false)
        ->has('gradeMeter.objectives', 0));
});

it('renders the same absence for a blank scenario as for a null one', function (): void {
    // Found by the Slice 2 browser pass, not by the suite: `hasScenario()` read `!== null`, so `''`
    // counted as declared and every self-gating panel looked its numbers up under `scenarios.scenarios.`
    // and got nothing — the run page answered with Laravel's exception screen instead of the run. The
    // web form normalises `''` to null, but the historical-run import will insert Trainer-supplied rows,
    // and a blank column must render the baseline rather than fatal.
    goalPanelPage('')->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('run.scenario', '')
        ->where('run.has_scenario', false)
        ->where('calendar.show', false)
        ->has('gradeMeter.objectives', 0));
});

it('mounts the meter and not the calendar where point deadlines exist', function (): void {
    goalPanelPage('trackblazer')->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        // The four standard-track objectives, debut included (ADR-0003): any fewer is a ladder to nowhere.
        ->has('gradeMeter.objectives', 4)
        ->where('calendar.show', false));
});

it('shows neither panel for a scenario with neither', function (): void {
    // Our Grand Concert is live, has the highest Speed cap of any scenario here,
    // and has no guide. The safe and honest rendering is the baseline strip alone.
    goalPanelPage('our_grand_concert')->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->has('strip.widgets')
        ->where('strip.declared', true)
        ->where('calendar.show', false)
        ->has('gradeMeter.objectives', 0));
});

it('never shows both panels at once, in any scenario', function (?string $scenario, bool $calendarShown, int $objectiveCount): void {
    // Both mount flags pinned per scenario; no row has the calendar and the meter on together.
    goalPanelPage($scenario)->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('calendar.show', $calendarShown)
        ->has('gradeMeter.objectives', $objectiveCount));
})->with([
    ['ura_finale', true, 0],
    ['unity_cup', true, 0],
    ['trackblazer', false, 4],
    ['our_grand_concert', false, 0],
    [null, false, 0],
]);

it('claims no figure for a run that has entered no race', function (): void {
    // The structure is real and the run's entries are not, so the panel names the
    // cause and the action instead of drawing 24 cells that all read "No race".
    goalPanelPage('ura_finale')->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('calendar.show', true)
        ->has('calendar.cells', 0));
});

it('claims no figure for a run that has entered no Grade Point', function (): void {
    // A zero here would read as "she is on zero points", which is a claim about a
    // trainee rather than about the log. The deadlines still print, because those
    // are the scenario's and they are the reason the panel is worth opening.
    goalPanelPage('trackblazer')->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('gradeMeter.objectives', fn (Collection $objectives) => $objectives->pluck('name')->all() === [
            'Debut race',
            'End of Junior Year',
            'End of Classic Year',
            'End of Senior Year',
        ])
        // Null with nothing unpriced is the "not yet recorded" branch, never "0 / 300" (KI-10, D-220).
        ->whereNull('gradeMeter.earned')
        ->where('gradeMeter.unpricedCount', 0));
});

it('moves the panels when the run is switched to another scenario', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    test()->get("/training-runs/{$run->id}")->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('calendar.show', true)
        ->has('gradeMeter.objectives', 0));

    test()->put("/training-runs/{$run->id}", [
        'umamusume_id' => $run->umamusume_id,
        'status' => 'Active',
        'scenario' => 'trackblazer',
    ])->assertRedirect();

    test()->get("/training-runs/{$run->id}")->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('calendar.show', false)
        ->has('gradeMeter.objectives', 4));
});
