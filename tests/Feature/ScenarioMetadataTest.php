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

    // The HFW parity pass cut the app's eight-row metadata grid: the wireframe's card face is name,
    // baseline pill, release tag, summary, composition tags and the published caps, and the old
    // Optimizes/Systems/… grid was the superset the audit's H2 found (d2bf1cce). The guard's job is
    // unchanged - one fact under one label, never a duplicated heading - so it now pins the shipped
    // face facts as one binding each: a second print of any of them fails the count.
    // A face fact is counted by its render gate, not its raw binding count: a tag that names a fact
    // once in its v-if and once in its interpolation is one fact under one label, while a second tag
    // printing the same fact would add a second v-if and fail the count.
    expect(substr_count($source, 'v-if="card.is_baseline"'))->toBe(1)
        ->and(substr_count($source, 'v-if="card.live_on_global"'))->toBe(1)
        ->and(substr_count($source, 'v-if="card.caps_label"'))->toBe(1)
        ->and(substr_count($source, 'card.metadata?.summary'))->toBe(1)
        ->and(substr_count($source, 'card.performance_input'))->toBe(1)
        ->and(substr_count($source, 'card.card_tags'))->toBe(1);

    // The two ruleset-worded facts can no longer collide on this card because neither is printed
    // here: the version and the rule family belong to the Cockpit strip and the payload. A card that
    // starts printing one must join RulesetVersionVocabularyTest's surface list first.
    expect($source)->not->toContain('Ruleset version')
        ->and($source)->not->toContain('Rule family')
        ->and($source)->not->toContain('<SubCell');

    // The payload still carries all three sources the card stopped printing, so the cut is a
    // presentation change, not a data loss: the tool-wide version with its reason, the scenario's own
    // ruleset date, and the rule family read from the entry's own source.
    $controller = (string) file_get_contents(app_path('Http/Controllers/Career/ScenarioSelectController.php'));

    expect($controller)->toContain("'ruleset'")
        ->and($controller)->toContain("'ruleset_date'")
        ->and($controller)->toContain("'rule_family'");
});
