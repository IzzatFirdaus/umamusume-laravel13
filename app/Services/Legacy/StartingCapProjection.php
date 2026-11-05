<?php

declare(strict_types=1);

namespace App\Services\Legacy;

use App\Models\TrainingRun;
use App\Services\ScenarioCaps;

/**
 * The ceiling a run's recorded parents imply before its first turn, and the provenance to print
 * beside it.
 *
 * `ADR-0025`'s "no derivation" clause is narrowed to exactly this computation by its 2026-10-31
 * amendment, and `ADR-0020` §3's record-only stance by the amendment of the same date. It is a
 * separate object so `AncestryGraph` keeps its "computes nothing" charter literally.
 *
 * **It computes one figure and no more.** Per `UMAMUSUME_REFERENCE.md:645`, career start is
 * deterministic for stat and aptitude factors, so the sum is arithmetic on a stated table rather than
 * a forecast: `:420` prices the raise at +16 per ★3 basic-ability factor, and `ScenarioCaps::capRaise()`
 * is the only place that number appears. No score, no affinity payout, no Spark roll, no ranking.
 *
 * **The figure is JP-sourced and stale, and says so.** The `+16` row is tagged `[JP]` and cited to one
 * `⚠️ STALE` guide, so the projected cell must print `label()` beside the number (`GP-2`, `D-256`,
 * `TOG-3`). A unique-skill factor also raises caps "in proportion to the donor's growth rates" with no
 * published magnitude, so it is stated as a direction and rendered as an absence, never a number.
 */
final class StartingCapProjection
{
    /**
     * The ceiling per stat for this run's recorded parents, or null when it has no selection.
     *
     * The base is the run's own cap — the entered reading when the Trainer recorded one, the
     * scenario's published cap otherwise, both read through `ScenarioCaps::forRun()` so a form and a
     * drawn ceiling cannot disagree (`ADR-0015`). The sum is clamped by the engine ceiling.
     *
     * @return list<array{stat: string, label: string, base: int, raise: int, ceiling: int}>|null
     */
    public static function project(TrainingRun $run): ?array
    {
        $selection = $run->legacySelection();

        if ($selection === null) {
            return null;
        }

        $hardCap = ScenarioCaps::hardCap();
        $legacies = $selection->toArray()['legacies'];
        $rows = [];

        // `forRun()` is `stat => ceiling`, already carrying the entered reading where one exists and the
        // scenario's published cap otherwise (`ADR-0015`), so the base needs no second resolution here.
        foreach (ScenarioCaps::forRun($run) as $stat => $base) {
            $label = ucfirst($stat);
            $raise = 0;

            foreach ($legacies as $legacy) {
                foreach ($legacy['sparks'] as $spark) {
                    if (strcasecmp((string) $spark['target'], $label) !== 0) {
                        continue;
                    }

                    $raise += ScenarioCaps::capRaise((int) ($spark['stars'] ?? 0), (string) $spark['kind']);
                }
            }

            $rows[] = [
                'stat' => $stat,
                'label' => $label,
                'base' => $base,
                'raise' => $raise,
                'ceiling' => min($hardCap, $base + $raise),
            ];
        }

        return $rows;
    }

    /**
     * The provenance the projected cell prints in visible words.
     */
    public static function label(): string
    {
        return 'This tool\'s arithmetic on a JP-sourced cap-raise table (Game8, 2025-11-21, marked stale), not a client reading. Only the ★3 tier is stated in the reference (+16 per ★3 Blue factor), so a ★1 or ★2 factor adds nothing to this ceiling.';
    }
}
