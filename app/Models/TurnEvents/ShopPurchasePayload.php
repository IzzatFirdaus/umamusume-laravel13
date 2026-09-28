<?php

declare(strict_types=1);

namespace App\Models\TurnEvents;

use App\Models\TrainingRun;

/**
 * One Trackblazer shop purchase, recorded on the turn it happened (D-226, ADR-0003).
 *
 * A purchase is an event, not a column: it belongs to one scenario, and a
 * `shop_purchases` table would be the fourth table Slice 8 was told not to add. The
 * shape is {item, cost, effect}, all as the Trainer entered them.
 *
 * The catalogue is the validator. `config/scenarios.php` `shop_items` is transcribed
 * from docs/scenarios/05 §"Full Shop Item List", so an item name the scenario does not
 * sell is rejected at save time rather than becoming a row that renders as nothing and
 * a coin total that quietly excludes it (D-256). Cost is checked against the same
 * table because a price the client does not charge is a fabricated fact, and the effect
 * sentence travels with the item so a later render never has to paraphrase the client.
 */
final readonly class ShopPurchasePayload
{
    public const KEYS = ['item', 'cost', 'effect'];

    public function __construct(
        public string $item,
        public int $cost,
        public string $effect,
    ) {}

    /**
     * The catalogue this run's scenario sells, as `name => ['cost' => .., 'effect' => ..]`.
     *
     * @return array<string, array{cost: int, effect: string}>
     */
    public static function catalogueFor(TrainingRun $run): array
    {
        if (! $run->composesShop()) {
            return [];
        }

        $items = [];

        foreach ((array) config('scenarios.scenarios.'.$run->scenarioKey().'.shop_items') as $row) {
            if (isset($row['name'])) {
                $items[(string) $row['name']] = [
                    'cost' => (int) ($row['cost'] ?? 0),
                    'effect' => (string) ($row['effect'] ?? ''),
                ];
            }
        }

        return $items;
    }

    /**
     * @param  array<string, mixed>  $deltas
     */
    public static function matches(array $deltas): bool
    {
        return array_key_exists('item', $deltas);
    }

    public static function make(string $item, int $cost, string $effect): self
    {
        return new self($item, $cost, $effect);
    }

    /**
     * Validate against the scenario's own catalogue, then build.
     *
     * @param  array<string, mixed>  $deltas
     */
    public static function fromArray(array $deltas, TrainingRun $run): self
    {
        $keys = array_keys($deltas);
        sort($keys);
        $expected = self::KEYS;
        sort($expected);

        if ($keys !== $expected) {
            throw new \InvalidArgumentException(
                'A purchase payload carries exactly ['.implode(', ', $expected).
                '] keys, got ['.implode(', ', array_keys($deltas)).'].',
            );
        }

        if (! is_string($deltas['item']) || ! is_int($deltas['cost']) || ! is_string($deltas['effect'])) {
            throw new \InvalidArgumentException('A purchase payload needs a string item, an int cost and a string effect.');
        }

        $catalogue = self::catalogueFor($run);

        if ($catalogue === []) {
            throw new \InvalidArgumentException(
                "Scenario [{$run->scenarioKey()}] has no shop, so it cannot record a purchase.",
            );
        }

        $row = $catalogue[$deltas['item']] ?? null;

        if ($row === null) {
            throw new \InvalidArgumentException(
                "Shop item [{$deltas['item']}] is not in the {$run->scenarioKey()} shop catalogue.",
            );
        }

        if ($row['cost'] !== $deltas['cost']) {
            throw new \InvalidArgumentException(
                "Shop item [{$deltas['item']}] costs {$row['cost']} and not {$deltas['cost']}.",
            );
        }

        return self::make($deltas['item'], $deltas['cost'], $deltas['effect']);
    }

    /**
     * @return array{item: string, cost: int, effect: string}
     */
    public function toArray(): array
    {
        return ['item' => $this->item, 'cost' => $this->cost, 'effect' => $this->effect];
    }
}
