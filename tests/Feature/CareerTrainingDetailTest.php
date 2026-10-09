<?php

declare(strict_types=1);

use App\Enums\MoodTier;
use App\Enums\RunStatus;
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
            ->where('options.0.supports', []));
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
