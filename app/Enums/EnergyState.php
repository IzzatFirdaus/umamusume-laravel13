<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * How a turn's Energy was recorded: a number, a coarse band, or not at all.
 *
 * The numeric `energy` column holds a figure the Trainer read exactly. A band is the honest reading
 * when the client shows a rough level rather than a number ("near a third"), and `Unknown` is the
 * explicit "not recorded" the audit found the tool collapsing into a bare N/A everywhere. The three
 * are kept apart so the advisor can name the state it is reasoning from instead of declining.
 */
enum EnergyState: string
{
    case Exact = 'exact';
    case Band = 'band';
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::Exact => 'Exact',
            self::Band => 'Band',
            self::Unknown => 'Not recorded',
        };
    }
}
