<?php

declare(strict_types=1);

use App\Services\DataPipeline\Parsers\GametoraRaceFansParser;

it('returns empty array on invalid json', function (): void {
    expect((new GametoraRaceFansParser)->parse('not json'))->toBe([])
        ->and((new GametoraRaceFansParser)->parse('{"object":true}'))->toBe([]);
});

it('maps fans by order', function (): void {
    $body = json_encode([
        ['id' => 5, 'fans' => [['order' => 1, 'fans' => 100], ['order' => 2, 'fans' => 200], ['order' => 3, 'fans' => 300]]],
    ], JSON_THROW_ON_ERROR);

    $records = (new GametoraRaceFansParser)->parse($body);

    expect($records)->toHaveCount(1)
        ->and($records[0]['id'])->toBe(5)
        ->and($records[0]['first'])->toBe(100)
        ->and($records[0]['second'])->toBe(200)
        ->and($records[0]['third'])->toBe(300);
});
