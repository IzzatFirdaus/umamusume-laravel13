<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Enums\RunStatus;
use App\Enums\TurnEventType;
use App\Http\Controllers\Controller;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\TurnEvent;
use App\Services\Advisor\Advice;
use App\Services\Advisor\TrainerAdvisor;
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
 *     special_training: array{energy_penalty_removed: bool|null, wit_burst_energy_bonus: int|null}
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
        ['key' => 'rest', 'label' => 'Rest', 'note' => 'Rest is a turn choice on the run screen, not one of the five training options.'],
        ['key' => 'recreation', 'label' => 'Recreation', 'note' => 'A Recreation screen of its own is not built yet; the run screen records it as a turn choice.'],
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
        // collection rather than re-queried, which is `showData()`'s own reading of the same rows.
        // `turnEvents` joins only when the scenario's team system has a payload reader at all: a
        // URA or Grand Concert cockpit must not hydrate every event row to discard it.
        $needsEvents = $run->composesPanel('team_rank_ladder')
            || in_array('spirit_bursts', (array) config('scenarios.scenarios.'.$run->scenarioKey().'.widgets', []), true);

        $run->load(['umamusume', 'turnEntries', 'raceEntries.raceCatalogSlot', 'raceEntries.scenarioSlot', 'raceEntries.turnEntry']);

        if ($needsEvents) {
            $run->load('turnEvents');
        }

        $latest = $run->turnEntries->sortByDesc('turn')->first();
        $advice = app(TrainerAdvisor::class)->advise($latest, $run->buildTarget());

        // Built once: the header's rank and the scenario section's team panel read the same pass.
        $team = $this->teamSection($run);

        return Inertia::render('Career/Cockpit', [
            'run' => $this->runSection($run),
            'header' => $this->headerSection($run, $latest, $team),
            'state' => $this->stateSection($run, $latest),
            'actions' => $this->actionSection($run, $advice),
            'advisor' => $this->advisorSection($advice),
            'raceStrip' => $this->raceStripSection($run),
            'scenario' => $this->scenarioSection($run, $team),
            'correction' => $this->correctionSection($run, $latest),
        ]);
    }

    /**
     * The run's own identity, flattened. `scenario_label` names the absence rather than borrowing the
     * baseline's name, which is a composition device and not a fact (D-220).
     *
     * @return array{id: int, trainee: string, trainee_ja: string|null, status: string, status_label: string, scenario_label: string, run_url: string, timeline_url: string, result_url: string}
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
            // The left column's own door to the Career Timeline (SCR-CAR-017): the region shows this
            // run's races, and the timeline is the screen that shows the whole recorded history.
            'timeline_url' => route('runs.timeline', $run),
            // The Career Result (SCR-CAR-018, plan §8 D15). It had no door on any 2.0 screen: the only
            // link to it sat in the 0.1.0 record page's header, so retiring that page would leave the
            // screen URL-only, which D14 ruled a defect. The result screen answers an unfinished career
            // itself, so the door does not gate on status.
            'result_url' => route('runs.result', $run),
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
     * @param  TeamSectionShape  $team  built once by `show()` so the header and the panel read one pass
     * @return array<string, mixed>
     */
    private function headerSection(TrainingRun $run, ?TurnEntry $latest, array $team): array
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
                // The rank letter the Trainer most recently reported rides the team payload, so it
                // is a recorded reading where one exists; the rest stay absent until a column or a
                // payload holds them (D-220). The Team panel reads the same payload, so the header
                // strip and the panel cannot disagree about one rank.
                'team_rank' => $team['rank'],
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
     * **Nothing here predicts.** No readiness band, no win figure, no percentage, no distance band
     * derived from metres and no race fact beyond the stored title and tier (`ADR-0016`, `ADR-0020` §3).
     *
     * @return array{shown: bool, notice: string|null, run: list<array<string, mixed>>, this_turn: array{turn: int, year_label: string, offered: int, recorded: list<array<string, mixed>>}|null, ahead: list<array<string, mixed>>, absences: array{turn_link: string, goal: string}, empty: array{run: string|null, this_turn: string|null, ahead: string|null}, links: array{decision_url: string, run_url: string}}
     */
    private function raceStripSection(TrainingRun $run): array
    {
        $links = [
            'decision_url' => route('runs.races.decision', $run),
            'run_url' => route('runs.show', $run),
        ];
        $absences = [
            'turn_link' => 'The Trainer has not tied this race to a logged turn (KI-17), so the turn is not known rather than zero.',
            'goal' => 'No column records this trainee\'s goal races (KI-34). The client marks a goal per character and that table is not sourced, so this list holds only the calendar\'s own mandatory races.',
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

        // The run's own history, oldest logged turn first and unlinked rows last. An array key sorts
        // element-wise, which is how the tie-break on the entry id rides along with the turn. The sort
        // reads the foreign key rather than the relation: the column is `nullable()->nullOnDelete()`
        // (a deleted turn must not delete its race), and testing it is the same answer the relation
        // would give without depending on how the analyzer models a `belongsTo`.
        /** @var list<array<string, mixed>> $recorded */
        $recorded = $run->raceEntries
            ->sortBy(fn (RaceEntry $entry): array => [$entry->turn_entry_id === null ? PHP_INT_MAX : $entry->turnEntry->turn, $entry->id])
            ->map(fn (RaceEntry $entry): array => $this->stripRow($entry))
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
                ->map(fn (RaceEntry $entry): array => $this->stripRow($entry))
                ->values()
                ->all(),
        ];

        $enteredIds = $run->raceEntries->pluck('race_catalog_slot_id')->filter();

        /** @var list<array<string, mixed>> $ahead */
        $ahead = $next === null ? [] : RaceCatalogSlot::query()
            ->forScenario($run->scenarioKey())
            ->where('is_mandatory', true)
            ->orderBy('year')
            ->orderBy('turn')
            ->get()
            ->reject(fn (RaceCatalogSlot $slot): bool => $slot->isAtOrBeforeTurn($next)
                || $enteredIds->contains($slot->id))
            ->map(fn (RaceCatalogSlot $slot): array => [
                'id' => $slot->id,
                'title' => $slot->title,
                'tier' => $slot->tier,
                'year_label' => $slot->yearLabel(),
                'turn' => $slot->turnNumber(),
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
                    ? 'No race is recorded for this run yet, so there is no history to read here. Log a turn on the run record screen, or open Race Decision to enter one at the turn you are in.'
                    : null,
                'this_turn' => match (true) {
                    $next === null => 'This run has no turn being decided, so there is no turn to read a race against. The run record screen holds the full history.',
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
     * @return array<string, mixed>
     */
    private function stripRow(RaceEntry $entry): array
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
    private function scenarioSection(TrainingRun $run, array $team): array
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
            'finale' => $def['finale'] ?? null,
            'finale_absence' => isset($def['finale'])
                ? null
                : 'This scenario declares no finale structure, so none is shown.',
            // The five Trackblazer-specific sections (plan §9 E4). Each is null for any scenario
            // whose matrix does not turn the matching flag on, so the section's key set stays
            // uniform across the four Global scenarios and the ScenarioPanelTest pin holds.
            // The pino legitimate of E4 is that the renderer reads these never by a scenario
            // identity field.
            'grade' => $this->gradeSection($run),
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
        $caps = ScenarioCaps::forRun($run);
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
    private function gradeSection(TrainingRun $run): ?array
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

        return [
            'catalogue' => $catalogue,
            'purchases' => $this->trackblazerPurchases($run),
            'rotation_turns' => (int) ($def['rotation_turns'] ?? 0) ?: null,
            'rotation_resets_in' => $run->shop_resets_in,
            'max_copies' => (int) ($def['max_copies_per_item'] ?? 0),
            'spend_total' => $run->composesShop() ? $run->shopSpendTotal() : 0,
            'fill' => route('runs.purchases.store', $run),
            'select_action' => route('runs.update', $run),
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

            if (! $recorded && $reached && $turn !== null && $run->status === RunStatus::Active) {
                $alerts[] = [
                    'level' => 'Critical',
                    'text' => $slot->title.' is due and not recorded',
                    'detail' => 'The calendar puts it at '.$slot->yearLabel().' turn '.$turn
                        .', and the next turn to play is '.$next['turn'].'.',
                ];
            }
        }

        return [
            'objectives' => [
                [
                    'group' => 'Career goals',
                    'rows' => [[
                        'label' => 'Per-character career goals',
                        'state' => null,
                        'year_label' => null,
                        'turn' => null,
                        'race_url' => null,
                        // design-2.0 §29's three parts: what is missing, why it matters, what unblocks
                        // it. The unblock names a schema, not a date, because no date is knowable here.
                        'absence' => 'No table in this tool holds a trainee\'s own objectives: trainee_goals'
                            .' does not exist, so the goals the client marks with a red banner cannot be'
                            .' listed. The mandatory races below are the scenario-wide record and are not a'
                            .' substitute for them. A schema proposal with a PRD citation is what unblocks'
                            .' this module.',
                    ]],
                ],
                [
                    'group' => 'Mandatory races',
                    'rows' => $rows,
                ],
                [
                    'group' => 'Happy Meek',
                    'rows' => $this->happyMeekRows(),
                ],
            ],
            'alerts' => $alerts,
        ];
    }

    /**
     * The four fields SCREEN-014 names for Happy Meek, each an unrecorded reading with its own reason.
     *
     * Nothing stores a level, so there is nothing to badge as Confirmed and no write route to record
     * one through; the brief forbids a migration, so the module is read-only and says why. The last
     * two fields are not merely unrecorded but unsourced, which is a different sentence and gets one.
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
                'absence' => 'No source this tool reads publishes a reward table for Happy Meek, so no'
                    .' figure is shown. An estimate would be an invented number.',
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
