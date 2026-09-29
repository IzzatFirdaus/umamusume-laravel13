<?php

declare(strict_types=1);

use App\Actions\StoreCharacterCards;
use App\Enums\CardRarity;
use App\Models\CharacterCard;
use App\Models\MatchCandidate;
use App\Models\Umamusume;
use App\Services\DataPipeline\Parsers\GametoraCharacterCardParser;
use App\Services\DataPipeline\PipelineRunner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/*
 * The card fetch (PRD FR-A-6, ADR-0008): a declared source whose rows key on the
 * source's own card id, so they never enter the match stage, and a store that is
 * idempotent by that id and stops at both grains of FR-B-4's `is_manual` lock.
 *
 * Every record here comes from the shipped Task 6 fixture through the shipped
 * parser, so the counts this file asserts are measured, not assumed: the fixture
 * emits 7 records — three for `gametora:char:1001`, two for 1007, two for 1003.
 */

beforeEach(function (): void {
    // Nothing in this file touches the network: the runner is handed the body the
    // way uma:reparse hands it. This turns that reading of the call list into an
    // enforced fact — a stray request now throws.
    Http::preventStrayRequests();
});

/**
 * @return array<int, array<string, mixed>> the fixture's card records, keyed by the
 *                                          source's own card id
 */
function cardsBySourceId(): array
{
    $records = [];

    foreach ((new GametoraCharacterCardParser)->parse(cardSampleBody()) as $record) {
        $records[$record['card_id']] = $record;
    }

    return $records;
}

function cardSampleBody(): string
{
    return file_get_contents(base_path('tests/Fixtures/gametora-character-cards.global.sample.json')) ?: '';
}

/**
 * Special Week's three Global forms, oldest card id first.
 *
 * @return list<array<string, mixed>>
 */
function specialWeekCardRecords(): array
{
    $cards = cardsBySourceId();

    return [$cards[100101], $cards[100102], $cards[100103]];
}

/**
 * @return array{url: string, parser: class-string, timezone?: string|null}
 */
function cardSourceConfig(): array
{
    /** @var array{url: string, parser: class-string, timezone?: string|null} $declared */
    $declared = config('uma.sources.gametora-character-cards');

    return $declared;
}

it('attaches every card to the trainee its char ref names', function (): void {
    $umamusume = Umamusume::factory()->create([
        'name' => 'Special Week', 'slug' => 'special-week', 'external_ref' => 'gametora:char:1001',
    ]);

    $counts = (new StoreCharacterCards)->handle(
        records: specialWeekCardRecords(),
        url: 'https://gametora.test/character-cards.json',
        snapshotPath: null,
        timezone: null,
    );

    $cards = CharacterCard::query()->orderBy('card_id')->get();
    $debut = $cards->firstWhere('card_id', 100101);

    expect($counts)->toMatchArray(['created' => 3, 'updated' => 0, 'skipped' => 0])
        ->and($cards->pluck('umamusume_id')->unique()->all())->toBe([$umamusume->id])
        ->and($debut?->title)->toBe('[Special Dreamer]')
        ->and($debut?->is_debut_form)->toBeTrue()
        ->and($cards->firstWhere('card_id', 100102)?->is_debut_form)->toBeFalse()
        // The record carries an int and the column is enum-cast, so the write has to
        // read back as the enum; the date is the record's string on a date column.
        ->and($debut?->rarity)->toBe(CardRarity::ThreeStar)
        ->and($debut?->global_release_date?->toDateString())->toBe('2025-06-26');
});

it('is idempotent: a re-fetch updates the card it wrote instead of adding a second one', function (): void {
    Umamusume::factory()->create(['external_ref' => 'gametora:char:1001']);
    $records = specialWeekCardRecords();

    $first = (new StoreCharacterCards)->handle($records, 'https://gametora.test/a.json', null, null);
    $rowId = CharacterCard::where('card_id', 100101)->value('id');

    $second = (new StoreCharacterCards)->handle(
        [[...$records[0], 'title' => '[Special Dreamer Revised]'], $records[1], $records[2]],
        'https://gametora.test/b.json',
        null,
        null,
    );

    expect($first)->toMatchArray(['created' => 3, 'updated' => 0])
        ->and($second)->toMatchArray(['created' => 0, 'updated' => 3])
        ->and(CharacterCard::query()->count())->toBe(3)
        ->and(CharacterCard::where('card_id', 100101)->value('id'))->toBe($rowId)
        ->and(CharacterCard::where('card_id', 100101)->value('title'))->toBe('[Special Dreamer Revised]')
        // The row now reports the fetch that last wrote it.
        ->and(CharacterCard::where('card_id', 100101)->value('source_url'))->toBe('https://gametora.test/b.json');
});

