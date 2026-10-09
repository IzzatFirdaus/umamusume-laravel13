<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Which half of a month a turn sits in, as the client spells it.
 *
 * The backed values are the strings `scenario_slots.half` and `race_entries.half` already store, and
 * `StoreRaceEntryRequest` already validates against, so a position and those rows speak one word.
 */
enum CareerPhase: string
{
    case Early = 'Early';
    case Late = 'Late';
}
