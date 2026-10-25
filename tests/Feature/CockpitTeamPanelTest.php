<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
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
     * R2-14: the Cockpit's team editor writes what the panel already printed.
     *
     * The read half is pinned above; these two pin the write half, through the same `runs.update` route
     * and `StoreTrainingRunRequest` the header forms use.
     */
    public function test_records_the_team_identity_through_the_cockpit_and_reads_it_back(): void
    {
        $run = $this->unityCupRun();

        $this->put(route('runs.update', $run), [
            'umamusume_id' => $run->umamusume_id,
            'status' => RunStatus::Active->value,
            'team_name' => 'Blue Bloom',
            'team_motto' => 'Dreaming Big',
            'team_league_placement' => 8,
            'team_preseason_wins' => 4,
        ])->assertRedirect(route('runs.cockpit', $run));

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('scenario.team.team_name', 'Blue Bloom')
                ->where('scenario.team.team_motto', 'Dreaming Big')
                ->where('scenario.team.team_league_placement', 8)
                ->where('scenario.team.team_preseason_wins', 4));
    }

    public function test_clearing_a_team_field_records_it_as_unstated_rather_than_zero(): void
    {
        $run = $this->unityCupRun([
            'team_name' => 'Blue Bloom',
            'team_league_placement' => 8,
        ]);

        // The editor posts `null` for a field the Trainer cleared. Zero is not a league placement the
        // client shows, so the absence has to survive as an absence (D-220).
        $this->put(route('runs.update', $run), [
            'umamusume_id' => $run->umamusume_id,
            'status' => RunStatus::Active->value,
            'team_name' => null,
            'team_league_placement' => null,
        ])->assertSessionHasNoErrors();

        $fresh = $run->fresh();

        expect($fresh->team_name)->toBeNull()
            ->and($fresh->team_league_placement)->toBeNull();
    }

    public function test_stores_per_stat_team_grades_and_rejects_s_plus(): void
    {
        $run = $this->unityCupRun();

        // A fresh run records no grades; the panel receives nulls, not a defaulted letter.
        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('scenario.team.stat_grades', fn (Collection $grades): bool => $grades->every(
                    static fn (array $grade): bool => $grade['grade'] === null,
                )));

        $this->put(route('runs.update', $run), [
            'umamusume_id' => $run->umamusume_id,
            'status' => RunStatus::Active->value,
            'team_stat_grades' => ['Speed' => 'S', 'Stamina' => 'A', 'Power' => 'A', 'Guts' => 'A', 'Wit' => 'A'],
        ])->assertSessionHasNoErrors();

        expect($run->fresh()->team_stat_grades)->toBe([
            'Speed' => 'S', 'Stamina' => 'A', 'Power' => 'A', 'Guts' => 'A', 'Wit' => 'A',
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('scenario.team.stat_grades', fn (Collection $grades): bool => $grades->pluck('grade')->all() === ['S', 'A', 'A', 'A', 'A']));

        // R10: S+ is the overall rank, never a per-stat value.
        $this->put(route('runs.update', $run), [
            'umamusume_id' => $run->umamusume_id,
            'status' => RunStatus::Active->value,
            'team_stat_grades' => ['Speed' => 'S+'],
        ])->assertSessionHasErrors([
            'team_stat_grades.Speed' => 'Per-stat grade must be G-S; S+ is overall team rank only.',
        ]);
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
