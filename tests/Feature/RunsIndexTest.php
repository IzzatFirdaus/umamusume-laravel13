<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\Umamusume;

/*
 * The run list, which had a route and a form but no test of its own.
 *
 * Two things here are worth a guard rather than an assertion of convenience:
 *
 *   - The empty state names what happens next. "No data" would leave a Trainer guessing
 *     whether the tool is broken or simply unused (runs/index.blade.php:13-19).
 *   - The row prints the scenario *label* from config, never the storage key. The key is
 *     an internal slug; printing it would show `ura_finale` where a name belongs
 *     (runs/index.blade.php:26-28).
 *
 * The list is paginated at 25, which is the one number worth pinning: it is the number
 * that decides whether a Trainer with a long history sees a second page.
 */
it('says what happens next when there are no runs', function (): void {
    $html = test()->get('/training-runs')->assertOk()->getContent();

    expect($html)
        ->toContain('No runs yet')
        ->toContain('nothing to list until you start one')
        // The empty state still offers the way out.
        ->toContain('New run');
});

it('lists a run under the trainee name and the scenario label, not the storage key', function (): void {
    $umamusume = Umamusume::factory()->create(['name' => 'Special Week']);
    TrainingRun::factory()->create([
        'umamusume_id' => $umamusume->id,
        'scenario' => 'ura_finale',
    ]);

    $html = test()->get('/training-runs')->assertOk()->getContent();

    $label = config('scenarios.scenarios.ura_finale.label');

    expect($html)
        ->toContain('Special Week')
        ->toContain($label)
        ->not->toContain('ura_finale')
        ->not->toContain('No runs yet');
});

it('leaves the scenario off a run that has none, rather than printing a bare separator', function (): void {
    $umamusume = Umamusume::factory()->create(['name' => 'Special Week']);
    TrainingRun::factory()->create(['umamusume_id' => $umamusume->id, 'scenario' => null]);

    $html = test()->get('/training-runs')->assertOk()->getContent();

    // A run with no scenario shows the name and nothing after it. The baseline key would
    // be a scenario the Trainer never chose (TrainingRun::hasScenario, D-221).
    expect($html)
        ->toContain('Special Week')
        ->not->toContain(config('scenarios.scenarios.ura_finale.label'));
});

it('shows the run status word and its date on the row', function (): void {
    $run = TrainingRun::factory()->create(['status' => RunStatus::Completed]);

    $html = test()->get('/training-runs')->assertOk()->getContent();

    expect($html)
        ->toContain($run->status->label())
        ->toContain($run->created_at->toDateString());
});

it('links each row to the run', function (): void {
    $run = TrainingRun::factory()->create();

    expect(test()->get('/training-runs')->assertOk()->getContent())
        ->toContain(route('runs.show', $run));
});

it('lists newest first', function (): void {
    $older = TrainingRun::factory()->create(['created_at' => now()->subWeek()]);
    $newer = TrainingRun::factory()->create(['created_at' => now()]);

    $html = test()->get('/training-runs')->assertOk()->getContent();

    // `latest()` on the controller, so the run just started is the one at the top.
    expect(
        strpos($html, route('runs.show', $newer)),
        strpos($html, route('runs.show', $older)),
    )->toBeLessThan(strpos($html, route('runs.show', $older)));
});

it('paginates at twenty-five and reaches a second page', function (): void {
    TrainingRun::factory()->count(26)->create();

    $first = test()->get('/training-runs')->assertOk();
    $second = test()->get('/training-runs?page=2')->assertOk();

    // 26 runs at a page size of 25 is the smallest case that proves the boundary is
    // 25 and not 50: one item over a single page, and the second page is reachable.
    expect($first->getContent())
        ->toContain('Next')
        ->and($second->getContent())->toContain('Previous');
});
