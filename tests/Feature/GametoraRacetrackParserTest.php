<?php

declare(strict_types=1);

use App\Services\DataPipeline\Parsers\GametoraRacetrackParser;

it('returns empty array on invalid json', function (): void {
    expect((new GametoraRacetrackParser)->parse('not json'))->toBe([])
        ->and((new GametoraRacetrackParser)->parse('{"object":true}'))->toBe([]);
});

it('filters to objects with an id key', function (): void {
    $body = json_encode([
        ['id' => 1, 'name_en' => 'Tokyo', 'name_ja' => 'Tokyo', 'country' => 'JP'],
        ['name_en' => 'No Id'],
    ], JSON_THROW_ON_ERROR);

    $records = (new GametoraRacetrackParser)->parse($body);

    expect($records)->toHaveCount(1)
        ->and($records[0]['id'])->toBe(1)
        ->and($records[0]['name_en'])->toBe('Tokyo');
});
