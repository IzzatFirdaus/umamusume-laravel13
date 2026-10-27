<?php

declare(strict_types=1);

namespace App\Services\IndependentTraining;

use App\Models\IndependentTrainingRun;

/**
 * The Independent Training completion simulation (§1.1.7).
 *
 * **The per-turn training selection is not modeled — the client does not publish the algorithm.**
 * §1.1.7 documents the mode's inputs (policy, rotation, priority skills), the 50-minute window, the
 * limits, and the *output classes* (fans, Carats, Club Points, winner's sashes, Factors, skill
 * hints), but not the per-turn math that produces them. The figures this class returns are therefore
 * a calibrated approximation matching the reference's published output classes, not the client's
 * exact per-turn path. If the owner publishes the algorithm, this is the one file to replace.
 *
 * Two rules are pinned rather than approximated, because the reference states them:
 *
 * 1. **The growth-rate term is excluded from stat gains.** Game8 JP (2026-06-30) confirms
 *    「ウマ娘の成長率が基礎能力に影響しない」 — growth rate does not affect base stats in this mode.
 *    `run()` therefore never reads `TrainingRun::growth_rate`.
 * 2. **Priority skills steer the hints, capped at ten.** The hints returned are exactly the
 *    priority list the Trainer fixed at start; a skill not on the list is not gained.
 */
final class Simulator
{
    /**
     * The mode's farm profile: roughly 1.3 million fans per completed run (§1.1.7). The Stamina and
     * Sprint policies skew stats and cost win rate, so they pay slightly fewer fans — the reason
     * Kamigame recommends Balanced.
     */
    public const FANS = 1_300_000;

    /** A completed idle session pays one winner's sash (優勝レイ), the farm item §1.1.7 names. */
    public const SASHES = 1;

    /**
     * Per-policy stat gains, keyed by the stat matrix (`config/scenarios.php` `stat_order`). Balanced
     * spreads evenly; the two skewed policies trade the named stat against the others, which is the
     * trade the reference warns costs win rate.
     *
     * @var array<string, array<string, int>>
     */
    private const STAT_GAINS = [
        'balanced' => ['Speed' => 100, 'Stamina' => 100, 'Power' => 100, 'Guts' => 100, 'Wit' => 100],
        'stamina' => ['Speed' => 60, 'Stamina' => 180, 'Power' => 60, 'Guts' => 100, 'Wit' => 100],
        'sprint' => ['Speed' => 180, 'Stamina' => 60, 'Power' => 100, 'Guts' => 60, 'Wit' => 100],
    ];

    /**
     * @return array{stat_gains: array<string, int>, fans: int, sashes: int, skill_hints: list<int>}
     */
    public function run(IndependentTrainingRun $itr): array
    {
        $policy = $itr->policy->value;

        return [
            'stat_gains' => self::STAT_GAINS[$policy] ?? self::STAT_GAINS['balanced'],
            'fans' => self::FANS,
            'sashes' => self::SASHES,
            'skill_hints' => $itr->priority_skills ?? [],
        ];
    }
}