<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\Umamusume;

/*
 * The scenario switch: PUT /training-runs/{run}, which had a route, a form and a request
 * but no test of its own.
 *
 * The interesting part is not that a scenario can be set. It is the boundary the request
 * draws and the normalisation it does before the model sees anything:
 *
 *   - A scenario is validated against the composition matrix's keys, so a value outside
 *     them is refused rather than stored. A run with a scenario no component can resolve
 *     would render a strip it has no mechanic for (D-240).
 *   - An empty scenario is normalised to null, so one representation reaches the model and
 *     `whereNull('scenario')` finds unset runs instead of missing the empty-string ones
 *     (StoreTrainingRunRequest::prepareForValidation).
 *
 * The switch is also the only way a run leaves the baseline strip, so both directions are
 * asserted against the model and not only against the redirect.
 */
function updatableRun(array $overrides = []): TrainingRun
{
    return TrainingRun::factory()->create(array_merge(['scenario' => 'ura_finale'], $overrides));
}

it('switches a run to another scenario in the matrix', function (): void {
    $run = updatableRun();

    test()->put("/training-runs/{$run->id}", [
        'umamusume_id' => $run->umamusume_id,
        'scenario' => 'unity_cup',
        'status' => RunStatus::Active->value,
    ])->assertRedirect(route('runs.show', $run));

    expect($run->refresh()->scenario)->toBe('unity_cup');
});

it('refuses a scenario the matrix does not have', function (): void {
    $run = updatableRun();

    test()->put("/training-runs/{$run->id}", [
        'umamusume_id' => $run->umamusume_id,
        'scenario' => 'grand_masters',
        'status' => RunStatus::Active->value,
    ])->assertSessionHasErrors('scenario');

    // The stored scenario is untouched: a refused switch must not blank the run.
    expect($run->refresh()->scenario)->toBe('ura_finale');
});

it('normalises an empty scenario to none, so one representation reaches the model', function (): void {
    $run = updatableRun();

    test()->put("/training-runs/{$run->id}", [
        'umamusume_id' => $run->umamusume_id,
        'scenario' => '',
        'status' => RunStatus::Active->value,
    ])->assertSessionHasNoErrors();

    // Not '' and not null-by-accident: `whereNull` has to be able to find this run.
    expect($run->refresh()->scenario)->toBeNull()
        ->and($run->hasScenario())->toBeFalse();
});

it('leaves the scenario alone when the field is omitted', function (): void {
    $run = updatableRun();

    // `scenario` is nullable, so an absent key is absent from `validated()` and `update()`
    // never touches the column. That is deliberate and is what the form's own comment
    // claims: the update carries through the fields it is not editing rather than
    // blanking them. The scenario switch is a select, so it always posts a value.
    test()->put("/training-runs/{$run->id}", [
        'umamusume_id' => $run->umamusume_id,
        'status' => RunStatus::Active->value,
    ])->assertSessionHasNoErrors();

    expect($run->refresh()->scenario)->toBe('ura_finale')
        ->and($run->hasScenario())->toBeTrue();
});

it('changes the status word and keeps the rest of the run', function (): void {
    $run = updatableRun(['notes' => 'kept']);

    test()->put("/training-runs/{$run->id}", [
        'umamusume_id' => $run->umamusume_id,
        'scenario' => 'ura_finale',
        'status' => RunStatus::Completed->value,
    ])->assertRedirect();

    $run->refresh();

    expect($run->status)->toBe(RunStatus::Completed)
        ->and($run->notes)->toBe('kept')
        ->and($run->turnEntries()->count())->toBe(0);
});

it('refuses a status outside the enum', function (): void {
    $run = updatableRun();

    test()->put("/training-runs/{$run->id}", [
        'umamusume_id' => $run->umamusume_id,
        'status' => 'Paused',
    ])->assertSessionHasErrors('status');

    expect($run->refresh()->status)->toBe(RunStatus::Active);
});

it('refuses a trainee who is not in the catalog', function (): void {
    $run = updatableRun();

    test()->put("/training-runs/{$run->id}", [
        'umamusume_id' => 999999,
        'status' => RunStatus::Active->value,
    ])->assertSessionHasErrors('umamusume_id');
});

it('refuses notes past the five-thousand-character limit', function (): void {
    $run = updatableRun();

    test()->put("/training-runs/{$run->id}", [
        'umamusume_id' => $run->umamusume_id,
        'status' => RunStatus::Active->value,
        'notes' => str_repeat('a', 5001),
    ])->assertSessionHasErrors('notes');
});

it('renders the scenario error on the form that refused it', function (): void {
    $run = updatableRun();

    // The run page is a second rail-shaped surface and it does render its own error, so a
    // refused switch tells the Trainer why instead of reloading the form silently. Only
    // `scenario` can fail here: the other two fields this form posts are hidden inputs
    // the page itself rendered, so it cannot submit a value its own markup disallows.
    //
    // `assertSessionHasErrors` is deliberately absent from this chain: reading the flashed
    // bag consumes it, and this test is about the message reaching the *next* request.
    test()->withHeader('referer', url("/training-runs/{$run->id}"))
        ->put("/training-runs/{$run->id}", [
            'umamusume_id' => $run->umamusume_id,
            'scenario' => 'grand_masters',
            'status' => RunStatus::Active->value,
        ])
        ->assertRedirect();

    // One GET, not two: the first request after the failed PUT is the one that receives
    // the flashed bag, so a second GET would read an empty one.
    $response = test()->get("/training-runs/{$run->id}")->assertOk();
    $html = $response->getContent();

    // The message is read out of the bag the template was given rather than transcribed,
    // so this asserts the thing that matters - the reason reaches the page - and stays
    // true if the wording is reworded.
    $messages = $response->viewData('errors')->getBag('default')->get('scenario');

    expect($messages)->not->toBeEmpty();

    foreach ((array) $messages as $message) {
        expect($html)->toContain($message);
    }

    expect($html)
        // The stored scenario survives the refusal, so the select still shows it.
        ->toMatch('/<option value="ura_finale"[^>]*selected/');
});

it('leaves a run that was never given a scenario reporting none, not the baseline key', function (): void {
    $umamusume = Umamusume::factory()->create();
    $run = TrainingRun::factory()->create(['umamusume_id' => $umamusume->id, 'scenario' => null]);

    // scenarioKey() falls back to the baseline so the strip has something to compose;
    // hasScenario() is what the goal panels read, and they must not borrow the baseline's
    // schedule (D-221). Both facts are needed and they are not the same fact.
    expect($run->scenarioKey())->toBe(config('scenarios.baseline'))
        ->and($run->hasScenario())->toBeFalse();
});
