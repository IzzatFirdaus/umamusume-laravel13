<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\TurnEntry;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * The timeline's origin: where the rail opens.
 *
 * A snapshot's rail starts at the position it was imported at, never at a synthesized turn 1; a
 * new-career run starts at its first logged turn. Neither renumbers the rows: the origin is the head
 * of the rail, not a row in it.
 */
it('opens a snapshot timeline at the imported position, with no rows renumbered', function (): void {
    $run = TrainingRun::factory()->create([
        'career_position' => ['year' => 3, 'month' => 10, 'phase' => 'Early', 'turn_index' => 67],
    ]);

    $this->get(route('runs.timeline', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Timeline')
            ->where('origin.year_label', 'Senior Year')
            ->where('origin.month_label', 'Early October')
            ->where('origin.turn', 67)
            ->where('origin.imported', true)
            ->has('entries', 0));
});

it('still opens a new-career timeline at Junior Early January', function (): void {
    $run = TrainingRun::factory()->create();
    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1]);

    $this->get(route('runs.timeline', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('origin.year_label', 'Junior Year')
            ->where('origin.month_label', 'Early January')
            ->where('origin.turn', 1)
            ->where('origin.imported', false));
});
