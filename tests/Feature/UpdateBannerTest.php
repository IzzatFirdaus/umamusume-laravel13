<?php

declare(strict_types=1);

it('returns inert when feed URL is empty', function (): void {
    config(['app.update_feed_url' => null]);

    $this->get(route('updates.check'))
        ->assertOk()
        ->assertJson(['available' => false, 'version' => null, 'url' => null]);
});
