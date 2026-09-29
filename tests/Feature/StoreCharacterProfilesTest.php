<?php

declare(strict_types=1);

use App\Actions\StoreCharacterProfiles;
use App\Models\Umamusume;
use App\Models\UmamusumeProfile;
use App\Services\DataPipeline\Parsers\GametoraCharacterProfileParser;

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

    expect($counts)->toBe(['created' => 1, 'updated' => 0, 'skipped' => 3])
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
    // The document is wider than the roster: 163 rows against 135 trainees. Skipping is the
    // whole of the "28 skipped" a real fetch reports, and it is not an error.
    $counts = $this->action->handle(parsedProfiles($this->body), $this->url, null, 'Asia/Tokyo');

    expect($counts['skipped'])->toBe(4)
        ->and(UmamusumeProfile::query()->count())->toBe(0);
});

it('updates its own row on a re-fetch rather than adding a second', function (): void {
    // PRD FR-B-5: idempotent by identity.
    Umamusume::factory()->create(['external_ref' => 'gametora:char:1001']);

    $this->action->handle(parsedProfiles($this->body), $this->url, null, 'Asia/Tokyo');

    $body = json_encode([
        ['char_id' => 1001, 'jp_name' => 'スペシャルウィーク', 'height' => 160, 'va_ja' => '和氣あず未'],
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

    expect($counts['skipped'])->toBe(4)
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

    expect($counts['skipped'])->toBe(4)
        ->and(UmamusumeProfile::query()->count())->toBe(0);
});

it('stores a null from the source rather than merging around it', function (): void {
    // One document owns this row, so a null is a statement about the trainee — the opposite trade
    // from PromoteMatchedRecord, which protects letters a second publisher may have written.
    $umamusume = Umamusume::factory()->create(['external_ref' => 'gametora:char:1095']);
    UmamusumeProfile::factory()->create([
        'umamusume_id' => $umamusume->id,
        'va_en' => 'a stale value from an earlier body',
    ]);

    $this->action->handle(parsedProfiles($this->body), $this->url, null, 'Asia/Tokyo');

    expect($umamusume->fresh()->profile->va_en)->toBeNull();
});
