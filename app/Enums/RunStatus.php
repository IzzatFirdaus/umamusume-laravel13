<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Lifecycle of a Trainer's training run (PRD FR-C-1). Retired means the run was
 * abandoned before completion; no run math depends on the status value.
 */
enum RunStatus: string
{
    use HasLabel;

    case Active = 'Active';
    case Completed = 'Completed';
    case Retired = 'Retired';
}
