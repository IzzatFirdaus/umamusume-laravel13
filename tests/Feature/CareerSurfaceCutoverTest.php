<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use App\Models\Veteran;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * F1, the career surface cutover: Trainer Desk 2.0 is the canonical career frontend.
 *
 * What this file pins is the selection half of the cutover: every surface that offers a career to
 * open carries the Cockpit's URL, so no normal navigation lands on the 0.1.0 run-detail page. The
 * redirect half (GET /training-runs/{run} itself) is deliberately NOT pinned yet. The owner held it
 * (ruling 2026-10-08), and the reason is not a test count: four writes have no 2.0 owner yet, so the
 * record screen is still the only place a Trainer can reach them (run status, a recorded turn's
 * edit/delete, a free-race entry, and the CSV/JSON exports). Plan §9's F1 row carries that list and
 * the sweep the flip owes.
 *
 * The browser cleanups that used to be on that list are not any more: teardown now goes over HTTP
 * through `tests/utils/delete-run.ts`, so no spec depends on the record page rendering.
 */

function cutoverRun(array $state = []): TrainingRun
{
    return TrainingRun::factory()->create([
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->create()->id,
        ...$state,
    ]);
}

it('sends the Careers row links to the Cockpit, not to the run-detail page', function (): void {
    $run = cutoverRun(['scenario' => 'unity_cup']);

    $this->get(route('runs.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('runs.data.0.url', route('runs.cockpit', $run)));
});

it('keeps the Dashboard Resume Career on the Cockpit it already pointed at', function (): void {
    $run = cutoverRun();

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('activeCareer.resume_url', route('runs.cockpit', $run)));
});

it('opens a career screen from every Veteran row link', function (): void {
    // A Veteran row's link is the Dashboard's own door to the run behind it (DashboardController).
    // The invariant is that following it renders a career screen for that run, not which generation
    // owns the screen: `Runs/Show` today, `Career/Cockpit` once the held redirect lands. Written to
    // survive the flip, because the owner ruled that pinning `runs.show` here would only make the
    // sweep a self-loop.
    $run = cutoverRun(['status' => RunStatus::Completed]);
    Veteran::factory()->create(['training_run_id' => $run->id]);

    $runUrl = $this->get(route('home'))
        ->assertOk()
        ->viewData('page')['props']['recentVeterans']['rows'][0]['run_url'];

    $page = $this->followingRedirects()->get($runUrl)->assertOk()->viewData('page');

    expect(['Runs/Show', 'Career/Cockpit'])->toContain($page['component']);
    expect($page['props']['run']['id'])->toBe($run->id);
});
