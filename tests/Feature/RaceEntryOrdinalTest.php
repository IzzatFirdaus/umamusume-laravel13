<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\TrainingRun;

/*
 * R69: a placement is read as an ordinal. The template concatenated `.'th'` onto every number,
 * so a win printed "1th". The teens are the part a last-digit rule loses, which is why 11/12/13
 * are in the table beside 21/22/23 rather than being assumed from 1/2/3.
 */

it('labels a placement with the English ordinal suffix', function (int $placement, string $expected): void {
    expect((new RaceEntry(['placement' => $placement]))->placementOrdinal())->toBe($expected);
})->with([
    [1, '1st'],
    [2, '2nd'],
    [3, '3rd'],
    [4, '4th'],
    [5, '5th'],
    [10, '10th'],
    [11, '11th'],
    [12, '12th'],
    [13, '13th'],
    [21, '21st'],
    [22, '22nd'],
    [23, '23rd'],
]);

it('says nothing was recorded rather than showing a zeroth place', function (): void {
    expect((new RaceEntry(['placement' => null]))->placementOrdinal())->toBe('no placement');
});

it('prints the ordinal on the recorded race row, not a bare number with th glued on', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $slot = ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'goal_race',
        'source_key' => 'src-ord',
        'title' => 'Japanese Oaks',
        'is_manual' => false,
    ]);
    RaceEntry::create([
        'training_run_id' => $run->id,
        'scenario_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
    ]);

    $html = $this->get(route('runs.cockpit', $run))->content();

    expect($html)->toContain('1st')
        ->and($html)->not->toContain('1th');
});
