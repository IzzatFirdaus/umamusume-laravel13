<?php

declare(strict_types=1);

use App\Services\DataPipeline\Parsers\GametoraRaceParser;

it('returns empty array on invalid json', function (): void {
    expect((new GametoraRaceParser)->parse('not json'))->toBe([])
        ->and((new GametoraRaceParser)->parse('{"object":true}'))->toBe([]);
});

it('maps race fields', function (): void {
    $body = json_encode([
        ['id' => 9, 'name_en' => 'Arima Kinen', 'name_ja' => '有馬記念', 'course' => 1, 'direction' => 2, 'season' => 3, 'entries' => 16, 'factor' => ['effect_1' => 'a', 'effect_2' => 'b'], 'track' => 4],
    ], JSON_THROW_ON_ERROR);

    $records = (new GametoraRaceParser)->parse($body);

    expect($records)->toHaveCount(1)
        ->and($records[0]['id'])->toBe(9)
        ->and($records[0]['name_en'])->toBe('Arima Kinen')
        ->and($records[0]['course'])->toBe(1)
        ->and($records[0]['direction'])->toBe(2)
        ->and($records[0]['season'])->toBe(3)
        ->and($records[0]['entries'])->toBe(16)
        ->and($records[0]['factor_effect_1'])->toBe('a')
        ->and($records[0]['factor_effect_2'])->toBe('b')
        ->and($records[0]['track_id'])->toBe(4);
});
