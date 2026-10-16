<?php

declare(strict_types=1);

namespace App\Services\Advisor;

use App\Enums\TurnChoice;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Services\ScenarioCaps;

/**
 * FD-2, the shared Next Decision contract (plan §9a, `screen-spec-2.0.md` §12 and §30).
 *
 * **It wraps `TrainerAdvisor`, it does not replace it.** Every ranking rule, every deficit and every
 * reason sentence is the engine's own and arrives in the `Advice` this method is handed; nothing here
 * re-ranks, and `TrainerAdvisor` is not edited. That is the smaller diff and the reason the engine's
 * twelve tests stay standing. A composable inside the component was rejected: FD-2 requires the logic
 * in a service, and the payload must be reachable by `assertInertia` like every other Cockpit section.
 *
 * **It reads no scenario key and no scenario label.** Of the seven tiers `screen-spec-2.0.md:1574-1582`
 * lists, only two are evaluable from state this tree records today: the advisory Energy line (tier 1) and
 * the deficit against the Trainer's own target (tier 4). The rest belong to objectives FD-3 brings later.
 * So the same eight keys come out of every scenario and a fifth one needs no edit here (G-33, `D-240`).
 *
 * **No score, no confidence, no band.** `ADR-0001` §3 leaves a score and a numeric confidence unsourced
 * and `ADR-0020` §2 says no figure may read as more certain than its provenance, so the shape has no
 * field for them. `Advice->band` stays inside the engine: on the wire, Energy is either recorded — as a
 * figure or as the Trainer's own band word — and the engine ranked, or it is absent and this refuses.
 *
 * **Two refusal copies, never one.** `design-2.0.md:234-237` separates "the engine could evaluate but
 * found none worth recommending" from "the rules or data to rank actions are incomplete". Which one
 * fired is decided from the run's own state — whether Energy and a `build_target` exist — not by
 * matching the engine's prose.
 *
 * **Two of the four provenance states are legal here.** Ruling 1c (`PLANS-AND-BRIEFS.md`
 * §"provenance-and-absence-contracts.md" §9) scopes the advisor axis to the relationship between a figure and its inputs: `confirmed` for a
 * declared constant printed as read, `calculated` for this tool's arithmetic over readings. `estimated`
 * on this axis is the opt-in modelled figure `ADR-0001` §3 describes, which is built nowhere, and
 * `unknown` beside an absence was retired by owner ruling 2a.
 */
final class NextDecision
{
    private const NOT_ENOUGH = 'Not enough information';

    private const NO_SUITABLE = 'No suitable action found';

    private const WIT = 'Wit';

    public function __construct(private readonly TrainerAdvisor $advisor) {}

    /**
     * FD-4's snapshot: the decision as it stood at the moment a turn was taken (ruling D-1a).
     *
     * `$latest` is the row the write compares against, which is the row the rail was reading when it made
     * the recommendation the Trainer acted on. Passing it in rather than deriving it here keeps one owner
     * for "the previous turn" (`TrainingRunController::previousTurn()`), the rule that already handles a
     * turn inserted in the middle of a run.
     *
     * The stored payload carries `objectives` as an empty list: the calendar read belongs to
     * `CockpitController::objectiveSection()` and this service neither queries nor re-ranks it, so a
     * snapshot written from the turn-save path would have to reach into another controller's read. The
     * recorded-action card renders priority, recommendation and reasons from a snapshot and never its
     * objective list, so nothing on screen claims the run had no objectives.
     *
     * @return array<string, mixed> `build()`'s shape, with `recorded_action` nulled.
     */
    public function snapshotFor(?TurnEntry $latest, TrainingRun $run): array
    {
        return $this->build($this->advisor->advise($latest, $run->buildTarget(), $run), $run, [], false);
    }

