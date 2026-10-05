<?php

declare(strict_types=1);

use App\Models\DeckSlot;
use App\Models\SupportCard;
use App\Models\SupportEffect;
use App\Models\TrainingRun;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Slice 2: the deck panel on the run screen and the picker that fills it.
 *
 * A POST test cannot see a `disabled` attribute, a mislabelled control, or an option list that dropped
 * the card already equipped, so the render assertions here read the props the page resolves; the
 * rendered DOM half of each claim is carried by the browser spec.
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
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            // The picker's `deck[N][support_card_id]` fields are built from these six rows, one per
            // position, with the first open and the rest reading `Not equipped` from an empty pick.
            ->has('deck.slots', 6)
            ->where('deck.slots', fn (Collection $slots): bool => $slots->pluck('position')->all() === [1, 2, 3, 4, 5, 6]
                && $slots->every(fn (array $slot): bool => $slot['selected'] === '' && $slot['selected_name'] === null))
            ->where('deck.slots.0.label', 'Slot 1')
            ->where('deck.slots.5.label', 'Slot 6 · Friends')
            ->where('deck.openSlot', 1));
});

it('names the absence rather than showing an empty frame when nothing is recorded', function (): void {
    // C-7: every data view renders its empty state, and D-220 wants the absence explained.
    $run = deckRun();

    test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            // `DeckPanel` keys its empty-state copy off this one count; the copy itself is rendered.
            ->has('deck.equipped', 0));
});

it('lists an equipped card with its slot, its type, its rarity word and a link to its own page', function (): void {
    $run = deckRun();
    $card = SupportCard::factory()->ssr()->wit()->create([
        'char_name' => 'Quiet Star',
        'title_en' => '[Osenai Dancer]',
        'release_global' => '2025-06-26',
    ]);
    DeckSlot::factory()->atPosition(4)->create(['training_run_id' => $run->id, 'support_card_id' => $card->id]);

    test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('deck.equipped.0.card_name', 'Quiet Star [Osenai Dancer]')
            ->where('deck.equipped.0.slot_word', 'Slot 4')
            ->where('deck.equipped.0.rarity_type', 'SSR Wit')
            // The panel is the entry point a Trainer actually reaches a card from: they logged the deck, now
            // they want the effect figures behind it. Asserted as the URL the row's anchor binds to, since
            // the visible text above is already pinned and a `<span>` would render identically to a name check.
            ->where('deck.equipped.0.card_url', route('support-cards.show', $card)));
});

it('labels slot six Friends even when a stat card sits there', function (): void {
    // The role belongs to the slot, not the card (ADR-0014 correction 1), so a Speed card parked at six
    // must still read as the friend slot.
    $run = deckRun();
    $speed = SupportCard::factory()->speed()->create(['release_global' => '2025-06-26', 'char_name' => 'Tokai Teio']);
    DeckSlot::factory()->friendSlot()->create(['training_run_id' => $run->id, 'support_card_id' => $speed->id]);

    test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('deck.equipped.0.slot_word', 'Friends')
            ->where('deck.equipped.0.card_name', 'Tokai Teio [Tracen Academy]'));
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
    $deck = test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Runs/Show')->has('deck.equipped', 2))
        ->inertiaProps('deck');

    expect(collect($deck['equipped'])->where('scenario_link', true))
        ->toHaveCount(1)
        ->and(collect($deck['equipped'])->firstWhere('scenario_link', true)['card_name'])->toBe('Aoi Kiryuin [Tracen Academy]');
});

it('offers only Global releases in the picker', function (): void {
    $run = deckRun();
    SupportCard::factory()->create(['char_name' => 'Global Card', 'release_global' => '2025-06-26']);
    SupportCard::factory()->jpOnly()->create(['char_name' => 'Japan Only Card']);

    $labels = collect(test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Runs/Show')->has('deck.options', 1))
        ->inertiaProps('deck')['options'])->pluck('label');

    expect($labels)->toHaveCount(1)
        ->and(str_starts_with($labels[0], 'Global Card'))->toBeTrue();
});

