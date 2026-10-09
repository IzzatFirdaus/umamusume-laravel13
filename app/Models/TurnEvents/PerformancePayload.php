<?php

declare(strict_types=1);

namespace App\Models\TurnEvents;

use App\Enums\PerformanceType;

/**
 * One observed change to one Performance type, on one turn (Our Grand Concert).
 *
 * **A delta, not a level.** `TurnEvent`'s own contract is that `deltas` records what was
 * observed and never what the run's absolute state was, so this carries the change the
 * Trainer recorded and not a running total. That is also the only honest shape here: the
 * value a run *starts* with is not published, so a stored level would have to be trusted
 * from the first turn while a delta stands on its own from any turn.
 *
 * **The sign is the transition.** A positive delta is Performance the run acquired, the
 * way a training turn pays it; a negative delta is Performance spent, the way the Lesson
 * menu spends it (`docs/scenarios/07-grand-concert.md` §"The loop, in order" steps 1-2).
 * One signed integer carries both because both are the same kind of fact, and a second
 * field would only restate the sign.
 *
 * **No amount is defined here and none is defaulted.** What a turn pays, what a Lesson
 * costs and what a live adds are all unverified per the guide's boundary, so this class
 * stores what the Trainer entered and refuses to invent a figure for a turn that did not
 * report one. A type the corpus does not name, a missing key, and a zero delta are each
 * rejected rather than coerced, because a coerced value is a game rule invented at the
 * storage layer.
 */
final readonly class PerformancePayload
{
    public const KEYS = ['type', 'delta'];

    public function __construct(public PerformanceType $type, public int $delta) {}

    public static function make(PerformanceType $type, int $delta): self
    {
        if ($delta === 0) {
            throw new \InvalidArgumentException(
                'A Performance payload records a change, and a zero delta is not one.',
            );
        }

        return new self($type, $delta);
    }

    /**
     * @param  array<string, mixed>  $deltas
     */
    public static function matches(array $deltas): bool
    {
        return array_key_exists('type', $deltas) || array_key_exists('delta', $deltas);
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
                'A Performance payload carries exactly ['.implode(', ', $expected).
                '] keys, got ['.implode(', ', array_keys($deltas)).'].',
            );
        }

        $type = $deltas['type'];

        if (! $type instanceof PerformanceType) {
            if (! is_string($type) || PerformanceType::tryFrom($type) === null) {
                $shown = is_scalar($type) ? (string) $type : get_debug_type($type);

                throw new \InvalidArgumentException(
                    "Performance type [{$shown}] is not one of the five Performance types (".
                    implode(', ', array_map(static fn (PerformanceType $t): string => $t->value, PerformanceType::cases())).').',
                );
            }

            $type = PerformanceType::from($type);
        }

        $delta = $deltas['delta'];

        if (! is_int($delta)) {
            $shown = is_scalar($delta) ? (string) $delta : get_debug_type($delta);

            throw new \InvalidArgumentException(
                "A Performance payload needs an integer delta, got [{$shown}].",
            );
        }

        return self::make($type, $delta);
    }

    /**
     * @return array{type: string, delta: int}
     */
    public function toArray(): array
    {
        return ['type' => $this->type->value, 'delta' => $this->delta];
    }
}
