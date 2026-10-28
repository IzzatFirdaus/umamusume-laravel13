<?php

declare(strict_types=1);

use App\Enums\MoodTier;
use App\Enums\RunStatus;
use App\Enums\TurnChoice;
use App\Enums\TurnEventType;
use App\Models\Advisor\BuildTargetPayload;
use App\Models\DeckSlot;
use App\Models\SupportCard;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Services\ScenarioCaps;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * SCREEN-010, the Training Decision detail (plan §8 D9). The props are asserted here; the rendered
 * copy, the disclosure keyboard path and the `N/A` titles live in
 * `tests/browser/career-training-detail.spec.ts` (plan §2, convention 2).
 *
 * The acceptance this file exists to hold is the exclusion, not the display: the wire may carry
 * entered values, stored catalog anchors, the scenario matrix's own figures and the advisor's
 * arithmetic, and nothing else. A per-training yield and a failure probability have no source
 * (`docs/research-scratch/PROCESS-PLANS.md` section `trainer-advisor.md` §1 and §5, `ADR-0001` §3),
 * so an option payload is asserted to have exactly the keys it is allowed to have. Add a field that
 * could carry a projected number and this test goes red before a screen does.
 */

/**
 * The option payload's whole shape, alphabetised. The key-set test compares against this list, which
 * is why the keys are named here rather than left to whatever the controller happened to assemble.
 */
const OPTION_KEYS = [
    'band',
    'cap',
    'cap_bonus',
    'choice',
    'cost',
    'current',
    'deficit',
    'energy_after',
    'key',
    'published',
    'reason',
    'recommended',
    'scenario_effects',
    'supports',
    'target',
];

/**
 * A run with `$turns` logged turns, the last carrying the given Energy, and an optional build target.
 *
 * Every stat is logged at 600 so a deficit is the difference between one target and one known number
 * rather than an arithmetic exercise in the test.
 */
