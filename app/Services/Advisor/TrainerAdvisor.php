<?php

declare(strict_types=1);

namespace App\Services\Advisor;

use App\Models\Advisor\BuildTargetPayload;
use App\Models\TurnEntry;
use InvalidArgumentException;

/**
 * Ranks the next turn's options against the Trainer's own build target (FR-F-2/3, `ADR-0020` §2).
 *
 * Pure over three inputs — the latest `TurnEntry`, the run's `BuildTargetPayload` and
 * `config('advisor')` — with no database writes and no game-state guessing. Everything it says is
 * arithmetic on numbers the Trainer entered plus constants whose source and date are in the config
 * beside them, which is what lets a surface print the working (`ADR-0001` §2, §4).
 *
 * What is deliberately absent is as much the design as what is here. There is no score, no numeric
 * confidence, and no per-training yield: all three are unsourced (`ADR-0001` §3, `ADR-0020` §2), and
 * the return type has no field for them to arrive in. There is no third band, because 50 is the only
 * Energy line any source states and the "dangerous below 30" figure is in none of them (`ADR-0001`
 * §3). Mood multipliers are recorded in `ADR-0001` and not read here, because they only matter to
 * yield prediction, which this does not do.
 *
 * The ranking rules fire in order and the first one to fire decides; a later rule does not run. The
 * order is the contract, and the four cases are: no Energy recorded, below the advisory line, at or
 * above the line with no target, at or above the line with a target.
 */
final class TrainerAdvisor
{
    /** The six options, in the order a surface lists them. */
    public const ACTIONS = ['Speed', 'Stamina', 'Power', 'Guts', 'Wit', 'Rest'];

    public const AT_OR_ABOVE = 'AtOrAboveAdvisory';

    public const BELOW = 'BelowAdvisory';

    /**
     * `Wit`'s band. Not a third state of the same scale: `Wit` always enters at full Energy, so
     * giving it a band would assert an exemption the sources leave unsettled (`ADR-0001` §3).
     */
    public const RISK_NOT_MEASURED = 'RiskNotMeasured';

    /**
     * `Rest` is public because it is already the advisor's published vocabulary — `ACTIONS` lists it —
     * and a surface that maps the advisor's answer onto its own controls has to name it rather than
     * repeat the string. `Wit` stays private: no surface needs to name it.
     */
    public const REST = 'Rest';

    private const WIT = 'Wit';

    /**
     * The constant set this engine reads, with the source and date of each entry.
     *
     * Exposed so a surface can print the date behind a suggestion without a second lookup
     * (`ADR-0001` §7), and so the engine's inputs are inspectable without reading this class.
     *
     * @return array<string, array{value: mixed, source: string, verified_at: string, confidence: string}>
     */
    public function constants(): array
    {
        /** @var array<string, array{value: mixed, source: string, verified_at: string, confidence: string}> $constants */
        $constants = config('advisor.constants');

        return $constants;
    }

    public function advise(?TurnEntry $latest, ?BuildTargetPayload $target): Advice
    {
        $energy = $latest?->energy;
        $deficits = $this->deficits($latest, $target);

        [$band, $recommendedAction, $alternativeAction, $absence] = $this->rank($energy, $target, $deficits);

        $options = $this->options($latest, $target, $energy, $band, $deficits, $recommendedAction);

        return new Advice(
            $band,
            $this->byAction($options, $recommendedAction),
            $this->byAction($options, $alternativeAction),
            $options,
            $absence,
        );
    }

    /**
     * Which rule fires, and what it decides.
     *
     * Returned as a tuple rather than acted on in place so the options are built knowing which one
     * is the recommendation — the reason line for the winner says so, and a reason assembled before
     * the ranking could not.
     *
     * @param  array<string, int>  $deficits
     * @return array{0: string|null, 1: string|null, 2: string|null, 3: string|null}
     */
    private function rank(?int $energy, ?BuildTargetPayload $target, array $deficits): array
    {
        // 1. No Energy recorded. A band off a null would be a number invented from nothing, and a
        //    ranking against it would be worse. The absence is named instead.
        if ($energy === null) {
            return [null, null, null, 'This run has recorded no Energy, so there is no band to read and nothing to rank against.'];
        }

        $band = $energy >= $this->threshold() ? self::AT_OR_ABOVE : self::BELOW;

        // 2. Below the advisory line: Rest, and Wit as the alternative when it has something to close.
        if ($band === self::BELOW) {
            return [$band, self::REST, $this->witHasDeficit($deficits) ? self::WIT : null, null];
        }

        // 3. At or above the line with no target: ADR-0001's original Energy-only scope. Ranking
        //    against a target that was never entered would be the tool inventing one.
        if ($target === null) {
            return [$band, null, null, 'No build target is set for this run, so there is no target to rank the options against.'];
        }

        // 4. At or above the line with a target: the largest deficit. Ties go to the stat matrix's
        //    own order, so the winner is a property of the matrix and not of this loop.
        $largest = $this->largestDeficit($deficits);

        if ($largest === null) {
            return [$band, null, null, 'Every stat is already at or above its target, so there is no deficit to close.'];
        }

        return [$band, $largest, $largest === self::WIT ? null : ($this->witHasDeficit($deficits) ? self::WIT : null), null];
    }

