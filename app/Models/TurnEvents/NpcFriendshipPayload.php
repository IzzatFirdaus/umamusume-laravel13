<?php

declare(strict_types=1);

namespace App\Models\TurnEvents;

/**
 * The friendship bars a Trainer read off the client for one NPC on one turn
 * (D-226, ADR-0003 turn_events).
 *
 * It is a value object rather than a `turn_entries` column because which NPCs the run
 * even contains is scenario-specific: Director Akikawa is absent from Unity Cup, so a
 * `akikawa_bars` column would push one scenario's chrome into every run's core record
 * and then be null for the runs that cannot have it.
 *
 * `bars` is stored exactly as entered, with no ceiling and no conversion into a
 * percentage: the corpus names gates in bars ("3-bar friendship", "max friendship")
 * and never the number of bars a full meter holds, so inventing a denominator would be
 * a fabricated fact wearing a progress bar (D-20, D-270).
 *
 * `npc` is this tool's key, not a client string. The per-scenario set of trackable NPCs
 * is a config decision that has not been made yet, so nothing here validates a key
 * against a registry; the display slice that shows a name owes that list.
 */
final readonly class NpcFriendshipPayload
{
    public const KEYS = ['npc', 'bars'];

    public function __construct(public string $npc, public int $bars) {}

    public static function make(string $npc, int $bars): self
    {
        if (trim($npc) === '') {
            throw new \InvalidArgumentException('A friendship payload needs the NPC it is about.');
        }

        if ($bars < 0) {
            throw new \InvalidArgumentException("bars [{$bars}] cannot be negative; friendship is entered as read.");
        }

        return new self($npc, $bars);
    }

    /**
     * @param  array<string, mixed>  $deltas
     */
    public static function matches(array $deltas): bool
    {
        return array_key_exists('npc', $deltas);
    }

    /**
     * Read a stored payload, rejecting any shape this class does not own.
     *
     * The exact-key test is the point: a payload that arrives with an extra key, or
     * arrives through a cast that dropped one, is a fact the tool cannot account for,
     * and quietly ignoring it is how a stored number goes missing without a failure.
     *
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
                'A friendship payload carries exactly ['.implode(', ', $expected).
                '] keys, got ['.implode(', ', array_keys($deltas)).'].',
            );
        }

        if (! is_string($deltas['npc']) || ! is_int($deltas['bars'])) {
            throw new \InvalidArgumentException('A friendship payload needs a string npc and an int bars.');
        }

        return self::make($deltas['npc'], $deltas['bars']);
    }

    /**
     * @return array{npc: string, bars: int}
     */
    public function toArray(): array
    {
        return ['npc' => $this->npc, 'bars' => $this->bars];
    }
}
