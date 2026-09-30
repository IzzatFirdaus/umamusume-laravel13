<?php

declare(strict_types=1);

use App\Models\DeckSlot;
use App\Models\SupportCard;
use App\Models\SupportEffect;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;

/*
 * Slice 2: Support card entities — model, factory and relationship tests.
 *
 * ADR-0014 authorized support card reference data and run linkage for Phase 1,
 * superseding ADR-0005. This suite covers the three models, their factories,
 * and the TrainingRun → DeckSlot relationship.
 */

// ── SupportCard model tests ────────────────────────────────────────────────

it('creates a support card from the factory', function (): void {
    $card = SupportCard::factory()->create();

    expect($card)->toBeInstanceOf(SupportCard::class)
        ->and($card->support_id)->toBeInt()
        ->and($card->name)->toContain('Test Card')
        ->and($card->rarity)->toBeIn([1, 2, 3])
        ->and($card->type)->toBeIn(['speed', 'stamina', 'power', 'guts', 'intelligence', 'friend', 'group']);
});

it('has correct rarity labels', function (): void {
    $r = SupportCard::factory()->r()->create();
    $sr = SupportCard::factory()->sr()->create();
    $ssr = SupportCard::factory()->ssr()->create();

    expect($r->rarityLabel())->toBe('R')
        ->and($sr->rarityLabel())->toBe('SR')
        ->and($ssr->rarityLabel())->toBe('SSR');
});

it('has correct type labels matching client vocabulary', function (): void {
    $speed = SupportCard::factory()->speed()->create();
    $wit = SupportCard::factory()->wit()->create();
    $friend = SupportCard::factory()->friend()->create();
    $group = SupportCard::factory()->group()->create();

    expect($speed->typeLabel())->toBe('Speed')
        ->and($wit->typeLabel())->toBe('Wit')
        ->and($friend->typeLabel())->toBe('Pal')
        ->and($group->typeLabel())->toBe('Group');
});

it('identifies friend and group type cards', function (): void {
    $friend = SupportCard::factory()->friend()->create();
    $group = SupportCard::factory()->group()->create();
    $speed = SupportCard::factory()->speed()->create();

    expect($friend->isFriendType())->toBeTrue()
        ->and($friend->isGroupType())->toBeFalse()
        ->and($group->isFriendType())->toBeFalse()
        ->and($group->isGroupType())->toBeTrue()
        ->and($speed->isFriendType())->toBeFalse()
        ->and($speed->isGroupType())->toBeFalse();
});

it('casts effects as array', function (): void {
    $card = SupportCard::factory()->create(['effects' => [[1, 10, 15, 20]]]);

    expect($card->effects)->toBeArray()
        ->and($card->effects)->toHaveCount(1);
});

it('casts dates correctly', function (): void {
    $card = SupportCard::factory()->create([
        'release_jp' => '2021-02-24',
        'release_global' => '2025-06-26',
    ]);

    expect($card->release_jp)->toBeInstanceOf(Carbon::class)
        ->and($card->release_global)->toBeInstanceOf(Carbon::class);
});

it('computes release status from dates', function (): void {
    $global = SupportCard::factory()->create(['release_global' => '2025-06-26', 'release_jp' => '2021-02-24']);
    $jpOnly = SupportCard::factory()->create(['release_global' => null, 'release_jp' => '2021-02-24']);

    // SQLite generated column — may need refresh after create
    $global->refresh();
    $jpOnly->refresh();

    expect($global->release_status)->toBe('Global')
        ->and($jpOnly->release_status)->toBe('JP-only');
});

// ── SupportEffect model tests ──────────────────────────────────────────────

it('creates a support effect from the factory', function (): void {
    $effect = SupportEffect::factory()->create();

    expect($effect)->toBeInstanceOf(SupportEffect::class)
        ->and($effect->effect_id)->toBeInt()
        ->and($effect->calc)->toBeIn(['flat', 'mult', 'add', 'level']);
});

it('has no timestamps', function (): void {
    $effect = SupportEffect::factory()->create();

    expect($effect->created_at)->toBeNull()
        ->and($effect->updated_at)->toBeNull();
});

// ── DeckSlot model tests ───────────────────────────────────────────────────

