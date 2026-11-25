<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The five distance columns the Unity Cup Team tab lays its roster out in (`D01`).
 *
 * §1.8 of `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md`: "five distance columns, Sprint, Mile,
 * Medium, Long and Dirt, with an **ACE** slot plus two more in each, matching the one-to-three-per-
 * distance rule at `docs/scenarios/02-unity-cup.md:170`."
 *
 * **Dirt is here and not in `BuildTargetPayload::DISTANCE_BANDS`.** The four bands are a training
 * target's distances; this list is the team's columns, and `config/scenarios.php`'s
 * `team_race.categories` records the difference in its own comment: "This is deliberately NOT
 * `BuildTargetPayload::DISTANCE_BANDS` (four, no Dirt): keying a team race off that list would drop
 * a category the guides name." Keying a member's column off the four would do the same.
 *
 * A member with no column is unassigned, which is `null` rather than a sixth case: a row the Trainer
 * added by name has no reading yet, and an invented column would be a claim about a record that does
 * not exist (D-220).
 */
enum RosterDistance: string
{
    use HasLabel;

    case Sprint = 'Sprint';
    case Mile = 'Mile';
    case Medium = 'Medium';
    case Long = 'Long';
    case Dirt = 'Dirt';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $distance): string => $distance->value, self::cases());
    }
}
