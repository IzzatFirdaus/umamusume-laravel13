<?php

declare(strict_types=1);

namespace App\Models\TurnEvents;

/**
 * The Team Rank the Trainer reads off the Unity Cup league board on a turn (US-10,
 * ADR-0003, D-222).
 *
 * It rides `turn_events` rather than a column because the rank is a per-turn scenario
 * fact that belongs to one scenario, and a `team_rank` column on every run would push
 * Unity Cup chrome into URA and Trackblazer rows that cannot hold it (D-221, D-226).
 *
 * The letter is entered, never inferred: nothing in this build reads the league, and
 * facility level is derived from the letter afterwards, through the config mapping, so
 * the one entered value is the whole input (D-270).
 *
 * `S+` is accepted and is deliberately outside `team_rank_ladder`: the config notes say
 * it sits above S and grants a second hint rather than a higher facility, so it has no
 * level to map to and the panel must not invent one.
 */
final readonly class TeamRankPayload
{
    public const KEYS = ['rank'];

    /** The letters the Global client's league board shows, S+ included. */
    public const LETTERS = ['G', 'F', 'E', 'D', 'C', 'B', 'A', 'S', 'S+'];

    public function __construct(public string $rank) {}

    public static function make(string $rank): self
    {
        if (! in_array($rank, self::LETTERS, true)) {
            throw new \InvalidArgumentException(
                'Team rank ['.$rank.'] is not one of the league letters ('
                .implode(', ', self::LETTERS).').',
            );
        }

        return new self($rank);
    }

    /**
     * @param  array<string, mixed>  $deltas
     */
    public static function matches(array $deltas): bool
    {
        return array_key_exists('rank', $deltas);
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
                'A team rank payload carries exactly ['.implode(', ', $expected).'] keys.',
            );
        }

        if (! is_string($deltas['rank'])) {
            throw new \InvalidArgumentException('A team rank payload needs a string rank.');
        }

        return self::make($deltas['rank']);
    }

    /**
     * @return array{rank: string}
     */
    public function toArray(): array
    {
        return ['rank' => $this->rank];
    }
}
