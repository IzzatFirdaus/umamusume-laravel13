<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * The team's own identity on the Cockpit's Team panel (A6.6 render): name, motto, league placement and
 * preseason rounds won, as the Trainer entered them.
 *
 * The rendered copy is asserted in the browser spec; this file proves the wire the renderer reads. The
 * four ride the scenario's own `team` payload, where the matrix already gates the team system to Unity
 * Cup. Each is null until a writer records it, so the panel draws the card only when one of the four is
 * set and names the absence otherwise (D-220).
 *
 * Class-based with private helpers, matching `RunGrowthRateTest`: a top-level function in
 * `tests/Feature` shares one namespace, and a duplicate name fatals the whole suite at load time.
 */
final class CockpitTeamPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_reads_the_team_identity_when_recorded(): void
    {
        $run = $this->unityCupRun([
            'team_name' => 'Blue Bloom',
            'team_motto' => 'Dreaming Big',
            'team_league_placement' => 8,
            'team_preseason_wins' => 4,
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('scenario.team.team_name', 'Blue Bloom')
                ->where('scenario.team.team_motto', 'Dreaming Big')
                ->where('scenario.team.team_league_placement', 8)
                ->where('scenario.team.team_preseason_wins', 4));
    }

    public function test_reads_the_team_identity_as_absent_when_not_recorded(): void
    {
        $run = $this->unityCupRun();

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('scenario.team.team_name', null)
                ->where('scenario.team.team_motto', null)
                ->where('scenario.team.team_league_placement', null)
                ->where('scenario.team.team_preseason_wins', null));
    }

    public function test_reads_a_partial_team_identity_without_inventing_the_missing_fields(): void
    {
        // One entered field draws the card; the three unstated ones stay null rather than defaulting to
        // zero, so the panel prints only what the Trainer said.
        $run = $this->unityCupRun(['team_name' => 'Blue Bloom']);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('scenario.team.team_name', 'Blue Bloom')
                ->where('scenario.team.team_motto', null)
                ->where('scenario.team.team_league_placement', null)
                ->where('scenario.team.team_preseason_wins', null));
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function unityCupRun(array $state = []): TrainingRun
    {
        return TrainingRun::factory()->create([
            'scenario' => 'unity_cup',
            'status' => RunStatus::Active,
            'umamusume_id' => Umamusume::factory()->create()->id,
            ...$state,
        ]);
    }
}
