<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Enums\SpiritBurstState;
use App\Enums\TurnEventType;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\TrainingRun;
use App\Models\TurnEvent;
use App\Models\TurnEvents\SpiritBurstPayload;
use App\Models\TurnEvents\TeamRankPayload;
use App\Models\Umamusume;
use App\Services\ScenarioCaps;
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

function scenarioPanelRun(?string $scenario): TrainingRun
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

function scenarioPanelSlot(string $scenario, string $kind, string $title, int $order = 0): ScenarioSlot
{
    // month and half are unique per (scenario, kind), so the order walks the calendar
    // instead of stacking every test slot on the same date.
    return ScenarioSlot::factory()->create([
        'scenario_key' => $scenario,
        'kind' => $kind,
        'title' => $title,
        'tier' => 'G1',
        'month' => ($order % 12) + 1,
        'half' => $order % 2 === 0 ? 'Early' : 'Late',
        'sort_order' => $order,
    ]);
}

/**
 * One scenario event on one turn, the shape the team payloads ride (ADR-0003).
 *
 * @param  array<string, mixed>  $deltas
 */
function scenarioPanelRecorded(TrainingRun $run, int $turn, array $deltas): TurnEvent
{
    return TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => $turn,
        'event_type' => TurnEventType::Scenario,
        'source_name' => 'Unity Training',
        'deltas' => $deltas,
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

/*
 * Ported from ScenarioPanelSchemaTest / ScenarioPanelUiTest / ScenarioStatCapsTest as the legacy
 * `runs.show` surface retired. Every assertion below now reads the Cockpit's `scenario` section,
 * which is where `ScenarioPanel` renders; a behavior the Cockpit payload does not carry is not
 * forced into it (see the hand-off: the fatigue chip and the consecutive-race absence have no
 * Cockpit/ScenarioPanel equivalent, because neither `scenario.team` nor any other Cockpit section
 * carries a fatigue reading).
 */

it('renders the shop countdown as entered and never as a computed number', function (): void {
    $run = scenarioPanelRun('trackblazer');
    $run->update(['shop_resets_in' => 4]);

    // The countdown reaches the shop panel as `scenario.shop.rotation_resets_in`, the entered figure
    // verbatim. The rotation is not modelled, so the panel prints the stored number and never one
    // derived from the turn (D-232, Planner Rule 5).
    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.shop.rotation_resets_in', 4));

    $unset = scenarioPanelRun('trackblazer');

    // Null is a disclosure, not a zero: the panel prints "countdown not recorded" and no
    // "resets in 0 turns" can be read off it.
    $this->get(route('runs.cockpit', $unset))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->whereNull('scenario.shop.rotation_resets_in'));
});

it('carries the shop overwrite warning', function (): void {
    $run = scenarioPanelRun('trackblazer');

    // The warning copy ("Buying at a higher rank overwrites the lower one...") is TrackblazerPanel's
    // own; the server-side fact that gates it is the composed shop. The matrix turns the shop flag
    // on and the section is non-null, so the warning has a panel to sit in.
    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.panels.shop.on', true)
            ->where('scenario.shop', fn (mixed $shop): bool => $shop !== null));
});

it('derives the epithet checklist and marks what it cannot see as unverifiable', function (): void {
    $run = scenarioPanelRun('trackblazer');
    $slot = scenarioPanelSlot('trackblazer', 'goal_race', 'Satsuki Sho', 1);

    RaceEntry::create([
        'training_run_id' => $run->id,
        'scenario_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
    ]);

    $rows = collect($run->epithetProgress())->keyBy('epithet');

    // Stunning is a named-race route with one of three races seen; Dirty Work is an aggregate
    // condition this surface has no field for, so it is `unverifiable`, never `open`; Lady is
    // a named-race route with none seen.
    expect($rows['Stunning']['state'])->toBe('open')
        ->and($rows['Stunning']['missing'])->toBe(['Japanese Derby', 'Kikuka Sho'])
        ->and($rows['Dirty Work']['state'])->toBe('unverifiable')
        ->and($rows['Lady']['state'])->toBe('open');

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.panels.epithet_routes.on', true)
            ->where('scenario.epithet.rows', function (Collection $rows): bool {
                $byEpithet = $rows->keyBy('epithet');

                return $byEpithet['Stunning']['state'] === 'open'
                    && $byEpithet['Stunning']['missing'] === ['Japanese Derby', 'Kikuka Sho']
                    && $byEpithet['Dirty Work']['state'] === 'unverifiable'
                    && $byEpithet['Lady']['state'] === 'open';
            })
            // The run has entered exactly one completed race, and the checklist says which names
            // it derived from rather than implying a full picture.
            ->where('scenario.epithet.seen_titles', fn (Collection $titles): bool => $titles->count() === 1));
});