    /**
     * The FD-2 payload, extended by FD-3's `objectives[]` (plan §9a, continuation prompt §4.1-§4.2).
     *
     * `$objectives` arrives already ordered by the controller, which owns the calendar read
     * (`CockpitController::objectiveSection()`); this service neither queries nor re-ranks it, so the one
     * read of `race_catalog_slots` stays with the one reader (`ADR-0015`).
     *
     * @param  list<array<string, mixed>>  $objectives
     * @return array{
     *     priority: string,
     *     recommended: array{action: string, action_url: string, benefit: list<string>}|null,
     *     alternatives: list<array{action: string, action_url: string, tradeoff: string}>,
     *     why: list<array{text: string, badge: string, title: string}>,
     *     switch_if: list<string>,
     *     refusal: array{reason: string, missing: list<string>}|null,
     *     objectives: list<array<string, mixed>>,
     *     recorded_action: array{
     *         id: int,
     *         decision: array<string, mixed>,
     *         action: string,
     *         choice: string,
     *         expected: list<array{text: string, badge: string, title: string}>,
     *         actual: list<array{text: string, badge: string, title: string}>|null,
     *         reconciliation: array{match: bool, note: string}|null,
     *         recorded_at: string|null,
     *         corrected_from: int|null,
     *     }|null,
     * }
     */
    public function build(Advice $advice, TrainingRun $run, array $objectives = [], bool $withRecordedAction = true): array
    {
        $refusal = $this->refusal($advice, $run);
        $ranked = $refusal === null;

        return [
            'priority' => $this->priority($advice, $refusal, $objectives),
            'recommended' => $ranked ? $this->recommended($advice, $run) : null,
            'alternatives' => $ranked ? $this->alternatives($advice, $run) : [],
            'why' => $this->why($advice, $run),
            'switch_if' => $ranked ? $this->switchIf($advice) : [],
            'refusal' => $refusal,
            'objectives' => $objectives,
            // A snapshot that embeds the previous turn's snapshot embeds that turn's snapshot too, so the
            // JSON column would grow with every turn the run takes. The key stays, the nesting does not.
            'recorded_action' => $withRecordedAction ? $this->recordedAction($run) : null,
        ];
    }

