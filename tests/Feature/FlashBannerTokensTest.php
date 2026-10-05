<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * G-18 / G-19 for the one confirmation surface the application shell has.
 *
 * The flash banner is the only place a `session('status')` message renders, and it
 * shipped on the skeleton palette: `border-green-300 bg-green-50 text-green-800`.
 * That is the defect D-101 records for `bg-zinc-50` on `body` — a literal colour that
 * does not flip with the theme, so the dark theme got a light box. It also sat
 * outside the G-19 grep's palette list (`zinc|gray|neutral|stone|slate|amber|red|
 * blue|emerald`), which is why the shell gate passed while it was still there.
 *
 * Why the tint-and-border pair and not `bg-green` + `text-on-pick`: DESIGN.md §3.4
 * splits every hue into a 500 chrome step that is "fills, borders, rings, lattice,
 * and anything non-text" and an ink-bearing step for "any surface with text on it",
 * and `green-500 #7FCC09` appears in the failing table at 1.99:1 rather than in the
 * approved text-pair table. `--color-green` is additionally documented in app.css as
 * "action and affordability only, never 'good'", and a status message is a "good".
 * The pair used is the one §6.16's band table already approves for the Safe band,
 * `ink` on `green-tint`, and it is shipped at resources/js/layouts/AppLayout.vue:97.
 *
 * The pages are Inertia pages now: the server-side claim is that the flash reaches
 * every one of them in the shared prop the shell renders the banner from, and the
 * rendered box (its tokens, its absence of palette-numbered greens) is the browser
 * spec's.
 */

/**
 * The status-chip pairs the shell renders, as (fill, border, ink) triples.
 *
 * This list is the single source for the shell's measured pairs. The D-288/G-18
 * browser sweep is still a stub (DesignTokensTest.php:248, :257), so when that body
 * is written the sweep reads this list rather than re-deriving the pair from the
 * markup — a pair that is only asserted in a unit test is a pair no browser gate
 * has ever measured.
 *
 * @return list<array{0: string, 1: string, 2: string}>
 */
function approvedStatusChipPairs(): array
{
    return [
        ['bg-green-tint', 'border-green-line', 'text-ink'],
    ];
}

it('sends the created-run flash the shell renders the banner from', function (): void {
    $umamusume = Umamusume::factory()->create();

    test()->followingRedirects()
        ->post('/training-runs', [
            'umamusume_id' => $umamusume->id,
            'status' => 'Active',
        ])
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('flash.status', 'Run created.'));
});

it('carries the shared flash prop on every shell page that can flash', function (string $target): void {
    $run = TrainingRun::factory()->create();

    // A provider closure runs before the test body, so the run does not exist yet and
    // the route has to be resolved here. Both spellings of the run route are covered.
    $url = match ($target) {
        'run detail' => "/training-runs/{$run->id}",
        'run create' => '/training-runs/create',
        default => $target,
    };

    test()->withSession(['status' => 'Run deleted.'])->get($url)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('flash.status', 'Run deleted.'));
})->with([
    'catalog index' => '/umamusume',
    'runs index' => '/training-runs',
    'run create' => 'run create',
    'review index' => '/review',
    'run detail' => 'run detail',
]);

it('agrees with the approved pair list, so the sweep reads one source', function (string $fill, string $border, string $ink): void {
    // `AppLayout` is the only shell now (B1 retired the Blade layout that drew the
    // same pair on the two error pages it wrapped).
    $layout = file_get_contents(base_path('resources/js/layouts/AppLayout.vue'));

    expect($layout)
        ->toContain($fill)
        ->toContain($border)
        ->toContain($ink);
})->with(approvedStatusChipPairs());
