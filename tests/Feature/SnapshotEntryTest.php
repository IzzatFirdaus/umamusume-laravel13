<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;

it('renders the snapshot entry screen', function (): void {
    $this->get(route('career.snapshot'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Career/Snapshot/Entry'));
});

it('resolves both halves of the dashboard\'s second entry point', function (): void {
    // The link itself is Vue template copy, so it does not appear in the HTML a Pest test sees:
    // Inertia answers a page request with the shell and the data-page payload, and the Dashboard
    // renders client-side (the rendered-copy assertion is browser work, per the migration rule the
    // career specs record). What Pest can pin is that both ends of the link resolve: the dashboard
    // as its component, and the address the card points at as the entry screen.
    $this->get(route('home'))->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Dashboard'));

    $this->get('/career/snapshot')->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Career/Snapshot/Entry'));
});

it('renders the setup form the entry screen leads to', function (): void {
    $this->get(route('career.snapshot.setup'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Snapshot/Setup')
            ->where('action', route('career.snapshot.store'))
            ->has('trainees')
            ->has('scenarios', 4)
            ->has('years', 3)
            ->has('phases', 2));
});
