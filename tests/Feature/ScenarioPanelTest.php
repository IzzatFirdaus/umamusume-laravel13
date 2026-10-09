<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * SCREEN-014 / 015 / 016, the scenario panel shell (plan §9 E1). The payload and the G-33 property
 * are asserted here; the rendered shell, the fallback chain's three failure modes, the 44px sweep
 * and the axe scan live in `tests/browser/scenario-panel.spec.ts`.
 *
 * **G-33 is asserted as a property of the payload, not by grepping `.vue` files.** The section
 * carries a `label` (display only), a `panels` object keyed by flag name, and a `widgets` array of
 * string keys — and no field whose name identifies the scenario (`key`, `id`, `scenario_key`,
 * `config_key`). A grep is a weaker version of the same claim and it rots the first time someone
 * writes `scenario.label` in a comment. What a grep cannot give us either is the positive half:
 * every one of the four Global scenarios produces the *same* shape, so one component can draw all
 * four with no branch. The branch-free property of `ScenarioPanel.vue` itself is verified by
 * reading it, which the hand-off states.
 */

function scenarioPanelRun(string $scenario): TrainingRun
{
    return TrainingRun::factory()->create([
        'scenario' => $scenario,
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->state([
            'name' => 'Rice Shower',
            'name_ja' => 'ライスシャワー',
        ])->create()->id,
    ]);
}

/**
 * The scenario section's own key list. Pinned so identity fields cannot arrive unnoticed, and so a
 * panel renderer's data cannot be smuggled in without a test naming it.
 *
 * @return list<string>
 */
function scenarioSectionKeys(): array
{
    return [
        'label',
        'declared',
        'documented',
        'version',
        'version_title',
        'widgets',
        'widget_labels',
        'widget_values',
        'widgets_absence',
        'panels',
        // The team system's facts (E3, plan §9.1) — named here so the section's own contract change
        // is a test-visible event, which is the pin's whole purpose.
        'team',
        'objectives',
        'actions',
        'alerts',
        'recommendations',
        'recommendations_absence',
        'finale',
        // This run's own position against the finale. Distinct from `finale`, which is what the
        // config declares the scenario composes: a structure and a reading are two questions.
        'finale_state',
        'finale_absence',
        // The five Trackblazer-specific sections (plan §9 E4). Null for any scenario whose matrix
        // does not turn the matching flag on, so the shape stays uniform across all four scenarios.
        'grade',
        'shop',
        'epithet',
        'rival',
        'finale_official_title_absence',
        // The baseline strip's own content (plan §9 E6). `caps` is uniform for every scenario; the
        // two absence keys are null wherever the entry declares none.
        'caps',
        'panel_absence',
        'panel_absence_title',
    ];
}

it('carries the §49 shape with a label, flag-keyed panels and widget keys, and no scenario identity field', function (): void {
    $run = scenarioPanelRun('unity_cup');

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.label', 'Unity Cup')
            ->where('scenario.documented', true)
            // `app.ruleset` is null and no source defines a Global ruleset version, so the version
            // is a named absence rather than an invented string.
            ->where('scenario.version', null)
            ->where('scenario.version_title', fn (string $title): bool => str_contains($title, 'ruleset'))
            // Widgets are string keys; the labels and values are maps keyed by the same keys.
            ->where('scenario.widgets', ['team_rank', 'spirit_bursts'])
            ->where('scenario.widget_labels.team_rank', 'Team Rank')
            ->where('scenario.widget_values.team_rank', null)
            // Panels are keyed by flag name, each carrying its own label and its on/off state.
            ->where('scenario.panels.team_race.label', 'Team races')
            ->where('scenario.panels.team_race.on', true)
            ->where('scenario.panels.shop.on', false)
            // The pinned key list. `array_keys` is read strictly: a field named `key`, `id`,
            // `scenario_key` or `config_key` fails this assertion before any reviewer has to
            // notice it, which is the whole of G-33's payload half.
            ->where('scenario', function (Collection $section): bool {
                return array_keys($section->all()) === scenarioSectionKeys();
            })
        );
});

