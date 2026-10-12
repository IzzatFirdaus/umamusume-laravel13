<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The catalog detail's "New run" action (plan Slice 4.3): the trainee the Trainer was reading is
 * carried into `SetupDraft`, and the wizard opens at its first step. The handoff's whole footprint
 * is two session keys — no run row exists until Preflight, which is `SetupDraft`'s own contract
 * and the reason the wizard drafts in the session at all.
 */

it('carries the catalog trainee into the setup draft and opens the wizard at its first step', function (): void {
    $trainee = Umamusume::factory()->create();

    $this->post(route('career.trainee.carry', $trainee))
        ->assertRedirect(route('career.scenario'));

    // The draft's read-back is the trainee step's own props: the same stored choice a Trainer who
    // picked her inside the wizard sees, not a second representation of the fact.
    $this->get(route('career.trainee'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('selected', $trainee->id)
            ->where('selectedName', $trainee->name));
});

it('keeps the carried trainee across the wizard steps', function (): void {
    $trainee = Umamusume::factory()->create();

    $this->post(route('career.trainee.carry', $trainee))->assertRedirect(route('career.scenario'));

    // Forward to the target step, then back to the trainee step: the draft outlives both reads,
    // which is what makes the carried choice survive normal navigation rather than a one-page prop.
    $this->get(route('career.target'))->assertOk();
    $this->get(route('career.trainee'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('selected', $trainee->id)
            ->where('selectedName', $trainee->name));
});

it('creates no run row on the carry', function (): void {
    $trainee = Umamusume::factory()->create();

    $this->post(route('career.trainee.carry', $trainee))->assertRedirect(route('career.scenario'));

    expect(TrainingRun::query()->count())->toBe(0);
});

it('refuses an unknown trainee with the route binding own 404', function (): void {
    $this->post('/career/setup/trainee/999999/carry')->assertNotFound();
});
