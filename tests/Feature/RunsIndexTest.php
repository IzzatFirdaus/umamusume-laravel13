<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The run list, ported to Inertia (ADR-0020 §1). This file asserts the resolved props; the
 * rendered copy and the pagination control live in tests/browser/runs.spec.ts.
 *
 * Two claims are worth a guard rather than an assertion of convenience:
 *
 *   - The row carries the scenario *label*, and the storage key is not in the payload at all.
 *     The key is an internal slug; a page that receives it can print `ura_finale` where a name
 *     belongs, so the prop shape is what keeps that off the screen.
 *   - The page size of 25 is the number that decides whether a Trainer with a long history
 *     sees a second page.
 */
it('sends an empty list when there are no runs', function (): void {
    test()->get('/training-runs')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Index')
            ->has('runs.data', 0)
            ->where('runs.total', 0));
});

it('sends each run under the trainee name and the scenario label, with no storage key in the payload', function (): void {
    $umamusume = Umamusume::factory()->create(['name' => 'Special Week']);
    TrainingRun::factory()->create([
        'umamusume_id' => $umamusume->id,
        'scenario' => 'ura_finale',
    ]);

    test()->get('/training-runs')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('runs.data.0.name', 'Special Week')
            ->where('runs.data.0.scenario_label', config('scenarios.scenarios.ura_finale.label'))
            // The key would be a scenario name the Trainer never chose, one prop away from
            // being printed (TrainingRun::hasScenario, D-221).
            ->missing('runs.data.0.scenario'));
});

it('sends a null scenario label for a run that has none, rather than a blank one', function (): void {
    $umamusume = Umamusume::factory()->create(['name' => 'Special Week']);
    TrainingRun::factory()->create(['umamusume_id' => $umamusume->id, 'scenario' => null]);

    test()->get('/training-runs')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('runs.data.0.name', 'Special Week')
            ->where('runs.data.0.scenario_label', null));
});

it('sends the status value and word plus both row doors', function (): void {
    $run = TrainingRun::factory()->create(['status' => RunStatus::Completed]);

    test()->get('/training-runs')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('runs.data.0.status', RunStatus::Completed->value)
            ->where('runs.data.0.status_label', $run->status->label())
            ->where('runs.data.0.url', route('runs.cockpit', $run))
            ->where('runs.data.0.result_url', route('runs.result', $run)));
});

it('filters by status, scenario and trainee search, and treats all as unfiltered', function (): void {
    $rice = Umamusume::factory()->create(['name' => 'Rice Shower']);
    $vodka = Umamusume::factory()->create(['name' => 'Vodka']);

    $active = TrainingRun::factory()->create([
        'umamusume_id' => $rice->id,
        'scenario' => 'unity_cup',
        'status' => RunStatus::Active,
    ]);
    $finished = TrainingRun::factory()->create([
        'umamusume_id' => $vodka->id,
        'scenario' => 'ura_finale',
        'status' => RunStatus::Completed,
    ]);

    test()->get('/training-runs?status=Completed')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('runs.data', 1)
            ->where('runs.data.0.id', $finished->id)
            ->where('filters.status', 'Completed'));

    test()->get('/training-runs?scenario=unity_cup')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('runs.data', 1)
            ->where('runs.data.0.id', $active->id));

    test()->get('/training-runs?search=Vodka')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('runs.data', 1)
            ->where('runs.data.0.id', $finished->id));

    test()->get('/training-runs?status=all&scenario=all&search=')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('runs.data', 2));
});

it('refuses a status outside the enum and returns to the list', function (): void {
    test()->get('/training-runs?status=Gone')
        ->assertRedirect(route('runs.index'));
});

it('links each row to the run', function (): void {
    $run = TrainingRun::factory()->create();

    test()->get('/training-runs')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('runs.data.0.url', route('runs.cockpit', $run)));
});

it('sends newest first', function (): void {
    $older = TrainingRun::factory()->create(['created_at' => now()->subWeek()]);
    $newer = TrainingRun::factory()->create(['created_at' => now()]);

    test()->get('/training-runs')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('runs.data.0.url', route('runs.cockpit', $newer))
            ->where('runs.data.1.url', route('runs.cockpit', $older)));
});

it('exposes the run id and distinguishes two runs of the same trainee', function (): void {
    // R4-01/R4-02: run 26 appeared only in the URL. Two careers of the same trainee in the same scenario
    // rendered two rows a Trainer could not tell apart, because `id` was never shipped.
    $umamusume = Umamusume::factory()->create(['name' => 'Rice Shower']);
    $first = TrainingRun::factory()->create(['umamusume_id' => $umamusume->id, 'scenario' => 'unity_cup']);
    $second = TrainingRun::factory()->create(['umamusume_id' => $umamusume->id, 'scenario' => 'unity_cup']);

    test()->get('/training-runs')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('runs.data', 2)
            ->where('runs.data.0.id', $second->id)
            ->where('runs.data.1.id', $first->id)
            // The two rows are the same trainee, scenario and status, so the id is the only thing that
            // can tell them apart.
            ->where('runs.data.0.name', 'Rice Shower')
            ->where('runs.data.1.name', 'Rice Shower'));
});

it('does not render a hardcoded turn for an active run', function (): void {
    // R4-30: the Careers row printed `Turn ~11 / 24` and `width: 45%` for every Active run,
    // regardless of the run's actual position. The row now ships the real turn_index.
    $run = TrainingRun::factory()->create(['status' => RunStatus::Active]);

    test()->get('/training-runs')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('runs.data', 1)
            ->where('runs.data.0.turn_index', 0)
            ->where('runs.data.0.turn_count', 72));
});

it('orders two runs created in the same second by id, newest first', function (): void {
    $first = TrainingRun::factory()->create(['created_at' => '2026-10-24 12:00:00']);
    $second = TrainingRun::factory()->create(['created_at' => '2026-10-24 12:00:00']);

    test()->get('/training-runs')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('runs.data.0.id', $second->id)
            ->where('runs.data.1.id', $first->id));
});

it('paginates at twenty-five and reaches a second page', function (): void {
    TrainingRun::factory()->count(26)->create();

    // 26 runs at a page size of 25 is the smallest case that proves the boundary is 25 and
    // not 50: one item over a single page, and the second page is reachable.
    test()->get('/training-runs')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('runs.data', 25)
            ->where('runs.per_page', 25)
            ->where('runs.current_page', 1)
            ->where('runs.last_page', 2));

    test()->get('/training-runs?page=2')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('runs.data', 1)
            ->where('runs.current_page', 2));
});
