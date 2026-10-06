<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Http\Controllers\Controller;
use App\Models\RaceCatalogSlot;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Services\Advisor\Advice;
use App\Services\Advisor\TrainerAdvisor;
use App\Services\ScenarioCaps;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The Career Cockpit, `SCR-CAR-011` (SCREEN-009, plan §8 D8). The screen a Trainer reads on every turn.
 *
 * **Read-only except one write it does not own.** The page renders stored and entered facts; the only
 * write it offers is the manual correction, which posts to the existing `runs.turns.update` and its
 * `StoreTurnEntryRequest` (`design-2.0` §38). No new route, Form Request or column is introduced.
 *
 * **The advisor crosses the wire as C2's own contract.** `advisor` carries the five fields the plan's
 * "Recommendation contract (D8)" names — action, band, reasons, alternative, risk — and nothing else.
 * There is no `score` and no numeric confidence, because `ADR-0001` §3 leaves both unsourced and C2's
 * `Advice` has no field to carry them; a page-level addition would be a second place to be wrong.
 * `risk` is null in this slice: the only risk statement the corpus supports is a delay or a
 * reachability verdict, and both are held computation (`ADR-0020` §3).
 *
 * **Energy absent is a state, not a zero.** C2 declines to rank when the run has recorded no Energy,
 * and the page carries that refusal across: `band` and `action` are null and the one reason line is
 * C2's own sentence. The recommendation card prints it rather than a band read off nothing.
 *
 * **The action grid is the spec's seven entries** (SCREEN-009 §12) with at most one `RECOMMENDED`
 * marker, mapped from C2's answer: a stat action marks Training, `Rest` marks Rest, and a declined
 * advisor marks none. Training has its own screen from D9 (SCREEN-010); the rest land at D10 to D12 or
 * E1, so each entry links to the screen that owns the action today and its `note` names the one that
 * will replace it. No entry is a dead link.
 */
class CockpitController extends Controller
{
    /**
     * The seven entries of the action grid, in the spec's order (SCREEN-009 §12), with the reason
     * each is not yet a link.
     *
     * @var list<array{key: string, label: string, note: string}>
     */
    private const ACTIONS = [
        ['key' => 'training', 'label' => 'Training', 'note' => 'The five training options, and the turn record.'],
        ['key' => 'race', 'label' => 'Race', 'note' => 'Entering a race arrives with the Race decision screen.'],
        ['key' => 'rest', 'label' => 'Rest', 'note' => 'Rest is a turn choice on the run screen, not one of the five training options.'],
        ['key' => 'recreation', 'label' => 'Recreation', 'note' => 'A Recreation screen of its own is not built yet; the run screen records it as a turn choice.'],
        ['key' => 'scenario', 'label' => 'Scenario action', 'note' => 'Scenario actions arrive with the scenario panels.'],
        ['key' => 'event', 'label' => 'Event', 'note' => 'Event recording arrives with the Event decision screen.'],
        ['key' => 'inheritance', 'label' => 'Inheritance', 'note' => 'Inheritance recording arrives with the Inheritance event screen.'],
    ];

