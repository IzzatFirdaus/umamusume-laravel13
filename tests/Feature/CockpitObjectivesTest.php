<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Models\Advisor\BuildTargetPayload;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;

/*
 * FD-3, objectives and deadline context on the Career Cockpit (plan §9a FD-3, continuation prompt §4).
 *
 * **Every state a row claims is read from data, and the one state the data cannot support is proven
 * absent.** `upcoming`, `current`, `completed` and `unknown` each have a recorded operand. `missed` does
 * not: a slot behind the run with no entry is the same stored fact as a slot the Trainer has not entered
 * yet, and no source in this repository says a mandatory race becomes unattainable. So no code path emits
 * it, and one case below asserts that. Inventing it would be execution rule 2's exact prohibition.
 *
 * **The badge is `calculated` on these rows because a state is a comparison, not a reading.** The
 * provenance table (`PLANS-AND-BRIEFS.md` §"provenance-and-absence-contracts.md" §1) bars `confirmed` from anything "the tool
 * computed, summed, counted, compared or interpolated", and every row here compares a slot position
 * against the run's own position and its recorded races. The `title` names both operands.
 *
 * **No fan threshold is a literal.** The gates read `race_catalog_slots.fans_needed` as stored per slot;
 * one case mutates that column and asserts the row moves with it, which is the falsifiable form of "not
 * hard-coded".
 *
 * Priority comes from `config('scenarios.objective_priority')` at the top level, so a fifth scenario
 * inherits it from the same list rather than from an entry of its own.
 */

function objectiveRun(string $scenario, int $turns): TrainingRun
{
    $run = TrainingRun::factory()->create([
        'scenario' => $scenario,
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->create()->id,
    ]);

    for ($turn = 1; $turn <= $turns; $turn++) {
        TurnEntry::factory()->create([
            'training_run_id' => $run->id,
            'turn' => $turn,
            'speed' => 600,
            'stamina' => 600,
            'power' => 600,
            'guts' => 600,
            'wit' => 600,
            'sp' => 120,
            'energy' => 80,
            'fans' => 1000 * $turn,
        ]);
    }

    return $run->fresh();
}

function objectiveSlot(?string $scenarioKey, string $title, int $year, ?int $turn, bool $mandatory = true, ?int $fansNeeded = null): RaceCatalogSlot
{
    return RaceCatalogSlot::factory()->create([
        'scenario_key' => $scenarioKey,
        'year' => $year,
        'month' => 4,
        'half' => 'Early',
        'turn' => $turn,
        'slot_label' => $title,
        'title' => $title,
        'tier' => 'G1',
        'is_mandatory' => $mandatory,
        'fans_needed' => $fansNeeded,
    ]);
}

/**
 * @return list<array<string, mixed>>
 */
function objectiveRows(TrainingRun $run): array
{
    /** @var array<string, mixed> $decision */
    $decision = test()->get(route('runs.cockpit', $run))->assertOk()->inertiaProps('advisor.decision');

    return $decision['objectives'];
}

it('carries objectives as a seventh key of the same contract in every Global scenario', function (): void {
    /** @var list<string> $scenarios */
    $scenarios = array_keys(config('scenarios.scenarios'));

    config()->set('scenarios.scenarios.fifth_scenario', [
        'label' => 'A Fifth Scenario',
        'live_on_global' => '2027-01-01',
        'cap_bonus' => ['Speed' => 0, 'Stamina' => 0, 'Power' => 0, 'Guts' => 0, 'Wit' => 0],
        'widgets' => ['turn', 'energy', 'fans'],
        'steps' => ['training', 'outcome', 'skill'],
        'panels' => [
            'race_calendar' => true,
            'team_race' => false,
            'grade_objectives' => false,
            'shop' => false,
            'epithet_routes' => false,
            'team_rank_ladder' => false,
            'career_goals' => false,
        ],
        'scenario_links' => [],
        'facility_level_source' => 'repetition',
    ]);

    // `objectives` is still the seventh key; FD-4's `recorded_action` (ruling D-1a) is the eighth. The
    // assertion is that every scenario produces this same list, so it grows when the contract grows.
    $keys = ['priority', 'recommended', 'alternatives', 'why', 'switch_if', 'refusal', 'objectives', 'recorded_action'];

    foreach ([...$scenarios, 'fifth_scenario'] as $scenario) {
        $run = objectiveRun($scenario, 6);
        objectiveSlot($scenario, 'Saihai Sho', RaceCatalogSlot::YEAR_JUNIOR, 5);

        /** @var array<string, mixed> $decision */
        $decision = $this->get(route('runs.cockpit', $run))->assertOk()->inertiaProps('advisor.decision');

        expect(array_keys($decision))->toBe($keys, "[{$scenario}] drifted from the shared contract");
    }
});

