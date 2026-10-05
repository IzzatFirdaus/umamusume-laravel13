<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\TrainingRun;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * KI-10's schema half: Grade Points are judged per period, so a finish has to know
 * which period it counts toward, and the run has to know which period the Trainer
 * is working in.
 *
 * Both are entered. Neither is derived. `ADR-0003` gives the four objectives
 * (Debut, then 60 / 300 / 300 at the ends of Junior, Classic and Senior) and
 * D-232 says surplus never carries, so each period is judged against zero. A tool
 * that inferred the period from a race date would be printing a guess as a
 * progress bar, and a date is exactly the guess the sources do not license: the
 * Global client shows the objective the career is on, it does not show a formula.
 *
 * The withholding rule is unchanged and is per period: an unpriced finish inside
 * the current period withholds that period's total, because `grade_point_by_grade`
 * prices a 1st place only. It does not poison the other periods.
 *
 * US-10, ADR-0003, D-270 (recorded, never derived), D-232, D-256, D-220.
 */
function periodRun(string $scenario = 'trackblazer'): TrainingRun
{
    return TrainingRun::factory()->create(['scenario' => $scenario]);
}

function gradeSlot(string $tier = 'G1'): ScenarioSlot
{
    // `goal_race` is the only slot kind that carries a tier, and the tier is what
    // prices the finish. Trackblazer has no fixed race list, so this row is a tier
    // carrier for the test, not a claim that the scenario has a mandatory race: that
    // the scenario is `trackblazer` is carried by the run's own `scenario` column,
    // which is what `gradeEarnedFor()` resolves its price table from.
    return ScenarioSlot::factory()->create([
        'scenario_key' => 'trackblazer',
        'kind' => 'goal_race',
        'tier' => $tier,
    ]);
}

function pricedFinish(TrainingRun $run, int $period, ScenarioSlot $slot, int $placement = 1): RaceEntry
{
    return RaceEntry::create([
        'training_run_id' => $run->id,
        'scenario_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => $placement,
        'objective_index' => $period,
    ]);
}

it('mass-assigns the two new period columns', function (): void {
    $run = periodRun();
    $slot = gradeSlot();

    $entry = pricedFinish($run, 2, $slot);
    $run::query()->whereKey($run->id)->update(['current_objective_index' => 3]);

    expect($entry->fresh()->objective_index)->toBe(2)
        ->and($run->fresh()->current_objective_index)->toBe(3);
});

it('keeps one period sum independent of the next', function (): void {
    $run = periodRun();
    $slot = gradeSlot();

    pricedFinish($run, 2, $slot);
    pricedFinish($run, 2, $slot);
    pricedFinish($run, 3, $slot);

    // 100 per G1 win. Period 2 holds two wins and period 3 one; no number on this
    // run is ever 300, because a running total would read as banking and D-232
    // says surplus dies at the deadline.
    expect($run->gradeEarnedFor(2))->toBe(200)
        ->and($run->gradeEarnedFor(3))->toBe(100)
        ->and($run->gradeEarnedFor(4))->toBeNull();
});

it('reports each period separately and never a cumulative total', function (): void {
    $run = periodRun();
    $slot = gradeSlot();

    pricedFinish($run, 2, $slot);
    pricedFinish($run, 3, $slot);
    pricedFinish($run, 3, $slot);

    $periods = $run->gradePeriods();

    expect($periods)->toHaveCount(4)
        ->and($periods[1]['earned'])->toBe(100)
        ->and($periods[2]['earned'])->toBe(200)
        ->and(array_sum(array_map(fn (array $p): int => $p['earned'] ?? 0, $periods)))->toBe(300)
        // The sum of all periods is a number the run does not display, so it must
        // not also be reachable as a period's own earned value.
        ->and($periods[0]['earned'])->toBeNull()
        ->and($periods[3]['earned'])->toBeNull();
});

it('withholds the total of the period holding an unpriceable finish, and only that period', function (): void {
    $run = periodRun();
    $slot = gradeSlot();

    pricedFinish($run, 2, $slot, placement: 2);
    pricedFinish($run, 3, $slot);

    expect($run->gradeEarnedFor(2))->toBeNull()
        ->and($run->gradeUnpricedFor(2))->toBe(1)
        ->and($run->gradeEarnedFor(3))->toBe(100)
        ->and($run->gradeUnpricedFor(3))->toBe(0);
});

it('counts a priced finish in the period it was entered against', function (): void {
    $run = periodRun();
    $slot = gradeSlot();

    // The same G1 win, entered against Classic rather than Junior, moves only
    // Classic. Nothing about the race's date or tier decides this.
    pricedFinish($run, 3, $slot);

    expect($run->gradeEarnedFor(2))->toBeNull()
        ->and($run->gradeEarnedFor(3))->toBe(100);
});

it('reports nothing while the Trainer has named no live period', function (): void {
    $run = periodRun();
    $slot = gradeSlot();

    pricedFinish($run, 2, $slot);
    expect($run->fresh()->current_objective_index)->toBeNull();

    // gradeEarned() is the figure the meter's headline prints. With no reported
    // period it is null, not 0 and not the sum of some period: D-220.
    expect($run->gradeEarned())->toBeNull();

    // An unreported period renders as "no period reported", never "Working toward"; the copy is the
    // meter's, and the props' null `current` is what selects it.
    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('gradeMeter.objectives', fn (Collection $objectives) => $objectives->isNotEmpty())
        ->whereNull('gradeMeter.current')
        ->whereNull('gradeMeter.earned'));
});

it('shows the reported period as the one being worked toward', function (): void {
    $run = periodRun();
    $run->update(['current_objective_index' => 3]);

    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        // The column is 1-based; the payload's `current` is a 0-based position into `objectives`,
        // which is the one place the two numberings meet.
        ->where('gradeMeter.current', 2)
        ->where('gradeMeter.objectives', fn (Collection $objectives) => $objectives->contains(
            fn (array $objective): bool => $objective['index'] === 3
                && $objective['name'] === 'End of Classic Year'
        )));
});

it('rejects an objective index outside the four periods', function (): void {
    $run = periodRun();
    $slot = gradeSlot();

    $this->expectException(Throwable::class);

    RaceEntry::create([
        'training_run_id' => $run->id,
        'scenario_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
        'objective_index' => 5,
    ]);
})->group('schema');

it('refuses a period on a scenario that composes no grade objectives', function (): void {
    $run = periodRun('ura_finale');
    $slot = ScenarioSlot::factory()->create(['scenario_key' => 'ura_finale']);

    $this->expectException(Throwable::class);

    RaceEntry::create([
        'training_run_id' => $run->id,
        'scenario_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
        'objective_index' => 2,
    ]);
});
