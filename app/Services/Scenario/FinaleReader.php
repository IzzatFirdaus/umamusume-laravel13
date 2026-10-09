<?php

declare(strict_types=1);

namespace App\Services\Scenario;

use App\Models\RaceCatalogSlot;
use App\Models\TrainingRun;

/**
 * The scenario's finale as one run has it, in one place, for every screen that shows it.
 *
 * Three consumers read this shape: the Career Result's finale block, the Cockpit's baseline strip,
 * and the advisor's proximity line. They must not disagree about whether the block has arrived, so
 * the question is answered here once and the answer travels as data (`ADR-0015`).
 *
 * Three answers, and the middle one was the missing one: not yet reached, reached with nothing
 * recorded, and recorded. Nothing is derived and no state is stored twice — the finale is a
 * scenario-scoped mandatory row in the career calendar, and the run's own `race_entries` row against
 * it is the outcome.
 *
 * `reached` reads the catalogue's own comparator, so no caller re-implements the arithmetic. The
 * exhausted-grid case is handled rather than inherited: a career with every turn logged has no *next*
 * turn to compare from, and answering that as "not yet reached" would tell a finished career its
 * finale never came.
 *
 * The label is the `[S]` noun Cygames prints in notice 905, "Four Promo Concerts and one Grand
 * Concert", not the catalogue row's title, which reads "URA Finals Final (Grand Live)" and is filed
 * as KI-82. ponytail: hardcoded until KI-82 closes; the upgrade path is `$slot->title` on the day
 * that row carries a `[Global]` name, and nothing else here changes.
 *
 * Static because it holds no state and needs no binding: the only input is the run. A value object
 * can replace the array later without moving a call site.
 */
final class FinaleReader
{
    /**
     * @return array{reached: bool, outcome: string|null, placement: string|null, turn: int|null, label: string}|null
     */
    public static function forRun(TrainingRun $run): ?array
    {
        $slot = $run->hasScenario()
            ? RaceCatalogSlot::query()
                ->where('scenario_key', $run->scenarioKey())
                ->where('is_mandatory', true)
                ->where('year', RaceCatalogSlot::YEAR_FINALE)
                ->first()
            : null;

        if (! $slot instanceof RaceCatalogSlot) {
            return null;
        }

        $entry = $run->raceEntries->firstWhere('race_catalog_slot_id', $slot->id);

        // The grid's last instant is Senior turn 24 and the finale block opens after it, so a career
        // that has logged every turn has arrived. A career with nothing logged has no position at all,
        // and neither case may be folded into the other: only the first has reached anything.
        $next = $run->nextTurnToPlay();
        $position = $next ?? ($run->turnEntries->isNotEmpty()
            ? ['year' => RaceCatalogSlot::YEAR_SENIOR, 'turn' => TrainingRun::TURNS_PER_YEAR]
            : null);

        return [
            'reached' => $slot->isAtOrBeforeTurn($position),
            'outcome' => $entry?->status->label(),
            'placement' => $entry?->placementOrdinal(),
            'turn' => $entry?->turnEntry?->turn,
            'label' => 'Grand Concert',
        ];
    }
}
