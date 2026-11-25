<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * What the Trainer did with one calendar slot (ADR-0003 race_entries).
 * Skipped and NotOffered are both stored rows on purpose: an absent row cannot
 * tell "declined" from "never offered", and the calendar renders them apart.
 */
enum RaceEntryStatus: string
{
    use HasLabel;

    case NotOffered = 'NotOffered';
    case Skipped = 'Skipped';
    case Entered = 'Entered';
    case Completed = 'Completed';

    /**
     * A calendar slot this build imported with no observed entry decision (KI-171).
     *
     * Read-side provenance, not an outcome: it says the calendar holds a race here, not that the
     * Trainer did anything about it. It is therefore absent from `writable()` — a hand-entered row
     * claiming imported provenance would defeat the distinction — and `recordsEntry()` is false for
     * it, so an obligation whose only row is Scheduled stays open (KI-173).
     */
    case Scheduled = 'Scheduled';

    /**
     * The statuses a Trainer may actually post.
     *
     * `Scheduled` is deliberately absent: it records where a row came from, and a form that could
     * write it would be a hand-entered row claiming imported provenance.
     *
     * @return list<self>
     */
    public static function writable(): array
    {
        return [
            self::NotOffered,
            self::Skipped,
            self::Entered,
            self::Completed,
        ];
    }

    /**
     * Whether a row in this state records an entry decision.
     *
     * Every reader that asks "has the Trainer decided on this race?" goes through here rather than
     * comparing against `Completed`, because a `Scheduled` row counted as an entry cleared the
     * obligation, emptied the still-to-come strip and greyed a planner row (KI-173).
     */
    public function recordsEntry(): bool
    {
        return $this !== self::Scheduled;
    }
}
