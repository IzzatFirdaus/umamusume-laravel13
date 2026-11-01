<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The scenario board suite on its own page (`v3.0.0`).
 *
 * The cockpit's inline `Scenario detail` disclosure retires this release and the door of the same name
 * in `Where to go` leads here instead. These cases are the ones that keep the panels reachable after
 * that deletion: the page assembles the same scenario section the disclosure used to render, and the
 * cockpit hands over the URL that reaches it.
 *
 * The third case pins the ADR-0026 trainee-goals flag specifically. `CareerGoalsPanel` has no host
 * other than this suite, so a payload that stopped carrying its flag would silently retire a surface
 * the release did not decide to remove.
 */

it('loads the scenario page with the section the panels read', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);

    $this->get(route('runs.scenario-detail', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/ScenarioDetail')
            ->has('scenario.widgets')
            ->has('scenario.panels')
            ->where('backUrl', route('runs.cockpit', $run)));
});

it('hands the cockpit both doors the retired blocks used to be', function (): void {
    $unityCup = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
    $ura = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    // `scenario_detail_url` is unconditional: every scenario reaches its own boards, including one
    // whose matrix composes no panel, which is what the empty page renders as.
    $this->get(route('runs.cockpit', $unityCup))
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario_detail_url', route('runs.scenario-detail', $unityCup))
            ->where('team_url', route('runs.team', $unityCup)));

    // `team_url` stays null where the scenario records no team, so the card gates on the URL.
    $this->get(route('runs.cockpit', $ura))
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario_detail_url', route('runs.scenario-detail', $ura))
            ->where('team_url', null));
});

it('still feeds the trainee-goals panel the cockpit no longer hosts', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);

    $this->get(route('runs.scenario-detail', $run))
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.panels.career_goals.on', true)
            ->where('scenario.panels.team_rank_ladder.on', true));
});
