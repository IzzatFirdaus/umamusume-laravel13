<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Why the Trainer is running a career (FR-F-1, SCREEN-005 "Target purpose").
 *
 * The four cases are the design target's own list, minus the story-clear/general
 * split: `StoryClear` is the general-training case the brief calls "General
 * Training", and the run's purpose is entered, never inferred from the scenario.
 * A purpose is a statement of intent, so it is required on the payload rather than
 * nullable — a target with no purpose would be a stat list with no reason.
 *
 * The cases are this tool's contract values, not client strings; a Global client
 * capture does not name them, so a display slice owes the label map (HasLabel).
 */
enum BuildPurpose: string
{
    use HasLabel;

    case StoryClear = 'StoryClear';
    case ChampionsMeeting = 'ChampionsMeeting';
    case ParentFarming = 'ParentFarming';
    case SkillFarming = 'SkillFarming';
}
