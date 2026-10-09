<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The three career years, in the order the client's calendar plays them.
 *
 * The backed values are the integers `race_catalog_slots.year` stores, so a position and a catalogue
 * row compare without a translation step - `RaceCatalogSlot::isAtOrBeforeTurn()` is the existing
 * reader of that arithmetic and keeps its own constants until its file is next touched.
 */
enum CareerYear: int
{
    case Junior = 1;
    case Classic = 2;
    case Senior = 3;

    /**
     * The position phrase, as the cockpit and the timeline say it. The race calendar's tabs keep
     * `RaceCatalogSlot::YEARS`'s short form (`Junior`), which is a tab label and not this phrase.
     */
    public function label(): string
    {
        return match ($this) {
            self::Junior => 'Junior Year',
            self::Classic => 'Classic Year',
            self::Senior => 'Senior Year',
        };
    }
}
