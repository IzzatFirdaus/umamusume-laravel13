<?php

declare(strict_types=1);

use App\Enums\CardRarity;
use App\Models\DeckSlot;
use App\Models\SupportCard;
use App\Models\TrainingRun;
use App\Services\Career\SetupDraft;
use Illuminate\Support\Facades\Schema;

/*
 * A-0.5 (ADR-0029 D1, owner ruling R11): the per-run card level on `deck_slots`.
 *
 * The wizard writes the session draft and the run-scoped builder writes the slot row; both are pinned
 * here. The column is a numerator only: the cap and the limit-break count are collection state declined
 * by ADR-0014, so the migration contract is asserted against the table itself.
 */

it('stores the level on the setup draft and writes no collection columns', function (): void {
    $card = SupportCard::factory()->create();

    test()->put(route('career.deck.store'), [
        'deck' => [
            1 => ['support_card_id' => (string) $card->id, 'ownership' => 'OWNED', 'card_level' => 35],
            2 => ['support_card_id' => '', 'ownership' => '', 'card_level' => ''],
        ],
    ])->assertSessionHasNoErrors();

    $draft = SetupDraft::deck();

    expect($draft[0]['card_level'])->toBe(35)
        ->and($draft[1]['card_level'])->toBeNull();

    // ADR-0029 D1's contract: the numerator is stored; the collection half is not.
    $columns = Schema::getColumnListing('deck_slots');

    expect($columns)->toContain('card_level')
        ->not->toContain('card_cap')
        ->not->toContain('limit_break');
});

it('writes the level the run-scoped deck posts onto the slot row', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $card = SupportCard::factory()->ssr()->create();

    test()->post(route('runs.deck.sync', $run), [
        'deck' => [
            1 => ['support_card_id' => (string) $card->id, 'ownership' => 'OWNED', 'card_level' => 50],
        ],
    ])->assertSessionHasNoErrors();

    expect(DeckSlot::query()
        ->where('training_run_id', $run->id)
        ->where('slot_position', 1)
        ->value('card_level'))->toBe(50);
});

/*
 * AUD-20261030-001 F-03: a level is bounded by its rarity's ceiling, not by the highest ceiling in the
 * game. `max:50` on the field is the SSR's number, so an R card at 45 passed every field rule and stored;
 * the after-hook reads the same `CardRarity` ladder `DeckSummary.vue` prints, so neither surface can
 * claim a different top. Both writers share the request, so both refuse, and the refusal lands on the
 * position's own field with the position's own label.
 */

it('refuses a level above the rarity ceiling and names the slot in its own words', function (): void {
    $card = SupportCard::factory()->create(['rarity' => CardRarity::OneStar]);
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    test()->post(route('runs.deck.sync', $run), [
        'deck' => [1 => ['support_card_id' => (string) $card->id, 'ownership' => 'OWNED', 'card_level' => 45]],
    ])->assertSessionHasErrors([
        'deck.1.card_level' => 'Slot 1 cannot record Lvl 45: an R card reaches Lvl 40 at four breaks and no further.',
    ]);

    test()->post(route('runs.deck.sync', $run), [
        'deck' => [6 => ['support_card_id' => (string) $card->id, 'ownership' => 'RENTED', 'card_level' => 41]],
    ])->assertSessionHasErrors([
        'deck.6.card_level' => 'Slot 6 · Friends cannot record Lvl 41: an R card reaches Lvl 40 at four breaks and no further.',
    ]);
});

it('accepts the top level each rarity actually reaches', function (): void {
    $r = SupportCard::factory()->create(['rarity' => CardRarity::OneStar]);
    $sr = SupportCard::factory()->create(['rarity' => CardRarity::TwoStar]);
    $ssr = SupportCard::factory()->create(['rarity' => CardRarity::ThreeStar]);
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    test()->post(route('runs.deck.sync', $run), [
        'deck' => [
            1 => ['support_card_id' => (string) $r->id, 'ownership' => 'OWNED', 'card_level' => 40],
            2 => ['support_card_id' => (string) $sr->id, 'ownership' => 'OWNED', 'card_level' => 45],
            3 => ['support_card_id' => (string) $ssr->id, 'ownership' => 'RENTED', 'card_level' => 50],
        ],
    ])->assertSessionHasNoErrors();
});

it('keeps the level already entered when a higher one is refused, on both writers', function (): void {
    $card = SupportCard::factory()->create(['rarity' => CardRarity::OneStar]);

    test()->put(route('career.deck.store'), [
        'deck' => [1 => ['support_card_id' => (string) $card->id, 'ownership' => 'OWNED', 'card_level' => 30]],
    ])->assertSessionHasNoErrors();

    test()->put(route('career.deck.store'), [
        'deck' => [1 => ['support_card_id' => (string) $card->id, 'ownership' => 'OWNED', 'card_level' => 45]],
    ])->assertSessionHasErrors('deck.1.card_level');

    expect(SetupDraft::deck()[0]['card_level'])->toBe(30);

    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    test()->post(route('runs.deck.sync', $run), [
        'deck' => [1 => ['support_card_id' => (string) $card->id, 'ownership' => 'OWNED', 'card_level' => 34]],
    ])->assertSessionHasNoErrors();

    test()->post(route('runs.deck.sync', $run), [
        'deck' => [1 => ['support_card_id' => (string) $card->id, 'ownership' => 'OWNED', 'card_level' => 60]],
    ])->assertSessionHasErrors('deck.1.card_level');

    expect(DeckSlot::query()
        ->where('training_run_id', $run->id)
        ->where('slot_position', 1)
        ->value('card_level'))->toBe(34);
});
