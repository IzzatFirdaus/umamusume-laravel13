<?php

declare(strict_types=1);

use App\Models\RaceCatalogSlot;
use App\Models\TraineeGoal;
use App\Models\TrainingRun;
use App\Models\TurnEntry;

/*
 * U-05b / R8 — a trainee goal row prints the fan gate its race carries (walk-9 S16).
 *
 * The client's Goals panel attaches a fan criterion to a race objective: `Place top 3 in Nikkei Sho` is
 * also a fan threshold to hold at that race (`SR` §1.15, the Goals panel; frames transcribed in walk-9
 * §2.3g). The Cockpit's ladder printed the placement and the deadline but not the gate, so a trainer
 * reading the row could not see the condition the client prints beside it.
 *
 * **The gate is read off the race's own calendar row, never derived.** `race_catalog_slots.fans_needed`
 * is the per-slot stored value the race card already prints as `Fan gate: N fans.`; the reference
 * publishes fan *ranges* per grade (§1.2.6: G1 1,000–25,000), so a grade-to-threshold lookup would
 * fabricate a number the client never showed. One owner for the value, two readers.
 *
 * **Absence is a value, not a zero.** A slot with no gate (all seven mandatory slots at this HEAD carry
 * `fans_needed = 0`) prints `N/A` carrying a `title`, which is the repository's absence contract, never
 * `0 fans`.
 */

function fanGateRun(): TrainingRun
{
    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup', 'status' => 'Active']);
    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 66, 'fans' => 209245]);

    return $run->fresh();
}

function fanGateGoal(TrainingRun $run, string $raceTitle, int $fansNeeded): TraineeGoal
{
    $slot = RaceCatalogSlot::factory()->create([
        'scenario_key' => null,
        'title' => $raceTitle,
        'year' => 3,
        'month' => 3,
        'half' => 'Late',
        'turn' => 6,
        'fans_needed' => $fansNeeded,
    ]);

    return TraineeGoal::factory()->create([
        'umamusume_id' => $run->umamusume_id,
        'race_catalog_slot_id' => $slot->id,
        'source_cond_id' => random_int(1, 1_000_000),
        'career_year' => 3,
        'turn' => 6,
        'source_order' => 1,
    ]);
}

it('prints the fan gate its race carries, read off the calendar row', function (): void {
    $run = fanGateRun();
    fanGateGoal($run, 'Nikkei Sho', 2000);

    $rows = test()->get(route('runs.cockpit', $run))
        ->assertOk()
        ->inertiaProps('scenario.objectives')[0]['rows'];

    expect($rows[0]['fan_gate'])->toBe(2000)
        ->and($rows[0]['fan_gate_label'])->toBe('Fan gate: 2,000 fans.');
});

it('prints N/A with a title when the race carries no gate, never a zero', function (): void {
    $run = fanGateRun();
    fanGateGoal($run, 'Junior Make Debut', 0);

    $rows = test()->get(route('runs.cockpit', $run))
        ->assertOk()
        ->inertiaProps('scenario.objectives')[0]['rows'];

    expect($rows[0]['fan_gate'])->toBeNull()
        ->and($rows[0]['fan_gate_label'])->toBe('N/A')
        ->and($rows[0]['fan_gate_title'])->toContain('no fan gate');
});

it('moves the gate with the stored column rather than a hard-coded number', function (): void {
    $run = fanGateRun();
    $goal = fanGateGoal($run, 'Tenno Sho (Spring)', 20000);

    RaceCatalogSlot::query()->whereKey($goal->race_catalog_slot_id)->update(['fans_needed' => 91000]);

    $rows = test()->get(route('runs.cockpit', $run))
        ->assertOk()
        ->inertiaProps('scenario.objectives')[0]['rows'];

    expect($rows[0]['fan_gate'])->toBe(91000)
        ->and($rows[0]['fan_gate_label'])->toBe('Fan gate: 91,000 fans.');
});
