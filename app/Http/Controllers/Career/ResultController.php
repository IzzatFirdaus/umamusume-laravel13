<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Enums\SkillAcquisition;
use App\Http\Controllers\Controller;
use App\Models\RaceEntry;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Services\Advisor\TrainerAdvisor;
use App\Services\Scenario\FinaleReader;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The Career Result, `SCR-CAR-018` (SCREEN-019, plan §8 D15). What a finished career adds up to.
 *
 * **A finished run only.** `RunStatus` has three cases and only `Completed` is finished. `Active`
 * and `Retired` are two different absences rather than one: an Active career is still being played
 * — the screen says so and sends the Trainer back to the Cockpit, which is where the next turn is
 * decided — while a Retired career was abandoned before completion, so inviting a turn decision
 * would be a door onto a screen that has no turn to offer. The two states carry different copy
 * because they are different facts (`AGENTS.md` §5's no-default rule).
 *
 * **What the screen may not say.** No build-quality score, no aggregate target-completion
 * percentage, no legacy-value rating and no best-use recommendation. The first three are
 * aggregates the corpus cannot price, and the fourth is `SCREEN-020`'s Factor Analysis, which
 * `ADR-0020` §3 keeps out of record screens. Per-stat deficit is the one arithmetic that ships,
 * because it is subtraction over two entered numbers, and it is **read from
 * `TrainerAdvisor::deficits()`** rather than recomputed here so the result and the ranking can
 * never disagree (`ADR-0015`).
 *
 * **`screen-spec-2.0`'s "build quality" section is omitted as a ruling, not by oversight.** The
 * brief's SCREEN-019 correction row upgrades the screen to a Veteran Creation workflow with a
 * factor quality assessment and a best-use recommendation; both are `ADR-0020` §3's held
 * computation, and neither has a column to read even if it were permitted. The omission is
 * recorded in `SCREEN_SPEC.md` SCR-CAR-018's Gaps.
 *
 * **Save Veteran was a named absence; it is a door since D16 landed.** The brief reads "Primary action
 * 'Save Veteran' to D16"; when this slice shipped, D16's route did not exist, so a link would have been a
 * dead link and the absence was named in copy instead. `7b04b6c` then landed `runs.veteran` and
 * `RecordVeteran`, and `saveVeteranSection()` below carries the door with its reason beside it. The
 * paragraph above is kept as the record of what was true when the screen was written; it stopped being
 * true at that commit, and `tests/browser/career-result.spec.ts` was corrected to match on 2026-10-08.
 */
class ResultController extends Controller
{
    public function show(TrainingRun $run): Response
    {
        // The run with everything the three sections read, so no section costs an N+1.
        $run->load([
            'umamusume',
            'turnEntries',
            'raceEntries.raceCatalogSlot',
            'raceEntries.scenarioSlot',
            'raceEntries.turnEntry',
            'skills',
            'veteran',
        ]);

        $finished = $run->status === RunStatus::Completed;
        $latest = $finished ? $run->turnEntries->sortByDesc('turn')->first() : null;

        return Inertia::render('Career/Result', [
            'run' => $this->runSection($run),
            'build' => $this->buildSection($run, $latest),
            'races' => $this->raceSection($run),
            'finale' => FinaleReader::forRun($run),
            'scenario' => $this->scenarioSection($run),
            'save_veteran' => $this->saveVeteranSection($run),
            'ruleset' => $this->rulesetSection(),
            'empty' => $this->emptyState($run),
        ]);
    }

    /**
     * @return array{id: int, trainee: string, trainee_ja: string|null, status: string, status_label: string, scenario_label: string, run_url: string, cockpit_url: string, timeline_url: string}
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
            'run_url' => route('runs.cockpit', $run),
            'cockpit_url' => route('runs.cockpit', $run),
            'timeline_url' => route('runs.timeline', $run),
        ];
    }

    /**
     * The final build: five stats against the targets they were set against, the skills the run
     * learned, and the trainee's own aptitude letters.
     *
     * **The deficit is the advisor's own number.** `TrainerAdvisor::deficits()` owns the
     * `max(0, target - current)` arithmetic, and this screen reads it rather than restating it
     * (`ADR-0015`). It is null — with a `title` naming which absence it is — when the run entered
     * no target or logged no turn, and never a deficit measured against a target of zero.
     *
     * `aptitudes` is the trainee's, not the run's: no column records an aptitude changing during a
     * career, so the section says what the trainee came with rather than implying the run earned it.
     *
     * @return array<string, mixed>
     */
    private function buildSection(TrainingRun $run, ?TurnEntry $latest): array
    {
        if ($run->status !== RunStatus::Completed) {
            return [
                'stats' => [],
                'target' => null,
                'skills' => ['learned' => [], 'not_learned' => 0, 'empty' => null],
                'aptitudes' => [],
            ];
        }

        /** @var list<string> $order */
        $order = config('scenarios.stat_order');
        $target = $run->buildTarget();
        $deficits = app(TrainerAdvisor::class)->deficits($latest, $target);

        $stats = [];

        foreach ($order as $stat) {
            $stats[] = [
                'key' => $stat,
                'label' => $stat,
                'current' => $this->currentStat($latest, $stat),
                'target' => $target?->targets[$stat] ?? null,
                'deficit' => $deficits[$stat] ?? null,
                'deficit_title' => $this->deficitTitle($target !== null, $latest !== null),
            ];
        }

        $learned = [];
        $notLearned = 0;

        foreach ($run->skills as $skill) {
            if ($skill->pivot->status === SkillAcquisition::Acquired->value) {
                $learned[] = [
                    'id' => $skill->id,
                    'name' => $skill->name,
                    'name_ja' => $skill->name_ja,
                    'turn_acquired' => $skill->pivot->turn_acquired,
                ];

                continue;
            }

            // Everything the run carries but did not learn: Suggested (never triggered) and
            // Skipped (triggered and passed). They are one number here because the screen asks
            // what the career ended with, and neither is a skill it ended with.
            $notLearned++;
        }

        return [
            'stats' => $stats,
            'target' => $target === null ? null : [
                'purpose_label' => $target->purpose->label(),
                'distance' => $target->distance,
                'surface' => $target->surface,
                'style' => $target->style,
            ],
            'skills' => [
                'learned' => $learned,
                'not_learned' => $notLearned,
                'empty' => $learned === []
                    ? 'No skill is recorded as learned on this run. Skills are marked on the run record screen as the career passes them.'
                    : null,
            ],
            'aptitudes' => $this->aptitudes($run),
        ];
    }

    /**
     * Why a deficit has no number, or null when it has one.
     *
     * Two absences, told apart. A run that entered no target has nothing to measure against; a run
     * with a target but no logged turn has nothing to measure. The `title` names which, so a
     * Trainer reading `N/A` knows what to record rather than guessing.
     */
    private function deficitTitle(bool $hasTarget, bool $hasTurn): ?string
    {
        if (! $hasTarget) {
            return 'No build target was set for this run, so there is no target to measure against.';
        }

        if (! $hasTurn) {
            return 'This run logged no turn, so there is no value to measure against the target.';
        }

        return null;
    }

    /**
     * The trainee's ten aptitude letters, or an empty list when the source published none.
     *
     * The axis list and its empty rule live on `Umamusume::aptitudeAxes()`, the model that owns the ten
     * columns, because the Veteran comparison (`SCREEN-022`) reads the same letters for the same trainee and
     * two label lists would be two answers about one trainee. `aptitude_turf` is the sentinel the parser's
     * all-or-nothing write makes reliable.
     *
     * @return list<array{key: string, label: string, letter: string|null}>
     */
    private function aptitudes(TrainingRun $run): array
    {
        return $run->umamusume->aptitudeAxes();
    }

    /**
     * The race history, counted the way the stored facts actually divide.
     *
     * **Four counts, never two.** `race_entries.status` and `placement` are independent columns:
     * a completed race whose finish was never entered is neither a win nor a placing below first,
     * and folding it into "losses" would assert a result nobody recorded. So the counts are
     * completed, wins (`placement` 1), placings below first (`placement` above 1) and finishes not
     * recorded, plus the G1 subset of the wins. `Skipped` and `NotOffered` rows are not races the
     * career ran and no count reads them.
     *
     * No win rate is computed: two of the four numbers would be a denominator chosen to flatter the
     * ratio, and a percentage over them is the aggregate this slice declines.
     *
     * @return array<string, mixed>
     */
    private function raceSection(TrainingRun $run): array
    {
        if ($run->status !== RunStatus::Completed) {
            return ['counts' => [], 'rows' => [], 'empty' => null];
        }

        $completed = $run->raceEntries->where('status', RaceEntryStatus::Completed);

        $wins = $completed->filter(
            static fn (RaceEntry $entry): bool => $entry->placement === 1
        );

        $rows = $completed
            ->sortBy('id')
            ->map(fn (RaceEntry $entry): array => $this->raceRow($entry))
            ->values()
            ->all();

        return [
            'counts' => [
                'completed' => $completed->count(),
                'wins' => $wins->count(),
                'below_first' => $completed->filter(
                    static fn (RaceEntry $entry): bool => $entry->placement !== null && $entry->placement > 1
                )->count(),
                'placement_unrecorded' => $completed->whereNull('placement')->count(),
                'g1_wins' => $wins->filter(
                    static fn (RaceEntry $entry): bool => $entry->tierKey() === 'G1'
                )->count(),
            ],
            'rows' => $rows,
            'empty' => $completed->isEmpty()
                ? 'No completed race is recorded for this run, so there is no race history to read. Races are entered on the Race decision screen or the run record screen.'
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function raceRow(RaceEntry $entry): array
    {
        return [
            'id' => $entry->id,
            'title' => $entry->raceCatalogSlot->title ?? $entry->scenarioSlot->title ?? 'a race with no calendar row',
            'tier' => $entry->tierKey(),
            'placement' => $entry->placement === null ? null : $entry->placementOrdinal(),
            'turn' => $entry->turnEntry?->turn,
        ];
    }

    /**
     * The scenario's own objectives, read through the composition matrix.
     *
     * `gradePeriods()` carries the ladder, what each period earned and how many finishes it could
     * not price; `gradeEarned()` is the live period's headline and null until a Trainer names one.
     * A scenario that composes no grade objectives gets a sentence saying so rather than an empty
     * panel. Rewards stay `N/A`: no column holds one, and the Race Decision screen already names
     * the same absence for race payouts.
     *
     * @return array<string, mixed>
     */
    private function scenarioSection(TrainingRun $run): array
    {
        if ($run->status !== RunStatus::Completed) {
            return [
                'composed' => false,
                'notice' => null,
                'periods' => [],
                'earned' => null,
                'unpriced' => 0,
                'unassigned' => 0,
                'rewards' => null,
            ];
        }

        $composed = $run->composesGradeObjectives();

        return [
            'composed' => $composed,
            'notice' => $composed
                ? null
                : 'This scenario keeps no Grade Point objectives, so there is no objective ladder to read here. Its own panel arrives with the scenario panels.',
            'periods' => $run->gradePeriods(),
            'earned' => $run->gradeEarned(),
            'unpriced' => $run->gradeUnpricedCount(),
            'unassigned' => $run->gradeUnassignedCount(),
            // No column holds a race reward on any table this tool reads, so the section names the
            // absence once instead of printing a row of dashes (the same ruling SCR-CAR-013 made).
            'rewards' => 'No source this tool reads records a race reward or a Skill Point payout, so none is shown here. What the client paid can be recorded on the run screen.',
        ];
    }

    /**
     * The door to Save Veteran (`SCREEN-020`).
     *
     * This section used to name the screen as an absence, because D16 had not landed and a link would have
     * been a dead link. It has now: `runs.veteran` exists, files the career through `RecordVeteran`, and
     * prefills from an existing row, so a career already in the library is offered the same door with the
     * truth beside it rather than a second route for editing.
     *
     * The key set is pinned by `CareerResultTest`, so `url` arrives with a test naming it.
     *
     * @return array{available: bool, reason: string, url: string}
     */
    private function saveVeteranSection(TrainingRun $run): array
    {
        $finished = $run->status === RunStatus::Completed;

        return [
            'available' => $finished,
            'reason' => ! $finished
                ? 'A career is filed once it is Completed. This one is '.$run->status->label().', so there is nothing to save yet.'
                : ($run->veteran !== null
                    ? 'This career is already in the Veteran library. Saving again rewrites its tags and note rather than filing it a second time.'
                    : 'Adds your own tags and note to this career in the Veteran library. The library stores what you enter and derives nothing from it.'),
            'url' => route('runs.veteran', $run),
        ];
    }

    /**
     * `app.ruleset` is null in the shared props and no source defines a Global ruleset version, so
     * the line is a named absence with its reason rather than an invented string
     * (`docs/proposals/design-2.0.md` §48).
     *
     * @return array{value: null, title: string}
     */
    private function rulesetSection(): array
    {
        return [
            'value' => null,
            'title' => 'No source defines a Global ruleset version, so this run stores none to print.',
        ];
    }

    /**
     * The two unfinished states, told apart, or null for a Completed run.
     *
     * @return array{reason: string, message: string}|null
     */
    private function emptyState(TrainingRun $run): ?array
    {
        return match ($run->status) {
            RunStatus::Completed => null,
            RunStatus::Active => [
                'reason' => 'active',
                'message' => 'This career is still running, so there is no result to read yet. The result screen reads a run whose status is Completed; until then the Cockpit is where the next turn is decided, and the run record screen is where the status changes.',
            ],
            RunStatus::Retired => [
                'reason' => 'retired',
                'message' => 'This career was retired before it finished, so no result exists to read. The turns and races it did record stay readable on the Career Timeline and the run record screen.',
            ],
        };
    }

    /**
     * ponytail: the fourth private copy of this matrix-name lookup (Cockpit, TrainingDecision,
     * EventDecision). The upgrade path D9's copy named — one accessor on `TurnEntry` — is now
     * overdue and becomes a small refactor slice; it is not this screen's change to make, because
     * it edits three landed controllers and their tests.
     */
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
