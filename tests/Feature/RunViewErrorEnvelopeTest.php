<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Static review F-04 gate. The `previewed` request-stage error and a per-field error must
 * arrive in one shared error envelope, and the page renders that envelope as one list
 * (ADR-0020 §1: the envelope is the `errors` prop, the list is in
 * `resources/js/pages/Runs/Show.vue`). The old top-line <p> treatment is gone. Pinned against
 * the resolved props after a rejected confirm submit; the rendered list is asserted in
 * tests/browser/run-detail.spec.ts.
 */

it('renders the previewed error and a field error inside the same list', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    test()->post(route('runs.turns.store', $run), [
        'stage' => 'confirm',
        'turn' => 2,
        'speed' => 550,
        'stamina' => 525,
        'power' => 601,
        'guts' => 75,
        'wit' => 95,
        'sp' => 240,
        'energy' => 88,
        'fans' => 9000,
        // `choice` omitted (per-field error) and `previewed` omitted (stage error).
    ]);

    $stageMessage = 'Preview the turn before confirming it.';
    $fieldMessage = 'Choose what this turn did.';

    // No assertSessionHasErrors between the POST and the GET: the assertion boots the
    // session store in a way that ages the flashed errors before the next request, so the
    // resolved page is the assert. The errors flash on the redirect and arrive on the
    // follow-up GET as the shared `errors` prop.
    test()->get(route('runs.show', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('errors', fn (Collection $errors): bool => $errors->contains($stageMessage)
                && $errors->contains($fieldMessage)));

    // The shared list: the bag renders through one `<ul>` whose items are the `<li>`s, and no
    // dedicated paragraph renders the stage error outside it (the per-form paragraphs are
    // bound to fixed keys and cannot carry `previewed`).
    $source = (string) file_get_contents(base_path('resources/js/pages/Runs/Show.vue'));

    expect($source)->toMatch('/<ul v-if="Object\.keys\(errors\)\.length > 0".*?<li v-for="\(message, key\) in errors"/s')
        ->and($source)->not->toContain('errors.previewed');
});
