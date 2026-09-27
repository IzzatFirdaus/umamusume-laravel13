<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Catalog availability of an Umamusume for the Global version (PRD FR-A-1).
 * JapanOnly is data, not an error: the character exists on JP but is not
 * available to a Global Trainer yet.
 */
enum ReleaseStatus: string
{
    use HasLabel;

    case GlobalReleased = 'GlobalReleased';
    case GlobalAnnounced = 'GlobalAnnounced';
    case JapanOnly = 'JapanOnly';
}
