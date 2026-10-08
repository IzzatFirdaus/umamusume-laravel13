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
}
