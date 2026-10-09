<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;

/**
 * Scenario card metadata.
 *
 * Unity Cup is the one scenario a source describes in the card's own terms, so its metadata is
 * recorded; the other three carry null, which the page names as "Not recorded" rather than filling
 * with a sentence nobody sourced.
 */
it('records Unity Cup metadata and leaves the other scenarios absent', function (): void {
    $this->get(route('career.scenario'))
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $page->component('Career/ScenarioSelect');

            $byKey = collect($page->toArray()['props']['scenarios'])->keyBy('key');

            $unity = $byKey['unity_cup']['metadata'];

            expect($unity['ruleset'])->toBe('Team')
                ->and($unity['summary'])->toBeString()->not->toBeEmpty()
                ->and($unity['planning_focus'])->toBeString()->not->toBeEmpty()
                ->and($unity['key_mechanics'])->not->toBeEmpty()
                ->and($byKey['ura_finale']['metadata'])->toBeNull()
                ->and($byKey['trackblazer']['metadata'])->toBeNull()
                ->and($byKey['our_grand_concert']['metadata'])->toBeNull();
        });
});
