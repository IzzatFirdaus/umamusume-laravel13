<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The team write's own page (`v3.0.0`).
 *
 * The cockpit's inline `Edit team state` disclosure retires this release and `Team setup` in
 * `Where to go` leads here instead. These cases are the ones that keep the write reachable: the page
 * loads for the scenario that records a team, every other scenario is sent back with the reason, and
 * the payload carries the fields the editor binds.
 *
 * The gate is the scenario's own capability flag (`composesPanel('team_rank_ladder')`), not a name, so
 * the second case uses a scenario the config does not flag rather than asserting the string 'unity_cup'.
 */

it('loads the team edit page for a scenario that records a team', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);

    $this->get(route('runs.team', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/TeamEdit')
            ->has('team')
            // The editor posts through the run's own update route, so the page must hand it over.
            ->where('run.update_url', route('runs.update', $run))
            ->where('backUrl', route('runs.cockpit', $run)));
});

it('sends a scenario that records no team back to the cockpit with the reason', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    $this->get(route('runs.team', $run))
        ->assertRedirect(route('runs.cockpit', $run))
        ->assertSessionHas('status', 'Team state is only available for a scenario that records a team.');
});

it('carries every field the editor binds', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);

    $this->get(route('runs.team', $run))
        ->assertInertia(fn (Assert $page) => $page
            ->has('team.team_name')
            ->has('team.team_motto')
            ->has('team.team_league_placement')
            ->has('team.team_preseason_wins')
            ->has('team.unity_trainings_count')
            ->has('team.spirit_bursts_count')
            ->has('team.extreme_bursts_count')
            // The five grade selects read one entry per stat from the scenario's own stat order.
            ->has('team.stat_grades', 5));
});
