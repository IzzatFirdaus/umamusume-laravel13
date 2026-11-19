<?php

declare(strict_types=1);

use App\Actions\PromoteMatchedRecord;
use App\Enums\CandidateStatus;
use App\Enums\MatchTier;
use App\Models\DataSource;
use App\Models\MatchCandidate;
use App\Models\Umamusume;
use App\Services\DataPipeline\Contracts\RaceEnrichmentSourceParser;
use App\Services\DataPipeline\NameNormalizer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\Fixtures\TestSourceParser;
use Tests\Fixtures\ThrowingSourceParser;

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
    Storage::disk('local')->put("snapshots/test-source/{$hash}.html", $body);

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

/*
 * S1.5: `uma:fetch` must not read the enrichment sources, and one bad source must not end the run.
 *
 * The four enrichment parsers implement `RaceEnrichmentSourceParser`, whose contract names
 * `uma:enrich-races` as the only consumer. None of them was skipped, so they fell through
 * `PipelineRunner`'s generic character loop: race-fans and race-rewards carry no name key and threw
 * (killing the run on the first one), while racetracks and races carry `name_en` and filed a Pending
 * review row per record. The 304-row backlog in the dev DB is that leak.
 */

it('skips every enrichment source and still fetches the rest of the run', function (): void {
    // The declared keys are read from config rather than listed here, so the case fails if someone
    // "fixes" the abort by deleting an entry instead of skipping it.
    /** @var array<string, array{parser?: class-string}> $sources */
    $sources = config('uma.sources');
    $enrichment = array_keys(array_filter(
        $sources,
        static fn (array $entry): bool => isset($entry['parser'])
            && is_a($entry['parser'], RaceEnrichmentSourceParser::class, true),
    ));

    // Non-vacuity: the four are still declared. A config with none of them would make this case pass
    // by having nothing to skip.
    expect($enrichment)->toHaveCount(4)
        ->and($enrichment)->toContain('gametora-racetracks', 'gametora-race-fans', 'gametora-races', 'gametora-race-rewards');

    // The declared list is narrowed to what this case fakes. A no-argument run visits EVERY configured
    // key, so leaving the real ones in place would have the case reaching gametora.com through
    // `preventStrayRequests()` and failing for a reason that is not the skip.
    $narrowed = [];
    foreach ($enrichment as $key) {
        $narrowed[$key] = $sources[$key];
    }
    $narrowed['test-source'] = $sources['test-source'];
    config(['uma.sources' => $narrowed]);

    Http::preventStrayRequests();
    Http::fake([FETCH_URL => Http::response(json_encode([['name' => 'Special Week']]))]);

    // No argument: every remaining key is visited. The enrichment four must be skipped, and the
    // ordinary test source must still be fetched, which is the half a blanket early-exit would break.
    $this->artisan('uma:fetch')
        ->expectsOutputToContain('is an enrichment source; read by uma:enrich-races. Skipped.')
        ->assertExitCode(0);

    foreach ($enrichment as $key) {
        $this->assertDatabaseMissing('match_candidates', ['source_key' => $key]);
    }
});

it('refuses an enrichment key asked for by name, naming the command that reads it', function (): void {
    // A RuntimeeException about a missing `name` key was the old answer, from inside the parser. The
    // refusal now happens before any fetch, and it names the door the Trainer actually wants.
    $this->artisan('uma:fetch gametora-race-fans')
        ->expectsOutputToContain('is read by uma:enrich-races, not uma:fetch.')
        ->assertExitCode(1);
});

it('continues past a source that throws and reports the run as degraded', function (): void {
    config([
        'uma.sources.throws-source' => [
            'url' => 'https://source.test/throws',
            'parser' => ThrowingSourceParser::class,
            'delay_ms' => 0,
            'timeout_s' => 5,
            'timezone' => 'Asia/Tokyo',
        ],
    ]);

    Http::preventStrayRequests();
    Http::fake([
        'https://source.test/throws' => Http::response('{"records":[]}', 200, ['Content-Type' => 'application/json']),
        FETCH_URL => Http::response(json_encode([['name' => 'Special Week']])),
    ]);

    // The throwing source is declared first in the merged config, so a run that stops at it never
    // reaches the healthy one.
    $this->artisan('uma:fetch')
        ->expectsOutputToContain("Source 'throws-source' failed:")
        ->expectsOutputToContain('source(s) failed and were skipped')
        ->assertExitCode(1);

    // The healthy source still ran: the failure was contained, which is what NFR-2 asks for. A run
    // that reports FAILURE while having fetched everything else is the correct degraded outcome.
    expect(MatchCandidate::query()->count())->toBeGreaterThan(0);
});
