<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The five mood tiers a Trainer reads off the client's Mood panel (ADR-0001,
 * ADR-0003 turn_entries). Case names follow this repo's TitleCase enum rule;
 * the backing values are the [Global] client strings verbatim, so the value is
 * what a Trainer sees and no display layer has to translate it.
 */
enum MoodTier: string
{
    case Great = 'GREAT';
    case Good = 'GOOD';
    case Normal = 'NORMAL';
    case Bad = 'BAD';
    case Awful = 'AWFUL';

    /**
     * The directional glyph D-259 makes mandatory. The three derived tiers sit 1.0-3.3
     * degrees apart at equal luminance, so colour carries no ordering and this glyph is
     * the only ordinal signal the readout has. One method rather than a per-view array
     * because a pill and a select must not disagree about which way is down.
     */
    public function arrow(): string
    {
        return match ($this) {
            self::Great, self::Good => '↑',
            self::Normal => '→',
            self::Bad, self::Awful => '↓',
        };
    }
}
