<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * SCREEN-011, the Race Decision (plan §8 D10). The props are asserted here; the rendered copy, the
 * drawer's keyboard behaviour, the 44px sweep and the axe scan live in
 * `tests/browser/career-race-decision.spec.ts` (plan §2 convention 2).
 *
 * The load-bearing cases are the absences. Four of the brief's ten race fields have no column behind
 * them, and the readiness band the correction row asks for is held on ADR-0016, so the tests below
 * pin that each one arrives as `N/A` with the reason attached rather than as a default — and that no
 * percentage exists anywhere in the slice.
 */

/**
 * A run with `$turns` logged turns, so `nextTurnToPlay()` lands on the turn after the last one.
 *
 * The career grid is 24 turns a year, two a month: turn 13 is Junior Year, Early July.
 */
function raceRun(int $turns = 0, ?string $scenario = 'ura_finale'): TrainingRun
{
    $run = TrainingRun::factory()->create([
        'scenario' => $scenario,
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->state(['name' => 'Rice Shower', 'name_ja' => 'ライスシャワー']),
    ]);

    for ($turn = 1; $turn <= $turns; $turn++) {
        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => $turn]);
    }

    return $run;
}

/**
 * One row of the shared career catalogue.
 *
 * `scenario_key` null means every scenario, which is what the seeded catalogue does for all but the
 * four scenario finals. `$state` is written on the left of the union so a caller's value wins: the
 * other order silently ignores every override, and the unique grain on
 * (scenario_key, year, month, half, title) then refuses the second row.
 */
function raceSlot(array $state = []): RaceCatalogSlot
{
    return RaceCatalogSlot::factory()->create($state + [
        'scenario_key' => null,
        'year' => RaceCatalogSlot::YEAR_JUNIOR,
        'month' => 7,
        'half' => 'Early',
        'turn' => 13,
        'title' => 'Chukyo Junior Stakes',
        'tier' => 'OP',
        'distance' => 1200,
        'distance_band' => 'Sprint',
        'surface' => 'Turf',
        'fans_gain_curve' => 12,
        'sort_order' => 1,
    ]);
}

