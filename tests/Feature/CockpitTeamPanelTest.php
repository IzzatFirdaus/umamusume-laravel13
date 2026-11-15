<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RunStatus;
use App\Enums\TurnEventType;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;
use InvalidArgumentException;
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
     * R6-48: the stored rank letter is the Team panel's source when the run has one.
     *
     * The header strip and the panel read the same resolved value (`TrainingRun::latestTeamRank()`),
     * so this case pins both surfaces against one stored column and one facility level derived from
     * it through the config ladder.
     */
    public function test_reads_the_stored_team_rank_letter_for_the_panel_and_the_header_strip(): void
    {
        $run = $this->unityCupRun(['team_rank' => 'S']);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('scenario.team.rank', 'S')
                ->where('scenario.team.facility_level', 5)
                ->where('header.values.team_rank', 'S'));
    }

    /**
     * One owner, one answer: when both stores hold a letter the stored current state wins over the
     * latest reported turn event (owner ruling 2026-11-15, R6-48), so the strip and the panel cannot
     * disagree about one rank.
     */
    public function test_reads_the_stored_rank_over_an_older_turn_event(): void
    {
        $run = $this->unityCupRun(['team_rank' => 'S']);

        $run->turnEvents()->create([
            'turn' => 3,
            'event_type' => TurnEventType::Scenario,
            'source_name' => 'Unity Training',
            'deltas' => ['rank' => 'A'],
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('scenario.team.rank', 'S')
                ->where('header.values.team_rank', 'S'));
    }

    /**
     * The event-only path stays alive for runs recorded before the column existed (the
     * `UnityCupEditor` turn route still writes it), so a run with no stored letter resolves through
     * the latest reported event.
     */
    public function test_falls_back_to_the_latest_turn_event_when_no_letter_is_stored(): void
    {
        $run = $this->unityCupRun();

        $run->turnEvents()->create([
            'turn' => 3,
            'event_type' => TurnEventType::Scenario,
            'source_name' => 'Unity Training',
            'deltas' => ['rank' => 'A'],
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('scenario.team.rank', 'A')
                ->where('header.values.team_rank', 'A'));
    }

    public function test_reads_the_rank_as_absent_when_neither_store_records_one(): void
    {
        $run = $this->unityCupRun();

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('scenario.team.rank', null)
                ->where('header.values.team_rank', null));
    }

    /**
     * R10 kept intact: `S+` is a valid overall rank (accepted by the column) while
     * `team_rank_ladder` maps it no rung, so the facility level stays null rather than invented.
     */
    public function test_accepts_s_plus_as_the_overall_rank_without_a_facility_rung(): void
    {
        $run = $this->unityCupRun(['team_rank' => 'S+']);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('scenario.team.rank', 'S+')
                ->where('scenario.team.facility_level', null));
    }

    public function test_refuses_a_letter_outside_the_league_vocabulary(): void
    {
        // The vocabulary is validated at the model boundary (owner ruling 2026-11-15, R6-48), so a
        // writer that is not a Form Request still cannot store a letter the client's board has not got.
        expect(fn () => $this->unityCupRun(['team_rank' => 'Z']))
            ->toThrow(InvalidArgumentException::class);
    }

    /**
     * BO-015: the league-placement bonus the Team Info panel's `+30` names.
     *
     * Doc §1.8 reads `Team Ranking Bonus: All Attributes + 30` beside an 8th-place league standing,
     * and §7 finding 11 settles that the figure is the placement ladder's band, not a Team Rank S
     * reading. The ladder is the matrix's own key, so the panel prints the band the stored placement
     * falls in; a placement no band names answers null and the panel states the placement alone.
     */
    public function test_reads_the_league_placement_bonus_from_the_published_ladder(): void
    {
        $cases = [
            8 => 30,   // the run's own standing, the band the client's +30 tooltip reads
            3 => 50,   // the top band (game8.co's "5th or above" reading includes 1st)
            12 => 20,
            18 => 10,
            25 => null, // above the paying bands: game8.jp prints 20〜30位 ボーナス無し
        ];

        foreach ($cases as $placement => $expected) {
            $run = $this->unityCupRun(['team_league_placement' => $placement]);

            $this->get(route('runs.cockpit', $run))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->where('scenario.team.league_bonus', $expected));
        }
    }

    public function test_reads_the_league_bonus_as_absent_when_no_placement_is_recorded(): void
    {
        $run = $this->unityCupRun();

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('scenario.team.league_bonus', null));
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
