<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TrainingRun;
use InvalidArgumentException;

/**
 * The ceiling one stat may reach in one scenario.
 *
 * The rule is the one `x-stat-band` already renders: the base cap plus that scenario's
 * own bonus for that stat, clamped to the engine hard cap. It lives here so the validator
 * and the band read the same number — the disagreement was the defect ADR-0002 recorded
 * and ADR-0015 closes, where a Trainer could see a reachable cap the form rejected.
 *
 * The source is `config/scenarios.php` (`base_cap`, `hard_cap`, per-scenario `cap_bonus`),
 * which its own header dates to 2026-09-27 against three Global sources.
 */
class ScenarioCaps
{
    /**
     * Every stat ceiling for one scenario key, keyed by the matrix's own stat names.
     *
     * @return array<string, int>
     */
    public static function caps(string $scenarioKey): array
    {
        $def = config("scenarios.scenarios.{$scenarioKey}");

        if (! is_array($def)) {
            throw new InvalidArgumentException("Unknown scenario [{$scenarioKey}] has no cap row.");
        }

        $base = (int) config('scenarios.base_cap');
        $hardCap = (int) config('scenarios.hard_cap');

        /** @var array<int, string> $order */
        $order = config('scenarios.stat_order');

        /** @var array<string, mixed> $bonus */
        $bonus = (array) ($def['cap_bonus'] ?? []);

        $caps = [];

        foreach ($order as $stat) {
            // min() against the hard cap is the engine ceiling, not a scenario fact: a
            // bonus that would push past it is bounded by it rather than trusted.
            $caps[$stat] = min($base + (int) ($bonus[$stat] ?? 0), $hardCap);
        }

        return $caps;
    }

    /**
     * One stat's ceiling in one scenario.
     */
    public static function stat(string $scenarioKey, string $stat): int
    {
        $caps = self::caps($scenarioKey);

        if (! array_key_exists($stat, $caps)) {
            throw new InvalidArgumentException("Unknown stat [{$stat}] has no ceiling.");
        }

        return $caps[$stat];
    }

    /**
     * The ceilings a run's turns are measured against.
     *
     * A run that names no scenario gets the base cap with **no bonus**. `scenarioKey()`
     * resolves null to the baseline so the strip has a descriptor to compose from, but a
     * bonus belongs to a scenario the Trainer chose, and lending URA Finale's +200 to a
     * run that never picked it would accept a number the tool has no source for. This is
     * the same refusal the panels already make on `hasScenario()` (D-220, D-221).
     *
     * @return array<string, int>
     */
    public static function forRun(?TrainingRun $run): array
    {
        if ($run === null || ! $run->hasScenario()) {
            $base = (int) config('scenarios.base_cap');
            $hardCap = (int) config('scenarios.hard_cap');

            /** @var array<int, string> $order */
            $order = config('scenarios.stat_order');

            $caps = [];

            foreach ($order as $stat) {
                $caps[$stat] = min($base, $hardCap);
            }

            return $caps;
        }

        return self::caps($run->scenarioKey());
    }
}
