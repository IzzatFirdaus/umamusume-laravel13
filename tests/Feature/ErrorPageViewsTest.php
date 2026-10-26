<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;

/*
 * SCREEN_SPEC.md §7-8: one custom error view existed, so a stale-session POST and any uncaught
 * failure both landed on a framework page that is off-theme (G-20 says the first paint is the
 * chosen theme; that promise only held on screens this app renders itself).
 *
 * The 500 case has a constraint the 404 and 419 do not: it is the page a Trainer sees when
 * something in the app is already broken, so it may not query the database or read the session to
 * draw itself. The theme composer reads the stored preference from `preferences` (D-104), which
 * is reachable for a 404 or a 419 and is not reachable for the page that says the connection
 * itself failed. That is what the query counter below is for: it is the difference between a page
 * that looks independent and one that is.
 *
 * B1 retired the shared Blade shell, so the three views draw their own document. `errors.404` and
 * `errors.419` still take `$theme` from the composer and still query; only the 500 is DB-free. The
 * canary at the end of this file proves that counter is counting something.
 *
 * A 419 is not reachable through the HTTP stack in tests: the framework's CSRF middleware
 * short-circuits when `runningUnitTests()`, so a tokenless POST is accepted rather than rejected.
 * The 419 view is therefore rendered directly, which is what the exception handler does with it
 * once the status exists.
 */

it('answers the data-loss question first and offers three exits on the 404', function (): void {
    $html = test()->get('/no-such-page')
        ->assertNotFound()
        ->getContent();

    expect($html)
        // error-404.html: the head names the fact, the lede answers whether anything was lost,
        // and the third region says what the page deliberately does not do.
        ->toContain('That page does not exist')
        ->toContain('Nothing was changed and nothing was lost')
        ->toContain('Where would you like to go?')
        ->toContain('What this page does not do')
        // Three exits, and the filled one is the Dashboard (error-404.html `.act`).
        ->toContain(route('home'))
        ->toContain(route('runs.index'))
        ->toContain(route('database.index'))
        ->toContain('bg-page')
        ->not->toMatch('/<style|font-family:\s*(?!var\()/');
});

it('renders a themed 419 that says what happened and what to do', function (): void {
    $html = view('errors.419')->render();

    expect($html)
        // This tool has no sign-in (PRD NFR-1), so the heading names the cause the body explains
        // instead of pointing at a screen that does not exist.
        ->toContain('Your session timed out')
        ->not->toContain('Sign-in')
        // error-419.html's Cause / Your data / What is lost table is the region that carries the
        // reassurance, so the case pins the region and its middle row rather than a sentence.
        ->toContain('Why this happens')
        ->toContain('Everything saved before the submission is intact')
        ->toContain('bg-page')
        ->toContain('min-h-11')
        // First paint is themed without a script deciding it afterwards: either the stored
        // attribute (via the layout composer, safe here because the database is reachable for a
        // 419) or the inline system-follow fallback.
        ->and(str_contains($html, 'data-theme=') || str_contains($html, 'prefers-color-scheme'))->toBeTrue();
});

it('renders the 500 page without querying the database', function (): void {
    DB::flushQueryLog();
    DB::enableQueryLog();

    $html = view('errors.500')->render();

    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    expect($queries)->toBe([], 'the 500 view asked the database something while the app was failing')
        ->and($html)->toContain('Something went wrong on our side')
        // error-500.html names what the page withholds, which is the sentence that keeps a stack
        // trace off a Trainer-facing surface.
        ->and($html)->toContain('What is shown and what is not')
        ->and($html)->toContain('A stack trace is developer information, not an interface');

    // The view is the reason the assertion above can pass: it is a standalone document that is not
    // on the composer's list, so nothing resolves its theme from the database for it.
    expect(file_get_contents(base_path('resources/views/errors/500.blade.php')))
        ->not->toContain('<x-layout');
});

it('proves the 500 assertion is about something, by failing it for a page the composer serves', function (): void {
    DB::flushQueryLog();
    DB::enableQueryLog();

    view('errors.419')->render();

    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    // Canary for the test above: the 419 takes its theme from AppServiceProvider's composer, which
    // reads `preferences`, so a zero-query result on the 500 is a property of that view rather
    // than a counter that never counts anything.
    expect($queries)->not->toBe([]);
});

it('drew the shell out of the error paths entirely, so no error view can reach the app shell', function (): void {
    // B1: the shell went away with the Inertia port. A future view that re-adds `<x-layout` would
    // also re-add a nav and a flash-banner region to a page that exists outside the product.
    expect(file_exists(base_path('resources/views/components/layout.blade.php')))->toBeFalse();

    foreach (['404', '419', '500'] as $status) {
        $source = (string) file_get_contents(base_path("resources/views/errors/{$status}.blade.php"));

        expect($source, "errors/{$status}")->not->toContain('<x-layout');
    }
});

it('keeps both new error pages free of a default value standing in for a fact', function (): void {
    foreach (['419', '500'] as $status) {
        $source = (string) file_get_contents(base_path("resources/views/errors/{$status}.blade.php"));

        // G-19: no skeleton palette utilities and no theme fork in shared surfaces, and R-02:
        // no dash glyph used as a stand-in for a missing value.
        expect($source)->not->toMatch('/\bdark:|bg-zinc-|text-zinc-|border-gray-/')
            ->and($source)->not->toMatch('/[\x{2013}\x{2014}]/u');
    }
});

it('gives each error page a way back to work', function (): void {
    // The exits are the wireframes' own: error-419.html returns to the screen that submitted the
    // form (which this view can only reach through the previous URL, not a named route) and offers
    // the Dashboard; error-500.html offers the Dashboard and the Careers list.
    expect(view('errors.419')->render())->toContain(route('home'))
        ->and(view('errors.500')->render())->toContain(route('runs.index'));
});

it('fills each error page primary with the app action colour, not the green chrome pair', function (): void {
    // Backlog slice 0.9. `bg-chrome` is the deep green the 2026-10-15 amendment keeps for the
    // capsule motif and the progress bar, and `app.css:445-448` states in terms that it is NOT the
    // action fill — so all three error documents painted their one primary green while every screen
    // inside the app filled it with `--color-primary-action`. `enamel` rode along as a dead class:
    // the utility was retired the same day and resolves to nothing.
    //
    // The equality is checked against the app's own action fill rather than a string constant, so
    // the case fails if either side drifts. `buttonStyles.ts` is the single owner of that class:
    // the HFW rewrite moved every page's primary onto the `variant="primary"` button primitive, so
    // reading a page file for the literal now proves only that the page delegates. `Runs/Index.vue`
    // is still read, for the half the module cannot show: a live screen actually asks for it.
    $owner = (string) file_get_contents(base_path('resources/js/components/Form/buttonStyles.ts'));
    $liveScreen = (string) file_get_contents(base_path('resources/js/pages/Runs/Index.vue'));

    expect($owner)->toContain('bg-primary-action')
        ->and($liveScreen)->toContain('variant="primary"');

    foreach (['404', '419', '500'] as $status) {
        $source = (string) file_get_contents(base_path("resources/views/errors/{$status}.blade.php"));

        expect($source, "errors/{$status}")
            ->toContain('bg-primary-action')
            ->toContain('text-on-primary-action')
            ->not->toContain('enamel')
            ->not->toContain('bg-chrome')
            ->not->toContain('text-on-chrome');
    }

    // Sourced is not rendered: the class has to survive the view.
    expect(view('errors.419')->render())->toContain('bg-primary-action');
});
