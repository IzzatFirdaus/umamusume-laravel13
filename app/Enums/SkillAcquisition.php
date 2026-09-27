<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Suggested = a skill the Trainer marked for this run before it happened;
 * Acquired / Skipped = outcome, recorded when the turn that would have
 * triggered it is logged (PRD FR-C-3).
 */
enum SkillAcquisition: string
{
    use HasLabel;

    case Suggested = 'Suggested';
    case Acquired = 'Acquired';
    case Skipped = 'Skipped';
}
