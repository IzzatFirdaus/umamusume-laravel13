<?php

declare(strict_types=1);

use App\Enums\TurnEventType;
use App\Models\TrainingRun;
use App\Models\TurnEvent;
use App\Models\TurnEvents\ShopPurchasePayload;
use Inertia\Testing\AssertableInertia as Assert;

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
    // The page carries both halves of that: the entered spend as a resolved figure, and no
    // balance at all, so a coin count the server never saw cannot be printed (D-232).
    // The sentence itself is asserted in `tests/browser/run-detail.spec.ts`.
    test()->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('shop.spendTotal', '65')
            ->where('strip.values.shop_coins', null));
});

it('refuses a sixth copy of the same item', function (): void {
    $run = shopRun();
    $limit = (int) config('scenarios.scenarios.trackblazer.shop.max_copies_per_item');

    for ($i = 1; $i <= $limit; $i++) {
        $this->post('/training-runs/'.$run->id.'/purchases', [
            'turn' => $i, 'item' => 'Glow Sticks', 'cost' => 15, 'effect' => 'Race fan gain +50%, 1 turn',
        ])->assertSessionHasNoErrors();
    }

    expect($run->turnEvents()->count())->toBe($limit);

    $this->post('/training-runs/'.$run->id.'/purchases', [
        'turn' => 9, 'item' => 'Glow Sticks', 'cost' => 15, 'effect' => 'Race fan gain +50%, 1 turn',
    ])->assertSessionHasErrors('item');
});

it('rejects an item the scenario does not sell as a field error on item', function (): void {
    $run = shopRun();

    $this->post('/training-runs/'.$run->id.'/purchases', [
        'turn' => 1, 'item' => 'Wit Manual', 'cost' => 15, 'effect' => '+7 Wit',
    ])->assertSessionHasErrors('item');
});

it('answers a cost the catalogue disagrees with as a 422 envelope, not a 500', function (): void {
    $run = shopRun();

    // A price the client does not charge is a Trainer mistake at a form, so it answers at
    // the boundary instead of reaching `ShopPurchasePayload::fromArray()` and throwing.
    // The envelope stays {code, message}: `ARCHITECTURE.md:166` defines it that way, so the
    // field map is not added to a JSON contract without a ruling.
    $this->postJson('/training-runs/'.$run->id.'/purchases', [
        'turn' => 1, 'item' => 'Vita 40', 'cost' => 40, 'effect' => 'Energy +40',
    ])->assertStatus(422)
        ->assertJsonPath('error.code', 'VALIDATION_ERROR')
        ->assertJsonPath('error.message', 'The trackblazer shop charges 55 coins for Vita 40, not 40. Enter the price the client showed.');
});

it('marks the cost input when the catalogue price disagrees', function (): void {
    $run = shopRun();

    // Its own test because a second failed request in the same session ages the first
    // one's flashed bag out before the page renders, which would prove nothing about the
    // form. The referer is what a browser sends, and `back()` needs it to return here.
    // Inertia carries the bag as the shared `errors` prop, which is what `ShopPanel.vue`
    // binds to the cost field's `aria-invalid`; both halves are asserted, because a bag that
    // arrives with nothing bound to it renders an unmarked input and no message.
    test()->withHeader('referer', url('/training-runs/'.$run->id))
        ->followingRedirects()
        ->post('/training-runs/'.$run->id.'/purchases', [
            'turn' => 1, 'item' => 'Vita 40', 'cost' => 40, 'effect' => 'Energy +40',
        ])
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('errors.cost', fn ($message): bool => is_string($message)
                && str_contains($message, 'charges 55 coins for Vita 40')));

    $panel = (string) file_get_contents(base_path('resources/js/components/ShopPanel.vue'));

    expect($panel)->toContain(":aria-invalid=\"purchaseForm.errors.cost ? 'true' : 'false'\"")
        ->and($panel)->toContain('id="purchase-cost-error"');
});

it('warns about the overwrite and the cap beside the purchase control', function (): void {
    $run = shopRun();

    // The cap the panel states is the scenario's own limit, read once here rather than
    // transcribed into the assertion, so a config change cannot leave a stale 5 behind.
    // The two sentences themselves are rendered-copy evidence in
    // `tests/browser/run-detail.spec.ts`.
    test()->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('shop.maxCopies', (int) config('scenarios.scenarios.trackblazer.shop.max_copies_per_item'))
            ->where('shop.panelsShop', true));
});

it('renders a purchase recorded through the writer in the panel', function (): void {
    $run = shopRun();

    test()->post('/training-runs/'.$run->id.'/purchases', [
        'turn' => 4, 'item' => 'Royal Kale Juice', 'cost' => 70, 'effect' => 'Energy +100, Mood −1',
    ])->assertSessionHasNoErrors();

    // The panel prints the item, its effect, its cost and the running total, all four of
    // them number-formatted by the controller; the rendered row is browser evidence in
    // `tests/browser/run-detail.spec.ts`.
    test()->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('shop.purchases', [[
                'item' => 'Royal Kale Juice',
                'effect' => 'Energy +100, Mood −1',
                'cost' => '70',
            ]])
            ->where('shop.spendTotal', '70'));
});
