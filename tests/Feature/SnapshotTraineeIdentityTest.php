<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\Umamusume;

/*
 * U-03 / R8 (E-04 ruling) — a snapshot records the trainee's rarity and Potential Level, and the review
 * reads them back before the write.
 *
 * The two facts are not new: `RunSettings` has edited and persisted them since migration
 * `2026_10_10_120000_add_trainee_rarity_and_potential_to_training_runs`. What was missing was the door —
 * a Trainer entering a career already underway had to save the snapshot, then go to a second screen to
 * state two facts their client was showing them at that moment.
 *
 * **Two fields, never conflated.** `potential_level` is the client's own Potential Lvl (1-5, the
 * awakening the trainee carries into the run); the Growth Rate row is a different fact stored on the
 * turn (`stat_bonus` percentages). This test pins the former and says nothing about the latter.
 *
 * **The bounds are mirrored, not re-chosen.** Both rules copy `StoreTrainingRunRequest`, so the range
 * has one owner: ★1-★3 and 1-5. A value outside them is refused by the same numbers that refuse it on
 * the settings screen.
 */

function identitySnapshotPayload(array $overrides = []): array
{
    return [
        'umamusume_id' => Umamusume::factory()->create()->id,
        'scenario' => 'unity_cup',
        'career_year' => 3,
        'career_month' => 10,
        'career_phase' => 'Early',
        'trainee_rarity' => 3,
        'potential_level' => 2,
        'speed' => 607,
        'stamina' => 577,
        'power' => 549,
        'guts' => 736,
        'wit' => 397,
        ...$overrides,
    ];
}

it('records rarity and Potential Level on the run the snapshot creates', function (): void {
    $response = test()->post(route('career.snapshot.store'), identitySnapshotPayload());

    $run = TrainingRun::query()->latest('id')->firstOrFail();

    $response->assertRedirect();
    expect($run->trainee_rarity)->toBe(3)
        ->and($run->potential_level)->toBe(2);
});

it('reads both back on the review screen in the Known vocabulary', function (): void {
    $response = test()->post(route('career.snapshot.review'), identitySnapshotPayload());

    $response->assertOk();
    $fields = collect($response->inertiaProps('fields'));

    $rarity = $fields->firstWhere('key', 'trainee_rarity');
    $potential = $fields->firstWhere('key', 'potential_level');

    expect($rarity['display'])->toBe('Known · Rarity ★3')
        ->and($potential['display'])->toBe('Known · Potential Level 2');
});

it('names the scenario by its full client label on the review read-back', function (): void {
    $response = test()->post(route('career.snapshot.review'), identitySnapshotPayload());
    $fields = collect($response->inertiaProps('fields'));

    // The identity row keeps the short label every other surface prints; the client's own banner name
    // travels as its own prop so the two are never conflated.
    expect($fields->firstWhere('key', 'scenario')['display'])->toBe('Known · Scenario Unity Cup')
        ->and($response->inertiaProps('scenario_full_label'))->toBe('Unity Cup: Shine On, Team Spirit!');
});

it('refuses a rarity or potential outside the range the settings screen owns', function (array $override, string $field): void {
    test()->post(route('career.snapshot.store'), identitySnapshotPayload($override))
        ->assertSessionHasErrors($field);
})->with([
    'rarity 4' => [['trainee_rarity' => 4], 'trainee_rarity'],
    'potential 6' => [['potential_level' => 6], 'potential_level'],
]);

it('leaves both absent rather than defaulting them when the client does not show them', function (): void {
    test()->post(route('career.snapshot.store'), identitySnapshotPayload([
        'trainee_rarity' => null,
        'potential_level' => null,
    ]))->assertRedirect();

    $run = TrainingRun::query()->latest('id')->firstOrFail();

    expect($run->trainee_rarity)->toBeNull()
        ->and($run->potential_level)->toBeNull();
});
