<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Enums\TurnEventType;
use App\Models\Advisor\BuildTargetPayload;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\TurnEvent;
use App\Models\TurnEvents\TeamRankPayload;
use App\Models\Umamusume;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * G4: the Unity Cup decision surfaces (plan §9.1's team system, `docs/Unity-Cup-plan.md`).
 *
 * **The scope is the owner's two rulings of 2026-10-11.** No focusable control may land inside the
 * Scenario panel region, because `tests/browser/unity-cup-panel.spec.ts:145` pins it at zero and its
 * purpose is that the panel hides no reading behind a click — so this slice builds no drawer and
 * asserts nothing about one. And the Team Board carries only the fact the screen lacks: rank, the
 * rank-to-level ladder, the five stat grades, the per-teammate burst states and the burst bands all
 * already render in `UnityCupPanel.vue`, and re-drawing any of them is the second representation the
 * cockpit forbids (its own comment at `Career/CockpitController.php:465-470`).
 *
 * Two of the four surfaces the slice was written with are therefore not built: `UnityCupBurstBank.vue`
 * (its gauge, its per-member states and both refused halves already print, at `:199-228` and `:308-310`)
 * and `UnityCupRaceBriefing.vue` (its circles margin and Alarm Clock line are `TeamRacePanel.vue:27-29`).
 * `TrainingRun::facilityLevel()` is the existing rank→level owner, so the board does not re-derive it.
 *
 * **Race coverage has no threshold anywhere.** A repo-wide config search for a coverage figure returns
 * nothing, and `scenario_slots` carries no distance column, so the verdict is an absence by the slice's
 * own conditional ("three states reachable only if threshold config-declared"). The counts are real and
 * come from the run's own race entries; the verdict that would need a published figure is not computed.
 * The band list is `BuildTargetPayload::DISTANCE_BANDS`, which already owns that vocabulary — no second
 * copy of four words lands in `config/scenarios.php`.
 *
 * **The dual-value readout's firing predicate is the composition matrix, not a capability object.** No
 * such object exists in `app/Services/Advisor/` (grepped), and `TrainingRun::latestTeamRank()` already
 * refuses to answer unless `team_rank_ladder` is on, so the rail fires on the same gate the model uses.
 * Its badges are the two states the advisor's axis allows (`resources/js/types.ts:60-66`, owner ruling
 * 2a): a value with no reading carries **no badge at all**, because a badge qualifies a displayed value
 * and `unknown` beside an absence was retired here rather than on the panels.
 *
 * **Corrected 2026-10-25 (owner ruling R3): the first sentence above is superseded.** The 2026-10-11
 * "no focusable control in the Scenario panel region" ruling was amended in `DESIGN.md` §5(a) to permit
 * `AbsenceValue`'s disclosure there, and its premise was wrong on the tree: `TeamRankLadderPanel` renders
 * one "Record this race" link per ladder row, so the region was never control-free and the browser guard
 * at `unity-cup-panel.spec.ts` never pinned a zero. This file's two source scans are unchanged by that —
 * they covered `UnityCupTeamBoard.vue` and `UnityCupTournamentReadiness.vue`, neither of which renders a
 * control — and the guard now asks what a stop can do instead of how many there are.
 */

/**
 * The cockpit's props as plain arrays. `assertInertia` hands objects to a closure as `Collection` at
 * each depth, which turns a structural read into a fight with the wrapper, so the page is unwrapped
 * once here and every case below reads arrays.
 *
 * @return array<string, mixed>
 */
function g4CockpitData(TestResponse $response): array
{
    /** @var array<string, mixed> $props */
    $props = [];

    $response->assertOk()->assertInertia(function (Assert $page) use (&$props): void {
        $props = (array) $page->toArray()['props'];
    });

    return $props;
}

/**
 * Speed 300 under target with every stat read at 600, so the rail ranks `training-Speed` first.
 *
 * @var array<string, int>
 */
const G4_TARGETS = ['Speed' => 900, 'Stamina' => 500, 'Power' => 500, 'Guts' => 500, 'Wit' => 500];

