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

it('sources the Spirit Bursts widget from the run\'s recorded tallies (KI-154)', function (): void {
    // KI-154: `ScenarioSectionBuilder` wrote `null` for every widget but `team_rank`, so the tile
    // read `N/A` on a run that recorded both tallies while the bands panel below it placed the
    // same figure in a tier. The widget now reads the combined total the bands panel already owns.
    $run = unityCupRun(['spirit_bursts_count' => 6, 'extreme_bursts_count' => 5]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.widget_values.spirit_bursts', 11)
            ->where('scenario.team.combined_bursts', 11)
            // The bands panel places the same figure in a tier, so the two surfaces agree.
            ->where('scenario.team.burst_band', '10–12'));
});

it('keeps the Spirit Bursts widget absent when only one tally is recorded', function (): void {
    // D-220: an unrecorded half is not a zero and not a partial count. The combined figure is null
    // unless both tallies exist, and the widget inherits that rule rather than re-deriving it.
    $run = unityCupRun(['spirit_bursts_count' => 6, 'extreme_bursts_count' => null]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.widget_values.spirit_bursts', null)
            ->where('scenario.team.combined_bursts', null));
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

it('attributes the +30 to league placement, not to the team rank (KI-155)', function (): void {
    // KI-155. The panel's provenance note used to say the bonus "binds to the rank rather than the
    // league standing", "inferred from screen position". `docs/research/flows-career-workspace.md:232`
    // refutes that: the client's `Team Ranking Bonus: All Attributes +30` is the LEAGUE-PLACEMENT
    // bonus (2nd-5th +50, 6th-10th +30, 11th-15th +20, 16th-20th +10), and the recorded run's 8th
    // place is the +30 band.
    //
    // Asserted as source text because the line is not browser-reachable: it renders only when the
    // team rank is `S`, and no trainer form writes a team rank (R6-48), so a browser case cannot put
    // it on screen. `scenario-panel.spec.ts:329` asserts the other half — that it is absent where no
    // rank was recorded.
    $source = (string) file_get_contents(base_path('resources/js/components/scenario/ScenarioCardGrid.vue'));

    expect($source)
        ->toContain('league-placement bonus, not a reading of the rank letter')
        ->toContain('6th-10th +30')
        ->toContain('docs/research/flows-career-workspace.md:232')
        // The refuted claim must not come back.
        ->not->toContain('binds to the rank')
        ->not->toContain('inferred from screen position');
});

it('cites edges-versus-criteria for the burst-band conflict, not a Skill Point disagreement (KI-156)', function (): void {
    // KI-156. The caveat used to say "two other publishers report 15/15/20/20 with no stat component",
    // and the visible line listed "the Skill Point figures" among the conflicts. Neither is what the
    // reference corpus records: `UMAMUSUME_REFERENCE.md` conflict row 57 says GameTora prints four
    // numbered bands (4 to 9, 7 to 9, 10 to 12, 13+) and the Wiki prints the same four reward tiers
    // with a *criterion* on the gold rows instead of numbered edges. The disagreement is edges versus
    // criteria; the SP figures are the same on both. GameTora's own first two bands also overlap.
    $source = (string) file_get_contents(base_path('resources/js/components/scenario/cards/BurstBandsCard.vue'));

    expect($source)
        ->toContain('edges versus criteria, not about the Skill Point figures')
        ->toContain("where the low bands' edges fall")
        ->toContain('overlap and cannot both hold')
        // The unsourced publisher figure must not come back.
        ->not->toContain('15/15/20/20');
});


it('carries the labelled manual Spirit proxy and keeps its absence distinct from zero (KI-133)', function (): void {
    // KI-133, ruled 2026-11-10: the gauge is per team member with no published maximum, so this
    // column stores ONE Trainer-entered aggregate for the whole team and the display labels it
    // a manual proxy. The two assertions are the contract: a stated figure rides the team payload
    // unchanged, and nothing entered is null on the payload (never a zero, D-220).
    $stated = unityCupRun(['current_spirit_proxy' => 33]);

    $this->get(route('runs.cockpit', $stated))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.team.current_spirit_proxy', 33));

    $blank = unityCupRun();

    $this->get(route('runs.cockpit', $blank))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.team.current_spirit_proxy', null));

    // The write path round-trips through the run's own update route: entering the figure via the
    // team editor stores it, and clearing it stores null rather than zero.
    $this->put(route('runs.update', $stated), [
        'umamusume_id' => $stated->umamusume_id,
        'scenario' => $stated->scenario,
        'status' => $stated->status->value,
        'current_spirit_proxy' => 40,
    ])->assertRedirect();

    expect($stated->fresh()->current_spirit_proxy)->toBe(40);

    $this->put(route('runs.update', $stated), [
        'umamusume_id' => $stated->umamusume_id,
        'scenario' => $stated->scenario,
        'status' => $stated->status->value,
        'current_spirit_proxy' => null,
    ])->assertRedirect();

    expect($stated->fresh()->current_spirit_proxy)->toBeNull();
});

it('lists only members with a recorded burst event, and never synthesizes one as Chargeable', function (): void {
    // KI-133 Option A's honesty boundary. `spiritBurstRoster()` is event-derived: it answers "which
    // members have a recorded burst state", not "who is on the team". A teammate the Trainer has never
    // recorded must therefore be ABSENT from the roster rather than defaulted into `Chargeable`, which
    // would assert a state nobody entered (D-220's rule, applied to a member instead of a meter).
    $run = unityCupRun();

    // Two members are recorded. A third, "Silence Suzuka", is named nowhere: she is the probe.
    unityRecorded($run, 1, SpiritBurstPayload::make('Haru Urara', SpiritBurstState::Chargeable)->toArray());
    unityRecorded($run, 2, SpiritBurstPayload::make('Biko Pegasus', SpiritBurstState::ExtremeSpent)->toArray());

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.team.roster', fn (Collection $roster): bool => $roster->count() === 2)
            ->where('scenario.team.roster', fn (Collection $roster): bool => $roster->pluck('teammate')->all() === [
                'Haru Urara',
                'Biko Pegasus',
            ])
            ->where('scenario.team.roster', fn (Collection $roster): bool => $roster
                ->pluck('teammate')
                ->doesntContain('Silence Suzuka'))
            // The recorded `Chargeable` is present because a Trainer entered it, and for no other reason.
            // This is the assertion that separates "the game says chargeable" from "the tool assumed it".
            ->where('scenario.team.roster', fn (Collection $roster): bool => $roster
                ->where('teammate', 'Haru Urara')
                ->first()['state'] === 'Chargeable'));
});

it('keeps one row per teammate and lets the latest state win', function (): void {
    // `spiritBurstRoster()` documents "the latest payload per key wins, because the states replace one
    // another; an earlier row for the same teammate is history, not a second teammate." This pins that:
    // three events for two members produce two rows, and the member who moved ends on the later state.
    $run = unityCupRun();

    unityRecorded($run, 1, SpiritBurstPayload::make('Haru Urara', SpiritBurstState::Chargeable)->toArray());
    unityRecorded($run, 2, SpiritBurstPayload::make('Matikanetannhauser', SpiritBurstState::Held)->toArray());
    unityRecorded($run, 3, SpiritBurstPayload::make('Haru Urara', SpiritBurstState::ExtremeChargeable)->toArray());

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.team.roster', fn (Collection $roster): bool => $roster->count() === 2)
            // Identity order is the roster's own (first appearance by turn), not alphabetical.
            ->where('scenario.team.roster', fn (Collection $roster): bool => $roster->pluck('teammate')->all() === [
                'Haru Urara',
                'Matikanetannhauser',
            ])
            // Haru Urara holds her LATER state, and only one row describes her.
            ->where('scenario.team.roster', fn (Collection $roster): bool => $roster
                ->where('teammate', 'Haru Urara')
                ->first()['state'] === 'ExtremeChargeable')
            ->where('scenario.team.roster', fn (Collection $roster): bool => $roster
                ->where('teammate', 'Haru Urara')
                ->count() === 1)
            // Held survives as its own distinct state rather than collapsing into Charged.
            ->where('scenario.team.roster', fn (Collection $roster): bool => $roster
                ->where('teammate', 'Matikanetannhauser')
                ->first()['state'] === 'Held'));
});

