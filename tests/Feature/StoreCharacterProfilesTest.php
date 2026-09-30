<?php

declare(strict_types=1);

use App\Actions\StoreCharacterProfiles;
use App\Models\Umamusume;
use App\Models\UmamusumeProfile;
use App\Services\DataPipeline\Parsers\GametoraCharacterProfileParser;
use Illuminate\Database\Eloquent\Model;

/*
 * The store action: the join through `external_ref`, the two `is_manual` gates, and the
 * provenance stamp. The parser test covers reading the body; this covers what happens to a row
 * once it has an id the catalog may or may not know.
 */
beforeEach(function (): void {
    $this->action = new StoreCharacterProfiles;
    $this->body = (string) file_get_contents(__DIR__.'/../Fixtures/gametora-characters.sample.json');
    $this->url = 'https://gametora.com/data/umamusume/characters.c6676539.json';
});

/**
 * @return list<array<string, mixed>>
 */
function parsedProfiles(string $body): array
{
    return (new GametoraCharacterProfileParser)->parse($body);
}

it('attaches a profile to the trainee whose external_ref names the row', function (): void {
    $umamusume = Umamusume::factory()->create(['external_ref' => 'gametora:char:1001']);

    $counts = $this->action->handle(parsedProfiles($this->body), $this->url, 'snapshots/x.html', 'Asia/Tokyo');

    // The filter drops the fixture's non-uma row (1095), so three records reach the store: 1001
    // has a trainee and is created, 2001 and 9040 do not and are skipped.
    expect($counts)->toBe(['created' => 1, 'updated' => 0, 'skipped' => 2])
        ->and($umamusume->fresh()->profile)->not->toBeNull()
        ->and($umamusume->fresh()->profile->name_ja)->toBe('スペシャルウィーク');
});

it('stamps provenance on the row it writes', function (): void {
    // ADR-0003 Amendment R3: the reference row carries its own four, and `umamusume` carries
    // none, which is the whole reason this is a sibling table.
    Umamusume::factory()->create(['external_ref' => 'gametora:char:1001']);

    $this->action->handle(parsedProfiles($this->body), $this->url, 'snapshots/x.html', 'Asia/Tokyo');

    $profile = UmamusumeProfile::sole();

    expect($profile->source_url)->toBe($this->url)
        ->and($profile->snapshot_path)->toBe('snapshots/x.html')
        ->and($profile->source_timezone)->toBe('Asia/Tokyo')
        ->and($profile->fetched_at)->not->toBeNull();
});

it('skips a row naming a trainee this catalog does not track', function (): void {
    // The document is wider than the roster even after the race filter, and a row with no matching
    // `umamusume` is skipped, not created and not an error. With no trainees seeded here, every
    // parsed record (the fixture's three uma rows) is skipped.
    $counts = $this->action->handle(parsedProfiles($this->body), $this->url, null, 'Asia/Tokyo');

    expect($counts['skipped'])->toBe(3)
        ->and(UmamusumeProfile::query()->count())->toBe(0);
});

it('updates its own row on a re-fetch rather than adding a second', function (): void {
    // PRD FR-B-5: idempotent by identity.
    Umamusume::factory()->create(['external_ref' => 'gametora:char:1001']);

    $this->action->handle(parsedProfiles($this->body), $this->url, null, 'Asia/Tokyo');

    $body = json_encode([
        ['char_id' => 1001, 'race' => 'uma', 'jp_name' => 'スペシャルウィーク', 'height' => 160, 'va_ja' => '和氣あず未'],
    ], JSON_UNESCAPED_UNICODE);

    $counts = $this->action->handle(parsedProfiles((string) $body), $this->url, null, 'Asia/Tokyo');

    expect($counts)->toBe(['created' => 0, 'updated' => 1, 'skipped' => 0])
        ->and(UmamusumeProfile::query()->count())->toBe(1)
        ->and(UmamusumeProfile::sole()->height)->toBe(160);
});

it('leaves a Trainer-corrected profile alone', function (): void {
    // PRD FR-B-4: a fetch does not overwrite a person.
    $umamusume = Umamusume::factory()->create(['external_ref' => 'gametora:char:1001']);
    UmamusumeProfile::factory()->manual()->create([
        'umamusume_id' => $umamusume->id,
        'height' => 999,
    ]);

    $counts = $this->action->handle(parsedProfiles($this->body), $this->url, null, 'Asia/Tokyo');

    expect($counts['skipped'])->toBe(3)
        ->and(UmamusumeProfile::sole()->height)->toBe(999);
});

it('writes no profile under a trainee the Trainer wrote by hand', function (): void {
    Umamusume::factory()->create([
        'external_ref' => 'gametora:char:1001',
        'is_manual' => true,
    ]);

    $counts = $this->action->handle(parsedProfiles($this->body), $this->url, null, 'Asia/Tokyo');

    expect($counts['created'])->toBe(0)
        ->and(UmamusumeProfile::query()->count())->toBe(0);
});

