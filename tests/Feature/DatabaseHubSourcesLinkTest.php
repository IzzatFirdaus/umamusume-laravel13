<?php

use Inertia\Testing\AssertableInertia as Assert;

/*
 * The Database hub links every area it ships (`SCREEN-023`, and the hub's own docblock: "the eight
 * areas the Database screen lists"). `/database/sources` was the one area with no door: it is
 * registered in `routes/web.php` and served by `DatabaseController::sources()`, so it is a live
 * screen a document request reaches, and nothing in the app points at it. A screen that exists and
 * cannot be navigated to is the "reachable only by URL" state the UX audit recorded as `F-14`.
 *
 * The assertion is on the payload the page renders, not on the controller's source: a door is a
 * link the page draws, and the hub draws exactly `props.areas`.
 */
it('gives the data sources screen a door from the Database hub', function (): void {
    $response = $this->get(route('database.index'));

    $response->assertOk()->assertInertia(function (Assert $page): void {
        $urls = collect($page->toArray()['props']['areas'])->pluck('url');

        // The floor: a hub that listed nothing would otherwise pass this.
        expect($urls)->toHaveCount(9);

        expect($urls->contains(fn (string $url): bool => str_ends_with($url, '/database/sources')))
            ->toBeTrue('/database/sources has no link on the Database hub, so the screen is URL-only.');
    });
});
