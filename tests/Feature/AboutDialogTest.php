<?php

declare(strict_types=1);

it('renders the about page with version and build SHA', function (): void {
    $this->get(route('about.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('About')
            ->has('version')
            ->has('buildSha')
        );
});
