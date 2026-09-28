<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TurnEventType;
use App\Models\TrainingRun;
use App\Models\TurnEvent;
use App\Models\TurnEvents\ShopPurchasePayload;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Records one shop purchase on the turn the Trainer says it happened (US-4, ADR-0003).
 *
 * The structural rules live here so a bad submission comes back as field errors on the
 * form rather than as an exception; the catalogue facts (this scenario sells this item,
 * at this price) are read from `ShopPurchasePayload::catalogueFor()`, which is the same
 * table the view offers and the model's saving guard still checks. A price the client
 * does not charge is a mistake a Trainer can correct, so it is a field error on `cost`
 * and not a 500 from the payload guard (Slice 10 T3).
 *
 * The holding limit is the one rule that needs the run's history: the shop stocks at most
 * `shop.max_copies_per_item` of an item, so a sixth copy is not a price problem, it is a
 * count of rows already stored.
 */
class StoreShopPurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // local-only tool; no auth surface (ARCHITECTURE §8)
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'turn' => ['required', 'integer', 'min:1'],
            // One key, two rules: an array with a duplicate key keeps only the last, and a
            // shop form that stopped checking the catalogue would still accept a price the
            // model then refuses, which is an error the Trainer cannot see on the field.
            'item' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! is_string($value)) {
                        return;
                    }

                    $run = $this->run();

                    if (! array_key_exists($value, ShopPurchasePayload::catalogueFor($run))) {
                        $fail('The '.$run->scenarioKey().' shop does not sell that item.');

                        return;
                    }

                    $limit = (int) config('scenarios.scenarios.'.$run->scenarioKey().'.shop.max_copies_per_item', 0);
                    $held = $this->heldCopies($value);

                    if ($limit > 0 && $held >= $limit) {
                        $fail("You already hold {$held} of {$limit} copies of {$value} this turn.");
                    }
                },
            ],
            'cost' => [
                'required',
                'integer',
                'min:0',
                // The price check moved to the boundary because the Trainer can fix it and
                // nothing else can: `ShopPurchasePayload::fromArray()` still refuses the
                // write, but on this path its exception arrived as a 500 with no field to
                // correct (D-256, C-7). An unknown item is the `item` rule's message, so
                // this one stays quiet when there is no catalogue row to compare against.
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! is_numeric($value)) {
                        return;
                    }

                    $run = $this->run();
                    $item = $this->input('item');
                    $row = is_string($item) ? (ShopPurchasePayload::catalogueFor($run)[$item] ?? null) : null;

                    if ($row !== null && $row['cost'] !== (int) $value) {
                        $fail("The {$run->scenarioKey()} shop charges {$row['cost']} coins for {$item}, not ".(int) $value.'. Enter the price the client showed.');
                    }
                },
            ],
            'effect' => ['required', 'string', 'max:255'],
        ];
    }

    private function run(): TrainingRun
    {
        $run = $this->route('run');

        return $run instanceof TrainingRun ? $run : new TrainingRun;
    }

    /**
     * Copies of this item already recorded, counted through the payload rather than through
     * a JSON query: the same shape rule that decides what is a purchase decides what counts.
     */
    private function heldCopies(string $item): int
    {
        return $this->run()
            ->turnEvents()
            ->where('event_type', TurnEventType::Scenario->value)
            ->get()
            ->filter(fn (TurnEvent $event): bool => $event->purchasePayload()?->item === $item)
            ->count();
    }
}