    /**
     * FD-4's reconciliation slot: the action the Trainer last took, what the rail said before it, and what
     * the turn actually gave. Null when the run has no turn that carries an action.
     *
     * **A row written before this slice is not rendered, not guessed at.** `choice` and
     * `decision_snapshot` are both new columns, so every older row has nulls in them; inventing a forecast
     * for a turn the tool never advised on would put a sentence in the rail that no screen ever showed the
     * Trainer (`AGENTS.md` §5, no fact without provenance).
     *
     * **`decision` is the stored snapshot, never a recompute.** It is read out of the row rather than built
     * from the run's present, which is what lets a corrected or long-settled turn keep showing what was
     * advised at the time. `expected` is that snapshot's own benefit lines and `actual` is the Trainer's
     * entered preview row; the reconciliation compares the *action taken* to the *action recommended*,
     * because those are the two facts on the row. It does not compare gains: the forecast names a deficit
     * to close, not a figure to hit, and no source in this corpus publishes what a training turn pays.
     *
     * @return array{
     *     id: int,
     *     decision: array<string, mixed>,
     *     action: string,
     *     choice: string,
     *     expected: list<array{text: string, badge: string, title: string}>,
     *     actual: list<array{text: string, badge: string, title: string}>|null,
     *     reconciliation: array{match: bool, note: string}|null,
     *     recorded_forecast: array<string, mixed>,
     *     recorded_at: string|null,
     *     corrected_from: int|null,
     * }|null
     */
    private function recordedAction(TrainingRun $run): ?array
    {
        $entry = $run->turnEntries
            ->filter(
                static fn (TurnEntry $entry): bool => $entry->superseded_by === null
                    && $entry->choice !== null
                    && is_array($entry->decision_snapshot),
            )
            ->sortBy('turn')
            ->last();

        if ($entry === null) {
            return null;
        }

        $choice = TurnChoice::tryFrom((string) $entry->choice);

        if ($choice === null) {
            return null;
        }

        /** @var array<string, mixed> $snapshot */
        $snapshot = (array) $entry->decision_snapshot;
        $recommended = is_array($snapshot['recommended'] ?? null) ? $snapshot['recommended'] : null;
        $actionTaken = $recommended === null ? null : ($recommended['action'] ?? null);

        $expected = [];

        foreach ((array) ($recommended['benefit'] ?? []) as $line) {
            $expected[] = [
                'text' => (string) $line,
                'badge' => 'calculated',
                'title' => 'This rail\'s own reason line, read off the decision stored when this turn was recorded.',
            ];
        }

        $actual = [];

        foreach ($entry->previewGainRows() ?? [] as $row) {
            $actual[] = [
                'text' => $row['label'].' '.($row['value'] >= 0 ? '+' : '').$row['value'],
                'badge' => 'confirmed',
                'title' => 'Entered by the Trainer from the client when this turn was recorded.',
            ];
        }

        return [
            'id' => $entry->id,
            'decision' => $snapshot,
            'action' => $choice->label(),
            'choice' => $choice->value,
            'expected' => $expected,
            'actual' => $actual === [] ? null : $actual,
            'reconciliation' => $actual === [] ? null : [
                'match' => $actionTaken !== null && $choice->label() === $actionTaken,
                'note' => match (true) {
                    $actionTaken === null => 'The rail was refusing to rank actions when this turn was taken, '
                        .'so there was no recommendation for it to agree with.',
                    $choice->label() === $actionTaken => 'The action recorded is the one this rail recommended '
                        .'at the time.',
                    default => 'The action recorded is not the one this rail recommended at the time. Both words '
                        .'are readings this tool kept, and nothing here says which was the better turn.',
                },
            ],
            // The citation the ruling asked for: what the reference says a turn of the recorded action does,
            // not what this tool computes. It is labelled on the screen as "Expected (from sources)", and the
            // distinction is the whole point — this section quotes published figures with their server and
            // their disagreement, so nothing here is a prediction of the turn the Trainer is about to take.
            //
            // Provenance, carried from the paragraph `TrainingRunController::turnChoices()` used to hold
            // before its unread detail lines were deleted. The published rest and outing values are
            // probabilities and §2.2 keeps those out of application code. The rest tier is the worse case,
            // because this repository's own two sources disagree on it: `docs/UMAMUSUME_REFERENCE.md` §1.1.5
            // carries "+30 Energy per standard rest" against GameWith 2026-09-25, while §2.1 of
            // `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` measures the modal rest at +50 with
            // +30 as the under-slept failure tier, and a post-rework Global table agrees 50 is the common
            // case. A single Trainer-facing number picked out of that would be a guess wearing the tool's
            // voice, so every branch renders and none is presented as the refill. Recreation's lift is
            // published without a magnitude, and the section says so rather than supplying one. A discipline
            // renders a named absence, because no source publishes a per-training yield and `ADR-0001` §3
            // keeps it that way: the panel does not become the yield table the advisor spec refused to build.
            'recorded_forecast' => match ($choice) {
                TurnChoice::Rest => [
                    'label' => 'Rest',
                    'kind' => 'reference',
                    'branches' => [
                        ['name' => 'Success', 'value' => '+50', 'note' => 'modal'],
                        ['name' => 'Big success', 'value' => '+70', 'note' => null],
                        ['name' => 'Poor Rest', 'value' => '+30', 'note' => 'may carry a stayed-up-late penalty'],
                    ],
                    'server' => '[JP]',
                    'source' => 'UMAMUSUME_REFERENCE.md §1.1.5 (GameWith n=3833, Kamigame n=100)',
                    'disagreement' => 'Sources agree on the shape; the modal value is single-source.',
                ],
                TurnChoice::Recreation => [
                    'label' => 'Recreation',
                    'kind' => 'reference',
                    'statement' => 'Lifts Mood; no magnitude published in the reference.',
                    'server' => '[JP]',
                    'source' => 'UMAMUSUME_REFERENCE.md §1.1.6',
                ],
                default => [
                    'label' => $choice->label(),
                    'kind' => 'absence',
                    'reason' => 'No source publishes a per-training yield for a discipline (ADR-0001 §3).',
                ],
            },
            'recorded_at' => $entry->created_at?->toIso8601String(),
            'corrected_from' => $entry->corrected_from,
        ];
    }

    /**
     * Which rule declined, in the copy that rule deserves.
     *
     * @return array{reason: string, missing: list<string>}|null
     */
    private function refusal(Advice $advice, TrainingRun $run): ?array
    {
        if ($advice->recommendation !== null) {
            return null;
        }

        $sentence = $advice->absence ?? 'The advisor ranked the options and returned nothing.';

        // No band at all is the engine's rule 1: this turn carries no Energy reading.
        if ($advice->band === null) {
            return ['reason' => self::NOT_ENOUGH.': '.$sentence, 'missing' => ['Energy']];
        }

        if ($run->buildTarget() === null) {
            return ['reason' => self::NOT_ENOUGH.': '.$sentence, 'missing' => ['build target']];
        }

        // Energy read, target entered, all five deficits evaluated, nothing short: the ranking ran.
        return ['reason' => self::NO_SUITABLE.': '.$sentence, 'missing' => []];
    }