it('produces the same payload shape for all four Global scenarios', function (): void {
    /** @var array<string, mixed> $scenarios */
    $scenarios = config('scenarios.scenarios');

    // The four the matrix declares. A fifth entry added by config would widen this loop, which is
    // the point: the test asserts a property of the matrix, not of a list kept beside it.
    expect($scenarios)->toHaveCount(4);

    $shapes = [];

    foreach (array_keys($scenarios) as $key) {
        $run = scenarioPanelRun($key);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(function (Assert $page) use (&$shapes, $key): void {
                $page->where('scenario', function (Collection $section) use (&$shapes, $key): bool {
                    $all = $section->all();

                    // Same key set for every scenario, in the same order.
                    expect(array_keys($all))->toBe(scenarioSectionKeys());

                    // The panel set is the matrix's own flags for every scenario: a scenario
                    // declares which are on, never which exist. The count is read from the config
                    // rather than repeated here, so E2's seventh flag cannot rot this comment.
                    expect(array_keys($all['panels']))->toBe(array_keys(config('scenarios.panel_labels')));

                    // The shape a renderer sees, minus the two fields that legitimately differ.
                    $shapes[$key] = [
                        'panels' => array_keys($all['panels']),
                        'widgets_type' => gettype($all['widgets']),
                        'label_type' => gettype($all['label']),
                    ];

                    return true;
                });
            });
    }

    // One shape, four scenarios: `array_unique` collapses to a single entry.
    expect(array_unique(array_map('json_encode', $shapes)))->toHaveCount(1);
});

it('turns on exactly the panels the matrix declares, and renders no panel data where it turns one off', function (): void {
    // Ported from GuidedStepScenarioCompositionTest: every panel the scenario's matrix turns ON
    // appears, and every panel it turns OFF renders nothing. The shell draws its `onPanels` from
    // the flag, so the payload's own `on` state is the whole of the claim, and the Trackblazer
    // sections being absent where their flag is off is the payload half of "renders nothing".
    /** @var array<string, mixed> $scenarios */
    $scenarios = config('scenarios.scenarios');
    /** @var array<string, string> $flags */
    $flags = config('scenarios.panel_labels');

    // The flags whose composition is a section of the payload in its own right. Where the matrix
    // turns the flag off the section is null rather than an empty stand-in, so no renderer can
    // print another scenario's rows.
    $sectionForFlag = [
        'grade_objectives' => 'grade',
        'shop' => 'shop',
        'epithet_routes' => 'epithet',
    ];

    foreach (array_keys($scenarios) as $key) {
        $run = scenarioPanelRun($key);
        /** @var array<string, bool> $declared */
        $declared = (array) config("scenarios.scenarios.{$key}.panels");

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(function (Assert $page) use ($declared, $flags, $sectionForFlag): void {
                // Every flag the matrix owns is emitted, and each carries this scenario's own
                // state: a scenario declares which panels are on, never which panels exist.
                $page->where('scenario.panels', function (Collection $panels) use ($declared, $flags): bool {
                    if (array_keys($panels->all()) !== array_keys($flags)) {
                        return false;
                    }

                    foreach (array_keys($flags) as $flag) {
                        if ($panels[$flag]['on'] !== (($declared[$flag] ?? false) === true)) {
                            return false;
                        }
                    }

                    return true;
                });

                // A flag the scenario does not turn on composes nothing: its section is absent
                // rather than empty, so no renderer can print another scenario's rows.
                foreach ($sectionForFlag as $flag => $section) {
                    $on = ($declared[$flag] ?? false) === true;

                    $page->where("scenario.{$section}", $on ? fn (mixed $value): bool => $value !== null : null);
                }
            });
    }
});

it('reads the documentation marker the owner ruled on, and defaults a scenario that declares none to documented', function (): void {
    // The badge condition is `partially_documented`, per the owner's ruling of 2026-10-07 (plan §4.1
    // item 6, `SCREEN_SPEC.md` SCR-CAR-019). `documented` stays a provenance marker on the entry, it
    // flipped to true on 2026-10-05, and nothing proves it means what a badge would need it to mean,
    // so the screen does not read it. This test pins which key the screen is actually on.
    expect(config('scenarios.scenarios.our_grand_concert.partially_documented'))->toBeTrue();
    expect(config('scenarios.scenarios.ura_finale.partially_documented'))->toBeNull();

    $expected = [
        // Our Grand Concert is the one entry that declares the marker, so it is the one scenario whose
        // badge prints the partial state. The others say nothing about it and are documented.
        'our_grand_concert' => false,
        'ura_finale' => true,
    ];

    foreach ($expected as $key => $documented) {
        $run = scenarioPanelRun($key);

        $this->get(route('runs.cockpit', $run))
            ->assertInertia(fn (Assert $page) => $page->where('scenario.documented', $documented));
    }
});

