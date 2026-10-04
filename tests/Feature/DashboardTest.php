<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;

// ADR-0020 §1: the 2.0 SPA shell renders through Inertia. Counts are read-only
// catalogue facts; with RefreshDatabase and no seed they are zero but present, so the
// test asserts the shape the Dashboard page consumes, not the numbers.
it('renders the 2.0 dashboard as an Inertia page with catalogue counts', function (): void {
    $this->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('counts.trainees')
            ->has('counts.skills')
            ->has('counts.supportCards'));
});
