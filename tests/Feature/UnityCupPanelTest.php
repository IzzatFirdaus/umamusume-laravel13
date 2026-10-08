<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Enums\SpiritBurstState;
use App\Enums\TurnEventType;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\TrainingRun;
use App\Models\TurnEvent;
use App\Models\TurnEvents\SpiritBurstPayload;
use App\Models\TurnEvents\TeamRankPayload;
use App\Models\Umamusume;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * SCREEN-015, the Unity Cup panel — the Team Cockpit (plan §9.1, §8.3's run-informed grounding).
 *
 * The `team` section of the scenario payload is what this slice names into the E1 contract: the
 * recorded team facts (rank letter, burst states, team race entries) and the config facts (ladder,
 * bands, Special Training), with a key set that is the same for every scenario so one renderer draws
 * all of them. The rendered Team Cockpit itself is asserted in the browser spec; this file proves the
 * wire: full team, partial team, no team data, and the non-team scenarios.
 *
 * Every recorded figure here rides the existing write paths' own payload shapes
 * (`TurnEvents\TeamRankPayload`, `TurnEvents\SpiritBurstPayload`, a `race_entries` row against a
 * `team_race` scenario slot) — the panel invents no new storage.
 */

function unityCupRun(array $state = []): TrainingRun
{
    return TrainingRun::factory()->create([
        'scenario' => 'unity_cup',
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->create()->id,
        ...$state,
    ]);
}

function unitySlot(string $title, int $order): ScenarioSlot
{
    return ScenarioSlot::factory()->create([
        'scenario_key' => 'unity_cup',
        'kind' => 'team_race',
        'title' => $title,
        'tier' => 'G1',
        'month' => ($order % 12) + 1,
        'half' => $order % 2 === 0 ? 'Early' : 'Late',
        'sort_order' => $order,
    ]);
}

function unityRecorded(TrainingRun $run, int $turn, array $deltas): TurnEvent
{
    return TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => $turn,
        'event_type' => TurnEventType::Scenario,
        'source_name' => 'Unity Training',
        'deltas' => $deltas,
    ]);
}

function unityTeamRanks(): Collection
{
    // The ladder flattened the way the gauge consumes it: one rung per letter, config's own order.
    return collect(config('scenarios.scenarios.unity_cup.team_rank_ladder'))
        ->flatMap(fn (array $rung): array => collect($rung['ranks'])
            ->map(fn (string $letter): array => ['rank' => $letter, 'level' => $rung['level']])
            ->all())
        ->values();
}

it('carries the recorded team facts for a run that has them all', function (): void {
    $run = unityCupRun();
    unityRecorded($run, 4, TeamRankPayload::make('S')->toArray());
    unityRecorded($run, 5, SpiritBurstPayload::make('Haru Urara', SpiritBurstState::Charged)->toArray());
    unityRecorded($run, 6, SpiritBurstPayload::make('Matikanetannhauser', SpiritBurstState::ExtremeSpent)->toArray());

    RaceEntry::create([
        'training_run_id' => $run->id,
        'scenario_slot_id' => unitySlot('Team Race A', 1)->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
        'circles' => 2,
    ]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.team.rank', 'S')
            // The facility level is the config mapping of the letter, not a stored figure.
            ->where('scenario.team.facility_level', 5)
            ->where('scenario.team.ladder', fn (Collection $ladder): bool => $ladder->all() === unityTeamRanks()->all())
            ->where('scenario.team.roster', fn (Collection $roster): bool => $roster->all() === [
                ['teammate' => 'Haru Urara', 'state' => 'Charged', 'stateLabel' => 'Charged'],
                ['teammate' => 'Matikanetannhauser', 'state' => 'ExtremeSpent', 'stateLabel' => 'Extreme spent'],
            ])
            ->where('scenario.team.race_guidance', 3)
            ->where('scenario.team.race_entries', fn (Collection $entries): bool => $entries->all() === [
                ['title' => 'Team Race A', 'tier' => 'G1', 'circles' => 2, 'placement' => 1],
            ])
            // The recorded letter fills the widget reading too, so the header strip, the panel
            // meter and the gauge cannot disagree about one rank.
            ->where('scenario.widget_values.team_rank', 'S')
            ->where('header.values.team_rank', 'S')
            // The five team stat grades have no writer, so each is a named absence, never a zero.
            ->where('scenario.team.stat_grades', fn (Collection $grades): bool => $grades->pluck('grade')->every(fn ($grade): bool => $grade === null)
                && $grades->pluck('label')->all() === config('scenarios.stat_order')));
});

it('carries the configured burst bands, their payout timing and the Special Training facts', function (): void {
    $run = unityCupRun();

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // Transcribed from docs/scenarios/02-unity-cup.md:213-220, post-rework corrected values.
            ->where('scenario.team.bands', fn (Collection $bands): bool => $bands->count() === 4
                && $bands->first()['total'] === '4–6'
                && str_contains($bands->last()['reward'], 'Gold hint Lv3'))
            ->where('scenario.team.payout_timing', 'Senior Year, Late November')
            // team_training_energy_penalty => false is the 2026-07-01 rework's removal, stated as
            // the fact it is; wit_burst_energy_bonus => 5 rides verbatim.
            ->where('scenario.team.special_training.energy_penalty_removed', true)
            ->where('scenario.team.special_training.wit_burst_energy_bonus', 5));
});

