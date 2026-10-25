<?php

declare(strict_types=1);

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
    $card = SupportCard::factory()->create();

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
