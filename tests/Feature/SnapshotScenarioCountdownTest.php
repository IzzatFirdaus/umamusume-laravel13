<?php

declare(strict_types=1);

use App\Domain\Career\CareerPosition;
use App\Enums\RunMode;
use App\Enums\RunStatus;
use App\Models\TrainingRun;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Plan §5.3: the scenario countdown is a figure a Trainer names when entering a career mid-flight, and
 * it reaches the cockpit header as its own prop.
 *
 * Null is an absence and not a zero. A career that named no milestone has no number to count down, and
 * printing `0 turns` for it would state a milestone arriving this turn, which the tool cannot know.
 */

it('carries the countdown an imported position names', function (): void {
    $run = TrainingRun::factory()->create([
        'scenario' => 'unity_cup',
        'status' => RunStatus::Active,
        'mode' => RunMode::Snapshot,
        'career_position' => CareerPosition::fromTurnIndex(67, 5),
        'career_position_source' => 'imported',
    ]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Cockpit')
            ->where('scenarioCountdown', 5)
            ->where('careerPosition.scenario_countdown', 5));
});

it('carries a countdown of zero as the reading it is, not as an absence', function (): void {
    $run = TrainingRun::factory()->create([
        'scenario' => 'unity_cup',
        'status' => RunStatus::Active,
        'mode' => RunMode::Snapshot,
        'career_position' => CareerPosition::fromTurnIndex(67, 0),
        'career_position_source' => 'imported',
    ]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Cockpit')
            ->where('scenarioCountdown', 0));
});

it('carries no countdown for a position that named none', function (): void {
    $run = TrainingRun::factory()->create([
        'scenario' => 'unity_cup',
        'status' => RunStatus::Active,
        'mode' => RunMode::Snapshot,
        'career_position' => CareerPosition::fromTurnIndex(67),
        'career_position_source' => 'imported',
    ]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Cockpit')
            ->where('scenarioCountdown', null)
            ->where('careerPosition.scenario_countdown', null));
});

it('carries no countdown for a career that derives its own position', function (): void {
    $run = TrainingRun::factory()->create([
        'scenario' => 'unity_cup',
        'status' => RunStatus::Active,
    ]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Cockpit')
            ->where('scenarioCountdown', null));
});
