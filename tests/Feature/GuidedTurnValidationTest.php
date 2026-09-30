<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\TurnEntry;

/*
 * D-3: what a failed submit hands back to the rail.
 *
 * A validation failure on `runs.turns.store` redirects back to the run, and the rail used
 * to come back empty. `show()` passed the default `$staged = []`, so every number the
 * Trainer had typed reverted to its placeholder and the whole turn had to be entered
 * again - and because the reason for the failure is on the outcome step, the Trainer also
 * lost the step they were standing on.
 *
 * The raw escape hatch beside the rail rehydrates from `old()` per field, so two forms
 * posting to the same endpoint disagreed about what to do with input the server had just
 * rejected, and the form that asks for more fields lost more. The rehydration now lives in
 * `showData()`, the one place the GET, the staged preview and the redirect-back all read,
 * so there is a single assembly rather than a per-view `old()` call that a later edit
 * would miss.
 *
 * D-3 was fixed rather than characterized: discarding a Trainer's input contradicts D-56,
 * which sends an error "back to the step that caused it", so no test here pins the loss.
 */

function validationRun(): TrainingRun
{
    return TrainingRun::factory()->create(['scenario' => 'ura_finale']);
}

function validationPayload(array $overrides = []): array
{
    return array_merge([
        'turn' => 2,
        'speed' => 550, 'stamina' => 525, 'power' => 601, 'guts' => 275, 'wit' => 190,
        'sp' => 40, 'energy' => 74, 'fans' => 12400,
        'mood' => 'GOOD', 'choice' => 'training-Speed', 'outcome' => 'Success',
        'stage' => 'preview', 'previewed' => '1',
    ], $overrides);
}

it('hands the typed numbers back after a rejected submit', function (): void {
    $run = validationRun();
    TurnEntry::create([
        'training_run_id' => $run->id, 'turn' => 1, 'speed' => 480, 'stamina' => 300,
        'power' => 355, 'guts' => 210, 'wit' => 95, 'sp' => 0, 'energy' => 88,
    ]);

    // A stat above the scenario's own ceiling is a stage-one failure, and it is the one a
    // Trainer hits by mistyping rather than by misunderstanding the flow. This run is URA
    // Finale, whose Speed ceiling is 1400, so 1500 is out by a hundred and still rejected.
    $payload = validationPayload(['speed' => 1500, 'stage' => 'confirm']);

    test()->withHeader('referer', url("/training-runs/{$run->id}"))
        ->post("/training-runs/{$run->id}/turns", $payload)
        ->assertSessionHasErrors('speed');

    $html = test()->get("/training-runs/{$run->id}")->assertOk()->getContent();

    expect($html)
        ->toContain('value="1500"')
        ->toContain('value="525"')
        ->toContain('value="12400"');
});

it('returns the rail to the step the failure came from', function (): void {
    $run = validationRun();
    TurnEntry::create([
        'training_run_id' => $run->id, 'turn' => 1, 'speed' => 480, 'stamina' => 300,
        'power' => 355, 'guts' => 210, 'wit' => 95, 'sp' => 0, 'energy' => 88,
    ]);

    // A failure missing its penalty kind is a stage-two failure, and the rail has to come
    // back on the outcome step - not on the choice cards, which is where a stage-one
    // rehydration would put it.
    test()->withHeader('referer', url("/training-runs/{$run->id}"))
        ->post("/training-runs/{$run->id}/turns", validationPayload([
            'outcome' => 'Failure',
            'penalty_kind' => null,
        ]))
        ->assertSessionHasErrors('penalty_kind');

    $html = test()->get("/training-runs/{$run->id}")->assertOk()->getContent();

    expect($html)->toContain('Step 2 of')
        ->toContain('name="previewed"');
});

it('keeps the chosen discipline and the mood across the redirect', function (): void {
    $run = validationRun();

    test()->withHeader('referer', url("/training-runs/{$run->id}"))
        ->post("/training-runs/{$run->id}/turns", validationPayload(['energy' => 999]))
        ->assertSessionHasErrors('energy');

    $html = test()->get("/training-runs/{$run->id}")->assertOk()->getContent();

    // `checked` on the radio and `selected` on the mood, both read back out of the staged
    // values. A choice that silently reverts is the same loss as a number that reverts.
    // The mood pattern starts at the <option>, not at the <select>: `[^>]*` cannot cross
    // the tag boundary between them, so anchoring on the select's name never matches.
    expect($html)
        ->toMatch('/name="choice"[^>]*value="training-Speed"[^>]*checked/')
        ->toMatch('/<option value="GOOD"[^>]*selected/');
});

it('writes nothing while handing the input back', function (): void {
    $run = validationRun();

    test()->withHeader('referer', url("/training-runs/{$run->id}"))
        ->post("/training-runs/{$run->id}/turns", validationPayload(['speed' => 1500]))
        ->assertSessionHasErrors('speed');

    expect(TurnEntry::query()->where('training_run_id', $run->id)->count())->toBe(0);
});

it('does not repopulate the rail from a rejected raw-form submit', function (): void {
    $run = validationRun();

    // The hatch posts eight fields and no `stage`. Its own inputs already rehydrate from
    // `old()` per field, so the rail must stay empty: two forms filling each other in
    // would be a second way to be wrong, and the rail would show numbers nobody staged.
    test()->withHeader('referer', url("/training-runs/{$run->id}"))
        ->post("/training-runs/{$run->id}/turns", [
            'turn' => 1, 'speed' => 1500, 'stamina' => 90, 'power' => 110,
            'guts' => 80, 'wit' => 95,
        ])
        ->assertSessionHasErrors('speed');

    $html = test()->get("/training-runs/{$run->id}")->assertOk()->getContent();

    expect($html)
        ->not->toContain('name="previewed"')
        ->not->toMatch('/name="choice"[^>]*checked/');
});

it('leaves a plain visit to a run with no staged values at all', function (): void {
    $run = validationRun();

    $html = test()->get("/training-runs/{$run->id}")->assertOk()->getContent();

    // No old input, no staged values: the inputs render empty and the stat band stays
    // unmounted, because a run that has logged nothing has nothing to show.
    expect($html)
        ->not->toContain('name="previewed"')
        ->not->toContain('value="1500"');
});