it('renders the races at the turn being decided, with every brief field present or named absent', function (): void {
    $run = raceRun(turns: 12);
    raceSlot(['title' => 'Chukyo Junior Stakes', 'sort_order' => 1]);
    raceSlot(['title' => 'Hakodate Junior Stakes', 'tier' => null, 'distance_band' => null, 'surface' => null, 'sort_order' => 2]);

    $this->get(route('runs.races.decision', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/RaceDecision')
            ->where('run.id', $run->id)
            ->where('run.trainee', 'Rice Shower')
            ->where('run.trainee_ja', 'ライスシャワー')
            ->where('run.scenario_label', 'URA Finale')
            ->where('run.run_url', route('runs.cockpit', $run))
            // Turn 13 on the client's 24-turn grid: Junior Year, Early July.
            ->where('nextTurn.year_label', 'Junior Year')
            ->where('nextTurn.turn', 13)
            ->where('nextTurn.position_label', 'Early July')
            ->has('races', 2)
            ->where('races.0.title', 'Chukyo Junior Stakes')
            ->where('races.1.title', 'Hakodate Junior Stakes')
            ->where('races.0.tier', 'OP')
            // Nothing has been recorded for either slot yet.
            ->where('races.0.status', null)
            ->where('races.0.placement', null)
            ->where('races.0.fans_gain', null)
            ->where('races.0.grade_points', null)
            // The empty-state sentence is null when there is a race to decide about.
            ->where('empty', null)
            // The one write reuses the existing route; no second race-write route exists.
            ->where('entry.action', route('runs.races.store', $run))
            // And the door to the planner, so that screen is not reachable by URL alone.
            ->where('planner_url', route('runs.races.planner', $run)));
});

it('states the fields the corpus holds and names the reason for each one it does not', function (): void {
    $run = raceRun(turns: 12);
    raceSlot();

    $this->get(route('runs.races.decision', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('races.0.facts', 10)
            // Present, straight off the catalogue row.
            ->where('races.0.facts.0.key', 'grade')
            ->where('races.0.facts.0.value', 'OP')
            ->where('races.0.facts.0.title', null)
            ->where('races.0.facts.1.key', 'distance')
            ->where('races.0.facts.1.value', '1,200 m')
            ->where('races.0.facts.2.key', 'distance_band')
            ->where('races.0.facts.2.value', 'Sprint')
            // The band carries its own note: the corpus disagrees with itself at 1400 m.
            ->where('races.0.facts.2.title', fn (string $title): bool => str_contains($title, '1400'))
            ->where('races.0.facts.3.key', 'surface')
            ->where('races.0.facts.3.value', 'Turf')
            // Absent, each with the reason it is absent rather than a default or a dash.
            ->where('races.0.facts.4.key', 'running_style')
            ->where('races.0.facts.4.value', null)
            ->where('races.0.facts.4.title', fn (string $title): bool => str_contains($title, 'aptitude'))
            ->where('races.0.facts.5.key', 'fan_gain')
            ->where('races.0.facts.5.value', null)
            ->where('races.0.facts.5.title', fn (string $title): bool => str_contains($title, 'payout curve'))
            ->where('races.0.facts.6.key', 'reward')
            ->where('races.0.facts.6.value', null)
            ->where('races.0.facts.7.key', 'skill_points')
            ->where('races.0.facts.7.value', null)
            ->where('races.0.facts.8.key', 'scenario_reward')
            ->where('races.0.facts.8.value', null)
            ->where('races.0.facts.9.key', 'win_probability')
            ->where('races.0.facts.9.value', null));
});

it('refuses to state a readiness or a win figure, and names the ADR-0016 blocker', function (): void {
    $run = raceRun(turns: 12);
    raceSlot();

    $this->get(route('runs.races.decision', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('readiness.label', 'N/A')
            ->where('readiness.title', fn (string $title): bool => str_contains($title, 'ADR-0016')
                && str_contains($title, '6.11'))
            // The brief's three thresholds are percentages and the risk indicator is a percentage
            // reading; neither ships. The whole slice carries no percent sign.
            ->where('readiness.title', fn (string $title): bool => ! str_contains($title, '%')));
});

it('lists the mandatory races as career obligations, marked as mandatory and never as goals', function (): void {
    $run = raceRun(turns: 12);
    raceSlot(['title' => 'Junior Make Debut', 'turn' => 11, 'month' => 6, 'is_mandatory' => true, 'sort_order' => 0]);
    raceSlot(['title' => 'Junior Maiden Race', 'turn' => 12, 'month' => 6, 'half' => 'Late', 'is_mandatory' => false, 'sort_order' => 1]);
    raceSlot(['title' => 'URA Finale', 'turn' => 24, 'month' => 12, 'half' => 'Late', 'is_mandatory' => true, 'year' => RaceCatalogSlot::YEAR_CLASSIC, 'sort_order' => 2]);

    $this->get(route('runs.races.decision', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // Two of the three rows are mandatory; the maiden race is not one.
            ->has('deadlines', 2)
            ->where('deadlines.0.title', 'Junior Make Debut')
            ->where('deadlines.0.year_label', 'Junior')
            ->where('deadlines.0.turn', 11)
            ->where('deadlines.0.state', 'Not recorded yet')
            // The first obligation the run has not cleared is the one in front of it, and only it.
            ->where('deadlines.0.is_next', true)
            ->where('deadlines.0.reached', true)
            ->where('deadlines.1.title', 'URA Finale')
            ->where('deadlines.1.is_next', false)
            // Turn 24 of Classic has not been reached from turn 13 of Junior.
            ->where('deadlines.1.reached', false));
});

it('reports a cleared obligation as cleared and moves the marker to the next one', function (): void {
    $run = raceRun(turns: 12);
    $debut = raceSlot(['title' => 'Junior Make Debut', 'turn' => 11, 'month' => 6, 'is_mandatory' => true, 'sort_order' => 0]);
    raceSlot(['title' => 'URA Finale', 'turn' => 24, 'month' => 12, 'half' => 'Late', 'is_mandatory' => true, 'year' => RaceCatalogSlot::YEAR_CLASSIC, 'sort_order' => 1]);

    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $debut->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
    ]);

    $this->get(route('runs.races.decision', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('deadlines.0.state', 'Cleared')
            ->where('deadlines.0.is_next', false)
            ->where('deadlines.1.is_next', true));
});

it('carries what the Trainer already recorded for a slot it is offering again', function (): void {
    $run = raceRun(turns: 12);
    $slot = raceSlot();

    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Entered,
        'placement' => 2,
        'fans_gain' => 1500,
    ]);

    $this->get(route('runs.races.decision', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('races.0.status', 'Entered')
            ->where('races.0.placement', '2nd')
            ->where('races.0.fans_gain', 1500));
});

it('names why there is no decision to make, in each of the three ways there is none', function (): void {
    // A run that names no scenario has no calendar at all.
    $bare = raceRun(turns: 12, scenario: null);
    raceSlot();

    $this->get(route('runs.races.decision', $bare))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('run.scenario_label', 'No scenario set')
            ->where('races', [])
            ->where('deadlines', [])
            ->where('empty', fn (string $empty): bool => str_contains($empty, 'names no scenario')));

    // A run with nothing logged has no turn to decide about.
    $fresh = raceRun(turns: 0);

    $this->get(route('runs.races.decision', $fresh))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('nextTurn', null)
            ->where('races', [])
            ->where('empty', fn (string $empty): bool => str_contains($empty, 'No turn has been logged')));

    // A turn the calendar carries no race on.
    $quiet = raceRun(turns: 2);

    $this->get(route('runs.races.decision', $quiet))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('nextTurn.turn', 3)
            ->where('races', [])
            ->where('empty', fn (string $empty): bool => str_contains($empty, 'No race on this scenario')));
});