    private const MONTHS = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December',
    ];

    /**
     * The four meta values the state panel shows beside the five stats (SCREEN-009 §13).
     *
     * @var list<array{key: string, label: string}>
     */
    private const META = [
        ['key' => 'energy', 'label' => 'Energy'],
        ['key' => 'mood', 'label' => 'Mood'],
        ['key' => 'fans', 'label' => 'Fans'],
        ['key' => 'skill_points', 'label' => 'Skill Points'],
    ];

    public function show(TrainingRun $run): Response
    {
        // Two queries for the whole page: the run with its trainee, and its turns. The latest turn
        // is read off the loaded collection rather than re-queried, which is `showData()`'s own
        // reading of the same rows.
        $run->load(['umamusume', 'turnEntries']);

        $latest = $run->turnEntries->sortByDesc('turn')->first();
        $advice = app(TrainerAdvisor::class)->advise($latest, $run->buildTarget());

        return Inertia::render('Career/Cockpit', [
            'run' => $this->runSection($run),
            'header' => $this->headerSection($run, $latest),
            'state' => $this->stateSection($run, $latest),
            'actions' => $this->actionSection($run, $advice),
            'advisor' => $this->advisorSection($advice),
            'calendar' => $this->calendarSection($run),
            'correction' => $this->correctionSection($run, $latest),
        ]);
    }

    /**
     * The run's own identity, flattened. `scenario_label` names the absence rather than borrowing the
     * baseline's name, which is a composition device and not a fact (D-220).
     *
     * @return array{id: int, trainee: string, trainee_ja: string|null, status: string, status_label: string, scenario_label: string, run_url: string}
     */
    private function runSection(TrainingRun $run): array
    {
        return [
            'id' => $run->id,
            'trainee' => $run->umamusume->name,
            'trainee_ja' => $run->umamusume->name_ja,
            'status' => $run->status->value,
            'status_label' => $run->status->label(),
            'scenario_label' => $run->hasScenario()
                ? (string) config('scenarios.scenarios.'.$run->scenarioKey().'.label', $run->scenarioKey())
                : 'No scenario set',
            // The cockpit descends from the run's own screen (SCREEN-009 §12), so the record screen
            // stays one link away and every existing write route stays where it was.
            'run_url' => route('runs.show', $run),
        ];
    }

    /**
     * The career header: where the run stands and the four readings it holds, plus the scenario's own
     * widgets beyond the three every scenario composes.
     *
     * The widget list is config's by subtraction against the baseline entry, never a scenario name
     * (D-240, gate G-33): a fifth scenario names its resource in one config line and no component
     * branches. A run that has logged no turn claims no year and no month, never Early January
     * (`DashboardController::positionLabel()`'s reasoning, D-220).
     *
     * @return array<string, mixed>
     */
    private function headerSection(TrainingRun $run, ?TurnEntry $latest): array
    {
        $turn = $run->turnEntries->count();
        $position = $turn < 1 ? null : (($turn - 1) % TrainingRun::TURNS_PER_YEAR) + 1;
        $year = $turn < 1 ? null : TrainingRun::careerYearForTurn($turn);

        return [
            'scenario_label' => $run->hasScenario()
                ? (string) config('scenarios.scenarios.'.$run->scenarioKey().'.label', $run->scenarioKey())
                : 'No scenario set',
            'scenario_declared' => $run->hasScenario(),
            'year_label' => $year === null ? null : (RaceCatalogSlot::YEARS[$year] ?? (string) $year).' Year',
            'month_label' => $position === null ? null : ($position % 2 === 1 ? 'Early ' : 'Late ').self::MONTHS[intdiv($position - 1, 2)],
            'turn' => $turn,
            'energy' => $latest?->energy,
            'mood' => $latest?->mood?->value,
            'fans' => $latest?->fans,
            'skill_points' => $latest?->sp,
            // The scenario's own widgets, and only those: the baseline three are the header's own
            // figures above, so passing them here would print Energy twice.
            'widgets' => $this->scenarioWidgets($run),
            'values' => [
                'turn' => $turn,
                'energy' => $latest?->energy,
                'fans' => $latest?->fans,
                // No column records a scenario resource yet, so each renders `N/A` with its reason
                // rather than a starting value the tool cannot source (D-220).
                'team_rank' => null,
                'bursts' => null,
                'grade_points' => null,
                'shop_coins' => null,
            ],
        ];
    }

    /**
     * The five stats and the four meta values (SCREEN-009 §13).
     *
     * Each stat carries its entered current value, the Trainer's own target from C1, and the ceiling
     * `ScenarioCaps::forRun()` states — the same call the turn validator makes, so the panel cannot
     * show a number the form would refuse (KI-47, `ADR-0015`). Absent values are null rather than
     * zero: a run that logged nothing has no stats, and a run with no target has no target.
     *
     * @return array{stats: list<array{key: string, label: string, current: int|null, target: int|null, cap: int}>, meta: list<array{key: string, label: string, value: int|string|null}>}
     */
    private function stateSection(TrainingRun $run, ?TurnEntry $latest): array
    {
        /** @var list<string> $order */
        $order = config('scenarios.stat_order');
        $caps = ScenarioCaps::forRun($run);
        $target = $run->buildTarget();

        $stats = [];

        foreach ($order as $stat) {
            $stats[] = [
                'key' => $stat,
                'label' => $stat,
                'current' => $this->currentStat($latest, $stat),
                'target' => $target?->targets[$stat] ?? null,
                'cap' => $caps[$stat],
            ];
        }

        $values = [
            'energy' => $latest?->energy,
            'mood' => $latest?->mood?->value,
            'fans' => $latest?->fans,
            'skill_points' => $latest?->sp,
        ];

        $meta = [];

        foreach (self::META as $row) {
            $meta[] = [...$row, 'value' => $values[$row['key']]];
        }

        return ['stats' => $stats, 'meta' => $meta];
    }

    /**
     * The seven entries, with at most one `RECOMMENDED` marker (Von Restorff, plan §13).
     *
     * Which entry carries it is a property of C2's answer, not of a scenario: a stat action marks
     * Training, `Rest` marks Rest, and a declined advisor marks none.
     *
     * Every entry links to the screen that owns the action **today** — the Training decision screen for
     * training (SCREEN-010, D9), the run's record screen whose guided rail records the other turn
     * choices, and the Legacy builder for the ancestry — and the entry's own 2.0 screen is named in its
     * `note` where that screen has not landed. The slice's
     * brief says "otherwise they are named absences"; a grid of seven unfocusable items would fail
     * this slice's own keyboard-path acceptance and would leave the Cockpit with no way to act, so
     * the absence is named in the copy and the door is a live route. No entry is ever a dead link.
     *
     * @return list<array{key: string, label: string, href: string, recommended: bool, note: string}>
     */
    private function actionSection(TrainingRun $run, Advice $advice): array
    {
        $recommended = match ($advice->recommendation?->action) {
            null => null,
            TrainerAdvisor::REST => 'rest',
            default => 'training',
        };

        return array_map(
            static fn (array $action): array => [
                ...$action,
                'href' => match ($action['key']) {
                    'inheritance' => route('legacy.builder', $run),
                    'training' => route('runs.training', $run),
                    default => route('runs.show', $run),
                },
                'recommended' => $action['key'] === $recommended,
            ],
            self::ACTIONS,
        );
    }

    /**
     * C2's answer, flattened to the five contract fields (plan §8 "Recommendation contract (D8)").
     *
     * `reasons` carries the recommendation's own reason line plus the Energy the action leaves
     * behind, because both are fields C2 already returns. When it declines, the one reason line is
     * C2's own sentence, so a Trainer acting on the refusal knows which input is missing.
     *
     * @return array{action: string|null, band: string|null, reasons: list<string>, alternative: string|null, risk: string|null}
     */
    private function advisorSection(Advice $advice): array
    {
        $recommendation = $advice->recommendation;

        $reasons = [];

        if ($recommendation !== null) {
            $reasons[] = $recommendation->reason;

            $after = $this->energyAfterLine($recommendation->energyAfter);

            if ($after !== null) {
                $reasons[] = $after;
            }
        } elseif ($advice->absence !== null) {
            $reasons[] = $advice->absence;
        }

        return [
            'action' => $recommendation?->action,
            'band' => $advice->band,
            'reasons' => $reasons,
            'alternative' => $advice->alternative?->action,
            // Null by choice, not by absence of data: `energyAfter` is in hand and a line could be
            // derived from it, but every derivation of "is this risky" is a judgement the engine owns
            // and C2 does not make (a delay, a reachability verdict — `ADR-0020` §3). The controller
            // does not grow a second opinion beside the one engine. D9 to D12 fill this field when a
            // screen has a sourced risk to state.
            'risk' => null,
        ];
    }

    /**
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
     * The race calendar's inputs, so the timeline column is real data rather than an empty frame.
     *
     * ponytail: this is `TrainingRunController::calendarPayload()`'s body, kept separate rather than
     * shared because extracting it would touch a landed controller and its browser spec for one
     * caller. Extract it into a shared builder when a third screen needs the calendar.
     *
     * @return array{show: bool, cells: list<array<string, mixed>>, year: int, yearTabs: list<array{year: int, label: string, url: string}>, yearWord: string|null, nextTurn: int|null}
     */
    private function calendarSection(TrainingRun $run): array
    {
        // Self-gating on the matrix, not on a scenario name (D-221, D-241, gate G-34).
        $show = $run->composesPanel('race_calendar');
        $calendarYear = $run->careerYearForTab(request('year'));

        $yearTabs = [];

        foreach (RaceCatalogSlot::YEARS as $value => $label) {
            if ($value === RaceCatalogSlot::YEAR_FINALE) {
                continue;
            }

            $yearTabs[] = [
                'year' => $value,
                'label' => $label.' Year',
                'url' => request()->fullUrlWithQuery(['year' => $value]),
            ];
        }

        $nextTurn = $run->hasScenario() ? $run->nextTurnToPlay() : null;

        return [
            'show' => $show,
            'cells' => $show ? $run->calendarCells($calendarYear) : [],
            'year' => $calendarYear,
            'yearTabs' => $yearTabs,
            'yearWord' => RaceCatalogSlot::YEARS[$calendarYear] ?? null,
            'nextTurn' => $nextTurn !== null && $calendarYear === $nextTurn['year'] ? $nextTurn['turn'] : null,
        ];
    }

    /**
     * The manual correction (`design-2.0` §38), opened on the latest logged turn and posted to the
     * route and Form Request that already own a turn write. Null when there is no turn to correct.
     *
     * @return array{action: string, turn: int, values: array<string, int|string|null>}|null
     */
    private function correctionSection(TrainingRun $run, ?TurnEntry $latest): ?array
    {
        if ($latest === null) {
            return null;
        }

        return [
            'action' => route('runs.turns.update', [$run, $latest]),
            'turn' => $latest->turn,
            'values' => [
                'speed' => $latest->speed,
                'stamina' => $latest->stamina,
                'power' => $latest->power,
                'guts' => $latest->guts,
                'wit' => $latest->wit,
                'sp' => $latest->sp,
                'energy' => $latest->energy,
                'fans' => $latest->fans,
                'mood' => $latest->mood?->value,
            ],
        ];
    }

    /**
     * The scenario's widgets beyond the baseline entry's three, by subtraction (D-240, gate G-33).
     *
     * @return list<string>
     */
    private function scenarioWidgets(TrainingRun $run): array
    {
        /** @var list<string> $widgets */
        $widgets = array_values((array) config('scenarios.scenarios.'.$run->scenarioKey().'.widgets'));
        /** @var list<string> $baseline */
        $baseline = array_values((array) config('scenarios.scenarios.'.config('scenarios.baseline').'.widgets'));

        return array_values(array_filter($widgets, static fn (string $widget): bool => ! in_array($widget, $baseline, true)));
    }

    private function currentStat(?TurnEntry $latest, string $stat): ?int
    {
        if ($latest === null) {
            return null;
        }

        return match ($stat) {
            'Speed' => $latest->speed,
            'Stamina' => $latest->stamina,
            'Power' => $latest->power,
            'Guts' => $latest->guts,
            'Wit' => $latest->wit,
            default => null,
        };
    }
}
