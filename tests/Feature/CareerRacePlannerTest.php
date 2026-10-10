<?php

declare(strict_types=1);

use App\Enums\BuildPurpose;
use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * SCREEN-017, the Scenario Race Planner (plan §9 E5, `SCR-CAR-023`). The props are asserted here; the
 * rendered copy, the keyboard path through the groups and the comparison table, the 44px sweep and the
 * axe scan live in `tests/browser/career-race-planner.spec.ts` (plan §2 convention 2).
 *
 * The load-bearing cases are the refusals. The brief asks for a win probability, an expected risk, a
 * recommendation block and the sentence "No critical training deadline will be missed"; all four are
 * held or unsourced, so the tests below pin that each arrives as `N/A` with its reason, or is absent,
 * rather than as a default.
 */

/**
 * A run with `$turns` logged turns, so the planner's position is the turn after the last one.
 *
 * The career grid is 24 turns a year, two a month: turn 13 is Junior Year, Early July.
 */
function plannerRun(int $turns = 0, ?string $scenario = 'ura_finale', ?array $target = null): TrainingRun
{
    $run = TrainingRun::factory()->create([
        'scenario' => $scenario,
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->state(['name' => 'Rice Shower', 'name_ja' => 'ライスシャワー']),
    ]);

    if ($target !== null) {
        $run->forceFill(['build_target' => $target])->save();
    }

    for ($turn = 1; $turn <= $turns; $turn++) {
        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => $turn]);
    }

    return $run->refresh();
}

/**
 * A build target as the wizard records one: exactly the matrix's stats, and the vocabularies
 * `BuildTargetPayload` accepts.
 *
 * @return array<string, mixed>
 */
function plannerTarget(string $band = 'Mile', string $surface = 'Turf', string $style = 'Front Runner'): array
{
    return [
        'purpose' => BuildPurpose::StoryClear->value,
        'distance' => $band,
        'surface' => $surface,
        'style' => $style,
        'targets' => ['Speed' => 600, 'Stamina' => 600, 'Power' => 600, 'Guts' => 600, 'Wit' => 600],
        'skill_priorities' => [],
    ];
}

/**
 * One row of the shared career catalogue.
 *
 * `$state` is written on the left of the union so a caller's value wins: the other order silently
 * ignores every override, and the unique grain on (scenario_key, year, month, half, title) then
 * refuses the second row.
 */
function plannerSlot(array $state = []): RaceCatalogSlot
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

it('partitions the calendar into the four groups the brief names, each with its own rule', function (): void {
    $run = plannerRun(turns: 12);
    plannerSlot(['title' => 'Junior Make Debut', 'turn' => 11, 'month' => 6, 'is_mandatory' => true, 'sort_order' => 0]);
    plannerSlot(['title' => 'Hopeful Stakes', 'turn' => 20, 'month' => 10, 'sort_order' => 1]);
    plannerSlot(['title' => 'Chukyo Junior Stakes', 'turn' => 13, 'sort_order' => 2]);

    $this->get(route('runs.races.planner', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/RacePlanner')
            ->where('run.trainee', 'Rice Shower')
            ->where('run.scenario_label', 'URA Finale')
            ->where('nextTurn.turn', 13)
            ->where('empty', null)
            // The obligation, read from the catalogue rather than from the run's entries, because an
            // obligation not yet reached has no entry and is exactly what the group exists to show.
            ->has('groups.mandatory.races', 1)
            ->where('groups.mandatory.races.0.title', 'Junior Make Debut')
            ->where('groups.mandatory.races.0.is_mandatory', true)
            // Ahead of the run: the turn has not arrived.
            ->has('groups.upcoming.races', 1)
            ->where('groups.upcoming.races.0.title', 'Hopeful Stakes')
            // Reached and unrecorded: the turn has arrived and the run has entered nothing.
            ->has('groups.optional.races', 1)
            ->where('groups.optional.races.0.title', 'Chukyo Junior Stakes')
            // No column marks a rival race, so the group states the absence rather than inventing rows.
            ->has('groups.rival.races', 0)
            ->where('groups.rival.empty', fn (string $empty): bool => str_contains($empty, 'rival')));
});

it('moves a race out of the optional group once the run has recorded an entry for it', function (): void {
    $run = plannerRun(turns: 12);
    $slot = plannerSlot(['title' => 'Chukyo Junior Stakes', 'turn' => 13]);

    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
    ]);

    $this->get(route('runs.races.planner', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('groups.optional.races', 0)
            ->where('groups.optional.empty', fn (string $empty): bool => str_contains($empty, 'recorded')));
});