it('keeps the whole-team manual proxy independent of every per-member burst state', function (): void {
    // KI-133's central constraint: `current_spirit_proxy` is ONE Trainer-entered aggregate for the whole
    // team, and the roster is per-member event-derived state. Neither may be read as the other. This
    // drives both at once and asserts they do not move together.
    $run = unityCupRun(['current_spirit_proxy' => 40]);

    unityRecorded($run, 1, SpiritBurstPayload::make('Haru Urara', SpiritBurstState::Held)->toArray());

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // Both readings travel, and they are different facts.
            ->where('scenario.team.current_spirit_proxy', 40)
            ->where('scenario.team.roster', fn (Collection $roster): bool => $roster->count() === 1)
            ->where('scenario.team.roster', fn (Collection $roster): bool => $roster
                ->first()['state'] === 'Held')
            // The proxy is not a gauge reading and must never be published as a burst state or a member.
            ->where('scenario.team.roster', fn (Collection $roster): bool => $roster
                ->pluck('state')
                ->doesntContain('40'))
            ->where('scenario.team.roster', fn (Collection $roster): bool => $roster
                ->pluck('teammate')
                ->doesntContain('Current Spirit')));

    // Clearing the proxy leaves every member's state untouched: the two stores are orthogonal.
    $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'scenario' => $run->scenario,
        'status' => $run->status->value,
        'current_spirit_proxy' => null,
    ])->assertRedirect();

    expect($run->fresh()->current_spirit_proxy)->toBeNull();

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.team.current_spirit_proxy', null)
            ->where('scenario.team.roster', fn (Collection $roster): bool => $roster->count() === 1)
            ->where('scenario.team.roster', fn (Collection $roster): bool => $roster
                ->first()['state'] === 'Held'));
});
