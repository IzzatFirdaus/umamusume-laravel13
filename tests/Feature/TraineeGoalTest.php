<?php

declare(strict_types=1);

use App\Enums\TraineeGoalType;
use App\Models\TraineeGoal;
use App\Models\Umamusume;
use Illuminate\Database\QueryException;

it('states the three placings the [Global] client captures confirm', function (): void {
    expect(TraineeGoal::factory()->create(['required_placement' => 1]))->requirementPhrase()->toBe('Place 1st')
        ->and(TraineeGoal::factory()->create(['required_placement' => 3]))->requirementPhrase()->toBe('Place top 3')
        ->and(TraineeGoal::factory()->create(['required_placement' => 5]))->requirementPhrase()->toBe('Place top 5');
});

it('leaves a placing figure it has no capture for undecoded rather than guessing the words', function (int $placement): void {
    $goal = TraineeGoal::factory()->create(['required_placement' => $placement]);

    // The kind is known - this is a placing requirement - so the row stays Placement and only the
    // phrase is withheld. Inventing "Place top 2" would be a client string with no source (D-20).
    expect($goal->goal_type)->toBe(TraineeGoalType::Placement)
        ->and($goal->requirementPhrase())->toBeNull()
        ->and($goal->isDecoded())->toBeFalse();
})->with([2, 4, 6]);

it('prints a fan objective in the client banner form with no thousands separator', function (): void {
    $goal = TraineeGoal::factory()->fan(5000, 37)->create();

    // "Earn 3000 fans" is what the [Global] goal banner reads (screenshot-notes/01:15); the guide's
    // "Have at least 5000 fans" is the same fact in second-hand words and is not what we print.
    expect($goal->goal_type)->toBe(TraineeGoalType::Fan)
        ->and($goal->requirementPhrase())->toBe('Earn 5000 fans')
        ->and($goal->fan_threshold)->toBe(5000)
        ->and($goal->race_catalog_slot_id)->toBeNull()
        ->and($goal->career_year)->toBe(2);
});

it('calls a no-placing objective Participate', function (): void {
    $goal = TraineeGoal::factory()->participate()->create();

    expect($goal->requirementPhrase())->toBe('Participate')->and($goal->isDecoded())->toBeTrue();
});

it('withholds the final rounds and the unread kinds instead of printing an empty requirement', function (string $method): void {
    $goal = $method === 'final'
        ? TraineeGoal::factory()->create(['goal_type' => TraineeGoalType::FinalRound, 'required_placement' => 1])
        : TraineeGoal::factory()->undecoded()->create();

    expect($goal->requirementPhrase())->toBeNull()->and($goal->isDecoded())->toBeFalse();
})->with(['final', 'undecoded']);

it('refuses a second objective at the same identity grain', function (): void {
    $first = TraineeGoal::factory()->create([
        'umamusume_id' => Umamusume::factory(),
        'source_cond_id' => 81,
        'turn' => 54,
        'source_race_choice' => 0,
    ]);

    expect(fn (): TraineeGoal => TraineeGoal::factory()->create([
        'umamusume_id' => $first->umamusume_id,
        'source_cond_id' => 81,
        'turn' => 54,
        'source_race_choice' => 0,
    ]))->toThrow(QueryException::class);
});

it('keeps two objectives that share an order but branch, which is what the choice column is for', function (): void {
    $trainee = Umamusume::factory();

    $a = TraineeGoal::factory()->create([
        'umamusume_id' => $trainee, 'source_order' => 7, 'source_cond_id' => 81, 'turn' => 48, 'source_race_choice' => 1,
    ]);
    $b = TraineeGoal::factory()->create([
        'umamusume_id' => $a->umamusume_id, 'source_order' => 7, 'source_cond_id' => 79, 'turn' => 49, 'source_race_choice' => 2,
    ]);

    expect(TraineeGoal::query()->where('umamusume_id', $a->umamusume_id)->count())->toBe(2)
        ->and($a->source_order)->toBe($b->source_order);
});

it('carries the engine stop sign and the provenance set the reference tables require', function (): void {
    $goal = TraineeGoal::factory()->notOnGlobal()->create();

    expect($goal->is_manual)->toBeFalse()
        ->and($goal->global_unreleased)->toBeTrue()
        ->and($goal->source_race_name)->toBe('Queen Elizabeth Cup')
        ->and(str_starts_with($goal->source_url, 'https://gametora.com/data/umamusume/'))->toBeTrue()
        ->and($goal->source_timezone)->toBe('Asia/Tokyo')
        ->and($goal->fetched_at)->not->toBeNull();
});
