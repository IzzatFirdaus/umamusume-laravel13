<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Script of an alias surface form (PRD FR-A-2). Tells the matcher and the
 * Trainer which writing system an alternate name belongs to; matching itself
 * is script-agnostic (it normalizes both sides).
 */
enum AliasLanguage: string
{
    use HasLabel;

    case Japanese = 'Japanese';
    case English = 'English';
    case Romanized = 'Romanized';
}
