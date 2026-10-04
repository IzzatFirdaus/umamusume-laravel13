<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\SupportCard;
use App\Models\Umamusume;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The Trainer Desk 2.0 landing screen (SCREEN-001, docs/proposals/screen-spec-2.0.md).
 * Thin: the counts are read-only catalogue facts rendered as CONFIRMED values
 * (design-2.0 §31), no business logic. ADR-0020 §1.
 */
class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'counts' => [
                'trainees' => Umamusume::count(),
                'skills' => Skill::count(),
                'supportCards' => SupportCard::count(),
            ],
        ]);
    }
}
