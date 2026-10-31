<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * `C6b.2` (KI-144) — the turn-correction door at `/training-runs/{run}/turns/{turn}/edit`.
 *
 * The Cockpit used to host this form inside its `Career tools` disclosure, which was the interim
 * home for writes the retired record screen owned. The disclosure cannot retire until each write
 * has a door of its own; this is the turn-correction one.
 *
 * **The door reads and does not write.** The form PUTs to `runs.turns.update`, the route and
 * validator that already own a turn's columns, so these cases assert the page's props and then
 * assert that the *existing* route still carries the write.
 */

function turnEditFixture(): array
{
    $run = TrainingRun::factory()->create([
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->state(['name' => 'Rice Shower'])->create()->id,
    ]);

    $turn = TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 7,
        'speed' => 100,
        'stamina' => 90,
        'power' => 80,
        'guts' => 70,
        'wit' => 60,
    ]);

    return [$run, $turn];
}

it('renders the turn-correction door with the turn it corrects', function (): void {
    [$run, $turn] = turnEditFixture();

    $this->get(route('runs.turns.edit', [$run, $turn]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/TurnEdit')
            ->where('run.id', $run->id)
            ->where('turn.turn', 7)
            ->where('turn.speed', 100)
            // The form posts to the one route that owns a turn's columns, never to a second one.
            ->where('update_url', route('runs.turns.update', [$run, $turn]))
            ->has('energy_states')
            ->has('moods')
        );
});

it('saves a corrected reading through the turn update route', function (): void {
    [$run, $turn] = turnEditFixture();

    $this->put(route('runs.turns.update', [$run, $turn]), [
        'turn' => 7,
        'speed' => 111,
        'stamina' => 90,
        'power' => 80,
        'guts' => 70,
        'wit' => 60,
    ])->assertRedirect();

    expect($turn->fresh()?->speed)->toBe(111);
});

it('refuses an invalid reading and writes nothing', function (): void {
    [$run, $turn] = turnEditFixture();

    $this->put(route('runs.turns.update', [$run, $turn]), [
        'turn' => 7,
        'speed' => -5,
        'stamina' => 90,
        'power' => 80,
        'guts' => 70,
        'wit' => 60,
    ])->assertSessionHasErrors('speed');

    expect($turn->fresh()?->speed)->toBe(100);
});