/**
 * A run on `$scenario` with `$turns` logged turns, each at 600 on all five stats and Energy recorded.
 *
 * Local to this file on purpose. `UnityCupPanelTest.php` and `NextDecisionTest.php` both define helpers
 * that do the same thing, but a helper imported from another file breaks
 * `vendor/bin/pest tests/Feature/UnityCupSurfacesTest.php` — the focused run, which is the Level 1
 * command in `AGENTS.md` §10 and the one that catches a fixture mistake in seconds.
 */
function g4Run(?string $scenario, int $turns = 0, ?array $targets = null): TrainingRun
{
    $run = TrainingRun::factory()->create([
        'scenario' => $scenario,
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->create()->id,
    ]);

    foreach (range(1, $turns) as $turn) {
        TurnEntry::factory()->create([
            'training_run_id' => $run->id,
            'turn' => $turn,
            'speed' => 600,
            'stamina' => 600,
            'power' => 600,
            'guts' => 600,
            'wit' => 600,
            'sp' => 120,
            'energy' => 80,
            'fans' => 1000 * $turn,
        ]);
    }

    if ($targets !== null) {
        $run->update(['build_target' => BuildTargetPayload::fromArray([
            'purpose' => 'StoryClear',
            'distance' => 'Medium',
            'surface' => 'Turf',
            'style' => 'Pace Chaser',
            'targets' => $targets,
            'skill_priorities' => [],
        ])->toArray()]);
    }

    return $run->fresh(['turnEntries', 'raceEntries', 'turnEvents']);
}

/** One turn event carrying a team payload, the shape `runs.turns.store` already writes. */
function g4Recorded(TrainingRun $run, int $turn, array $deltas): TurnEvent
{
    return TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => $turn,
        'event_type' => TurnEventType::Scenario,
        'source_name' => 'Unity Training',
        'deltas' => $deltas,
    ]);
}

it('carries the dual-value readout on the advised training action, each figure with its own source', function (): void {
    $run = g4Run('unity_cup', 2, G4_TARGETS);
    g4Recorded($run, 2, TeamRankPayload::make('A')->toArray());

    /** @var array<string, mixed> $recommended */
    $recommended = (array) g4CockpitData($this->get(route('runs.cockpit', $run)))['advisor']['decision']['recommended'];

    expect($recommended['action'])->toBe('Speed');

    /** @var list<array<string, mixed>> $values */
    $values = (array) $recommended['team_value'];
    $byLabel = collect($values)->keyBy('label');

    expect($values)->toHaveCount(2)
        ->and($byLabel->keys()->all())->toBe(['Trainee value', 'Team value'])
        // The trainee figure is the room left under the cap, read through the same ScenarioCaps call
        // the header bars make (ADR-0015), so the rail and the strip cannot disagree about a ceiling.
        ->and($byLabel->get('Trainee value')['value'])->toBe('700 below the Speed cap of 1,300')
        ->and($byLabel->get('Trainee value')['badge'])->toBe('calculated')
        ->and($byLabel->get('Trainee value')['title'])->toContain('ScenarioCaps')
        ->and($byLabel->get('Trainee value')['title'])->toContain('not a gain the session is promised')
        // The team figure is the reported rank letter mapped through the config ladder.
        ->and($byLabel->get('Team value')['value'])->toBe('Team rank A, facility level 4')
        ->and($byLabel->get('Team value')['badge'])->toBe('calculated')
        ->and($byLabel->get('Team value')['title'])->toContain("the scenario's own rank ladder");
});

it('gives the team figure no value and no badge until the run reports a rank', function (): void {
    /** @var list<array<string, mixed>> $values */
    $values = (array) g4CockpitData($this->get(route('runs.cockpit', g4Run('unity_cup', 2, G4_TARGETS))))['advisor']['decision']['recommended']['team_value'];
    $team = collect($values)->firstWhere('label', 'Team value');

    expect($team['value'])->toBeNull()
        // A badge qualifies a displayed value. With nothing displayed the row carries none, which is
        // ruling 2a's retirement of `unknown` on this axis rather than an omission.
        ->and($team['badge'])->toBeNull()
        ->and($team['title'])->toContain('no rank reported');
});

