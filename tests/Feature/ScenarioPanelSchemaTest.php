<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\TrainingRun;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Slice 8 T1: the two columns the Unity Cup and Trackblazer panels need, both entered
 * and neither derived.
 *
 * `race_entries.circles` is what the Trainer saw on the client's own estimate display
 * before committing a Team Race (D-225, Planner Rule 1). The tool records it; it never
 * computes it, and `circles_per_category` is deliberately absent from config so no
 * component is tempted to do the client's arithmetic.
 *
 * The 0..5 bound is the owner's ruling for this slice. The corpus names 3 circles as a
 * safety margin (`config/scenarios.php` `team_race.circles_guidance`, from
 * docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md section "Unity Cup Team Races") and never names a maximum, so the upper bound is a validation
 * ceiling, not a claimed game fact, and nothing renders it as a meter of five.
 *
 * `training_runs.shop_resets_in` is the countdown the shop panel shows. It is entered,
 * and null renders the N/A disclosure: the rotation is not modelled by this build, so a
 * computed countdown would be a prediction (D-232, Planner Rule 5).
 */
function panelRun(string $scenario = 'unity_cup'): TrainingRun
{
    return TrainingRun::factory()->create(['scenario' => $scenario]);
}

function teamRaceSlot(string $scenario = 'unity_cup'): ScenarioSlot
{
    return ScenarioSlot::factory()->create([
        'scenario_key' => $scenario,
        'kind' => 'team_race',
        'title' => 'Team Race',
        'month' => 6,
        'half' => 'Late',
    ]);
}

function goalSlot(string $scenario = 'unity_cup'): ScenarioSlot
{
    return ScenarioSlot::factory()->create([
        'scenario_key' => $scenario,
        'kind' => 'goal_race',
        'month' => 4,
        'half' => 'Early',
    ]);
}

function circlesEntry(TrainingRun $run, ScenarioSlot $slot, ?int $circles): RaceEntry
{
    return RaceEntry::create([
        'training_run_id' => $run->id,
        'scenario_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
        'circles' => $circles,
    ]);
}

it('mass-assigns circles onto a team race entry', function (): void {
    $run = panelRun();
    $entry = circlesEntry($run, teamRaceSlot(), 3);

    expect($entry->fresh()->circles)->toBe(3);
});

it('accepts every circle count the owner bounded and rejects the next one', function (): void {
    $run = panelRun();
    $slot = teamRaceSlot();

    foreach (range(0, 5) as $circles) {
        expect(circlesEntry($run, $slot, $circles)->fresh()->circles)->toBe($circles);
    }

    $this->expectException(InvalidArgumentException::class);
    circlesEntry($run, $slot, 6);
});

it('refuses circles on a slot that is not a team race', function (): void {
    $run = panelRun();

    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('only be entered against a team race slot');

    circlesEntry($run, goalSlot(), 3);
});

it('keeps circles absent rather than zero on a race with no team circles', function (): void {
    $run = panelRun();
    $entry = circlesEntry($run, goalSlot(), null);

    expect($entry->fresh()->circles)->toBeNull();
});

it('mass-assigns the shop countdown and stays null until it is said', function (): void {
    $run = panelRun('trackblazer');

    expect($run->fresh()->shop_resets_in)->toBeNull();

    $run->update(['shop_resets_in' => 4]);
    expect($run->fresh()->shop_resets_in)->toBe(4);

    $this->expectException(InvalidArgumentException::class);
    $run->update(['shop_resets_in' => 300]);
});

it('refuses a shop countdown on a scenario with no shop', function (): void {
    $run = panelRun('ura_finale');

    $this->expectException(InvalidArgumentException::class);
    $run->update(['shop_resets_in' => 2]);
});

it('renders the shop countdown as entered and never as a computed number', function (): void {
    $run = panelRun('trackblazer');
    $run->update(['shop_resets_in' => 4]);

    // The countdown reaches the shop panel as `shop.resetsInLabel`, composed on the server from
    // the entered figure. The exact words ShopPanel prints around it are the component's; that the
    // entered 4 becomes "resets in 4 turns" and not a computed number is the prop's claim.
    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('shop.resetsInLabel', 'resets in 4 turns'));

    $unset = panelRun('trackblazer');

    // Null is a disclosure, not a zero: with no countdown entered the label is absent, so nothing
    // on the page can read "resets in 0 turns". The "countdown not recorded" copy the panel shows
    // for that null is the browser's.
    $this->get('/training-runs/'.$unset->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->whereNull('shop.resetsInLabel'));
});
