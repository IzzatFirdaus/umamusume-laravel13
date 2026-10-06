<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
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
            ->has('quickActions', 3)
            ->where('quickActions.0.label', 'New Career')
            ->where('quickActions.1.label', 'Legacy Lab')
            ->where('quickActions.2.label', 'Support Cards')
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
            ->where('activeCareer.energy', 72)
            // Unity Cup's one widget beyond the baseline three is `team_rank`, read from config by
            // subtraction rather than by scenario name (G-33).
            ->where('activeCareer.resource.label', 'Team Rank')
            ->where('activeCareer.resource.value', null)
            ->has('activeCareer.resource.value_hint')
            ->where('activeCareer.resume_url', route('runs.show', $run)));
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