it('creates a deck slot from the factory', function (): void {
    $slot = DeckSlot::factory()->create();

    expect($slot)->toBeInstanceOf(DeckSlot::class)
        ->and($slot->slot_position)->toBeInt()
        ->and($slot->slot_position)->toBeBetween(1, 6);
});

it('identifies friend slot by position', function (): void {
    $friendSlot = DeckSlot::factory()->friendSlot()->create();
    $trainingSlot = DeckSlot::factory()->create(['slot_position' => 3]);

    expect($friendSlot->isFriendSlot())->toBeTrue()
        ->and($trainingSlot->isFriendSlot())->toBeFalse();
});

it('enforces unique slot position per run', function (): void {
    $run = TrainingRun::factory()->create();
    $card1 = SupportCard::factory()->create();
    $card2 = SupportCard::factory()->create();

    DeckSlot::factory()->create([
        'training_run_id' => $run->id,
        'support_card_id' => $card1->id,
        'slot_position' => 1,
    ]);

    expect(fn () => DeckSlot::factory()->create([
        'training_run_id' => $run->id,
        'support_card_id' => $card2->id,
        'slot_position' => 1,
    ]))->toThrow(QueryException::class);
});

// ── TrainingRun relationship tests ─────────────────────────────────────────

it('has many deck slots ordered by position', function (): void {
    $run = TrainingRun::factory()->create();
    $cards = SupportCard::factory()->count(3)->create();

    // Create slots out of order
    DeckSlot::factory()->create([
        'training_run_id' => $run->id,
        'support_card_id' => $cards[2]->id,
        'slot_position' => 3,
    ]);
    DeckSlot::factory()->create([
        'training_run_id' => $run->id,
        'support_card_id' => $cards[0]->id,
        'slot_position' => 1,
    ]);
    DeckSlot::factory()->create([
        'training_run_id' => $run->id,
        'support_card_id' => $cards[1]->id,
        'slot_position' => 2,
    ]);

    $slots = $run->deckSlots;

    expect($slots)->toHaveCount(3)
        ->and($slots[0]->slot_position)->toBe(1)
        ->and($slots[1]->slot_position)->toBe(2)
        ->and($slots[2]->slot_position)->toBe(3);
});

it('deletes deck slots when run is deleted', function (): void {
    $run = TrainingRun::factory()->create();
    $card = SupportCard::factory()->create();

    $slot = DeckSlot::factory()->create([
        'training_run_id' => $run->id,
        'support_card_id' => $card->id,
        'slot_position' => 1,
    ]);

    $run->delete();

    expect(DeckSlot::find($slot->id))->toBeNull();
});

// ── SupportCard relationship tests ─────────────────────────────────────────

it('belongs to an Umamusume character', function (): void {
    $umamusume = Umamusume::factory()->create();
    $card = SupportCard::factory()->create(['char_id' => $umamusume->id]);

    expect($card->umamusume)->not->toBeNull()
        ->and($card->umamusume->id)->toBe($umamusume->id);
});

it('allows null char_id for NPC staff cards', function (): void {
    $card = SupportCard::factory()->create(['char_id' => null]);

    expect($card->char_id)->toBeNull()
        ->and($card->umamusume)->toBeNull();
});

it('has many deck slots', function (): void {
    $card = SupportCard::factory()->create();
    $run1 = TrainingRun::factory()->create();
    $run2 = TrainingRun::factory()->create();

    DeckSlot::factory()->create([
        'training_run_id' => $run1->id,
        'support_card_id' => $card->id,
        'slot_position' => 1,
    ]);
    DeckSlot::factory()->create([
        'training_run_id' => $run2->id,
        'support_card_id' => $card->id,
        'slot_position' => 2,
    ]);

    expect($card->deckSlots)->toHaveCount(2);
});

it('restricts deletion when deck slots exist', function (): void {
    $card = SupportCard::factory()->create();
    $run = TrainingRun::factory()->create();

    DeckSlot::factory()->create([
        'training_run_id' => $run->id,
        'support_card_id' => $card->id,
        'slot_position' => 1,
    ]);

    expect(fn () => $card->delete())->toThrow(QueryException::class);
});
