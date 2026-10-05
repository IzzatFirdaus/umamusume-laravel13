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

it('sends the status word and the created date on the row', function (): void {
    $run = TrainingRun::factory()->create(['status' => RunStatus::Completed]);

    test()->get('/training-runs')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('runs.data.0.status_label', $run->status->label())
            ->where('runs.data.0.created_date', $run->created_at->toDateString()));
});

it('links each row to the run', function (): void {
    $run = TrainingRun::factory()->create();

    test()->get('/training-runs')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('runs.data.0.url', route('runs.show', $run)));
});

it('sends newest first', function (): void {
    $older = TrainingRun::factory()->create(['created_at' => now()->subWeek()]);
    $newer = TrainingRun::factory()->create(['created_at' => now()]);

    test()->get('/training-runs')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('runs.data.0.url', route('runs.show', $newer))
            ->where('runs.data.1.url', route('runs.show', $older)));
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
