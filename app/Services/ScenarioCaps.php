<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Training\StatCeilings;
use App\Models\TrainingRun;
use InvalidArgumentException;

/**
 * The ceiling one stat may reach in one run.
 *
 * Two readings, one owner. `forRun()` answers what a form may accept and what a band draws: a ceiling
 * the Trainer entered for that run (`ADR-0025`) where one exists, and otherwise the published rule.
 * `scenarioCaps()` answers that published rule on its own, for the surface whose source line names the
 * matrix. The rule is the one `x-stat-band` already renders: the base cap plus that scenario's
 * own bonus for that stat, clamped to the engine hard cap. It lives here so the validator
 * and the band read the same number — the disagreement was the defect ADR-0002 recorded
 * and ADR-0015 closes, where a Trainer could see a reachable cap the form rejected.
 *
 * The source is `config/scenarios.php` (`base_cap`, `hard_cap`, per-scenario `cap_bonus`),
 * which its own header dates to 2026-09-27 against three Global sources. An entered ceiling comes from
 * no config and no fetch: it is the figure the Trainer read off the client, stored on the run.
 */
class ScenarioCaps
{
    /**
     * The four stats whose ceiling a Trainer may enter, keyed by the stat matrix and naming the
     * `training_runs` column that holds each (`ADR-0025`).
     *
     * Wit is absent: its scenario bound is 1800 and no ruled surface writes a Wit ceiling, so there is no
     * column to read. This map is the one list of the entered stats; the requests build their rules from
     * it, so a fifth entered ceiling needs a column, a ruling and this line rather than three of them.
     */
    public const ENTERED_CAPS = [
        'Speed' => 'cap_speed',
        'Stamina' => 'cap_stamina',
        'Power' => 'cap_power',
        'Guts' => 'cap_guts',
    ];

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
     * The engine ceiling no stat may pass, whatever the scenario's bonus says.
     *
     * The number the form validators clamp against, so a validator and a renderer read the same
     * bound from the same place (`ADR-0015`) instead of each reading `config('scenarios.hard_cap')`
     * and trusting the other to have read it too.
     */
    public static function hardCap(): int
    {
        return (int) config('scenarios.hard_cap');
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
     * A ceiling the Trainer entered for that run wins over the scenario's own arithmetic, and a stat with
     * no entered ceiling keeps the answer `scenarioCaps()` gives. The engine hard cap still bounds the
     * result: an entered figure is a reading, not a licence to pass the ceiling the engine imposes.
     *
     * One owner, one read — every validator, band and advisor asks this, so a form bound and a drawn
     * ceiling cannot disagree (`ADR-0015`, extended by `ADR-0025`).
     *
     * @return array<string, int>
     */
    public static function forRun(?TrainingRun $run): array
    {
        $caps = self::scenarioCaps($run);

        foreach (self::enteredCaps($run) as $stat => $entered) {
            if ($entered !== null) {
                $caps[$stat] = min($entered, self::hardCap());
            }
        }

        return $caps;
    }

    /**
     * The ceilings entered on this run, keyed by the stat matrix, null where a stat states none.
     *
     * The only place the four columns are read. A cast makes each an int or a null; a value that is
     * neither (a row written by hand, or a column that predates its own cast) answers null rather than a
     * figure the tool cannot stand behind.
     *
     * @return array<string, int|null>
     */
    public static function enteredCaps(?TrainingRun $run): array
    {
        $caps = [];

        foreach (self::ENTERED_CAPS as $stat => $column) {
            $value = $run?->getAttribute($column);
            $caps[$stat] = is_int($value) ? $value : null;
        }

        return $caps;
    }

    /**
     * The ceiling the scenario's own composition states, ignoring anything the Trainer entered.
     *
     * This is the published arithmetic: `base_cap` plus the scenario's bonus, clamped to the hard cap. A
     * surface that cites `config/scenarios.php` as its source prints this, because an entered ceiling is
     * the Trainer's observation and the matrix never published it.
     *
     * A run that names no scenario gets the base cap with **no bonus**. `scenarioKey()`
     * resolves null to the baseline so the strip has a descriptor to compose from, but a
     * bonus belongs to a scenario the Trainer chose, and lending URA Finale's +200 to a
     * run that never picked it would accept a number the tool has no source for. This is
     * the same refusal the panels already make on `hasScenario()` (D-220, D-221).
     *
     * @return array<string, int>
     */
    public static function scenarioCaps(?TrainingRun $run): array
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

    /**
     * Both ceiling levels a run's stats are measured against, as one object.
     *
     * `knownCap` is the ceiling the validators clamp against and the band has always drawn: base plus
     * that scenario's bonus, clamped to the hard cap. `potentialCap` is the level a stat could still
     * reach, which is the scenario's hard cap, and it is named per stat so a caller that cannot yet
     * substantiate one records the absence instead of lending the engine ceiling out. A run with no
     * scenario gets neither a bonus nor a potential: it claims no scenario's figures at all.
     */
    public static function ceilingsForRun(?TrainingRun $run): StatCeilings
    {
        $known = self::forRun($run);

        if ($run === null || ! $run->hasScenario()) {
            return new StatCeilings($known, array_fill_keys(array_keys($known), null));
        }

        return new StatCeilings($known, array_fill_keys(array_keys($known), self::hardCap()));
    }
}