    /**
     * @param  array{reason: string, missing: list<string>}|null  $refusal
     * @param  list<array<string, mixed>>  $objectives
     */
    private function priority(Advice $advice, ?array $refusal, array $objectives): string
    {
        // FD-3 first, and deliberately ahead of the refusal below: an obligation that has arrived and
        // carries no record is actionable whatever the advisor cannot rank. `screen-spec-2.0.md:1574-1582`
        // puts mandatory objectives at tier 2 and build targets at tier 4, so a missing target or a missing
        // Energy reading suppresses the *session* recommendation in `refusal` without cancelling the race
        // the calendar says is due. The copy is E2's own, "due and not recorded", which is exactly what the
        // state asserts and no more.
        $current = collect($objectives)->first(static fn (array $row): bool => $row['state'] === 'current');

        if ($current !== null) {
            return $current['label'].' is due and not recorded.';
        }

        if ($refusal !== null) {
            return match (true) {
                $refusal['missing'] === ['Energy'] => 'Nothing can be prioritised until this turn\'s Energy is recorded.',
                $refusal['missing'] === ['build target'] => 'No priority can be stated until a build target is entered.',
                default => 'Every stat has reached its target, so no stat is prioritised.',
            };
        }

        return $advice->band === TrainerAdvisor::BELOW
            ? 'Recover Energy before the next session.'
            : 'Close the largest deficit against the build target you entered.';
    }

    /**
     * @return array{action: string, action_url: string, benefit: list<string>, team_value: list<array<string, mixed>>|null}
     */
    private function recommended(Advice $advice, TrainingRun $run): array
    {
        /** @var AdvisorOption $option */
        $option = $advice->recommendation;

        return [
            'action' => $option->action,
            'action_url' => $this->actionUrl($run, $option->action),
            'benefit' => array_values(array_filter([
                $option->reason,
                $this->energyAfterLine($option->energyAfter),
            ])),
            // Always carried, null when it does not fire: one shape for every scenario rather than a
            // consumer that has to test for the key's existence.
            'team_value' => $this->teamValue($advice, $run),
        ];
    }

    /**
     * G4's dual-value readout: what the advised session is worth to the trainee, and to the team.
     *
     * Unity Cup's training turn pays twice — it raises a stat and it counts as a Unity Training toward
     * the team rank — and the rail printed only the first half. Two figures, each traced, and no
     * prediction: the trainee side is the room left under the cap `ScenarioCaps` owns, read through the
     * same call the stat bars make so the rail and the strip cannot disagree (`ADR-0015`); the team side
     * is the rank letter this run last reported, mapped by `TrainingRun::facilityLevel()`, which is the
     * existing owner of that lookup. Neither is a forecast of what the session will gain.
     *
     * **The firing predicate is the composition matrix, not a capability object.** No capability object
     * exists in this namespace, and `latestTeamRank()` already refuses to answer unless
     * `team_rank_ladder` is on (`TrainingRun.php:1176-1180`), so the rail opens on the same gate the
     * model uses and a fifth scenario needs a config entry rather than a branch here (gate G-33). A
     * non-training action raises no stat and has no trainee side to pair with, so Rest and Recreation
     * read null rather than half a pair.
     *
     * **Badges are the two states this axis allows, and an empty row carries none.**
     * `resources/js/types.ts:60-66` records the ruling: `estimated` would claim a model nothing builds,
     * and `unknown` beside an absence was retired here by owner ruling 2a. A badge qualifies a displayed
     * value, so with nothing displayed the row states its reason alone.
     *
     * @return list<array{label: string, value: string|null, badge: string|null, title: string}>|null
     */
    private function teamValue(Advice $advice, TrainingRun $run): ?array
    {
        $option = $advice->recommendation;

        if (! $option instanceof AdvisorOption || ! $run->composesPanel('team_rank_ladder')) {
            return null;
        }

        /** @var list<string> $stats */
        $stats = (array) config('scenarios.stat_order');

        if (! in_array($option->action, $stats, true)) {
            return null;
        }

        /** @var TurnEntry|null $latest */
        $latest = $run->turnEntries->sortByDesc('turn')->first();
        /** @var array<string, int> $caps */
        $caps = ScenarioCaps::forRun($run);
        $cap = $caps[$option->action] ?? null;
        $stat = strtolower($option->action);
        $reading = $latest instanceof TurnEntry ? $latest->getAttribute($stat) : null;

        $trainee = [
            'label' => 'Trainee value',
            'value' => null,
            'badge' => null,
            'title' => 'No turn entry for this run records a '.$option->action.' reading, or no cap is '
                .'published for it, so there is no headroom to state.',
        ];

        if (is_numeric($reading) && $cap !== null) {
            $trainee = [
                'label' => 'Trainee value',
                'value' => number_format(max(0, (int) $cap - (int) $reading))
                    .' below the '.$option->action.' cap of '.number_format($cap),
                'badge' => 'calculated',
                'title' => 'The cap is read through ScenarioCaps::forRun(), the same call the header\'s '
                    .'stat bars make (ADR-0015), less the '.$option->action.' figure of this run\'s latest '
                    .'turn entry. Both inputs are recorded or published figures; this is room under a cap, '
                    .'not a gain the session is promised.',
            ];
        }

        $rank = $run->latestTeamRank()?->rank;
        $level = $run->facilityLevel($rank);

        $team = [
            'label' => 'Team value',
            'value' => null,
            'badge' => null,
            'title' => 'This run has no rank reported, so the team side of the session has no figure to '
                .'print. A rank arrives on a turn event payload, entered by the Trainer.',
        ];

        if ($rank !== null) {
            // `S+` sits above the top rung and grants no higher facility, which is what the config notes
            // say, so it is named without a level rather than handed one it does not have.
            $team = [
                'label' => 'Team value',
                'value' => $level === null
                    ? 'Team rank '.$rank.', above the top rung of the ladder'
                    : 'Team rank '.$rank.', facility level '.$level,
                'badge' => 'calculated',
                'title' => 'The letter is the one this run last reported. The level maps it through '
                    .'config/scenarios.php team_rank_ladder by TrainingRun::facilityLevel(), which does the '
                    .'lookup rather than storing a second copy of it.',
            ];
        }

        return [$trainee, $team];
    }

