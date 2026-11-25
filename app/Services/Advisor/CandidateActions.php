<?php

declare(strict_types=1);

namespace App\Services\Advisor;

use App\Models\TrainingRun;
use App\Models\TurnEntry;

/**
 * One action the engine is willing to rank, with the reason it is rankable, and the actions it
 * refuses and why (Advisor AI slice, Phase B).
 *
 * **The eligible set is the engine's ranking, not its derivable list.** `Advice->options` holds
 * every option a reason line could be derived for — the engine's own docblock calls it "every option
 * that could be derived, ranked or not". `recommendation` and `alternative` are what it actually
 * ranked. Offering all of `options` to a provider lets it pick a training turn the engine had
 * already ruled against, which is a model overriding a deterministic judgement rather than
 * supplementing one.
 *
 * Measured 2026-11-22, and this is the case that forced the distinction: a career at Energy 42, below
 * the 50 advisory line. The engine recommended Rest and emitted all six options with their deficit
 * reasons. Clef was offered all six, chose Speed, and the response validated — because Speed really
 * was in the set it had been given. The action was legal in the game and against the advisor's rule
 * at the same time, and only one of those is this tool's business.
 *
 * **So the rule is:** when the engine has a recommendation, the provider chooses between that and
 * its named alternative. When the engine has none, every derivable option is offered, because
 * ranking them is precisely the question it declined to answer.
 *
 * **A provider is offered only the `eligible` list.** Refused actions travel as `blocked` so a
 * surface can explain what the engine ruled out and why, but they are never selectable.
 */
final readonly class CandidateActions
{
    /**
     * @param  list<array{id: string, label: string, kind: string, reason: string, evidence: list<string>, deficit: int}>  $eligible
     * @param  list<array{id: string, label: string, blocker: string}>  $blocked
     */
    public function __construct(
        public array $eligible,
        public array $blocked,
        public ?string $obligation,
    ) {}

    /**
     * Builds the candidate set for one career.
     *
     * `$advice` must be the same `Advice` the deterministic rail is about to render. Passing a
     * recomputed one would let the AI layer rank a different turn than the one on screen, which is
     * the exact divergence `NextDecision` exists to avoid.
     *
     * `$obligation` is a mandatory objective the calendar says is due, taken from
     * `CockpitController::objectiveSection()`'s payload rather than re-read here: that controller
     * owns the `race_catalog_slots` read (`ADR-0015`), and a second reader would be a second answer.
     *
     * @param  list<array<string, mixed>>  $objectives
     */
    public static function from(Advice $advice, TrainingRun $run, ?TurnEntry $latest, array $objectives = []): self
    {
        $byLabel = [];

        foreach ($advice->options as $option) {
            $byLabel[$option->action] = $option;
        }

        // The engine ranked something: the decision is between its answer and its own alternative.
        // Everything else it derived stays visible as blocked, naming the rule that withheld it.
        $ranked = null;

        if ($advice->recommendation !== null) {
            $ranked = [$advice->recommendation->action];

            if ($advice->alternative !== null && $advice->alternative->action !== $advice->recommendation->action) {
                $ranked[] = $advice->alternative->action;
            }
        }

        $eligible = [];
        $blocked = [];

        if ($ranked === null) {
            foreach ($advice->options as $option) {
                $eligible[] = self::entry($option);
            }
        } else {
            // `$advice->recommendation` is non-null inside this branch by construction, and the band
            // is read from the advice rather than the option.
            $engineChoice = $advice->recommendation->action;

            foreach ($advice->options as $option) {
                if (in_array($option->action, $ranked, true)) {
                    $eligible[] = self::entry($option);

                    continue;
                }

                $blocked[] = [
                    'id' => self::id($option->action),
                    'label' => $option->action,
                    'blocker' => $advice->band === TrainerAdvisor::BELOW
                        ? "the engine advised {$engineChoice} at this Energy reading and did not rank this action"
                        : "the engine ranked {$engineChoice} ahead of this action",
                ];
            }
        }

        // A training action the engine omitted entirely is omitted because it had no derivable
        // reason, not because the game forbids it. Naming that distinction keeps a surface honest:
        // "not ranked" and "not available" are different statements (`ADR-0001` §4).
        foreach (TrainerAdvisor::ACTIONS as $action) {
            if (isset($byLabel[$action])) {
                continue;
            }

            $blocked[] = [
                'id' => self::id($action),
                'label' => $action,
                'blocker' => 'no reason line could be derived: this run records neither an Energy reading '
                    .'nor a target for this stat',
            ];
        }

        $obligation = null;

        foreach ($objectives as $objective) {
            if (($objective['state'] ?? null) === 'current') {
                $label = (string) ($objective['label'] ?? 'an objective');
                $obligation = "{$label} is due and not recorded.";

                break;
            }
        }

        return new self($eligible, $blocked, $obligation);
    }

    /**
     * @return array{id: string, label: string, kind: string, reason: string, evidence: list<string>, deficit: int}
     */
    private static function entry(AdvisorOption $option): array
    {
        return [
            'id' => self::id($option->action),
            'label' => $option->action,
            'kind' => $option->action === TrainerAdvisor::REST ? 'recovery' : 'training',
            'reason' => $option->reason,
            'evidence' => [],
            'deficit' => $option->deficitClosed,
        ];
    }

    /**
     * A stable option id a provider can echo back and this application can recognise.
     *
     * Lowercased and underscore-joined so it survives a JSON round trip unchanged; the engine's own
     * labels stay the human-readable `label`. Deriving the id rather than accepting one from the
     * response is what makes "the model named an option we never offered" detectable.
     */
    public static function id(string $action): string
    {
        return strtolower(str_replace(' ', '_', $action));
    }

    /**
     * The ids a provider may choose from, plus the abstention.
     *
     * `insufficient_evidence` is always present. Without it a model asked to choose between two
     * options it cannot distinguish will still pick one, and the abstention is the honest answer the
     * Advisor renders as "nothing further can be said from what is recorded".
     *
     * @return list<string>
     */
    public function selectableIds(): array
    {
        return array_merge(array_column($this->eligible, 'id'), [self::ABSTAIN]);
    }

    public const ABSTAIN = 'insufficient_evidence';

    /**
     * Whether an id the provider returned is one this turn actually offered.
     *
     * Deliberately a membership test against the built set rather than a format check: an id of the
     * right shape that names an action the engine refused is still invalid, and that is the case
     * that matters.
     */
    public function allows(string $id): bool
    {
        return in_array($id, $this->selectableIds(), true);
    }

    /**
     * The provider-facing criteria map: id to a description assembled from the engine's own reasons.
     *
     * Every description reuses the engine's sentence rather than a new summary, so the model chooses
     * between the same statements the Trainer can read on screen. A description written fresh here
     * would be a second opinion on the same option.
     *
     * @return array<string, string>
     */
    public function criteria(): array
    {
        $criteria = [];

        foreach ($this->eligible as $candidate) {
            $criteria[$candidate['id']] = $candidate['reason'];
        }

        $criteria[self::ABSTAIN] = 'No defensible preference can be established from the recorded state.';

        return $criteria;
    }

    /**
     * @return array<string, mixed>
     */
    public function forProvider(): array
    {
        return [
            'eligible' => array_map(
                static fn (array $c): array => [
                    'id' => $c['id'],
                    'label' => $c['label'],
                    'kind' => $c['kind'],
                    'reason' => $c['reason'],
                ],
                $this->eligible,
            ),
            'obligation' => $this->obligation,
        ];
    }
}
