<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * `C6b.3` (KI-144) — the delete-run confirmation door at `/training-runs/{run}/delete`.
 *
 * Deletion is the one write that must never happen by accident, so its door states what will be
 * lost and `runs.destroy` refuses a request that carries no confirmation flag. These cases assert
 * the page, the refusal, and the delete.
 */

function runDeleteFixture(): TrainingRun
{
    $run = TrainingRun::factory()->create([
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->state(['name' => 'Rice Shower'])->create()->id,
    ]);

    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1]);
    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 2]);

    return $run;
}

it('renders the confirmation door with what will be lost', function (): void {
    $run = runDeleteFixture();

    $this->get(route('runs.confirm-delete', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/ConfirmDelete')
            ->where('run.id', $run->id)
            ->where('run.trainee', 'Rice Shower')
            ->where('counts.turns', 2)
            ->where('destroy_url', route('runs.destroy', $run))
        );
});

it('refuses a delete that carries no confirmation and keeps the run', function (): void {
    $run = runDeleteFixture();

    $this->delete(route('runs.destroy', $run))
        ->assertSessionHasErrors('confirmed');

    expect(TrainingRun::find($run->id))->not->toBeNull();
});

it('deletes the run once the confirmation flag is present', function (): void {
    $run = runDeleteFixture();

    $this->delete(route('runs.destroy', $run), ['confirmed' => 1])
        ->assertRedirect(route('runs.index'));

    expect(TrainingRun::find($run->id))->toBeNull();
});