    /**
     * @param  array<string, int>  $deficits
     * @return list<AdvisorOption>
     */
    private function options(
        ?TurnEntry $latest,
        ?BuildTargetPayload $target,
        ?int $energy,
        ?string $band,
        array $deficits,
        ?string $recommendedAction,
    ): array {
        $options = [];

        foreach (self::ACTIONS as $action) {
            $reason = $this->reason($action, $target, $energy, $deficits, $action === $recommendedAction);

            // ADR-0001 §4: a suggestion with no derivable reason does not ship. A training action on
            // a run with neither Energy nor a target has nothing behind it, so it is left out rather
            // than offered with a line that says nothing.
            if ($reason === null) {
                continue;
            }

            $options[] = new AdvisorOption(
                action: $action,
                deficitClosed: $deficits[$action] ?? 0,
                energyAfter: $energy === null ? null : $this->energyAfter($action, $energy),
                band: $action === self::WIT ? self::RISK_NOT_MEASURED : $band,
                reason: $reason,
            );
        }

        return $options;
    }

    /**
     * @param  array<string, int>  $deficits
     */
    private function reason(
        string $action,
        ?BuildTargetPayload $target,
        ?int $energy,
        array $deficits,
        bool $isRecommendation,
    ): ?string {
        if ($action === self::REST) {
            $recovery = $this->restRecovery();

            if ($energy === null) {
                return "Rest recovers +{$recovery} Energy, but this run has recorded none to recover.";
            }

            return $energy < $this->threshold()
                ? "Energy {$energy} is below the {$this->threshold()} advisory line; Rest recovers +{$recovery}."
                : "Rest recovers +{$recovery} Energy and you are at {$energy}.";
        }

        if ($action === self::WIT) {
            $cost = $this->witCost();

            return $energy === null
                ? "Wit costs {$cost} Energy, so it needs no Energy reading to be affordable."
                : "Wit costs {$cost} Energy and you are at {$energy}.";
        }

        $deficit = $deficits[$action] ?? 0;

        if ($target !== null && $deficit > 0) {
            $suffix = $isRecommendation ? ' (the largest deficit)' : '';

            return "{$action} is {$deficit} below target{$suffix}.";
        }

        if ($energy === null) {
            return null; // nothing to derive: no Energy to spend and no target to close
        }

        [$low, $high] = $this->sessionCost();

        return "A {$action} session costs {$low} to {$high} Energy and you are at {$energy}.";
    }

    /**
     * @return array{min: int, max: int}
     */
    private function energyAfter(string $action, int $energy): array
    {
        if ($action === self::REST) {
            return $this->single(min(100, $energy + $this->restRecovery()));
        }

        if ($action === self::WIT) {
            return $this->single(min(100, $energy + $this->witCost()));
        }

        [$low, $high] = $this->sessionCost();

        // The range inverts: the cheapest session leaves the most Energy, so the low cost pairs with
        // the high reading. Clamped at zero because Energy cannot go below it and a negative would
        // read as a debt.
        return ['min' => max(0, $energy - $high), 'max' => max(0, $energy - $low)];
    }

    /**
     * @return array{min: int, max: int}
     */
    private function single(int $value): array
    {
        return ['min' => $value, 'max' => $value];
    }

    /**
     * @return array<string, int>
     */
    private function deficits(?TurnEntry $latest, ?BuildTargetPayload $target): array
    {
        if ($latest === null || $target === null) {
            return [];
        }

        $deficits = [];

        foreach ($this->statOrder() as $stat) {
            $deficits[$stat] = max(0, $target->targets[$stat] - $this->current($latest, $stat));
        }

        return $deficits;
    }

    /**
     * The stat with the largest deficit, or null when no stat is short.
     *
     * Strictly greater, so a tie is won by whichever stat comes first in the matrix rather than by
     * the order this loop happened to visit them.
     *
     * @param  array<string, int>  $deficits
     */
    private function largestDeficit(array $deficits): ?string
    {
        $best = null;
        $bestDeficit = 0;

        foreach ($this->statOrder() as $stat) {
            $deficit = $deficits[$stat] ?? 0;

            if ($deficit > $bestDeficit) {
                $bestDeficit = $deficit;
                $best = $stat;
            }
        }

        return $best;
    }

    /**
     * @param  array<string, int>  $deficits
     */
    private function witHasDeficit(array $deficits): bool
    {
        return ($deficits[self::WIT] ?? 0) > 0;
    }

    /**
     * @param  list<AdvisorOption>  $options
     */
    private function byAction(array $options, ?string $action): ?AdvisorOption
    {
        if ($action === null) {
            return null;
        }

        foreach ($options as $option) {
            if ($option->action === $action) {
                return $option;
            }
        }

        return null;
    }

    private function current(TurnEntry $latest, string $stat): int
    {
        return match ($stat) {
            'Speed' => (int) $latest->speed,
            'Stamina' => (int) $latest->stamina,
            'Power' => (int) $latest->power,
            'Guts' => (int) $latest->guts,
            'Wit' => (int) $latest->wit,
            default => throw new InvalidArgumentException("Unknown stat [{$stat}]."),
        };
    }

    /**
     * @return list<string>
     */
    private function statOrder(): array
    {
        /** @var list<string> $order */
        $order = config('scenarios.stat_order');

        return $order;
    }

    private function threshold(): int
    {
        return (int) $this->constants()['advisory_threshold']['value'];
    }

    private function restRecovery(): int
    {
        return (int) $this->constants()['rest_recovery']['value'];
    }

    private function witCost(): int
    {
        return (int) $this->constants()['wit_cost']['value'];
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function sessionCost(): array
    {
        /** @var array{0: int, 1: int} $cost */
        $cost = $this->constants()['session_cost']['value'];

        return $cost;
    }
}
