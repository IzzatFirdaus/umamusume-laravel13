<?php

declare(strict_types=1);

use App\Models\Umamusume;
use App\Services\DataPipeline\NameNormalizer;
use App\Services\DataPipeline\Parsers\GametoraCharacterParser;
use App\Services\DataPipeline\PipelineRunner;

function scenarioBody(array $cards): string
{
    return json_encode($cards, JSON_THROW_ON_ERROR);
}

it('emits the ten aptitude letters in the export order', function (): void {
    $body = scenarioBody([[
        'char_id' => 1001,
        'card_id' => 100101,
        'name_en' => 'Special Week',
        'name_ja' => 'スペシャルウィーク',
        'release' => '2021-02-24',
        'release_en' => '2025-06-26',
        'aptitude' => ['A', 'G', 'F', 'C', 'A', 'A', 'G', 'A', 'A', 'C'],
    ]]);

    $record = (new GametoraCharacterParser)->parse($body)[0];

    expect($record['aptitude_turf'])->toBe('A')
        ->and($record['aptitude_dirt'])->toBe('G')
        ->and($record['aptitude_sprint'])->toBe('F')
        ->and($record['aptitude_mile'])->toBe('C')
        ->and($record['aptitude_long'])->toBe('A')
        ->and($record['aptitude_front_runner'])->toBe('G')
        ->and($record['aptitude_end_closer'])->toBe('C');
})->name('maps the aptitude array onto named columns');

it('omits every aptitude column when the set is incomplete or malformed', function (array $aptitude) {
    $body = scenarioBody([[
        'char_id' => 1002,
        'card_id' => 100201,
        'name_en' => 'Silence Suzuka',
        'release' => '2021-02-24',
        'aptitude' => $aptitude,
    ]]);

    $record = (new GametoraCharacterParser)->parse($body)[0];

    expect($record)->toHaveKey('name')
        ->and(array_intersect_key($record, array_flip(GametoraCharacterParser::APTITUDE_COLUMNS)))->toBe([]);
})->with([
    'short array' => [[['A', 'G', 'F']]],
    'unknown letter' => [[['A', 'G', 'F', 'C', 'A', 'A', 'G', 'Z', 'A', 'C']]],
    'non string entry' => [[['A', 'G', 'F', 'C', 'A', 'A', 'G', null, 'A', 'C']]],
]);

it('persists aptitudes through the pipeline onto an existing catalog row', function (): void {
    $umamusume = Umamusume::factory()->create([
        'name' => 'Special Week',
        'match_key' => app(NameNormalizer::class)->normalize('Special Week'),
        'aptitude_turf' => null,
    ]);

    $body = scenarioBody([[
        'char_id' => 1001,
        'card_id' => 100101,
        'name_en' => 'Special Week',
        'name_ja' => 'スペシャルウィーク',
        'release' => '2021-02-24',
        'release_en' => '2025-06-26',
        'aptitude' => ['A', 'G', 'F', 'C', 'A', 'A', 'G', 'A', 'A', 'C'],
    ]]);

    $counts = app(PipelineRunner::class)->run(
        'gametora-characters',
        config('uma.sources.gametora-characters'),
        $body,
        null,
    );

    $umamusume->refresh();

    expect($counts['updated'])->toBe(1)
        ->and($umamusume->aptitude_turf)->toBe('A')
        ->and($umamusume->aptitude_end_closer)->toBe('C')
        ->and($umamusume->dataSources()->count())->toBe(1);
})->name('promotion writes aptitude columns and keeps provenance');

it('keeps stored aptitudes when a later fetch carries none', function (): void {
    $umamusume = Umamusume::factory()->create([
        'name' => 'Silence Suzuka',
        'match_key' => app(NameNormalizer::class)->normalize('Silence Suzuka'),
        'aptitude_turf' => 'A',
    ]);

    $body = scenarioBody([[
        'char_id' => 1002,
        'card_id' => 100201,
        'name_en' => 'Silence Suzuka',
        'release' => '2021-02-24',
    ]]);

    app(PipelineRunner::class)->run('gametora-characters', config('uma.sources.gametora-characters'), $body, null);

    expect($umamusume->refresh()->aptitude_turf)->toBe('A');
})->name('a body without aptitudes never blanks stored grades');