it('carries no dual-value readout for a scenario whose matrix composes no team ladder', function (): void {
    foreach (['ura_finale', 'trackblazer', 'our_grand_concert'] as $key) {
        $run = g4Run($key, 2, [
            'Speed' => 900, 'Stamina' => 500, 'Power' => 500, 'Guts' => 500, 'Wit' => 500,
        ]);

        /** @var array<string, mixed>|null $recommended */
        $recommended = data_get(g4CockpitData($this->get(route('runs.cockpit', $run))), 'advisor.decision.recommended');

        // The key is always carried and null when it does not fire, so the consumer has one shape. An
        // absent key would satisfy a bare null check and prove nothing.
        expect($recommended)->not->toBeNull()
            ->and($recommended)->toHaveKey('team_value')
            ->and($recommended['team_value'])->toBeNull();
    }
});

it('keeps the decision contract additive: the eight keys on top, team_value nested inside recommended', function (): void {
    /** @var array<string, mixed> $decision */
    $decision = (array) g4CockpitData($this->get(route('runs.cockpit', g4Run('unity_cup', 2, G4_TARGETS))))['advisor']['decision'];

    expect(array_keys($decision))->toBe([
        'priority', 'recommended', 'alternatives', 'why', 'switch_if', 'refusal', 'objectives', 'recorded_action',
    ]);

    /** @var array<string, mixed> $recommended */
    $recommended = (array) $decision['recommended'];

    expect(array_keys($recommended))->toBe(['action', 'action_url', 'benefit', 'team_value']);
});

it('boards the five team race categories with the published ask and no invented reading', function (): void {
    /** @var array<string, mixed> $coverage */
    $coverage = (array) g4CockpitData($this->get(route('runs.cockpit', g4Run('unity_cup', 2, G4_TARGETS))))['scenario']['team']['coverage'];

    /** @var list<array<string, mixed>> $rows */
    $rows = (array) $coverage['rows'];

    // A Team Race is five categories and Dirt is one of them. Keying the board off the four distance
    // bands would drop a category the guides name, so the guard is on the count as well as the words.
    expect(array_column($rows, 'category'))->toBe(['Sprint', 'Mile', 'Medium', 'Long', 'Dirt'])
        ->and(array_column($rows, 'asks'))->toBe(['3 fielded', '3 fielded', '3 fielded', '3 fielded', '3 fielded'])
        // No column records who this run fields, so no row gets a count and none gets a state.
        ->and(array_column($rows, 'fielded'))->toBe([null, null, null, null, null])
        ->and($coverage['badge'])->toBe('confirmed')
        // `91a6047` retired the `02-unity-cup.md:149` citation from shipped copy (a repository
        // identifier in front of a Trainer is what `UiHygieneTest` forbids); the sentence that ships
        // names the guide in words instead.
        ->and($coverage['source_title'])->toContain('a guide transcribed into the scenario\'s own settings')
        ->and($coverage['absence_title'])->toContain('No column records who this run fields');
});

it('carries an empty coverage and tournament block for a scenario that declares no team race', function (): void {
    $run = g4Run('ura_finale', 1, [
        'Speed' => 900, 'Stamina' => 500, 'Power' => 500, 'Guts' => 500, 'Wit' => 500,
    ]);

    $team = (array) g4CockpitData($this->get(route('runs.cockpit', $run)))['scenario']['team'];

    expect($team['coverage'])->toBeNull()
        ->and($team['tournament'])->toBeNull();
});

it('reads the published team race shape and refuses an opponent-projection claim', function (): void {
    /** @var array<string, mixed> $tournament */
    $tournament = (array) g4CockpitData($this->get(route('runs.cockpit', g4Run('unity_cup', 2, G4_TARGETS))))['scenario']['team']['tournament'];

    expect($tournament['cadence_months'])->toBe(6)
        ->and($tournament['month_anchors'])->toBe(['Late June', 'Late December'])
        ->and($tournament['category_count'])->toBe(5)
        ->and($tournament['clear_all_reward'])->toBe('an increase to all stats, sized by performance')
        ->and($tournament['badge'])->toBe('confirmed')
        // Same retirement as the coverage row above: the shipped sentence names the scenario guides
        // rather than the `SCENARIO-PUBLISHER-REFERENCES` key.
        ->and($tournament['source_title'])->toContain('transcribed from the scenario guides')
        // The one line the slice asks for, and the only answer the corpus supports.
        ->and($tournament['projection'])->toBeNull()
        ->and($tournament['projection_title'])->toContain('No source says how strong each opponent team is')
        // The matrix's three opponent names are uncorroborated (two appear in no document the repo
        // holds), so none of them crosses the wire to be printed as a client fact.
        ->and($tournament)->not->toHaveKey('opponents')
        ->and($tournament['risks'])->toBe([
            'No published source ranks the three teams, so the screen calls no side of a team race easier.',
            'No column records who this run fields in each category, so the board states the ask and not the run.',
        ]);
});

