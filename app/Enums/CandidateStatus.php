<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Review-queue workflow for match candidates (PRD US-5). Pending is the intake
 * state; Confirmed promotes the payload, Aliased records it as an alias of an
 * existing row, Rejected closes the candidate without catalog changes.
 */
enum CandidateStatus: string
{
    case Pending = 'Pending';
    case Confirmed = 'Confirmed';
    case Aliased = 'Aliased';
    case Rejected = 'Rejected';
}
