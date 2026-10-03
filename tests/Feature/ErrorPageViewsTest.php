<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;

/*
 * SCREEN_SPEC.md §7-8: one custom error view existed, so a stale-session POST and any uncaught
 * failure both landed on a framework page that is off-theme (G-20 says the first paint is the
 * chosen theme; that promise only held on screens this app renders itself).
 *
 * The 500 case has a constraint the 404 does not: it is the page a Trainer sees when something in
 * the app is already broken, so it may not query the database or read the session to draw itself.
 * `components/layout` cannot be used there, because its composer reads the stored theme from
 * `preferences` (D-104). That is what the query counter below is for: it is the difference between
 * a page that looks independent and one that is.
 *
 * A 419 is not reachable through the HTTP stack in tests: the framework's CSRF middleware
 * short-circuits when `runningUnitTests()`, so a tokenless POST is accepted rather than rejected.
 * The 419 view is therefore rendered directly, which is what the exception handler does with it
 * once the status exists.
 */

it('keeps a missing address inside the product shell', function (): void {
    $html = test()->get('/no-such-page')
        ->assertNotFound()
        ->getContent();

    expect($html)
        ->toContain('Page not found')
        ->toContain('Nothing to show here')
        ->toContain('bg-page')
        ->not->toMatch('/<style|font-family:\s*(?!var\()/');
});

it('renders a themed 419 that says what happened and what to do', function (): void {
    $html = view('errors.419')->render();

    expect($html)
        ->toContain('Sign-in session expired')
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
        ->and($html)->toContain('Something broke on this machine');

    // The view is the reason the assertion above can pass: it is a standalone document rather than
    // `x-layout`, whose theme composer reads `preferences`.
    expect(file_get_contents(base_path('resources/views/errors/500.blade.php')))
        ->not->toContain('<x-layout');
});

it('proves the 500 assertion is about something, by failing it for the layout', function (): void {
    DB::flushQueryLog();
    DB::enableQueryLog();

    view('errors.419')->render();

    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    // Canary for the test above: the shared shell does query, so a zero-query result on the 500 is
    // a property of that view rather than a counter that never counts anything.
    expect($queries)->not->toBe([]);
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
    expect(view('errors.419')->render())->toContain(route('runs.index'))
        ->and(view('errors.500')->render())->toContain(route('runs.index'));
});
