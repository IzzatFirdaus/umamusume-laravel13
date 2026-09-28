<?php

declare(strict_types=1);

use Tests\TestCase;

it('redirects the home page to the runs index', function () {
    /** @var TestCase $this */
    $response = $this->get('/');

    $response->assertRedirect(route('runs.index'));
});
