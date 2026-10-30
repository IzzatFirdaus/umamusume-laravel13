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
     * @return array<int, array{name: string, symbol: string|null, calc: string|null}>
     */
    public static function dictionary(): array
    {
        return SupportEffect::query()
            ->get(['effect_id', 'name_en', 'symbol', 'calc'])
            ->mapWithKeys(fn (SupportEffect $effect): array => [
                $effect->effect_id => ['name' => $effect->name_en, 'symbol' => $effect->symbol, 'calc' => $effect->calc],
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
     * @param  array<int, array{name: string, symbol: string|null, calc: string|null}>  $dictionary
     * @return list<array{effect_id: int, name: string|null, display: string, value: int, symbol: string|null, calc: string|null}>
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

            $value = (int) $value;
            $effectId = $row[0];
            $entry = $dictionary[$effectId] ?? null;

            $rows[] = [
                'effect_id' => $effectId,
                'name' => $entry['name'] ?? null,
                'display' => self::formatValue($value, $entry['symbol'] ?? null),
                // The anchor's own number, unit and combining mode, beside the word a Trainer reads.
                // `DeckAnalysis` sums the numbers and reads `calc` to know which of them must not be
                // summed at all: a `mult` summed would be a figure the client does not state.
                'value' => $value,
                'symbol' => $entry['symbol'] ?? null,
                'calc' => $entry['calc'] ?? null,
            ];
        }

        return $rows;
    }

    /**
     * The card's effects as the source states them, anchor by anchor.
     *
     * `atCap()` answers "what is this worth at its best" and discards where the source says so. The card
     * detail screen needs the anchors themselves, because the export states a value at particular card
     * levels and says nothing between them: a level with no entry is an absence, not a zero, so a
     * per-break column would print six evenly spaced cells the source never stated. Each row therefore
     * carries its stated pairs in level order and the last of them, which is the cap `atCap()` returns.
     *
     * A row with no stated anchor at all is dropped, exactly as `atCap()` drops it.
     *
     * @param  array<int, array{name: string, symbol: string|null, calc: string|null}>  $dictionary
     * @return list<array{effect_id: int, name: string|null, anchors: list<array{level: int, display: string}>, cap: array{level: int, display: string}}>
     */
    public static function stages(SupportCard $card, array $dictionary): array
    {
        $rows = [];

        foreach ($card->effects ?? [] as $row) {
            if (count($row) !== count(self::ANCHOR_LEVELS) + 1) {
                continue;
            }

            $effectId = (int) $row[0];
            $entry = $dictionary[$effectId] ?? null;
            $anchors = [];

            foreach (self::ANCHOR_LEVELS as $index => $level) {
                $value = $row[$index + 1];

                if ($value !== -1) {
                    $anchors[] = [
                        'level' => $level,
                        'display' => self::formatValue((int) $value, $entry['symbol'] ?? null),
                    ];
                }
            }

            if ($anchors === []) {
                continue;
            }

            $rows[] = [
                'effect_id' => $effectId,
                'name' => $entry['name'] ?? null,
                'anchors' => $anchors,
                'cap' => $anchors[count($anchors) - 1],
            ];
        }

        return $rows;
    }

    /**
     * The card's effects at a level the run reached, interpolated between the stated anchors.
     *
     * `forLevel()` answers the same question of a card id; `atLevel()` answers it of a card already in
     * hand, because the deck step reads the ladder for all six equipped cards at once and six `find()`
     * calls per tier would be thirty-two queries for rows the page already loaded. Same `valueAt()`,
     * same floor rule, so the two cannot drift.
     *
     * @param  array<int, array{name: string, symbol: string|null, calc: string|null}>|null  $dictionary
     * @return list<array{effect_id: int, name: string|null, display: string, value: int, symbol: string|null, calc: string|null}>
     */
    public static function atLevel(SupportCard $card, int $level, ?array $dictionary = null): array
    {
        $dictionary ??= self::dictionary();
        $rows = [];

        foreach ($card->effects ?? [] as $row) {
            if (count($row) !== count(self::ANCHOR_LEVELS) + 1) {
                continue;
            }

            $value = self::valueAt($row, $level);

            if ($value === null) {
                continue;
            }

            $effectId = (int) $row[0];
            $entry = $dictionary[$effectId] ?? null;

            $rows[] = [
                'effect_id' => $effectId,
                'name' => $entry['name'] ?? null,
                'display' => self::formatValue($value, $entry['symbol'] ?? null),
                'value' => $value,
                'symbol' => $entry['symbol'] ?? null,
                'calc' => $entry['calc'] ?? null,
            ];
        }

        return $rows;
    }

    /**
     * The card's effects at a level the run reached, interpolated between the stated anchors.
     *
     * `forLevel()` derives the reached effect values for a card at a given level by linear interpolation
     * between the two anchors bracketing `$level` on `support_cards.effects`. Below the lowest anchor,
     * clamp to the lowest anchor's value. At or above the highest anchor, return the highest anchor's
     * value (equivalent to `atCap()`). The method stores nothing — reached values are derived per
     * ADR-0029 D2, never a per-run column. Source: UMAMUSUME_REFERENCE.md §1.4.2 (card level drives
     * support effects only, not teammate stats).
     *
     * @param  array<int, array{name: string, symbol: string|null, calc: string|null}>|null  $dictionary
     * @return list<array{effect_id: int, name: string|null, display: string, value: int, symbol: string|null, calc: string|null}>
     */
    public static function forLevel(int $cardId, int $level, ?array $dictionary = null): array
    {
        $card = SupportCard::query()->find($cardId);

        return $card === null ? [] : self::atLevel($card, $level, $dictionary);
    }

    /**
     * The stated brackets around `$level`, or null when the row states no anchor at all.
     *
     * Interpolating between the nearest stated anchors rather than between array positions keeps a
     * `-1` gap from being read as a zero: a gap means the source states nothing at that level.
     *
     * @param  array<int, int>  $row
     */
    private static function valueAt(array $row, int $level): ?int
    {
        /** @var array<int, int> $anchors */
        $anchors = [];

        foreach (self::ANCHOR_LEVELS as $index => $anchor) {
            $value = $row[$index + 1] ?? -1;

            if ($value !== -1) {
                $anchors[$anchor] = (int) $value;
            }
        }

        if ($anchors === []) {
            return null;
        }

        $levels = array_keys($anchors);
        $first = $levels[0];
        $last = $levels[count($levels) - 1];

        if ($level <= $first) {
            return $anchors[$first];
        }

        if ($level >= $last) {
            return $anchors[$last];
        }

        if (isset($anchors[$level])) {
            return $anchors[$level];
        }

        $low = $first;

        foreach ($levels as $anchor) {
            if ($anchor <= $level) {
                $low = $anchor;
            }
        }

        $high = $last;

        foreach ($levels as $anchor) {
            if ($anchor >= $level) {
                $high = $anchor;
                break;
            }
        }

        $ratio = ($level - $low) / ($high - $low);

        // UMAMUSUME_REFERENCE.md §1.4.7: the floor of the straight line, not the nearest integer —
        // "rounding is truncation, not nearest, and getting that wrong shifts every intermediate
        // value by one". At level 35 Friendship Bonus is 15 + 5·5/15 = 16.67 → 16, not 17.
        return (int) floor($anchors[$low] + $ratio * ($anchors[$high] - $anchors[$low]));
    }

    /**
     * The anchor's own unit word, applied once here so a figure and a total of figures cannot read
     * two different units for the same effect.
     */
    public static function formatValue(int $value, ?string $symbol): string
    {
        return match ($symbol) {
            'percent' => $value.'%',
            'level' => 'Lv '.$value,
            default => (string) $value,
        };
    }
}
