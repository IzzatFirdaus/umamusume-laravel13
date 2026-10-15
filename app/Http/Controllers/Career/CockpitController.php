<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Domain\Career\CareerPosition;
use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Enums\TraineeGoalType;
use App\Enums\TurnEventType;
use App\Http\Controllers\Controller;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\Skill;
use App\Models\TraineeGoal;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\TurnEvent;
use App\Services\Advisor\Advice;
use App\Services\Advisor\NextDecision;
use App\Services\Advisor\TrainerAdvisor;
use App\Services\CareerCalendar;
use App\Services\FanLadder;
use App\Services\Scenario\FinaleReader;
use App\Services\ScenarioCaps;
use Illuminate\Support\Collection;
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
 * advisor marks none. Training has its own screen from D9 (SCREEN-010), Race from D10 (SCREEN-011)
 * and Inheritance from D12 (SCREEN-013); Recreation, Events and the scenario actions land at Phase
 * E, so each entry links to the screen that owns the action today and its `note` names the one that
 * will replace it. No entry is a dead link.
 *
 * **The left column is run-scoped, not a catalogue.** D14a replaced the reused `RaceCalendar` grid with
 * `raceStrip`: this run's recorded races, the turn being decided, and the mandatory races still to come.
 * The full calendar stays on the run record screen (`SCR-RUN-001`), where seeing every race is the
 * question being asked. `SCREEN_SPEC.md` SCR-CAR-011 gap 2 carries the history.
 *
 * **The run's own identity is one prop, read per block.** `identity` carries the facts the Trainer
 * entered about the trainee itself (rarity, potential, the card form, the growth-rate row) and the fan
 * ladder the latest reading sits in; each is null until a writer records it, so a block with nothing to
 * say renders the absence rather than a zero (D-220). The team's identity and the Unity Cup counters
 * ride the scenario's own `team` payload instead, because that is where the matrix already gates the
 * system to the scenarios that compose it. The fan ladder is read through `FanLadder::fromValue`, the
 * one reader of `config('uma.fan_ladder')`, so the class and the gap cannot come from different bands.
 *
 * @phpstan-type TeamSectionShape array{
 *     rank: string|null,
 *     facility_level: int|null,
 *     ladder: list<array{rank: string, level: int}>,
 *     stat_grades: list<array{key: string, label: string, grade: null}>,
 *     roster: list<array{teammate: string, state: string, stateLabel: string}>,
 *     race_guidance: int|null,
 *     race_entries: list<array{title: string|null, tier: string|null, circles: int|null, placement: int|null}>,
 *     bands: list<array{total: string, reward: string}>,
 *     payout_timing: string|null,
 *     special_training: array{energy_penalty_removed: bool|null, wit_burst_energy_bonus: int|null},
 *     team_name: string|null,
 *     team_motto: string|null,
 *     team_league_placement: int|null,
 *     team_preseason_wins: int|null,
 *     unity_trainings_count: int|null,
 *     spirit_bursts_count: int|null,
 *     extreme_bursts_count: int|null,
 *     combined_bursts: int|null,
 *     burst_band: string|null
 * }
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
        ['key' => 'rest', 'label' => 'Rest', 'note' => 'Rest is a turn choice no 2.0 screen offers yet, and it is not one of the five training options.'],
        ['key' => 'recreation', 'label' => 'Recreation', 'note' => 'A Recreation screen of its own is not built yet, and no 2.0 screen records the turn it would stand for.'],
        ['key' => 'scenario', 'label' => 'Scenario action', 'note' => 'Scenario actions arrive with the scenario panels.'],
        ['key' => 'event', 'label' => 'Event', 'note' => 'Event choices are recorded at the Event decision screen.'],
        ['key' => 'inheritance', 'label' => 'Inheritance', 'note' => 'Inheritance is recorded at the Inheritance event screen.'],
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
        // The run with its trainee, its turns, and its race log with the slots and the turn link each
        // entry points at, so the strip costs no N+1. The latest turn is read off the loaded
        // collection rather than re-queried.
        // `turnEvents` joins only when the scenario's team system has a payload reader at all: a
        // URA or Grand Concert cockpit must not hydrate every event row to discard it.
        $needsEvents = $run->composesPanel('team_rank_ladder')
            || in_array('spirit_bursts', (array) config('scenarios.scenarios.'.$run->scenarioKey().'.widgets', []), true);

        $run->load(['umamusume', 'characterCard', 'turnEntries', 'raceEntries.raceCatalogSlot', 'raceEntries.scenarioSlot', 'raceEntries.turnEntry']);

        if ($needsEvents) {
            $run->load('turnEvents');
        }

        $latest = $run->turnEntries->sortByDesc('turn')->first();
        $advice = app(TrainerAdvisor::class)->advise($latest, $run->buildTarget(), $run);

        // Built once: the header's rank and the scenario section's team panel read the same pass.
        $team = $this->teamSection($run);

        // Built once for the same reason: a run with no stored position derives one with a
        // `max(turn)` query, and three props read it.
        $position = $run->careerPosition();

        return Inertia::render('Career/Cockpit', [
            'run' => $this->runSection($run),
            'header' => $this->headerSection($run, $latest, $team, $position),
            // Where the career stands, and whether that answer was named or derived. A snapshot's
            // position is the one its Trainer entered; a new career's is read off its own turn log.
            // The distinction crosses the wire because the header prints a named position and the
            // review screen reads the confidence map that rides the same value object.
            'careerPosition' => $position?->toArray(),
            'hasImportedPosition' => $run->hasImportedPosition(),
            'scenarioCountdown' => $position?->scenarioCountdown,
            'gradeObjectives' => $run->gradeObjectives(),
            // The run's own identity as the Trainer entered it (A6.1, A6.2, A6.3): the trainee's
            // rarity and potential, the card form it started on, and the fan ladder its latest
            // reading sits in. Read per block on the page; the team identity rides the scenario's
            // own panel payload, where the matrix already gates it to Unity Cup.
            'identity' => $this->identitySection($run, $latest),
            'state' => $this->stateSection($run, $latest),
            'actions' => $this->actionSection($run, $advice),
            'advisor' => $this->advisorSection($advice, $run),
            'raceStrip' => $this->raceStripSection($run),
            'scenario' => $this->scenarioSection($run, $team, $latest, $advice),
            // F2, plan §9.6 ruling 2: arbitrary-turn correction. The selected turn defaults to the
            // latest, but `?edit_turn=<id>` names any logged turn; the form posts to the existing
            // `runs.turns.update` / `runs.turns.destroy`, unchanged.
            'correction' => $this->correctionSection($run, $this->resolvedCorrectionTurn($run, $latest)),
        ]);
    }

    /**
     * The run's own identity, flattened. `scenario_label` names the absence rather than borrowing the
     * baseline's name, which is a composition device and not a fact (D-220).
     *
     * @return array{id: int, trainee: string, trainee_ja: string|null, status: string, status_label: string, scenario_label: string, run_url: string, timeline_url: string, result_url: string, training_url: string, deck_url: string, status_labels: array<string,string>, scenarios: array<string,string>, update_url: string, destroy_url: string, current_objective_index: int|null}
     */
    private function runSection(TrainingRun $run): array
    {
        return [
            'id' => $run->id,
            'trainee' => $run->umamusume->name,
            'trainee_ja' => $run->umamusume->name_ja,
            'status' => $run->status->value,
            'status_label' => $run->status->label(),
            // The header edit forms (ruling 1) carry these as hidden values so a partial update
            // cannot blank a sibling: `runs.update` requires `umamusume_id` and accepts `scenario`,
            // and the form re-posts the stored values it is not editing.
            'umamusume_id' => $run->umamusume_id,
            'scenario' => $run->scenario,
            'scenario_label' => $run->hasScenario()
                ? (string) config('scenarios.scenarios.'.$run->scenarioKey().'.label', $run->scenarioKey())
                : 'No scenario set',
            // The cockpit owns the run-identity writes (F2, plan §9.6 ruling 1): status, scenario
            // and grade-point period all POST through `runs.update`, the same route and validator the
            // 0.1.0 record screen used. The cockpit is the door, so the 2.0 edit URLs ride here.
            'run_url' => route('runs.cockpit', $run),
            // The left column's own door to the Career Timeline (SCR-CAR-017): the region shows this
            // run's races, and the timeline is the screen that shows the whole recorded history.
            'timeline_url' => route('runs.timeline', $run),
            // The Career Result (SCR-CAR-018, plan §8 D15). It had no door on any 2.0 screen: the only
            // link to it sat in the 0.1.0 record page's header, so retiring that page would leave the
            // screen URL-only, which D14 ruled a defect. The result screen answers an unfinished career
            // itself, so the door does not gate on status.
            'result_url' => route('runs.result', $run),
            // The door to Training Decision (SCREEN-010), where a turn is recorded; the Cockpit's empty
            // state links here rather than to the legacy run screen, which the redirect will retire.
            'training_url' => route('runs.training', $run),
            // The run's own Support deck builder (R2-17). It answers and writes this run's six slots,
            // and until now no 2.0 screen linked it: the Training screen's door arrived in `e2889ff`,
            // and the Cockpit, which is where a Trainer mid-career stands, offered none at all, so the
            // screen was reachable only by typing its URL.
            'deck_url' => route('runs.deck', $run),
            // The write routes and picker choices for the header edit form. Labelled
            // `current_objective_index` mirrors `runs.update`'s form key so a partial update
            // (status only, scenario only, period only) carries the other two fields as their current
            // value rather than letting a PUT blank the siblings the form did not render.
            'update_url' => route('runs.update', $run),
            'destroy_url' => route('runs.destroy', $run),
            // The export door (F2, plan §9.6 ruling: export is one of the nine legacy write-adjacent
            // controls). Both are GET file responses; a download must not become an HTML redirect, so
            // the Cockpit links straight at them rather than through the retired record screen.
            'export_csv_url' => route('runs.export', [$run, 'csv']),
            'export_json_url' => route('runs.export', [$run, 'json']),
            'status_labels' => [
                RunStatus::Active->value => RunStatus::Active->label(),
                RunStatus::Completed->value => RunStatus::Completed->label(),
                RunStatus::Retired->value => RunStatus::Retired->label(),
            ],
            'scenarios' => $this->scenarioLabels(),
            'current_objective_index' => $run->current_objective_index,
        ];
    }

    /**
     * Every scenario key the run may be assigned, as key => label, for the header's scenario picker.
     * Read from `config/scenarios.php` — the only source of scenario names in the layout path.
     *
     * @return array<string, string>
     */
    private function scenarioLabels(): array
    {
        /** @var array<string, array<string, mixed>> $scenarios */
        $scenarios = config('scenarios.scenarios', []);

        return collect($scenarios)
            ->mapWithKeys(fn (array $def, string $key): array => [$key => (string) ($def['label'] ?? $key)])
            ->all();
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
     * @param  TeamSectionShape  $team  built once by `show()` so the header and the panel read one pass
     * @return array<string, mixed>
     */
    private function headerSection(TrainingRun $run, ?TurnEntry $latest, array $team, ?CareerPosition $position = null): array
    {
        // One turn owner, in one priority order: a position the Trainer named, else the position the
        // calendar derives from this run's own turn log, else the highest logged turn number. The count
        // of logged turns is never a turn number - a snapshot logs one entry, at turn 75, and a count
        // printed it as turn 1 beside a correction form reading Turn 75. `careerPosition()` answers null
        // for a turn the 72-turn calendar does not model, so year and month render as unrecorded rather
        // than as Junior Early January.
        $position ??= $run->careerPosition();
        $turn = $position?->turnIndex ?? (int) ($latest?->turn ?? 0);
        $yearLabel = $position?->year->label();
        $monthLabel = $position === null
            ? null
            : $position->phase->value.' '.CareerCalendar::monthLabel($position->month);

        $energy = $latest === null ? ['value' => null, 'text' => null] : $latest->energyReading();

        return [
            'scenario_label' => $run->hasScenario()
                ? (string) config('scenarios.scenarios.'.$run->scenarioKey().'.label', $run->scenarioKey())
                : 'No scenario set',
            'scenario_declared' => $run->hasScenario(),
            'year_label' => $yearLabel,
            'month_label' => $monthLabel,
            'turn' => $turn,
            'energy' => $energy['value'],
            // The reading as the header prints it: a recorded band reads `Low band`, not `N/A`. The
            // number alone was a half answer, because a turn recorded without one is still recorded
            // (R3-01) and `TurnEntry::energyReading()` is the one owner of that resolution.
            'energy_text' => $energy['text'],
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
                // The rank letter the Trainer most recently reported rides the team payload, so it
                // is a recorded reading where one exists; the rest stay absent until a column or a
                // payload holds them (D-220). The Team panel reads the same payload, so the header
                // strip and the panel cannot disagree about one rank.
                'team_rank' => $team['rank'],
                // KI-97: the strip captions this box "normal plus Extreme", and the panel already owns
                // that combined figure, so the rail reads the same value instead of recomputing it or
                // printing an absence over a recorded pair.
                'bursts' => $team['combined_bursts'] ?? null,
                'grade_points' => null,
                'shop_coins' => null,
            ],
        ];
    }

    /**
     * The run's own identity, as the Trainer entered it: the trainee's rarity and potential (A6.1),
     * the card form it started on (A6.2), the growth-rate row it read off that card (A6.3), and the
     * fan ladder the latest reading sits in.
     *
     * Each field is null until a writer records it, so a block with nothing renders the absence rather
     * than a zero (D-220). The card title is the verbatim Global client string (`[Rosy Dreams]`),
     * brackets included. The ladder is read through `FanLadder::fromValue` so the class and the gap are
     * one band's answer; a count no documented band contains yields a null class and a bare number,
     * never a class nobody sourced.
     *
     * @return array{trainee_rarity: int|null, potential_level: int|null, card_title: string|null, growth_rate: array<string, int|null>|null, stat_order: list<string>, caps: array<string, array{entered: int|null, scenario: int}>, fans: int|null, fan_ladder: array{class: string|null, nextThreshold: int|null, gap: int|null}|null}
     */
    private function identitySection(TrainingRun $run, ?TurnEntry $latest): array
    {
        $fans = $latest?->fans;

        // The four ceilings the Trainer may enter (`ADR-0025`), each beside the figure the scenario itself
        // states, so the identity form prefills what was read and still shows what the matrix publishes.
        // Which stats may carry one is `ScenarioCaps`' answer, not a list written out here.
        $published = ScenarioCaps::scenarioCaps($run);
        $caps = [];

        foreach (ScenarioCaps::enteredCaps($run) as $stat => $entered) {
            $caps[$stat] = ['entered' => $entered, 'scenario' => $published[$stat]];
        }

        return [
            'trainee_rarity' => $run->trainee_rarity,
            'potential_level' => $run->potential_level,
            'card_title' => $run->characterCard?->title,
            'growth_rate' => $run->growth_rate,
            'stat_order' => array_values((array) config('scenarios.stat_order')),
            'caps' => $caps,
            'fans' => $fans,
            'fan_ladder' => $fans === null ? null : FanLadder::fromValue((int) $fans),
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
     * @return array{stats: list<array{key: string, label: string, current: int|null, target: int|null, cap: int}>, meta: list<array{key: string, label: string, value: int|string|null, band_text: string|null}>}
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

        // The gauge needs the number; a turn recorded as a band has none, and the words the client showed
        // travel beside it so the strip prints the reading rather than an absence (R3-01).
        $energy = $latest === null
            ? ['kind' => 'none', 'value' => null, 'text' => null]
            : $latest->energyReading();

        $values = [
            'energy' => $energy['value'],
            'mood' => $latest?->mood?->value,
            'fans' => $latest?->fans,
            'skill_points' => $latest?->sp,
        ];

        $meta = [];

        foreach (self::META as $row) {
            $meta[] = [
                ...$row,
                'value' => $values[$row['key']],
                'band_text' => $row['key'] === 'energy' && $energy['kind'] === 'band' ? $energy['text'] : null,
            ];
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
     * training (SCREEN-010, D9), the Race decision screen for race (SCREEN-011, D10) and the
     * Inheritance event screen for inheritance (SCREEN-013, D12) — and the entry's own 2.0 screen is
     * named in its `note` where that screen has not landed. Recreation, Events and the scenario
     * actions still land at Phase E, so their entries keep their Phase E `note` and link to the run
     * record screen. The slice's brief says "otherwise they are named absences"; a grid of seven
     * unfocusable items would fail this slice's own keyboard-path acceptance and would leave the
     * Cockpit with no way to act, so the absence is named in the copy and the door is a live route.
     * No entry is ever a dead link.
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
                    'inheritance' => route('runs.inheritance', $run),
                    'training' => route('runs.training', $run),
                    'race' => route('runs.races.decision', $run),
                    'event' => route('runs.events.decision', $run),
                    default => route('runs.cockpit', $run),
                },
                'recommended' => $action['key'] === $recommended,
            ],
            self::ACTIONS,
        );
    }

    /**
     * The shared Next Decision contract (FD-2), plus the finale position the engine already returns.
     *
     * `decision` is the frozen six-key payload and nothing else; the flattening, the provenance rows and
     * the two refusal copies all belong to `NextDecision`, not to this controller (§7, thin controllers).
     *
     * `finale` stays a sibling rather than folding into the contract: the frozen shape has exactly six
     * keys, and it is a position read off the career calendar rather than a judgement about the next turn
     * (`ADR-0020` §3, `SCREEN_SPEC.md` §7-22).
     *
     * @return array{decision: array<string, mixed>, finale: array{label: string, state: string, turns_away: int|null}|null}
     */
    private function advisorSection(Advice $advice, TrainingRun $run): array
    {
        return [
            'decision' => app(NextDecision::class)->build($advice, $run, $this->objectiveSection($run)),
            'finale' => $advice->finale_context,
        ];
    }

    /**
     * The scenario's own obligations, as FD-3 states them (plan §9a, continuation prompt §4.2).
     *
     * **One query, two readers.** `obligationSlots()` is the only place this screen reads
     * `race_catalog_slots` for the mandatory rows, and the run race strip filters its result rather than
     * asking again — `ADR-0015`'s point that unifying the function is not unifying the call, so both
     * readers must pass the same career position.
     *
     * **`grade_checkpoint` is declared in `objective_priority` and produces no row here on purpose.**
     * Trackblazer's grade ladder already renders on its own panel from `TrackblazerGradeSection.periods`,
     * with the required figure, the earned figure and the unpriced count per period. Lending those same
     * numbers to the rail would be a second representation of one fact, which Phase G's acceptance forbids
     * more loudly than FD-3 asks for another surface. `scenario_milestone` produces no row because nothing
     * in the tree names a milestone; both classes stay in the order so a scenario that does have a source
     * for one can supply rows without a component changing.
     *
     * @return list<array{label: string, state: string, due_turn: int|null, due_label: string|null, consequence: string|null, source: string, badge: string, title: string}>
     */
    private function objectiveSection(TrainingRun $run): array
    {
        // A class the scenario's matrix does not compose produces no row. `race_calendar` is off for
        // Trackblazer (whose own note says no mandatory race goals exist) and for Our Grand Concert, so
        // neither is lent an obligation its composition denies (G-33, D-240, D-225).
        if (! (config("scenarios.scenarios.{$run->scenarioKey()}.panels.race_calendar") ?? false)) {
            return [];
        }

        $rows = [];
        $next = $run->nextTurnToPlay();
        $entered = $run->raceEntries->pluck('race_catalog_slot_id')->filter()->all();

        foreach ($this->obligationSlots($run) as $slot) {
            $class = $this->obligationClass($slot);
            $recorded = in_array($slot->id, $entered, true);
            $state = match (true) {
                $recorded => 'completed',
                $slot->turn === null => 'unknown',
                $slot->isAtOrBeforeTurn($next) => 'current',
                default => 'upcoming',
            };

            $rows[] = [
                'label' => $slot->hasFanGate()
                    ? $slot->title.' · '.number_format((int) $slot->fans_needed).' fans'
                    : $slot->title,
                'state' => $state,
                'due_turn' => $slot->turnNumber(),
                'due_label' => $slot->turnNumber() === null
                    ? null
                    : $slot->yearLabel().' Year · Turn '.$slot->turnNumber(),
                // No source in this repository states what changes when an obligation is not met, so the
                // field stays null rather than carrying an invented consequence.
                'consequence' => null,
                'source' => 'race_catalog_slots',
                'badge' => 'calculated',
                'title' => match ($state) {
                    'completed' => 'Compared race_entries.race_catalog_slot_id against this '
                        .'race_catalog_slots row: an entered race points at it.',
                    'current' => 'Compared race_catalog_slots.year and .turn against the turn this run is '
                        .'deciding: the slot has arrived and no race_entries row points at it.',
                    'upcoming' => 'Compared race_catalog_slots.year and .turn against the turn this run is '
                        .'deciding: the slot falls later, and no race_entries row points at it.',
                    default => 'The source states no turn for this slot (race_catalog_slots.turn is null), '
                        .'so it cannot be placed in the grid. It is an obligation the calendar names and '
                        .'the tool cannot date.',
                },
                'class' => $class,
            ];
        }

        /** @var list<string> $order */
        $order = config('scenarios.objective_priority');

        $rows = collect($rows)
            // One composite key: the declared class order first, then the turn the calendar states, and a
            // slot with no turn last within its class rather than at turn zero.
            ->sortBy(fn (array $row): string => sprintf(
                '%03d-%020d',
                ($rank = array_search($this->obligationClassFromRow($row), $order, true)) === false
                    ? count($order)
                    : $rank,
                $row['due_turn'] ?? PHP_INT_MAX,
            ))
            ->map(static fn (array $row): array => array_slice($row, 0, 8, true))
            ->values()
            ->all();

        return $rows;
    }

    /**
     * The class a built row belongs to, recovered from the row itself.
     *
     * The eight FD-3 fields are the contract and a class name is not one of them, so the class is carried
     * while sorting and dropped before the row crosses the wire rather than added as a ninth key a
     * consumer could branch on.
     *
     * @param  array<string, mixed>  $row
     */
    private function obligationClassFromRow(array $row): string
    {
        return (string) $row['class'];
    }

    /**
     * Which objective class a slot belongs to, read from the row's own columns.
     */
    private function obligationClass(RaceCatalogSlot $slot): string
    {
        return match (true) {
            $slot->year === RaceCatalogSlot::YEAR_FINALE => 'finale',
            $slot->hasFanGate() => 'fan_gate',
            default => 'mandatory_race',
        };
    }

    /**
     * The mandatory slots this scenario's calendar records, in grid order: the one reader of that table
     * for this screen, shared by the rail and by the run race strip.
     *
     * @return Collection<int, RaceCatalogSlot>
     */
    private function obligationSlots(TrainingRun $run): Collection
    {
        return RaceCatalogSlot::query()
            ->forScenario($run->scenarioKey())
            ->where('is_mandatory', true)
            ->orderBy('year')
            ->orderBy('turn')
            ->get();
    }

    /**
     * The run-scoped race strip (D14a): this run's race history, the turn it is deciding, and the
     * obligations still to come. It replaces the 0.1.0 catalogue grid in the left column, which was
     * built for the run record screen where the whole calendar is the question (`SCREEN_SPEC.md`
     * SCR-CAR-011 gap 2).
     *
     * **Three regions, one race per region.** A race the run already entered appears under Run and
     * nowhere else, so a finished race can never read as something still to come. Ahead is therefore
     * strictly future *and* unrecorded, which is also what lets its rows carry no state field at all.
     *
     * **This Turn counts; it does not list.** The seed catalogue holds eleven rows at Senior turn 19,
     * and a narrow column that prints eleven names is the wall this slice exists to remove. The option
     * list, the facts and the fan-gap arithmetic are the Race Decision screen's (`SCR-CAR-013`), so the
     * strip gives the position, how many races the calendar offers there, and what this run has already
     * entered against them.
     *
     * **No goal races.** `trainee_goals` has never been migrated (KI-34), `race_catalog_slots` has no
     * trainee-bearing column, and `scenario_slots` holds `goal_race` rows for `ura_finale` alone. A
     * per-row `goal` flag would print a claim the tool cannot source, so the region states the absence
     * once and the rows have no such key. `is_mandatory` is a career obligation, never a Goal
     * (`79ffad5`).
     *
     * *Dated correction 2026-10-13 (`ADR-0026`):* the paragraph above described the state before the
     * ladder was fetched, and its last sentence still holds - `is_mandatory` is not a Goal. What changed
     * is the first clause: `trainee_goals` is migrated, sourced per character, and every strip row whose
     * calendar slot is one of this trainee's objectives now carries a `goal` line stating the requirement.
     * The obligation prints **beside** a gate rather than in place of it: a fan-locked goal is still
     * locked, and a recorded goal keeps its finish state and still shows the mark. The absence sentence
     * stays for a scenario or trainee with no ladder to read.
     *
     * **Nothing here predicts.** No readiness band, no win figure, no percentage, no distance band
     * derived from metres and no race fact beyond the stored title and tier (`ADR-0016`, `ADR-0020` §3).
     *
     * @return array{shown: bool, notice: string|null, run: list<array<string, mixed>>, this_turn: array{turn: int, year_label: string, offered: int, recorded: list<array<string, mixed>>}|null, ahead: list<array<string, mixed>>, absences: array{turn_link: string, goal: string}, empty: array{run: string|null, this_turn: string|null, ahead: string|null}, links: array{decision_url: string, run_url: string}}
     */
    private function raceStripSection(TrainingRun $run): array
    {
        $links = [
            'decision_url' => route('runs.races.decision', $run),
            'run_url' => route('runs.cockpit', $run),
        ];
        $absences = [
            'turn_link' => 'The Trainer has not tied this race to a logged turn (KI-17), so the turn is not known rather than zero.',
            // Retired by ADR-0026: the trainee's own goal ladder is fetched per character. The strip marks
            // an obligation on the row it belongs to and says where the whole ladder reads.
            'goal' => 'A race marked Goal here is one this trainee\'s own ladder asks of her; the full ladder,'
                .' including the objectives that name no race on this calendar, reads under Trainee goals in'
                .' the Scenario panel (ADR-0026). A race missing the mark is not a race nobody asked of her.',
        ];

        if (! $run->composesPanel('race_calendar')) {
            return [
                'shown' => false,
                'notice' => 'This scenario composes no race calendar, so there is nothing to orient on here. The scenario\'s own panels say what it does compose.',
                'run' => [],
                'this_turn' => null,
                'ahead' => [],
                'absences' => $absences,
                'empty' => ['run' => null, 'this_turn' => null, 'ahead' => null],
                'links' => $links,
            ];
        }

        $next = $run->hasScenario() ? $run->nextTurnToPlay() : null;

        // The trainee's own obligations, keyed on the calendar row each one names (PRD FR-A-10,
        // ADR-0026). One query for the whole region, not one per row: this strip prints three regions and
        // a hundred races, and an N+1 here is a defect rather than a trade-off. Objectives with no slot
        // (a fan gate, an unread one, a race Global has not shipped) carry no key, because there is no
        // calendar row to mark; they still read in the Trainee goals group. That group runs its own query
        // in the same request and both are indexed on (umamusume_id, career_year, turn) - two bounded
        // reads, not one per row.
        /** @var array<int, string> $obligations */
        $obligations = TraineeGoal::query()
            ->where('umamusume_id', $run->umamusume_id)
            ->where('goal_type', '!=', TraineeGoalType::FinalRound->value)
            ->whereNotNull('race_catalog_slot_id')
            ->with('raceCatalogSlot')
            ->get()
            ->mapWithKeys(fn (TraineeGoal $goal): array => [(int) $goal->race_catalog_slot_id => $goal->obligationLine()])
            ->all();

        // The run's own history, oldest logged turn first and unlinked rows last. An array key sorts
        // element-wise, which is how the tie-break on the entry id rides along with the turn. The sort
        // reads the foreign key rather than the relation: the column is `nullable()->nullOnDelete()`
        // (a deleted turn must not delete its race), and testing it is the same answer the relation
        // would give without depending on how the analyzer models a `belongsTo`.
        /** @var list<array<string, mixed>> $recorded */
        $recorded = $run->raceEntries
            ->sortBy(fn (RaceEntry $entry): array => [$entry->turn_entry_id === null ? PHP_INT_MAX : $entry->turnEntry->turn, $entry->id])
            ->map(fn (RaceEntry $entry): array => $this->stripRow($entry, $obligations))
            ->values()
            ->all();

        /** @var Collection<int, RaceCatalogSlot> $offered */
        $offered = $next === null
            ? collect()
            : RaceCatalogSlot::query()
                ->forScenario($run->scenarioKey())
                ->onTurn($next['year'], $next['turn'])
                ->orderBy('sort_order')
                ->get();

        $slotIds = $offered->pluck('id');

        // Null when there is nothing to show, which covers both a run with no turn being decided and
        // a turn the calendar carries no race on. The region's own sentence says which (`empty`).
        $thisTurn = ($next === null || $offered->isEmpty()) ? null : [
            'turn' => $next['turn'],
            'year_label' => RaceCatalogSlot::YEARS[$next['year']] ?? (string) $next['year'],
            'offered' => $offered->count(),
            'recorded' => $run->raceEntries
                ->filter(fn (RaceEntry $entry): bool => $entry->race_catalog_slot_id !== null
                    && $slotIds->contains($entry->race_catalog_slot_id))
                ->map(fn (RaceEntry $entry): array => $this->stripRow($entry, $obligations))
                ->values()
                ->all(),
        ];

        $enteredIds = $run->raceEntries->pluck('race_catalog_slot_id')->filter();

        /** @var list<array<string, mixed>> $ahead */
        $ahead = $next === null ? [] : $this->obligationSlots($run)
            ->reject(fn (RaceCatalogSlot $slot): bool => $slot->isAtOrBeforeTurn($next)
                || $enteredIds->contains($slot->id))
            ->map(fn (RaceCatalogSlot $slot): array => [
                'id' => $slot->id,
                'title' => $slot->title,
                'tier' => $slot->tier,
                'year_label' => $slot->yearLabel(),
                'turn' => $slot->turnNumber(),
                'goal' => $obligations[$slot->id] ?? null,
            ])
            ->values()
            ->all();

        return [
            'shown' => true,
            'notice' => null,
            'run' => $recorded,
            'this_turn' => $thisTurn,
            'ahead' => $ahead,
            'absences' => $absences,
            'empty' => [
                'run' => $recorded === []
                    ? 'No race is recorded for this run yet, so there is no history to read here. Record the turn from the action grid, or open Race Decision to enter one at the turn you are in.'
                    : null,
                'this_turn' => match (true) {
                    $next === null => 'This run has no turn being decided, so there is no turn to read a race against. The Race planner draws the whole calendar.',
                    $offered->isEmpty() => 'No race on this scenario\'s calendar falls at turn '.$next['turn'].'. Open Race Decision to see the turns that do carry one.',
                    default => null,
                },
                'ahead' => match (true) {
                    $next === null => 'Nothing can be placed ahead of a run with no turn being decided.',
                    $ahead === [] => 'No mandatory race is still to come on the calendar this run can read. Obligations already entered are listed under Run, and Race Decision keeps the full obligation list.',
                    default => null,
                },
            ],
            'links' => $links,
        ];
    }

    /**
     * One recorded race, as the strip reads it: the store's own words and figures, nothing derived.
     *
     * `id` is the calendar slot the entry points at, or null for a race the Trainer typed in, which
     * has no catalogue row by definition. `title` falls back exactly as the run record screen's race
     * panel does, so one vocabulary owns the case where neither slot is there.
     *
     * @param  array<int, string>  $obligations  calendar slot id => the trainee's own objective line
     * @return array<string, mixed>
     */
    private function stripRow(RaceEntry $entry, array $obligations): array
    {
        return [
            'id' => $entry->race_catalog_slot_id,
            'entry_id' => $entry->id,
            'title' => $entry->raceCatalogSlot->title ?? $entry->scenarioSlot->title ?? 'a race with no calendar row',
            'tier' => $entry->tierKey(),
            'status' => $entry->status->value,
            'status_label' => $entry->status->label(),
            'placement' => $entry->placement === null ? null : $entry->placementOrdinal(),
            'turn' => $entry->turnEntry?->turn,
            // The obligation prints beside the finish, not instead of it: a race the run has already run
            // keeps its status and still says what the ladder asked (plan §5.2).
            'goal' => $entry->race_catalog_slot_id === null ? null : ($obligations[$entry->race_catalog_slot_id] ?? null),
        ];
    }

    /**
     * The scenario panel's own section (plan §9 E1): the brief's §49 shape, drawn from the
     * composition matrix and the run's stored rows.
     *
     * **No scenario identity crosses the wire.** The section carries a `label` for display and
     * `panels` keyed by flag name and `widgets` as bare string keys; it carries no `key`, `id`,
     * `scenario_key` or `config_key`, so no component downstream *can* branch on one. That is the
     * structural half of G-33 (`D-240`); `ScenarioPanelTest` pins the key list so an identity field
     * cannot arrive unnoticed.
     *
     * **A panel flag is read for every scenario, not just the ones that declare it.** Every key of
     * `panel_labels` is emitted with its own `on` state, so a renderer switches on a flag
     * this matrix owns rather than on the presence of a key. A run that names no scenario gets every
     * flag off: the baseline entry describes URA Finale, and lending its flags to a run that never
     * chose a scenario would state a composition nobody declared.
     *
     * **Widgets are the scenario's own, by the same subtraction the header uses.** The baseline
     * three are the header's figures, so repeating them here would print Turn, Energy and Fans
     * twice on one screen. Labels and values are emitted only for keys `widget_labels` names: a key
     * the matrix does not label is carried with neither map entry, which is what lets the shell fall
     * back and print the key rather than invent a name for it.
     *
     * @param  TeamSectionShape  $team  built once by `show()`, the same array the header's rank reads
     * @return array<string, mixed>
     */
    private function scenarioSection(TrainingRun $run, array $team, ?TurnEntry $latest, Advice $advice): array
    {
        $declared = $run->hasScenario();
        $def = $declared ? (array) config('scenarios.scenarios.'.$run->scenarioKey(), []) : [];

        /** @var array<string, string> $widgetLabels */
        $widgetLabels = (array) config('scenarios.widget_labels', []);
        /** @var array<string, string> $panelLabels */
        $panelLabels = (array) config('scenarios.panel_labels', []);

        $labels = [];
        $values = [];
        $widgets = $this->scenarioWidgets($run);

        foreach ($widgets as $widget) {
            if (! isset($widgetLabels[$widget])) {
                continue;
            }

            $labels[$widget] = $widgetLabels[$widget];
            // A scenario resource with no recorded reading is a named absence rather than a zero: a
            // zero would read as "the run has none", which this tool cannot know (D-220). The rank
            // is the one resource a payload already records, so it fills when it exists.
            $values[$widget] = $widget === 'team_rank' ? $team['rank'] : null;
        }

        $panels = [];

        foreach ($panelLabels as $flag => $label) {
            $panels[$flag] = [
                'label' => $label,
                'on' => $declared && ($def['panels'][$flag] ?? false) === true,
            ];
        }

        // The panel's own sections are composed only for a scenario that turns the flag on, so a
        // cockpit whose matrix says off cannot gain another scenario's rows (gate G-33).
        $goalSections = $panels['career_goals']['on']
            ? $this->careerGoalSections($run)
            : ['objectives' => [], 'alerts' => []];

        return [
            'label' => $declared
                ? (string) ($def['label'] ?? $run->scenarioKey())
                : 'No scenario set',
            'declared' => $declared,
            // The badge condition, and the owner's ruling of 2026-10-07 (plan §4.1 item 6,
            // `SCREEN_SPEC.md` SCR-CAR-019): `partially_documented` drives the badge, not
            // `documented`. `documented` stays a provenance marker recording whether a guide is held;
            // it flipped to `true` on 2026-10-05 when the mechanics read landed, no screen reads it,
            // and a value nothing proves cannot double as the badge condition.
            //
            // `partially_documented` is absent from every other entry, which is the shape "nothing to
            // say" this matrix already uses, so a scenario that declares none is documented.
            'documented' => ($def['partially_documented'] ?? false) !== true,
            'version' => null,
            'version_title' => 'No source defines a Global ruleset version, so this run stores none to print.',
            // Every key the scenario declares, labelled or not: an unlabelled one is what the
            // shell's fallback exists for, and dropping it here would hide the gap instead of
            // drawing it (the plan's "an unrecognised key renders a labelled fallback").
            'widgets' => $widgets,
            'widget_labels' => $labels,
            'widget_values' => $values,
            'widgets_absence' => $labels === []
                ? null
                : 'No column records a scenario resource, so an unrecorded one reads N/A rather than zero.',
            'panels' => $panels,
            // The team system's own facts (E3, plan §9.1): what the run recorded through the team
            // payloads, what config states, and nothing else — the brief's remaining Team Info
            // fields have no writer and stay absences the panel names. The key set is uniform for
            // every scenario so one renderer can draw all of them without branching.
            'team' => $team,
            // §49's four sections beyond the strip. Their content is each panel's own to fill
            // (E2 to E4); the shell carries them empty rather than inventing a row.
            'objectives' => $goalSections['objectives'],
            'actions' => [],
            'alerts' => $goalSections['alerts'],
            'recommendations' => [],
            'recommendations_absence' => 'No source this tool reads states scenario advice, so none is offered here. The advisor ranks the turn you are deciding.',
            // What the scenario's own config entry declares about its finale, and nothing about this
            // run. Corrected 2026-10-11: this comment used to name `ura_finale` as the only entry that
            // declares a `finale` key, and the matrix says otherwise — the single declaring entry is
            // `trackblazer` (`config/scenarios.php`, 'finale' => points_league over three races), so
            // URA, Unity Cup and Our Grand Concert answer null here. `finale_structure` is the declared
            // shape and `finale_state` below is the run's own standing: the key used to sit under the
            // single word `finale`, and Slice 23 separated the two so one word carries one meaning.
            'finale_structure' => $def['finale'] ?? null,
            'finale_state' => FinaleReader::forRun($run),
            'finale_absence' => $this->finaleAbsence($run, $def),
            // The five Trackblazer-specific sections (plan §9 E4). Each is null for any scenario
            // whose matrix does not turn the matching flag on, so the section's key set stays
            // uniform across the four Global scenarios and the ScenarioPanelTest pin holds.
            // The pino legitimate of E4 is that the renderer reads these never by a scenario
            // identity field.
            'grade' => $this->gradeSection($run, $latest),
            'shop' => $this->shopSection($run),
            'epithet' => $this->epithetSection($run),
            'rival' => $this->rivalSection($run),
            'finale_official_title_absence' => $this->finaleOfficialTitleAbsence($run),
            // The baseline strip's own content (plan §9 E6, D-241). `caps` is uniform for every
            // scenario; the two absence keys are the scenario's own line about why its panels are
            // off, null wherever the entry declares none, which is the shape `grade` and `shop` use.
            'caps' => $this->capsSection($run),
            'panel_absence' => isset($def['panel_absence']) ? (string) $def['panel_absence'] : null,
            'panel_absence_title' => isset($def['panel_absence_title']) ? (string) $def['panel_absence_title'] : null,
            // Phase G's three decision surfaces. Each reads the scenario's own config entry, so a
            // scenario that declares none of these keys gets `[]` or `null` and draws nothing: the
            // empties are the matrix's shape, not a branch on a scenario name (gate G-33).
            'milestones' => $this->milestoneSection($run, $latest),
            'duel' => $this->duelSection($run),
            'pre_final' => $this->preFinalSection($run, $latest, $advice),
        ];
    }

    /**
     * The published stat caps, as the baseline strip's own table (plan §9 E6, D-241).
     *
     * `cap` is read through `ScenarioCaps`, the single owner of the base-plus-bonus arithmetic
     * (ADR-0015), so the strip and the header's stat bars cannot disagree about a ceiling. `base` and
     * `bonus` travel as separate terms because the config's own header forbids collapsing them and one
     * JP scenario carries a negative bonus, which a flat cap would erase.
     *
     * The date is the matrix's verification date, not a fetch date: nothing here was fetched on it.
     *
     * @return array{verified_at: string, base: int, hard_cap: int, source_title: string, rows: list<array{key: string, label: string, base: int, bonus: int, cap: int}>}
     */
    private function capsSection(TrainingRun $run): array
    {
        /** @var list<string> $order */
        $order = config('scenarios.stat_order');
        $base = (int) config('scenarios.base_cap');
        // The scenario's own arithmetic, deliberately not `forRun()`: this table's own source line names
        // `config/scenarios.php`, and a ceiling the Trainer read off the client is not in that file. The
        // run's effective bound is what the header's stat bars carry (`stateSection`), so the two surfaces
        // state two different things and stay readable as such (`ADR-0025`).
        $caps = ScenarioCaps::scenarioCaps($run);
        /** @var array<string, mixed> $bonus */
        $bonus = (array) config('scenarios.scenarios.'.$run->scenarioKey().'.cap_bonus', []);
        $verifiedAt = (string) config('scenarios.verified_at');

        $rows = [];

        foreach ($order as $stat) {
            $rows[] = [
                'key' => $stat,
                'label' => $stat,
                'base' => $base,
                'bonus' => (int) ($bonus[$stat] ?? 0),
                'cap' => $caps[$stat],
            ];
        }

        return [
            'verified_at' => $verifiedAt,
            'base' => $base,
            'hard_cap' => (int) config('scenarios.hard_cap'),
            'source_title' => "Published caps from config/scenarios.php, verified {$verifiedAt} against the sources that file names.",
            'rows' => $rows,
        ];
    }

    /**
     * The Trackblazer grade-points meter (plan §9 E4, `x-grade-point-meter` provenance). Built
     * only for a Trackblazer run; null otherwise. Each figure is read through the existing
     * `TrainingRun` helpers so this method is glue, never a re-derivation.
     *
     * The ladder row count is 4, debut + three end-of-year rows from `config('scenarios.scenarios.trackblazer.grade_objectives.standard')`.
     * The dirt-leaning and limited-turf-range tracks the matrix also carries are not the
     * rendered track, because no column tells the renderer which trainee belongs where (KI-15).
     *
     * @return array<string, mixed>|null
     */
    private function gradeSection(TrainingRun $run, ?TurnEntry $latest): ?array
    {
        if ($run->scenarioKey() !== 'trackblazer') {
            return null;
        }

        return [
            'objectives' => $run->gradeObjectives(),
            'current' => $run->current_objective_index,
            'earned' => $run->gradeEarned(),
            'unpriced_count' => $run->gradeUnpricedCount(),
            'unassigned_count' => $run->gradeUnassignedCount(),
            'periods' => $run->gradePeriods(),
            'ladder_source' => 'standard',
            'rotation_turns' => (int) (config('scenarios.scenarios.trackblazer.shop.rotation_turns') ?? 0) ?: null,
            'rotation_resets_in' => $run->shop_resets_in,
            // G5's three planning surfaces. They nest here rather than joining the top-level section
            // because they are readings of the grade ladder, and the section's own shape is what
            // `scenarioSectionKeys()` pins (gate G-33: the matrix, not a scenario name, opens them).
            'planning' => $this->planningSection($run, $latest),
            'calendar' => $this->calendarSection($run),
            'fatigue' => $this->fatigueSection($run),
        ];
    }

    /**
     * The route planner's runway (plan §6): for each end-of-year period, what it asks for, what the
     * races this run has entered would pay if every one of them were won, and what those same races
     * still pay at 2nd and 3rd place.
     *
     * No row here is an outcome. The first figure is config, the second is the Trainer's own entered
     * `race_entries` rows at status `Entered` priced through `TrainingRun::gradePointsForTier()`, and
     * the floors are that second figure scaled by the published modifiers. `ADR-0016` holds the
     * readiness-scoring shape and `PRD.md` §6.11 keeps prediction out, so the surface states what a
     * placement is worth and never what this trainee will place.
     *
     * The entered races the published grade table does not price are counted out and the count is
     * printed: a total that silently omits them would understate itself, which is the same refusal
     * `gradeUnpricedFor()` makes about finished races.
     *
     * @return array<string, mixed>
     */
    private function planningSection(TrainingRun $run, ?TurnEntry $latest): array
    {
        /** @var array<string, mixed> $def */
        $def = (array) (config('scenarios.scenarios.trackblazer') ?? []);
        /** @var array<string, int> $modifiers */
        $modifiers = (array) ($def['grade_point_placement_modifiers'] ?? []);
        /** @var array<string, mixed> $planning */
        $planning = (array) ($def['planning'] ?? []);
        /** @var list<string> $floorPlacements */
        $floorPlacements = (array) ($planning['floor_placements'] ?? []);
        $threshold = (int) ($planning['density_threshold'] ?? 0);

        $entries = $run->raceEntries;
        $entries->loadMissing('raceCatalogSlot');

        /** @var array<int, int> $planByPeriod */
        $planByPeriod = [];
        /** @var array<int, int> $uncounted */
        $uncounted = [];
        $unattributed = 0;

        foreach ($entries as $entry) {
            if ($entry->status !== RaceEntryStatus::Entered) {
                continue;
            }

            if ($entry->objective_index === null) {
                $unattributed++;

                continue;
            }

            $tier = $entry->tierKey();
            $price = $tier === null ? null : $run->gradePointsForTier($tier);

            if ($price === null) {
                $uncounted[$entry->objective_index] = ($uncounted[$entry->objective_index] ?? 0) + 1;

                continue;
            }

            $planByPeriod[$entry->objective_index] = ($planByPeriod[$entry->objective_index] ?? 0) + $price;
        }

        $secondLabel = $floorPlacements[0] ?? '';
        $thirdLabel = $floorPlacements[1] ?? '';
        $secondModifier = (int) ($modifiers[$secondLabel] ?? 0);
        $thirdModifier = (int) ($modifiers[$thirdLabel] ?? 0);

        $floorTitle = 'The two figures a plan still pays at the placements this row names: the total '
            .$secondLabel.' ('.$secondModifier.'% of a win) and the total at '.$thirdLabel.' ('
            .$thirdModifier.'% of a win). Both are arithmetic on the published modifiers in '
            .'config(\'scenarios.scenarios.trackblazer.grade_point_placement_modifiers\'), which is '
            .'uma.guide\'s table at docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md lines 104-112 '
            .'(the page the master dates 2026-04-29). It is a lower bound at a stated placement, not a '
            .'guess at a result, and no earned total in this tool reads these modifiers: the other '
            .'publisher in the same master names no ratio (line 355), which is why '
            .'`RaceEntry::gradePointsFor()` still prices a 1st place alone.';

        $current = $run->current_objective_index;

        $periods = [];

        foreach ($run->gradeObjectives() as $objective) {
            $index = (int) $objective['index'];

            // The debut row asks for no points, so it has no runway to plan.
            if ($index < 2) {
                continue;
            }

            $plan = $planByPeriod[$index] ?? null;

            $periods[] = [
                'index' => $index,
                'name' => (string) $objective['name'],
                'needed' => number_format((int) $objective['required']),
                'needed_badge' => 'confirmed',
                'needed_title' => 'The standard track in config(\'scenarios.scenarios.trackblazer.grade_objectives\'), '
                    .'transcribed from docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md lines 84-89 '
                    .'and 342-349. Only the standard track is printed: no column says which trainee belongs '
                    .'on the dirt-leaning or limited-turf-range one, which is KI-15.',
                'is_current' => $current === $index,
                'plan' => $plan === null ? null : number_format($plan),
                'plan_badge' => $plan === null ? null : 'calculated',
                'plan_title' => $plan === null
                    ? 'No race is entered against this period yet, so there is no plan to total. The Trainer '
                        .'enters a race from the Race Decision screen and names the period it is for.'
                    : 'The sum of the entered races attributed to this period, each at what a 1st place pays '
                        .'for its grade in config(\'scenarios.scenarios.trackblazer.grade_point_by_grade\') '
                        .'(docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md lines 94-102). A race that has '
                        .'already been run is history, not plan, and is left out.',
                'uncounted' => $uncounted[$index] ?? 0,
                'uncounted_reason' => ($uncounted[$index] ?? 0) > 0
                    ? 'An entered race whose grade no published table prices is left out of that total rather '
                        .'than priced at a guess.'
                    : null,
                'uncounted_title' => 'How many entered races in this period have no published Grade Point value '
                    .'for their grade, the same wording the meter above uses for a finished race. They are left '
                    .'out of the total rather than guessed at, and a total that silently omitted them would '
                    .'understate the plan.',
                'floor_second' => $plan === null ? null : number_format(intdiv($plan * $secondModifier, 100)),
                'floor_third' => $plan === null ? null : number_format(intdiv($plan * $thirdModifier, 100)),
                'floor_label' => $secondLabel.' / '.$thirdLabel,
                'floor_badge' => $plan === null ? null : 'calculated',
                'floor_title' => $floorTitle,
            ];
        }

        /** @var array<string, mixed> $rivals */
        $rivals = (array) ($def['rival_races'] ?? []);
        /** @var list<array<string, mixed>> $gates */
        $gates = (array) ($def['year_end_gates'] ?? []);
        $gate = $current === null ? null : ($gates[$current - 2] ?? null);
        $fans = $latest?->fans;

        $yearEnd = null;

        if (is_array($gate)) {
            $needed = (int) $gate['fans'];
            $shortfall = $fans === null ? null : max(0, $needed - (int) $fans);

            $yearEnd = [
                'gate_label' => (string) $gate['label'],
                'timing' => (string) $gate['timing'],
                'fans_needed' => number_format($needed),
                'fans_needed_badge' => 'confirmed',
                'fans_needed_title' => 'The fan figure two publishers in this master agree on: '
                    .'docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md lines 306-310 (uma.guide) and the '
                    .'GameWith row at line 1235 both print 5,000, 60,000 and 120,000 at the three assessments.',
                'fans_recorded' => $fans === null ? null : number_format((int) $fans),
                'fans_recorded_badge' => $fans === null ? null : 'confirmed',
                'fans_recorded_title' => $fans === null
                    ? 'No turn entry for this run records a fan count, so the reading is unknown rather than zero.'
                    : 'The fan count on the run\'s most recent turn entry, the same reading the header and the '
                        .'fan ladder use.',
                'fans_shortfall' => $shortfall === null ? null : number_format($shortfall),
                'fans_shortfall_badge' => $shortfall === null ? null : 'calculated',
                'fans_shortfall_title' => 'The published figure minus the recorded reading, floored at zero. '
                    .'Nothing about the assessment itself is estimated: it is the gap between two figures this '
                    .'run has, not a forecast of how it will place.',
                'bond_figures' => implode(' / ', array_map(
                    static fn (array $row): string => (string) $row['bond_uma_guide'],
                    $gates
                )).' (uma.guide) · '.implode(' / ', array_map(
                    static fn (array $row): string => (string) $row['bond_game_with'],
                    $gates
                )).' (GameWith)',
                'bond_recorded' => null,
                'bond_title' => 'The Director\'s bond level has no column: `turn_events` stores an unattributed '
                    .'bond delta with no starting value, so this run\'s bond reading cannot be taken and the '
                    .'comparison is not printed. The two figures above are both published and neither settles '
                    .'the other: docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md lines 306-310 against '
                    .'the GameWith row at 1235, which docs/Trackblazer-plan.md:361 rules only as single-source.',
            ];
        }

        $flags = [];
        $longest = $this->longestEnteredRaceChain($entries);

        if ($threshold > 0 && $longest >= $threshold) {
            $flags[] = [
                'kind' => 'race_density',
                'label' => $longest.' consecutive races with no rest turn between them (fatigue risk)',
                'badge' => 'calculated',
                'title' => 'Counted over the races this run has entered that point at a catalogue row, on '
                    .'consecutive turns of the career grid. It reads the entered plan only: what the client '
                    .'offers on a turn this run has not entered is not a race the Trainer chose, and the '
                    .'threshold is config(\'scenarios.scenarios.trackblazer.planning.density_threshold\'). '
                    .'Fatigue keys on this same length, which is why the flag names it.',
            ];
        }

        if ($yearEnd !== null) {
            $flags[] = [
                'kind' => 'year_end_gate',
                'label' => 'Year-end gate, '.$yearEnd['gate_label'].': '.$yearEnd['fans_needed']
                    .' fans and the Director\'s bond',
                'badge' => 'confirmed',
                'title' => 'The Late December assessment that levels up the Unique Skill. Its fan figure is '
                    .'published by two sources and its bond figure by two disagreeing ones; the bond half of '
                    .'this gate has no reading to compare against.',
            ];
        }

        $flags[] = [
            'kind' => 'rival_races',
            'label' => 'Rival races cannot be scheduled',
            'badge' => 'confirmed',
            'title' => 'They appear on their own: '
                .(string) ($rivals['first_offered'] ?? 'not recorded in this corpus')
                .' and at random after that (docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md line 194). '
                .'A plan that listed one would be a plan for something the client has not offered, so no row '
                .'of this surface claims a rival race.',
        ];

        return [
            'periods' => $periods,
            // Prominent while the first checkpoint is still ahead of the Trainer; folded to the
            // periods that still have a plan to make once it is behind them.
            'collapses_after_period' => (int) ($planning['collapses_after_period'] ?? 0),
            'flags' => $flags,
            'year_end' => $yearEnd,
            'unattributed' => $unattributed,
            'unattributed_title' => 'Entered races this run has not attributed to a period. They count toward '
                .'no runway row above, and the grade meter prints the same figure as "unassigned".',
            'routes_pointer' => 'Route progress is the Epithet routes surface\'s own list, printed there once.',
        ];
    }

    /**
     * The longest run of entered races on consecutive turns of the career grid.
     *
     * A career year is a fixed number of turns, so one integer orders the two the same way
     * `RaceCatalogSlot::isAtOrBeforeTurn()` does, and the Late December into Early January step counts
     * as consecutive because on the grid it is.
     *
     * @param  Collection<int, RaceEntry>  $entries
     */
    private function longestEnteredRaceChain(Collection $entries): int
    {
        $positions = [];

        foreach ($entries as $entry) {
            if ($entry->status !== RaceEntryStatus::Entered) {
                continue;
            }

            $slot = $entry->raceCatalogSlot;

            if ($slot === null || $slot->turn === null) {
                continue;
            }

            $positions[(int) $slot->year.'-'.(int) $slot->turn] =
                (int) $slot->year * TrainingRun::TURNS_PER_YEAR + (int) $slot->turn;
        }

        $positions = array_values($positions);
        sort($positions);

        $longest = 0;
        $streak = 0;
        $previous = null;

        foreach ($positions as $position) {
            $streak = $previous === $position - 1 ? $streak + 1 : 1;
            $longest = max($longest, $streak);
            $previous = $position;
        }

        return $longest;
    }

    /**
     * The three races this run has coming up, with what each pays and what skipping it costs
     * (plan §17).
     *
     * This is not the rival block above. That one lists the scenario's catalogue as a reference and
     * caps at eight rows; this one is windowed against the turn the run is actually deciding, drops
     * rows the run already entered, and carries the two figures a race is worth. The matrix keeps
     * `race_calendar` off for Trackblazer (`config/scenarios.php`), so `raceStripSection()` prints its
     * "ahead" region for the scenarios that do compose a calendar and nothing here — this surface is
     * the only place a Trackblazer run sees what is still to come.
     *
     * The values are published rules, not this run's prospects: a win at G2 pays 80 whether or not
     * this trainee can win it, which is exactly why no row here compares her aptitudes to the race
     * (`ADR-0016`, `PRD.md` §6.11).
     *
     * @return array<string, mixed>
     */
    private function calendarSection(TrainingRun $run): array
    {
        /** @var array<string, mixed> $def */
        $def = (array) (config('scenarios.scenarios.trackblazer') ?? []);
        /** @var array<string, int> $gradePoints */
        $gradePoints = (array) ($def['grade_point_by_grade'] ?? []);
        /** @var array<string, int> $coins */
        $coins = (array) ($def['shop_coins_by_placement'] ?? []);
        /** @var array<string, mixed> $planning */
        $planning = (array) ($def['planning'] ?? []);
        /** @var list<array<string, mixed>> $routes */
        $routes = (array) ($def['epithet_routes'] ?? []);
        $window = (int) ($planning['calendar_window'] ?? 0);

        $next = $run->nextTurnToPlay();
        $enteredIds = $run->raceEntries->pluck('race_catalog_slot_id')->filter();

        /** @var Collection<int, RaceCatalogSlot> $ahead */
        $ahead = RaceCatalogSlot::query()
            ->forScenario($run->scenarioKey())
            ->orderBy('year')
            ->orderBy('turn')
            ->orderBy('sort_order')
            ->get()
            ->reject(fn (RaceCatalogSlot $slot): bool => $slot->isAtOrBeforeTurn($next)
                || $enteredIds->contains($slot->id));

        $firstCoin = $coins['1st'] ?? null;
        $now = $next === null ? null : $next['year'] * TrainingRun::TURNS_PER_YEAR + $next['turn'];

        $rows = $next === null ? [] : $ahead->take($window)
            ->map(function (RaceCatalogSlot $slot) use ($gradePoints, $firstCoin, $routes, $now): array {
                $gradePointsWin = $slot->tier === null ? null : ($gradePoints[$slot->tier] ?? null);

                $epithets = array_values(array_filter(array_map(
                    static fn (array $row): ?string => in_array($slot->title, (array) ($row['races'] ?? []), true)
                        ? (string) $row['epithet']
                        : null,
                    $routes
                )));

                $forgone = [];

                if ($gradePointsWin !== null) {
                    $forgone[] = 'up to '.number_format($gradePointsWin).' Grade Points';
                }

                if ($firstCoin !== null) {
                    $forgone[] = number_format($firstCoin).' Shop Coins';
                }

                $consequence = 'Skipping this race forgoes '.($forgone === []
                    ? 'the fan gain and a turn of racing, which is all this row can say'
                    : implode(' and ', $forgone))
                    .($epithets === []
                        ? '.'
                        : ', and leaves '.$slot->title.' unclaimed for '.implode(', ', $epithets).'.');

                return [
                    'title' => $slot->title,
                    'tier' => $slot->tier,
                    'year_label' => $slot->yearLabel(),
                    'turn' => $slot->turnNumber(),
                    'turns_away' => $now === null || $slot->turn === null
                        ? null
                        : (string) ((int) $slot->year * TrainingRun::TURNS_PER_YEAR + (int) $slot->turn - $now),
                    'distance' => $slot->distanceLabel(),
                    'surface' => $slot->surface,
                    'surface_title' => $slot->surface === null
                        ? 'The catalogue row this race is stored on carries no surface, so none is printed.'
                        : 'Metres and surface are the catalogue row\'s own columns, printed as stored.',
                    'grade_points_win' => $gradePointsWin === null ? null : number_format($gradePointsWin),
                    'grade_points_badge' => $gradePointsWin === null ? null : 'confirmed',
                    'grade_points_title' => $gradePointsWin === null
                        ? 'No published Grade Point value prices this grade, so no figure is printed for it. '
                            .'A race with no tier is common: the catalogue keeps a grade only where a source '
                            .'corroborated it per row (R75).'
                        : 'What a 1st place at this grade pays, from '
                            .'config(\'scenarios.scenarios.trackblazer.grade_point_by_grade\'), transcribed from '
                            .'docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md lines 94-102 and 357-363.',
                    'coins_win' => $firstCoin === null ? null : number_format($firstCoin),
                    'coins_badge' => $firstCoin === null ? null : 'confirmed',
                    'coins_title' => 'Shop Coins are paid by placement alone: the same master says at line 372 '
                        .'that a Pre-OP win and a G1 win both pay 100 for 1st place, so this figure does not '
                        .'move with the grade above. The plan\'s illustrative §17 rows print different coin '
                        .'figures per grade; the published table wins.',
                    'skip_consequence' => $consequence,
                    'skip_badge' => 'calculated',
                    'skip_title' => 'The published figures a race that is not run cannot pay, added to this row. '
                        .'"Up to" is because a 1st place is the only placement the grade table prices: '
                        .'what a 4th place would have paid is the gap KI-10 leaves open.',
                ];
            })
            ->values()
            ->all();

        return [
            'window' => $window,
            'turns' => $next === null ? null : [
                'year' => $next['year'],
                'year_label' => RaceCatalogSlot::YEARS[$next['year']] ?? (string) $next['year'],
                'turn' => $next['turn'],
            ],
            'rows' => $rows,
            'absence' => match (true) {
                $next === null => 'This run has no turn being decided, so nothing can be placed ahead of it. '
                    .'A career with every turn logged has no next turn on the grid.',
                $rows === [] => 'No race is left ahead of turn '.$next['turn'].' on this scenario\'s calendar that '
                    .'this run has not already entered. The catalogue a run reads is the wizard\'s optional seed '
                    .'set, so an empty list here is a local database with no rows rather than a season with no '
                    .'races (R39).',
                default => null,
            },
        ];
    }

    /**
     * The race-fatigue reading (plan §10): the chain the Trainer reported, the row the publisher
     * labels with it, the two caveats that row leaves open, and the fixed recovery events.
     *
     * The chain length is entered, never derived. `TrainingRun::consecutiveRaceCount()` returns null
     * by design: a turn with no race logged is "the Trainer has not named this turn a race", which is
     * not the same statement as "this turn rested" (D-270). So an unreported chain prints its reason
     * and no row is read.
     *
     * The caveats are display copy rather than a tooltip because a caveat a Trainer has to hover for
     * is a caveat they never read.
     *
     * @return array<string, mixed>
     */
    private function fatigueSection(TrainingRun $run): array
    {
        /** @var array<string, mixed> $fatigue */
        $fatigue = (array) (config('scenarios.scenarios.trackblazer.race_fatigue') ?? []);
        /** @var list<string> $labels */
        $labels = (array) ($fatigue['chain_labels'] ?? []);
        /** @var array<string, array<string, string>> $odds */
        $odds = (array) ($fatigue['odds'] ?? []);

        $reported = $run->latestFatigue();
        $chain = $reported?->consecutiveRaces;
        $label = $chain === null ? null : ($labels[min($chain, 4) - 1] ?? null);

        return [
            'chain' => $chain,
            'chain_label' => $label,
            'chain_badge' => $chain === null ? null : 'confirmed',
            'chain_title' => $chain === null
                ? 'The Trainer has not entered a consecutive-race count on any turn of this run, so no chain '
                    .'length is shown and no row is read. The count is entered on the turn it applies to; a turn '
                    .'with no race logged is not a rest this tool was told about.'
                : 'The count the Trainer reported on a `turn_events` row of this run. Nothing here derives it: '
                    .'`TrainingRun::consecutiveRaceCount()` returns null rather than reading a rest off an '
                    .'unlogged turn.',
            'row' => $label === null ? null : ($odds[$label] ?? null),
            'row_title' => $label === null
                ? 'No chain, so no row. The table prints in full below rather than one column being guessed at.'
                : 'The row this publisher labels '.$label.'. Odds are published only by uma.guide '
                    .'(docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md lines 231-236, a page the master '
                    .'dates 2026-04-29); the other Trackblazer section in the same master names no odds, so '
                    .'one source is not two.',
            'table' => $odds,
            'caveats' => array_values((array) ($fatigue['caveats'] ?? [])),
            'recovery' => array_values((array) ($fatigue['recovery_events'] ?? [])),
            'recovery_badge' => 'confirmed',
            'recovery_title' => 'Guaranteed Energy and Mood events, from '
                .(string) ($fatigue['recovery_source'] ?? 'the publisher master'),
        ];
    }

    /**
     * The Trackblazer Pro Shop (plan §9 E4, `x-shop-panel` provenance).
     *
     * The catalogue renders in `shop_items` order — verbatim from config, transcribed from
     * `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Full Shop Item List".
     * No `recommended` field exists: the rotation is not modelled (plan §3 knowledge
     * groundings), and a "best value" sort or default selection would state a state of a
     * lineup this tool cannot see (D-256).
     *
     * Purchases are events, not a column (`ShopPurchasePayload` shape pinned in §3 + ADR-0003).
     * `fill` and `select_action` are the existing `runs.purchases.store` and `runs.update`
     * routes — the Vue form posts to them and `StoreShopPurchaseRequest` is the boundary.
     *
     * @return array<string, mixed>|null
     */
    private function shopSection(TrainingRun $run): ?array
    {
        if ($run->scenarioKey() !== 'trackblazer') {
            return null;
        }

        $def = (array) (config('scenarios.scenarios.trackblazer.shop') ?? []);

        $catalogue = collect((array) config('scenarios.scenarios.trackblazer.shop_items'))
            ->map(fn (array $row): array => [
                'name' => (string) $row['name'],
                'cost' => (int) ($row['cost'] ?? 0),
                'effect' => (string) ($row['effect'] ?? ''),
            ])
            ->values()
            ->all();

        $purchases = $this->trackblazerPurchases($run);

        return [
            'catalogue' => $catalogue,
            'purchases' => $purchases,
            'rotation_turns' => (int) ($def['rotation_turns'] ?? 0) ?: null,
            'rotation_resets_in' => $run->shop_resets_in,
            'max_copies' => (int) ($def['max_copies_per_item'] ?? 0),
            'spend_total' => $run->composesShop() ? $run->shopSpendTotal() : 0,
            'fill' => route('runs.purchases.store', $run),
            'select_action' => route('runs.update', $run),
            // G6's ledger. It nests under `shop` because these are shop facts, and the exhaustive key
            // assertion in `TrackblazerPanelTest` is updated in the same change so the contract move is
            // test-visible rather than silent.
            'ledger' => $this->ledgerSection($run, $purchases),
        ];
    }

    /**
     * The shop ledger (G6): what this run holds, the cap each holding sits against, the one publisher's
     * worth ratings, and its Summer reserve.
     *
     * **What it does not print, on purpose.** The catalogue, the purchases list, the purchase form and
     * the spend total all render in this arm already, and so does the coin balance's absence ("Shop
     * Coins: not yet recorded"). A ledger that repeated any of them would be the second surface for one
     * fact that correction 27 exists to prevent, so it carries only what the arm lacks: a held count per
     * item, the cap, the publisher's ratings, and an inflow projection.
     *
     * **Nothing here is this tool's advice.** The ratings and the reserve are uma.guide's own words
     * (config `shop_worth`, `shop_reserve`, each citing the master), they travel as `estimated`, and the
     * arm's sentence "no row is recommended" stays true: the tool recommends nothing and reports what one
     * publisher said.
     *
     * **No projected cost.** Nothing writes a planned purchase, so a cost projection would be invented;
     * it is reported as an owed writer and the row is absent.
     *
     * @param  list<array{item: string, cost: int, effect: string, turn: int}>  $purchases
     * @return array<string, mixed>
     */
    private function ledgerSection(TrainingRun $run, array $purchases): array
    {
        /** @var array<string, mixed> $def */
        $def = (array) (config('scenarios.scenarios.trackblazer') ?? []);
        /** @var array<string, mixed> $shop */
        $shop = (array) ($def['shop'] ?? []);
        /** @var array<string, string> $worth */
        $worth = (array) ($def['shop_worth'] ?? []);
        /** @var array<string, mixed> $reserve */
        $reserve = (array) ($def['shop_reserve'] ?? []);
        /** @var array<string, int> $coinTable */
        $coinTable = (array) ($def['shop_coins_by_placement'] ?? []);

        $cap = (int) ($shop['max_copies_per_item'] ?? 0);
        $coinWin = (int) ($coinTable['1st'] ?? 0);

        /** @var array<string, int> $held */
        $held = [];

        foreach ($purchases as $purchase) {
            $held[$purchase['item']] = ($held[$purchase['item']] ?? 0) + 1;
        }

        // Catalogue order, so the list does not reshuffle as purchases land.
        $items = [];

        foreach ((array) config('scenarios.scenarios.trackblazer.shop_items') as $row) {
            $name = (string) $row['name'];

            if (! isset($held[$name])) {
                continue;
            }

            $items[] = [
                'name' => $name,
                'held' => $held[$name],
                'cap' => $cap,
                'worth' => $worth[$name] ?? null,
                'worth_badge' => isset($worth[$name]) ? 'estimated' : null,
                'worth_title' => isset($worth[$name])
                    ? 'One publisher\'s rating rather than a game rule: '.(string) ($def['shop_worth_source'] ?? '')
                    : 'The publisher does not rate this item, so no rating is printed rather than one inferred '
                        .'from a nearby row.',
            ];
        }

        $planned = $run->raceEntries
            ->filter(fn (RaceEntry $entry): bool => $entry->status === RaceEntryStatus::Entered)
            ->count();

        return [
            'items' => $items,
            'items_absence' => $items === []
                ? 'No purchase is recorded for this run yet, so nothing is held. Record one from the form above.'
                : null,
            'cap' => $cap,
            'inflow' => $planned === 0 ? null : number_format($planned * $coinWin),
            'inflow_badge' => $planned === 0 ? null : 'calculated',
            'inflow_title' => $planned === 0
                ? 'No race is entered for this run yet, so there is nothing to project an inflow from. The '
                    .'plan above shows the races the Trainer has entered.'
                : 'The races this run has entered, each at the published 1st-place payout of '
                    .number_format($coinWin).' coins from config(\'scenarios.scenarios.trackblazer.'
                    .'shop_coins_by_placement\'), which the master says does not move with race grade '
                    .'(docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md line 372). A projection from '
                    .'the entered plan, not a promise: it assumes every one of them is won.',
            'reserve' => $reserve === [] ? null : [
                'label' => (string) ($reserve['label'] ?? ''),
                'value' => number_format((int) ($reserve['coins'] ?? 0)),
                'badge' => 'estimated',
                'title' => 'The publisher\'s own tip rather than a rule this tool derives: '
                    .(string) ($def['shop_reserve_source'] ?? ''),
            ],
            'rated_count' => count($worth),
            'rated_title' => 'How many catalogue items carry a rating from the publisher. An item with no '
                .'rating prints none, and an item this run does not hold is not listed here at all.',
        ];
    }

    /**
     * The recorded purchases, read through the same payload path the Blade form used. Each row
     * carries the turn the Trainer recorded, which `ShopPurchasePayload` itself does not hold.
     *
     * @return list<array{item: string, cost: int, effect: string, turn: int}>
     */
    private function trackblazerPurchases(TrainingRun $run): array
    {
        return $run->turnEvents()
            ->where('event_type', TurnEventType::Scenario->value)
            ->get()
            ->filter(fn (TurnEvent $event): bool => $event->purchasePayload() !== null)
            ->map(fn (TurnEvent $event): array => [
                'item' => (string) $event->purchasePayload()->item,
                'cost' => (int) $event->purchasePayload()->cost,
                'effect' => (string) $event->purchasePayload()->effect,
                'turn' => (int) $event->turn,
            ])
            ->values()
            ->all();
    }

    /**
     * The epithet checklist (plan §9 E4, `x-epithet-checklist` provenance). Returns the rows
     * from `TrainingRun::epithetProgress()` so the renderer never recomputes the three states.
     *
     * @return array<string, mixed>|null
     */
    private function epithetSection(TrainingRun $run): ?array
    {
        if ($run->scenarioKey() !== 'trackblazer') {
            return null;
        }

        return [
            'rows' => $run->epithetProgress(),
            'seen_titles' => $run->completedRaceTitles(),
        ];
    }

    /**
     * The rival / race-schedule facts Trackblazer draws on directly. The matrix carries
     * `race_calendar => false` (Trackblazer has no mandatory calendar), but rival races still
     * need to render as rows the panel can show.
     *
     * @return array<string, mixed>|null
     */
    private function rivalSection(TrainingRun $run): ?array
    {
        if ($run->scenarioKey() !== 'trackblazer') {
            return null;
        }

        $rows = RaceCatalogSlot::query()
            ->forScenario('trackblazer')
            ->orderBy('year')
            ->orderBy('turn')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (RaceCatalogSlot $slot): array => [
                'title' => (string) $slot->title,
                'year_label' => $slot->yearLabel(),
                'turn' => $slot->turnNumber(),
                'tier' => $slot->tier,
            ])
            ->values()
            ->all();

        return [
            'rows' => $rows,
            // The race catalog is the wizard's optional seed set, so an empty read is the
            // normal shape on a fresh database (R39). Saying so keeps the panel honest.
            'absence' => $rows === []
                ? 'No Trackblazer race is in the local calendar; rival rows appear once a row is added.'
                : null,
        ];
    }

    /**
     * The points-league finale reads as "Trackblazer finale"; any client name is a named absence.
     *
     * "Twinkle Star Climax" is a §7 conflict row 31 UNVERIFIED name in the reference guide,
     * so it is not printed. A screen-reader citation carries the row.
     */
    private function finaleOfficialTitleAbsence(TrainingRun $run): ?string
    {
        if ($run->scenarioKey() !== 'trackblazer') {
            return null;
        }

        return 'No published client name. "Twinkle Star Climax" is §7 conflict row 31 of the reference guide, marked UNVERIFIED, so the export label "Trackblazer" stands until the guide publishes a client string.';
    }

    /**
     * Why no finale structure is shown, where the two answers are different claims.
     *
     * The config's `finale` key describes a composition the shell could draw. An absent key has never
     * meant the career has no finale, and for the four `[Global]` scenarios it would be false: each
     * carries exactly one scenario-scoped row in the finale block
     * (`GametoraRaceCatalogParser::GLOBAL_FINALS_BY_SLOT`), and Our Grand Concert's is the row KI-71
     * corrected to `our_grand_concert` at `480b711`. Telling that Trainer the scenario "declares no
     * finale structure" reads as though the career ends without an ending, which is the false-absence
     * shape the bar's disclosure rule refuses (D-220).
     *
     * The row's presence is read from the catalogue rather than from the config entry, because the
     * catalogue is where the fact lives and `config/scenarios.php` stays the only place a scenario
     * name enters the layout path (D-240). No `finale` key is invented to make the sentence pass:
     * nothing reads that key's contents today, so a value there would be dead config.
     *
     * What is shown is still nothing beyond the calendar row, and the sentence says so.
     *
     * @param  array<string, mixed>  $def
     */
    private function finaleAbsence(TrainingRun $run, array $def): ?string
    {
        if (isset($def['finale'])) {
            return null;
        }

        if (! $run->hasScenario()) {
            return 'This scenario declares no finale structure, so none is shown.';
        }

        $onCalendar = RaceCatalogSlot::query()
            ->where('scenario_key', $run->scenarioKey())
            ->where('year', RaceCatalogSlot::YEAR_FINALE)
            ->exists();

        return $onCalendar
            ? 'The finale is on the career calendar as a mandatory race; this scenario declares no structure beyond it, so nothing further is drawn.'
            : 'This scenario declares no finale structure, so none is shown.';
    }

    /**
     * The career goals panel's own sections: the mandatory races this scenario's calendar records,
     * and the two modules this repository holds no data for (plan §9 E2, SCREEN-014).
     *
     * A row is either a reading with a state, or an unrecorded value whose `absence` says why it is
     * absent. The state vocabulary is the brief's four (`completed`, `current`, `upcoming`,
     * `missed`), and the calendar position is never re-derived here: `RaceCatalogSlot::isAtOrBeforeTurn()`
     * is the same owner the race strip above already reads (ADR-0015), so the two regions cannot
     * disagree about whether a turn has arrived.
     *
     * Two rows are the same thing wearing different shapes, and that is the point of stating it: the
     * finale block carries no turn in the catalogue, so its position is the first instant of the
     * fourth year and it can be `current` but can never be `missed`, and it raises no alert because
     * the alert this screen is allowed to make is about a *recorded* turn that has arrived with
     * nothing logged. A window length appears nowhere in the corpus, so none is invented.
     *
     * @return array{objectives: list<array<string, mixed>>, alerts: list<array<string, mixed>>}
     */
    private function careerGoalSections(TrainingRun $run): array
    {
        $next = $run->hasScenario() ? $run->nextTurnToPlay() : null;

        /** @var Collection<int, RaceCatalogSlot> $slots */
        $slots = RaceCatalogSlot::query()
            ->forScenario($run->scenarioKey())
            ->where('is_mandatory', true)
            ->orderBy('year')
            ->orderBy('turn')
            ->orderBy('sort_order')
            ->get();

        // A hand-entered race has no slot id, so it cannot settle a mandatory row. Reading the
        // foreign key rather than the relation is the same choice the race strip makes, for the same
        // reason: a deleted turn must not delete its race, so the column is the fact that survives.
        $entered = $run->raceEntries->pluck('race_catalog_slot_id')->filter();

        $rows = [];
        $alerts = [];

        foreach ($slots as $slot) {
            $recorded = $entered->contains($slot->id);
            $turn = $slot->turnNumber();
            $reached = $slot->isAtOrBeforeTurn($next);

            $state = match (true) {
                $recorded => 'completed',
                $next === null => 'upcoming',
                $turn !== null && $turn === $next['turn'] && (int) $slot->year === (int) $next['year'] => 'current',
                $turn === null => $reached ? 'current' : 'upcoming',
                $reached => 'missed',
                default => 'upcoming',
            };

            // A race the run already has needs no door back to the picker.
            $rows[] = [
                'label' => (string) $slot->title,
                'state' => $state,
                'year_label' => $slot->yearLabel(),
                'turn' => $turn,
                'race_url' => $recorded ? null : route('runs.races.decision', $run),
                'absence' => $turn === null
                    ? 'The calendar records no turn for this race, only its career year, so no turn is printed.'
                    : null,
            ];

            // A race alerts only in the career year the run is standing in. `isAtOrBeforeTurn()` is an
            // ordinal test, so an unrecorded Junior race stays "reached" for the rest of the career and a
            // Senior-year run would banner its first-year debut every turn. The ladder row keeps its
            // `missed` state; only the alert is scoped, because the alert claims a race is still due.
            if (! $recorded && $reached && $turn !== null && $run->status === RunStatus::Active
                && (int) $slot->year === (int) $next['year']) {
                $alerts[] = [
                    'level' => 'Critical',
                    'text' => $slot->title.' is due and not recorded',
                    'detail' => 'The calendar puts it at '.$slot->yearLabel().' turn '.$turn
                        .', and the next turn to play is '.$next['turn'].'.',
                ];
            }
        }

        /** @var array{rows: list<array<string, mixed>>, alerts: list<array<string, mixed>>, summary: string|null, empty_note: string|null} $trainee */
        $trainee = $this->traineeGoalRows($run, $next, $slots);

        $objectives = [
            [
                'group' => 'Trainee goals',
                'rows' => $trainee['rows'],
                'summary' => $trainee['summary'],
                'empty_note' => $trainee['empty_note'],
            ],
            [
                'group' => 'Mandatory races',
                'rows' => $rows,
                'summary' => null,
                'empty_note' => null,
            ],
        ];

        // Happy Meek is URA's own mechanic, so it composes for URA alone. The switch that turned
        // `career_goals` on for Unity Cup and the Grand Concert must not hand those cockpits a module
        // whose rows are all named absences of a mechanic their scenario does not have. Same guard
        // shape the `trackblazer` composers use, and the panel reads the group list as data, so no
        // scenario name reaches a component (gate G-33).
        if ($run->scenarioKey() === 'ura_finale') {
            $objectives[] = [
                'group' => 'Happy Meek',
                'rows' => $this->happyMeekRows(),
                'summary' => null,
                'empty_note' => null,
            ];
        }

        return [
            'objectives' => $objectives,
            // The trainee's own overdue objectives lead, the way every alert here leads the groups.
            'alerts' => [...$trainee['alerts'], ...$alerts],
        ];
    }

    /**
     * The trainee's own objectives, as the sourced ladder states them and this run has recorded them.
     *
     * Progress is **derived, never stored**: a race objective is met by a completed entry at the same
     * calendar row finishing where the source asked, and a fan objective by the fan figure the run had
     * already reached at or before the deadline turn. There is no write path for either, so no second
     * owner exists for a fact the race record and the turn log already answer.
     *
     * Two rules the panel cannot see from here:
     *   - an objective whose calendar row is scenario-wide mandatory raises **no alert of its own**. The
     *     Mandatory races group alerts on it, and the debut is both facts at once: the export makes it
     *     objective #1 for every trainee while the capture corpus says the client banners no pennant on
     *     it (`RACE-AND-SLICE-RESEARCH.md:287-290`, carried to `SCREEN_SPEC.md` §7). One obligation, one
     *     alert, whichever way that question is settled.
     *   - an objective the source marks unreleased on `[Global]` renders as the source naming a race this
     *     calendar does not carry, and never as a Global obligation.
     *
     * The scenario's three final rounds are deliberately absent. They are stored as `FinalRound` rows and
     * are the obligation `race_catalog_slots.is_mandatory` already prints, so listing them here would show
     * one demand twice.
     *
     * @param  array{year: int, turn: int}|null  $next
     * @param  Collection<int, RaceCatalogSlot>  $mandatory
     * @return array{rows: list<array<string, mixed>>, alerts: list<array<string, mixed>>, summary: string|null, empty_note: string|null}
     */
    private function traineeGoalRows(TrainingRun $run, ?array $next, Collection $mandatory): array
    {
        /** @var Collection<int, TraineeGoal> $goals */
        $goals = TraineeGoal::query()
            ->where('umamusume_id', $run->umamusume_id)
            ->where('goal_type', '!=', TraineeGoalType::FinalRound->value)
            ->with('raceCatalogSlot')
            ->orderBy('career_year')
            ->orderBy('turn')
            ->orderBy('source_order')
            ->orderBy('source_race_choice')
            ->get();

        if ($goals->isEmpty()) {
            return [
                'rows' => [],
                'alerts' => [],
                'summary' => null,
                // The first sentence belongs to the model, because the catalog detail page prints the same
                // absence and two copies is how one absence ends up with two vocabularies. The clause after
                // it is this screen's alone: only here does a Mandatory races group sit below the ladder.
                // `ladderAbsenceReason()` answers with a sentence whenever this branch is the one taken, so
                // the coalesce is for the typechecker, not for a state that can reach here.
                'empty_note' => (TraineeGoal::ladderAbsenceReason($goals) ?? '')
                    .' The mandatory races below are the scenario-wide record.',
            ];
        }

        /** @var Collection<int, Collection<int, RaceEntry>> $finished */
        $finished = $run->raceEntries
            ->filter(fn (RaceEntry $entry): bool => $entry->status === RaceEntryStatus::Completed
                && $entry->race_catalog_slot_id !== null)
            ->groupBy('race_catalog_slot_id');

        $rows = [];
        $alerts = [];
        /** @var array<int, bool> $metByOrder */
        $metByOrder = [];

        foreach ($goals as $goal) {
            $slot = $goal->raceCatalogSlot;
            $met = $this->objectiveMet($goal, $run, $finished);
            $metByOrder[$goal->source_order] = ($metByOrder[$goal->source_order] ?? false) || $met;

            $reached = $goal->isAtOrBeforeTurn($next);

            $state = match (true) {
                $goal->global_unreleased => null,
                $met => 'completed',
                $next === null => 'upcoming',
                $goal->turnWithinYear() === (int) $next['turn']
                    && $goal->career_year === (int) $next['year'] => 'current',
                $reached => 'missed',
                default => 'upcoming',
            };

            $race = $slot !== null ? $slot->title : $goal->source_race_name;
            $phrase = $goal->requirementPhrase();

            $rows[] = [
                // One reader for both surfaces, so the panel and the race strip cannot drift into two
                // vocabularies for one objective (TraineeGoal::obligationLine()).
                'label' => $goal->obligationLine(),
                'state' => $state,
                'year_label' => $goal->yearLabel(),
                'turn' => $goal->turnWithinYear(),
                // A door back to the picker only for a race this calendar carries that the run has not
                // finished. A fan objective is met by training, not by entering something.
                'race_url' => $slot !== null && ! $met && $state !== null
                    ? route('runs.races.decision', $run)
                    : null,
                // One owner for the sentence, shared with the catalog detail ladder (`TraineeGoal::absenceReason()`).
                'absence' => $goal->absenceReason(),
            ];

            // An objective alerts only when the race has not run at all. A recorded entry whose
            // placement was never typed in is unproven, not outstanding, and `is due and not met` claims
            // a door that already closed. Same year scope as the mandatory group above: an ordinal
            // deadline stays passed for the rest of the career, so a Senior run would banner its
            // Classic-year objectives every turn.
            $ranThisRace = $slot !== null && $finished->has((int) $slot->id);

            if ($state !== null && $reached && ! $met && ! $ranThisRace && $run->status === RunStatus::Active
                && ($slot === null || ! $mandatory->contains('id', $slot->id))
                && (int) $goal->career_year === (int) $next['year']) {
                $alerts[] = [
                    'level' => 'Critical',
                    'text' => $race !== null ? $race.' is due and not met' : 'A trainee objective is due and not met',
                    'detail' => 'The source puts it at '.$goal->yearLabel().' year turn '.$goal->turnWithinYear()
                        .', and the next turn to play is '.$next['turn'].'. '
                        .($phrase !== null ? $phrase.' is what it asks for.' : 'What it asks for is not decoded here.'),
                ];
            }
        }

        $obligations = count($metByOrder);
        $cleared = count(array_filter($metByOrder));

        return [
            'rows' => $rows,
            'alerts' => $alerts,
            // Counted by ladder position rather than by row: an alternates branch is one obligation the
            // Trainer answers either way. An integer pair and nothing else - no bar, no percentage, no
            // score (PRD §6.11, and ux-laws §9 item 6).
            'summary' => $obligations > 0 ? 'Goals Achieved '.$cleared.' of '.$obligations : null,
            'empty_note' => null,
        ];
    }

    /**
     * Whether this run has met one objective. Read-only, and false where the requirement is unread.
     *
     * @param  Collection<int, Collection<int, RaceEntry>>  $finished
     */
    private function objectiveMet(TraineeGoal $goal, TrainingRun $run, Collection $finished): bool
    {
        if ($goal->goal_type === TraineeGoalType::Fan) {
            $threshold = (int) $goal->fan_threshold;

            // `turn_entries.turn` counts from the start of the career, the same clock the source's deadline
            // turn uses, so the two compare without a conversion.
            $fans = $run->turnEntries
                ->filter(fn (TurnEntry $entry): bool => (int) $entry->turn <= $goal->turn)
                ->max('fans');

            return $threshold > 0 && $fans !== null && (int) $fans >= $threshold;
        }

        $slot = $goal->raceCatalogSlot;

        if ($slot === null) {
            return false;
        }

        /** @var Collection<int, RaceEntry> $entries */
        $entries = $finished->get($slot->id, new Collection);

        if ($goal->goal_type === TraineeGoalType::Participate) {
            return $entries->isNotEmpty();
        }

        $placing = $goal->required_placement;

        // An unconfirmed placing figure (2 and 4) cannot be claimed met, because this tool cannot state
        // what would satisfy it. The row still shows its deadline.
        if ($goal->goal_type !== TraineeGoalType::Placement || $placing === null
            || ! in_array($placing, [1, 3, 5], true)) {
            return false;
        }

        return $entries->contains(fn (RaceEntry $entry): bool => $entry->placement !== null
            && (int) $entry->placement <= $placing);
    }

    /**
     * The four fields SCREEN-014 names for Happy Meek, each an unrecorded reading with its own reason.
     *
     * Nothing stores a level, so there is nothing to badge as Confirmed and no write route to record
     * one through; the brief forbids a migration, so the module is read-only and says why. The last
     * field is not merely unrecorded but unsourced, which is a different sentence and gets one: the
     * rewards themselves are published, and the duel panel is where they read.
     *
     * @return list<array<string, mixed>>
     */
    private function happyMeekRows(): array
    {
        return [
            [
                'label' => 'Current level',
                'state' => null,
                'year_label' => null,
                'turn' => null,
                'race_url' => null,
                'absence' => 'No column stores a Happy Meek level and no route writes one, so this run'
                    .' records none. A Trainer-entered level needs a schema proposal first.',
            ],
            [
                'label' => 'Duel availability',
                'state' => null,
                'year_label' => null,
                'turn' => null,
                'race_url' => null,
                'absence' => 'Nothing stored or configured says whether a duel is open for this run,'
                    .' so the screen does not claim one way or the other.',
            ],
            [
                'label' => 'Potential reward',
                'state' => null,
                'year_label' => null,
                'turn' => null,
                'race_url' => null,
                // Corrected 2026-10-11: this row used to say no source publishes a Happy Meek reward
                // table. One does, and the duel panel now prints it. What stays unrecorded is this
                // run's own reward, and that is a different sentence.
                'absence' => 'No column stores a reward for this run, so nothing is owed to it. The published '
                    .'duel rewards are a scenario rule, and they read on the duel panel rather than here.',
            ],
            [
                'label' => 'Final-race contribution',
                'state' => null,
                'year_label' => null,
                'turn' => null,
                'race_url' => null,
                'absence' => 'No source states how a Happy Meek level changes the finale, so nothing is'
                    .' computed here. This is held computation, not a missing reading.',
            ],
        ];
    }

    /**
     * The fan-gate timeline a scenario's own config entry names.
     *
     * Only the gates the matrix declares travel here, which is why Unity Cup, Trackblazer and Our
     * Grand Concert answer `[]` rather than a list of zeroes: they declare no `fan_gates` key, and an
     * invented empty gate would be the false claim `AGENTS.md` §7 treats a hardcoded scenario name as.
     *
     * The recorded calendar's obligations are deliberately NOT re-listed. `careerGoalSections()` draws
     * them in the same panel as `Mandatory races`, and the finale block is `ScenarioPanel`'s strip
     * reading `FinaleReader`; a third surface for either fact is the second representation Phase G's
     * acceptance forbids. The calendar does contribute one thing: whether the Senior year is behind
     * the run, which is what turns a short gate into a missed one.
     *
     * A gate publishes two figures and the tool cannot tell which one binds. No column records a turf
     * or dirt preference (`character_cards` carries none), so the state is decided on the two bounds:
     * at or above both clears it whichever track applies, below both misses it, and between them the
     * answer is `unknown` and renders as `N/A` naming the missing column. Picking a track would be
     * the guess, not the bound.
     *
     * @return list<array<string, mixed>>
     */
    private function milestoneSection(TrainingRun $run, ?TurnEntry $latest): array
    {
        /** @var list<array<string, mixed>> $gates */
        $gates = (array) config('scenarios.scenarios.'.$run->scenarioKey().'.fan_gates', []);

        if ($gates === []) {
            return [];
        }

        // The same reading the header and the fan ladder use, so the tracker cannot disagree with the
        // strip about how many fans this run holds.
        $fans = $latest?->fans;
        $finale = FinaleReader::forRun($run);
        $seniorBehind = $finale !== null && $finale['reached'];

        $rows = [];

        foreach ($gates as $gate) {
            $turf = (int) $gate['turf'];
            $dirt = (int) $gate['dirt'];
            $both = max($turf, $dirt);
            $either = min($turf, $dirt);

            [$state, $title] = match (true) {
                $fans === null => [
                    'upcoming',
                    'No turn entry for this run records a fan count, so nothing has cleared the gate yet.',
                ],
                $fans >= $both => [
                    'completed',
                    'The recorded reading is at or above both published figures, so the gate is cleared on '
                        .'either track. Compared against '.number_format($both).' fans.',
                ],
                $fans < $either && $seniorBehind => [
                    'missed',
                    'The finale block has arrived and the recorded reading stands below both figures, so '
                        .'neither gate could have been cleared. Compared against '.number_format($either).' fans.',
                ],
                $fans < $either => [
                    'upcoming',
                    'The recorded reading is below both published figures and the Senior year is still '
                        .'open, so the gate is ahead of the run. Compared against '.number_format($either).' fans.',
                ],
                default => [
                    'unknown',
                    'The recorded reading sits between the two figures this event publishes. No column says '
                        .'whether this trainee races on turf or dirt, so neither number is the gate, and the '
                        .'state that follows from it is not this tool\'s to pick.',
                ],
            };

            $rows[] = [
                'label' => (string) $gate['label'],
                'kind' => 'fan_gate',
                'state' => $state,
                'title' => $title,
                'figures' => number_format($turf).' Turf / '.number_format($dirt).' Dirt',
                'figures_badge' => 'confirmed',
                'figures_title' => 'config/scenarios.php, from docs/scenarios/01-ura-finale.md:80-83 and its '
                    .'Senior Year event table, corroborated by docs/UMAMUSUME_REFERENCE.md §2.2.1.',
                // Dated by month and never by turn: `docs/URA-Finale-plan.md:251` records that no source
                // publishes the turn offset, so the row states the month and prints no turn.
                'due_turn' => null,
                'due_label' => (string) $gate['year_label'].' Year · '.(string) $gate['timing'],
                'due_title' => 'The guides date this event by month within the career year and no source '
                    .'publishes the career turn it falls on, so no turn is printed.',
                'consequence' => (string) $gate['effect'],
                'condition' => $gate['condition'] === null ? null : (string) $gate['condition'],
                // The one action a fan gate answers to is racing, and the Race Decision screen is where
                // this run's options live. Same door `careerGoalSections()` gives a mandatory race row.
                'decision_url' => route('runs.races.decision', $run),
            ];
        }

        return $rows;
    }

    /**
     * What the duel with Happy Meek is, as one row per published rule, and what this run records about it.
     *
     * Display-only, and that is a ruling rather than a shortfall: `TurnChoice` states that the URA
     * finale's duels are display-only in this build (D-5, `app/Enums/TurnChoice.php:20`), no column
     * stores a duel, and the panel adds no column to the question. Everything on the rules side reads
     * off `config/scenarios.php`; everything on the run side is `N/A` with the reason it is empty.
     *
     * No percentage appears. The client shows an icon per option and prints no figure, which
     * `docs/URA-Finale-plan.md:245` records as unconfirmed, so a chance row carries the rule and an
     * empty value rather than a number.
     *
     * **An absent value carries no badge** (owner ruling 2a, 2026-10-09): a badge qualifies a
     * displayed value, and `unknown` beside an absence was retired. The rows that state a gap carry
     * `badge => null` and their title is the absence reason the panel prints.
     *
     * @return array<string, mixed>|null
     */
    private function duelSection(TrainingRun $run): ?array
    {
        /** @var array<string, mixed> $duel */
        $duel = (array) config('scenarios.scenarios.'.$run->scenarioKey().'.duel', []);

        if ($duel === []) {
            return null;
        }

        $cite = 'config/scenarios.php, from docs/UMAMUSUME_REFERENCE.md §2.2.1.';

        /** @var list<array{label: string, value: string|null, badge: string|null, title: string}> $rules */
        $rules = [
            ['label' => 'The prompt', 'value' => (string) $duel['options_rule'], 'badge' => 'confirmed', 'title' => 'The options a duel puts in front of the Trainer, from docs/URA-Finale-plan.md:182.'],
            ['label' => 'Win', 'value' => (string) $duel['win']['gains'], 'badge' => 'confirmed', 'title' => 'What a win grants. '.$cite],
            ['label' => 'Win Skill Points', 'value' => number_format((int) $duel['win']['sp']), 'badge' => 'confirmed', 'title' => 'The published win reward. '.$cite],
            ['label' => 'Win hint', 'value' => 'a Racing Spirit hint for the stat dueled', 'badge' => 'confirmed', 'title' => 'The hint is keyed to the stat the duel was fought on. '.$cite],
            ['label' => 'Lose', 'value' => (string) $duel['lose']['gains'], 'badge' => 'confirmed', 'title' => 'What a loss still grants. '.$cite],
            ['label' => 'Lose Skill Points', 'value' => number_format((int) $duel['lose']['sp']), 'badge' => 'confirmed', 'title' => 'The published loss reward. '.$cite],
            ['label' => 'Escalation', 'value' => 'after '.number_format((int) $duel['escalation_wins']).' wins', 'badge' => 'confirmed', 'title' => 'Wins that carry her to the powered-up state at the finals. '.$cite],
            ['label' => 'Powered-up reward', 'value' => (string) $duel['escalation_reward']['skill']
                .', '.number_format((int) $duel['escalation_reward']['sp_minimum']).' or more Skill Points',
                'badge' => 'confirmed', 'title' => 'Beating the powered-up state at the finals. '.$cite],
            ['label' => 'Enhanced spark', 'value' => (string) $duel['escalation_reward']['spark'], 'badge' => 'confirmed', 'title' => 'The chance is published; its rate is not. '.$cite],
            ['label' => 'Enhanced spark rate', 'value' => null, 'badge' => null, 'title' => 'No source this tool reads publishes the trigger rate, so none is shown.'],
            ['label' => 'Per-stat gain of a win', 'value' => null, 'badge' => null, 'title' => 'The sources say a win grants stat gains and raise the cap of the stat dueled, and quantify neither.'],
            ['label' => 'Win chance per option', 'value' => null, 'badge' => null, 'title' => 'The client shows an icon beside each option and prints no percentage, and no source publishes one.'],
        ];

        /** @var array<string, string> $hintNames */
        $hintNames = (array) ($duel['hints'] ?? []);
        // The names live in the catalogue, so the panel prints what was fetched with a client flag on
        // it rather than a copy pasted out of a plan document (`AGENTS.md` §13).
        $catalogue = Skill::query()
            ->whereIn('name', array_values($hintNames))
            ->where('name_is_client', true)
            ->get()
            ->keyBy('name');

        $hints = [];

        foreach ($hintNames as $stat => $name) {
            $row = $catalogue->get($name);

            $hints[] = [
                'stat' => (string) $stat,
                'name' => $name,
                'sp_cost' => $row === null ? null : number_format((int) $row->sp_cost),
                'badge' => $row === null ? null : 'confirmed',
                'title' => $row === null
                    ? 'The skills catalogue holds no client-named row for '.$name.', so no figure travels with it.'
                    : 'Read from the skills table, which carries this name as a client noun (name_is_client).',
            ];
        }

        /** @var list<array{label: string, value: string|null, badge: string|null, title: string}> $runRows */
        $runRows = [
            ['label' => 'Duels this run has fought', 'value' => null, 'badge' => null, 'title' => 'No table stores a duel. A race is a race_entries row and an event a turn_events row, and neither posts a choice.'],
            ['label' => 'Wins toward escalation', 'value' => null, 'badge' => null, 'title' => 'Counting wins needs a stored duel outcome, which this build has none of.'],
            ['label' => 'Happy Meek level', 'value' => null, 'badge' => null, 'title' => 'No column stores a Happy Meek level and no route writes one, so this run records none.'],
            ['label' => 'The options offered', 'value' => null, 'badge' => null, 'title' => 'A duel puts two random stats and the stat dueled in front of the Trainer, and nothing this run records names them.'],
        ];

        return ['rules' => $rules, 'hints' => $hints, 'run' => $runRows];
    }

    /**
     * The check a career makes against its own finale, inside the window the advisor already owns.
     *
     * The window is `TrainerAdvisor::FINALE_TURNS_AHEAD` and nothing else: the section reads the
     * proximity the advisor computed for this turn (`Advice::$finale_context`) rather than re-running
     * the arithmetic, so the rail and this panel cannot disagree about how close the block is. Outside
     * the window the section is null and the panel draws nothing at all, which is the honest shape for
     * a career three turns old.
     *
     * @return array<string, mixed>|null
     */
    private function preFinalSection(TrainingRun $run, ?TurnEntry $latest, Advice $advice): ?array
    {
        /** @var array<string, mixed> $targets */
        $targets = (array) config('scenarios.scenarios.'.$run->scenarioKey().'.finale_targets', []);
        $context = $advice->finale_context;

        if ($targets === [] || $context === null) {
            return null;
        }

        /** @var list<string> $order */
        $order = (array) config('scenarios.stat_order');
        $caps = ScenarioCaps::forRun($run);
        $cite = 'docs/UMAMUSUME_REFERENCE.md §2.2.1.';

        $recorded = [];

        foreach ($order as $stat) {
            $value = $this->currentStat($latest, $stat);
            $cap = $caps[$stat] ?? null;

            $recorded[] = [
                'label' => $stat,
                'value' => $value === null ? null : number_format($value),
                'cap' => $cap === null ? null : number_format((int) $cap),
                // No badge on the empty branch: a badge qualifies a displayed value (ruling 2a).
                'badge' => $value === null ? null : 'confirmed',
                'title' => $value === null
                    ? 'No turn entry for this run records a '.$stat.' reading, so nothing is shown.'
                    : 'The latest turn entry of this run, read the same way the header bars read it.',
            ];
        }

        $speed = $this->currentStat($latest, 'Speed');
        $approx = (int) $targets['speed_to_win']['approx'];

        return [
            'window' => [
                'state' => (string) $context['state'],
                'label' => (string) $context['label'],
                'turns_away' => $context['turns_away'] === null ? null : (int) $context['turns_away'],
                'turns_ahead' => TrainerAdvisor::FINALE_TURNS_AHEAD,
                'title' => 'Counted from the advisor\'s own proximity for this turn, over the '
                    .TrainerAdvisor::FINALE_TURNS_AHEAD.' turns it looks ahead '
                    .'(App\Services\Advisor\TrainerAdvisor::FINALE_TURNS_AHEAD).',
            ],
            'recorded' => $recorded,
            // Carries the source's own hedge: the guide says "around 800", and an unqualified 800 would
            // state a requirement nobody published.
            'speed_target' => [
                'phrasing' => (string) $targets['speed_to_win']['phrasing'],
                'value' => $speed === null ? null : number_format($speed),
                'short_by' => $speed === null || $speed >= $approx
                    ? null
                    : number_format($approx - $speed),
                'badge' => $speed === null ? null : 'calculated',
                'title' => 'The recorded Speed against the published target, which '.$cite.' states as an approximation.',
            ],
            'stamina_floors' => [
                'rows' => array_map(
                    static fn (string $distance, string $floor): array => ['distance' => $distance, 'floor' => number_format((int) $floor)],
                    array_keys((array) $targets['stamina_by_distance']),
                    array_values((array) $targets['stamina_by_distance']),
                ),
                'badge' => 'confirmed',
                'title' => 'The stamina figure each distance asks for, published by '.$cite,
            ],
            // The rule that names a URA finale distance is the career's most-raced distance type
            // (`docs/URA-Finale-plan.md:64`), and no code in this tree computes it, so the row states
            // the absence and the four floors stay a table rather than collapsing to one verdict.
            'distance' => [
                'value' => null,
                'title' => 'The finale distance is the distance this career raced most, and no column or reader in '
                    .'this tool computes it, so no single floor applies.',
            ],
        ];
    }

    /**
     * The team system's facts (plan §9.1): what this run recorded through the team payloads, what
     * config states, and nothing else. The key set is the same for every scenario, so one renderer
     * draws all of them and a fifth scenario's empties are the shape, not a branch.
     *
     * Each recorded half is gated the way the run record screen's own payloads are: the ladder on
     * `composesPanel('team_rank_ladder')`, the roster on `spirit_bursts` widget membership
     * (D-221, gate G-34), the race entries on `composesPanel('team_race')`. The rank letter is the
     * one reading a payload already holds; per-stat team grades have no writer, so all five rows
     * are absences and stay that way until a source records one.
     *
     * @return TeamSectionShape
     */
    private function teamSection(TrainingRun $run): array
    {
        $ladder = [];

        if ($run->composesPanel('team_rank_ladder')) {
            foreach ((array) config('scenarios.scenarios.'.$run->scenarioKey().'.team_rank_ladder') as $rung) {
                foreach ((array) ($rung['ranks'] ?? []) as $letter) {
                    $ladder[] = ['rank' => (string) $letter, 'level' => (int) $rung['level']];
                }
            }
        }

        $roster = [];

        if (in_array('spirit_bursts', (array) config('scenarios.scenarios.'.$run->scenarioKey().'.widgets', []), true)) {
            foreach ($run->spiritBurstRoster() as $row) {
                $roster[] = [
                    'teammate' => $row['teammate'],
                    'state' => $row['state']->value,
                    'stateLabel' => $row['state']->label(),
                ];
            }
        }

        $entries = [];

        if ($run->composesPanel('team_race')) {
            foreach ($run->raceEntries as $entry) {
                $slot = $entry->scenarioSlot;

                if ($slot === null || $slot->kind !== 'team_race') {
                    continue;
                }

                $entries[] = [
                    'title' => $slot->title,
                    'tier' => $slot->tier,
                    'circles' => $entry->circles,
                    'placement' => $entry->placement,
                ];
            }
        }

        $def = $run->hasScenario() ? (array) config('scenarios.scenarios.'.$run->scenarioKey(), []) : [];
        $rank = $run->latestTeamRank()?->rank;

        /** @var list<string> $order */
        $order = config('scenarios.stat_order');

        // One gate for the burst machine's reference rows: the bands and the payout timing are the
        // same system's facts, so a matrix that declares the timing without the widget gets neither
        // rather than a sentence that has no table to sit beside.
        $composesBursts = in_array('spirit_bursts', (array) config('scenarios.scenarios.'.$run->scenarioKey().'.widgets', []), true);

        // A missing margin reads as an absence, never as zero: "aim for at least 0 circles" would
        // state a margin the matrix never declared.
        $margin = $run->composesPanel('team_race')
            ? config('scenarios.scenarios.'.$run->scenarioKey().'.team_race.circles_guidance')
            : null;

        // The combined Spirit + Extreme total the band table reads, only when both tallies are
        // recorded: an unrecorded one is not a zero (D-220), so a run that stated one of the two
        // gets no combined figure and the panel keeps its honest absence rather than half a count.
        $combined = ($run->spirit_bursts_count !== null && $run->extreme_bursts_count !== null)
            ? $run->spirit_bursts_count + $run->extreme_bursts_count
            : null;

        return [
            'rank' => $rank,
            'facility_level' => $run->facilityLevel($rank),
            'ladder' => $ladder,
            // No column records a team stat grade, so every row is a named absence rather than a
            // zero (D-220); the labels ride config so the renderer never invents one.
            'stat_grades' => array_map(
                static fn (string $stat): array => ['key' => $stat, 'label' => $stat, 'grade' => null],
                $order
            ),
            'roster' => $roster,
            'race_guidance' => is_numeric($margin) ? (int) $margin : null,
            'race_entries' => $entries,
            'bands' => $composesBursts
                ? array_values((array) config('scenarios.scenarios.'.$run->scenarioKey().'.spirit_burst_bands', []))
                : [],
            'payout_timing' => $composesBursts && isset($def['burst_payout_timing'])
                ? (string) $def['burst_payout_timing']
                : null,
            'special_training' => [
                'energy_penalty_removed' => array_key_exists('team_training_energy_penalty', $def)
                    ? $def['team_training_energy_penalty'] === false
                    : null,
                'wit_burst_energy_bonus' => isset($def['wit_burst_energy_bonus'])
                    ? (int) $def['wit_burst_energy_bonus']
                    : null,
            ],
            // The team's own identity and the Unity Cup counters (A6.6, A6.7), as the Trainer
            // entered them. Null until stated, so the panel names the absence rather than a zero.
            'team_name' => $run->team_name,
            'team_motto' => $run->team_motto,
            'team_league_placement' => $run->team_league_placement,
            'team_preseason_wins' => $run->team_preseason_wins,
            'unity_trainings_count' => $run->unity_trainings_count,
            'spirit_bursts_count' => $run->spirit_bursts_count,
            'extreme_bursts_count' => $run->extreme_bursts_count,
            'combined_bursts' => $combined,
            // The band's own `total` label, so the panel marks the row it already renders rather
            // than carrying a second copy of the bounds.
            'burst_band' => $this->burstBandFor($combined, $run->scenarioKey()),
            // G4's two team surfaces. Both are null unless the matrix turns `team_race` on, so the
            // three scenarios without a team layer draw neither and the section keeps one key set
            // across all four (gate G-33).
            'coverage' => $this->teamCoverageSection($run),
            'tournament' => $this->teamTournamentSection($run, $def),
        ];
    }

    /**
     * The team race's published shape, one row per category, and the reading this run does not have.
     *
     * Five categories, Dirt among them: `docs/scenarios/02-unity-cup.md:149` states the set and corrects
     * an earlier line in the same file, and `SCENARIO-PUBLISHER-REFERENCES.md:1028` repeats it. That is
     * why the rows are not `BuildTargetPayload::DISTANCE_BANDS` — four bands and no Dirt would drop a
     * category of the race it describes, and a board missing a fifth of the thing is worse than no board.
     *
     * The ask is published; who this run fields in a category is not stored anywhere. No column carries a
     * distance for a team race row (`scenario_slots` has none), and the run's individual `race_entries`
     * are a different fact, so they are not counted here as an answer to this question. Each row states
     * the ask with no value beside it and no coverage state is claimed.
     *
     * @return array<string, mixed>|null
     */
    private function teamCoverageSection(TrainingRun $run): ?array
    {
        /** @var array<string, mixed> $race */
        $race = (array) config('scenarios.scenarios.'.$run->scenarioKey().'.team_race', []);

        if (! $run->composesPanel('team_race') || $race === []) {
            return null;
        }

        /** @var list<string> $categories */
        $categories = (array) ($race['categories'] ?? []);
        $asks = number_format((int) ($race['field_per_category'] ?? 0)).' fielded';

        $rows = [];

        foreach ($categories as $category) {
            $rows[] = [
                'category' => $category,
                'asks' => $asks,
                'fielded' => null,
            ];
        }

        return [
            'rows' => $rows,
            'badge' => 'confirmed',
            'source_title' => 'The five categories and the number fielded in each come from '
                .'config/scenarios.php, transcribed from docs/scenarios/02-unity-cup.md:149 and '
                .'SCENARIO-PUBLISHER-REFERENCES.md:1028.',
            'absence_title' => 'No column records who this run fields in any category, so every row '
                .'states the ask with no reading beside it and no coverage state is claimed.',
        ];
    }

    /**
     * What a team race is made of, as far as the sources carry it, and the one question they do not.
     *
     * Cadence and anchors are published (`docs/scenarios/02-unity-cup.md:153`, references `:658` and
     * `:1028`). The turn a round falls on is not: references `:1186` records the per-round turn count as
     * unverified, so the block names the months and leaves `next` empty rather than deriving a turn from
     * a calendar the run does not hold.
     *
     * **The matrix's three opponent names are not read, and none reaches the page.** Measured 2026-10-11:
     * the corpus holds "Turf Queens" and "The Apex" (`02-unity-cup.md:163`, references `:1324`) and
     * nothing for the other two names the config carries. A row rendered from that list would be a client
     * fact with no client source behind it (`AGENTS.md` §5), so the block states the published shape and
     * the absence of a projection, and the names stay in config. Clearing that row is the owner's call,
     * not a slice's.
     *
     * The displayed label avoids the wiki-side gloss for a stat that the ban governs; the key that
     * carries the absence is named for the question it answers.
     *
     * @param  array<string, mixed>  $def
     * @return array<string, mixed>|null
     */
    private function teamTournamentSection(TrainingRun $run, array $def): ?array
    {
        /** @var array<string, mixed> $race */
        $race = (array) ($def['team_race'] ?? []);

        if (! $run->composesPanel('team_race') || $race === []) {
            return null;
        }

        /** @var list<string> $anchors */
        $anchors = (array) ($race['month_anchors'] ?? []);
        $months = isset($race['occurs_every_months']) ? (int) $race['occurs_every_months'] : null;

        return [
            'cadence_months' => $months,
            'month_anchors' => array_map(static fn ($month): string => (string) $month, $anchors),
            'category_count' => count((array) ($race['categories'] ?? [])),
            'clear_all_reward' => isset($race['clear_all_reward']) ? (string) $race['clear_all_reward'] : null,
            'badge' => 'confirmed',
            'source_title' => 'config/scenarios.php, from docs/scenarios/02-unity-cup.md:149-153 and '
                .'SCENARIO-PUBLISHER-REFERENCES.md:658, :1028.',
            'projection' => null,
            'projection_title' => 'No source says how strong each opponent team is. The guides name which '
                .'teams to avoid before a rank is high enough and stop there, so no side of any team race '
                .'can be called favoured and no chance is printed.',
            'next' => null,
            'next_title' => 'A team race arrives every '.$months.' months, at '
                .implode(' and ', $anchors).', and no source publishes the career turn a round falls on, '
                .'so no turn is printed here.',
            'risks' => [
                'No published source ranks the three teams, so the screen calls no side of a team race easier.',
                'No column records who this run fields in each category, so the board states the ask and not the run.',
            ],
        ];
    }

    /**
     * The burst-count band a combined Spirit + Extreme total falls in, as the band's own `total`
     * label (`4–6`, `13+`), or null when the total is unknown or no band contains it.
     *
     * The band labels are display strings from `config('scenarios.scenarios.*.spirit_burst_bands')`,
     * and they are the only bound the matrix carries, so they are parsed here rather than duplicated
     * in config. A label this parse does not recognise matches nothing, which keeps the panel's
     * absence rather than inventing a placement.
     */
    private function burstBandFor(?int $combined, string $scenarioKey): ?string
    {
        if ($combined === null) {
            return null;
        }

        foreach ((array) config('scenarios.scenarios.'.$scenarioKey.'.spirit_burst_bands', []) as $band) {
            $total = (string) ($band['total'] ?? '');

            if (preg_match('/^(\d+)(?:–(\d+))?\+?$/u', $total, $matches) === 1) {
                $low = (int) $matches[1];
                // The upper bound's group is optional and absent from `$matches` when the label is
                // open-ended (`13+`), which is what makes the band unbounded rather than empty.
                $high = isset($matches[2]) ? (int) $matches[2] : null;

                if ($combined >= $low && ($high === null || $combined <= $high)) {
                    return $total;
                }
            }
        }

        return null;
    }

    /**
     * The manual correction (`design-2.0` §38), opened on the latest logged turn by default and on
     * any logged turn named by `?edit_turn=<id>`. Null when there is no turn to correct.
     *
     * F2, plan §9.6 ruling 2: the write is the route and Form Request that already own a turn write
     * (`runs.turns.update` / `runs.turns.destroy`); this section only adds the turn list and the
     * per-turn action URLs. No new route, and no new cascade.
     *
     * @return array{action: string, destroy_action: string, turn: int, selected_turn_id: int, turns: list<array{id: int, turn: int}>, values: array<string, int|string|null>}|null
     */
    private function correctionSection(TrainingRun $run, ?TurnEntry $selected): ?array
    {
        if ($selected === null) {
            return null;
        }

        return [
            'action' => route('runs.turns.update', [$run, $selected]),
            'destroy_action' => route('runs.turns.destroy', [$run, $selected]),
            'turn' => $selected->turn,
            'selected_turn_id' => $selected->id,
            'turns' => $run->turnEntries
                ->sortBy('turn')
                ->map(static fn (TurnEntry $turn): array => ['id' => $turn->id, 'turn' => $turn->turn])
                ->values()
                ->all(),
            'values' => [
                'speed' => $selected->speed,
                'stamina' => $selected->stamina,
                'power' => $selected->power,
                'guts' => $selected->guts,
                'wit' => $selected->wit,
                'sp' => $selected->sp,
                'energy' => $selected->energy,
                // R2-16: the state and the band ride with the number so the correction form can prefill
                // them and a Trainer can clear a band the state no longer names. `energy_state` is cast to
                // `EnergyState` on the model, so the wire carries the backed word.
                'energy_state' => $selected->energy_state?->value,
                'energy_band' => $selected->energy_band,
                'fans' => $selected->fans,
                'mood' => $selected->mood?->value,
            ],
        ];
    }

    /**
     * The turn the correction opens on: `?edit_turn=<id>` when it names one of this run's turns, the
     * latest turn otherwise. An id from another run, or a non-numeric value, falls back to the
     * latest rather than erroring — the selector is a convenience, not a trust boundary.
     */
    private function resolvedCorrectionTurn(TrainingRun $run, ?TurnEntry $latest): ?TurnEntry
    {
        $requested = request()->query('edit_turn');

        if (! is_numeric($requested)) {
            return $latest;
        }

        return $run->turnEntries->firstWhere('id', (int) $requested) ?? $latest;
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
