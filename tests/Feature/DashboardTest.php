<?php

declare(strict_types=1);

use App\Domain\Career\CareerPosition;
use App\Enums\RunMode;
use App\Enums\RunStatus;
use App\Enums\TurnEventType;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\TurnEvent;
use App\Models\Umamusume;
use App\Models\Veteran;
use Inertia\Testing\AssertableInertia as Assert;

// ADR-0020 §1: the 2.0 SPA shell renders through Inertia. SCREEN-001's props are asserted here;
// the rendered copy, the 44px sweep and the keyboard path live in `tests/browser/dashboard.spec.ts`
// (plan §2 convention 2). The shared `app` props come from `HandleInertiaRequests` and reach every
// page, so they are asserted once at the bottom rather than per case.

/**
 * One run with `$turns` logged turns, the last carrying `$energy`.
 *
 * `stripValues()` reads Energy off the latest logged turn and counts the turns, so a run's Energy and
 * its turn count are two readings of the same rows and the fixture has to write them together.
 */
function runWithTurns(array $runState, int $turns, ?int $energy = null): TrainingRun
{
    $run = TrainingRun::factory()->create($runState);

    for ($turn = 1; $turn <= $turns; $turn++) {
        TurnEntry::factory()->create([
            'training_run_id' => $run->id,
            'turn' => $turn,
            'energy' => $turn === $turns ? $energy : 50,
        ]);
    }

    return $run;
}

it('renders the dashboard with no active career, an empty Veteran list and the data-status badge', function (): void {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            // design-2.0 §29: the empty state is a real state, not a blank panel, so the prop is
            // null rather than a zeroed career.
            ->where('activeCareer', null)
            // C3's library is empty, and the panel says so rather than printing "0 Veterans" as
            // though a count of nothing were a fact about the Trainer.
            ->where('recentVeterans.rows', [])
            ->where('recentVeterans.total', 0)
            ->where('recentVeterans.limit', 3)
            ->has('recentVeterans.library_url')
            // Every destination here is one the sidebar does not carry. The three the card used
            // to show (New Career, Legacy Lab, Support Cards) all sit in the primary nav, so the
            // card repeated a path the Trainer already had a click for; `quickActions()` replaced
            // them, and this assertion follows the code rather than the retired design.
            ->has('quickActions', 4)
            ->where('quickActions.0.label', 'Import a historical career')
            ->where('quickActions.1.label', 'Review queue')
            ->where('quickActions.2.label', 'Open settings')
            ->where('quickActions.3.label', 'Database hub')
            // The badge's date is a config fact, so it cannot drift from the matrix it describes.
            ->where('dataStatus.label', 'GLOBAL DATA')
            ->where('dataStatus.state', 'Current')
            ->where('dataStatus.verified_at', config('scenarios.verified_at'))
            ->where('dataStatus.verified_at', '2026-09-27')
            ->has('counts.trainees')
            ->has('counts.skills')
            ->has('counts.supportCards'));
});

it('names the active career with its scenario, position, turn, energy and primary resource', function (): void {
    $run = runWithTurns([
        'scenario' => 'unity_cup',
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->state([
            'name' => 'Tokai Teio',
            'name_ja' => 'トウカイテイオー',
        ]),
    ], turns: 13, energy: 72);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('activeCareer.run_id', $run->id)
            ->where('activeCareer.trainee', 'Tokai Teio')
            ->where('activeCareer.trainee_ja', 'トウカイテイオー')
            ->where('activeCareer.scenario_label', 'Unity Cup')
            // Turn 13 is Junior Year, Early July on the client's 24-turn grid: 24 turns a year, two
            // per month, Early then Late.
            ->where('activeCareer.position_label', 'Junior Year · Early July')
            ->where('activeCareer.turn', 13)
            // The cockpit's standing for a played-from-turn-one run: one past the count, which is
            // the normal reading the discrepancy note must not fire on.
            ->where('activeCareer.position_turn', 14)
            ->where('activeCareer.energy', 72)
            // Unity Cup's one widget beyond the baseline three is `team_rank`, and the rank now has
            // its own row with a real owner (`teamRank()` reads `latestTeamRank()`), so the resource
            // slot defers to it rather than rendering a second, permanently-absent Team Rank cell.
            ->where('activeCareer.resource', null)
            ->where('activeCareer.team_rank.value', null)
            ->where('activeCareer.team_rank.value_hint', 'No logged turn has reported a team rank yet.')
            ->where('activeCareer.resume_url', route('runs.cockpit', $run)));
});

it('hands over both turn readings when a snapshot stands ahead of its recorded log', function (): void {
    // The HFW's labelled defect (index.html:102-107): a snapshot holds one entry but stands at the
    // position it was imported at, so the page prints the count and the cockpit's turn side by side.
    $run = TrainingRun::factory()->create([
        'scenario' => 'unity_cup',
        'status' => RunStatus::Active,
        'mode' => RunMode::Snapshot,
        'career_position' => CareerPosition::fromTurnIndex(67),
        'career_position_source' => 'imported',
    ]);

    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 67]);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('activeCareer.turn', 1)
            ->where('activeCareer.position_turn', 67));
});

