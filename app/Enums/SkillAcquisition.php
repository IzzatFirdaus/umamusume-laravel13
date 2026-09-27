<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Suggested = planned before the run; Acquired/Skipped = outcome (PRD FR-C-3).
 */
enum SkillAcquisition: string
{
    use HasLabel;

    case Suggested = 'Suggested';
    case Acquired = 'Acquired';
    case Skipped = 'Skipped';
}
