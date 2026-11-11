<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;

/*
 * The Help screen's About-this-build card (SCR-SYS-012's fifth card). The app version travels on the
 * shared `app` props rather than a page prop, because `HandleInertiaRequests` already shares it with
 * every screen; a page prop would be a second copy of the same fact. The ruleset is asserted as the
 * named absence it is, so this test fails if a future source defines one and the page prints a stale
 * N/A.
 */
it('shares the app version and the ruleset absence with the Help screen', function (): void {
    $response = $this->get(route('help'));

    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->where('app.version', '3.0.0')
        ->where('app.ruleset', null));
});
