<?php

declare(strict_types=1);

namespace App\Services\Career;

use App\Models\RaceCatalogSlot;
use App\Models\TrainingRun;

/**
 * The career position in the client's own words: the year, then the Early or Late half of a month.
 *
 * One owner for three surfaces that used to spell it independently: `DashboardController` built the
 * whole sentence, `RaceDecisionController` built its two halves as separate props, and the snapshot
 * review had no label at all. The month names, the half/month arithmetic and the year lookup live
 * here so the three cannot disagree; each caller composes the pieces it ships, and every existing
 * string is unchanged.
 *
 * Pure: no state, no dependencies beyond the model's own grid constants. A caller passes a turn
 * (1..72) or the two parts it already holds.
 */
final class CareerPositionLabel
{
    /** The twelve month names, as the client's own Early/Late halves divide them. */
    private const MONTHS = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December',
    ];

    /** `Senior Year`, from the client's own year names; a year outside the grid prints its number. */
    public static function forYear(int $year): string
    {
        return (RaceCatalogSlot::YEARS[$year] ?? (string) $year).' Year';
    }

    /**
     * `Early October`: the position within the career year, 1..24. Odd positions are the month's
     * Early half and even positions its Late half, which is the grid the client itself uses.
     */
    public static function forPosition(int $position): string
    {
        return ($position % 2 === 1 ? 'Early ' : 'Late ')
            .self::MONTHS[intdiv($position - 1, 2)];
    }

    /** `Senior Year · Early October`, or null for a turn the career has not reached. */
    public static function forTurn(int $turn): ?string
    {
        if ($turn < 1) {
            return null;
        }

        $year = TrainingRun::careerYearForTurn($turn);
        $position = (($turn - 1) % TrainingRun::TURNS_PER_YEAR) + 1;

        return self::forYear($year).' · '.self::forPosition($position);
    }
}