it('renders a fifth scenario from config alone, drawing only on widget and flag keys that already exist', function (): void {
    // The acceptance case the plan names: one config entry, zero component edits. The entry is
    // built from the four keys the component already knows, so nothing here is a new vocabulary.
    config()->set('scenarios.scenarios.fifth_scenario', [
        'label' => 'A Fifth Scenario',
        'live_on_global' => '2027-01-01',
        'cap_bonus' => ['Speed' => 0, 'Stamina' => 0, 'Power' => 0, 'Guts' => 0, 'Wit' => 0],
        'widgets' => ['turn', 'energy', 'fans', 'grade_points'],
        'steps' => ['training', 'outcome', 'skill'],
        'panels' => [
            'race_calendar' => false,
            'team_race' => false,
            'grade_objectives' => true,
            'shop' => false,
            'epithet_routes' => false,
            'team_rank_ladder' => false,
        ],
        'scenario_links' => [],
        'facility_level_source' => 'repetition',
    ]);

    $run = scenarioPanelRun('fifth_scenario');

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.label', 'A Fifth Scenario')
            // The scenario's own widgets only; the baseline three are the header's figures and are
            // subtracted by the same rule the Dashboard's resource label uses.
            ->where('scenario.widgets', ['grade_points'])
            ->where('scenario.widget_labels.grade_points', 'Grade Points')
            ->where('scenario.panels.grade_objectives.on', true)
            ->where('scenario.panels.shop.on', false)
        );
});

it('composes a fifth scenario from config alone, turning on only the panels its entry declares', function (): void {
    // Ported from GuidedStepScenarioVariationTest: the same payload shape holds for a scenario the
    // build has never seen, and its composition is the entry's own. One config entry, zero
    // component edits, and no panel on that the entry did not turn on.
    config()->set('scenarios.scenarios.fifth_scenario', [
        'label' => 'A Fifth Scenario',
        'live_on_global' => '2027-01-01',
        'cap_bonus' => ['Speed' => 0, 'Stamina' => 0, 'Power' => 0, 'Guts' => 0, 'Wit' => 0],
        'widgets' => ['turn', 'energy', 'fans'],
        'steps' => ['training', 'outcome', 'skill'],
        'panels' => [
            'race_calendar' => true,
            'team_race' => false,
            'grade_objectives' => false,
            'shop' => false,
            'epithet_routes' => false,
            'team_rank_ladder' => false,
            'career_goals' => false,
        ],
        'scenario_links' => [],
        'facility_level_source' => 'repetition',
    ]);

    $run = scenarioPanelRun('fifth_scenario');

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.label', 'A Fifth Scenario')
            ->where('scenario.declared', true)
            // The same §49 key set the four Global scenarios carry: a fifth entry adds no field,
            // so the shell draws it without an edit (gate G-33).
            ->where('scenario', fn (Collection $section): bool => array_keys($section->all()) === scenarioSectionKeys())
            // The composition is the entry's own, not the baseline's: race_calendar on, the rest off.
            ->where('scenario.panels.race_calendar.on', true)
            ->where('scenario.panels.team_race.on', false)
            ->where('scenario.panels.shop.on', false)
            ->where('scenario.panels.career_goals.on', false)
            // A panel the entry leaves off renders nothing, exactly as for a Global scenario.
            ->where('scenario.shop', null)
            ->where('scenario.grade', null)
            ->where('scenario.epithet', null)
        );
});

it('carries a widget key the matrix does not label, so the shell falls back rather than guessing a name', function (): void {
    // The WidgetFallback path. It is config-reachable only: every widget key the four Global
    // scenarios declare is labelled, so this state cannot be reached by editing a scenario today.
    // The key is added to a scenario that is not the baseline, because the strip subtracts the
    // baseline's own widgets and URA Finale *is* the baseline, so a widget added there cancels out.
    config()->set('scenarios.scenarios.unity_cup.widgets', [
        'turn', 'energy', 'fans', 'team_rank', 'spirit_bursts', 'unlabelled_widget',
    ]);

    $run = scenarioPanelRun('unity_cup');

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // The unlabelled key is carried so the shell can draw it; the two labelled ones keep
            // their maps. A key with no label has no value entry either, which is what makes the
            // shell print the key rather than an invented name beside a blank.
            ->where('scenario.widgets', ['team_rank', 'spirit_bursts', 'unlabelled_widget'])
            ->where('scenario.widget_labels', fn (Collection $labels): bool => array_keys($labels->all()) === ['team_rank', 'spirit_bursts'])
            ->where('scenario.widget_values', fn (Collection $values): bool => array_keys($values->all()) === ['team_rank', 'spirit_bursts'])
        );
});
