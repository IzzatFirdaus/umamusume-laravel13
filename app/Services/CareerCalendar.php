<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Career\CareerPosition;
use App\Enums\CareerPhase;
use App\Enums\CareerYear;
use App\Models\RaceCatalogSlot;
use App\Models\TrainingRun;
use InvalidArgumentException;

/**
 * The one owner of the turn-index-to-calendar-position arithmetic.
 *
 * The mapping existed four times before this class: `TrainingRun::careerYearForTurn()` held the year,
 * `CockpitController` and `RaceDecisionController` each spelled the month and half for themselves, and
 * the race-catalog parser carries a month table of its own for reading the source. None is rewired
 * here - three of those files carry a concurrent session's uncommitted work - and this class is where
 * they converge when they are free. `config/scenarios.php` does not define the calendar: it holds
 * per-scenario rhythm, and the year length lives on the model the catalogue already reads.
 *
 * A career year is `TrainingRun::TURNS_PER_YEAR` turns, each turn one month-half: an odd turn is the
 * Early half of a month and an even turn its Late half. Turn 1 is Junior Year Early January, and turn
 * `TURNS_PER_CAREER` is Senior Year Late December.
 */
final class CareerCalendar
{
    public const TURNS_PER_CAREER = TrainingRun::TURNS_PER_YEAR * RaceCatalogSlot::YEAR_SENIOR;

    private const MONTHS = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December',
    ];

    /**
     * @throws InvalidArgumentException when the turn is outside a standard Global career
     */
    public static function turnIndexToPosition(int $turnIndex, ?int $scenarioCountdown = null): CareerPosition
    {
        if ($turnIndex < 1 || $turnIndex > self::TURNS_PER_CAREER) {
            throw new InvalidArgumentException(
                'A career turn index is 1 to '.self::TURNS_PER_CAREER."; [{$turnIndex}] is not.",
            );
        }

        $positionInYear = (($turnIndex - 1) % TrainingRun::TURNS_PER_YEAR) + 1;

        return new CareerPosition(
            year: CareerYear::from(intdiv($turnIndex - 1, TrainingRun::TURNS_PER_YEAR) + 1),
            month: intdiv($positionInYear - 1, 2) + 1,
            phase: $positionInYear % 2 === 1 ? CareerPhase::Early : CareerPhase::Late,
            turnIndex: $turnIndex,
            scenarioCountdown: $scenarioCountdown,
        );
    }

    public static function positionToTurnIndex(CareerPosition $position): int
    {
        return (($position->year->value - 1) * TrainingRun::TURNS_PER_YEAR)
            + (($position->month - 1) * 2)
            + ($position->phase === CareerPhase::Early ? 1 : 2);
    }

    /**
     * @throws InvalidArgumentException when the month is outside a calendar year
     */
    public static function monthLabel(int $month): string
    {
        if ($month < 1 || $month > 12) {
            throw new InvalidArgumentException("A career month is 1 to 12; [{$month}] is not.");
        }

        return self::MONTHS[$month - 1];
    }
}
