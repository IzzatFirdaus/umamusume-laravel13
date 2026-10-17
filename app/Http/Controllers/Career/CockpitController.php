<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Domain\Career\CareerPosition;
use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Enums\TraineeGoalType;
use App\Enums\TurnChoice;
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
use App\Services\Career\RaceStripBuilder;
use App\Services\Career\ScenarioSectionBuilder;
use App\Services\Career\TeamPanelBuilder;
use App\Services\Career\TrackblazerPanelBuilder;
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
     * The public query value the Recreation entry carries, and the only place this screen names it.
     *
     * It is the client's word rather than `TurnChoice::Recreation`'s stored literal (`mood`): the address
     * a Trainer reads and can bookmark is the word on the button, and the Training screen maps it back to
     * the literal it writes at its own boundary. The two stay apart on purpose, so neither the enum nor
     * this URL has to carry the other's concern.
     */
    private const RECREATION_QUERY = 'recreation';

    /**
     * The seven entries of the action grid, in the spec's order (SCREEN-009 §12), with what each one
     * does today.
     *
     * @var list<array{key: string, label: string, note: string}>
     */
    private const ACTIONS = [
        ['key' => 'training', 'label' => 'Training', 'note' => 'The five training options, and the turn record.'],
        ['key' => 'race', 'label' => 'Race', 'note' => 'Entering a race arrives with the Race decision screen.'],
        ['key' => 'rest', 'label' => 'Rest', 'note' => 'Records a Rest turn through the Training screen.'],
        ['key' => 'recreation', 'label' => 'Recreation', 'note' => 'Records a Recreation turn through the Training screen.'],
        ['key' => 'scenario', 'label' => 'Scenario action', 'note' => 'Scenario actions arrive with the scenario panels.'],
        ['key' => 'event', 'label' => 'Event', 'note' => 'Event choices are recorded at the Event decision screen.'],
        ['key' => 'inheritance', 'label' => 'Inheritance', 'note' => 'Inheritance is recorded at the Inheritance event screen.'],
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

    /**
     * Plan §3.1a (F1): the race strip's builder, extracted so the left column's section is testable
     * without the controller. `obligationSlots()` stays here because the decision rail reads it too;
     * the collection is passed in, so both readers still share the one query.
     *
     * F1 4.2 (`TrackblazerPanelBuilder`): the Trackblazer arm's five sections and their nested
     * surfaces — the grade ladder and its planning/calendar/fatigue readings, the shop and its
     * ledger, the epithet checklist and the rival rows. Every one was read only from
     * `scenarioSection()` and its own arm, so the whole cluster moved together; the controller
     * keeps only the call sites.
     */
    public function __construct(
        private RaceStripBuilder $raceStrip,
        private TrackblazerPanelBuilder $trackblazer,
        private TeamPanelBuilder $team,
        private ScenarioSectionBuilder $scenario,
    ) {}

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
        $team = $this->team->teamSection($run);

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
            'raceStrip' => $this->raceStrip->raceStripSection($run, $this->obligationSlots($run)),
            'scenario' => $this->scenario->scenarioSection($run, $team, $latest, $advice, $this->scenarioSectionContext($run, $latest, $advice)),
            // F2, plan §9.6 ruling 2: arbitrary-turn correction. The selected turn defaults to the
            // latest, but `?edit_turn=<id>` names any logged turn; the form posts to the existing
            // `runs.turns.update` / `runs.turns.destroy`, unchanged.
            'correction' => $this->correctionSection($run, $this->resolvedCorrectionTurn($run, $latest)),
        ]);
    }

    /**
     * The pre-computed pieces `ScenarioSectionBuilder::scenarioSection()` composes (F1 4.4).
     *
     * The section's own shape moved to the service; the four sections it folds in stayed here because
     * each is read elsewhere or belongs to another slice, and `obligationSlots()`'s precedent is the
     * same: the caller keeps the reader, the builder receives the value.
     *
     * @return array{widgets: list<string>, objectives: list<array<string, mixed>>, alerts: list<array<string, mixed>>, milestones: list<array<string, mixed>>, duel: array<string, mixed>|null, pre_final: array<string, mixed>|null, grade: array<string, mixed>|null, shop: array<string, mixed>|null, epithet: array<string, mixed>|null, rival: array<string, mixed>|null}
     */
    private function scenarioSectionContext(TrainingRun $run, ?TurnEntry $latest, Advice $advice): array
    {
        $def = $run->hasScenario() ? (array) config('scenarios.scenarios.'.$run->scenarioKey(), []) : [];
        // The panel's own sections are composed only for a scenario that turns the flag on, so a
        // cockpit whose matrix says off cannot gain another scenario's rows (gate G-33).
        $goalsOn = $run->hasScenario() && ($def['panels']['career_goals'] ?? false) === true;
        $goalSections = $goalsOn
            ? $this->team->careerGoalSections($run)
            : ['objectives' => [], 'alerts' => []];

        return [
            'widgets' => $this->scenarioWidgets($run),
            'objectives' => $goalSections['objectives'],
            'alerts' => $goalSections['alerts'],
            'milestones' => $this->milestoneSection($run, $latest),
            'duel' => $this->duelSection($run),
            'pre_final' => $this->preFinalSection($run, $latest, $advice),
            'grade' => $this->trackblazer->gradeSection($run, $latest),
            'shop' => $this->trackblazer->shopSection($run),
            'epithet' => $this->trackblazer->epithetSection($run),
            'rival' => $this->trackblazer->rivalSection($run),
        ];
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
            // 2.0.0 record screen used. The cockpit is the door, so the 2.0 edit URLs ride here.
            'run_url' => route('runs.cockpit', $run),
            // The left column's own door to the Career Timeline (SCR-CAR-017): the region shows this
            // run's races, and the timeline is the screen that shows the whole recorded history.
            'timeline_url' => route('runs.timeline', $run),
            // The Career Result (SCR-CAR-018, plan §8 D15). It had no door on any 2.0 screen: the only
            // link to it sat in the 2.0.0 record page's header, so retiring that page would leave the
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
        $turn = $position === null ? (int) ($latest === null ? 0 : $latest->turn) : $position->turnIndex;
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
                'current' => $latest?->statValue($stat),
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
     * training (SCREEN-010, D9) and for the two turn choices that are not trainings, Rest and Recreation,
     * the Race decision screen for race (SCREEN-011, D10) and the Inheritance event screen for
     * inheritance (SCREEN-013, D12) — and the entry's own 2.0 screen is named in its `note` where that
     * screen has not landed. Events and the scenario actions still land at Phase E, so their entries keep
     * their Phase E `note` and link to the run. The slice's brief says "otherwise they are named
     * absences"; a grid of seven unfocusable items would fail this slice's own keyboard-path acceptance
     * and would leave the Cockpit with no way to act, so the absence is named in the copy and the door is
     * a live route. No entry is ever a dead link, and none is a loop back to this screen.
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
                    // Rest and Recreation are turn choices the Training screen's record form holds, not
                    // trainings (SCR-CAR-012), so the entry arrives with the choice already picked. Rest's
                    // public value is the one the row is written under. Recreation's public word is
                    // `recreation` while its row is written under `mood` (`TurnChoice`'s docblock records
                    // why), so the screen normalises the address back to the stored literal at its own
                    // boundary; this controller names no storage key the request does not validate.
                    'rest' => route('runs.training', ['run' => $run, 'choice' => TurnChoice::Rest->value]),
                    'recreation' => route('runs.training', ['run' => $run, 'choice' => self::RECREATION_QUERY]),
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
            $value = $latest?->statValue($stat);
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

        $speed = $latest?->statValue('Speed');
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
}
