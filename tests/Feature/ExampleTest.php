<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

it('renders the 2.0 dashboard at the home page', function () {
    /** @var TestCase $this */
    $response = $this->get('/');

    // ADR-0020 §1: the front door is the SPA Dashboard, not a redirect into the runs index.
    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Dashboard'));
});
