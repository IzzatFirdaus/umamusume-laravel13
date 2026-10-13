<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Enums\TraineeGoalType;
use App\Models\RaceCatalogSlot;
use App\Models\TraineeGoal;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use Illuminate\Support\Collection;

/**
 * A run at Junior turn 13, so the next turn to play is 13 of year 1 and a turn-12 objective has
 * arrived while a turn-30 one has not.
 */
function goalsRun(string $scenario = 'ura_finale'): TrainingRun
{
    $run = TrainingRun::factory()->create(['scenario' => $scenario, 'status' => 'Active']);
    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 12, 'fans' => 100]);

    return $run->fresh();
}

function goalFor(TrainingRun $run, array $attributes = []): TraineeGoal
{
    return TraineeGoal::factory()->create([
        'umamusume_id' => $run->umamusume_id,
        'source_cond_id' => random_int(1, 1_000_000),
        ...$attributes,
    ]);
}

/**
 * @return array{group: string, rows: list<array<string, mixed>>, summary: string|null}
 */
function goalsGroup(TrainingRun $run): array
{
    /** @var list<array<string, mixed>> $objectives */
    $objectives = test()->get(route('runs.cockpit', $run))->assertOk()->inertiaProps('scenario.objectives');

    return $objectives[0] ?? ['group' => '', 'rows' => [], 'summary' => null];
}

it('replaces the named absence with the trainee own ladder, ordered by the career', function (): void {
    $run = goalsRun();
    goalFor($run, ['career_year' => 3, 'turn' => 54, 'source_order' => 5, 'required_placement' => 3]);
    goalFor($run, ['career_year' => 1, 'turn' => 12, 'source_order' => 1, 'goal_type' => TraineeGoalType::Participate, 'required_placement' => 0]);

    $group = goalsGroup($run);

    expect($group['group'])->toBe('Trainee goals')
        ->and($group['rows'])->toHaveCount(2)
        // Ordered by the career, not by insertion: the debut first.
        ->and($group['rows'][0]['year_label'])->toBe('Junior')
        ->and($group['rows'][1]['year_label'])->toBe('Senior')
        ->and($group['summary'])->toBe('Goals Achieved 0 of 2');
});

it('derives a met placing from the race entry the Trainer already recorded, with no new write', function (): void {
    $run = goalsRun();
    $slot = RaceCatalogSlot::factory()->create(['year' => 3, 'month' => 3, 'half' => 'Late', 'title' => 'Nikkei Sho']);
    goalFor($run, ['career_year' => 3, 'turn' => 54, 'race_catalog_slot_id' => $slot->id, 'required_placement' => 3]);

    $run->raceEntries()->create(['scenario_race_id' => null, 'race_catalog_slot_id' => $slot->id, 'status' => RaceEntryStatus::Completed, 'placement' => 3]);

    $group = goalsGroup($run->fresh());

    expect($group['rows'][0]['label'])->toBe('Place top 3 in Nikkei Sho')
        ->and($group['rows'][0]['state'])->toBe('completed')
        ->and($group['rows'][0]['race_url'])->toBeNull()
        ->and($group['summary'])->toBe('Goals Achieved 1 of 1');
});

it('does not credit a finish worse than the one the source asked for', function (): void {
    $run = goalsRun();
    $slot = RaceCatalogSlot::factory()->create(['year' => 3, 'month' => 3, 'half' => 'Late', 'title' => 'Nikkei Sho']);
    goalFor($run, ['career_year' => 3, 'turn' => 54, 'race_catalog_slot_id' => $slot->id, 'required_placement' => 1]);

    $run->raceEntries()->create(['scenario_race_id' => null, 'race_catalog_slot_id' => $slot->id, 'status' => RaceEntryStatus::Completed, 'placement' => 2]);

    expect(goalsGroup($run->fresh())['rows'][0]['state'])->not->toBe('completed');
});

it('derives a fan objective from the turn log at or before its deadline', function (): void {
    $run = goalsRun();
    goalFor($run, ['goal_type' => TraineeGoalType::Fan, 'required_placement' => null, 'fan_threshold' => 5000, 'career_year' => 2, 'turn' => 37]);

    $group = goalsGroup($run);
    expect($group['rows'][0]['label'])->toBe('Earn 5000 fans')
        ->and($group['rows'][0]['state'])->toBe('upcoming')
        // The run has 100 fans at turn 12 and the gate is 5,000, so nothing is met.
        ->and($group['summary'])->toBe('Goals Achieved 0 of 1');

    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 30, 'fans' => 6000]);
    expect(goalsGroup($run->fresh())['rows'][0]['state'])->toBe('completed');

    // A reading after the deadline cannot clear an objective whose window has passed unmet.
    goalFor($run, ['goal_type' => TraineeGoalType::Fan, 'required_placement' => null, 'fan_threshold' => 9000, 'career_year' => 2, 'turn' => 45]);
    $late = goalsGroup($run->fresh());
    expect($late['rows'][1]['label'])->toBe('Earn 9000 fans')->and($late['rows'][1]['state'])->toBe('upcoming');
});

