<?php

declare(strict_types=1);

use App\Models\MatchCandidate;
use App\Models\RaceCatalogSlot;
use App\Services\DataPipeline\Parsers\GametoraRaceCatalogParser;
use App\Services\DataPipeline\PipelineRunner;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * A three-row slice of `race_instances`: two Classic slots and one Senior
 * recurrence of the same race at the same turn, which is the case this grain
 * exists for. Literal on purpose, so a reader can see exactly what was parsed.
 */
function raceCatalogFixture(): string
{
    return json_encode([
        [
            'id' => '62', 'year' => 2, 'month' => 4, 'half' => 1,
            'fans_needed' => 4500, 'fans_gain' => 21,
            'details' => ['id' => 100801, 'name_en' => 'Oka Sho', 'grade' => 100,
                'distance' => 1600, 'terrain' => 1, 'track' => 10008],
        ],
        [
            'id' => '66', 'year' => 2, 'month' => 5, 'half' => 1,
            'fans_needed' => 5000, 'fans_gain' => 24,
            'details' => ['id' => 101001, 'name_en' => 'NHK Mile Cup', 'grade' => 100,
                'distance' => 1600, 'terrain' => 1, 'track' => 10006],
        ],
        [
            'id' => '63', 'year' => 3, 'month' => 4, 'half' => 1,
            'fans_needed' => 4500, 'fans_gain' => 21,
            'details' => ['id' => 100802, 'name_en' => 'Oka Sho', 'grade' => 100,
                'distance' => 1600, 'terrain' => 1, 'track' => 10008],
        ],
    ], JSON_THROW_ON_ERROR);
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array{url: string, parser: class-string, timezone: string}
 */
function raceCatalogSourceConfig(array $overrides = []): array
{
    /** @var array{url: string, parser: class-string, timezone: string} $declared */
    $declared = config('uma.sources.gametora-race-catalog');

    return [...$declared, ...$overrides];
}

it('is declared as a fetch source with the race-catalog parser and a manifest block', function (): void {
    expect(config('uma.sources.gametora-race-catalog.timezone'))->toBe('Asia/Tokyo')
        ->and(config('uma.sources.gametora-race-catalog.parser'))
        ->toBe(GametoraRaceCatalogParser::class)
        ->and(config('uma.sources.gametora-race-catalog.manifest.key'))->toBe('race_instances')
        ->and(config('uma.sources.gametora-race-catalog.manifest.base'))->toBe('https://gametora.com/data/umamusume/')
        ->and(config('uma.sources.gametora-race-catalog.manifest.url'))->toBe('https://gametora.com/data/manifests/umamusume.json')
        // The withdrawn pin stays declared: it is what `uma:reparse` and a manifest outage fall
        // back to, and KI-67 records why the manifest leg exists at all.
        ->and(config('uma.sources.gametora-race-catalog.url'))
        ->toBe('https://gametora.com/data/umamusume/race_instances.294424fc.json');
});

it('routes a race-catalogue source to the writer, not to character matching', function (): void {
    $counts = app(PipelineRunner::class)->run(
        'gametora-race-catalog',
        raceCatalogSourceConfig(),
        raceCatalogFixture(),
        'storage/framework/testing/disks/local/snapshots/race-instances.json',
    );

    expect($counts)->toMatchArray(['created' => 3, 'updated' => 0, 'review' => 0])
        // The failure this branch exists to prevent: 410 races filed as Umamusume
        // that nobody could match.
        ->and(MatchCandidate::count())->toBe(0)
        ->and(RaceCatalogSlot::count())->toBe(3);
});

it('stamps R3 provenance on every row it writes', function (): void {
    app(PipelineRunner::class)->run(
        'gametora-race-catalog',
        raceCatalogSourceConfig(),
        raceCatalogFixture(),
        'storage/framework/testing/disks/local/snapshots/race-instances.json',
    );

    $slot = RaceCatalogSlot::where('title', 'Oka Sho')->where('year', 2)->firstOrFail();

    expect($slot->source_url)->toBe('https://gametora.com/data/umamusume/race_instances.294424fc.json')
        ->and($slot->snapshot_path)->toBe('storage/framework/testing/disks/local/snapshots/race-instances.json')
        ->and($slot->source_timezone)->toBe('Asia/Tokyo')
        ->and($slot->fetched_at)->not->toBeNull()
        ->and($slot->is_manual)->toBeFalse();
});

it('updates in place on a re-run instead of colliding with its own grain', function (): void {
    $pipeline = app(PipelineRunner::class);
    $config = raceCatalogSourceConfig();

    $pipeline->run('gametora-race-catalog', $config, raceCatalogFixture(), null);
    $second = $pipeline->run('gametora-race-catalog', $config, raceCatalogFixture(), null);

    expect($second)->toMatchArray(['created' => 0, 'updated' => 3])
        ->and(RaceCatalogSlot::count())->toBe(3);
});

it('holds the same race in two career years as two rows', function (): void {
    app(PipelineRunner::class)->run(
        'gametora-race-catalog',
        raceCatalogSourceConfig(),
        raceCatalogFixture(),
        null,
    );

    expect(RaceCatalogSlot::where('title', 'Oka Sho')->pluck('year')->all())->toBe([2, 3]);
});

it('never writes over a row the Trainer entered by hand', function (): void {
    RaceCatalogSlot::factory()->create([
        'title' => 'Oka Sho',
        'year' => 2,
        'month' => 4,
        'half' => 'Early',
        'turn' => 7,
        'scenario_key' => null,
        'fans_needed' => 1,
        'is_manual' => true,
    ]);

    $counts = app(PipelineRunner::class)->run(
        'gametora-race-catalog',
        raceCatalogSourceConfig(),
        raceCatalogFixture(),
        null,
    );

    expect($counts)->toMatchArray(['created' => 2, 'updated' => 0, 'skipped' => 1])
        ->and(RaceCatalogSlot::where('title', 'Oka Sho')->where('year', 2)->value('fans_needed'))->toBe(1);
});

it('stores the export decodings the grid and picker depend on', function (): void {
    app(PipelineRunner::class)->run(
        'gametora-race-catalog',
        raceCatalogSourceConfig(),
        raceCatalogFixture(),
        null,
    );

    $nhk = RaceCatalogSlot::where('title', 'NHK Mile Cup')->firstOrFail();

    expect($nhk)->toMatchArray([
        'turn' => 9,
        'slot_label' => 'Early May',
        'tier' => 'G1',
        'grade_code' => 100,
        'distance' => 1600,
        'distance_band' => 'Mile',
        'surface' => 'Turf',
        'fans_needed' => 5000,
        'is_mandatory' => false,
    ])->and($nhk->hasKnownFanGain())->toBeTrue();
});

it('resolves the document URL through the manifest and records that resolved URL', function (): void {
    Storage::fake('local');
    config(['uma.sources.gametora-race-catalog.delay_ms' => 0]);

    Http::preventStrayRequests();
    Http::fake([
        'gametora.com/data/manifests/umamusume.json' => Http::response(['race_instances' => 'abcd1234']),
        'gametora.com/data/umamusume/race_instances.abcd1234.json' => Http::response(raceCatalogFixture(), 200),
    ]);

    $this->artisan('uma:fetch', ['source' => 'gametora-race-catalog'])->assertExitCode(0);

    Http::assertSent(fn (Request $r): bool => str_contains($r->url(), 'race_instances.abcd1234.json'));

    // Provenance carries the document actually fetched, not the withdrawn pin declared in config.
    expect(RaceCatalogSlot::where('title', 'Oka Sho')->where('year', 2)->value('source_url'))
        ->toBe('https://gametora.com/data/umamusume/race_instances.abcd1234.json');
});

it('uses the pinned URL when the manifest itself will not answer', function (): void {
    Storage::fake('local');
    config(['uma.sources.gametora-race-catalog.delay_ms' => 0]);

    Http::preventStrayRequests();
    Http::fake([
        'gametora.com/data/manifests/umamusume.json' => Http::response('', 500),
        'gametora.com/data/umamusume/race_instances.294424fc.json' => Http::response(raceCatalogFixture(), 200),
    ]);

    $this->artisan('uma:fetch', ['source' => 'gametora-race-catalog'])->assertExitCode(0);

    expect(RaceCatalogSlot::count())->toBe(3)
        ->and(RaceCatalogSlot::where('title', 'Oka Sho')->where('year', 2)->value('source_url'))
        ->toBe('https://gametora.com/data/umamusume/race_instances.294424fc.json');
});
