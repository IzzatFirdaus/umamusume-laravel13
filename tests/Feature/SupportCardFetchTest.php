<?php

declare(strict_types=1);

use App\Models\MatchCandidate;
use App\Models\SupportCard;
use App\Models\SupportEffect;
use App\Services\DataPipeline\Parsers\GametoraSupportCardParser;
use App\Services\DataPipeline\Parsers\GametoraSupportEffectParser;
use App\Services\DataPipeline\PipelineRunner;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/*
 * The ingest wiring for the two support datasets: the declared sources, the pipeline routing that keeps
 * them out of the review queue, and the offline command.
 *
 * `uma:fetch` and `uma:reparse` take a source key and need no change, because `PipelineRunner::run()`
 * branches on the parser's contract rather than on a source name. That is what these tests hold up to
 * the light: a support-card record has no `name` key, so a routing miss does not quietly misfile a row,
 * it throws in the match stage or files 559 cards as characters nobody could identify.
 *
 * The provenance assertions read the committed body's own sha256 because the hash in a GameTora file
 * name is a content hash. `KI-24` measured that a stale pinned hash answers `200` with the superseded
 * document, so a pin is only worth what its bytes are: the check below is what makes the pinned URL a
 * statement about the file in the repository rather than a hope.
 */

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function supportCardSourceConfig(array $overrides = []): array
{
    return [...config('uma.sources.gametora-support-cards'), 'delay_ms' => 0, ...$overrides];
}

/**
 * The smallest body the publisher's shape allows: two cards, one dictionary row.
 *
 * @return array<int, array<string, mixed>>
 */
function supportCardFetchBody(): array
{
    return [
        [
            'support_id' => 95001, 'char_id' => 1001, 'char_name' => 'Special Week',
            'name_jp' => 'スペシャルウィーク', 'title_en' => '[Tracen Academy]', 'title_ja' => '[トレセン学園]',
            'rarity' => 1, 'type' => 'guts', 'release' => '2021-02-24', 'release_en' => '2025-06-26',
            'effects' => [[1, 5, -1, -1, 10, 10, -1, -1, 15, -1, -1, -1]],
        ],
        [
            'support_id' => 95002, 'char_id' => 9001, 'char_name' => 'Tazuna Hayakawa',
            'name_jp' => '早川タズナ', 'rarity' => 3, 'type' => 'friend', 'release' => '2026-06-29',
            'effects' => [[19, -1, 5, -1, 20, 20, -1, -1, 30, -1, -1, -1]],
        ],
    ];
}

it('declares both support datasets as allowlisted sources with their own parsers', function (): void {
    expect(config('uma.sources.gametora-support-cards.parser'))->toBe(GametoraSupportCardParser::class)
        ->and(config('uma.sources.gametora-support-cards.url'))
        ->toBe('https://gametora.com/data/umamusume/support-cards.88dea522.json')
        ->and(config('uma.sources.gametora-support-cards.manifest.key'))->toBe('support-cards')
        ->and(config('uma.sources.gametora-support-cards.manifest.base'))
        ->toBe('https://gametora.com/data/umamusume/')
        ->and(config('uma.sources.gametora-support-cards.manifest.url'))
        ->toBe('https://gametora.com/data/manifests/umamusume.json')
        ->and(config('uma.sources.gametora-support-effects.parser'))->toBe(GametoraSupportEffectParser::class)
        ->and(config('uma.sources.gametora-support-effects.url'))
        ->toBe('https://gametora.com/data/umamusume/support_effects.ca447e53.json')
        // The manifest spells the two keys differently, one hyphen and one underscore, and
        // `SourceFetcher` builds the file name from the key. Neither entry may tidy the other's.
        ->and(config('uma.sources.gametora-support-effects.manifest.key'))->toBe('support_effects')
        ->and(config('uma.sources.gametora-support-cards.timezone'))->toBe('Asia/Tokyo')
        ->and(config('uma.sources.gametora-support-effects.timezone'))->toBe('Asia/Tokyo');
});

it('commits each body under the hash its content actually carries', function (): void {
    foreach (['gametora-support-cards', 'gametora-support-effects'] as $key) {
        /** @var string $file */
        $file = config("uma.sources.{$key}.seed_file");
        $path = base_path("database/seeders/data/{$file}");

        expect(is_file($path))->toBeTrue()
            // Asserted first because the rebuild below is vacuous without it: `preg_replace()` hands
            // back the subject unchanged when nothing matches, and a file name with no hash in it would
            // then "agree" with its own content hash.
            ->and($file)->toMatch('/\.[a-f0-9]{8}\.json$/');

        // Eight hex digits, the pattern `SourceFetcher::HASH_PATTERN` will accept from a manifest, and
        // the whole reason the committed file and the pinned URL name the same revision.
        $hash = substr(hash('sha256', (string) file_get_contents($path)), 0, 8);

        expect($file)->toBe(preg_replace('/^(.*)\.[a-f0-9]{8}\.json$/', '${1}.'.$hash.'.json', $file))
            ->and(config("uma.sources.{$key}.url"))->toBe(
                'https://gametora.com/data/umamusume/'.$file
            );
    }
});

