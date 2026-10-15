<?php

declare(strict_types=1);

use App\Enums\TraineeGoalType;
use App\Models\TraineeGoal;
use App\Models\Umamusume;

test('every goal_type has a factory state or path', function (): void {
    // Placement - default
    $placement = TraineeGoal::factory()->create();
    expect($placement->goal_type)->toBe(TraineeGoalType::Placement);

    // Participate - factory state
    $participate = TraineeGoal::factory()->participate()->create();
    expect($participate->goal_type)->toBe(TraineeGoalType::Participate);

    // Fan - factory state
    $fan = TraineeGoal::factory()->fan()->create();
    expect($fan->goal_type)->toBe(TraineeGoalType::Fan);

    // FinalRound - explicit create
    $final = TraineeGoal::factory()->create(['goal_type' => TraineeGoalType::FinalRound]);
    expect($final->goal_type)->toBe(TraineeGoalType::FinalRound);

    // Undecoded - factory state
    $undecoded = TraineeGoal::factory()->undecoded()->create();
    expect($undecoded->goal_type)->toBe(TraineeGoalType::Undecoded);
});

test('Placement goal type returns expected requirementPhrase for confirmed figures', function (): void {
    expect(TraineeGoal::factory()->create(['required_placement' => 1])->requirementPhrase())->toBe('Place 1st');
    expect(TraineeGoal::factory()->create(['required_placement' => 3])->requirementPhrase())->toBe('Place top 3');
    expect(TraineeGoal::factory()->create(['required_placement' => 5])->requirementPhrase())->toBe('Place top 5');
});

test('Placement goal type returns null for unconfirmed figures (2, 4)', function (): void {
    expect(TraineeGoal::factory()->create(['required_placement' => 2])->requirementPhrase())->toBeNull();
    expect(TraineeGoal::factory()->create(['required_placement' => 4])->requirementPhrase())->toBeNull();
    // The kind stays Placement
    expect(TraineeGoal::factory()->create(['required_placement' => 2])->goal_type)->toBe(TraineeGoalType::Placement);
    expect(TraineeGoal::factory()->create(['required_placement' => 4])->goal_type)->toBe(TraineeGoalType::Placement);
});

test('Participate returns expected requirementPhrase', function (): void {
    expect(TraineeGoal::factory()->participate()->create()->requirementPhrase())->toBe('Participate');
});

test('Fan returns client banner form without thousands separator', function (): void {
    $goal = TraineeGoal::factory()->fan(5000, 37)->create();
    expect($goal->requirementPhrase())->toBe('Earn 5000 fans');
    expect($goal->requirementPhrase())->not->toContain(',');
});

test('FinalRound returns null', function (): void {
    expect(TraineeGoal::factory()->create(['goal_type' => TraineeGoalType::FinalRound])->requirementPhrase())->toBeNull();
});

test('Undecoded returns null and names itself as undecoded', function (): void {
    $goal = TraineeGoal::factory()->undecoded()->create();
    expect($goal->requirementPhrase())->toBeNull();
    expect($goal->goal_type)->toBe(TraineeGoalType::Undecoded);
    // Raw source figures present
    expect($goal->source_cond_type)->toBe(2);
    expect($goal->source_cond_value)->toBe(3);
});

test('cv=2 placement case is pinned as not decoded, not inferred', function (): void {
    $goal = TraineeGoal::factory()->create([
        'required_placement' => 2,
        'source_cond_value' => 2,
    ]);
    // The ADR ruling: "Place top 2" is not a client string, so the phrase is null
    expect($goal->requirementPhrase())->toBeNull();
    expect($goal->isDecoded())->toBeFalse();
    // The absenceReason should mention the raw figures
    $reason = $goal->absenceReason();
    expect($reason)->not->toBeNull();
    expect($reason)->toContain('condition type 1');
    expect($reason)->toContain('value 2');
});

test('mutation: changing requirementPhrase to return Place top 2 for cv=2 fails', function (): void {
    // This test documents that the behavior is pinned
    $goal = TraineeGoal::factory()->create(['required_placement' => 2]);
    expect($goal->requirementPhrase())->toBeNull(); // If this changes, the test fails
});