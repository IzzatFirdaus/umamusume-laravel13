<?php

declare(strict_types=1);

use App\Models\DeckSlot;
use App\Models\SupportCard;
use App\Models\SupportEffect;
use App\Models\TrainingRun;

/*
 * Slice 2: the deck panel on the run screen and the picker that fills it.
 *
 * A POST test cannot see a `disabled` attribute, a mislabelled control, or an option list that dropped
 * the card already equipped, so the render assertions here read the returned HTML rather than only the
 * rows.
 *
 * The write side is delete-then-insert, which is the shape the unique index on
 * (training_run_id, slot_position) forces: a card that moves from slot two to slot three has to leave
 * slot two, and an upsert keyed on the card alone would leave it standing in both. That is tested
 * directly, because it is the failure a later "just use sync()" refactor would reintroduce.
 */

function deckRun(): TrainingRun
{
    return TrainingRun::factory()->create(['scenario' => 'ura_finale']);
}

/**
 * @param  array<int, int|null>  $positionToCardId
 * @return array<string, array<string, int|null>>
 */
function deckPayload(array $positionToCardId): array
{
    $deck = [];

    foreach ($positionToCardId as $position => $cardId) {
        $deck[$position] = ['support_card_id' => $cardId];
    }

    return ['deck' => $deck];
}

// ── the write ──────────────────────────────────────────────────────────────

it('stores a full six-card deck at the positions chosen', function (): void {
    $run = deckRun();
    $cards = SupportCard::factory()->count(6)->create();

    test()->post("/training-runs/{$run->id}/deck", deckPayload(array_combine(range(1, 6), $cards->pluck('id')->all())))
        ->assertRedirect("/training-runs/{$run->id}");

    $slots = $run->fresh()->deckSlots;

    expect($slots)->toHaveCount(6)
        ->and($slots->pluck('slot_position')->all())->toBe([1, 2, 3, 4, 5, 6])
        ->and($slots->first()->support_card_id)->toBe($cards[0]->id);
});

it('moves a card to a new slot without leaving it in the old one', function (): void {
    $run = deckRun();
    $card = SupportCard::factory()->create();

    test()->post("/training-runs/{$run->id}/deck", deckPayload([2 => $card->id]));
    expect($run->fresh()->deckSlots->pluck('slot_position')->all())->toBe([2]);

    test()->post("/training-runs/{$run->id}/deck", deckPayload([3 => $card->id]));

    $after = $run->fresh()->deckSlots;

    expect($after)->toHaveCount(1)
        ->and($after->first()->slot_position)->toBe(3)
        ->and($after->first()->support_card_id)->toBe($card->id);
});

it('clears a slot the Trainer sets back to not equipped', function (): void {
    $run = deckRun();
    $first = SupportCard::factory()->create();
    $second = SupportCard::factory()->create();

    test()->post("/training-runs/{$run->id}/deck", deckPayload([1 => $first->id, 2 => $second->id]));
    test()->post("/training-runs/{$run->id}/deck", deckPayload([1 => $first->id, 2 => null]));

    $after = $run->fresh()->deckSlots;

    expect($after)->toHaveCount(1)
        ->and($after->first()->slot_position)->toBe(1);
});

it('accepts a run with no deck at all, which is what a Trainer who cannot remember has', function (): void {
    $run = deckRun();
    DeckSlot::factory()->create(['training_run_id' => $run->id]);

    test()->post("/training-runs/{$run->id}/deck", deckPayload([1 => null, 2 => null]))
        ->assertRedirect();

    expect($run->fresh()->deckSlots)->toHaveCount(0);
});

it('refuses the same card in two slots, because duplicates are spent on breaking', function (): void {
    // UMAMUSUME_REFERENCE.md §1.4.5: two copies of one card cannot sit in a deck.
    $run = deckRun();
    $card = SupportCard::factory()->create();

    test()->post("/training-runs/{$run->id}/deck", deckPayload([1 => $card->id, 2 => $card->id]))
        ->assertSessionHasErrors('deck');

    expect($run->fresh()->deckSlots)->toHaveCount(0);
});

it('refuses a position outside the six a deck has', function (): void {
    $run = deckRun();
    $card = SupportCard::factory()->create();

    test()->post("/training-runs/{$run->id}/deck", deckPayload([7 => $card->id]))
        ->assertSessionHasErrors('deck');

    expect($run->fresh()->deckSlots)->toHaveCount(0);
});

