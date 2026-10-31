<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * `C6b.1` (KI-144) — the run-identity door at `/training-runs/{run}/settings`.
 *
 * The Cockpit used to host these four forms inside its `Career tools` disclosure, which was the
 * interim home for writes the retired record screen owned. The disclosure cannot retire until each
 * write has a door of its own; this is the identity one.
 *
 * **The door reads and does not write.** Every field still PUTs to `runs.update`, the route and
 * validator that already own these columns, so these cases assert the page's props and then assert
 * that the *existing* route still carries the write. A second identity-write route would be a
 * second write path for the same columns.
 *
 * The rendered copy, the 44px sweep and the axe scan live in `tests/browser/run-settings.spec.ts`.
 */

function runSettingsRun(): TrainingRun
{
    return TrainingRun::factory()->create([
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->state([
            'name' => 'Rice Shower',
        ])->create()->id,
    ]);
}

it('renders the identity door with the run and its identity props', function (): void {
    $run = runSettingsRun();

    $this->get(route('runs.settings', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/RunSettings')
            ->where('run.id', $run->id)
            ->where('run.trainee', 'Rice Shower')
            // The forms post to the one route that owns these columns, never to a second one.
            ->where('run.update_url', route('runs.update', $run))
            ->has('identity.stat_order')
            ->has('identity.caps')
        );
});

it('saves an identity field through the run update route', function (): void {
    $run = runSettingsRun();

    $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'status' => RunStatus::Active->value,
        'trainee_rarity' => 3,
        'potential_level' => 2,
    ])->assertRedirect();

    $fresh = $run->fresh();

    expect($fresh?->trainee_rarity)->toBe(3)
        ->and($fresh?->potential_level)->toBe(2);
});

it('surfaces a validation error and writes nothing', function (): void {
    $run = runSettingsRun();

    $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'status' => 'nonsense',
    ])->assertSessionHasErrors('status');

    expect($run->fresh()?->status)->toBe(RunStatus::Active);
});

it('redirects back to the door the form posted from', function (): void {
    $run = runSettingsRun();

    $this->from(route('runs.settings', $run))->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'status' => RunStatus::Active->value,
    ])->assertRedirect(route('runs.settings', $run));
});
