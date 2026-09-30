<?php

declare(strict_types=1);

use App\Models\DeckSlot;
use App\Models\SupportCard;
use App\Models\TrainingRun;

/*
 * Slice 2: the read-only support-card endpoints and the deck on the run detail response.
 *
 * These pin the envelope the other three api endpoints already use (`data` plus `pagination`, an error
 * branch that stays unreachable because nothing here writes) and the two serialisation rules that are
 * easy to break by accident: the wire carries the export's machine tokens rather than the client's
 * words, and an unloaded relation is absent from the payload rather than an empty list. An empty `deck`
 * on a run index row would tell a consumer the Trainer equipped nothing, which is not something the
 * index query knows.
 */

it('lists support cards in the data plus pagination envelope', function (): void {
    SupportCard::factory()->count(3)->create();

    test()->getJson('/api/v1/support-cards')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [['id', 'supportId', 'charId', 'charName', 'rarity', 'type', 'releaseStatus', 'effects', 'sourceUrl', 'isManual']],
            'pagination' => ['page', 'pageSize', 'totalItems', 'totalPages'],
        ])
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('pagination.totalItems', 3);
});

it('orders the list by support_id so paging is stable', function (): void {
    SupportCard::factory()->create(['support_id' => 300001]);
    SupportCard::factory()->create(['support_id' => 100002]);
    SupportCard::factory()->create(['support_id' => 200003]);

    test()->getJson('/api/v1/support-cards')
        ->assertOk()
        ->assertJsonPath('data.0.supportId', 100002)
        ->assertJsonPath('data.1.supportId', 200003)
        ->assertJsonPath('data.2.supportId', 300001);
});

it('pages support cards twenty five at a time by default', function (): void {
    SupportCard::factory()->count(30)->create();

    test()->getJson('/api/v1/support-cards')
        ->assertOk()
        ->assertJsonCount(25, 'data')
        ->assertJsonPath('pagination.pageSize', 25)
        ->assertJsonPath('pagination.totalPages', 2);
});

it('clamps the page size without treating an out-of-range one as an error', function (): void {
    SupportCard::factory()->count(3)->create();

    test()->getJson('/api/v1/support-cards?pageSize=5000')
        ->assertOk()
        ->assertJsonPath('pagination.pageSize', 100);

    test()->getJson('/api/v1/support-cards?pageSize=0')
        ->assertOk()
        ->assertJsonPath('pagination.pageSize', 1);
});

it('serves one support card by id', function (): void {
    $card = SupportCard::factory()->ssr()->speed()->create(['support_id' => 412001]);

    test()->getJson("/api/v1/support-cards/{$card->id}")
        ->assertOk()
        ->assertJsonPath('data.supportId', 412001)
        ->assertJsonPath('data.charName', 'Special Week')
        ->assertJsonPath('data.type', 'speed')
        ->assertJsonPath('data.rarity', 3);
});

it('answers a card that does not exist with the api not-found envelope', function (): void {
    test()->getJson('/api/v1/support-cards/999999')
        ->assertNotFound()
        ->assertJsonPath('error.code', 'NOT_FOUND');
});

it('emits the export keys, not the client words, on the wire', function (): void {
    // `typeLabel()` renders Wit and Pal at the view boundary. The api carries `intelligence` and
    // `friend` so a consumer has one spelling to key on, matching how mood and status are emitted as
    // enum values elsewhere.
    $card = SupportCard::factory()->wit()->create();

    test()->getJson("/api/v1/support-cards/{$card->id}")
        ->assertOk()
        ->assertJsonPath('data.type', 'intelligence')
        ->assertJsonPath('data.rarity', 1);
});

it('carries the effect anchors as the eleven-wide vector the source sends', function (): void {
    $card = SupportCard::factory()->create([
        'effects' => [[1, 5, -1, -1, 10, 10, -1, -1, 15, -1, -1, -1]],
    ]);

    $response = test()->getJson("/api/v1/support-cards/{$card->id}")->assertOk();

    expect($response->json('data.effects.0'))->toHaveCount(12);
});

it('serialises a staff-keyed Pal card with no trainee to join to', function (): void {
    // The 23 cards that a foreign key on char_id would have dropped from the catalogue entirely.
    $card = SupportCard::factory()->friend()->create();

    test()->getJson("/api/v1/support-cards/{$card->id}")
        ->assertOk()
        ->assertJsonPath('data.charId', 9001)
        ->assertJsonPath('data.charName', 'Tazuna Hayakawa')
        ->assertJsonPath('data.type', 'friend');
});

it('reports a card Global has not received from the generated column, not a computed field', function (): void {
    $jpOnly = SupportCard::factory()->jpOnly()->create();

    test()->getJson("/api/v1/support-cards/{$jpOnly->id}")
        ->assertOk()
        ->assertJsonPath('data.releaseGlobal', null)
        ->assertJsonPath('data.releaseStatus', 'JP-only');
});

// ── the deck on the run detail response ────────────────────────────────────

it('shows a run deck ordered by slot position with the friend slot flagged', function (): void {
    $run = TrainingRun::factory()->create();
    $friend = SupportCard::factory()->friend()->create();
    $speed = SupportCard::factory()->speed()->create();

    DeckSlot::factory()->atPosition(6)->create(['training_run_id' => $run->id, 'support_card_id' => $friend->id]);
    DeckSlot::factory()->atPosition(1)->create(['training_run_id' => $run->id, 'support_card_id' => $speed->id]);

    test()->getJson("/api/v1/training-runs/{$run->id}")
        ->assertOk()
        ->assertJsonCount(2, 'data.deck')
        ->assertJsonPath('data.deck.0.slotPosition', 1)
        ->assertJsonPath('data.deck.0.isFriendSlot', false)
        ->assertJsonPath('data.deck.0.supportCard.type', 'speed')
        ->assertJsonPath('data.deck.1.slotPosition', 6)
        ->assertJsonPath('data.deck.1.isFriendSlot', true)
        ->assertJsonPath('data.deck.1.supportCard.charName', 'Tazuna Hayakawa');
});

it('reports a run with no recorded deck as an empty list, which is true', function (): void {
    $run = TrainingRun::factory()->create();

    test()->getJson("/api/v1/training-runs/{$run->id}")
        ->assertOk()
        ->assertJsonPath('data.deck', []);
});

it('omits the deck from the run index rather than claiming an empty one', function (): void {
    // The index never eager-loads deckSlots. Returning `[]` there would assert that the Trainer
    // equipped nothing, from a query that did not look.
    $run = TrainingRun::factory()->create();
    DeckSlot::factory()->create(['training_run_id' => $run->id]);

    $body = test()->getJson('/api/v1/training-runs')->assertOk()->json();

    expect(array_key_exists('deck', $body['data'][0]))->toBeFalse();
});
