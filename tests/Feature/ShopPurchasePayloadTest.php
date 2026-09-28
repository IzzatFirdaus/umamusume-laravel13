<?php

declare(strict_types=1);

use App\Enums\TurnEventType;
use App\Models\TrainingRun;
use App\Models\TurnEvent;
use App\Models\TurnEvents\ShopPurchasePayload;

/*
 * Slice 8 T1b: a Trackblazer shop purchase rides `turn_events`, not a new table
 * (ADR-0003 pattern, D-226). The payload is {item, cost, effect}, each as entered:
 * the item key is checked against the catalogue config holds for the run's scenario,
 * and cost and effect are what the Trainer read off the client, never a re-derivation.
 *
 * Save-time rejection of an unknown item key is the whole point. A json column accepts
 * anything, so a typo becomes a purchase row that renders as nothing and a coin total
 * that quietly excludes it (D-256).
 */
function shopRun(string $scenario = 'trackblazer'): TrainingRun
{
    return TrainingRun::factory()->create(['scenario' => $scenario]);
}

function purchase(TrainingRun $run, string $item, int $cost, string $effect): TurnEvent
{
    return TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => 9,
        'event_type' => TurnEventType::Scenario,
        'source_name' => 'Shop',
        'deltas' => ShopPurchasePayload::make($item, $cost, $effect)->toArray(),
    ]);
}

it('lists the catalogue keys the run scenario actually sells', function (): void {
    $names = array_keys(ShopPurchasePayload::catalogueFor(shopRun()));

    expect($names)->toContain('Speed Notepad')
        ->and($names)->toContain('Good-Luck Charm')
        ->and(count($names))->toBe(count(config('scenarios.scenarios.trackblazer.shop_items')));
});

it('round-trips a purchase without losing the effect sentence', function (): void {
    $event = purchase(shopRun(), 'Vita 40', 55, 'Energy +40');

    $fresh = TurnEvent::query()->findOrFail($event->id);

    expect($fresh->getRawOriginal('deltas'))
        ->toBe('{"item":"Vita 40","cost":55,"effect":"Energy +40"}')
        ->and($fresh->purchasePayload()?->item)->toBe('Vita 40')
        ->and($fresh->purchasePayload()?->cost)->toBe(55)
        ->and($fresh->purchasePayload()?->effect)->toBe('Energy +40');
});

it('refuses an item the scenario catalogue does not list', function (): void {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('not in the trackblazer shop catalogue');

    purchase(shopRun(), 'Wit Manual', 15, '+7 Wit');
});

it('refuses a cost that disagrees with the catalogue price', function (): void {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('costs 55');

    purchase(shopRun(), 'Vita 40', 40, 'Energy +40');
});

it('refuses a purchase on a scenario with no shop', function (): void {
    $run = shopRun('ura_finale');

    $this->expectException(InvalidArgumentException::class);

    TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => 9,
        'event_type' => TurnEventType::Scenario,
        'source_name' => 'Shop',
        'deltas' => ['item' => 'Vita 40', 'cost' => 55, 'effect' => 'Energy +40'],
    ]);
});

it('sums entered coins and never a coin balance it cannot see', function (): void {
    $run = shopRun();
    purchase($run, 'Vita 40', 55, 'Energy +40');
    purchase($run, 'Speed Notepad', 10, '+3 Speed');

    expect($run->shopSpendTotal())->toBe(65);

    // The balance is coins earned minus coins spent, and earning is not stored per turn,
    // so the panel says the balance is not recorded rather than subtracting toward zero.
    $html = $this->get('/training-runs/'.$run->id)->assertOk()->getContent();

    expect(strip_tags($html))->toMatch('/Shop Coins: not yet recorded/i');
});
