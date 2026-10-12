<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Cross-scenario isolation and update preservation (plan Slice 6.2), parameterized over the four
 * Global scenarios the composition matrix carries.
 *
 * The distributed files own each scenario's own reads and writes: the panel payloads, the team
 * editor's null-vs-zero cases (`CockpitTeamPanelTest`, `CockpitUnityCupCountersTest`), the
 * Trackblazer period form and the URA strip. What this file adds is the cross-scenario shape: the
 * panel map a Cockpit payload carries is the matrix's answer for ITS OWN scenario and nobody
 * else's; a partial update preserves what it omits on every scenario alike; and null, zero and
 * cleared hold apart on the columns every scenario shares. A failure here is a leak between
 * scenarios, not a defect in any one panel.
 */

it('composes each scenario panels from the matrix alone, and no other scenario panels', function (string $scenario): void {
    $run = TrainingRun::factory()->create([
        'scenario' => $scenario,
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->create()->id,
    ]);

    /** @var array<string, mixed> $definition */
    $definition = config('scenarios.scenarios.'.$scenario);
    /** @var array<string, string> $labels */
    $labels = config('scenarios.panel_labels');

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(function (Assert $page) use ($definition, $labels): void {
            foreach (array_keys($labels) as $flag) {
                // The payload's flag is the matrix's own answer for this run's scenario: the same
                // read `CockpitController` performs (G-33), asserted here so a payload that grew a
                // flag config does not own fails the suite before a screen draws it.
                $expected = (($definition['panels'][$flag] ?? false) === true);

                $page->where('scenario.panels.'.$flag.'.on', $expected);
            }
        });
})->with(['ura_finale', 'unity_cup', 'trackblazer', 'our_grand_concert']);

it('keeps every field a partial update omits, including the scenario, on every scenario', function (string $scenario): void {
    $run = TrainingRun::factory()->create([
        'scenario' => $scenario,
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->create()->id,
        'notes' => 'Carried through the slice.',
        'trainee_rarity' => 3,
        'growth_rate' => ['Speed' => 10],
    ]);

    // The pair the request requires and nothing else: every omitted column keeps its value, and the
    // scenario field itself is not reset by an update that does not name it.
    $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'status' => RunStatus::Active->value,
    ])->assertSessionHasNoErrors();

    $fresh = $run->fresh();

    expect($fresh->scenario)->toBe($scenario)
        ->and($fresh->notes)->toBe('Carried through the slice.')
        ->and($fresh->trainee_rarity)->toBe(3)
        ->and($fresh->growth_rate)->toBe(['Speed' => 10]);
})->with(['ura_finale', 'unity_cup', 'trackblazer', 'our_grand_concert']);

it('holds null zero and cleared apart on the columns every scenario shares', function (string $scenario): void {
    $run = TrainingRun::factory()->create([
        'scenario' => $scenario,
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->create()->id,
    ]);

    $put = fn (array $fields) => $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'status' => RunStatus::Active->value,
        ...$fields,
    ])->assertSessionHasNoErrors();

    // Zero is a figure the Trainer read off the card: it stores as zero and reads back as zero,
    // including beside a stated figure for another stat.
    $put(['growth_rate' => ['Speed' => 0]]);
    expect($run->fresh()->growth_rate)->toBe(['Speed' => 0]);

    $put(['growth_rate' => ['Speed' => 10, 'Wit' => 5]]);
    expect($run->fresh()->growth_rate)->toBe(['Speed' => 10, 'Wit' => 5]);

    // Clearing the bag records unstated, not a bag of zeroes (D-220).
    $put(['growth_rate' => null]);
    expect($run->fresh()->growth_rate)->toBeNull();

    // The rarity behaves the same way, with the sharper boundary that zero is not a rarity at all:
    // stated, then cleared back to unstated, never defaulted.
    $put(['trainee_rarity' => 3]);
    expect($run->fresh()->trainee_rarity)->toBe(3);

    $put(['trainee_rarity' => null]);
    expect($run->fresh()->trainee_rarity)->toBeNull();
})->with(['ura_finale', 'unity_cup', 'trackblazer', 'our_grand_concert']);