it('discloses which grade point track is being shown', function (): void {
    $run = scenarioPanelRun('trackblazer');

    // Trackblazer composes the grade-objectives panel, and the standard track's four rows are what
    // it renders. The dirt-leaning and limited-turf-range tracks are not drawn (KI-15).
    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.panels.grade_objectives.on', true)
            ->where('scenario.grade.objectives', fn (Collection $objectives): bool => $objectives->isNotEmpty()));
});

it('keeps circles off the race form where the scenario has no team race', function (): void {
    // The circle field is the race form's; the Cockpit carries the composition fact it gates on.
    // URA Finale composes no team race, Unity Cup does, so only the latter offers a circle input.
    $this->get(route('runs.cockpit', scenarioPanelRun('ura_finale')))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.panels.team_race.on', false));

    $this->get(route('runs.cockpit', scenarioPanelRun('unity_cup')))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.panels.team_race.on', true));
});

it('records a team rank as a payload and derives its facility level', function (): void {
    $run = scenarioPanelRun('unity_cup');
    scenarioPanelRecorded($run, 4, TeamRankPayload::make('A')->toArray());

    // The letter is entered and the level is derived through the config ladder, never stored.
    expect($run->fresh()->latestTeamRank()?->rank)->toBe('A')
        ->and($run->fresh()->facilityLevel('A'))->toBe(4);
});

it('renders the rank gauge as unrecorded before anything is entered', function (): void {
    $run = scenarioPanelRun('unity_cup');

    // Unity Cup composes the ladder, and a run that has reported no rank claims no level: both
    // readings are null rather than a zero or a baseline letter.
    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.panels.team_rank_ladder.on', true)
            ->whereNull('scenario.team.rank')
            ->whereNull('scenario.team.facility_level'));
});

it('names the rank the facility level was derived from', function (): void {
    $run = scenarioPanelRun('unity_cup');
    scenarioPanelRecorded($run, 6, TeamRankPayload::make('S')->toArray());

    // Rank S maps to level 5 in the matrix's ladder, and the panel carries the cause beside the
    // level so the facility chip is never read as a stored figure (D-222).
    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.panels.team_rank_ladder.on', true)
            ->where('scenario.team.rank', 'S')
            ->where('scenario.team.facility_level', 5));
});

it('keeps S+ off the ladder without inventing a level for it', function (): void {
    $run = scenarioPanelRun('unity_cup');
    scenarioPanelRecorded($run, 6, TeamRankPayload::make('S+')->toArray());

    // S+ sits above S and grants a second hint rather than a higher facility, so it has no rung and
    // the panel must not hand it one.
    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.panels.team_rank_ladder.on', true)
            ->where('scenario.team.rank', 'S+')
            ->whereNull('scenario.team.facility_level'));

    expect($run->fresh()->facilityLevel('S+'))->toBeNull();
});

it('shows every burst state with a word, so no teammate is read by colour alone', function (): void {
    $run = scenarioPanelRun('unity_cup');

    foreach (SpiritBurstState::cases() as $index => $state) {
        scenarioPanelRecorded($run, $index + 1, SpiritBurstPayload::make('mate_'.$index, $state)->toArray());
    }

    // Each roster row carries the backing `state` value and the human `stateLabel`. The three
    // states whose value differs from their label are the leak detector: a regression that printed
    // `->value` as the label would fail because the prop would carry the backing string as stateLabel.
    $ranks = SpiritBurstState::cases();

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(function (Assert $page) use ($ranks): void {
            $page->where('scenario.panels.team_rank_ladder.on', true)
                ->where('scenario.team.roster', fn (Collection $roster): bool => $roster->count() === count($ranks));

            foreach ($ranks as $state) {
                $page->where('scenario.team.roster', function (Collection $roster) use ($state): bool {
                    foreach ($roster as $row) {
                        if ($row['stateLabel'] === $state->label() && $row['state'] === $state->value) {
                            return true;
                        }
                    }

                    return false;
                });
            }
        });
});

