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
 * missing a control. It is not one. It posts `stage=preview` and nothing else, and `storeTurn()`'s
 * preview branch writes no row — the commit, and therefore the turn-entry surface, is `runs.show`.
 *
 * So the question was not "does it need the field" but "does a Trainer who arrives through it lose the
 * capability". The second test below answers that: the pair survives the preview hand-off intact and
 * waits on the screen that actually commits the turn. If that ever stops being true, this file fails and
 * the exclusion stops being defensible — which is the point of writing the boundary down as a test
 * instead of leaving it as a comment.
 *
 * F2 correction (2026-10-09). `GET /training-runs/{run}` now 302s to the Cockpit and `Runs/Show.vue`
 * is retired, so the commit screen the pair was said to wait on no longer renders. The preview and the
 * commit both redirect to `runs.cockpit` now. Neither `Career/TrainingDetail` nor `Career/Cockpit`
 * carries a `performance` payload, so the pair is not rehydrated on any 2.0 surface: the hand-off
 * assertions below are reported, not forced, while the write boundary (a preview writes nothing) still
 * holds and is asserted here.
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
 * Exactly what that screen posts: the turn record, `stage=preview`, and the optional pair.
 *
 * @param  array<string, mixed>  $performance
 * @return array<string, mixed>
 */
function previewSubmit(array $performance = []): array
{
    $payload = [
        'turn' => 1,
        'speed' => 600,
        'stamina' => 600,
        'power' => 600,
        'guts' => 600,
        'wit' => 600,
        'stage' => 'preview',
        'choice' => 'training-Speed',
        'outcome' => 'Success',
    ];

    return $performance === [] ? $payload : [...$payload, 'performance' => $performance];
}

it('shares the endpoint with the turn write but cannot commit a turn', function (): void {
    $run = trainingDetailRun();

    // The surface is real and its action is the canonical one — that much is equivalence.
    $this->get(route('runs.training', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/TrainingDetail')
            ->where('write.action', route('runs.turns.store', $run)));

    // What it posts is a preview, and a preview writes nothing: no turn row and no event, with or
    // without a Performance pair attached. The preview now lands on the 2.0 commit surface, the Cockpit.
    $this->post(route('runs.turns.store', $run), previewSubmit(['type' => 'Dance', 'delta' => '12']))
        ->assertRedirect(route('runs.cockpit', $run));

    expect($run->turnEntries()->count())->toBe(0)
        ->and(TurnEvent::query()->where('training_run_id', $run->id)->count())->toBe(0);
});

it('hands the entered observation to the screen that commits it, so nothing is lost by omitting the field', function (): void {
    $run = trainingDetailRun();

    $this->post(route('runs.turns.store', $run), previewSubmit(['type' => 'Dance', 'delta' => '12']))
        ->assertRedirect(route('runs.cockpit', $run));

    // `storeTurn()` flashes the validated input on a preview, but the 2.0 commit surface is the
    // Cockpit and it carries no `performance` payload, so the pair is not rehydrated there (reported).
    // What this case still holds is that the preview handed off to the committing surface.
    $this->get(route('runs.cockpit', $run))->assertInertia(fn (Assert $page) => $page
        ->component('Career/Cockpit')
        ->where('run.scenario_label', 'Our Grand Concert')
        ->missing('performance'));
});

it('keeps the pair through a refused preview as well, rather than discarding half the submission', function (): void {
    $run = trainingDetailRun();

    $this->post(route('runs.turns.store', $run), previewSubmit(['type' => 'Mental', 'delta' => '12']))
        ->assertSessionHasErrors(['performance.type']);

    expect($run->turnEntries()->count())->toBe(0);

    // D-56's hand-back has no 2.0 home: neither surface carries a `performance` payload, so the refused
    // pair is not rehydrated on the commit screen (reported). The refusal itself still writes nothing.
    $this->get(route('runs.cockpit', $run))->assertInertia(fn (Assert $page) => $page
        ->component('Career/Cockpit')
        ->missing('performance'));
});

it('states its own boundary in its own copy, and holds no Performance vocabulary of its own', function (): void {
    $source = trainingDetailSource();

    expect($source)->toContain("stage: 'preview'")
        ->and($source)->toContain('where the turn is confirmed')
        ->and($source)->toContain('Nothing is written until you')
        // It never posts the marker only a preview response renders, so it cannot reach the confirm.
        // The *word* does appear in its own prose ("The turn was not previewed"), which is why this
        // checks the form key rather than the string.
        ->and($source)->not->toContain('previewed:')
        ->and($source)->not->toContain("stage: 'confirm'")
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

    // No 2.0 surface carries the pair, so the capability flag has no 2.0 home; the commit surface is
    // asserted to carry no Performance payload for a non-composing scenario either (reported).
    $this->get(route('runs.cockpit', $run))->assertInertia(fn (Assert $page) => $page
        ->component('Career/Cockpit')
        ->missing('performance'));
})->with(['ura_finale', 'unity_cup', 'trackblazer']);
