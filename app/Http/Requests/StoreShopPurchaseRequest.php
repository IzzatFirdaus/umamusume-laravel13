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
 * at this price) are decided once, in `ShopPurchasePayload`, and the model's saving guard
 * still refuses the write if anything reaches it from another path. The two together are
 * the reason this request does not re-check the price table itself.
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
            'cost' => ['required', 'integer', 'min:0'],
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
