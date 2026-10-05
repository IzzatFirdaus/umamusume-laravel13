<?php

declare(strict_types=1);

use App\Models\RaceEntry;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * C-5: four fields the request validates and the model persists, brought onto the
 * create form so a Trainer can record them while creating the run rather than
 * hand-editing the row afterwards. `docs/research-scratch/AUDIT-AND-VERIFICATION.md`, section `## UIX-AUDIT-TRAINING-RUNS.md` C-5 names them:
 * `inheritance_parent_a_id`, `inheritance_parent_b_id`, `current_objective_index`,
 * `shop_resets_in`. The shape is nullable on the request and unchanged on the schema,
 * so a run created before this change reads exactly as one created after with the
 * fields left on Not set.
 */

it('persists the four C-5 fields when the create form posts them', function (): void {
    $trainee = Umamusume::factory()->create();
    $parentA = Umamusume::factory()->create();
    $parentB = Umamusume::factory()->create();

    // Trackblazer composes `grade_objectives`, so `current_objective_index` is in
    // scope for the request's scenario closure (`StoreTrainingRunRequest:65-82`).
    test()->post('/training-runs', [
        'umamusume_id' => $trainee->id,
        'status' => 'Active',
        'scenario' => 'trackblazer',
        'inheritance_parent_a_id' => $parentA->id,
        'inheritance_parent_b_id' => $parentB->id,
        'current_objective_index' => 3,
        'shop_resets_in' => 5,
    ])->assertSessionHasNoErrors()->assertRedirect();

    $run = TrainingRun::firstOrFail();
    expect($run->inheritance_parent_a_id)->toBe($parentA->id)
        ->and($run->inheritance_parent_b_id)->toBe($parentB->id)
        ->and($run->current_objective_index)->toBe(3)
        ->and($run->shop_resets_in)->toBe(5);
});

it('sends the ceiling the Grade Point period field states its bound from', function (): void {
    // The four C-5 fields are markup the component owns, so their captions are measured in
    // tests/browser/runs.spec.ts. What comes from the server is the one number in that block, and a
    // form that invented its own ceiling would drift from `RaceEntry` the moment the constant moved.
    test()->get('/training-runs/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Create')
            ->where('maxObjectiveIndex', RaceEntry::MAX_OBJECTIVE_INDEX));
});

it('leaves the four fields unset when the Trainer posts without them', function (): void {
    // The whole point of `nullable` on the request rules is that a Trainer who does not
    // know the answer is not forced to guess. This pins the surface against a change
    // that flips any of the four to `required`.
    $trainee = Umamusume::factory()->create();

    test()->post('/training-runs', [
        'umamusume_id' => $trainee->id,
        'status' => 'Active',
    ])->assertSessionHasNoErrors()->assertRedirect();

    $run = TrainingRun::firstOrFail();
    expect($run->inheritance_parent_a_id)->toBeNull()
        ->and($run->inheritance_parent_b_id)->toBeNull()
        ->and($run->current_objective_index)->toBeNull()
        ->and($run->shop_resets_in)->toBeNull();
});
