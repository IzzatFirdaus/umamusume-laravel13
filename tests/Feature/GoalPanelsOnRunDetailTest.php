<?php

declare(strict_types=1);

use App\Models\TrainingRun;

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
 */

function goalPanelHtml(?string $scenario): string
{
    return test()
        ->get('/training-runs/'.TrainingRun::factory()->create(['scenario' => $scenario])->id)
        ->assertOk()
        ->getContent();
}

it('mounts the race calendar and not the meter where race goals exist', function (?string $scenario): void {
    $html = goalPanelHtml($scenario);

    expect($html)
        ->toContain('Race calendar')
        ->toContain('No races entered for this run yet')
        ->not->toContain('Grade Point</span>');
})->with(['ura_finale', 'unity_cup']);

it('mounts neither panel for a run with no scenario chosen', function (): void {
    // This case used to sit in the provider above, on the premise stated in this
    // file's header: "This build stores no race entries and no Grade Points, so
    // both panels land on a run that has neither." Slice 2's S2 ended that premise —
    // the panels now read `scenario_slots` and the race log. With real slots loaded,
    // keeping `null` there rendered the baseline's actual schedule (Oka Sho, Tenno
    // Sho, Asahi Hai) under a header reading "No scenario set", which is a invented
    // race calendar for a run that has chosen nothing. `scenarioKey()`'s baseline
    // fallback is a ruling about the generic resource strip, not about named races.
    $html = goalPanelHtml(null);

    expect($html)
        ->toContain('No scenario set')
        ->not->toContain('Race calendar')
        ->not->toContain('Grade Point</span>');
});

it('mounts the meter and not the calendar where point deadlines exist', function (): void {
    $html = goalPanelHtml('trackblazer');

    expect($html)
        ->toContain('Grade Point</span>')
        ->not->toContain('Race calendar');
});

it('shows neither panel for a scenario with neither', function (): void {
    // Our Grand Concert is live, has the highest Speed cap of any scenario here,
    // and has no guide. The safe and honest rendering is the baseline strip alone.
    $html = goalPanelHtml('our_grand_concert');

    expect($html)
        ->toContain('Resources')
        ->not->toContain('Race calendar')
        ->not->toContain('Grade Point</span>');
});

it('never shows both panels at once, in any scenario', function (?string $scenario): void {
    $html = goalPanelHtml($scenario);

    expect(str_contains($html, 'Race calendar') && str_contains($html, 'Grade Point</span>'))
        ->toBeFalse();
})->with(['ura_finale', 'unity_cup', 'trackblazer', 'our_grand_concert', null]);

it('claims no figure for a run that has entered no race', function (): void {
    $html = goalPanelHtml('ura_finale');

    // The structure is real and the run's entries are not, so the panel names the
    // cause and the action instead of drawing 24 cells that all read "No race".
    expect($html)
        ->toContain('No races entered for this run yet')
        ->toContain('24 turn slots');
});

it('claims no figure for a run that has entered no Grade Point', function (): void {
    $html = goalPanelHtml('trackblazer');

    // A zero here would read as "she is on zero points", which is a claim about a
    // trainee rather than about the log. The deadlines still print, because those
    // are the scenario's and they are the reason the panel is worth opening.
    expect($html)
        ->toContain('End of Junior Year')
        ->toContain('End of Senior Year')
        ->toContain('not yet recorded')
        ->not->toMatch('/\b\d+ \/ \d+/');
});

it('moves the panels when the run is switched to another scenario', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    expect(test()->get("/training-runs/{$run->id}")->getContent())->toContain('Race calendar');

    test()->put("/training-runs/{$run->id}", [
        'umamusume_id' => $run->umamusume_id,
        'status' => 'Active',
        'scenario' => 'trackblazer',
    ])->assertRedirect();

    $html = test()->get("/training-runs/{$run->id}")->assertOk()->getContent();

    expect($html)
        ->toContain('Grade Point</span>')
        ->not->toContain('Race calendar');
});
