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

/**
 * The scenario card's template, with its comments out.
 *
 * R2-05 found the Unity Cup card heading two rows `Ruleset`: the global ruleset *version*, which is
 * `N/A` because no source defines one (`design-2.0` §48), and the scenario's own rule family, `Team`
 * from `config/scenarios.php`. Two different facts under one label read as a card contradicting
 * itself, and the props cannot show it — the collision is in the words the page prints, so the page
 * source is where it is visible whole (the same reading `RunRaceStripTest` takes of its component).
 *
 * Comments are stripped because they document a rule by naming the shape it rejects, which is exactly
 * what a label sweep would then match on (`DesignTokensTest` does the same).
 */
function scenarioCardTemplate(): string
{
    $source = (string) file_get_contents(resource_path('js/pages/Career/ScenarioSelect.vue'));

    return (string) preg_replace(['/\{\{--.*?--\}\}/s', '/<!--.*?-->/s'], '', $source);
}

it('prints each fact on a scenario card under one label, and never one label twice', function (): void {
    $source = scenarioCardTemplate();

    preg_match_all('/<dt[^>]*>([^<]+)<\/dt>/', $source, $matches);

    // The card's own fact grid then its recorded metadata, in the order the template prints them. One
    // entry per fact: a duplicated heading fails this, and so does a pattern that matched nothing.
    expect(array_map(trim(...), $matches[1]))->toBe([
        'Optimizes',
        'Systems',
        'Tracked resources',
        'Turn loop',
        'Live on Global',
        'Ruleset version',
        'Description',
        'Recommended use',
        'Rule family',
        'Planning focus',
    ]);

    // Each of the two keeps its own source: the version row carries the reason for its `N/A` on the
    // element, and the family row still reads the matrix value rather than a word typed in here.
    expect($source)
        ->toContain(':title="props.ruleset.title"')
        ->toMatch('/Rule family<\/dt>.*?card\.metadata\.ruleset/s');
});