it('reads the reported team rank onto the active career card', function (): void {
    $run = runWithTurns(['scenario' => 'unity_cup', 'status' => RunStatus::Active], turns: 4, energy: 60);

    TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => 4,
        'event_type' => TurnEventType::Scenario,
        'source_name' => 'Unity Training',
        'deltas' => ['rank' => 'S'],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('activeCareer.team_rank.value', 'S')
            ->where('activeCareer.resource', null));
});

it('names the team rank absence for a scenario that composes no ladder', function (): void {
    runWithTurns(['scenario' => 'ura_finale', 'status' => RunStatus::Active], turns: 2, energy: 60);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('activeCareer.team_rank.value', null)
            ->where('activeCareer.team_rank.value_hint', 'The run\'s scenario composes no team rank ladder, so there is no rank to show.'));
});

it('reads the primary resource from the scenario config rather than from a scenario name', function (): void {
    // Two scenarios, two different resources, one code path. A Trackblazer run resolves `grade_points`
    // and a Unity Cup run resolves `team_rank`, both by subtracting the baseline's widget list.
    $trackblazer = runWithTurns(['scenario' => 'trackblazer', 'status' => RunStatus::Active], turns: 1, energy: 80);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('activeCareer.run_id', $trackblazer->id)
            ->where('activeCareer.resource.label', 'Grade Points'));

    // A scenario that composes no widget beyond the baseline three names no resource at all, rather
    // than defaulting to one of the three every scenario has.
    TrainingRun::query()->update(['status' => RunStatus::Retired]);

    runWithTurns(['scenario' => 'ura_finale', 'status' => RunStatus::Active], turns: 1, energy: 80);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('activeCareer.resource', null));
});

it('claims no position and no energy for an active career that has logged no turn', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'trackblazer', 'status' => RunStatus::Active]);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('activeCareer.run_id', $run->id)
            ->where('activeCareer.turn', 0)
            // A run with nothing logged has no position to claim and no Energy anyone recorded; both
            // are null, never a zero and never Early January (D-220).
            ->where('activeCareer.position_label', null)
            ->where('activeCareer.energy', null)
            ->where('activeCareer.resource.value', null));
});

it('resumes only an Active run and names the absence when the only runs are finished', function (): void {
    TrainingRun::factory()->create(['status' => RunStatus::Completed]);
    TrainingRun::factory()->create(['status' => RunStatus::Retired]);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('activeCareer', null));
});

it('lists the newest Veterans, capped, and counts the whole library', function (): void {
    foreach (range(1, 5) as $index) {
        $run = TrainingRun::factory()->create([
            'status' => RunStatus::Completed,
            'scenario' => 'trackblazer',
            'umamusume_id' => Umamusume::factory()->state(['name' => "Veteran {$index}"]),
        ]);

        Veteran::factory()->create(['training_run_id' => $run->id]);
    }

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('recentVeterans.rows', 3)
            ->where('recentVeterans.total', 5)
            // Newest first, which is `ListVeterans`' own order.
            ->where('recentVeterans.rows.0.trainee', 'Veteran 5')
            ->where('recentVeterans.rows.0.scenario_label', 'Trackblazer')
            ->has('recentVeterans.rows.0.run_url')
            ->where('recentVeterans.rows.2.trainee', 'Veteran 3'));
});

it('shares the app props every page reads, with the ruleset as a named absence', function (): void {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('app.name', config('app.name'))
            ->has('app.version')
            // No source defines a Global ruleset version (design-2.0 §48), so the page prints N/A
            // with a reason rather than a version it does not hold.
            ->where('app.ruleset', null));
});

it('counts the active careers so a second one can be named, with the list link', function (): void {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('activeCareerCount', 0)
            ->where('careersUrl', route('runs.index')));

    TrainingRun::factory()->create(['status' => RunStatus::Active]);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('activeCareerCount', 1));

    TrainingRun::factory()->create(['status' => RunStatus::Active]);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('activeCareerCount', 2));
});

it('keeps the sourced catalogue count distinct from the operational trainee count', function (): void {
    // DM-2: the KPI prints the catalogue figure, and the difference against this database is a fact
    // the payload carries rather than a number the page guesses. The join rule is the dataset's own
    // (`assets/data/README.md`): a trainee is catalogued when the pipeline stamped a source reference.
    Umamusume::factory()->create(['name' => 'Sourced One', 'external_ref' => 'game-tora-1']);
    Umamusume::factory()->create(['name' => 'Fixture One']);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('counts.catalogueTrainees', 1)
            ->where('counts.trainees', 2)
            // The stale flag is the tile's risk border, so it travels as a bool rather than being
            // read back out of the displayed state word.
            ->where('dataStatus.stale', false));
});
