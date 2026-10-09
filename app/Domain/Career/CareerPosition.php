<?php

declare(strict_types=1);

namespace App\Domain\Career;

use App\Enums\CareerPhase;
use App\Enums\CareerYear;
use App\Services\CareerCalendar;
use InvalidArgumentException;

/**
 * Where a career stands, as the client's own calendar names it.
 *
 * The audit's finding was that a mid-career position had no representation: a run could hold a turn
 * count and nothing else, so a snapshot taken at Senior Early October read as a run standing in Junior
 * Early January. This value object is the thing the tool was missing, and it is the shape
 * `TrainingRun::nextTurnToPlay()` already computed in two parts (a year index and a turn-in-year)
 * with the month and half the cockpit and the race decision each spelled for themselves.
 *
 * A position is constructed from one source and read as a whole. `fromTurnIndex()` is the derived
 * case - an existing new-career run holds only a turn number - and `fromArray()` is the stored case,
 * which refuses a payload whose turn index disagrees with its own year, month and half rather than
 * trusting the larger of the two numbers.
 *
 * `scenarioCountdown` is the turns-until-a-scenario-milestone figure a snapshot may carry, and it is
 * nullable because a scenario with no pending milestone has no number: null is an absence, not a zero.
 */
final class CareerPosition
{
    public function __construct(
        public readonly CareerYear $year,
        public readonly int $month,
        public readonly CareerPhase $phase,
        public readonly int $turnIndex,
        public readonly ?int $scenarioCountdown = null,
    ) {
        if ($month < 1 || $month > 12) {
            throw new InvalidArgumentException("A career month is 1 to 12; [{$month}] is not.");
        }

        if ($turnIndex < 1 || $turnIndex > CareerCalendar::TURNS_PER_CAREER) {
            throw new InvalidArgumentException(
                'A career turn index is 1 to '.CareerCalendar::TURNS_PER_CAREER."; [{$turnIndex}] is not.",
            );
        }

        if ($scenarioCountdown !== null && $scenarioCountdown < 0) {
            throw new InvalidArgumentException(
                "A scenario countdown counts turns and cannot be negative; [{$scenarioCountdown}] is not.",
            );
        }
    }

    public static function fromTurnIndex(int $turnIndex, ?int $scenarioCountdown = null): self
    {
        return CareerCalendar::turnIndexToPosition($turnIndex, $scenarioCountdown);
    }

    /**
     * The stored shape, refused rather than trusted: a payload whose `turn_index` disagrees with the
     * year, month and half it travels with would let a snapshot claim two positions at once, and the
     * disagreement is exactly the defect this object exists to close.
     *
     * @param  array<string, mixed>  $payload
     *
     * @throws InvalidArgumentException when a field is missing, out of range, or inconsistent
     */
    public static function fromArray(array $payload): self
    {
        foreach (['year', 'month', 'phase', 'turn_index'] as $key) {
            if (! array_key_exists($key, $payload)) {
                throw new InvalidArgumentException("A career position requires [{$key}] and it is absent.");
            }
        }

        $year = CareerYear::tryFrom((int) $payload['year']);
        $phase = CareerPhase::tryFrom((string) $payload['phase']);

        if ($year === null) {
            throw new InvalidArgumentException(
                'A career year is one of '.implode(', ', array_column(CareerYear::cases(), 'name')).
                '; ['.var_export($payload['year'], true).'] is not.',
            );
        }

        if ($phase === null) {
            throw new InvalidArgumentException(
                'A career phase is one of '.implode(', ', array_column(CareerPhase::cases(), 'value')).
                '; ['.var_export($payload['phase'], true).'] is not.',
            );
        }

        $position = new self(
            year: $year,
            month: (int) $payload['month'],
            phase: $phase,
            turnIndex: (int) $payload['turn_index'],
            // Absent rather than required: a position stored before a milestone was pending has no
            // countdown, and that absence travels as null rather than as a key every writer must add.
            scenarioCountdown: $payload['scenario_countdown'] ?? null,
        );

        $expected = CareerCalendar::positionToTurnIndex($position);

        if ($expected !== $position->turnIndex) {
            throw new InvalidArgumentException(
                "A career position's turn index disagrees with its calendar: [{$position->year->name} ".
                "month {$position->month} {$position->phase->value}] is turn {$expected}, ".
                "not {$position->turnIndex}.",
            );
        }

        return $position;
    }

    /**
     * The stored shape. `year` keeps the integer `race_catalog_slots.year` stores so a position and a
     * catalogue row compare without a translation; `phase` keeps the word the client spells.
     *
     * @return array{year: int, month: int, phase: string, turn_index: int, scenario_countdown: int|null}
     */
    public function toArray(): array
    {
        return [
            'year' => $this->year->value,
            'month' => $this->month,
            'phase' => $this->phase->value,
            'turn_index' => $this->turnIndex,
            'scenario_countdown' => $this->scenarioCountdown,
        ];
    }
}
