<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\TraineeGoal;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;

/*
 * The three Cockpit defects reproduced live on 2026-10-15 against the seeded URA runs, each with the
 * page that printed the wrong answer as its own evidence.
 *
 * **The header counted rows.** `CockpitController::headerSection()` read `turnEntries->count()` while the
 * correction form beside it read the latest entry's own `turn` column. A snapshot career logged one entry,
 * at turn 75, so the header printed `TURN 1` next to a dropdown reading `Turn 75`. Now both read
 * `careerPosition()`, which answers the named position, else the position derived from the highest logged
 * turn, and the count is gone.
 *
 * **A recorded race is not due.** A trainee objective whose race slot the run has already entered raised
 * `is due and not met` when the placement was never typed in. The absence of a placement is an unproven
 * requirement, not an outstanding race, and the mandatory group already had this guard while the trainee
 * group did not.
 *
 * **An ordinal deadline is not a current one.** `isAtOrBeforeTurn()` compares one career-wide turn index,
 * so an unrecorded Junior debut stays "reached" for the rest of the career and a Senior-year run banners
 * it every turn. Both alert sites now also require the calendar year to be the year the run stands in. The
 * ladder rows keep their own states; only the alert is scoped, because the alert claims a door is still
 * open. The second half of each case is the guard against over-suppression: a same-year obligation that
 * really is outstanding still alerts.
 */

function scopedRun(RunStatus $status = RunStatus::Active, int $lastLoggedTurn = 67): TrainingRun
{
    $run = TrainingRun::factory()->create([
        'scenario' => 'ura_finale',
        'status' => $status,
        'umamusume_id' => Umamusume::factory()->create()->id,
    ]);

    for ($turn = 1; $turn <= $lastLoggedTurn; $turn++) {
        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => $turn]);
    }

    return $run->fresh();
}

/**
 * @return list<array{level: string, text: string, detail: string}>
 */
function scopedAlerts(TrainingRun $run): array
{
    /** @var list<array{level: string, text: string, detail: string}> $alerts */
    $alerts = test()->get(route('runs.cockpit', $run))->assertOk()->inertiaProps('scenario.alerts');

    return $alerts;
}

function scopedSlot(string $title, int $year, ?int $turn, bool $mandatory): RaceCatalogSlot
{
    return RaceCatalogSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'year' => $year,
        'month' => 4,
        'half' => 'Early',
        'turn' => $turn,
        'slot_label' => $title,
        'title' => $title,
        'tier' => 'G1',
        'is_mandatory' => $mandatory,
        'fans_needed' => null,
    ]);
}

it('reads the header turn from the turn log, so it agrees with the correction form it sits beside', function (): void {
    // The reproduction: one logged entry at turn 75, which is 1 row and turn 75 at once.
    $run = TrainingRun::factory()->create([
        'scenario' => 'ura_finale',
        'status' => RunStatus::Completed,
        'umamusume_id' => Umamusume::factory()->create()->id,
    ]);
    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 75]);

    /** @var array{turn: int} $header */
    $header = $this->get(route('runs.cockpit', $run->fresh()))->assertOk()->inertiaProps('header');
    /** @var array{turn: int} $correction */
    $correction = $this->get(route('runs.cockpit', $run->fresh()))->assertOk()->inertiaProps('correction');

    expect($header['turn'])->toBe(75)
        ->and($correction['turn'])->toBe(75);

    // A run that logged every turn from 1 to 12 stands on turn 12, not on 12 rows of anything else.
    $contiguous = scopedRun(RunStatus::Active, 12);

    /** @var array{turn: int} $contiguousHeader */
    $contiguousHeader = $this->get(route('runs.cockpit', $contiguous))->assertOk()->inertiaProps('header');
    /** @var array{turn: int} $contiguousCorrection */
    $contiguousCorrection = $this->get(route('runs.cockpit', $contiguous))->assertOk()->inertiaProps('correction');

    expect($contiguousHeader['turn'])->toBe(12)
        ->and($contiguousCorrection['turn'])->toBe(12);
});

it('raises no due alert for a trainee objective whose race the run already entered', function (): void {
    $run = scopedRun();
    $slot = scopedSlot('Nikkei Sho', RaceCatalogSlot::YEAR_SENIOR, 6, false);

    TraineeGoal::factory()->create([
        'umamusume_id' => $run->umamusume_id,
        'race_catalog_slot_id' => $slot->id,
        'career_year' => RaceCatalogSlot::YEAR_SENIOR,
        'turn' => 54,
        'required_placement' => 3,
    ]);

    // The race ran. Its placement was never typed in, so the requirement is unproven rather than
    // outstanding, and `is due` is the wrong claim.
    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => null,
    ]);

    expect(collect(scopedAlerts($run->fresh()))->pluck('text'))
        ->not->toContain('Nikkei Sho is due and not met');

    // Over-suppression guard: the same objective with no entry at all is still outstanding.
    $unran = scopedRun();
    $openSlot = scopedSlot('Yasuda Kinen', RaceCatalogSlot::YEAR_SENIOR, 11, false);

    TraineeGoal::factory()->create([
        'umamusume_id' => $unran->umamusume_id,
        'race_catalog_slot_id' => $openSlot->id,
        'career_year' => RaceCatalogSlot::YEAR_SENIOR,
        'turn' => 59,
        'required_placement' => 1,
    ]);

    expect(collect(scopedAlerts($unran->fresh()))->pluck('text'))
        ->toContain('Yasuda Kinen is due and not met');
});

it('does not banner a mandatory race from a career year the run has left behind', function (): void {
    $run = scopedRun();
    scopedSlot('Junior Make Debut', RaceCatalogSlot::YEAR_JUNIOR, 12, true);

    $texts = collect(scopedAlerts($run->fresh()))->pluck('text');

    expect($texts)->not->toContain('Junior Make Debut is due and not recorded');

    // Over-suppression guard: a mandatory race inside the year the run stands in still alerts. The run
    // has logged turns 1 to 67, so the turn being decided is Senior year turn 20, and a slot at turn 19
    // of that same year has arrived with nothing recorded for it.
    $senior = scopedRun();
    scopedSlot('Tenno Sho (Autumn)', RaceCatalogSlot::YEAR_SENIOR, 19, true);

    expect(collect(scopedAlerts($senior->fresh()))->pluck('text'))
        ->toContain('Tenno Sho (Autumn) is due and not recorded');
});

it('raises no mandatory-race alert at all on a finished career', function (): void {
    // The nine-alert symptom was reported on a Completed run. A finished career has no turn left to be
    // due in, so the advisor must print nothing whatever the calendar holds.
    $run = scopedRun(RunStatus::Completed, 67);
    scopedSlot('Junior Make Debut', RaceCatalogSlot::YEAR_JUNIOR, 12, true);
    scopedSlot('Finals Qualifier', RaceCatalogSlot::YEAR_SENIOR, 20, true);

    expect(scopedAlerts($run->fresh()))->toBe([]);
});
