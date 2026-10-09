<?php

declare(strict_types=1);

namespace App\Services;

/**
 * The fan ladder: which class a run's fan count holds, and how far it is from the next threshold.
 *
 * The audit's screen 7 recorded `Fans 209,245 / class Star / the 30,755-fan gap` with no field behind
 * any of the three, so the cockpit could print the number and nothing about what it meant. The bands
 * live in `config('uma.fan_ladder')`, one entry per documented band, and this class is the only reader.
 *
 * A query the ladder cannot place answers null rather than guessing. That is the honest shape for a
 * corpus that names exactly one band: a count above the highest documented threshold has no class this
 * tool can claim, and a null renders as the bare number rather than as a class nobody sourced.
 */
final class FanLadder
{
    /**
     * The class the count holds, or null when no documented band contains it.
     */
    public static function classFor(int $fans): ?string
    {
        $band = self::bandFor($fans);

        return $band['class'] ?? null;
    }

    /**
     * The threshold that ends the count's band - the next class's own threshold - or null when the
     * ladder names none.
     */
    public static function nextThreshold(int $fans): ?int
    {
        $band = self::bandFor($fans);

        return $band === null ? null : (int) $band['to'];
    }

    /**
     * How many fans stand between the count and the next threshold, or null when there is none to name.
     */
    public static function gapToNext(int $fans): ?int
    {
        $next = self::nextThreshold($fans);

        return $next === null ? null : $next - $fans;
    }

    /**
     * All three answers at once, in the shape a page prop carries. Kept together so a caller cannot
     * print a class beside a gap computed from a different band.
     *
     * @return array{class: string|null, nextThreshold: int|null, gap: int|null}
     */
    public static function fromValue(int $fans): array
    {
        return [
            'class' => self::classFor($fans),
            'nextThreshold' => self::nextThreshold($fans),
            'gap' => self::gapToNext($fans),
        ];
    }

    /**
     * @return array{class: string, from: int, to: int}|null
     */
    private static function bandFor(int $fans): ?array
    {
        /** @var list<array{class: string, from: int, to: int}> $bands */
        $bands = (array) config('uma.fan_ladder', []);

        foreach ($bands as $band) {
            if ($fans >= (int) $band['from'] && $fans < (int) $band['to']) {
                return $band;
            }
        }

        return null;
    }
}
