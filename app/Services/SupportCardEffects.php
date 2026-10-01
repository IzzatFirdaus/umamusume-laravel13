<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SupportCard;
use App\Models\SupportEffect;

/**
 * What a support card's effects are worth at their highest stated level.
 *
 * The anchors are the export's own: one row per effect, the effect id followed by eleven values at
 * card levels 1, 5, 10, 15, 20, 25, 30, 35, 40, 45 and 50, where `-1` means the source holds no entry
 * at that level rather than that the effect is zero (`UMAMUSUME_REFERENCE.md` §1.4.7, `PRD.md` A-8
 * limit iii). The cap is therefore the last anchor that is not `-1`, which is not always level 50:
 * measured across the committed body, the highest stated anchor falls on level 45 for 1,724 rows and
 * on level 50 for 1,391. Nothing here interpolates, so every figure this returns is a stated anchor
 * and D-256's rule is satisfied by the panel naming that basis rather than by printing bracketing
 * anchors beside the value.
 *
 * The unit word is the source's own `symbol` field, and only three values occur: `percent`, `none`
 * and `level` (`SupportEffect::SYMBOLS`). Two dictionary rows carry no symbol at all, and those
 * figures print bare.
 */
class SupportCardEffects
{
    /**
     * The card levels the eleven anchors answer to (UMAMUSUME_REFERENCE.md §1.4.7).
     *
     * @var list<int>
     */
    private const ANCHOR_LEVELS = [1, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50];

    /**
     * @return array<int, array{name: string, symbol: string|null}>
     */
    public static function dictionary(): array
    {
        return SupportEffect::query()
            ->get(['effect_id', 'name_en', 'symbol'])
            ->mapWithKeys(fn (SupportEffect $effect): array => [
                $effect->effect_id => ['name' => $effect->name_en, 'symbol' => $effect->symbol],
            ])
            ->all();
    }

    /**
     * The card's effects at cap, in the order the source stores them.
     *
     * An anchor row with no value at any level is dropped rather than printed as a zero, and a row
     * that is not eleven anchors plus its id is dropped because a positional vector of the wrong
     * width would otherwise be read with the wrong levels. A dictionary row that is absent leaves
     * `name` null so the surface can mark the gap instead of inventing a label (D-20).
     *
     * @param  array<int, array{name: string, symbol: string|null}>  $dictionary
     * @return list<array{effect_id: int, name: string|null, display: string}>
     */
    public static function atCap(SupportCard $card, array $dictionary): array
    {
        $rows = [];

        foreach ($card->effects ?? [] as $row) {
            if (count($row) !== count(self::ANCHOR_LEVELS) + 1) {
                continue;
            }

            $value = null;

            foreach (self::ANCHOR_LEVELS as $index => $level) {
                if ($row[$index + 1] !== -1) {
                    $value = $row[$index + 1];
                }
            }

            if ($value === null) {
                continue;
            }

            $effectId = $row[0];
            $entry = $dictionary[$effectId] ?? null;

            $rows[] = [
                'effect_id' => $effectId,
                'name' => $entry['name'] ?? null,
                'display' => self::format((int) $value, $entry['symbol'] ?? null),
            ];
        }

        return $rows;
    }

    private static function format(int $value, ?string $symbol): string
    {
        return match ($symbol) {
            'percent' => $value.'%',
            'level' => 'Lv '.$value,
            default => (string) $value,
        };
    }
}