it('stamps the four provenance columns on every card row it writes', function (): void {
    Umamusume::factory()->create(['external_ref' => 'gametora:char:1001']);

    (new StoreCharacterCards)->handle(
        specialWeekCardRecords(),
        'https://gametora.test/c.json',
        'snapshots/gametora-character-cards/2026-09-29/abc.json',
        'Asia/Tokyo',
    );

    // ADR-0003 Amendment R3 puts provenance on the reference row, and Task 11 reads
    // the card's source line out of these columns — `data_sources` stays the
    // character-level table behind FR-A-4, so asserting a DataSource row here would
    // pin a write this path never makes. Counted against the whole table so a row
    // missing one of the four cannot pass by being out of frame.
    expect(CharacterCard::query()->count())->toBe(3)
        ->and(CharacterCard::query()
            ->where('source_url', 'https://gametora.test/c.json')
            ->where('snapshot_path', 'snapshots/gametora-character-cards/2026-09-29/abc.json')
            ->where('source_timezone', 'Asia/Tokyo')
            ->whereNotNull('fetched_at')
            ->where('is_manual', false)
            ->count())->toBe(3);
});

it('does not clear a cross-check verdict the fetch did not write', function (): void {
    $umamusume = Umamusume::factory()->create(['external_ref' => 'gametora:char:1001']);
    CharacterCard::factory()->unconfirmed()->create([
        'umamusume_id' => $umamusume->id, 'card_id' => 100101,
    ]);

    $counts = (new StoreCharacterCards)->handle(
        [cardsBySourceId()[100101]], 'https://gametora.test/d.json', null, null,
    );

    // Task 8 owns `unconfirmed`: a re-fetch reports what the source says and does not
    // get to quietly clear a human verdict it never set. The flag is absent from the
    // write payload precisely for this, which is why the row is still updated.
    expect($counts)->toMatchArray(['created' => 0, 'updated' => 1])
        ->and(CharacterCard::where('card_id', 100101)->value('unconfirmed'))->toBeTrue();
});

it('skips every card whose trainee is not in the catalog', function (): void {
    $counts = (new StoreCharacterCards)->handle(
        array_values(cardsBySourceId()), 'https://gametora.test/e.json', null, null,
    );

    // The roster has not cleared the review queue for any of them yet.
    expect($counts)->toMatchArray(['created' => 0, 'updated' => 0, 'skipped' => 7])
        ->and(CharacterCard::query()->count())->toBe(0);
});

it('never writes under a trainee the Trainer owns by hand: FR-B-4 at the character grain', function (): void {
    $trainee = Umamusume::factory()->manual()->create(['external_ref' => 'gametora:char:1001']);
    $card = CharacterCard::factory()->create([
        'umamusume_id' => $trainee->id, 'card_id' => 100101, 'title' => '[Typed By The Trainer]',
    ]);

    // 100102 has no row yet, and that is the half this case pins: her lock stops a
    // card being attached under her at all, not just an existing one being rewritten.
    $cards = cardsBySourceId();
    $counts = (new StoreCharacterCards)->handle(
        [$cards[100101], $cards[100102]], 'https://gametora.test/f.json', null, null,
    );

    expect($counts)->toMatchArray(['created' => 0, 'updated' => 0, 'skipped' => 2])
        ->and($card->fresh()->title)->toBe('[Typed By The Trainer]')
        ->and(CharacterCard::query()->count())->toBe(1);
});

