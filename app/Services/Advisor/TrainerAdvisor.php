<?php

declare(strict_types=1);

namespace App\Services\Advisor;

use App\Enums\EnergyState;
use App\Models\Advisor\BuildTargetPayload;
use App\Models\RaceCatalogSlot;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Services\Scenario\FinaleReader;
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

    /** Turns ahead of the finale within which the card is worth a line about it. */
    public const FINALE_TURNS_AHEAD = 5;

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

    public function advise(?TurnEntry $latest, ?BuildTargetPayload $target, ?TrainingRun $run = null): Advice
    {
        $energy = $latest?->energy;
        $deficits = $this->deficits($latest, $target);

        // A band is a named reading, not a number: the Trainer read a level off the client, so the
        // advisor reasons from the band and names it rather than declining for want of a figure.
        if ($latest?->energy_state === EnergyState::Band && $latest->energy_band !== null) {
            return $this->bandAdvice($latest, $target, $deficits, $latest->energy_band, $run);
        }

        [$band, $recommendedAction, $alternativeAction, $absence] = $this->rank($energy, $latest?->energy_state, $target, $deficits);

        $options = $this->options($latest, $target, $energy, $band, $deficits, $recommendedAction);

        return new Advice(
            $band,
            $this->byAction($options, $recommendedAction),
            $this->byAction($options, $alternativeAction),
            $options,
            $absence,
            $this->finaleContext($run),
        );
    }

    /**
     * Advice from a coarse Energy band.
     *
     * The client sometimes shows a level rather than a figure ("near a third"), which is recorded as
     * `band` with its own word. A low band is treated as below the advisory line and Rest is advised; a
     * mid or high band is at or above it, and the largest deficit is advised. The recommendation's reason
     * names the band it came from, so the advice rests on the state the Trainer recorded rather than on a
     * figure the tool does not hold.
     *
     * @param  array<string, int>  $deficits
     */
    private function bandAdvice(
        ?TurnEntry $latest,
        ?BuildTargetPayload $target,
        array $deficits,
        string $energyBand,
        ?TrainingRun $run,
    ): Advice {
        $atOrAbove = $energyBand !== 'low';
        $band = $atOrAbove ? self::AT_OR_ABOVE : self::BELOW;

        if (! $atOrAbove) {
            $action = self::REST;
            $alternative = $this->witHasDeficit($deficits) ? self::WIT : null;
            $source = "Energy is recorded as a {$energyBand} band, below the {$this->threshold()} advisory line; Rest is advised.";
        } else {
            $action = $target === null ? null : $this->largestDeficit($deficits);
            $alternative = ($action === null || $action === self::WIT)
                ? null
                : ($this->witHasDeficit($deficits) ? self::WIT : null);
            $source = $action === null
                ? "Energy is recorded as a {$energyBand} band; no stat is below target, so nothing is ranked."
                : "Energy is recorded as a {$energyBand} band; {$action} closes the largest deficit of ".($deficits[$action] ?? 0).'.';
        }

        $options = $this->options($latest, $target, null, $band, $deficits, $action, $source);

        return new Advice(
            $band,
            $this->byAction($options, $action),
            $this->byAction($options, $alternative),
            $options,
            null,
            $this->finaleContext($run),
        );
    }

    /**
     * The finale line for the recommendation card, or null when there is nothing to say.
     *
     * N = 5 turns. That is not a rule of the game and is not presented as one: it is the span the
     * screen needs to show a position the Trainer can still act on, counted in the turns this tool
     * has already seen a race take (enter it, see the result). Wider and the line is wallpaper on
     * every turn of Senior year; narrower and it arrives too late to mean anything. The number is a
     * display choice, and `PRD.md` FR-F-3's rule that the advisor states facts rather than counsel
     * is why it is not put in the reason list.
     *
     * `turns_away` counts inclusively from the turn being decided, so the turn the block arrives on
     * reads as 1 and not 0; it is null once the block is behind the run.
     *
     * @return array{label: string, state: string, turns_away: int|null}|null
     */
    private function finaleContext(?TrainingRun $run): ?array
    {
        if ($run === null) {
            return null;
        }

        $finale = FinaleReader::forRun($run);

        if ($finale === null) {
            return null;
        }

        // The block opens at the first instant after the 72-turn grid, so the distance is counted from
        // the grid's end rather than through a year and a turn: nextTurnToPlay() deliberately reports no
        // position once the grid is spent, and a run past it has passed the finale rather than lost its
        // place. One is added because the turn being decided counts as the first of the run's own.
        $away = RaceCatalogSlot::YEAR_SENIOR * TrainingRun::TURNS_PER_YEAR
            + 1
            - $run->nextTurnNumber();

        if ($away <= 0) {
            return $finale['outcome'] === null
                ? ['label' => $finale['label'], 'state' => 'passed', 'turns_away' => null]
                : null;
        }

        if ($away > self::FINALE_TURNS_AHEAD) {
            return null;
        }

        return [
            'label' => $finale['label'],
            'state' => $away === 1 ? 'next' : 'upcoming',
            'turns_away' => $away,
        ];
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
    private function rank(?int $energy, ?EnergyState $state, ?BuildTargetPayload $target, array $deficits): array
    {
        // 1. No Energy recorded. A band off a null would be a number invented from nothing, and a
        //    ranking against it would be worse. The absence is named instead, and the state is named
        //    with it: "not recorded" is a reading the Trainer took, not a blank the tool failed to fill.
        if ($energy === null) {
            return [null, null, null, $state === EnergyState::Unknown
                ? 'Energy is not recorded for this run, so there is no band to read. Each option below still states what it closes against your target.'
                : 'This run has recorded no Energy, so there is no band to read and nothing to rank against.'];
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
        ?string $recommendationSource = null,
    ): array {
        $options = [];

        foreach (self::ACTIONS as $action) {
            $reason = $this->reason($action, $target, $energy, $deficits, $action === $recommendedAction);

            // A recommendation carries the line the caller derived it from, even when the generic
            // reason would be null (a band has no figure to price a session against).
            if ($action === $recommendedAction && $recommendationSource !== null) {
                $reason = $recommendationSource;
            }

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
     * How far each stat sits below the Trainer's own target, clamped at zero.
     *
     * Public because a second surface states the same number: the Career Result screen prints the
     * final deficit per stat, and `ADR-0015`'s rule — where two surfaces read a number, both call
     * the same owner — is cheaper honoured than restated. An empty array means the question has no
     * answer (no turn recorded, or no target entered), which is what makes the caller render `N/A`
     * rather than a deficit against a target nobody set.
     *
     * @return array<string, int>
     */
    public function deficits(?TurnEntry $latest, ?BuildTargetPayload $target): array
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