it('gives every objective row the declared fields and a state the vocabulary allows', function (): void {
    $run = objectiveRun('unity_cup', 6);
    objectiveSlot('unity_cup', 'Saihai Sho', RaceCatalogSlot::YEAR_JUNIOR, 12);

    foreach (objectiveRows($run) as $row) {
        expect(array_keys($row))->toBe(['label', 'state', 'due_turn', 'due_label', 'consequence', 'source', 'badge', 'title', 'scope'])
            // The five documented states, minus `missed`, which nothing in this build may claim.
            ->and(['upcoming', 'current', 'completed', 'unknown'])->toContain($row['state'])
            ->and(['confirmed', 'calculated'])->toContain($row['badge'])
            ->and(['shared', 'scenario'])->toContain($row['scope'])
            ->and($row['title'])->not->toBe('');
    }
});

it('separates the career-wide finals rail from the scenario\'s own obligation (UX-04)', function (): void {
    // `scopeForScenario()` unions `scenario_key IS NULL` with the run's own key, so every scenario's rail
    // held the shared rows and its own final under one heading. Unity Cup draws the same per-turn race
    // schedule as URA Finale (`docs/scenarios/02-unity-cup.md:21-24`), so its facts were right and only
    // the grouping was missing: a Trainer read three career-wide rows as though Unity Cup owned them.
    $run = objectiveRun('unity_cup', 6);
    // A row every scenario inherits (`scenario_key IS NULL`) and Unity Cup's own forked final.
    objectiveSlot(null, 'URA Finals Qualifier', RaceCatalogSlot::YEAR_FINALE, null);
    objectiveSlot('unity_cup', 'URA Finals Final (Aoharu)', RaceCatalogSlot::YEAR_FINALE, null);

    $rows = collect(objectiveRows($run));

    $shared = $rows->where('scope', 'shared')->pluck('label')->all();
    $scenario = $rows->where('scope', 'scenario')->pluck('label')->all();

    expect($shared)->toContain('URA Finals Qualifier')
        ->and($scenario)->toContain('URA Finals Final (Aoharu)')
        // The fork is the only structural difference between the two scenarios' calendars: Unity Cup's own
        // final is scenario-scoped, and the qualifier every scenario inherits is not.
        ->and($shared)->not->toContain('URA Finals Final (Aoharu)')
        ->and($scenario)->not->toContain('URA Finals Qualifier');
});

it('marks a mandatory race that has not arrived as upcoming, with the turn the calendar states', function (): void {
    $run = objectiveRun('unity_cup', 6);
    objectiveSlot('unity_cup', 'Saihai Sho', RaceCatalogSlot::YEAR_JUNIOR, 12);

    $row = objectiveRows($run)[0];

    expect($row['label'])->toBe('Saihai Sho')
        ->and($row['state'])->toBe('upcoming')
        ->and($row['due_turn'])->toBe(12)
        ->and($row['due_label'])->toBe('Junior Year · Turn 12')
        ->and($row['source'])->toBe('race_catalog_slots')
        ->and($row['badge'])->toBe('calculated')
        ->and($row['title'])->toContain('race_catalog_slots')
        // A consequence is printed only where a source states one, and none does for a mandatory race.
        ->and($row['consequence'])->toBeNull();
});

it('reads a due-and-not-recorded obligation as current and puts it in the priority line', function (): void {
    $run = objectiveRun('unity_cup', 12);
    objectiveSlot('unity_cup', 'Saihai Sho', RaceCatalogSlot::YEAR_JUNIOR, 12);
    $run->update(['build_target' => BuildTargetPayload::fromArray([
        'purpose' => 'StoryClear',
        'distance' => 'Medium',
        'surface' => 'Turf',
        'style' => 'Pace Chaser',
        'targets' => ['Speed' => 900, 'Stamina' => 500, 'Power' => 500, 'Guts' => 500, 'Wit' => 800],
        'skill_priorities' => [],
    ])->toArray()]);

    /** @var array{priority: string, recommended: mixed, refusal: mixed} $decision */
    $decision = $this->get(route('runs.cockpit', $run->fresh()))->assertOk()->inertiaProps('advisor.decision');

    expect($decision['objectives'][0]['state'])->toBe('current')
        ->and($decision['priority'])->toBe('Saihai Sho is due and not recorded.')
        // The obligation beats the deficit: `objective_priority` ranks mandatory objectives above build
        // targets, and this run carries both.
        ->and($decision['recommended'])->not->toBeNull()
        ->and($decision['refusal'])->toBeNull();
});