it('states the circle margin as a margin, not as a win condition', function (): void {
    $run = scenarioPanelRun('unity_cup');
    $slot = scenarioPanelSlot('unity_cup', 'team_race', 'Team Race A', 3);

    RaceEntry::create([
        'training_run_id' => $run->id,
        'scenario_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
        'circles' => 2,
    ]);

    // `race_guidance` is the config margin (3), not a win condition, and the entry's own circle
    // estimate and placement ride beside it.
    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.panels.team_race.on', true)
            ->where('scenario.team.race_guidance', 3)
            ->where('scenario.team.race_entries', fn (Collection $entries): bool => $entries->count() === 1
                && $entries->first()['circles'] === 2
                && $entries->first()['placement'] === 1));
});

it('draws the band at the ceiling its own form enforces when the run names no scenario', function (): void {
    $run = scenarioPanelRun(null);
    $run->turnEntries()->create([
        'turn' => 1, 'speed' => 600, 'stamina' => 10, 'power' => 10, 'guts' => 10, 'wit' => 10,
    ]);

    // KI-47. A run this tool holds to 1,200 must not display a bar ending at the baseline entry's
    // 1,400. Every state panel denominator is the base cap, and the entered Speed is what the bar
    // fills against it.
    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.declared', false)
            ->where('state.stats', fn (Collection $stats): bool => $stats->every(
                fn (array $stat): bool => $stat['cap'] === 1200
            ))
            ->where('state.stats', fn (Collection $stats): bool => $stats->firstWhere('key', 'Speed')['current'] === 600));
});

it('puts the base cap in the strip when the run names no scenario', function (): void {
    $run = scenarioPanelRun(null);

    // The published-cap strip's own rows carry the base cap with no bonus for a run that never
    // picked a scenario; lending it the baseline entry's +200 would state a bonus nobody chose.
    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.caps.base', 1200)
            ->where('scenario.caps.rows', fn (Collection $rows): bool => $rows->every(
                fn (array $row): bool => $row['cap'] === 1200
            )));
});

it('holds both forms to the same ceilings the validator uses', function (): void {
    $run = scenarioPanelRun('unity_cup');

    // The state panel's per-stat cap and the published-cap strip both read `ScenarioCaps::forRun()`,
    // the same owner the turn validator calls (ADR-0015, KI-47), so the ceiling a form offers and
    // the ceiling a POST is measured against are literally one number.
    $stats = $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('scenario.panels.team_rank_ladder.on', true))
        ->inertiaProps('state.stats');

    $caps = ScenarioCaps::forRun($run);
    $byStat = collect($stats)->mapWithKeys(fn (array $stat): array => [$stat['key'] => $stat['cap']])->all();

    expect($byStat)->toBe($caps)
        // Unity Cup's Stamina is base 1,200 + 100.
        ->and($caps['Stamina'])->toBe(1300);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.caps.rows', function (Collection $rows) use ($caps): bool {
                foreach ($rows as $row) {
                    if ($row['cap'] !== $caps[$row['key']]) {
                        return false;
                    }
                }

                return true;
            }));
});

it('puts the scenario ceiling in the form, not a hardcoded 1200', function (): void {
    $run = scenarioPanelRun('trackblazer');

    // The browser pass found the server accepting Stamina 1900 while every stat input carried
    // max="1200". On the Cockpit the published caps are the scenario's own numbers, not a hardcoded
    // 1200, and SP is absent because it is not one of the five rated stats.
    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.caps.rows', function (Collection $rows): bool {
                $byKey = $rows->keyBy('key');

                return $byKey['Stamina']['cap'] === 1900
                    && $byKey['Wit']['cap'] === 1500
                    && $byKey['Speed']['cap'] === 1200;
            })
            ->where('scenario.caps.rows', fn (Collection $rows): bool => ! $rows->contains(
                fn (array $row): bool => $row['key'] === 'SP'
            )));
});

it('still honours the bonus a scenario the Trainer actually chose', function (): void {
    $run = scenarioPanelRun('ura_finale');

    // The guard against over-correcting. A chosen URA Finale's Speed ceiling is base + its own 200,
    // and the strip carries that breakdown rather than the no-bonus branch. The scenario section
    // carries no scenario identity (G-33), so the run section's `scenario` names it.
    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('run.scenario', 'ura_finale')
            ->where('scenario.caps.rows', function (Collection $rows): bool {
                $speed = $rows->firstWhere('key', 'Speed');

                return $speed['cap'] === 1400 && $speed['bonus'] === 200;
            }));
});