it('never writes over a card the Trainer corrected by hand: FR-B-4 at the card grain', function (): void {
    $trainee = Umamusume::factory()->create(['external_ref' => 'gametora:char:1001']);
    $corrected = CharacterCard::factory()->manual()->create([
        'umamusume_id' => $trainee->id, 'card_id' => 100101, 'title' => '[Typed By The Trainer]',
    ]);
    $sibling = CharacterCard::factory()->create([
        'umamusume_id' => $trainee->id, 'card_id' => 100102, 'title' => '[Title From An Older Fetch]',
    ]);

    $cards = cardsBySourceId();
    $counts = (new StoreCharacterCards)->handle(
        [$cards[100101], $cards[100102], $cards[100103]],
        'https://gametora.test/g.json',
        null,
        null,
    );

    // The distinction the card-level lock buys: one corrected title does not claim
    // her whole character, so her unlocked siblings keep updating while that row is
    // skipped — skipped, not duplicated by a create that would collide anyway.
    expect($counts)->toMatchArray(['created' => 1, 'updated' => 1, 'skipped' => 1])
        ->and($corrected->fresh()->title)->toBe('[Typed By The Trainer]')
        // Skipped means untouched, provenance included: the row still reports the
        // fetch that last wrote it, not the one that walked past it.
        ->and($corrected->fresh()->source_url)->toBe('https://gametora.test/character-cards.json')
        ->and($sibling->fresh()->title)->toBe("[Hopp'n♪Happy Heart]")
        ->and(CharacterCard::query()->count())->toBe(3);
});

it('writes only columns the model declares fillable, so strict mode cannot throw on a record key', function (): void {
    // A parser record carries `char_external_ref`, which is neither a
    // `character_cards` column nor a #[Fillable] key. Spreading a record into the
    // write is discarded silently today and becomes a QueryException the day the app
    // turns on Model::preventSilentlyDiscardingAttributes(), so this turns it on for
    // one call and restores whatever the app had.
    $wasPreventing = Model::preventsSilentlyDiscardingAttributes();
    Model::preventSilentlyDiscardingAttributes(true);

    try {
        Umamusume::factory()->create(['external_ref' => 'gametora:char:1001']);
        $counts = (new StoreCharacterCards)->handle(
            specialWeekCardRecords(), 'https://gametora.test/h.json', null, null,
        );
    } finally {
        Model::preventSilentlyDiscardingAttributes($wasPreventing);
    }

    expect($counts)->toMatchArray(['created' => 3])
        ->and(CharacterCard::query()->count())->toBe(3);
});

it('routes the card source past the match stage and into the card table', function (): void {
    Umamusume::factory()->create([
        'name' => 'Special Week', 'slug' => 'special-week', 'external_ref' => 'gametora:char:1001',
    ]);

    $counts = app(PipelineRunner::class)->run(
        'gametora-character-cards',
        cardSourceConfig(),
        cardSampleBody(),
        null,
    );

    // Measured, not copied from the plan: the parser emits 7 records for this
    // fixture, 3 of them Special Week's, so 3 created and 4 skipped.
    expect($counts)->toMatchArray(['created' => 3, 'updated' => 0, 'skipped' => 4, 'review' => 0])
        // Without the branch this body reaches the match loop, which reads a `name`
        // key card records never carry. Nothing here is ambiguous, so nothing may be
        // filed for a Trainer to adjudicate.
        ->and(MatchCandidate::query()->count())->toBe(0)
        ->and(CharacterCard::query()->count())->toBe(3)
        ->and(CharacterCard::query()->where('unconfirmed', false)->count())->toBe(3)
        ->and(CharacterCard::query()->value('source_url'))->toBe(cardSourceConfig()['url']);
});

it('bumps catalog:version when cards land, because the catalog caches its id list', function (): void {
    Umamusume::factory()->create(['external_ref' => 'gametora:char:1001']);
    Cache::put('catalog:version', 5, 3600);

    app(PipelineRunner::class)->run('gametora-character-cards', cardSourceConfig(), cardSampleBody(), null);

    // CatalogController::cached() keys the page group on catalog:version and nothing
    // else. The race-catalogue branch returns before the shared bump because race
    // rows are not in the catalog list; card rows are.
    expect((int) Cache::get('catalog:version'))->toBe(6);
});

it('leaves catalog:version alone when no card lands', function (): void {
    Cache::put('catalog:version', 5, 3600);

    app(PipelineRunner::class)->run('gametora-character-cards', cardSourceConfig(), cardSampleBody(), null);

    expect((int) Cache::get('catalog:version'))->toBe(5)
        ->and(CharacterCard::query()->count())->toBe(0);
});

it('is declared as a fetch source with the card-grain parser', function (): void {
    expect(config('uma.sources.gametora-character-cards'))->toMatchArray([
        'url' => 'https://gametora.com/data/umamusume/character-cards.e9e9ee6d.json',
        'delay_ms' => 1000,
        'timeout_s' => 15,
        'timezone' => 'Asia/Tokyo',
    ])->and(config('uma.sources.gametora-character-cards.parser'))
        ->toBe(GametoraCharacterCardParser::class);
});
