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
}