function decisionRun(array $runState = [], int $turns = 0, ?int $energy = null, ?array $targets = null): TrainingRun
{
    $run = TrainingRun::factory()->create($runState);

    for ($turn = 1; $turn <= $turns; $turn++) {
        TurnEntry::factory()->create([
            'training_run_id' => $run->id,
            'turn' => $turn,
            'speed' => 600,
            'stamina' => 600,
            'power' => 600,
            'guts' => 600,
            'wit' => 600,
            'sp' => 120,
            'energy' => $turn === $turns ? $energy : 80,
            'mood' => MoodTier::Normal,
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

    return $run->refresh();
}

it('renders one card per training in the stat matrix order, with the sourced energy cost on each', function (): void {
    $run = decisionRun(['scenario' => 'unity_cup', 'status' => RunStatus::Active], turns: 1, energy: 80);

    [$sessionLow, $sessionHigh] = config('advisor.constants.session_cost.value');
    $witCost = config('advisor.constants.wit_cost.value');

    $this->get(route('runs.training', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/TrainingDetail')
            ->where('run.id', $run->id)
            ->where('run.scenario_label', 'Unity Cup')
            ->where('run.run_url', route('runs.cockpit', $run))
            ->where('run.cockpit_url', route('runs.cockpit', $run))
            ->count('options', 5)
            ->where('options.0.key', 'Speed')
            ->where('options.0.choice', 'training-Speed')
            ->where('options.1.key', 'Stamina')
            ->where('options.2.key', 'Power')
            ->where('options.3.key', 'Guts')
            ->where('options.4.key', 'Wit')
            // The cost is the config entry, range and all: `session_cost` scales with a training level
            // this tool does not track, so a point value would be invented precision (ADR-0001 §2).
            ->where('options.0.cost', fn ($cost): bool => $cost['min'] === $sessionLow
                && $cost['max'] === $sessionHigh
                && $cost['source'] === config('advisor.constants.session_cost.source')
                && $cost['verified_at'] === config('advisor.constants.session_cost.verified_at')
                && $cost['confidence'] === config('advisor.constants.session_cost.confidence'))
            // `wit_cost` is a single sourced figure, so its range has two equal ends (ADR-0001 §2).
            ->where('options.4.cost', fn ($cost): bool => $cost['min'] === $witCost
                && $cost['max'] === $witCost
                && $cost['source'] === config('advisor.constants.wit_cost.source')));
});

it('carries an option field set that has no home for a projected yield or a failure rate', function (): void {
    $run = decisionRun(['scenario' => 'ura_finale', 'status' => RunStatus::Active], turns: 1, energy: 80);

    $this->get(route('runs.training', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // Every option, not just the first: a field that exists only on Wit would pass otherwise.
            ->where('options', fn ($options): bool => collect($options)
                ->every(fn ($option): bool => collect($option)->keys()->sort()->values()->all() === OPTION_KEYS))
            ->missing('options.0.yield')
            ->missing('options.0.expected_gains')
            ->missing('options.0.failure')
            ->missing('options.0.failure_probability')
            ->missing('options.0.risk_percent')
            ->missing('options.0.score'));
});

it('states the target deficit as the advisor calculated it, and nothing as a gain', function (): void {
    $run = decisionRun(
        ['scenario' => 'unity_cup', 'status' => RunStatus::Active],
        turns: 1,
        energy: 80,
        targets: ['Speed' => 900, 'Stamina' => 500, 'Power' => 500, 'Guts' => 500, 'Wit' => 800],
    );

    $caps = ScenarioCaps::forRun($run);

    $this->get(route('runs.training', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('options.0.current', 600)
            ->where('options.0.target', 900)
            ->where('options.0.cap', $caps['Speed'])
            // 900 - 600. The number is C2's own, not a second subtraction in a controller.
            ->where('options.0.deficit', 300)
            ->where('options.0.band', 'AtOrAboveAdvisory')
            ->where('options.0.reason', 'Speed is 300 below target (the largest deficit).')
            // A stat already at or above its target has a deficit of zero, which is an answer.
            ->where('options.1.deficit', 0)
            // Wit's band is the engine's own refusal, not a third state of the Energy scale.
            ->where('options.4.band', 'RiskNotMeasured')
            ->where('options.4.reason', 'Wit costs 0 Energy and you are at 80.')
            ->where('options.4.deficit', 200)
            // Energy after, as the range the cost implies: 80 - 28 through 80 - 17.
            ->where('options.0.energy_after', ['min' => 52, 'max' => 63])
            ->where('options.4.energy_after', ['min' => 80, 'max' => 80]));
});

it('marks exactly one card recommended, none when the advisor recommends Rest, and none when it declines', function (): void {
    $recommended = decisionRun(
        ['scenario' => 'unity_cup', 'status' => RunStatus::Active],
        turns: 1,
        energy: 80,
        targets: ['Speed' => 900, 'Stamina' => 500, 'Power' => 500, 'Guts' => 500, 'Wit' => 800],
    );

    $this->get(route('runs.training', $recommended))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('advisor.action', 'Speed')
            ->where('options', fn ($options): bool => collect($options)->filter(fn ($o): bool => $o['recommended'])->count() === 1)
            ->where('options.0.recommended', true)
            ->where('options.4.recommended', false));

    // Below the sourced 50 line the advisor recommends Rest, which is not one of the five training
    // cards, so no card may borrow the marker.
    $resting = decisionRun(
        ['scenario' => 'unity_cup', 'status' => RunStatus::Active],
        turns: 1,
        energy: 30,
        targets: ['Speed' => 900, 'Stamina' => 500, 'Power' => 500, 'Guts' => 500, 'Wit' => 800],
    );

    $this->get(route('runs.training', $resting))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('advisor.action', 'Rest')
            ->where('advisor.band', 'BelowAdvisory')
            ->where('options', fn ($options): bool => collect($options)->every(fn ($o): bool => $o['recommended'] === false)));

    // No Energy and no target: C2's first rule is to name the absence, so the page carries the refusal.
    $declined = decisionRun(['status' => RunStatus::Active]);

    $this->get(route('runs.training', $declined))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('advisor.action', null)
            ->where('advisor.band', null)
            ->where('advisor.absence', 'This run has recorded no Energy, so there is no band to read and nothing to rank against.')
            ->where('options.0.deficit', null)
            ->where('options.0.band', null)
            ->where('options.0.energy_after', null)
            ->where('options.0.current', null)
            ->where('options.0.target', null)
            ->where('options', fn ($options): bool => collect($options)->every(fn ($o): bool => $o['recommended'] === false)));
});

it('lists the deck cards entered for that training with their stated anchors, and says so when there is no deck', function (): void {
    $run = decisionRun(['scenario' => 'unity_cup', 'status' => RunStatus::Active], turns: 1, energy: 80);
    $speed = SupportCard::factory()->speed()->create(['char_name' => 'Rice Shower', 'title_en' => '[Rosy Dreams]']);
    DeckSlot::factory()->atPosition(1)->create(['training_run_id' => $run->id, 'support_card_id' => $speed->id]);

    $this->get(route('runs.training', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('deck.recorded', true)
            ->where('deck.slots', 1)
            ->where('options.0.supports', fn ($supports): bool => count($supports) === 1
                // `displayName()` is the landed label: a character name and its bracketed epithet.
                && $supports[0]['name'] === 'Rice Shower [Rosy Dreams]'
                // The export's own anchor at the card's highest stated level, never interpolated.
                && count($supports[0]['effects']) === 1
                && $supports[0]['effects'][0]['display'] === '15')
            // No Stamina card is in the deck, so the Stamina card has nothing to show.
            ->where('options.1.supports', [])
            // `intelligence` is the export key and Wit is the client word; the match is on the word.
            ->where('options.4.supports', []));

    $noDeck = decisionRun(['scenario' => 'unity_cup', 'status' => RunStatus::Active], turns: 1, energy: 80);

    $this->get(route('runs.training', $noDeck))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('deck.recorded', false)
            ->where('deck.slots', 0)
            ->where('options.0.supports', [])
            // R2-18: the screen's "record a deck" door belongs to this run. It pointed at
            // `/career/setup/deck`, which is step 5 of a new wizard rather than this career's deck, so
            // following it abandoned the run being trained and started an unrelated setup draft.
            ->where('run.deck_url', route('runs.deck', $noDeck)));

    // And the wizard's step is not printed as a path on this screen any more, in markup or in script.
    expect(file_get_contents(resource_path('js/pages/Career/TrainingDetail.vue')))
        ->not->toContain('/career/setup/deck');
});

it('carries a Wit deck card onto the Wit card under the client word, not the export key', function (): void {
    $run = decisionRun(['scenario' => 'unity_cup', 'status' => RunStatus::Active], turns: 1, energy: 80);
    $wit = SupportCard::factory()->wit()->create(['char_name' => 'Agnes Tachyon']);
    DeckSlot::factory()->atPosition(2)->create(['training_run_id' => $run->id, 'support_card_id' => $wit->id]);

    $this->get(route('runs.training', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('options.4.supports', fn ($supports): bool => count($supports) === 1
                && $supports[0]['name'] === 'Agnes Tachyon [Tracen Academy]')
            ->where('options.0.supports', []));
});

it('carries the scenario matrix published per-level row keyed to the facility level the turn recorded', function (): void {
    // Unity Cup with every facility at the Level 3 the turn recorded: the artifact's Speed row
    // there is +10 Speed, +4 Power, +4 SP, costing E−21. The figures are JP-sourced and the
    // server tag rides the payload; Unity Cup's one Global divergence rides the notes, which is
    // the wiring's Caveat 3 made checkable.
    $run = decisionRun(['scenario' => 'unity_cup', 'status' => RunStatus::Active], turns: 1, energy: 80);
    TurnEntry::query()->where('training_run_id', $run->id)->update([
        'facility_speed' => 3, 'facility_stamina' => 3, 'facility_power' => 3,
        'facility_guts' => 3, 'facility_wit' => 3,
    ]);

    $tables = config('scenarios.scenarios.unity_cup.training_tables');

    $this->get(route('runs.training', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('options.0.published', fn ($published): bool => $published['gains']['level'] === 3
                && $published['gains']['text'] === '+10 Speed, +4 Power, +4 SP'
                && $published['gains']['server'] === $tables['server']
                && $published['gains']['source'] === $tables['source']
                && in_array('Global SP payouts for Unity Cup match URA Finale (+2 SP on Energy disciplines) after the 2026-07-01 rework, not the JP +4 values. The base stat gains are unchanged.', $published['gains']['notes'], true)
                && $published['cost']['value'] === -21
                && $published['cost']['text'] === 'E−21'
                && $published['risk_reason'] === config('scenarios.scenarios.unity_cup.risk_absence.reason'))
            // Wit's row states a refund, and the parity pass carries it as the signed figure the
            // HFW prints (+5 Energy). Its risk row is the engine's own RiskNotMeasured word, so no
            // published reason rides it.
            ->where('options.4.published.gains.text', '+8 Wit, +2 Speed, +5 SP')
            ->where('options.4.published.cost.value', 5)
            ->where('options.4.published.cost.text', 'E+5')
            ->where('options.4.published.risk_reason', null));
});

it('renders the published row as a reasoned absence when the turn recorded no facility level', function (): void {
    $run = decisionRun(['scenario' => 'ura_finale', 'status' => RunStatus::Active], turns: 1, energy: 80);

    $this->get(route('runs.training', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // A Level-1 figure shown for an unrecorded level would state a level nobody entered,
            // so the absence names the missing reading instead of substituting the floor row.
            ->where('options.0.published.gains', null)
            ->where('options.0.published.cost', null)
            ->where('options.0.published.gains_absence', fn (string $absence): bool => str_contains($absence, 'No facility level is recorded'))
            ->where('options.0.published.risk_reason', config('scenarios.scenarios.ura_finale.risk_absence.reason')));

    // And the reason is the artifact's own per scenario: Trackblazer's recorded search carries
    // no parenthetical, so the verbatim carriage is per entry, not one shared sentence.
    $trackblazer = decisionRun(['scenario' => 'trackblazer', 'status' => RunStatus::Active], turns: 1, energy: 80);

    $this->get(route('runs.training', $trackblazer))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('options.0.published.risk_reason', config('scenarios.scenarios.trackblazer.risk_absence.reason')));
});

it('resolves the published row against the most recent turn that recorded a level, not the latest turn alone', function (): void {
    // The shape of a real career: turn 6 is the race logged without opening the ladder, turn 5 carries
    // the reading the client showed. Keying off the newest row printed N/A on all five cards for a run
    // whose facility levels are known, so each stat walks back on its own.
    $run = decisionRun(['scenario' => 'unity_cup', 'status' => RunStatus::Active], turns: 6, energy: 80);
    TurnEntry::query()->where('training_run_id', $run->id)->where('turn', 5)->update([
        'facility_speed' => 4, 'facility_stamina' => 4, 'facility_power' => 4,
        'facility_guts' => 5, 'facility_wit' => 4,
    ]);

    $this->get(route('runs.training', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // The level is what the walk-back decides; the row a level selects is pinned for this same
            // table by the two cases above and the 4-by-5 grid case below.
            ->where('options.0.published.gains.level', 4)
            ->where('options.0.published.gains_absence', null)
            // Guts is the one facility the client showed at 5, and it resolves to its own row.
            ->where('options.3.published.gains.level', 5)
            ->where('options.4.published.gains.level', 4)
            ->where('options.0.published.cost.level', 4));
});

it('prefills the record form with the facility levels this run last read', function (): void {
    // The ladder is a state the client keeps showing, so the next turn opens on the last reading and
    // the Trainer changes only what moved. A stat nobody recorded stays null, which the form renders
    // as a blank input rather than a level it never saw.
    $run = decisionRun(['scenario' => 'unity_cup', 'status' => RunStatus::Active], turns: 2, energy: 80);
    TurnEntry::query()->where('training_run_id', $run->id)->where('turn', 1)->update([
        'facility_speed' => 4, 'facility_stamina' => 3,
    ]);

    $this->get(route('runs.training', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('write.previous.facility_speed', 4)
            ->where('write.previous.facility_stamina', 3)
            ->where('write.previous.facility_power', null)
            ->where('write.previous.facility_guts', null)
            ->where('write.previous.facility_wit', null));
});

it('carries no published row and no scenario risk reason when the run declares no scenario', function (): void {
    $run = decisionRun(['status' => RunStatus::Active], turns: 1, energy: 80);

    $this->get(route('runs.training', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('options.0.published.gains', null)
            ->where('options.0.published.cost', null)
            ->where('options.0.published.risk_reason', null)
            ->where('options.0.published.gains_absence', fn (string $absence): bool => str_contains($absence, 'declares no scenario')));
});

it('resolves a published row for every discipline on every scenario the matrix composes, keyed to the recorded level', function (): void {
    // The wiring brief's grid check: 4 scenarios by 5 disciplines, each reading the row its own
    // recorded facility level names. The expected text is composed here from the same config the
    // controller reads, so a wrong routing or a wrong level key fails per cell, and the one
    // per-server divergence note appears only where the matrix records it.
    foreach (array_keys((array) config('scenarios.scenarios')) as $scenarioKey) {
        $run = decisionRun(['scenario' => $scenarioKey, 'status' => RunStatus::Active], turns: 1, energy: 80);
        TurnEntry::query()->where('training_run_id', $run->id)->update([
            'facility_speed' => 3, 'facility_stamina' => 3, 'facility_power' => 3,
            'facility_guts' => 3, 'facility_wit' => 3,
        ]);

        /** @var array<string, mixed> $tables */
        $tables = config('scenarios.scenarios.'.$scenarioKey.'.training_tables');
        $routes = ['Speed' => ['Power'], 'Stamina' => ['Guts'], 'Power' => ['Stamina'], 'Guts' => ['Speed', 'Power'], 'Wit' => ['Speed']];

        $this->get(route('runs.training', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('options', function ($options) use ($tables, $routes, $scenarioKey): bool {
                    foreach ($options as $index => $option) {
                        $stat = $option['key'];
                        $row = $tables['per_level'][$stat]['Lv3'];

                        $parts = ['+'.$row['primary'].' '.$stat];
                        foreach ($routes[$stat] as $i => $route) {
                            $key = $i === 0 ? (array_key_exists('secondary', $row) ? 'secondary' : 'secondary_a') : 'secondary_b';
                            $parts[] = '+'.$row[$key].' '.$route;
                        }
                        $parts[] = '+'.$row['skill_points'].' SP';

                        if ($option['published']['gains']['text'] !== implode(', ', $parts)
                            || $option['published']['gains']['level'] !== 3
                            || $option['published']['gains']['server'] !== $tables['server']
                            || $option['published']['gains']['source'] !== $tables['source']) {
                            return false;
                        }

                        // Every discipline carries the row's own Energy figure, signed: the four
                        // Energy disciplines spend it, Wit returns it as a refund. Unity Cup's
                        // divergence note rides only its own entries.
                        if ($option['published']['cost'] === null
                            || $option['published']['cost']['value'] !== $row['energy']) {
                            return false;
                        }

                        $notes = implode(' ', $option['published']['gains']['notes']);
                        $hasDivergence = str_contains($notes, 'Global SP payouts for Unity Cup');

                        if ($hasDivergence !== ($scenarioKey === 'unity_cup')) {
                            return false;
                        }
                    }

                    return true;
                }));
    }
});

it('states the scenario effects from the matrix alone, and names the absence for a run with no scenario', function (): void {
    $unityCup = decisionRun(['scenario' => 'unity_cup', 'status' => RunStatus::Active], turns: 1, energy: 80);
    $bonus = config('scenarios.scenarios.unity_cup.cap_bonus');

    $this->get(route('runs.training', $unityCup))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('options.0.cap_bonus', $bonus['Speed'])
            ->where('options.4.cap_bonus', $bonus['Wit'])
            // The matrix's two training keys: the team penalty reads on every card, the burst bonus
            // only on the one it names, so it may not appear on the Speed card.
            ->where('options.0.scenario_effects', [
                ['label' => 'Extra Energy cost for team training', 'value' => 'No'],
            ])
            ->where('options.4.scenario_effects', [
                ['label' => 'Extra Energy cost for team training', 'value' => 'No'],
                ['label' => 'Energy a Wit burst returns', 'value' => (string) config('scenarios.scenarios.unity_cup.wit_burst_energy_bonus')],
            ]));

    $noScenario = decisionRun(['status' => RunStatus::Active], turns: 1, energy: 80);

    $this->get(route('runs.training', $noScenario))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('run.scenario_label', 'No scenario set')
            ->where('options.0.cap_bonus', null)
            ->where('options.0.scenario_effects', null));
});

it('posts the turn through the write the run screen already owns', function (): void {
    $run = decisionRun(['scenario' => 'unity_cup', 'status' => RunStatus::Active], turns: 3, energy: 80);

    $this->get(route('runs.training', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('write.action', route('runs.turns.store', $run))
            ->where('write.turn', 4)
            ->where('write.moods', array_column(MoodTier::cases(), 'value'))
            // The last logged row travels as a placeholder and never as a field value: an input
            // arriving pre-filled would assert the stat did not change (D-220).
            ->where('write.previous.speed', 600)
            ->where('write.previous.energy', 80));

    // The page posts directly to the turn write the run screen already owns, with no `stage`
    // intermediate (F2, plan §9.6; owner ruling on group R-2 retired the preview-and-confirm rail).
    // The write lands and the response redirects to the Cockpit, where the recorded turn appears
    // in the correction selector.
    $this->post(route('runs.turns.store', $run), [
        'turn' => 4,
        'speed' => 640,
        'stamina' => 600,
        'power' => 600,
        'guts' => 600,
        'wit' => 600,
        'energy' => 62,
        'mood' => 'GOOD',
        'fans' => 4000,
        'sp' => 150,
        'choice' => 'training-Speed',
        'outcome' => 'Success',
    ])->assertRedirect(route('runs.cockpit', $run));

    expect($run->turnEntries()->where('turn', 4)->exists())->toBeTrue();
});

it('offers Rest and Recreation as turn choices beside the five trainings', function (): void {
    // `SCREEN_SPEC.md` SCR-CAR-012 rules them turn choices, not training options: they ride on the
    // write so the one record form covers all seven turns a career takes, and they are never a sixth
    // and seventh card. Their words are `TurnChoice`'s, which is why Recreation arrives as `mood`,
    // the value every already-written row is looked up by.
    $run = decisionRun(['scenario' => 'unity_cup', 'status' => RunStatus::Active], turns: 3, energy: 80);

    $this->get(route('runs.training', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('write.extra_choices', 2)
            ->where('write.extra_choices.0.value', 'rest')
            ->where('write.extra_choices.0.label', 'Rest')
            ->where('write.extra_choices.0.recommended', false)
            ->where('write.extra_choices.1.value', 'mood')
            ->where('write.extra_choices.1.label', 'Recreation')
            ->where('write.extra_choices.1.recommended', false)
            // The totals still travel as placeholders only: no source says a rest or an outing leaves
            // them where they were, so pre-filling them would assert what the corpus is silent on.
            ->where('write.previous.speed', 600));

    // Below the sourced 50 line the advisor recommends Rest, and the marker moves to that choice.
    $tired = decisionRun(['scenario' => 'unity_cup', 'status' => RunStatus::Active], turns: 3, energy: 30);

    $this->get(route('runs.training', $tired))
        ->assertInertia(fn (Assert $page) => $page
            ->where('advisor.action', 'Rest')
            ->where('write.extra_choices.0.recommended', true)
            ->where('write.extra_choices.1.recommended', false));
});

it('records a Rest turn and a Recreation turn carrying the prior totals', function (string $choice, string $label, string $outcome): void {
    // The five totals are required whatever the turn was, so a Rest or Recreation row carries the
    // readings the client still shows. This is the write the page's form posts, unchanged. The outcome
    // word travels with the choice because `TurnChoice::outcomes()` offers each turn its own pair or
    // triple (D-205): a rest succeeds or is poor, an outing lifts, holds or falls.
    $run = decisionRun(['scenario' => 'unity_cup', 'status' => RunStatus::Active], turns: 3, energy: 80);
    $prior = $run->turnEntries()->where('turn', 3)->sole();

    $this->post(route('runs.turns.store', $run), [
        'turn' => 4,
        'speed' => $prior->speed,
        'stamina' => $prior->stamina,
        'power' => $prior->power,
        'guts' => $prior->guts,
        'wit' => $prior->wit,
        'sp' => $prior->sp,
        'energy' => 100,
        'mood' => 'GOOD',
        'fans' => 4000,
        'choice' => $choice,
        'outcome' => $outcome,
    ])->assertRedirect(route('runs.cockpit', $run));

    $entry = $run->turnEntries()->where('turn', 4)->sole();

    expect(TurnChoice::from((string) $entry->choice)->label())->toBe($label)
        ->and([
            $entry->speed, $entry->stamina, $entry->power, $entry->guts, $entry->wit,
        ])->toBe([
            $prior->speed, $prior->stamina, $prior->power, $prior->guts, $prior->wit,
        ]);
})->with([
    ['rest', 'Rest', 'Success'],
    ['mood', 'Recreation', 'Lifted'],
]);

it('refuses a turn the Trainer has not finished entering, naming the fields', function (): void {
    $run = decisionRun(['scenario' => 'unity_cup', 'status' => RunStatus::Active], turns: 1, energy: 80);

    // Nothing here is a projected number: the five stat totals are what the Trainer reads off the
    // client, so an empty form is the honest state and the write refuses it rather than defaulting.
    // Without a `stage` marker the request treats `choice` and `outcome` as nullable, so the five
    // stats are the only fields a bare submit fails on.
    $this->post(route('runs.turns.store', $run), [
        'turn' => 2,
        'choice' => 'training-Speed',
    ])->assertSessionHasErrors(['speed', 'stamina', 'power', 'guts', 'wit']);
});

it('is reachable for every run status the tool holds', function (): void {
    foreach (RunStatus::cases() as $status) {
        $run = decisionRun(['status' => $status], turns: 1, energy: 80);

        $this->get(route('runs.training', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Career/TrainingDetail')
                ->where('run.status', $status->value)
                ->where('run.status_label', $status->label()));
    }
});

it('offers each turn choice its own outcome words, with the failure flag beside each', function (): void {
    // D-200 gives a choice a success branch and a failure branch unless it can be said it cannot fail,
    // and D-205 rules that Rest carries the same treatment as any other choice
    // (`docs/research-scratch/DESIGN-CORPUS.md`). `TurnChoice::outcomes()` is the one place naming the
    // words: the select reads this map and the Form Request reads the same method, so an offered word
    // and an accepted word cannot be two lists.
    $run = decisionRun(['scenario' => 'unity_cup', 'status' => RunStatus::Active], turns: 1, energy: 80);

    $this->get(route('runs.training', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('write.outcomes', 7)
            ->where('write.outcomes.training-Speed', ['Success' => false, 'Failure' => true])
            ->where('write.outcomes.rest', ['Success' => false, 'Poor Rest' => true])
            ->where('write.outcomes.mood', ['Lifted' => false, 'No Change' => false]));
});

it('accepts the outcome words its choice offers and refuses the ones another choice owns', function (string $choice, string $outcome, bool $accepted): void {
    $run = decisionRun(['scenario' => 'unity_cup', 'status' => RunStatus::Active], turns: 1, energy: 80);

    $post = $this->post(route('runs.turns.store', $run), [
        'turn' => 2,
        'speed' => 610, 'stamina' => 600, 'power' => 600, 'guts' => 600, 'wit' => 600,
        'sp' => 130, 'energy' => 90, 'mood' => 'GOOD', 'fans' => 2000,
        'choice' => $choice,
        'outcome' => $outcome,
        'penalty_kind' => 'mood',
    ]);

    if ($accepted) {
        $post->assertSessionHasNoErrors();

        return;
    }

    $post->assertSessionHasErrors('outcome');
})->with([
    ['rest', 'Success', true],
    ['rest', 'Poor Rest', true],
    ['mood', 'Lifted', true],
    ['mood', 'No Change', true],
    ['training-Speed', 'Success', true],
    ['training-Speed', 'Failure', true],
    // A word belongs to the turn that owns it. `Success` is not an outing's outcome, which is what keeps
    // the dropdown's list and the boundary's list one list rather than one flat vocabulary a Rest could
    // answer with an outing's word.
    ['mood', 'Success', false],
    ['rest', 'Lifted', false],
]);

it('writes a failure event for exactly the outcome words that name one', function (string $choice, string $outcome, bool $failed): void {
    // The event is the whole persistence of an outcome: no column stores the word, and the Timeline's
    // `×` row and its Failure chip are built from this write (`SCREEN_SPEC.md` SCR-RUN-002: a turn's
    // outcome lives in `turn_events`). Rest's poor tier is the one the corpus calls a failure
    // (`UMAMUSUME_REFERENCE.md` §1.1.5, D-201); `Fell` names a mood direction the sources do not
    // document as a Recreation failure (D-200/D-201/D-205), so it writes no event.
    $run = decisionRun(['scenario' => 'unity_cup', 'status' => RunStatus::Active], turns: 1, energy: 80);

    $this->post(route('runs.turns.store', $run), [
        'turn' => 2,
        'speed' => 610, 'stamina' => 600, 'power' => 600, 'guts' => 600, 'wit' => 600,
        'sp' => 130, 'energy' => 90, 'mood' => 'GOOD', 'fans' => 2000,
        'choice' => $choice,
        'outcome' => $outcome,
        'penalty_kind' => $failed ? 'mood' : null,
    ])->assertSessionHasNoErrors();

    expect($run->turnEvents()->where('event_type', TurnEventType::Failure->value)->exists())->toBe($failed);
})->with([
    ['rest', 'Success', false],
    ['rest', 'Poor Rest', true],
    ['mood', 'Lifted', false],
    ['mood', 'No Change', false],
    ['training-Speed', 'Success', false],
    ['training-Speed', 'Failure', true],
]);
