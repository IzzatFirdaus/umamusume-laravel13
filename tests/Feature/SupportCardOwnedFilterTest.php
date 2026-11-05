<?php

declare(strict_types=1);

use App\Models\DeckSlot;
use App\Models\SupportCard;
use App\Models\TrainingRun;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\NoTransactionTestCase;

/*
 * Test the "recorded as owned" filter without database transactions to avoid
 * SQLite in-memory transaction isolation issues with HTTP requests.
 */

uses(NoTransactionTestCase::class)
    ->in('Feature/SupportCardOwnedFilterTest')
    ->beforeEach(function (): void {
        $this->withoutVite();
    });

it('filters by recorded as owned', function (): void {
    $ownedCard = SupportCard::factory()->create(['char_name' => 'Owned', 'title_en' => '[Tracen Academy]']);
    $rentedCard = SupportCard::factory()->create(['char_name' => 'Rented', 'title_en' => '[Tracen Academy]']);

    // Create a saved run with owned card
    $run = TrainingRun::factory()->create();
    DeckSlot::factory()->create([
        'training_run_id' => $run->id,
        'support_card_id' => $ownedCard->id,
        'slot_position' => 1,
        'ownership' => 'OWNED',
    ]);

    // Create a saved run with rented card
    $run2 = TrainingRun::factory()->create();
    DeckSlot::factory()->create([
        'training_run_id' => $run2->id,
        'support_card_id' => $rentedCard->id,
        'slot_position' => 1,
        'ownership' => 'RENTED',
    ]);

    // Debug: check the data directly
    $ownedCards = SupportCard::query()
        ->whereHas('deckSlots', function (Builder $q): void {
            $q->where('ownership', 'OWNED')
                ->whereHas('trainingRun', function (Builder $rq): void {
                    $rq->whereNotNull('id');
                });
        })
        ->get();
    Log::info('Direct query owned cards: '.$ownedCards->count());
    foreach ($ownedCards as $c) {
        Log::info("  Card: {$c->char_name} {$c->title_en}");
    }

    // Debug: check the HTTP request directly
    $response = $this->get('/support-cards?owned=1');
    Log::info('HTTP response status: '.$response->getStatusCode());
    $content = $response->getContent();
    Log::info('HTTP response content length: '.strlen($content));
    // Parse the Inertia response
    $data = json_decode($content, true);
    if (isset($data['page']['props']['cards']['data'])) {
        Log::info('HTTP response cards count: '.count($data['page']['props']['cards']['data']));
        foreach ($data['page']['props']['cards']['data'] as $card) {
            Log::info("  HTTP Card: {$card['name']}");
        }
    } else {
        Log::info('No cards data in response');
    }

    // Filter by owned
    $this->get('/support-cards?owned=1')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 1)
            ->where('cards.data.0.name', 'Owned [Tracen Academy]'));

    // Filter by not owned (RENTED doesn't count)
    $this->get('/support-cards?owned=0')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cards.data', 1)
            ->where('cards.data.0.name', 'Rented [Tracen Academy]'));
});