it('routes support cards to the writer instead of the review queue', function (): void {
    $counts = app(PipelineRunner::class)->run(
        'gametora-support-cards',
        supportCardSourceConfig(),
        (string) json_encode(supportCardFetchBody()),
        'snapshots/support.json',
    );

    expect($counts)->toBe(['created' => 2, 'updated' => 0, 'skipped' => 0, 'review' => 0])
        ->and(SupportCard::count())->toBe(2)
        // The flood the branch exists to prevent: every card filed as a character nobody could match.
        ->and(MatchCandidate::count())->toBe(0);
});

it('routes the effect dictionary to its own writer', function (): void {
    $counts = app(PipelineRunner::class)->run(
        'gametora-support-effects',
        [...config('uma.sources.gametora-support-effects'), 'delay_ms' => 0],
        (string) json_encode([
            ['id' => 1, 'name_en' => 'Friendship Bonus', 'name_ja' => '友情ボーナス',
                'calc' => 'mult', 'symbol' => 'percent', 'desc_en' => 'Increases the effectiveness of Friendship Training'],
            ['id' => 20, 'name_en' => 'Max Speed', 'name_ja' => 'スピード限界値アップ', 'symbol' => 'none'],
        ]),
        'snapshots/support-effects.json',
    );

    expect($counts)->toBe(['created' => 2, 'updated' => 0, 'skipped' => 0, 'review' => 0])
        ->and(SupportEffect::count())->toBe(2)
        ->and(SupportEffect::where('effect_id', 20)->value('calc'))->toBeNull()
        ->and(MatchCandidate::count())->toBe(0);
});

it('imports both committed bodies with no network at all', function (): void {
    Http::preventStrayRequests();

    $this->artisan('uma:import:support-cards')->assertExitCode(0);

    expect(SupportCard::count())->toBe(559)
        ->and(SupportEffect::count())->toBe(35)
        ->and(MatchCandidate::count())->toBe(0)
        // Provenance from the offline path is the pinned URL, which the committed body's own hash
        // confirms, and every row carries it.
        ->and(SupportCard::where('source_url', config('uma.sources.gametora-support-cards.url'))->count())
        ->toBe(559)
        ->and(SupportEffect::where('source_url', config('uma.sources.gametora-support-effects.url'))->count())
        ->toBe(35)
        ->and(SupportCard::whereNotNull('fetched_at')->count())->toBe(559);
});

it('imports the dictionary it can and reports the body it cannot find', function (): void {
    // One unreadable source is reported and the other still lands, the decision `SourceDocumentSeeder`
    // makes for the same reason: cards with no dictionary to label them are a worse state than a
    // reported partial import the Trainer can re-run.
    config(['uma.sources.gametora-support-cards.seed_file' => 'support-cards.deadbeef.json']);

    $this->artisan('uma:import:support-cards')->assertExitCode(1);

    expect(SupportCard::count())->toBe(0)
        ->and(SupportEffect::count())->toBe(35);
});

it('records the URL the manifest resolved when uma:fetch lands the cards', function (): void {
    Storage::fake('local');
    config(['uma.sources.gametora-support-cards.delay_ms' => 0]);

    Http::preventStrayRequests();
    Http::fake([
        // A hash the pin does not carry, so the URL below can only have come from the manifest.
        'gametora.com/data/manifests/umamusume.json' => Http::response(['support-cards' => 'abcd1234']),
        'gametora.com/data/umamusume/support-cards.abcd1234.json' => Http::response(
            (string) json_encode(supportCardFetchBody()),
            200
        ),
    ]);

    $this->artisan('uma:fetch', ['source' => 'gametora-support-cards'])->assertExitCode(0);

    expect(SupportCard::count())->toBe(2)
        ->and(SupportCard::where('support_id', 95001)->value('source_url'))
        ->toBe('https://gametora.com/data/umamusume/support-cards.abcd1234.json');
});

it('refuses a manifest hash that cannot be a file name and falls back to the pin', function (): void {
    Storage::fake('local');
    config(['uma.sources.gametora-support-cards.delay_ms' => 0]);

    Http::preventStrayRequests();
    Http::fake([
        'gametora.com/data/manifests/umamusume.json' => Http::response(['support-cards' => '../../evil']),
        'gametora.com/data/umamusume/support-cards.88dea522.json' => Http::response(
            (string) json_encode(supportCardFetchBody()),
            200
        ),
    ]);

    $this->artisan('uma:fetch', ['source' => 'gametora-support-cards'])->assertExitCode(0);

    // The SSRF floor holds for a resolved URL exactly as for a pinned one: the address the row carries
    // is the one declared in config, because the manifest's answer could not be trusted to name a file.
    expect(SupportCard::count())->toBe(2)
        ->and(SupportCard::where('support_id', 95002)->value('source_url'))
        ->toBe('https://gametora.com/data/umamusume/support-cards.88dea522.json');
});
