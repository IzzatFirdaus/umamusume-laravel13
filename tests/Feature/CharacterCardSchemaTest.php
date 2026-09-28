<?php

declare(strict_types=1);

use App\Enums\CardRarity;
use App\Models\CharacterCard;
use App\Models\Umamusume;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

/*
 * ADR-0008 / PRD FR-A-6: costume cards are their own rows, keyed by the source's
 * own card id, and they carry their provenance inline the way every other
 * reference table here does. These tests pin the grain (one row per card), the
 * identity rule (a re-fetch cannot duplicate), the delete rule (a card never
 * outlives its trainee) and the casts the read path in Tasks 10-12 depends on.
 */

it('creates a card, links it to its trainee and casts its rarity', function (): void {
    $umamusume = Umamusume::factory()->create();
    $card = CharacterCard::factory()->create([
        'umamusume_id' => $umamusume->id,
        'title' => '[RUN! RUIN! LAUNCHER!]',
        'rarity' => CardRarity::ThreeStar,
    ]);

    expect($card->rarity)->toBe(CardRarity::ThreeStar)
        ->and($card->umamusume->id)->toBe($umamusume->id)
        ->and($umamusume->cards)->toHaveCount(1);
});

it('keeps card ids unique so a re-fetch cannot duplicate a card', function (): void {
    $first = CharacterCard::factory()->create(['card_id' => 900701]);

    expect(fn () => CharacterCard::factory()->create([
        'card_id' => $first->card_id,
        'umamusume_id' => Umamusume::factory()->create()->id,
    ]))->toThrow(QueryException::class);
});

it('deletes a trainee\'s cards with her', function (): void {
    $umamusume = Umamusume::factory()->create();
    CharacterCard::factory()->count(3)->create(['umamusume_id' => $umamusume->id]);

    $umamusume->delete();

    expect(CharacterCard::query()->count())->toBe(0);
});

it('stores the source character link the parser has always emitted', function (): void {
    expect(Schema::hasColumn('umamusume', 'external_ref'))->toBeTrue();
});

it('ships exactly the columns the card-layer docs list', function (): void {
    // ADR-0008's Decision table, ARCHITECTURE.md §3's character_cards fence, the
    // ESSENTIALS digest line and D-30 all name the same twelve fillable columns
    // plus `id` and timestamps. This is the schema side of that agreement, and it
    // is an exact pin rather than a `toContain` sweep on purpose: a column added
    // to the migration without moving those four docs fails here, and a column
    // they name that no longer ships fails too. Schema change and digest change
    // then travel together, which is the AGENTS.md Architect rule made checkable.
    expect(Schema::getColumnListing('character_cards'))->toBe([
        'id',
        'card_id',
        'umamusume_id',
        'title',
        'rarity',
        'global_release_date',
        'is_debut_form',
        'unconfirmed',
        'source_url',
        'snapshot_path',
        'fetched_at',
        'source_timezone',
        'is_manual',
        'created_at',
        'updated_at',
    ]);
});

it('reads fetched_at as a datetime so the card can be shown in the display zone', function (): void {
    // Task 11's card row prints $card->fetched_at->timezone(...)->format(...).
    // A 'timestamp' column without the cast hands back a string and that chain
    // dies on the string; the JST day proves the value moved through a real zone.
    $card = CharacterCard::factory()
        ->manual()
        ->create([
            'fetched_at' => '2026-06-30 20:00:00',
            'global_release_date' => '2026-06-01',
            'unconfirmed' => true,
        ])
        ->fresh();

    expect($card->fetched_at)->toBeInstanceOf(Carbon::class)
        ->and($card->fetched_at->timezone('Asia/Tokyo')->format('Y-m-d'))->toBe('2026-07-01');
});

it('reads the three card flags as booleans, not as whatever the driver stored', function (): void {
    $card = CharacterCard::factory()
        ->manual()
        ->unconfirmed()
        ->debut()
        ->create()
        ->fresh();

    expect($card->is_manual)->toBeTrue()
        ->and($card->unconfirmed)->toBeTrue()
        ->and($card->is_debut_form)->toBeTrue();

    $fetched = CharacterCard::factory()->create()->fresh();

    expect($fetched->is_manual)->toBeFalse()
        ->and($fetched->unconfirmed)->toBeFalse()
        ->and($fetched->is_debut_form)->toBeFalse();
});

it('refuses a card with no source and no Global date, which are the two rules that make a row a card', function (): void {
    foreach (['source_url' => null, 'global_release_date' => null] as $column => $value) {
        expect(fn () => CharacterCard::factory()->create([$column => $value]))
            ->toThrow(QueryException::class, $column);
    }
});

it('orders rarity by the glyph run, since hue cannot rank three near-identical fills', function (): void {
    expect(CardRarity::cases())->toHaveCount(3)
        ->and(CardRarity::OneStar->stars())->toBe('★')
        ->and(CardRarity::TwoStar->stars())->toBe('★★')
        ->and(CardRarity::ThreeStar->stars())->toBe('★★★');
});