    /**
     * @return list<array{action: string, action_url: string, tradeoff: string}>
     */
    private function alternatives(Advice $advice, TrainingRun $run): array
    {
        $alternative = $advice->alternative;

        if ($alternative === null) {
            return [];
        }

        // The engine's own line for the alternative is the trade-off. A second sentence written here
        // would be a second opinion on the same option.
        return [[
            'action' => $alternative->action,
            'action_url' => $this->actionUrl($run, $alternative->action),
            'tradeoff' => $alternative->reason,
        ]];
    }

    /**
     * The provenance ledger: one row per figure this payload prints, carrying the state its inputs
     * deserve. `ADR-0001` §2 requires the constants behind a number to be visible wherever it appears
     * and §7 requires the date, so a row that rests on a declared constant names its source and read
     * date in the visible text rather than only in the `title` (owner ruling 5a: a tooltip is
     * mouse-only and fails keyboard and touch).
     *
     * @return list<array{text: string, badge: string, title: string}>
     */
    private function why(Advice $advice, TrainingRun $run): array
    {
        $rows = [
            $this->constantRow(
                'advisory_threshold',
                "The advisory line is Energy {$this->threshold()}, read as-is from a named source.",
            ),
        ];

        $option = $advice->recommendation;

        if ($option !== null) {
            $deficit = $this->deficitRow($option, $run);

            if ($deficit !== null) {
                $rows[] = $deficit;
            }

            $rows[] = $option->action === TrainerAdvisor::REST
                ? $this->constantRow('rest_recovery', "Rest recovers +{$this->constant('rest_recovery')} Energy.")
                : $this->sessionCostRow();
        }

        if ($advice->alternative?->action === self::WIT) {
            $rows[] = $this->constantRow('wit_cost', "Wit costs {$this->constant('wit_cost')} Energy.");
        }

        $finale = $advice->finale_context;

        if ($finale !== null) {
            $rows[] = [
                'text' => match ($finale['state']) {
                    'next' => "{$finale['label']} is the next turn.",
                    'passed' => "{$finale['label']} has passed; no outcome is recorded.",
                    default => "{$finale['label']} is {$finale['turns_away']} turns away.",
                },
                'badge' => 'calculated',
                'title' => 'Counted from the end of the career grid (YEAR_SENIOR x TURNS_PER_YEAR) '
                    .'against the turn this run would play next. Where the run stands, not a recommendation '
                    .'about the finale itself (ADR-0020 §3).',
            ];
        }

        return $rows;
    }

    /**
     * The deficit as arithmetic the Trainer can redo, not as a verdict.
     *
     * `current` is recovered as `target - deficit`, exact wherever the deficit is above zero, and it
     * costs no query on a payload read on every Cockpit render.
     *
     * @return array{text: string, badge: string, title: string}|null
     */
    private function deficitRow(AdvisorOption $option, TrainingRun $run): ?array
    {
        $target = $run->buildTarget()?->targets[$option->action] ?? null;

        if ($target === null) {
            return null;
        }

        return [
            'text' => "{$option->action} reads ".($target - $option->deficitClosed)
                ." in the latest turn entry against a target of {$target}: "
                ."the deficit is {$option->deficitClosed}.",
            'badge' => 'calculated',
            'title' => 'max(target - current, 0) over training_runs.build_target and the latest '
                .'turn_entries row, both entered by the Trainer.',
        ];
    }