it('skips rather than guessing when one char ref names two trainees', function (): void {
    // `external_ref` is indexed but not unique. An ambiguous ref means the source's own mapping
    // is broken, and attaching to whichever row an unordered lookup returned would bury that
    // under a full run of confident-looking profiles.
    Umamusume::factory()->count(2)->create(['external_ref' => 'gametora:char:1001']);

    $counts = $this->action->handle(parsedProfiles($this->body), $this->url, null, 'Asia/Tokyo');

    expect($counts['skipped'])->toBe(3)
        ->and(UmamusumeProfile::query()->count())->toBe(0);
});

it('stores a null from the source rather than merging around it', function (): void {
    // One document owns this row, so a null is a statement about the trainee — the opposite trade
    // from PromoteMatchedRecord, which protects letters a second publisher may have written.
    // 9040 (Darley Arabian) is a trainee the fixture gives no va_en, so the store writes null over
    // the stale value. (It was 1095 before the race filter; 1095 is the non-uma namesake row the
    // filter drops, so it can no longer stand in for a trainee.)
    $umamusume = Umamusume::factory()->create(['external_ref' => 'gametora:char:9040']);
    UmamusumeProfile::factory()->create([
        'umamusume_id' => $umamusume->id,
        'va_en' => 'a stale value from an earlier body',
    ]);

    $this->action->handle(parsedProfiles($this->body), $this->url, null, 'Asia/Tokyo');

    expect($umamusume->fresh()->profile->va_en)->toBeNull();
});

it('drops a refused key a record should not carry instead of spreading it into the write', function (): void {
    // The store projects named columns; it never spreads a record. Feed a real parsed record and
    // graft on the keys the parser is forbidden to emit — `rl` with a death date, plus `sex`,
    // `race`, `va_link`, `jp_name_real` — and the `char_external_ref` lookup key that is not a
    // column. Under preventSilentlyDiscardingAttributes() a spread would throw before the row
    // lands, so writing one clean row with none of the refused keys persisted is the store-side
    // half of the allowlist: the parser refuses to emit them, the store refuses to persist them.
    $wasPreventing = Model::preventsSilentlyDiscardingAttributes();
    Model::preventSilentlyDiscardingAttributes(true);

    try {
        Umamusume::factory()->create(['external_ref' => 'gametora:char:1001']);

        $records = parsedProfiles($this->body);
        $records[0]['rl'] = ['death' => '2018-04-27'];
        $records[0]['sex'] = 1;
        $records[0]['race'] = 'a value no column can hold';
        $records[0]['va_link'] = 'https://example.invalid/never-followed';
        $records[0]['jp_name_real'] = 'a real person\'s pseudonym';

        $counts = $this->action->handle($records, $this->url, null, null);
    } finally {
        Model::preventSilentlyDiscardingAttributes($wasPreventing);
    }

    $profile = UmamusumeProfile::where('umamusume_id', Umamusume::sole()->id)->sole();

    expect($counts)->toMatchArray(['created' => 1])
        ->and($profile->getAttribute('rl'))->toBeNull()
        ->and($profile->getAttribute('sex'))->toBeNull()
        ->and($profile->getAttribute('va_link'))->toBeNull()
        ->and($profile->getAttribute('jp_name_real'))->toBeNull();
});

it('writes a null for every value column of an all-null record, never a coerced 0', function (): void {
    // The store's contract is records, not JSON, so this builds an in-memory record rather than a
    // fabricated source row: inventing a document the source never published would test a
    // hypothetical, and the fixture cannot reach this path because the source always carries
    // height, the name and the birth day/month, so no row here has a null in those columns. Every
    // value column null must persist as null — an (int) cast on any of them would write 0, and
    // that is D-220's exact error: "the source did not say" is not "the source said 0".
    Umamusume::factory()->create(['external_ref' => 'gametora:char:9999']);

    $record = array_fill_keys([
        'char_external_ref', 'name_ja', 'va_ja', 'va_en', 'birth_year', 'birth_month', 'birth_day',
        'height', 'three_sizes_b', 'three_sizes_h', 'three_sizes_w',
    ], null);
    $record['char_external_ref'] = 'gametora:char:9999';

    $counts = $this->action->handle([$record], $this->url, null, null);

    $profile = UmamusumeProfile::where('umamusume_id', Umamusume::sole()->id)->sole();

    expect($counts)->toMatchArray(['created' => 1, 'updated' => 0, 'skipped' => 0])
        ->and(array_filter(array_map(
            fn (string $column): bool => $profile->getAttribute($column) !== null,
            ['name_ja', 'va_ja', 'va_en', 'birth_year', 'birth_month', 'birth_day',
                'height', 'three_sizes_b', 'three_sizes_h', 'three_sizes_w'],
        )))->toBe([]);
});
