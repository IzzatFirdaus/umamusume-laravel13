<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * What fired a resolved event (ADR-0003 turn_events). Scenario is the catch-all
 * for an event that belongs to the run's scenario rather than to a unit or card.
 */
enum TurnEventType: string
{
    case Character = 'Character';
    case SupportCard = 'SupportCard';
    case Group = 'Group';
    case Scenario = 'Scenario';
}
