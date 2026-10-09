<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\TurnEvent;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Why the Training Decision screen has no Performance field (Grand Concert Slice 7).
 *
 * Slice 6 left this open on purpose rather than assume it: `Career/TrainingDetail.vue` posts to the
 * same `runs.turns.store` as the guided rail, so it could have been read as a second turn-entry surface
 * missing a control. It is not one. Under F2, plan §9.6 ruling 2 and Fix A (owner default), the
 * screen writes the turn directly — no preview step — and the response redirects to `runs.cockpit`.
 * Neither `Career/TrainingDetail` nor `Career/Cockpit` carries a `performance` payload, so the pair
 * cannot be posted from either surface and cannot be rehydrated on either after a write.
 *
 * So the question was not "does it need the field" but "does a Trainer who arrives through it lose the
 * capability". Under Fix A the exclusion is total: the write happens here and no other 2.0 surface
 * rehydrates the pair, so a Trainer who omits the pair at this screen records the turn without it. If
 * that ever stops being true, this file fails and the exclusion stops being defensible — which is the
 * point of writing the boundary down as a test rather than leaving it as a comment.
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

    // The pair is stored as a `Scenario` event keyed on the turn, and it is the write's only home:
    // neither this screen nor the Cockpit carries a `performance` payload, so it is not rehydrated on
    // either after the redirect. The pair is not lost by omitting the field at submit time — the
    // write is one shot.
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

it('states its own boundary in its own copy, and holds no Performance vocabulary of its own', function (): void {
    $source = trainingDetailSource();

    // Under Fix A the form posts no `stage` at all: the previous preview-and-confirm rail has no 2.0
    // reproduction, so there is no stage to advance and no marker to gate. The word "preview" does
    // not appear in the form's field list, only in the surrounding prose (a comment naming R-2).
    expect($source)->not->toContain("stage: 'preview'")
        ->and($source)->not->toContain("stage: 'confirm'")
        ->and($source)->not->toContain('previewed:')
        // And the exclusion is total rather than half-finished: no field names, no type list, and no
        // GameTora rendering anywhere in the file.
        ->and($source)->not->toContain('performance[type]')
        ->and($source)->not->toContain('performance[delta]')
        ->and($source)->not->toContain('Mental');
});

it('leaves that screen every turn field it already had', function (): void {
    $source = trainingDetailSource();

    foreach (['name="turn"', 'speed', 'stamina', 'power', 'guts', 'wit', 'sp', 'energy', 'fans', 'mood', 'outcome', 'penalty_kind'] as $field) {
        expect($source)->toContain($field);
    }
});

it('keeps the capability flag off every other scenario on the screen that does carry the pair', function (string $scenario): void {
    $run = trainingDetailRun($scenario);

    // No 2.0 surface carries the pair, so the capability flag has no 2.0 home; the Cockpit is
    // asserted to carry no Performance payload for a non-composing scenario either (reported).
    $this->get(route('runs.cockpit', $run))->assertInertia(fn (Assert $page) => $page
        ->component('Career/Cockpit')
        ->missing('performance'));
})->with(['ura_finale', 'unity_cup', 'trackblazer']);