    /**
     * @return array{text: string, badge: string, title: string}
     */
    private function sessionCostRow(): array
    {
        /** @var array{0: int, 1: int} $cost */
        $cost = $this->constant('session_cost');

        return $this->constantRow(
            'session_cost',
            "A training session costs {$cost[0]} to {$cost[1]} Energy — declared as a range because it "
            .'scales with a facility level this tool does not track.',
        );
    }

    /**
     * A row for a constant taken as read, with its source, read date and stale flag in the text.
     *
     * @return array{text: string, badge: string, title: string}
     */
    private function constantRow(string $key, string $text): array
    {
        /** @var array{value: mixed, source: string, verified_at: string, confidence: string} $constant */
        $constant = $this->constants()[$key];

        return [
            'text' => $text.' Source: '.$constant['source'].', read '.$constant['verified_at'].'.'
                .($constant['confidence'] === 'stale' ? ' Marked stale.' : ''),
            'badge' => 'confirmed',
            'title' => "config('advisor.constants.{$key}') — a declared constant printed as read, not a "
                .'figure this tool computed.',
        ];
    }

    /**
     * @return list<string>
     */
    private function switchIf(Advice $advice): array
    {
        $rows = [];

        // Only a stat can "reach its target". Below the advisory line the recommendation is Rest, and a
        // successor row would say the recovery is a deficit being closed, which it is not.
        if ($advice->recommendation?->action !== TrainerAdvisor::REST) {
            $successor = $this->successor($advice);

            if ($successor !== null) {
                $rows[] = "If {$advice->recommendation?->action} reaches its target, {$successor[0]} "
                    ."at a deficit of {$successor[1]} becomes the largest and ranks first.";
            }
        }

        $rows[] = $advice->band === TrainerAdvisor::BELOW
            ? "Once Energy reads {$this->threshold()} or above, the largest deficit against your target ranks first."
            : "If Energy reads below {$this->threshold()} at the next turn, Rest ranks ahead of every stat.";

        return $rows;
    }

    /**
     * The next-largest deficit after the recommendation, read off the options the engine already built.
     *
     * @return array{0: string, 1: int}|null
     */
    private function successor(Advice $advice): ?array
    {
        $excluded = [$advice->recommendation?->action, TrainerAdvisor::REST];
        $next = null;
        $largest = 0;

        foreach ($advice->options as $option) {
            if (in_array($option->action, $excluded, true) || $option->deficitClosed <= $largest) {
                continue;
            }

            $largest = $option->deficitClosed;
            $next = $option;
        }

        return $next === null ? null : [$next->action, $next->deficitClosed];
    }

    /**
     * Rest's door is the Training screen's record form, holding the choice, which is the rule
     * `CockpitController::actionSection()` already applies to the `rest` entry: the two surfaces that
     * offer a Rest turn must not disagree about where it is recorded. No door is a dead link, and none
     * is a loop back to this screen.
     */
    private function actionUrl(TrainingRun $run, string $action): string
    {
        return $action === TrainerAdvisor::REST
            ? route('runs.training', ['run' => $run, 'choice' => TurnChoice::Rest->value])
            : route('runs.training', $run);
    }

    /**
     * Energy after the action, as the range the declared constant describes. Moved here from
     * `CockpitController::energyAfterLine()`, whose only caller was the section this replaces, so the
     * sentence keeps one owner.
     *
     * @param  array{min: int, max: int}|null  $energyAfter
     */
    private function energyAfterLine(?array $energyAfter): ?string
    {
        if ($energyAfter === null) {
            return null;
        }

        return $energyAfter['min'] === $energyAfter['max']
            ? "Energy after this action: {$energyAfter['min']}."
            : "Energy after this action: {$energyAfter['min']} to {$energyAfter['max']}.";
    }

    /**
     * @return array<string, array{value: mixed, source: string, verified_at: string, confidence: string}>
     */
    private function constants(): array
    {
        return $this->advisor->constants();
    }

    private function constant(string $key): mixed
    {
        return $this->constants()[$key]['value'];
    }

    private function threshold(): int
    {
        return (int) $this->constant('advisory_threshold');
    }
}
