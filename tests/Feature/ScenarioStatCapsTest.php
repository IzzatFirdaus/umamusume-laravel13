<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Services\ScenarioCaps;
use Illuminate\Support\ViewErrorBag;

/*
 * ADR-0015: a stat's ceiling is the run's own scenario ceiling, not a flat number.
 *
 * The bound was `between:0,1200` for all five stats since the legacy planner's
 * MAX_STAT_VALUE, while `config/scenarios.php` has always carried the per-stat bonus
 * each scenario adds to that base — the figure `x-stat-band` renders as the cap. So the
 * validator rejected numbers the same tool's own stat band displayed as reachable, and
 * ADR-0002 recorded the disagreement rather than resolving it.
 *
 * A run that names no scenario gets the base cap with no bonus. `scenarioKey()` resolves
 * null to the baseline so the strip has a descriptor to compose from, but a bonus is a
 * property of a scenario the Trainer chose, and granting URA Finale's +200 to a run that
 * never picked it would store a number the tool has no source for. The same refusal is
 * already law for the panels: `runs/show.blade.php` gates them on `hasScenario()` because
 * borrowing the baseline's schedule would describe races nobody selected (D-220, D-221).
 */

function capRun(?string $scenario): TrainingRun
{
    return TrainingRun::factory()->create(['scenario' => $scenario]);
}

function capPayload(array $overrides = []): array
{
    return array_merge([
        'turn' => 1, 'speed' => 10, 'stamina' => 10, 'power' => 10, 'guts' => 10, 'wit' => 10,
    ], $overrides);
}

it('accepts a stat at the ceiling its own scenario states', function (string $scenario, string $stat, int $ceiling): void {
    $run = capRun($scenario);

    test()->post("/training-runs/{$run->id}/turns", capPayload([$stat => $ceiling]))
        ->assertSessionHasNoErrors();
})->with([
    ['unity_cup', 'speed', 1300],       // 1200 base + 100, and Wit in the same scenario reaches 1800
    ['unity_cup', 'wit', 1800],
    ['ura_finale', 'speed', 1400],
    ['trackblazer', 'stamina', 1900],   // the widest bonus in the matrix
    ['trackblazer', 'wit', 1500],
    ['our_grand_concert', 'speed', 1600],
]);

it('rejects a stat one point past the ceiling its own scenario states', function (string $scenario, string $stat, int $ceiling): void {
    $run = capRun($scenario);

    test()->post("/training-runs/{$run->id}/turns", capPayload([$stat => $ceiling + 1]))
        ->assertSessionHasErrors($stat);
})->with([
    ['unity_cup', 'speed', 1300],
    ['unity_cup', 'wit', 1800],
    ['ura_finale', 'speed', 1400],
    ['trackblazer', 'stamina', 1900],
    ['our_grand_concert', 'guts', 1500],
]);

it('applies each stat ceiling to its own stat, not the widest one in the scenario', function (): void {
    $run = capRun('unity_cup');

    // Wit at 1800 is legal here and speed at 1800 is not: the pair proves the bound is
    // read per stat. A single max-per-scenario number would accept both.
    test()->post("/training-runs/{$run->id}/turns", capPayload(['wit' => 1800, 'speed' => 1800]))
        ->assertSessionHasErrors('speed');

    // The bag's keys are the assertion: `assertSessionHasNoErrors('wit')` would ignore the
    // argument and fail on the speed error that is supposed to be there.
    /** @var ViewErrorBag $bag */
    $bag = session('errors');

    expect(array_keys($bag->getBag('default')->toArray()))->toBe(['speed']);
});

it('holds a run with no scenario to the base cap and claims no bonus for it', function (): void {
    $run = capRun(null);

    expect($run->hasScenario())->toBeFalse()
        ->and($run->scenarioKey())->toBe('ura_finale');

    test()->post("/training-runs/{$run->id}/turns", capPayload(['speed' => 1200]))
        ->assertSessionHasNoErrors();

    test()->post("/training-runs/{$run->id}/turns", capPayload(['turn' => 2, 'speed' => 1201]))
        ->assertSessionHasErrors('speed');
});

it('never raises a ceiling above the engine hard cap', function (): void {
    $hardCap = (int) config('scenarios.hard_cap');

    foreach (array_keys(config('scenarios.scenarios')) as $key) {
        foreach (ScenarioCaps::caps($key) as $stat => $ceiling) {
            expect($ceiling)->toBeLessThanOrEqual($hardCap)
                ->and($ceiling)->toBeGreaterThan(0);
        }
    }
});

it('derives each ceiling from the base cap plus that scenario own bonus', function (): void {
    $base = (int) config('scenarios.base_cap');

    foreach (['ura_finale', 'unity_cup', 'trackblazer', 'our_grand_concert'] as $key) {
        $bonus = config("scenarios.scenarios.{$key}.cap_bonus");

        foreach (array_keys($bonus) as $stat) {
            expect(ScenarioCaps::stat($key, $stat))
                ->toBe(min($base + (int) $bonus[$stat], (int) config('scenarios.hard_cap')));
        }
    }
});

it('puts the scenario ceiling in the form, not a hardcoded 1200', function (): void {
    $run = capRun('trackblazer');

    // The browser pass found this: the server accepted Stamina 1900 and every stat input
    // carried max="1200", so the number was refused before the request was sent. A POST
    // test cannot see a native constraint, so the rendered attribute is what is asserted.
    $html = test()->get("/training-runs/{$run->id}")->assertOk()->getContent();

    expect($html)->toContain('name="stamina" min="0" max="1900"')
        ->and($html)->toContain('name="wit" min="0" max="1500"')
        ->and($html)->toContain('name="speed" min="0" max="1200"');

    // SP carries no ceiling in the view either: the field used to hold a recycled 1200.
    expect($html)->not->toContain('name="sp" min="0" max=');
});

it('holds both forms to the same ceilings the validator uses', function (): void {
    $run = capRun('unity_cup');

    $html = test()->get("/training-runs/{$run->id}")->assertOk()->getContent();

    // The guided rail and the raw escape hatch are separate markup. One max per stat is a
    // rule, not a per-form detail, so both must carry the scenario's number.
    $staminaMaxes = preg_match_all('/name="stamina"[^>]*max="(\d+)"/', $html, $m) ? $m[1] : [];

    expect($staminaMaxes)->not->toBeEmpty()
        ->and(array_unique($staminaMaxes))->toBe(['1300']);
});

it('puts the base cap in the form when the run names no scenario', function (): void {
    $run = capRun(null);

    $html = test()->get("/training-runs/{$run->id}")->assertOk()->getContent();

    expect($html)->toContain('name="speed" min="0" max="1200"');
});

it('keeps skill points open above the stat ceilings because no source states a ceiling', function (): void {
    $run = capRun('ura_finale');

    // SP is not one of the five rated stats and no reference in the corpus puts a cap on
    // it, so the bound stays the non-negative check 9b774f9 left. A ceiling here would be
    // an invented number; ADR-0015 records the absence instead.
    test()->post("/training-runs/{$run->id}/turns", capPayload(['sp' => 99999]))
        ->assertSessionHasNoErrors();

    test()->post("/training-runs/{$run->id}/turns", capPayload(['turn' => 2, 'sp' => -1]))
        ->assertSessionHasErrors('sp');
});
