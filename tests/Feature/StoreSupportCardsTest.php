<?php

declare(strict_types=1);

use App\Actions\StoreSupportCards;
use App\Actions\StoreSupportEffects;
use App\Models\SupportCard;
use App\Models\SupportEffect;
use App\Models\Umamusume;
use App\Services\DataPipeline\Parsers\GametoraSupportCardParser;
use App\Services\DataPipeline\Parsers\GametoraSupportEffectParser;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/*
 * The two support-card writers, run against the real published documents.
 *
 * Both grains are in one file because the interesting fact between them is a join: a card's anchor
 * vector names dictionary rows by id, so "the import landed" means nothing until the labels a deck
 * panel reads are in the table beside it. `StoreSupportCards` guards `is_manual` (PRD FR-B-4) and
 * `StoreSupportEffects` cannot, because `support_effects` has no such column; that asymmetry is
 * asserted here rather than left to the reader.
 *
 * Idempotence is pinned on the whole 559-record body rather than on a slice, because FR-B-5's promise is
 * about the catalogue: a re-run updates the rows it wrote and collides with none of them. The narrower
 * cases take the smallest real subset that still reaches the branch.
 */

function supportCardImportUrl(): string
{
    return (string) config('uma.sources.gametora-support-cards.url');
}

function supportEffectImportUrl(): string
{
    return (string) config('uma.sources.gametora-support-effects.url');
}

/**
 * @return list<array<string, mixed>>
 */
function importedSupportCards(): array
{
    return (new GametoraSupportCardParser)->parse(
        (string) file_get_contents(base_path('database/seeders/data/support-cards.88dea522.json'))
    );
}

/**
 * @return list<array<string, mixed>>
 */
function importedSupportEffects(): array
{
    return (new GametoraSupportEffectParser)->parse(
        (string) file_get_contents(base_path('database/seeders/data/support_effects.ca447e53.json'))
    );
}

it('imports all 559 published cards and updates them instead of duplicating them on a re-run', function (): void {
    $action = new StoreSupportCards;

    expect($action->handle(importedSupportCards(), supportCardImportUrl()))
        ->toBe(['created' => 559, 'updated' => 0, 'skipped' => 0])
        ->and(SupportCard::count())->toBe(559)
        // The second pass lands on the same `support_id`s. The unique index is what a writer that had
        // not matched on the export's own grain would hit here.
        ->and($action->handle(importedSupportCards(), supportCardImportUrl()))
        ->toBe(['created' => 0, 'updated' => 559, 'skipped' => 0])
        ->and(SupportCard::count())->toBe(559);

    // What the generated column reads out of the two dates the writer stored, and the two facts the
    // floor rules require of every one of those rows.
    expect(SupportCard::whereNotNull('release_global')->count())->toBe(251)
        ->and(SupportCard::where('release_status', 'Global')->count())->toBe(251)
        ->and(SupportCard::where('release_status', 'JP-only')->count())->toBe(308)
        ->and(SupportCard::whereNotIn('type', SupportCard::TYPES)->count())->toBe(0)
        ->and(SupportCard::whereNull('source_url')->count())->toBe(0)
        ->and(SupportCard::whereNull('fetched_at')->count())->toBe(0);
});

it('lands the 23 staff-keyed cards with no trainee row anywhere in the database', function (): void {
    // The regression the wrongly-declared foreign key caused. `char_id` addresses GameTora's character
    // space while `umamusume.id` is a local surrogate, and the 9000 block is staff who are not in the
    // trainable catalogue at all: with that constraint in place every one of these rows was rejected.
    $staff = array_values(array_filter(
        importedSupportCards(),
        fn (array $record): bool => $record['char_id'] >= 9000
    ));

    expect($staff)->toHaveCount(23)
        ->and(Umamusume::count())->toBe(0);

    $counts = (new StoreSupportCards)->handle($staff, supportCardImportUrl());

    expect($counts)->toBe(['created' => 23, 'updated' => 0, 'skipped' => 0])
        ->and(SupportCard::where('char_id', '>=', 9000)->count())->toBe(23)
        ->and(SupportCard::where('char_name', 'Tazuna Hayakawa')->count())->toBe(3)
        // The two group cards the Scenario Link derivation needs, and the reason the column keeps the
        // export's id instead of nulling what this catalogue does not hold.
        ->and(SupportCard::whereIn('char_id', [9040, 9047])->pluck('char_name')->sort()->values()->all())
        ->toBe(['Ancestors & Guides', 'Embodiment of Legends']);
});

