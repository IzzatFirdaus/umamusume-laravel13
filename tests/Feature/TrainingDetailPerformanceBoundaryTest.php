<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\TurnEvent;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The Training Decision screen's Performance boundary (Grand Concert Slice 7).
 *
 * Slice 6 left the field off this screen on purpose rather than assume it, and Slice 7 wrote that
 * exclusion down as Fix A (owner default): under F2, plan §9.6 ruling 2, the screen writes the turn
 * directly — no preview step — and the response redirects to `runs.cockpit`.
 *
 * **Overturned 2026-10-13, owner ruling on the remaining-implementation brief (D1):** the pair is
 * offered here after all. `SCREEN_SPEC.md`'s dated rail row already specified the field in full —
 * the `performance.enabled` flag, the five client words, the signed delta with no bound and no
 * placeholder — so this file's job flips from guarding the absence to pinning the shape that row
 * names. The Cockpit half of Fix A stands untouched: the correction form gains no pair and the
 * Cockpit carries no `performance` payload, because `updateTurn` writes the entry row only and a
 * correction is not a second observation.
 */
function trainingDetailRun(string $scenario = 'our_grand_concert'): TrainingRun
{
    return TrainingRun::factory()->create(['scenario' => $scenario]);
}

function trainingDetailSource(): string
{
    return (string) file_get_contents(base_path('resources/js/pages/Career/TrainingDetail.vue'));
}

/**
 * Exactly what that screen posts: the turn record and the optional pair, with no `stage` marker.
 *
 * @param  array<string, mixed>  $performance
 * @return array<string, mixed>
 */
function recordSubmit(array $performance = []): array
{
    $payload = [
        'turn' => 1,
        'speed' => 600,
        'stamina' => 600,
        'power' => 600,
        'guts' => 600,
        'wit' => 600,
        'choice' => 'training-Speed',
        'outcome' => 'Success',
    ];

    return $performance === [] ? $payload : [...$payload, 'performance' => $performance];
}

it('shares the endpoint with the turn write and commits the turn it posts', function (): void {
    $run = trainingDetailRun();

    // The surface is real and its action is the canonical one — that much is equivalence.
    $this->get(route('runs.training', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/TrainingDetail')
            ->where('write.action', route('runs.turns.store', $run)));

    // Under Fix A the write lands on the Cockpit redirect: no preview step in between, so a Training
    // Detail submit writes the row and the associated failure or Performance event. Without a pair the
    // write is a bare turn row; the exclusion of the field is not a refusal of the write.
    $this->post(route('runs.turns.store', $run), recordSubmit())
        ->assertRedirect(route('runs.cockpit', $run));

    expect($run->turnEntries()->count())->toBe(1)
        ->and(TurnEvent::query()->where('training_run_id', $run->id)->count())->toBe(0);
});

it('writes the pair when it is posted, but no 2.0 surface rehydrates it after the write', function (): void {
    $run = trainingDetailRun();

    $this->post(route('runs.turns.store', $run), recordSubmit(['type' => 'Dance', 'delta' => '12']))
        ->assertRedirect(route('runs.cockpit', $run));

    // The pair is stored as a `Scenario` event keyed on the turn, and the write's redirect target
    // rehydrates nothing: the Cockpit carries no `performance` payload, so the pair is not read back
    // there after the redirect. The capability now rides on this screen's own write payload, but a
    // stored observation has no renderer yet — reading history back is the model's
    // (`PerformanceReadPathTest`), and a display surface for it is a later, separately-specified
    // decision rather than something this overturn invents.
    expect(TurnEvent::query()->where('training_run_id', $run->id)->where('event_type', 'Scenario')->count())->toBe(1);

    $this->get(route('runs.cockpit', $run))->assertInertia(fn (Assert $page) => $page
        ->component('Career/Cockpit')
        ->where('run.scenario_label', 'Our Grand Concert')
        ->missing('performance'));
});

it('refuses a turn the pair fails on, rather than discarding half the submission', function (): void {
    $run = trainingDetailRun();

    $this->post(route('runs.turns.store', $run), recordSubmit(['type' => 'Mental', 'delta' => '12']))
        ->assertSessionHasErrors(['performance.type']);

    expect($run->turnEntries()->count())->toBe(0);

    // The refusal writes nothing, and neither surface carries a `performance` payload to rehydrate the
    // refused pair (reported). A subsequent GET of the Cockpit therefore shows no partial state.
    $this->get(route('runs.cockpit', $run))->assertInertia(fn (Assert $page) => $page
        ->component('Career/Cockpit')
        ->missing('performance'));
});

it('carries the pair behind the capability flag in its own copy, and no borrowed word', function (): void {
    $source = trainingDetailSource();

    // Under Fix A the form posts no `stage` at all: the previous preview-and-confirm rail has no 2.0
    // reproduction, so there is no stage to advance and no marker to gate. The word "preview" does
    // not appear in the form's field list, only in the surrounding prose (a comment naming R-2).
    expect($source)->not->toContain("stage: 'preview'")
        ->and($source)->not->toContain("stage: 'confirm'")
        ->and($source)->not->toContain('previewed:')
        // The pair is drawn exactly as the spec row names it, and the GameTora rendering stays out:
        // the five words come from the enum through the controller, never as literals in this file.
        ->and($source)->toContain('performance[type]')
        ->and($source)->toContain('performance[delta]')
        ->and($source)->toContain('none, nothing to record')
        ->and($source)->not->toContain('Mental');
});

it('leaves that screen every turn field it already had', function (): void {
    $source = trainingDetailSource();

    foreach (['name="turn"', 'speed', 'stamina', 'power', 'guts', 'wit', 'sp', 'energy', 'fans', 'mood', 'outcome', 'penalty_kind'] as $field) {
        expect($source)->toContain($field);
    }
});

it('keeps the capability flag off every other scenario on the screen that carries the pair', function (string $scenario): void {
    $run = trainingDetailRun($scenario);

    // The screen carries the pair now, so the gate is asserted where it lives: a non-composing
    // career gets `enabled: false` and an empty type list, so the page holds no vocabulary for a
    // control it does not draw.
    $this->get(route('runs.training', $run))->assertInertia(fn (Assert $page) => $page
        ->component('Career/TrainingDetail')
        ->where('write.performance.enabled', false)
        ->where('write.performance.types', []));
})->with(['ura_finale', 'unity_cup', 'trackblazer']);