it('renders no win figure and no percentage anywhere in the slice', function (): void {
    // A percentage is the shape a win estimate takes, and the brief's three risk thresholds
    // (`< 10` / `10-30` / `> 30`) are percentages too. Neither is built (ADR-0016), so the rendered
    // surface carries no percent sign at all.
    $surfaces = [
        resource_path('js/pages/Career/RaceDecision.vue'),
        resource_path('js/components/career/RaceCard.vue'),
    ];

    foreach ($surfaces as $file) {
        expect(file_get_contents($file), $file)->not->toContain('%');
    }

    // And the props carry none. The controller's own source is not grepped for the character: it
    // holds a modulo in the month arithmetic and a docblock quoting the brief's thresholds, so a
    // file-level check there would fail on arithmetic and citation rather than on copy. What matters
    // is that nothing reaching the page can print one.
    $run = raceRun(turns: 12);
    raceSlot();

    $this->get(route('runs.races.decision', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('readiness.label', 'N/A')
            ->where('readiness.title', fn (string $title): bool => ! str_contains($title, '%'))
            ->where('races.0.facts', fn ($facts): bool => collect($facts)->every(
                static fn (array $fact): bool => ! str_contains((string) $fact['value'], '%')
                    && ! str_contains((string) $fact['title'], '%'),
            )));
});

/*
 * F2, plan §9.6 ruling 3: manual free-race entry lands on the Race Decision. These port the three
 * write behaviours the legacy run screen owned, against `runs.races.store` with `entry_mode=manual`.
 * The form now posts from the decision screen, so each submission carries that referer and returns
 * to it.
 */

it('leaves the turn link null on a manual race the Trainer does not tie to a turn', function (): void {
    $run = raceRun(turns: 12);

    $this->from(route('runs.races.decision', $run))
        ->post(route('runs.races.store', $run), [
            'entry_mode' => 'manual',
            'title' => 'Autumn Practice Stakes',
            'month' => 9,
            'half' => 'Late',
            'status' => RaceEntryStatus::Completed->value,
        ])
        ->assertRedirect(route('runs.races.decision', $run));

    // KI-17: the link is nullable on purpose, so an entry the Trainer does not tie to a turn is a
    // complete row rather than a refused one.
    expect(RaceEntry::where('training_run_id', $run->id)->sole()->turn_entry_id)->toBeNull();
});

it('links a manual race to the turn the Trainer names', function (): void {
    $run = raceRun(turns: 12);
    $turn = TurnEntry::where('training_run_id', $run->id)->where('turn', 3)->sole();

    $this->from(route('runs.races.decision', $run))
        ->post(route('runs.races.store', $run), [
            'entry_mode' => 'manual',
            'title' => 'Autumn Practice Stakes',
            'month' => 9,
            'half' => 'Late',
            'turn_entry_id' => $turn->id,
            'status' => RaceEntryStatus::Completed->value,
        ])
        ->assertRedirect(route('runs.races.decision', $run));

    $entry = RaceEntry::where('training_run_id', $run->id)->sole();

    expect($entry->turn_entry_id)->toBe($turn->id)
        ->and($entry->turnEntry)->not->toBeNull()
        ->and($entry->turnEntry->turn)->toBe(3);
});

it('rejects a turn logged on a different run and writes no row', function (): void {
    $run = raceRun(turns: 12);
    $otherRun = raceRun(turns: 2);
    $foreignTurn = TurnEntry::where('training_run_id', $otherRun->id)->where('turn', 1)->sole();

    $this->from(route('runs.races.decision', $run))
        ->post(route('runs.races.store', $run), [
            'entry_mode' => 'manual',
            'title' => 'Autumn Practice Stakes',
            'month' => 9,
            'half' => 'Late',
            'turn_entry_id' => $foreignTurn->id,
            'status' => RaceEntryStatus::Completed->value,
        ])
        ->assertRedirect(route('runs.races.decision', $run))
        ->assertSessionHasErrors('turn_entry_id');

    // The manual arm writes a slot and an entry together; a refused turn leaves neither.
    expect(RaceEntry::where('training_run_id', $run->id)->count())->toBe(0)
        ->and(ScenarioSlot::where('kind', 'free_race')->count())->toBe(0);
});

it('refuses circles on a manual free race, which is not a team race', function (): void {
    $run = raceRun(turns: 12);

    $this->from(route('runs.races.decision', $run))
        ->post(route('runs.races.store', $run), [
            'entry_mode' => 'manual',
            'title' => 'Autumn Practice Stakes',
            'month' => 9,
            'half' => 'Late',
            'circles' => 2,
            'status' => RaceEntryStatus::Completed->value,
        ])
        ->assertRedirect(route('runs.races.decision', $run))
        ->assertSessionHasErrors('circles');

    expect(RaceEntry::where('training_run_id', $run->id)->count())->toBe(0);
});

it('writes a free_race slot and its race entry for a manual race', function (): void {
    $run = raceRun(turns: 12);

    $this->from(route('runs.races.decision', $run))
        ->post(route('runs.races.store', $run), [
            'entry_mode' => 'manual',
            'title' => 'Autumn Practice Stakes',
            'month' => 9,
            'half' => 'Late',
            'tier' => 'OP',
            'status' => RaceEntryStatus::Completed->value,
        ])
        ->assertRedirect(route('runs.races.decision', $run));

    $slot = ScenarioSlot::where('scenario_key', 'ura_finale')->where('kind', 'free_race')->sole();

    expect($slot->title)->toBe('Autumn Practice Stakes')
        ->and($slot->slot_label)->toBe('Autumn Practice Stakes')
        ->and($slot->month)->toBe(9)
        ->and($slot->half)->toBe('Late')
        ->and($slot->tier)->toBe('OP')
        ->and($slot->is_manual)->toBeTrue();

    $entry = RaceEntry::where('training_run_id', $run->id)->sole();

    expect($entry->scenario_slot_id)->toBe($slot->id)
        ->and($entry->scenarioSlot->kind)->toBe('free_race');

    // The slot the write just made reads back on the manual arm of the Race Decision.
    $this->get(route('runs.races.decision', ['run' => $run, 'entry_mode' => 'manual']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('entry_mode', 'manual')
            ->where('manual_slots', [['id' => $slot->id, 'title' => 'Autumn Practice Stakes']]));
});

/*
 * R67/R71, ported from the retired `RaceEntryDisclosureTest`: the two-path race form is
 * server-driven disclosure, exactly as guided-step does it. Which half is open is server state,
 * resolved into `entry_mode` (the query wins over the 'calendar' default), so the reachable branch
 * is asserted from the page payload rather than from a template's inert markup. The switch is a
 * navigation, so the mode arrives as the query parameter a Trainer's click sends.
 */

it('renders the calendar branch reachable, with the hand-entered races out of the branch', function (): void {
    $run = raceRun(turns: 12);
    raceSlot(['title' => 'Japanese Oaks']);
    ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'free_race',
        'title' => 'Autumn Practice Stakes',
        'slot_label' => 'Autumn Practice Stakes',
        'month' => 9,
        'half' => 'Late',
        'is_manual' => true,
    ]);

    $this->get(route('runs.races.decision', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/RaceDecision')
            // The branch the page resolved, not a literal a template could leave inert.
            ->where('entry_mode', 'calendar')
            // The calendar branch names the catalogue race at the turn being decided...
            ->has('races', 1)
            ->where('races.0.title', 'Japanese Oaks')
            // ...and a race the Trainer typed earlier keeps its own list, out of this branch.
            ->has('manual_slots', 1)
            ->where('manual_slots.0.title', 'Autumn Practice Stakes'));
});

it('renders the manual branch reachable when the disclosure switches the mode', function (): void {
    $run = raceRun(turns: 12);

    $this->get(route('runs.races.decision', ['run' => $run, 'entry_mode' => 'manual']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/RaceDecision')
            ->where('entry_mode', 'manual'));
});

it('rejects a failed manual entry and stores nothing', function (): void {
    $run = raceRun(turns: 12);

    $this->from(route('runs.races.decision', $run))
        ->post(route('runs.races.store', $run), [
            'entry_mode' => 'manual',
            'title' => 'Midsummer Practice Race',
            'month' => 13,
            'half' => 'Early',
            'status' => RaceEntryStatus::Completed->value,
        ])
        ->assertRedirect(route('runs.races.decision', $run))
        ->assertSessionHasErrors('month');

    expect(RaceEntry::where('training_run_id', $run->id)->count())->toBe(0)
        ->and(ScenarioSlot::where('kind', 'free_race')->count())->toBe(0);
});

it('writes a free race through the rendered manual form, not a direct post', function (): void {
    $run = raceRun(turns: 12);

    $page = $this->get(route('runs.races.decision', ['run' => $run, 'entry_mode' => 'manual']))->assertOk();

    $page->assertInertia(fn (Assert $assert) => $assert
        ->component('Career/RaceDecision')
        ->where('entry_mode', 'manual')
        // The action the form itself posts to, read out of the page payload rather than assumed.
        ->where('entry.action', route('runs.races.store', $run)));

    $this->from(route('runs.races.decision', $run))
        ->post($page->inertiaProps('entry.action'), [
            // The mode the page served, not a literal a template could leave inert.
            'entry_mode' => $page->inertiaProps('entry_mode'),
            'title' => 'Autumn Practice Stakes',
            'month' => 9,
            'half' => 'Late',
            'status' => RaceEntryStatus::Completed->value,
            'placement' => 3,
        ])
        ->assertRedirect(route('runs.races.decision', $run));

    $slot = ScenarioSlot::where('kind', 'free_race')->where('title', 'Autumn Practice Stakes')->first();

    expect($slot)->not->toBeNull()
        ->and($slot->is_manual)->toBeTrue()
        ->and($slot->month)->toBe(9)
        ->and($slot->half)->toBe('Late')
        ->and($slot->tier)->toBeNull();

    // The row the form just wrote is readable back out of the manual arm's own list.
    $this->get(route('runs.races.decision', ['run' => $run, 'entry_mode' => 'manual']))
        ->assertOk()
        ->assertInertia(fn (Assert $assert) => $assert
            ->where('manual_slots', [['id' => $slot->id, 'title' => 'Autumn Practice Stakes']]));
});
