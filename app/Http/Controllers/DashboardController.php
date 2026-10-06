<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ListVeterans;
use App\Enums\RunStatus;
use App\Models\RaceCatalogSlot;
use App\Models\Skill;
use App\Models\SupportCard;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use App\Models\Veteran;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The Trainer Desk 2.0 landing screen (SCREEN-001, `docs/proposals/screen-spec-2.0.md`, `ADR-0020` §1).
 *
 * Thin: every value here is a stored fact, a config value, or a named absence. Nothing is derived
 * beyond the career position, which is the client's own 24-turn calendar grid read back from a
 * recorded turn count (`TrainingRun::careerYearForTurn`'s own divisor). Three things the brief's
 * SCREEN-001 mockup asks for are deliberately absent, each for a stated reason:
 *
 * - **Recent Builds.** There is no "build" record and no query that lists one. A run is not a build
 *   until it holds a Career Plan (`build_target`, C1) and the wizard that enters one is D2–D7;
 *   rendering the runs list under the word "Builds" would label a run as something it is not. The
 *   page renders the absence with its reason rather than a fabricated list.
 * - **Legacy-goal gaps.** A gap is the active run's targets set against inherited stats, which is the
 *   inheritance computation `ADR-0020` §3 bans. C1 and C3 hold no honest comparison, so the panel is
 *   omitted rather than approximated.
 * - **A scenario resource value.** `stripValues()` carries the three widgets every scenario composes
 *   (turn, energy, fans); a scenario's own resource has no column, so the resource renders `N/A` with
 *   its reason, exactly as `TrainingRunController::stripFor()` renders it on the run page.
 *
 * Provenance before D4: only entered or stored values print. No figure here is calculated, so none
 * needs a `ProvenanceBadge` (`ADR-0020` §2).
 */
class DashboardController extends Controller
{
    /**
     * How many Veterans the recent list shows. A dashboard panel is a glance, not the library; the
     * full, filterable list is `/legacy` and the panel links to it.
     */
    private const RECENT_VETERANS = 3;

    /**
     * The month names the career grid counts in. Twelve turns per year is two per month, Early and
     * Late, which is the client's own grid (`TrainingRun::careerYearForTurn`). The data-pipeline
     * parser carries its own copy for race dates; this one names the turn a career is standing on.
     */
    private const MONTHS = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December',
    ];

    public function index(ListVeterans $veterans): Response
    {
        return Inertia::render('Dashboard', [
            'activeCareer' => $this->activeCareer(),
            'recentVeterans' => $this->recentVeterans($veterans),
            'quickActions' => $this->quickActions(),
            'counts' => [
                'trainees' => Umamusume::count(),
                'skills' => Skill::count(),
                'supportCards' => SupportCard::count(),
            ],
            'dataStatus' => $this->dataStatus(),
        ]);
    }

    /**
     * The career the Trainer is in the middle of, or null when there is none.
     *
     * More than one `Active` run is reachable (nothing in the schema forbids it and nothing on the
     * create form closes the previous one), so the newest is the one this screen resumes. That is a
     * choice, not a rule, and it is recorded as an open question on the slice rather than invented
     * into the schema.
     *
     * `stripValues()` reads the latest logged turn for Energy and Fans and counts the turns, which is
     * the same reading the run page's resource strip takes; Energy is null, not zero, when no turn has
     * recorded it (`ADR-0003`).
     *
     * @return array<string, mixed>|null
     */
    private function activeCareer(): ?array
    {
        $run = TrainingRun::query()
            ->with('umamusume')
            ->where('status', RunStatus::Active)
            ->latest('id')
            ->first();

        if ($run === null) {
            return null;
        }

        $strip = $run->stripValues();
        $turn = (int) $strip['turn'];

        return [
            'run_id' => $run->id,
            'trainee' => $run->umamusume->name,
            'trainee_ja' => $run->umamusume->name_ja,
            'scenario_label' => $this->scenarioLabel($run),
            'position_label' => $this->positionLabel($turn),
            'turn' => $turn,
            'energy' => $strip['energy'],
            'resource' => $this->primaryResource($run, $strip),
            // "Resume" continues the career, so it opens the Cockpit (SCREEN-009, `SCR-CAR-011`), the
            // screen the run is read from every turn, not the record sheet that logs its turns.
            'resume_url' => route('runs.cockpit', $run),
        ];
    }

    /**
     * Where the career is standing, in the client's own words: the year, then the Early or Late half
     * of a month. Null, never a default, for a run with no logged turn: it has no position to claim
     * and pointing at Early January would tell a Trainer who has not started that they are in it
     * (`TrainingRun::nextTurnToPlay()`'s reasoning, D-220).
     */
    private function positionLabel(int $turn): ?string
    {
        if ($turn < 1) {
            return null;
        }

        $year = TrainingRun::careerYearForTurn($turn);
        $position = (($turn - 1) % TrainingRun::TURNS_PER_YEAR) + 1;

        return (RaceCatalogSlot::YEARS[$year] ?? (string) $year).' Year · '
            .($position % 2 === 1 ? 'Early ' : 'Late ').self::MONTHS[intdiv($position - 1, 2)];
    }

    /**
     * The one resource the run's scenario composes beyond the three every scenario carries, or null
     * for a scenario that composes none (URA Finale, Our Grand Concert).
     *
     * Which widget that is comes from `config('scenarios')` by subtraction against the baseline
     * entry's own widget list, so a fifth scenario names its resource in one config line and no
     * component branches on a scenario name (D-240, gate G-33).
     *
     * @param  array<string, int|null>  $strip
     * @return array{label: string, value: int|null, value_hint: string}|null
     */
    private function primaryResource(TrainingRun $run, array $strip): ?array
    {
        $widget = $this->primaryWidget($run);

        if ($widget === null) {
            return null;
        }

        // No column holds a scenario resource yet, so this is always the absent arm today. The hint
        // travels with the value rather than sitting in the page's copy, so a reader of the element
        // alone still gets the reason (`LegacyController`'s Spark-chance cell made the same call).
        return [
            'label' => (string) config('scenarios.widget_labels.'.$widget, $widget),
            'value' => is_int($strip[$widget] ?? null) ? $strip[$widget] : null,
            'value_hint' => 'No column records this scenario resource yet, so this run holds no value for it.',
        ];
    }

    private function primaryWidget(TrainingRun $run): ?string
    {
        /** @var list<string> $widgets */
        $widgets = array_values((array) config('scenarios.scenarios.'.$run->scenarioKey().'.widgets'));
        /** @var list<string> $baseline */
        $baseline = array_values((array) config('scenarios.scenarios.'.config('scenarios.baseline').'.widgets'));

        foreach ($widgets as $widget) {
            if (! in_array($widget, $baseline, true)) {
                return $widget;
            }
        }

        return null;
    }

    /**
     * The newest Veterans, capped. `ListVeterans` is C3's own query and already eager-loads
     * `trainingRun.umamusume`, so the list is one query for the page plus one per relation and no
     * N+1; trainer data is never cached (`ARCHITECTURE.md` §6).
     *
     * @return array{rows: list<array<string, mixed>>, total: int, limit: int, library_url: string}
     */
    private function recentVeterans(ListVeterans $veterans): array
    {
        $page = $veterans->handle([], self::RECENT_VETERANS);

        return [
            'rows' => array_map(fn (Veteran $veteran): array => $this->veteranRow($veteran), $page->items()),
            'total' => $page->total(),
            'limit' => self::RECENT_VETERANS,
            'library_url' => route('legacy.index'),
        ];
    }

    /**
     * @return array{id: int, trainee: string, trainee_ja: string|null, scenario_label: string, run_id: int, run_url: string}
     */
    private function veteranRow(Veteran $veteran): array
    {
        $run = $veteran->trainingRun;

        return [
            'id' => $veteran->id,
            'trainee' => $run->umamusume->name,
            'trainee_ja' => $run->umamusume->name_ja,
            'scenario_label' => $this->scenarioLabel($run),
            'run_id' => $run->id,
            // A Veteran is a completed run, and the run is where its facts live, so the row opens
            // the run rather than a second reading of the same data (`Veteran`'s docblock).
            'run_url' => route('runs.show', $run),
        ];
    }

    /**
     * The three destinations a returning Trainer wants first. Every one is a live route; a
     * destination whose slice had not landed would be a named absence here rather than a dead link
     * (`AppLayout`'s `to: null` rule).
     *
     * @return list<array{label: string, to: string}>
     */
    private function quickActions(): array
    {
        return [
            ['label' => 'New Career', 'to' => route('runs.create')],
            ['label' => 'Legacy Lab', 'to' => route('legacy.index')],
            ['label' => 'Support Cards', 'to' => route('support-cards.index')],
        ];
    }

    /**
     * The badge the Data Versioning UI asks for (`design-2.0` §48). `verified_at` is a config fact,
     * not a literal, so the badge cannot drift from the matrix it describes. The ruleset version is
     * not here: it is the shared `app.ruleset` prop and it is null, so the page prints `N/A` with a
     * reason rather than a second, emptier copy of the same absence.
     *
     * @return array{label: string, state: string, verified_at: string|null}
     */
    private function dataStatus(): array
    {
        return [
            'label' => 'GLOBAL DATA',
            'state' => 'Current',
            'verified_at' => config('scenarios.verified_at'),
        ];
    }

    /**
     * The scenario's label, or a named absence. The storage key is never printed (D-240).
     */
    private function scenarioLabel(TrainingRun $run): string
    {
        if (! $run->hasScenario()) {
            return 'No scenario set';
        }

        return (string) config('scenarios.scenarios.'.$run->scenarioKey().'.label', $run->scenarioKey());
    }
}