it('marks exactly one objective current, on the turn the career is about to play', function (): void {
    $run = goalsRun();
    goalFor($run, ['career_year' => 1, 'turn' => 13, 'source_order' => 2]);
    goalFor($run, ['career_year' => 1, 'turn' => 14, 'source_order' => 3]);

    $states = array_column(goalsGroup($run)['rows'], 'state');

    expect($states)->toBe(['current', 'upcoming']);
});

it('alerts once for an objective the scenario already makes mandatory, whichever way the debut banner settles', function (): void {
    $run = goalsRun();
    $slot = RaceCatalogSlot::factory()->create(['year' => 1, 'month' => 6, 'half' => 'Late', 'title' => 'Junior Make Debut', 'is_mandatory' => true]);
    goalFor($run, ['career_year' => 1, 'turn' => 12, 'source_order' => 1, 'goal_type' => TraineeGoalType::Participate, 'required_placement' => 0, 'race_catalog_slot_id' => $slot->id]);

    /** @var list<array<string, mixed>> $alerts */
    $alerts = test()->get(route('runs.cockpit', $run))->assertOk()->inertiaProps('scenario.alerts');

    // The mandatory group raises it. The goals group stays silent on the same slot, so a Trainer reads
    // one demand once even though the export and the capture corpus disagree about its banner.
    expect(collect($alerts)->filter(fn (array $a): bool => str_contains($a['text'], 'Junior Make Debut')))->toHaveCount(1);
});

it('leaves an unread requirement unread, naming the source figures rather than a guessed sentence', function (): void {
    $run = goalsRun();
    goalFor($run, [
        'goal_type' => TraineeGoalType::Undecoded, 'required_placement' => null, 'race_catalog_slot_id' => null,
        'source_cond_type' => 2, 'source_cond_value' => 3, 'source_cond_value_2' => 3, 'career_year' => 3, 'turn' => 60,
    ]);

    $row = goalsGroup($run)['rows'][0];

    expect($row['label'])->toBe('An objective the source states in codes this tool has not read')
        ->and($row['absence'])->toContain('has not decoded')
        ->and($row['absence'])->toContain('condition type 2')
        ->and($row['absence'])->toContain('second value 3');
});

it('stores a race the source marks unreleased on Global without printing it as a Global obligation', function (): void {
    $run = goalsRun();
    goalFor($run, ['global_unreleased' => true, 'source_race_name' => 'Queen Elizabeth Cup', 'race_catalog_slot_id' => null, 'career_year' => 3, 'turn' => 69]);

    $row = goalsGroup($run)['rows'][0];

    // The requirement is stated and the race is named as the source names it; what the row must not do is
    // read as a Global obligation, so it carries no state and no door to the picker.
    expect($row['label'])->toBe('Place top 3 in Queen Elizabeth Cup')
        ->and($row['state'])->toBeNull()
        ->and($row['absence'])->toContain('not released on [Global]')
        ->and($row['race_url'])->toBeNull();
});

it('counts an alternates branch as one obligation, not two', function (): void {
    $run = goalsRun();
    goalFor($run, ['career_year' => 3, 'turn' => 48, 'source_order' => 7, 'source_race_choice' => 1]);
    goalFor($run, ['career_year' => 3, 'turn' => 49, 'source_order' => 7, 'source_race_choice' => 2]);

    $group = goalsGroup($run);

    expect($group['rows'])->toHaveCount(2)->and($group['summary'])->toBe('Goals Achieved 0 of 1');
});