it('reads N/A-shaped empties for a Unity Cup run that has recorded nothing', function (): void {
    $run = unityCupRun();

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.team.rank', null)
            ->where('scenario.team.facility_level', null)
            ->where('scenario.team.roster', [])
            ->where('scenario.team.race_entries', [])
            ->where('scenario.widget_values.team_rank', null)
            ->where('header.values.team_rank', null)
            // The ladder and the bands are config facts, not run facts: they render for a run
            // that has recorded nothing, which is what makes them reference rather than state.
            ->where('scenario.team.ladder', fn (Collection $ladder): bool => $ladder->count() === 8)
            ->where('scenario.team.bands', fn (Collection $bands): bool => $bands->count() === 4));
});

it('keeps a rank without a burst roster and a roster without races as two partial states', function (): void {
    $rankOnly = unityCupRun();
    unityRecorded($rankOnly, 2, TeamRankPayload::make('A')->toArray());

    $this->get(route('runs.cockpit', $rankOnly))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.team.rank', 'A')
            ->where('scenario.team.facility_level', 4)
            ->where('scenario.team.roster', [])
            ->where('scenario.team.race_entries', []));

    $rosterOnly = unityCupRun();
    unityRecorded($rosterOnly, 3, SpiritBurstPayload::make('Biko Pegasus', SpiritBurstState::Held)->toArray());

    $this->get(route('runs.cockpit', $rosterOnly))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.team.rank', null)
            ->where('scenario.team.roster', fn (Collection $roster): bool => $roster->count() === 1
                && $roster->first()['stateLabel'] === 'Charged, held')
            ->where('scenario.widget_values.team_rank', null));
});

it('carries the same team shape, empty, for the scenarios that compose no team system', function (): void {
    foreach (['ura_finale', 'trackblazer', 'our_grand_concert'] as $key) {
        $run = TrainingRun::factory()->create([
            'scenario' => $key,
            'status' => RunStatus::Active,
            'umamusume_id' => Umamusume::factory()->create()->id,
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('scenario.team.rank', null)
                ->where('scenario.team.ladder', [])
                ->where('scenario.team.roster', [])
                ->where('scenario.team.race_guidance', null)
                ->where('scenario.team.race_entries', [])
                ->where('scenario.team.bands', [])
                ->where('scenario.team.payout_timing', null)
                ->where('scenario.team.special_training.energy_penalty_removed', null)
                ->where('scenario.team.special_training.wit_burst_energy_bonus', null));
    }
});

it('reads no team system into a run that names no scenario, although the keys resolve through the baseline', function (): void {
    // `scenarioKey()` falls back to the baseline entry when a run names no scenario, so every read
    // in `teamSection()` has to be gated by a flag or membership test rather than by the lookup.
    // This pins that the fallback yields the empty shape, rank included, even with a rank-shaped
    // event on the run: a payload the scenario does not compose is not a reading.
    $run = TrainingRun::factory()->create([
        'scenario' => null,
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->create()->id,
    ]);

    unityRecorded($run, 1, TeamRankPayload::make('S')->toArray());

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.declared', false)
            ->where('scenario.team.rank', null)
            ->where('scenario.team.facility_level', null)
            ->where('scenario.team.ladder', [])
            ->where('scenario.team.roster', [])
            ->where('scenario.team.race_guidance', null)
            ->where('scenario.team.bands', [])
            ->where('scenario.team.payout_timing', null)
            // A run that names no scenario composes no scenario widget, so the widget map is empty
            // rather than carrying a null rank: there is no labelled key to read a value under.
            ->where('scenario.widget_values', [])
            ->where('header.values.team_rank', null));
});

it('keeps the team section out of a fifth scenario unless its config entry asks for the systems', function (): void {
    config()->set('scenarios.scenarios.fifth_scenario', [
        'label' => 'A Fifth Scenario',
        'live_on_global' => '2027-01-01',
        'cap_bonus' => ['Speed' => 0, 'Stamina' => 0, 'Power' => 0, 'Guts' => 0, 'Wit' => 0],
        'widgets' => ['turn', 'energy', 'fans'],
        'steps' => ['training', 'outcome', 'skill'],
        'panels' => [
            'race_calendar' => true, 'team_race' => false, 'grade_objectives' => false,
            'shop' => false, 'epithet_routes' => false, 'team_rank_ladder' => false,
        ],
        'scenario_links' => [],
        'facility_level_source' => 'repetition',
    ]);

    $run = TrainingRun::factory()->create([
        'scenario' => 'fifth_scenario',
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->create()->id,
    ]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.team.rank', null)
            ->where('scenario.team.race_guidance', null)
            ->where('scenario.team.bands', []));
});