it('marks a recorded obligation completed from the entry that points at the slot', function (): void {
    $run = objectiveRun('unity_cup', 12);
    $slot = objectiveSlot('unity_cup', 'Saihai Sho', RaceCatalogSlot::YEAR_JUNIOR, 12);

    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
        'turn_entry_id' => $run->turnEntries()->where('turn', 12)->firstOrFail()->id,
    ]);

    $row = objectiveRows($run->fresh())[0];

    expect($row['state'])->toBe('completed')
        // The comparison is against a stored row, so the title says which two things were compared.
        ->and($row['title'])->toContain('race_entries');
});

it('never reports a mandatory race as missed, because nothing in the tree says it became unattainable', function (): void {
    // Turn 30 with a mandatory slot at turn 12 that was never entered: the run has moved past it. That is
    // the same stored fact as "not entered yet", so the honest state is current, not missed.
    $run = objectiveRun('unity_cup', 30);
    objectiveSlot('unity_cup', 'Saihai Sho', RaceCatalogSlot::YEAR_JUNIOR, 12);

    expect(objectiveRows($run))->toBeArray()
        ->and(collect(objectiveRows($run))->firstWhere('state', 'missed'))->toBeNull()
        ->and(objectiveRows($run)[0]['state'])->toBe('current');
});

it('draws a fan-gate threshold from the slot row rather than from a number in the code', function (): void {
    $run = objectiveRun('ura_finale', 6);
    objectiveSlot('ura_finale', 'The Fan-Gated Race', RaceCatalogSlot::YEAR_CLASSIC, 30, true, 60000);

    $before = objectiveRows($run)[0];
    expect($before['label'])->toContain('60,000');

    // The same slot with a different recorded gate must move the printed figure. If the threshold were a
    // literal in a component or a controller, this write would change nothing and the case would fail.
    RaceCatalogSlot::query()->where('title', 'The Fan-Gated Race')->update(['fans_needed' => 91000]);

    $after = objectiveRows($run)[0];

    expect($after['label'])->toContain('91,000')
        ->and($after['label'])->not->toContain('60,000')
        ->and(json_encode($before))->not->toContain('91,000');
});

it('composes no mandatory-race obligation for a scenario whose matrix turns the calendar off', function (): void {
    // `trackblazer.panels.race_calendar` is false and its own note says no mandatory race goals exist
    // there, so a mandatory row would state a system the scenario does not compose (G-33).
    $run = objectiveRun('trackblazer', 6);
    objectiveSlot('trackblazer', 'A Race That Should Not Rank', RaceCatalogSlot::YEAR_JUNIOR, 12);

    expect(collect(objectiveRows($run))->firstWhere('label', 'A Race That Should Not Rank'))->toBeNull();
});

it('dates an undated finale row from the advisor\'s distance instead of printing no date (UX-09)', function (): void {
    // The finale rows sit outside the per-turn grid (`race_catalog_slots.turn` is null), so they printed
    // `no date` while the Scenario strip on the same screen printed `5 turns away`. The figure is a
    // distance, not a grid position: `due_turn` stays null and only `due_label` borrows the count.
    //
    // Placed at Senior turn 21 because the advisor's finale window is the last five turns: further out it
    // returns no context at all and the row correctly keeps `null`.
    $run = objectiveRun('unity_cup', RaceCatalogSlot::YEAR_SENIOR * TrainingRun::TURNS_PER_YEAR - 3);
    objectiveSlot('unity_cup', 'URA Finals Final (Aoharu)', RaceCatalogSlot::YEAR_FINALE, null);

    $row = collect(objectiveRows($run))->firstWhere('label', 'URA Finals Final (Aoharu)');

    expect($row)->not->toBeNull()
        ->and($row['due_turn'])->toBeNull()
        ->and($row['due_label'])->toMatch('/turns? away|This turn|Passed/')
        ->and($row['due_label'])->not->toContain('no date');
});

it('reports unknown where the calendar states no turn, rather than ranking it as turn zero', function (): void {
    // The finale block is a slot whose `turn` column is null: it sits outside the 24-turn year grid. An
    // arithmetic comparison against a null turn would read it as year x 24 + 0 and place it in front of
    // the year it belongs to, which is a derivation the source does not support.
    $run = objectiveRun('unity_cup', 6);
    RaceCatalogSlot::factory()->create([
        'scenario_key' => 'unity_cup',
        'year' => RaceCatalogSlot::YEAR_FINALE,
        'month' => null,
        'turn' => null,
        'slot_label' => 'Finale block',
        'title' => 'Cup Finale',
        'is_mandatory' => true,
        // The factory defaults `fans_needed`, and a gate on a slot the test did not ask for would quietly
        // reclassify it as a fan_gate row.
        'fans_needed' => null,
    ]);

    $rows = objectiveRows($run);

    expect($rows)->toHaveCount(1)
        ->and($rows[0]['state'])->toBe('unknown')
        ->and($rows[0]['due_turn'])->toBeNull()
        ->and($rows[0]['due_label'])->toBeNull()
        ->and($rows[0]['title'])->toContain('no turn');
});