it('stamps the source url and the fetch time on every row it creates', function (): void {
    (new StoreSupportCards)->handle(array_slice(importedSupportCards(), 0, 30), supportCardImportUrl());

    expect(SupportCard::count())->toBe(30)
        ->and(SupportCard::where('source_url', '!=', supportCardImportUrl())->count())->toBe(0)
        ->and(SupportCard::whereNotNull('fetched_at')->count())->toBe(30)
        // An import never sets the flag that stops the next import.
        ->and(SupportCard::where('is_manual', true)->count())->toBe(0);
});

it('leaves a card the Trainer corrected by hand and imports its siblings', function (): void {
    $corrected = SupportCard::factory()->create([
        'support_id' => 10001,
        'title_en' => 'a correction the Trainer typed in',
        'is_manual' => true,
        'fetched_at' => '2020-01-01 00:00:00',
    ]);

    $counts = (new StoreSupportCards)->handle(array_slice(importedSupportCards(), 0, 3), supportCardImportUrl());

    expect($counts)->toBe(['created' => 2, 'updated' => 0, 'skipped' => 1])
        ->and($corrected->refresh()->title_en)->toBe('a correction the Trainer typed in')
        ->and($corrected->fetched_at->toDateString())->toBe('2020-01-01');

    // One stopped row is not a stopped batch: a hand-corrected card must not cost the Trainer the
    // catalogue, the same trade `StoreSkills` makes.
    expect(SupportCard::count())->toBe(3);
});

it('round-trips an anchor vector as twelve wide and keeps -1 as no anchor', function (): void {
    // ADR-0014 correction 4: store the anchors, interpolate on read. The column is `text` holding JSON,
    // so both the model cast and the raw value have to agree that nothing was reformatted and no `-1`
    // became a `0`.
    $record = importedSupportCards()[0];

    (new StoreSupportCards)->handle([$record], supportCardImportUrl());

    $card = SupportCard::where('support_id', 10001)->firstOrFail();

    expect($card->effects)->toBe($record['effects'])
        ->and($card->effects[0])->toHaveCount(12)
        ->and($card->effects[0][1])->toBe(5)
        ->and(in_array(-1, $card->effects[0], true))->toBeTrue();

    $raw = DB::table('support_cards')->where('support_id', 10001)->value('effects');

    expect(json_decode((string) $raw, true))->toBe($record['effects']);
});

it('round-trips both skill lists, and keeps a stored null from becoming an empty list', function (): void {
    // The projection is by name, so a key the writer forgets is a silent column that never fills. The
    // null half matters more than the list half: `hint_skills` null and `[]` render as two different
    // sentences on the card page, and a JSON column that stored null as `[]` would collapse them.
    $record = importedSupportCards()[0];

    (new StoreSupportCards)->handle([$record, [...$record, 'support_id' => 90030, 'hint_skills' => null, 'event_skills' => []]], supportCardImportUrl());

    $card = SupportCard::where('support_id', 10001)->firstOrFail();

    expect($card->hint_skills)->toBe($record['hint_skills'])
        ->and($card->event_skills)->toBe($record['event_skills'])
        ->and(DB::table('support_cards')->where('support_id', 90030)->value('hint_skills'))->toBeNull()
        ->and(SupportCard::where('support_id', 90030)->firstOrFail()->event_skills)->toBe([]);
});