it('refuses a card id that is not in the catalogue and says why', function (): void {
    $run = deckRun();

    test()->post("/training-runs/{$run->id}/deck", deckPayload([1 => 424242]))
        ->assertSessionHasErrors('deck.1.support_card_id');
});

it('writes nothing when one slot is bad, so a run is never left half-cleared', function (): void {
    $run = deckRun();
    $good = SupportCard::factory()->create();
    DeckSlot::factory()->atPosition(1)->create(['training_run_id' => $run->id, 'support_card_id' => $good->id]);

    test()->post("/training-runs/{$run->id}/deck", deckPayload([1 => $good->id, 2 => 999999]))
        ->assertSessionHasErrors();

    $after = $run->fresh()->deckSlots;

    expect($after)->toHaveCount(1)
        ->and($after->first()->slot_position)->toBe(1);
});

// ── the render ─────────────────────────────────────────────────────────────

it('renders the deck panel with one picker per slot on the run screen', function (): void {
    $run = deckRun();
    SupportCard::factory()->create(['release_global' => '2025-06-26', 'char_name' => 'Special Week']);

    test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->assertSee('Support deck')
        ->assertSee('name="deck[1][support_card_id]"', false)
        ->assertSee('name="deck[6][support_card_id]"', false)
        ->assertSee('Slot 6 · Friends', false)
        ->assertSee('Not equipped');
});

it('names the absence rather than showing an empty frame when nothing is recorded', function (): void {
    // C-7: every data view renders its empty state, and D-220 wants the absence explained.
    $run = deckRun();

    test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->assertSee('No support cards recorded for this run', false);
});

it('lists an equipped card with its slot, its type and its rarity word', function (): void {
    $run = deckRun();
    $card = SupportCard::factory()->ssr()->wit()->create([
        'char_name' => 'Quiet Star',
        'title_en' => '[Osenai Dancer]',
        'release_global' => '2025-06-26',
    ]);
    DeckSlot::factory()->atPosition(4)->create(['training_run_id' => $run->id, 'support_card_id' => $card->id]);

    test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->assertSee('Quiet Star [Osenai Dancer]')
        ->assertSee('Slot 4')
        ->assertSee('SSR Wit');
});

it('labels slot six Friends even when a stat card sits there', function (): void {
    // The role belongs to the slot, not the card (ADR-0014 correction 1), so a Speed card parked at six
    // must still read as the friend slot.
    $run = deckRun();
    $speed = SupportCard::factory()->speed()->create(['release_global' => '2025-06-26', 'char_name' => 'Tokai Teio']);
    DeckSlot::factory()->friendSlot()->create(['training_run_id' => $run->id, 'support_card_id' => $speed->id]);

    test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->assertSee('Friends')
        ->assertSee('Tokai Teio');
});

it('badges a linked character on the scenario, derived on read', function (): void {
    $run = deckRun();
    $linked = SupportCard::factory()->create([
        'char_name' => 'Aoi Kiryuin',
        'char_id' => 9004,
        'type' => 'friend',
        'release_global' => '2025-06-26',
    ]);
    $other = SupportCard::factory()->create(['char_name' => 'Special Week', 'release_global' => '2025-06-26']);

    DeckSlot::factory()->atPosition(1)->create(['training_run_id' => $run->id, 'support_card_id' => $linked->id]);
    DeckSlot::factory()->atPosition(2)->create(['training_run_id' => $run->id, 'support_card_id' => $other->id]);

    // URA Finale's only linked character is Aoi Kiryuin, so exactly one row carries the badge.
    $html = test()->get("/training-runs/{$run->id}")->assertOk()->getContent();

    expect(substr_count($html, 'Scenario Link'))->toBe(1);
});

it('offers only Global releases in the picker', function (): void {
    $run = deckRun();
    SupportCard::factory()->create(['char_name' => 'Global Card', 'release_global' => '2025-06-26']);
    SupportCard::factory()->jpOnly()->create(['char_name' => 'Japan Only Card']);

    test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->assertSee('Global Card')
        ->assertDontSee('Japan Only Card');
});

