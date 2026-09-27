<?php

declare(strict_types=1);

use App\Services\DataPipeline\Parsers\GametoraScenarioParser;

/**
 * @param  array<string, mixed>  $overrides
 */
function scenarioRecord(array $overrides = []): array
{
    $base = [
        'id' => 1,
        'order' => 1,
        'url_name' => 'ura-finale',
        'name_ja' => '新設！URAファイナルズ',
        'name_en' => 'URA Finale',
        'stats' => [200, 200, 200, 200, 200],
        'hard_caps' => [2000, 2000, 2000, 2000, 2000, 9999],
        'start_ja' => 1614139200,
        'start_en' => 1750897800,
    ];

    $body = json_encode([array_merge($base, $overrides)], JSON_THROW_ON_ERROR);

    return (new GametoraScenarioParser)->parse($body)[0] ?? [];
}

it('derives each scenario cap from the base plus the published bonus', function (): void {
    $record = scenarioRecord();

    expect($record['slug'])->toBe('ura-finale')
        ->and($record['name'])->toBe('URA Finale')
        ->and($record['cap_speed'])->toBe(1400)
        ->and($record['cap_wit'])->toBe(1400)
        ->and($record['hard_cap'])->toBe(2000);
});

it('reproduces the four Global scenario cap rows the reference document records', function (string $name, string $slug, array $bonus, array $caps): void {
    $record = scenarioRecord([
        'url_name' => $slug,
        'name_en' => $name,
        'stats' => $bonus,
        'hard_caps' => array_fill(0, 5, 2000) + [9999],
    ]);

    expect([
        $record['cap_speed'], $record['cap_stamina'], $record['cap_power'],
        $record['cap_guts'], $record['cap_wit'],
    ])->toBe($caps);
})->with([
    ['URA Finale', 'ura-finale', [200, 200, 200, 200, 200], [1400, 1400, 1400, 1400, 1400]],
    ['Unity Cup', 'unity-cup', [100, 100, 100, 100, 600], [1300, 1300, 1300, 1300, 1800]],
    ['Trackblazer', 'trackblazer', [0, 700, 0, 0, 300], [1200, 1900, 1200, 1200, 1500]],
    ['Our Grand Concert', 'grand-concert', [400, 100, 100, 300, 100], [1600, 1300, 1300, 1500, 1300]],
]);

it('dates the cap rework only for scenarios that launched on Global before it', function (): void {
    expect(scenarioRecord(['start_en' => 1750897800])['caps_reworked_at'])->toBe('2026-07-01')
        ->and(scenarioRecord(['start_en' => strtotime('2026-08-01')])['caps_reworked_at'])->toBeNull()
        ->and(scenarioRecord(['start_en' => null])['global_start_date'])->toBeNull()
        ->and(scenarioRecord(['start_en' => null])['caps_reworked_at'])->toBeNull();
});

it('falls back to the Japanese-side rendering when no Global name exists', function (): void {
    $record = scenarioRecord(['name_en' => null, 'name_en_old' => 'Great Food Festival']);

    expect($record['name'])->toBe('Great Food Festival');
});

it('drops unusable entries rather than guessing', function (array $scenario) {
    $body = json_encode([$scenario], JSON_THROW_ON_ERROR);

    expect((new GametoraScenarioParser)->parse($body))->toBe([]);
})->with([
    'no slug' => [['url_name' => null, 'name_en' => 'X', 'stats' => [0, 0, 0, 0, 0]]],
    'no usable name' => [['url_name' => 'x', 'name_en' => '  ', 'name_en_old' => null, 'stats' => [0, 0, 0, 0, 0]]],
    'bonus with wrong length' => [['url_name' => 'x', 'name_en' => 'X', 'stats' => [200, 200]]],
    'no bonus at all' => [['url_name' => 'x', 'name_en' => 'X']],
]);

it('rejects bodies that are not a scenario list', function (string $body): void {
    expect((new GametoraScenarioParser)->parse($body))->toBe([]);
})->with(['not json' => ['{oops'], 'object' => ['{"id": 1}'], 'empty' => ['[]']]);