it('states the reward comparison rows that have a source and names the reason for each that does not', function (): void {
    $run = plannerRun(turns: 12);
    plannerSlot(['title' => 'Chukyo Junior Stakes', 'tier' => 'OP', 'distance' => 1200, 'distance_band' => 'Sprint', 'surface' => 'Turf', 'fans_needed' => 1200, 'sort_order' => 1]);
    plannerSlot(['title' => 'Hopeful Stakes', 'turn' => 20, 'month' => 10, 'tier' => null, 'distance' => null, 'distance_band' => null, 'surface' => null, 'sort_order' => 2]);

    $this->get(route('runs.races.planner', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // One row set, in one order, for every race: that is what makes the columns comparable
            // (design-2.0 §46).
            ->has('comparison.rows', 10)
            ->where('comparison.rows.0.key', 'grade')
            ->where('comparison.rows.4.key', 'fan_gate')
            ->where('comparison.rows.9.key', 'shop_coins')
            ->has('groups.mandatory.races', 0)
            ->has('groups.upcoming.races', 1)
            ->has('groups.optional.races', 1)
            ->has('groups.upcoming.races.0.comparison', 10)
            ->where('groups.upcoming.races.0.comparison.0.value', null)
            ->where('groups.upcoming.races.0.comparison.0.title', fn (string $title): bool => str_contains($title, 'tier'))
            ->where('groups.optional.races.0.comparison.0.value', 'OP')
            ->where('groups.optional.races.0.comparison.0.title', null)
            ->where('groups.optional.races.0.comparison.2.value', 'Sprint')
            // The band carries its own note: the corpus disagrees with itself at 1400 m.
            ->where('groups.optional.races.0.comparison.2.title', fn (?string $title): bool => is_string($title) && str_contains($title, '1400'))
            ->where('groups.optional.races.0.comparison.3.value', 'Turf')
            ->where('groups.optional.races.0.comparison.4.value', '1,200')
            // Absent, each with the reason it is absent rather than a default or a dash.
            ->where('groups.optional.races.0.comparison.5.value', null)
            ->where('groups.optional.races.0.comparison.5.title', fn (string $title): bool => str_contains($title, 'payout curve'))
            ->where('groups.optional.races.0.comparison.6.value', null)
            ->where('groups.optional.races.0.comparison.7.value', null)
            // Grade Points is a config fact, not a scenario name: this run's scenario defines no
            // `grade_point_by_grade` table, so the row names the absence.
            ->where('groups.optional.races.0.comparison.8.value', null)
            ->where('groups.optional.races.0.comparison.8.title', fn (string $title): bool => str_contains($title, 'Grade Point'))
            // Shop Coins are paid by placement, which is an outcome rather than a fact about the race.
            ->where('groups.optional.races.0.comparison.9.value', null)
            ->where('groups.optional.races.0.comparison.9.title', fn (string $title): bool => str_contains($title, 'placement')));
});

it('prices Grade Points from the run\'s own config table, and never from a scenario name', function (): void {
    $run = plannerRun(turns: 12, scenario: 'trackblazer');
    plannerSlot(['title' => 'Chukyo Junior Stakes', 'tier' => 'G1', 'sort_order' => 1]);
    plannerSlot(['title' => 'Hopeful Stakes', 'turn' => 20, 'month' => 10, 'tier' => 'Maiden', 'sort_order' => 2]);

    $this->get(route('runs.races.planner', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('groups.optional.races', 1)
            ->where('groups.optional.races.0.comparison.8.value', '100')
            ->where('groups.optional.races.0.comparison.8.title', null)
            // A tier the table does not carry is still an absence, not a zero.
            ->where('groups.upcoming.races.0.comparison.8.value', null));
});

it('aligns the race against the build target in plain words, with no score and no percentage', function (): void {
    $run = plannerRun(turns: 12, target: plannerTarget(band: 'Mile', surface: 'Turf'));
    plannerSlot(['title' => 'Chukyo Junior Stakes', 'distance_band' => 'Mile', 'surface' => 'Turf', 'turn' => 13, 'sort_order' => 1]);
    plannerSlot(['title' => 'Hopeful Stakes', 'turn' => 20, 'month' => 10, 'distance_band' => 'Sprint', 'surface' => null, 'sort_order' => 2]);

    $this->get(route('runs.races.planner', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('alignment.recorded', true)
            ->where('alignment.absence', null)
            ->has('alignment.rows', 3)
            ->where('alignment.rows.0.key', 'distance')
            ->where('alignment.rows.1.key', 'surface')
            ->where('alignment.rows.2.key', 'style')
            ->where('groups.optional.races.0.alignment.0.value', 'Matches')
            ->where('groups.optional.races.0.alignment.1.value', 'Matches')
            ->where('groups.upcoming.races.0.alignment.0.value', 'Does not match')
            ->where('groups.upcoming.races.0.alignment.1.value', 'Not recorded')
            // A running style is the trainee's aptitude and no column holds a per-race one, so the
            // style row is always an absence rather than a guess.
            ->where('groups.optional.races.0.alignment.2.value', 'Not recorded')
            ->where('groups.optional.races.0.alignment.2.title', fn (string $title): bool => str_contains($title, 'aptitude')));
});

it('records nothing against the target when the run has no build target, and says where to enter one', function (): void {
    $run = plannerRun(turns: 12);
    plannerSlot(['title' => 'Chukyo Junior Stakes', 'turn' => 13, 'distance_band' => 'Mile', 'surface' => 'Turf']);

    $this->get(route('runs.races.planner', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('alignment.recorded', false)
            ->where('alignment.absence', fn (string $absence): bool => str_contains($absence, 'target'))
            ->where('groups.optional.races.0.alignment.0.value', 'Not recorded'));
});

it('refuses a win figure and an expected risk, and names the ADR-0016 blocker', function (): void {
    $run = plannerRun(turns: 12);
    plannerSlot(['title' => 'Chukyo Junior Stakes', 'turn' => 13]);

    $this->get(route('runs.races.planner', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('held.win_probability.label', 'N/A')
            ->where('held.win_probability.title', fn (string $title): bool => str_contains($title, 'ADR-0016'))
            ->where('held.expected_risk.label', 'N/A')
            ->where('held.expected_risk.title', fn (string $title): bool => str_contains($title, 'ADR-0016'))
            // No readiness band, no LOW / MEDIUM / HIGH wording and no percentage ships, so the two
            // titles carry no percent sign.
            ->where('held.win_probability.title', fn (string $title): bool => ! str_contains($title, '%'))
            ->where('held.expected_risk.title', fn (string $title): bool => ! str_contains($title, '%')));
});

it('states the next mandatory race and its turn, and raises the Level 1 alert only once its turn arrives', function (): void {
    $run = plannerRun(turns: 12);
    $debut = plannerSlot(['title' => 'Junior Make Debut', 'turn' => 11, 'month' => 6, 'is_mandatory' => true, 'sort_order' => 0]);
    plannerSlot(['title' => 'URA Finals Semifinal', 'year' => RaceCatalogSlot::YEAR_FINALE, 'month' => null, 'half' => null, 'turn' => null, 'is_mandatory' => true, 'sort_order' => 1]);

    $this->get(route('runs.races.planner', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('deadline.next.title', 'Junior Make Debut')
            ->where('deadline.next.year_label', 'Junior')
            ->where('deadline.next.turn', 11)
            ->where('deadline.next.state', 'Not recorded yet')
            // Turn 11 of Junior has been reached from turn 13, so this is the Level 1 item.
            ->where('deadline.alert.text', 'Critical: Junior Make Debut is due and not recorded')
            ->where('deadline.alert.detail', fn (string $detail): bool => str_contains($detail, '11')));

    // Clearing it moves both the marker and the alert to the next obligation, whose turn has not
    // arrived, so the alert goes quiet rather than carrying over.
    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $debut->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
    ]);

    $this->get(route('runs.races.planner', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('deadline.next.title', 'URA Finals Semifinal')
            ->where('deadline.next.turn', null)
            ->where('deadline.alert', null));
});

it('names why there is no calendar to plan against, in each of the two ways there is none', function (): void {
    // A run that names no scenario has no calendar at all.
    $bare = plannerRun(turns: 12, scenario: null);
    plannerSlot();

    $this->get(route('runs.races.planner', $bare))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('run.scenario_label', 'No scenario set')
            ->where('groups', null)
            ->where('empty', fn (string $empty): bool => str_contains($empty, 'names no scenario')));

    // A run with nothing logged has no position to partition the calendar by.
    $fresh = plannerRun(turns: 0);
    plannerSlot(['title' => 'Hakodate Junior Stakes', 'turn' => 14, 'month' => 7, 'half' => 'Late']);

    $this->get(route('runs.races.planner', $fresh))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('nextTurn', null)
            ->where('groups.upcoming.empty', fn (string $empty): bool => str_contains($empty, 'No turn has been logged'))
            ->where('groups.optional.empty', fn (string $empty): bool => str_contains($empty, 'No turn has been logged')));
});

it('reads a scenario-less row as every scenario and scopes only the scenario finals', function (string $scenario): void {
    // KI-45. The GameTora export publishes a scenario key on four rows only - the four finals - so
    // 406 of the 410 catalogue rows carry `scenario_key => null` (GametoraRaceCatalogParser
    // GLOBAL_FINALS_BY_SLOT). `forScenario()` reads a null key as "every scenario", which is what
    // makes the shared calendar visible to every run; the finale is the one kind of row that filters,
    // and another scenario's finale must not leak into this run's planner.
    $other = $scenario === 'ura_finale' ? 'unity_cup' : 'ura_finale';
    $run = plannerRun(turns: 12, scenario: $scenario);

    plannerSlot(['title' => 'Chukyo Junior Stakes', 'turn' => 13, 'scenario_key' => null, 'sort_order' => 1]);
    plannerSlot([
        'title' => 'Own Final',
        'scenario_key' => $scenario,
        'year' => RaceCatalogSlot::YEAR_FINALE,
        'month' => null,
        'half' => null,
        'turn' => null,
        'is_mandatory' => true,
        'sort_order' => 2,
    ]);
    plannerSlot([
        'title' => 'Other Final',
        'scenario_key' => $other,
        'year' => RaceCatalogSlot::YEAR_FINALE,
        'month' => null,
        'half' => null,
        'turn' => null,
        'is_mandatory' => true,
        'sort_order' => 3,
    ]);

    $this->get(route('runs.races.planner', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // The scenario-less row is present for this scenario...
            ->where('groups.optional.races', fn ($races): bool => collect($races)->contains('title', 'Chukyo Junior Stakes'))
            // ...this scenario's own finale is present...
            ->where('groups.mandatory.races', fn ($races): bool => collect($races)->contains('title', 'Own Final'))
            // ...and the other scenario's finale is not.
            ->where('groups.mandatory.races', fn ($races): bool => ! collect($races)->contains('title', 'Other Final'))
            ->has('groups.mandatory.races', 1));
})->with(['ura_finale', 'unity_cup', 'our_grand_concert', 'trackblazer']);

it('carries the existing race write and offers no second one', function (): void {
    $run = plannerRun(turns: 12);
    plannerSlot(['title' => 'Chukyo Junior Stakes', 'turn' => 13]);

    $this->get(route('runs.races.planner', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('entry.action', route('runs.races.store', $run))
            ->where('run.run_url', route('runs.cockpit', $run)));
});

it('caps a position group and names what it left out rather than stopping silently', function (): void {
    $run = plannerRun(turns: 12);

    // Ten races ahead, all non-mandatory, so the group has more than its ceiling holds.
    for ($index = 0; $index < 10; $index++) {
        $turn = 14 + $index;

        plannerSlot([
            'title' => "Upcoming Race {$index}",
            'turn' => $turn,
            'month' => intdiv($turn - 1, 2) + 1,
            'half' => $turn % 2 === 1 ? 'Early' : 'Late',
            'sort_order' => $index,
        ]);
    }

    $this->get(route('runs.races.planner', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('groups.upcoming.total', 10)
            ->has('groups.upcoming.races', 8)
            ->where('groups.upcoming.truncated', fn (string $truncated): bool => str_contains($truncated, 'First 8 of 10'))
            // The empty state and the truncation are different claims, so only one is ever set.
            ->where('groups.upcoming.empty', null)
            ->where('groups.optional.truncated', null));
});

it('reads the calendar once, however many regions partition it', function (): void {
    // The plan asks for one eager-loaded calendar read, and the page has two regions that partition
    // it: the four groups and the deadline. A second query for the seven mandatory rows would be the
    // same data asked twice, so this pins the count rather than trusting the docblock.
    //
    // The fixture logs turns but enters no race: the run's `raceEntries.raceCatalogSlot` eager load
    // issues its own read of this table only when there are entries to resolve, so an entry-free run
    // is what makes this count the controller's own reads and nothing else.
    $run = plannerRun(turns: 12);
    plannerSlot(['title' => 'Junior Make Debut', 'turn' => 11, 'month' => 6, 'is_mandatory' => true, 'sort_order' => 0]);
    plannerSlot(['title' => 'Hopeful Stakes', 'turn' => 20, 'month' => 10, 'half' => 'Late', 'sort_order' => 1]);

    $reads = 0;
    DB::listen(function ($query) use (&$reads): void {
        if (str_contains($query->sql, 'from "race_catalog_slots"')) {
            $reads++;
        }
    });

    $this->get(route('runs.races.planner', $run))->assertOk();

    expect($reads)->toBe(1);
});

it('carries no readiness-band enum and no win figure, in the markup or in the props', function (): void {
    // The acceptance asks for a grep proving no readiness band and no win percentage. A band is the
    // LOW / MEDIUM / HIGH triple and the brief's figure is a percentage; neither ships, and neither
    // word set appears even as a refusal, so the grep is unambiguous rather than needing a scope.
    $surfaces = [
        resource_path('js/pages/Career/RacePlanner.vue'),
        resource_path('js/components/career/RacePlanList.vue'),
    ];

    foreach ($surfaces as $file) {
        $source = (string) file_get_contents($file);

        expect(preg_match('/\b(LOW|MEDIUM|HIGH)\b/', $source), "{$file} carries a readiness-band word")->toBe(0);
        expect($source, "{$file} carries a percent sign")->not->toContain('%');
    }

    $run = plannerRun(turns: 12, target: plannerTarget());
    plannerSlot(['title' => 'Chukyo Junior Stakes', 'turn' => 13]);

    $this->get(route('runs.races.planner', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('held', function ($held): bool {
                $json = json_encode($held) ?: '';

                return preg_match('/\b(LOW|MEDIUM|HIGH)\b/', $json) === 0 && ! str_contains($json, '%');
            }));
});

it('renders no win figure and no percentage anywhere in the slice', function (): void {
    // A percentage is the shape a win estimate takes, and the brief's recommendation block prints one
    // ("Win probability: 84%"). Neither is built (ADR-0016), so the rendered surface carries no
    // percent sign at all.
    $surfaces = [
        resource_path('js/pages/Career/RacePlanner.vue'),
        resource_path('js/components/career/RacePlanList.vue'),
    ];

    foreach ($surfaces as $file) {
        expect(file_get_contents($file), $file)->not->toContain('%');
    }

    // And the props carry none. The controller's own source is not grepped for the character: it holds
    // a docblock quoting the brief's figure, so a file-level check there would fail on citation rather
    // than on copy. What matters is that nothing reaching the page can print one.
    $run = plannerRun(turns: 12, target: plannerTarget());
    plannerSlot(['title' => 'Chukyo Junior Stakes', 'turn' => 13]);

    $this->get(route('runs.races.planner', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('held', fn ($held): bool => ! str_contains(json_encode($held) ?: '', '%'))
            ->where('alignment.rows', fn ($rows): bool => ! str_contains(json_encode($rows) ?: '', '%'))
            ->where('comparison.rows', fn ($rows): bool => ! str_contains(json_encode($rows) ?: '', '%'))
            ->where('groups.optional.races.0.comparison', fn ($cells): bool => ! str_contains(json_encode($cells) ?: '', '%')));
});

it('points the no-position absence at the screen that can record the turn', function (): void {
    // R2-07. This sentence used to send the Trainer to the retired run screen. The sweep in
    // `NoStaleRunRecordLinksTest` proves the old name is gone; this proves the replacement points
    // somewhere real: a run with no logged turn is told to record it from the Cockpit, which is the
    // screen whose action grid leads to the turn write.
    $run = plannerRun(turns: 0);

    $this->get(route('runs.races.planner', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('groups.upcoming.empty', fn (string $line): bool => str_contains($line, 'Record the first turn from the Cockpit'))
            ->where('groups.optional.empty', fn (string $line): bool => str_contains($line, 'Record the first turn from the Cockpit')));
});
