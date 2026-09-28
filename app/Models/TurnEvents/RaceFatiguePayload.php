<?php

declare(strict_types=1);

namespace App\Models\TurnEvents;

/**
 * How many races in a row the Trainer says this run has just run (D-230, ADR-0003).
 *
 * Race Fatigue keys on the count of consecutive races and on nothing else, and the
 * count is entered here rather than derived: `race_entries` points at a timeline slot,
 * never at a turn, and no guided choice marks a race turn, so the run of consecutive
 * races is not recoverable from the log as the schema stands. D-230's premise that it
 * is recoverable from `turn_entries` is recorded as not holding, and KI-17 carries the
 * gap; this payload is the honest way to keep the fact without inventing a link.
 *
 * The word is a label over the sourced bands, not a probability. `docs/scenarios/05`
 * §Race Fatigue publishes 0-15 / 0-33 / 60-90+ / 100 percent mood-down ranges against
 * 1 / 2 / 3 / 4+ consecutive races, and one source is not two, so the panel prints the
 * word and the pointer and never a percentage (D-230).
 */
final readonly class RaceFatiguePayload
{
    public const KEYS = ['consecutive_races'];

    public function __construct(public int $consecutiveRaces) {}

    public static function make(int $consecutiveRaces): self
    {
        if ($consecutiveRaces < 1) {
            throw new \InvalidArgumentException(
                "consecutive_races [{$consecutiveRaces}] cannot be below one: a fatigue reading is recorded only when a race was just run.",
            );
        }

        return new self($consecutiveRaces);
    }

    /**
     * @param  array<string, mixed>  $deltas
     */
    public static function matches(array $deltas): bool
    {
        return array_key_exists('consecutive_races', $deltas);
    }

    /**
     * @param  array<string, mixed>  $deltas
     */
    public static function fromArray(array $deltas): self
    {
        $keys = array_keys($deltas);
        sort($keys);
        $expected = self::KEYS;
        sort($expected);

        if ($keys !== $expected) {
            throw new \InvalidArgumentException(
                'A fatigue payload carries exactly ['.implode(', ', $expected).'] keys.',
            );
        }

        if (! is_int($deltas['consecutive_races'])) {
            throw new \InvalidArgumentException('A fatigue payload needs an int consecutive_races.');
        }

        return self::make($deltas['consecutive_races']);
    }

    /**
     * The qualitative band this count falls in, named for the sourced range it labels.
     */
    public function riskWord(): string
    {
        return match (true) {
            $this->consecutiveRaces >= 4 => 'certain',
            $this->consecutiveRaces === 3 => 'likely',
            $this->consecutiveRaces === 2 => 'possible',
            default => 'unlikely',
        };
    }

    /**
     * @return array{consecutive_races: int}
     */
    public function toArray(): array
    {
        return ['consecutive_races' => $this->consecutiveRaces];
    }
}