it('takes the ordering from config, so changing the list changes which obligation is first', function (): void {
    $run = objectiveRun('unity_cup', 6);
    objectiveSlot('unity_cup', 'Saihai Sho', RaceCatalogSlot::YEAR_JUNIOR, 12);
    RaceCatalogSlot::factory()->create([
        'scenario_key' => 'unity_cup',
        'year' => RaceCatalogSlot::YEAR_FINALE,
        'month' => null,
        'turn' => null,
        'slot_label' => 'Finale block',
        'title' => 'Cup Finale',
        'is_mandatory' => true,
        // The factory defaults `fans_needed`, and a gate on a slot the test did not ask for would quietly
        // reclassify it as a fan_gate row.
        'fans_needed' => null,
    ]);

    // Both rows come from the same table, so only the declared class order can decide which is first.
    expect(objectiveRows($run)[0]['label'])->toBe('Saihai Sho');

    config()->set('scenarios.objective_priority', array_reverse(config('scenarios.objective_priority')));

    expect(objectiveRows($run)[0]['label'])->toBe('Cup Finale')
        ->and(config('scenarios.objective_priority'))->toBeArray();
});

it('lets a due obligation outrank the advisor\'s own inability to rank a session', function (): void {
    // The obligation is actionable whatever the advisor is missing, so the priority line names the race
    // while `refusal` still reports the missing input. Tier 2 over tier 4 in screen-spec-2.0 §29.
    $run = objectiveRun('unity_cup', 12);
    objectiveSlot('unity_cup', 'Saihai Sho', RaceCatalogSlot::YEAR_JUNIOR, 12);
    $run->update(['build_target' => null]);

    /** @var array{priority: string, recommended: mixed, refusal: array{missing: list<string>}} $decision */
    $decision = $this->get(route('runs.cockpit', $run->fresh()))->assertOk()->inertiaProps('advisor.decision');

    expect($decision['priority'])->toBe('Saihai Sho is due and not recorded.')
        ->and($decision['recommended'])->toBeNull()
        ->and($decision['refusal']['missing'])->toBe(['build target']);
});

it('carries neither a band, nor a risk, nor a score, nor a confidence into the extended payload', function (): void {
    $run = objectiveRun('unity_cup', 12);
    objectiveSlot('unity_cup', 'Saihai Sho', RaceCatalogSlot::YEAR_JUNIOR, 12);
    $run->update(['build_target' => BuildTargetPayload::fromArray([
        'purpose' => 'StoryClear',
        'distance' => 'Medium',
        'surface' => 'Turf',
        'style' => 'Pace Chaser',
        'targets' => ['Speed' => 900, 'Stamina' => 500, 'Power' => 500, 'Guts' => 500, 'Wit' => 800],
        'skill_priorities' => [],
    ])->toArray()]);

    /** @var array<string, mixed> $decision */
    $decision = $this->get(route('runs.cockpit', $run->fresh()))->assertOk()->inertiaProps('advisor.decision');

    $flat = json_encode($decision, JSON_THROW_ON_ERROR);

    expect($flat)->not->toMatch('/"(score|confidence|band|risk)"/')
        ->and(array_key_exists('objectives', $decision))->toBeTrue();
});

it('keeps the objectives list empty rather than inventing a row when the calendar reads no obligations', function (): void {
    $run = objectiveRun('our_grand_concert', 4);

    expect(objectiveRows($run))->toBe([]);
});

it('reads a past-due obligation with no record as not_reached, not current', function () {
    // Turn 67 with a Junior obligation due turn 12 that was never entered: the deadline passed
    // 55 turns ago and the run holds no race_entries row for it. `current` is the wrong word for
    // a passed deadline (F-01 / TOG-3); `not_reached` is the honest value, matching the goals
    // panel S3 landed in `TeamPanelBuilder::objectiveState`.
    $run = objectiveRun('unity_cup', 67);
    objectiveSlot('unity_cup', 'Junior Make Debut', RaceCatalogSlot::YEAR_JUNIOR, 6);

    $juniorDebut = collect(objectiveRows($run))->firstWhere('label', 'Junior Make Debut');

    expect($juniorDebut)->not->toBeNull()
        ->and($juniorDebut['state'])->toBe('not_reached')
        ->and($juniorDebut['state'])->not->toBe('current')
        // The title must not claim the slot "has arrived": it fell due before this run's position.
        ->and($juniorDebut['title'])->not->toContain('has arrived');
});
