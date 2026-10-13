<?php

declare(strict_types=1);

use App\Enums\TraineeGoalType;
use App\Services\DataPipeline\Parsers\GametoraUraObjectivesParser;

/**
 * @return array<string, mixed>
 */
function objectivesBody(): array
{
    return json_decode(
        (string) file_get_contents(database_path('seeders/data/ura-objectives.ead4bbfe.json')),
        true,
    );
}

/**
 * @param  list<array<string, mixed>>  $records
 * @return array<string, mixed>|null
 */
function findRecord(array $records, int $charId, int $turn, ?int $choice = null): ?array
{
    foreach ($records as $record) {
        if ($record['char_ref'] !== 'gametora:char:'.$charId || $record['turn'] !== $turn) {
            continue;
        }

        if ($choice !== null && $record['source_race_choice'] !== $choice) {
            continue;
        }

        return $record;
    }

    return null;
}

it('flattens the whole document to one record per objective', function (): void {
    $records = (new GametoraUraObjectivesParser)->parse(json_encode(objectivesBody()));

    // 135 characters, 1,701 objectives as published on 2026-10-13. Pinned because the parse is the only
    // place the ladder's shape is understood, and a republish that changed it must fail here rather than
    // store a half-read ladder.
    expect($records)->toBeArray()->and(count($records))->toBe(1701)
        ->and(count(array_unique(array_column($records, 'char_ref'))))->toBe(135);
});

it('reads a fan objective at the grain the client prints it', function (): void {
    $records = (new GametoraUraObjectivesParser)->parse(json_encode(objectivesBody()));
    $goal = findRecord($records, 1052, 37);

    // Haru Urara's second objective. A published [Global] client readout of this exact ladder says
    // "Have at least 5000 fans … Turn: 37 … Classic Class … Early July", and turn 37 has to land on
    // year 2, month 7, Early for that to be the same fact. This is the measurement that settled the
    // arithmetic, not an assumption about it.
    expect($goal)->not->toBeNull()
        ->and($goal['goal_type'])->toBe(TraineeGoalType::Fan->value)
        ->and($goal['fan_threshold'])->toBe(5000)
        ->and($goal['career_year'])->toBe(2)
        ->and($goal['race_slot'])->toBeNull()
        ->and($goal['source_race_name'])->toBeNull();
});

it('places a Senior Late March deadline on the month the calendar names', function (): void {
    $records = (new GametoraUraObjectivesParser)->parse(json_encode(objectivesBody()));

    // Rice Shower, turn 54: the capture says "Place top 3 in Nikkei Sho (Senior Late Mar)".
    $goal = findRecord($records, 1030, 54);
    expect($goal['goal_type'])->toBe(TraineeGoalType::Placement->value)
        ->and($goal['required_placement'])->toBe(3)
        ->and($goal['career_year'])->toBe(3)
        ->and($goal['race_slot'])->toBe([
            'year' => 3, 'month' => 3, 'half' => 'Late', 'title' => 'Nikkei Sho',
        ]);
});

it('keys the debut as a no-placing objective at Junior Late June', function (): void {
    $records = (new GametoraUraObjectivesParser)->parse(json_encode(objectivesBody()));
    $debut = findRecord($records, 1001, 12);

    expect($debut['goal_type'])->toBe(TraineeGoalType::Participate->value)
        ->and($debut['required_placement'])->toBe(0)
        ->and($debut['career_year'])->toBe(1)
        ->and($debut['race_slot']['month'])->toBe(6)
        ->and($debut['race_slot']['half'])->toBe('Late')
        ->and($debut['source_race_name'])->toBe('Junior Make Debut');
});

it('routes the three post-December final rounds away from the trainee ladder', function (): void {
    $records = (new GametoraUraObjectivesParser)->parse(json_encode(objectivesBody()));

    foreach ([74, 76, 78] as $turn) {
        $round = findRecord($records, 1030, $turn);
        expect($round['goal_type'])->toBe(TraineeGoalType::FinalRound->value)
            ->and($round['race_slot'])->toBeNull();
    }

    // 67 roster trainees times three rounds. These are the scenario's obligation, and
    // race_catalog_slots.is_mandatory already renders them.
    $finals = array_filter($records, fn (array $r): bool => $r['goal_type'] === TraineeGoalType::FinalRound->value);
    expect(count($finals))->toBe(135 * 3);
});

it('keeps two objectives that branch at one ladder order instead of merging them', function (): void {
    $records = (new GametoraUraObjectivesParser)->parse(json_encode(objectivesBody()));

    // char 1144 offers Arima Kinen or Nikkei Shinshun Hai at order 7. Both rows survive, because the
    // client offers a choice and one row would state a single obligation.
    $first = findRecord($records, 1144, 48, 1);
    $second = findRecord($records, 1144, 49, 2);

    expect($first)->not->toBeNull()->and($second)->not->toBeNull()
        ->and($first['source_order'])->toBe($second['source_order'])
        ->and($first['source_race_name'])->toBe('Arima Kinen')
        ->and($second['source_race_name'])->toBe('Nikkei Shinshun Hai');
});

it('carries the source\'s own not-released-on-Global marker instead of dropping the row', function (): void {
    $records = (new GametoraUraObjectivesParser)->parse(json_encode(objectivesBody()));
    $unreleased = array_values(array_filter(
        $records,
        fn (array $r): bool => $r['global_unreleased'] === true,
    ));

    expect($unreleased)->not->toBeEmpty()
        ->and($unreleased[0]['source_race_name'])->toBeString()
        ->and($unreleased[0]['race_slot'])->not->toBeNull();
});

it('keeps an unread condition as a row rather than inventing what it asks for', function (): void {
    $records = (new GametoraUraObjectivesParser)->parse(json_encode(objectivesBody()));

    // cond_type 2 (ten rows, five of them on trainees this tool holds: Oguri Cap, Agnes Digital, Tamamo
    // Cross, Smart Falcon, Matikanetannhauser) and cond_type 12 (one row) name a condition no capture or
    // guide line in this repository states. Nothing in the corpus says what the client prints for them, so
    // they stay rows with their raw figures rather than becoming a guessed requirement.
    $undecoded = array_values(array_filter(
        $records,
        fn (array $r): bool => $r['goal_type'] === TraineeGoalType::Undecoded->value,
    ));

    expect($undecoded)->toHaveCount(11)
        ->and(array_count_values(array_column($undecoded, 'source_cond_type')))->toBe(['2' => 10, '12' => 1])
        ->and($undecoded[0]['required_placement'])->toBeNull();
});

it('returns nothing for a body that is not the document it expects', function (string $body): void {
    expect((new GametoraUraObjectivesParser)->parse($body))->toBe([]);
})->with(['not json at all', '{"char_id":1001}', '[]', 'null']);
