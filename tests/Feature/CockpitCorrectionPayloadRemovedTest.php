<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;

/**
 * T01 — Remove obsolete Cockpit correction payload.
 *
 * The Cockpit used to host a turn-correction form inside its `Career tools` disclosure. That
 * disclosure was the interim home for writes the retired record screen owned, and it cannot
 * retire until each write has a door of its own. This is the turn-correction one.
 *
 * The form was moved to `TurnEdit.vue` (C6b.2, KI-144), which posts to `runs.turns.update` —
 * the route and validator that already own a turn's columns. The Cockpit payload was retired
 * with the old form, but the `?edit_turn` handling and the `correction` prop remained.
 *
 * T01 removes the obsolete payload now that the dedicated door exists and Timeline links to it.
 */
it('does not include correction prop in cockpit payload', function (): void {
    $trainee = Umamusume::factory()->create();
    $run = TrainingRun::factory()->create([
        'umamusume_id' => $trainee->id,
        'scenario' => 'unity_cup',
    ]);

    // Record a turn so the run has recorded progress.
    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 1,
    ]);

    // The Cockpit should not send a `correction` prop.
    $this->get(route('runs.cockpit', $run))
        ->assertInertia(fn ($page) => $page
            ->component('Career/Cockpit')
            ->missing('correction')
        );
});

it('turn edit door still works via runs.turns.edit route', function (): void {
    $trainee = Umamusume::factory()->create();
    $run = TrainingRun::factory()->create([
        'umamusume_id' => $trainee->id,
        'scenario' => 'unity_cup',
    ]);

    $turn = TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 1,
        'speed' => 50,
        'stamina' => 60,
        'power' => 70,
        'guts' => 80,
        'wit' => 90,
    ]);

    // The turn edit door should render.
    $this->get(route('runs.turns.edit', [$run, $turn]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Career/TurnEdit')
            ->where('run.id', $run->id)
            ->where('turn.id', $turn->id)
        );
});

it('timeline entry includes edit_url for turn correction door', function (): void {
    $trainee = Umamusume::factory()->create();
    $run = TrainingRun::factory()->create([
        'umamusume_id' => $trainee->id,
        'scenario' => 'unity_cup',
    ]);

    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 1,
    ]);

    // Timeline entries should carry `edit_url` pointing to `runs.turns.edit`.
    $this->get(route('runs.timeline', $run))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Career/Timeline')
            ->has('entries.0.edit_url')
        );
});
