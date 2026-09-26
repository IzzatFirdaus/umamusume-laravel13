<?php

use Tests\TestCase;

it('returns a successful response for the home page', function () {
    /** @var TestCase $this */
    $response = $this->get('/');

    $response->assertStatus(200);
});