it('marks the strip row its own ladder names, beside the finish state rather than in place of it', function (): void {
    $run = goalsRun();
    $slot = RaceCatalogSlot::factory()->create([
        'scenario_key' => 'ura_finale', 'year' => 1, 'month' => 6, 'half' => 'Late', 'turn' => 12,
        'title' => 'Junior Make Debut',
    ]);
    TraineeGoal::factory()->participate()->create([
        'umamusume_id' => $run->umamusume_id, 'race_catalog_slot_id' => $slot->id,
        'career_year' => 1, 'turn' => 12, 'source_order' => 1,
    ]);
    $run->raceEntries()->create([
        'scenario_race_id' => null, 'race_catalog_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed, 'placement' => 1,
    ]);

    /** @var array{run: list<array<string, mixed>>, ahead: list<array<string, mixed>>} $strip */
    $strip = test()->get(route('runs.cockpit', $run->fresh()))->assertOk()->inertiaProps('raceStrip');

    // The mark and the finish are two claims on one row: the client's pennant survives the finish, and
    // "ran it" is not the same statement as "won it". Rows the ladder does not name carry the key as
    // null rather than omitting it, so the component never has to guess why a row is silent.
    expect($strip['run'][0]['goal'])->toBe('Participate in Junior Make Debut')
        ->and($strip['run'][0]['status'])->toBe('Completed')
        ->and($strip['run'][0]['placement'])->not->toBeNull();
    // The null-when-unmarked half of the same contract is pinned in RunRaceStripTest, where a run with no
    // fetched ladder reads ahead rows with `goal` present and null.
});

it('offers the obligation on the decision row and its drawer, beside the calendar fact rather than over it', function (): void {
    $run = goalsRun();
    $slot = RaceCatalogSlot::factory()->create([
        'scenario_key' => 'ura_finale', 'year' => 1, 'month' => 7, 'half' => 'Early', 'turn' => 13,
        'title' => 'Satsuki Sho', 'is_mandatory' => false,
    ]);
    TraineeGoal::factory()->create([
        'umamusume_id' => $run->umamusume_id, 'race_catalog_slot_id' => $slot->id,
        'career_year' => 1, 'turn' => 13, 'source_order' => 2, 'required_placement' => 5,
    ]);

    /** @var list<array<string, mixed>> $races */
    $races = test()->get(route('runs.races.decision', $run->fresh()))->assertOk()->inertiaProps('races');

    // Two claims on one row: `is_mandatory` stays the scenario's, `goal` is hers. The screen's own
    // docblock says it renders "mandatory", never "goal", and this is the addition that keeps both.
    expect($races)->toHaveCount(1)
        ->and($races[0]['goal'])->toBe('Place top 5 in Satsuki Sho')
        ->and($races[0]['is_mandatory'])->toBeFalse();
});

it('draws the ladder for the three scenarios that keep race goals and not for Trackblazer', function (string $scenario, bool $expected): void {
    $run = goalsRun($scenario);
    goalFor($run, ['career_year' => 1, 'turn' => 12, 'source_order' => 1, 'goal_type' => TraineeGoalType::Participate, 'required_placement' => 0]);

    /** @var list<array<string, mixed>> $objectives */
    $objectives = test()->get(route('runs.cockpit', $run))->assertOk()->inertiaProps('scenario.objectives');

    expect(in_array('Trainee goals', array_column($objectives, 'group'), true))->toBe($expected);
})->with([
    ['ura_finale', true],
    ['unity_cup', true],
    ['our_grand_concert', true],
    ['trackblazer', false],
]);

it('keeps Happy Meek in the scenario whose mechanic it is', function (string $scenario, bool $expected): void {
    // The defect this pins: `career_goals` is one switch, and the group list it gates was composed as
    // three groups for every scenario that turns it on. Unity Cup and the Grand Concert therefore gained
    // a URA module whose rows are all named absences of a mechanic their scenario does not have, which
    // the browser suite caught at `tests/browser/ura-panel.spec.ts:202` once the flag was on.
    $run = goalsRun($scenario);

    /** @var list<array<string, mixed>> $objectives */
    $objectives = test()->get(route('runs.cockpit', $run))->assertOk()->inertiaProps('scenario.objectives');

    expect(in_array('Happy Meek', array_column($objectives, 'group'), true))->toBe($expected);
})->with([
    ['ura_finale', true],
    ['unity_cup', false],
    ['our_grand_concert', false],
]);

it('leaves the group out entirely for a trainee whose ladder was never fetched', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale', 'status' => 'Active']);
    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1, 'fans' => 0]);
    expect(goalsGroup($run->fresh())['rows'])->toBe([]);

    $unfetched = Umamusume::factory()->create();
    $other = TrainingRun::factory()->create(['umamusume_id' => $unfetched->id, 'scenario' => 'ura_finale']);
    TurnEntry::factory()->create(['training_run_id' => $other->id, 'turn' => 1, 'fans' => 0]);

    /** @var Collection<int, array<string, mixed>> $objectives */
    $objectives = collect(test()->get(route('runs.cockpit', $other->fresh()))->assertOk()->inertiaProps('scenario.objectives'));

    expect($objectives->first()['group'])->toBe('Trainee goals')
        ->and($objectives->first()['rows'])->toBe([])
        ->and($objectives->first()['summary'])->toBeNull();
});
