<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;

/**
 * The hint discount ladder a skill's point cost obeys (`UMAMUSUME_REFERENCE.md` §1.1.4).
 *
 * The `[Global]` Learn screen prints `Hint Lvl N` above `NN% OFF`, read off 68 frames on 2026-10-03:
 * Lv1 10%, Lv2 20%, Lv3 30%, Lv4 35%, `Lv Max` 40%. The displayed cost is `floor(base × (1 − discount))`,
 * and the ladder is not linear past Lv3. Pure, so the planner and its tests read the same arithmetic.
 */
final class HintDiscount
{
    /**
     * The client captions the tiers print, in the order the Learn screen shows them.
     *
     * @return list<array{key: int|string, label: string, discount: int}>
     */
    public static function ladder(): array
    {
        $labels = [
            1 => 'Hint Lvl 1',
            2 => 'Hint Lvl 2',
            3 => 'Hint Lvl 3',
            4 => 'Hint Lvl 4',
            'max' => 'Hint Lvl Max',
        ];

        $rows = [];

        /** @var array<int|string, int> $discounts */
        $discounts = config('uma.skills.hint_discount');

        foreach ($discounts as $key => $discount) {
            $rows[] = [
                'key' => $key,
                'label' => $labels[$key] ?? "Hint Lvl {$key}",
                'discount' => (int) $discount,
            ];
        }

        return $rows;
    }

    /**
     * The full ladder priced against one skill's own base: the six rows the detail screen prints.
     *
     * The first row is the base itself (the "None" row the catalogue page draws); each tier then prices
     * through `price()`, so the detail and the planner cannot disagree about the arithmetic. A null base
     * prices every row null rather than inventing a zero: a class that states no cost has no ladder.
     *
     * @return list<array{key: string, label: string, discount: int, price: int|null}>
     */
    public static function priceLadder(?int $base): array
    {
        $labels = [
            1 => 'Lv1',
            2 => 'Lv2',
            3 => 'Lv3',
            4 => 'Lv4',
            'max' => 'Lv Max',
        ];

        $rows = [[
            'key' => 'none',
            'label' => 'None',
            'discount' => 0,
            'price' => $base,
        ]];

        foreach (self::ladder() as $tier) {
            $key = $tier['key'];

            $rows[] = [
                'key' => $key === 'max' ? 'max' : "lv{$key}",
                'label' => $labels[$key] ?? (string) $tier['label'],
                'discount' => $tier['discount'],
                'price' => $base === null ? null : self::price($base, $key),
            ];
        }

        return $rows;
    }

    /**
     * The discount percentage a hint level earns, or null when the level is outside the ladder.
     */
    public static function discountPct(int|string $hintLevel): ?int
    {
        /** @var array<int|string, int> $discounts */
        $discounts = config('uma.skills.hint_discount');

        return $discounts[$hintLevel] ?? null;
    }

    /**
     * The displayed cost of a skill at one hint level: `floor(base × (1 - discount / 100))`.
     *
     * Integer arithmetic, not `floor($base * (1 - $discount / 100))`: the float form drifts. Measured
     * at 180 SP and Lv3, `180 * (1 - 0.3)` evaluates to 125.99999999999999 and floors to 125, where
     * the client's rule prints 126. `intdiv` is exact for an integer base.
     *
     * An unknown hint level leaves the base cost untouched rather than inventing a discount.
     */
    public static function price(int $base, int|string $hintLevel): int
    {
        $discount = self::discountPct($hintLevel);

        if ($discount === null) {
            return $base;
        }

        if ($discount < 0 || $discount > 100) {
            throw new InvalidArgumentException("Hint discount [{$discount}] is outside 0 to 100.");
        }

        return intdiv($base * (100 - $discount), 100);
    }
}
