<?php

declare(strict_types=1);

namespace App\Domain\Training;

use InvalidArgumentException;

/**
 * The ceilings one run's stats are measured against, as two distinct levels.
 *
 * The audit's experiential finding was that the tool showed one ceiling where a Trainer's client shows
 * three: the halved-gains line, the scenario's own ceiling, and the level the run could still reach.
 * `knownCap` is the second of those and is what the validators clamp against today (`ScenarioCaps`
 * has always owned it). `potentialCap` is the third, and it is nullable per stat because a level the
 * run has no recorded basis for is an absence the screen must show as one, never a number lent from
 * the hard cap (`ADR-0015`, D-162).
 */
final readonly class StatCeilings
{
    /**
     * @param  array<string, int>  $knownCap  keyed by the stat order the config publishes
     * @param  array<string, int|null>  $potentialCap  same keys; null where no potential is known
     */
    public function __construct(
        public array $knownCap,
        public array $potentialCap,
    ) {
        if (array_keys($this->knownCap) !== array_keys($this->potentialCap)) {
            throw new InvalidArgumentException(
                'A ceiling pair must name the same stats for both levels; the known caps carry ['.
                implode(', ', array_keys($this->knownCap)).'] and the potentials ['.
                implode(', ', array_keys($this->potentialCap)).'].',
            );
        }
    }

    /**
     * @return list<string>
     */
    public function stats(): array
    {
        return array_keys($this->knownCap);
    }
}