it('states the next team race by its published months rather than by a turn nobody publishes', function (): void {
    /** @var array<string, mixed> $tournament */
    $tournament = (array) g4CockpitData($this->get(route('runs.cockpit', g4Run('unity_cup', 2, G4_TARGETS))))['scenario']['team']['tournament'];

    // references:1186 records the per-round turn count as unverified, so the anchor is the calendar
    // month the guides state and never a derived turn.
    expect($tournament['next'])->toBeNull()
        ->and($tournament['next_title'])->toContain('every 6 months')
        ->and($tournament['next_title'])->toContain('Late June')
        ->and($tournament['next_title'])->toContain('no source publishes the career turn');
});

it('keeps the refused vocabulary and every number out of the two new components', function (): void {
    $files = [
        resource_path('js/components/scenario/UnityCupTeamBoard.vue'),
        resource_path('js/components/scenario/UnityCupTournamentReadiness.vue'),
    ];

    foreach ($files as $file) {
        $source = (string) file_get_contents($file);

        expect(strtolower($source))->not->toContain('win probability')
            ->and(strtolower($source))->not->toContain('rank if won')
            ->and(strtolower($source))->not->toContain('recommended timing')
            ->and(strtolower($source))->not->toContain('projected benefit')
            ->and($source)->not->toMatch('/\d+\s*%/')
            // The numbers and the band names arrive composed; a literal here would be a second owner.
            ->and($source)->not->toMatch('/Sprint|Elite Team Ares|occurs_every_months/')
            // Owner ruling 1: the panel region carries no focusable control.
            ->and($source)->not->toMatch('/<(button|a\s|details|input|select|textarea)|tabindex=/');
    }
});

it('says where Team Rank is recorded rather than leaving the editor silent (R4-13, ruling R2)', function (): void {
    $source = (string) file_get_contents(resource_path('js/components/career/UnityCupEditor.vue'));

    // R2 = A: copy-only. The rank already has a writer — a turn's team-race event — so the defect is
    // that the editor does not say so, not that it lacks an input. A rank field appearing here would be
    // a second writer for one fact, which is what this case fails on.
    expect($source)->toContain("Team Rank is recorded through a turn's team-race event, not through this editor.")
        ->and($source)->not->toMatch('/name=\"team_rank\"|id=\"team_rank\"/');
});

it('leaves the ranking engine alone and reads a stat through the model rather than a new map', function (): void {
    $engine = (string) file_get_contents(app_path('Services/Advisor/TrainerAdvisor.php'));
    $decision = (string) file_get_contents(app_path('Services/Advisor/NextDecision.php'));

    // The checkable form of the rule that `NextDecision.php:15-17` states in prose: the engine is wrapped,
    // not edited. Its stat accessor staying private is what keeps a caller from widening it for a new
    // slice, and `NextDecision` cannot assert it does not *mention* the engine — it injects it in its
    // constructor and reads three of its constants, so that assertion would only ever be writable false.
    expect($engine)->toContain('private function current(')
        ->and($engine)->not->toContain('public function current(');

    // And the dual readout reaches the figure through the model's own attribute accessor rather than
    // writing a fourth `match` over the five stat columns.
    expect($decision)->toContain('getAttribute($stat)')
        ->and($decision)->not->toContain("'Speed' => (int) \$latest->speed");
});

it('records no coverage, tournament or team readout for a run whose scenario was never chosen', function (): void {
    $data = g4CockpitData($this->get(route('runs.cockpit', g4Run(null))));

    $team = (array) $data['scenario']['team'];

    expect($team['coverage'])->toBeNull()
        ->and($team['tournament'])->toBeNull()
        ->and($team['rank'])->toBeNull()
        ->and(data_get($data, 'advisor.decision.recommended.team_value'))->toBeNull();
});