it('still offers a JP-only card the run already uses', function (): void {
    // Otherwise a Trainer logging an older deck sees their own card listed below and absent from the
    // select, so re-saving drops it with no way to put it back.
    $run = deckRun();
    $jpOnly = SupportCard::factory()->jpOnly()->create(['char_name' => 'Forever Young', 'title_en' => '[New Order]']);
    DeckSlot::factory()->atPosition(1)->create(['training_run_id' => $run->id, 'support_card_id' => $jpOnly->id]);

    $labels = collect(test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Runs/Show'))
        ->inertiaProps('deck')['options'])->pluck('label');

    expect($labels->contains(fn (string $label): bool => str_starts_with($label, 'Forever Young [New Order]')))->toBeTrue();
});

it('keeps the six picks a Trainer made when the server rejects the deck', function (): void {
    // D-3's finding applied to this form: a failed submit must not return the picker to its defaults.
    // Taken as one round trip with `followingRedirects()`, because that is what the browser does and it
    // is the only way the flashed `old()` input reaches the resolved picker.
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
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            // The refusal lands on the slot that erred, and that is the slot the picker reopens.
            // The envelope is keyed by the field name verbatim, which is the key the picker reads back.
            ->where('errors', fn (Collection $errors): bool => str_contains($errors['deck.2.support_card_id'] ?? '', 'That card is not in the catalogue'))
            // The pick survives, read back two ways: the name beside the slot, and the value the
            // next submit carries. Slot 1 posts through its closed-slot path.
            ->where('deck.slots.0.selected', (string) $good->id)
            ->where('deck.slots.0.selected_name', 'Keep Me [Tracen Academy]')
            ->where('deck.openSlot', 2));
});

it('carries one card list for the six slots rather than six', function (): void {
    /*
     * R-1's fix, counted. Six selects over the same catalogue measured 1,512 option nodes on a run page
     * holding 8,941 elements, and those nodes are the no-script path rather than a visual thing, so the
     * cut had to keep it: one slot open with the full list, five closed and posting hidden values, and a
     * query-string link per closed slot so a Trainer with scripting off still reaches the catalogue for
     * any of them.
     */
    $run = deckRun();
    SupportCard::factory()->count(4)->create(['release_global' => '2025-06-26']);

    test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            // One option list on the payload, once, whichever slot is open...
            ->has('deck.options', 4)
            // ...and every slot names itself in the navigation that opens it, so the five closed
            // rows keep their own route into the catalogue.
            ->where('deck.slots', fn (Collection $slots): bool => $slots->every(
                fn (array $slot): bool => str_contains($slot['open_url'], 'deck_slot='.$slot['position'])
            )));
});

it('shows the deck panel on a run that names no scenario', function (): void {
    // Every scenario has six slots, so the panel is not scenario-composed and must not hide behind the
    // `hasScenario()` guard the scenario panels sit under.
    $run = TrainingRun::factory()->create(['scenario' => null]);

    test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('run.has_scenario', false)
            ->has('deck.slots', 6));
});

it('lists an equipped card effect at its highest stated anchor and names that basis', function (): void {
    // The vector is the export's own shape: id then eleven anchors at levels 1, 5, 10 ... 50, and the
    // highest stated here is level 45, not level 50. The figure must not read as a level this run holds,
    // so the figure rides with its basis sentence beside it (D-256).
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
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('deck.equipped.0.effects.0.name', 'Friendship Bonus')
            ->where('deck.equipped.0.effects.0.display', '25%'));
});

it('marks an anchor with no dictionary row instead of inventing a label', function (): void {
    // D-20: a gap is shown as a gap. A null `name` is what makes the row print the source's own id
    // rather than guess a word for an effect the dictionary does not name.
    $run = deckRun();
    $card = SupportCard::factory()->create([
        'char_name' => 'Quiet Star',
        'release_global' => '2025-06-26',
        'effects' => [[77, -1, -1, -1, -1, -1, -1, -1, -1, -1, 5, -1]],
    ]);
    DeckSlot::factory()->atPosition(1)->create(['training_run_id' => $run->id, 'support_card_id' => $card->id]);

    test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('deck.equipped.0.effects.0.effect_id', 77)
            ->where('deck.equipped.0.effects.0.name', null)
            // No symbol either, so the figure prints bare rather than wearing an invented unit.
            ->where('deck.equipped.0.effects.0.display', '5'));
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
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('deck.equipped.0.card_name', 'Quiet Star [Tracen Academy]')
            ->has('deck.equipped.0.effects', 0));
});
