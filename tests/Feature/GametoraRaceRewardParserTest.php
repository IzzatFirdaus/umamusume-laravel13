<?php

declare(strict_types=1);

use App\Services\DataPipeline\Parsers\GametoraRaceRewardParser;

it('returns empty array on invalid json', function (): void {
    expect((new GametoraRaceRewardParser)->parse('not json'))->toBe([])
        ->and((new GametoraRaceRewardParser)->parse('{"object":true}'))->toBe([]);
});

it('maps rewards', function (): void {
    $body = json_encode([
        ['id' => 7, 'rewards_combined' => [['item' => 'x', 'num_min' => 1, 'num_max' => 2, 'chance' => 0.5]]],
    ], JSON_THROW_ON_ERROR);

    $records = (new GametoraRaceRewardParser)->parse($body);

    expect($records)->toHaveCount(1)
        ->and($records[0]['id'])->toBe(7)
        ->and($records[0]['rewards'])->toBe([['item' => 'x', 'num_min' => 1, 'num_max' => 2, 'chance' => 0.5]]);
});