it('writes no card at all when one record carries a type the column refuses', function (): void {
    // Nothing here invents an in-domain value for an out-of-domain type: the parser drops such a record,
    // which `GametoraSupportCardParserTest` pins. This pins the layer behind it, because the CHECK is
    // what still holds the day a writer reaches the table without passing through the parser, and the
    // failure has to cost the batch rather than leave three quarters of a catalogue in place (NFR-2).
    $records = array_slice(importedSupportCards(), 0, 3);
    $records[2]['type'] = 'turbo';

    expect(fn () => (new StoreSupportCards)->handle($records, supportCardImportUrl()))
        ->toThrow(QueryException::class);

    expect(SupportCard::count())->toBe(0);
});

it('imports the 35-row dictionary once and keeps exactly the four declaring calc', function (): void {
    $action = new StoreSupportEffects;

    expect($action->handle(importedSupportEffects(), supportEffectImportUrl()))
        ->toBe(['created' => 35, 'updated' => 0, 'skipped' => 0])
        ->and(SupportEffect::count())->toBe(35)
        ->and(SupportEffect::whereNotNull('calc')->count())->toBe(4)
        ->and(SupportEffect::whereNull('calc')->count())->toBe(31)
        ->and(SupportEffect::where('calc', 'mult')->pluck('effect_id')->sort()->values()->all())
        ->toBe([1, 27, 28])
        ->and(SupportEffect::where('effect_id', 19)->value('calc'))->toBe('add')
        ->and(SupportEffect::where('calc', 'flat')->count())->toBe(0)
        ->and(SupportEffect::whereNull('source_url')->count())->toBe(0)
        ->and(SupportEffect::whereNull('fetched_at')->count())->toBe(0);

    expect($action->handle(importedSupportEffects(), supportEffectImportUrl()))
        ->toBe(['created' => 0, 'updated' => 35, 'skipped' => 0])
        ->and(SupportEffect::count())->toBe(35);
});

it('writes no dictionary row when one record carries a calc the source never emits', function (): void {
    // `flat` is this repository's prose for an effect that declares no mode, and §1.4.8 makes the
    // absence load-bearing, so the column refuses the word. `SupportCardTest` pins the factory-level
    // refusal; this pins that an import cannot smuggle it in either.
    $records = importedSupportEffects();
    $records[0]['calc'] = 'flat';

    expect(fn () => (new StoreSupportEffects)->handle($records, supportEffectImportUrl()))
        ->toThrow(QueryException::class);

    expect(SupportEffect::count())->toBe(0);
});

it('names every effect id the catalogue references and keeps the nine it does not', function (): void {
    // The join the dictionary exists for. `support_cards.effects[n][0]` is an `effect_id`, so an anchor
    // naming no row is a deck panel printing a number where a label belongs.
    $cards = importedSupportCards();

    $referenced = [];

    foreach ($cards as $card) {
        foreach ($card['effects'] as $vector) {
            $referenced[$vector[0]] = true;
        }
    }

    (new StoreSupportEffects)->handle(importedSupportEffects(), supportEffectImportUrl());

    $dictionary = SupportEffect::query()->pluck('name_en', 'effect_id');

    expect($dictionary)->toHaveCount(35)
        ->and(array_keys($referenced))->toHaveCount(26);

    foreach (array_keys($referenced) as $effectId) {
        // Every id the cards reach for has a name, and none of those names is empty.
        expect($dictionary->get($effectId))->not->toBeNull();
    }

    // Nine published effects are named by no card in this document and still land, because the
    // dictionary is the source's, not this tool's subset: ids 20 to 24 and 29 are the six rows the
    // publisher marks `inactive`, and 33, 41 and 9991 are rows only the dictionary carries.
    $unused = array_values(array_diff(
        array_column(importedSupportEffects(), 'effect_id'),
        array_keys($referenced)
    ));

    expect($unused)->toBe([20, 21, 22, 23, 24, 29, 33, 41, 9991]);
});
