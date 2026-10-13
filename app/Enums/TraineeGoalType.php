<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * What kind of objective a `trainee_goals` row states (PRD US-10, ADR-0026).
 *
 * The five are the source's own condition codes, not a UI grouping. `Undecoded` means the
 * kind itself is unknown (`cond_type` 2 and 12, six rows on the Global roster); an objective
 * whose kind *is* known but whose figure is not — `cond_value` 2 or 4 — stays `Placement`
 * with `TraineeGoal::requirementPhrase()` returning null, because "this is a placing
 * requirement, and this figure is unread" is a truer statement than a invented sixth kind.
 */
enum TraineeGoalType: string
{
    use HasLabel;

    case Placement = 'Placement';
    case Participate = 'Participate';
    case Fan = 'Fan';
    case FinalRound = 'FinalRound';
    case Undecoded = 'Undecoded';
}
