<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * What fired a resolved event (ADR-0003 turn_events). Scenario is the catch-all
 * for an event that belongs to the run's scenario rather than to a unit or card.
 *
 * Failure is not a source like the other four: it is the Trainer recording that the
 * turn's chosen activity failed. The word is the client's own — it surfaces a
 * `Failure 0%` badge during training (`DESIGN.md` §6.16b) — so it is a measured string
 * rather than one this tool composed. It is the only case that writes a row for a
 * *turn*: `turn_entries` already records the absolute values a success produced, so a
 * success needs no event row, and D-200 makes the failure branch mandatory enough that
 * recording one has to have somewhere to land.
 */
enum TurnEventType: string
{
    case Character = 'Character';
    case SupportCard = 'SupportCard';
    case Group = 'Group';
    case Scenario = 'Scenario';
    case Inheritance = 'Inheritance';
    case Failure = 'Failure';
}