it('still offers a JP-only card the run already uses', function (): void {
    // Otherwise a Trainer logging an older deck sees their own card listed below and absent from the
    // select, so re-saving drops it with no way to put it back.
    $run = deckRun();
    $jpOnly = SupportCard::factory()->jpOnly()->create(['char_name' => 'Forever Young', 'title_en' => '[New Order]']);
    DeckSlot::factory()->atPosition(1)->create(['training_run_id' => $run->id, 'support_card_id' => $jpOnly->id]);

    test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->assertSee('Forever Young [New Order]');
});

it('keeps the six picks a Trainer made when the server rejects the deck', function (): void {
    // D-3's finding applied to this form: a failed submit must not return the picker to its defaults.
    // Taken as one round trip with `followingRedirects()`, because that is what the browser does and it
    // is the only way the flashed `old()` input reaches the rendered select.
    $run = deckRun();
    $good = SupportCard::factory()->create(['char_name' => 'Keep Me', 'release_global' => '2025-06-26']);

    // The Referer is sent because a real browser sends one from the form page, and `back()` has no
    // other way to find the run: without it the failure lands on the run index, where the picker the
    // Trainer was filling in does not exist at all.
    test()->followingRedirects()
        ->post(
            "/training-runs/{$run->id}/deck",
            deckPayload([1 => $good->id, 2 => 999999]),
            ['HTTP_REFERER' => url("/training-runs/{$run->id}")]
        )
        ->assertOk()
        ->assertSee('That card is not in the catalogue')
        // Blade's `@selected` echoes the bare attribute, not `selected="selected"`.
        ->assertSee('value="'.$good->id.'" selected', false);
});

it('shows the deck panel on a run that names no scenario', function (): void {
    // Every scenario has six slots, so the panel is not scenario-composed and must not hide behind the
    // `hasScenario()` guard the scenario panels sit under.
    $run = TrainingRun::factory()->create(['scenario' => null]);

    test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->assertSee('Support deck')
        ->assertSee('deck[1][support_card_id]', false);
});

it('lists an equipped card effect at its highest stated anchor and names that basis', function (): void {
    // The vector is the export's own shape: id then eleven anchors at levels 1, 5, 10 ... 50, and the
    // highest stated here is level 45, not level 50. The figure must not read as a level this run holds,
    // so the panel carries the basis sentence beside it (D-256).
    $run = deckRun();
    SupportEffect::factory()->create(['effect_id' => 1, 'name_en' => 'Friendship Bonus', 'symbol' => 'percent']);
    $card = SupportCard::factory()->create([
        'char_name' => 'Quiet Star',
        'release_global' => '2025-06-26',
        'effects' => [[1, 10, -1, -1, -1, -1, 20, 20, -1, -1, 25, -1]],
    ]);
    DeckSlot::factory()->atPosition(1)->create(['training_run_id' => $run->id, 'support_card_id' => $card->id]);

    test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->assertSee('Friendship Bonus 25%', false)
        ->assertSee('highest stated anchor', false);
});

it('marks an anchor with no dictionary row instead of inventing a label', function (): void {
    // D-20: a gap is shown as a gap. The id is the source's own number, so printing it states a fact
    // rather than guessing a word for an effect the dictionary does not name.
    $run = deckRun();
    $card = SupportCard::factory()->create([
        'char_name' => 'Quiet Star',
        'release_global' => '2025-06-26',
        'effects' => [[77, -1, -1, -1, -1, -1, -1, -1, -1, -1, 5, -1]],
    ]);
    DeckSlot::factory()->atPosition(1)->create(['training_run_id' => $run->id, 'support_card_id' => $card->id]);

    test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->assertSee('[Unverified] effect 77', false);
});

it('renders no effect line for a card whose vector states nothing at any level', function (): void {
    // An all-`-1` vector is a card the source gives no figure for. A line of nothing would read as a
    // fact about the effect rather than as an absence, so the row carries no line at all.
    $run = deckRun();
    SupportEffect::factory()->create(['effect_id' => 2, 'name_en' => 'Mood Effect', 'symbol' => 'percent']);
    $card = SupportCard::factory()->create([
        'char_name' => 'Quiet Star',
        'release_global' => '2025-06-26',
        'effects' => [[2, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1]],
    ]);
    DeckSlot::factory()->atPosition(1)->create(['training_run_id' => $run->id, 'support_card_id' => $card->id]);

    test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->assertSee('Quiet Star')
        ->assertDontSee('Mood Effect', false);
});
