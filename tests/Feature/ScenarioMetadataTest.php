<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;

/**
 * Scenario card metadata.
 *
 * Every scenario the matrix composes carries card metadata a source describes. The cells the
 * 2026-10-15 artifact recorded as searched-and-not-found carried that recorded search verbatim;
 * the 2026-10-16 publisher pass recovered five of the six, and each recovered cell now rides the
 * payload as a value with its source beside it, with its search reason dropped.
 */
it('records the card metadata the matrix holds, with a source beside every recovered cell', function (): void {
    $this->get(route('career.scenario'))
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $page->component('Career/ScenarioSelect');

            $byKey = collect($page->toArray()['props']['scenarios'])->keyBy('key');

            $unity = $byKey['unity_cup']['metadata'];

            expect($unity['summary'])->toBeString()->not->toBeEmpty()
                ->and($unity['planning_focus'])->toBeString()->not->toBeEmpty()
                ->and($unity['key_mechanics'])->not->toBeEmpty()
                ->and($unity['planning_focus_reason'])->toBeNull()
                // A row that prints a published line cannot also claim nobody named one: the reason
                // key is gone wherever the value arrived, and the value travels with its source.
                ->and($unity['recommended_use'])->toBeString()->not->toBeEmpty()
                ->and($unity['recommended_use_source'])->toBeString()->not->toBeEmpty()
                ->and($unity['recommended_use_reason'])->toBeNull()
                ->and($byKey['ura_finale']['metadata']['recommended_use'])->toBeString()->not->toBeEmpty()
                // The one cell no publisher names keeps both halves of the absence.
                ->and($byKey['ura_finale']['metadata']['planning_focus'])->toBeNull()
                ->and($byKey['ura_finale']['metadata']['planning_focus_reason'])->toBeString()->not->toBeEmpty()
                ->and($byKey['trackblazer']['metadata']['recommended_use'])->toBeString()->not->toBeEmpty()
                ->and($byKey['trackblazer']['metadata']['planning_focus'])->toBeString()->not->toBeEmpty()
                ->and($byKey['trackblazer']['metadata']['recommended_use_reason'])->toBeNull()
                ->and($byKey['trackblazer']['metadata']['planning_focus_reason'])->toBeNull()
                ->and($byKey['our_grand_concert']['metadata']['recommended_use'])->toBeString()->not->toBeEmpty()
                ->and($byKey['our_grand_concert']['metadata']['planning_focus'])->toBeString()->not->toBeEmpty()
                ->and($byKey['our_grand_concert']['metadata']['planning_focus_source'])->toBeString()->not->toBeEmpty()
                ->and($byKey['our_grand_concert']['metadata']['recommended_use_reason'])->toBeNull()
                ->and($byKey['our_grand_concert']['metadata']['planning_focus_reason'])->toBeNull();
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

it('heads the ruleset version the same way on the wizard card and the Cockpit strip', function (): void {
    // The scenario card splits the tool-wide *version* from the scenario's rule *family* (R2-05); the
    // Cockpit's scenario strip carries only the version, so its heading has to say so in the same words
    // rather than the bare `Ruleset` the card had to abandon. A Trainer moving between the two should not
    // meet the same label meaning a different thing. The card's own heading is pinned by the case below,
    // which reads its whole `<dt>` set.
    $strip = (string) file_get_contents(resource_path('js/components/scenario/ScenarioPanel.vue'));

    expect($strip)->toMatch('/^\s*Ruleset version:$/m')
        ->and($strip)->not->toMatch('/^\s*Ruleset:$/m');
});

it('prints each fact on a scenario card under one label, and never one label twice', function (): void {
    $source = scenarioCardTemplate();

    // Match both SubCell labels and raw <dt> in file order.
    // SubCell label="..." can appear before />; raw <dt>content</dt>.
    // Use a combined regex that finds label="..." on SubCell lines and <dt> on dt lines,
    // preserving file order by scanning line by line.
    $lines = explode("\n", $source);
    $labels = [];
    foreach ($lines as $line) {
        // SubCell label="..."
        if (strpos($line, '<SubCell') !== false && preg_match('/label="([^"]+)"/', $line, $m)) {
            $labels[] = trim($m[1]);
        }
        // raw <dt>...</dt>
        if (preg_match('/<dt[^>]*>([^<]+)<\/dt>/', $line, $m)) {
            $labels[] = trim($m[1]);
        }
    }

    // The card's own fact grid then its recorded metadata, in the order the template prints them.
    // One entry per fact: a duplicated heading fails this, and so does a pattern that matched nothing.
    // `Scenario ruleset date` sits between the availability row and the tool-wide version row, which
    // is the 2026-10-15 wiring's third ruleset-worded fact on its own row.
    expect($labels)->toBe([
        'Optimizes',
        'Systems',
        'Tracked resources',
        'Turn loop',
        'Live on Global',
        'Scenario ruleset date',
        'Ruleset version',
        'Description',
        'Recommended use',
        'Rule family',
        'Planning focus',
    ]);

    // Each of the three keeps its own source: the version row carries the reason for its `N/A` on
    // the element, the scenario date row reads the payload the entry's `ruleset_version` builds,
    // and the family row reads the entry's `facility_level_source` rather than a word typed in here.
    expect($source)
        ->toContain(':title="props.ruleset.title"')
        ->toMatch('/Rule family<\/dt>.*?card\.rule_family/s')
        ->toMatch('/Scenario ruleset date.*?card\.ruleset_date/s');
});
