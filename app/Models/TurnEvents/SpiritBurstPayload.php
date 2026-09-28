<?php

declare(strict_types=1);

namespace App\Models\TurnEvents;

use App\Enums\SpiritBurstState;

/**
 * Which of the six Spirit Burst states one teammate was in on one turn (D-223,
 * D-226).
 *
 * Stored as a state, not as a counter. `bursts_triggered = 2` cannot express "charged
 * and deliberately held", and it turns Extreme spent into a dead end, which is the
 * reading the 2026-07-01 update made wrong (D-223). It is not a `turn_entries` column
 * either, because the mechanic belongs to one scenario.
 *
 * The state arrives as the enum or as one of its six values, and nothing else: a
 * seventh value is not a state this machine has, and coercing one into "spent" would
 * be inventing game rules at the storage layer.
 */
final readonly class SpiritBurstPayload
{
    public const KEYS = ['teammate', 'state'];

    public function __construct(public string $teammate, public SpiritBurstState $state) {}

    public static function make(string $teammate, SpiritBurstState $state): self
    {
        if (trim($teammate) === '') {
            throw new \InvalidArgumentException('A burst payload needs the teammate it is about.');
        }

        return new self($teammate, $state);
    }

    /**
     * @param  array<string, mixed>  $deltas
     */
    public static function matches(array $deltas): bool
    {
        return array_key_exists('teammate', $deltas) || array_key_exists('state', $deltas);
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
                'A burst payload carries exactly ['.implode(', ', $expected).
                '] keys, got ['.implode(', ', array_keys($deltas)).'].',
            );
        }

        if (! is_string($deltas['teammate'])) {
            throw new \InvalidArgumentException('A burst payload needs a string teammate.');
        }

        $state = $deltas['state'];

        if ($state instanceof SpiritBurstState) {
            return self::make($deltas['teammate'], $state);
        }

        if (! is_string($state) || SpiritBurstState::tryFrom($state) === null) {
            $shown = is_scalar($state) ? (string) $state : get_debug_type($state);

            throw new \InvalidArgumentException(
                "Spirit Burst state [{$shown}] is not one of the six Spirit Burst states (".
                implode(', ', array_map(static fn (SpiritBurstState $s): string => $s->value, SpiritBurstState::cases())).').',
            );
        }

        return self::make($deltas['teammate'], SpiritBurstState::from($state));
    }

    /**
     * @return array{teammate: string, state: string}
     */
    public function toArray(): array
    {
        return ['teammate' => $this->teammate, 'state' => $this->state->value];
    }
}
