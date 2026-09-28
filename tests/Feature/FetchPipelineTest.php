<?php

declare(strict_types=1);

use App\Actions\PromoteMatchedRecord;
use App\Enums\CandidateStatus;
use App\Enums\MatchTier;
use App\Models\DataSource;
use App\Models\MatchCandidate;
use App\Models\Umamusume;
use App\Services\DataPipeline\NameNormalizer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\Fixtures\TestSourceParser;

const FETCH_URL = 'https://source.test/list';

function declareTestSource(): void
{
    config([
        'uma.sources.test-source' => [
            'url' => FETCH_URL,
            'parser' => TestSourceParser::class,
            'delay_ms' => 0,
            'timeout_s' => 5,
            'timezone' => 'Asia/Tokyo',
        ],
    ]);
}

beforeEach(function (): void {
    Storage::fake('local');
    declareTestSource();
});

it('promotes exact matches with provenance and queues unmatched records for review', function (): void {
    $normalizer = app(NameNormalizer::class);
    Umamusume::factory()->create([
        'name' => 'Special Week',
        'slug' => 'special-week',
        'match_key' => $normalizer->normalize('Special Week'),
    ]);

    Http::preventStrayRequests();
    Http::fake([
        'source.test/list' => Http::response(json_encode([
            ['name' => 'Special Week', 'name_ja' => 'スペシャルウィーク'],
            ['name' => 'Brand New Name'],
        ])),
    ]);

    $this->artisan('uma:fetch', ['source' => 'test-source'])->assertExitCode(0);

    $special = Umamusume::where('slug', 'special-week')->firstOrFail();
    expect($special->name_ja)->toBe('スペシャルウィーク')
        ->and(DataSource::where('umamusume_id', $special->id)->count())->toBe(1)
        ->and(DataSource::first()->source_timezone)->toBe('Asia/Tokyo');

    expect(MatchCandidate::where('status', CandidateStatus::Pending->value)->count())->toBe(1)
        ->and(MatchCandidate::first()->match_tier)->toBe(MatchTier::None);

    expect(Storage::disk('local')->exists('snapshots/test-source'))->toBeTrue();
});

it('never overwrites a manual row', function (): void {
    $normalizer = app(NameNormalizer::class);
    $manual = Umamusume::factory()->manual()->create([
        'name' => 'Special Week',
        'slug' => 'special-week',
        'name_ja' => 'トレーナー編集済み',
        'match_key' => $normalizer->normalize('Special Week'),
    ]);

    Http::preventStrayRequests();
    Http::fake([
        'source.test/list' => Http::response(json_encode([
            ['name' => 'Special Week', 'name_ja' => 'エンジン値'],
        ])),
    ]);

    $this->artisan('uma:fetch', ['source' => 'test-source'])->assertExitCode(0);

    expect($manual->fresh()->name_ja)->toBe('トレーナー編集済み')
        ->and(DataSource::count())->toBe(0);
});

it('is idempotent: an unchanged source body writes nothing new on re-run', function (): void {
    $normalizer = app(NameNormalizer::class);
    Umamusume::factory()->create([
        'name' => 'Special Week',
        'slug' => 'special-week',
        'match_key' => $normalizer->normalize('Special Week'),
    ]);

    $body = json_encode([['name' => 'Special Week']]);

    Http::preventStrayRequests();
    Http::fake(['source.test/list' => Http::response($body)]);

    $this->artisan('uma:fetch', ['source' => 'test-source'])->assertExitCode(0);
    $this->artisan('uma:fetch', ['source' => 'test-source'])->assertExitCode(0);

    expect(Umamusume::count())->toBe(1)
        ->and(DataSource::count())->toBe(1)
        ->and(MatchCandidate::count())->toBe(0);
});

it('queues a fuzzy match as a review candidate with the suggested row', function (): void {
    $normalizer = app(NameNormalizer::class);
    $existing = Umamusume::factory()->create([
        'name' => 'Special Week',
        'slug' => 'special-week',
        'match_key' => $normalizer->normalize('Special Week'),
    ]);

    Http::preventStrayRequests();
    Http::fake([
        'source.test/list' => Http::response(json_encode([['name' => 'Special Weck']])),
    ]);

    $this->artisan('uma:fetch', ['source' => 'test-source'])->assertExitCode(0);

    $candidate = MatchCandidate::firstOrFail();

    expect($candidate->match_tier)->toBe(MatchTier::Fuzzy)
        ->and($candidate->suggested_umamusume_id)->toBe($existing->id)
        ->and(Umamusume::count())->toBe(1);
});

it('reparses from the stored snapshot with zero network', function (): void {
    $normalizer = app(NameNormalizer::class);
    Umamusume::factory()->create([
        'name' => 'Special Week',
        'slug' => 'special-week',
        'match_key' => $normalizer->normalize('Special Week'),
    ]);

    $body = json_encode([['name' => 'Special Week', 'name_ja' => '第一回']]);
    $hash = hash('sha256', $body);
    Storage::disk('local')->put("snapshots/test-source/2026-09-27/{$hash}.html", $body);

    Http::preventStrayRequests(); // any HTTP call in this test now throws

    $this->artisan('uma:reparse', ['source' => 'test-source'])->assertExitCode(0);

    expect(DataSource::count())->toBe(1)
        ->and(Umamusume::count())->toBe(1)
        ->and(Umamusume::first()->name_ja)->toBe('第一回');
});

/*
 * umamusume.external_ref is the durable link to the source row (ADR-0008): the
 * parser has always emitted it and the card fetch attaches through it, so promote
 * has to keep it on both of its paths. The create path is unreachable from
 * uma:fetch — an Exact or Alias match names a row that already exists — which is
 * why it is driven through the action the review queue calls.
 */

it('stores the source character link on the row it creates', function (): void {
    $result = app(PromoteMatchedRecord::class)->handle(
        record: ['name' => 'Brand New Trainee', 'external_ref' => 'gametora:char:1008'],
        existing: null,
        sourceKey: 'test-source',
        url: FETCH_URL,
    );

    expect($result['created'])->toBeTrue()
        ->and($result['umamusume']->external_ref)->toBe('gametora:char:1008')
        ->and(Umamusume::firstOrFail()->external_ref)->toBe('gametora:char:1008');
});

it('stores the source link on the row it updates and keeps one a later fetch stops stating', function (): void {
    $normalizer = app(NameNormalizer::class);
    Umamusume::factory()->create([
        'name' => 'Special Week',
        'slug' => 'special-week',
        'match_key' => $normalizer->normalize('Special Week'),
    ]);

    Http::preventStrayRequests();
    // A second Http::fake() merges into the stub list instead of replacing it, so
    // two runs of one source get their bodies off a sequence.
    Http::fake([
        'source.test/list' => Http::sequence()
            ->push(json_encode([['name' => 'Special Week', 'external_ref' => 'gametora:char:1008']]))
            ->push(json_encode([['name' => 'Special Week', 'name_ja' => 'スペシャルウィーク']])),
    ]);

    $this->artisan('uma:fetch', ['source' => 'test-source'])->assertExitCode(0);

    expect(Umamusume::where('slug', 'special-week')->firstOrFail()->external_ref)
        ->toBe('gametora:char:1008');

    $this->artisan('uma:fetch', ['source' => 'test-source'])->assertExitCode(0);

    $stored = Umamusume::where('slug', 'special-week')->firstOrFail();

    expect($stored->external_ref)->toBe('gametora:char:1008')
        ->and($stored->name_ja)->toBe('スペシャルウィーク');
});
