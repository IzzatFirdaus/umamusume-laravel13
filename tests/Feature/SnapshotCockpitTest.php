<?php

declare(strict_types=1);

use App\Domain\Career\CareerPosition;
use App\Enums\RunMode;
use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Plan §4.4 / §5.3: the cockpit reads the career position the run carries rather than the count of
 * turns this tool happens to hold for it.
 *
 * The defect this closes is concrete. `CreateSnapshotRun` writes exactly one turn entry, at the
 * imported turn, so a career entered at Senior Early October holds one row. A header that counts rows
 * prints Junior Early January turn 1 for it, which is the misreading `CareerPosition` exists to
 * prevent. The rendered copy is the browser suite's; what is proven here is the wire contract.
 */

it('reads a named career position into the header instead of counting logged turns', function (): void {
    $run = TrainingRun::factory()->create([
        'scenario' => 'unity_cup',
        'status' => RunStatus::Active,
        'mode' => RunMode::Snapshot,
        // Senior Early October is turn 67: two completed years of 24, nine completed months of two
        // turns each, and the Early half of the tenth.
        'career_position' => CareerPosition::fromTurnIndex(67),
        'career_position_source' => 'imported',
    ]);

    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 67,
        'speed' => 900,
        'stamina' => 700,
        'power' => 800,
        'guts' => 600,
        'wit' => 650,
    ]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Cockpit')
            ->where('hasImportedPosition', true)
            ->where('careerPosition.turn_index', 67)
            ->where('careerPosition.year', 3)
            ->where('careerPosition.month', 10)
            ->where('careerPosition.phase', 'Early')
            ->where('header.year_label', 'Senior Year')
            ->where('header.month_label', 'Early October')
            ->where('header.turn', 67)
            ->where('header.values.turn', 67));
});

it('derives the header from the turn log for a career that named no position', function (): void {
    $run = TrainingRun::factory()->create([
        'scenario' => 'unity_cup',
        'status' => RunStatus::Active,
    ]);

    foreach ([1, 2, 3] as $turn) {
        TurnEntry::factory()->create([
            'training_run_id' => $run->id,
            'turn' => $turn,
            'speed' => 400,
            'stamina' => 400,
            'power' => 400,
            'guts' => 400,
            'wit' => 400,
        ]);
    }

    // Turn 3 is Junior Year, the second month, its Early half: an odd turn is Early, and the month is
    // intdiv(3 - 1, 2) + 1 = 2.
    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Cockpit')
            ->where('hasImportedPosition', false)
            ->where('careerPosition.turn_index', 3)
            ->where('header.year_label', 'Junior Year')
            ->where('header.month_label', 'Early February')
            ->where('header.turn', 3));
});

it('claims no position at all for a run with neither a named position nor a logged turn', function (): void {
    $run = TrainingRun::factory()->create([
        'scenario' => 'unity_cup',
        'status' => RunStatus::Active,
    ]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Cockpit')
            ->where('hasImportedPosition', false)
            ->where('careerPosition', null)
            ->where('header.year_label', null)
            ->where('header.month_label', null)
            ->where('header.turn', 0));
});
