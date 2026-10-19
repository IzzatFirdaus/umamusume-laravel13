<?php

declare(strict_types=1);

use App\Enums\EnergyState;
use App\Enums\RunStatus;
use App\Models\Advisor\BuildTargetPayload;
use App\Models\RaceCatalogSlot;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * FD-2, the shared Next Decision contract (plan §9a, PROMPT G1).
 *
 * **G-33 is asserted as a property of the payload, the way `ScenarioPanelTest.php:25-32` asserts it.**
 * The decision carries action names, route URLs and sentences; it carries no field whose name identifies
 * the scenario, so one consumer can draw every scenario with no branch. The positive half — all four
 * Global scenarios plus a fifth built from config alone produce the *same* key set — is what proves the
 * contract is shared rather than Cockpit-shaped.
 *
 * The ranking rules themselves are `TrainerAdvisorTest.php`'s. What this file owns is the contract: the
 * eight keys and nothing else, no `score`, no `confidence`, `recommended` and `refusal` mutually exclusive,
 * the two refusal copies distinct, and every figure carrying the provenance state its inputs deserve
 * (`ADR-0020` §2, ruling 1c, `ADR-0001` §2 and §7).
 */

/**
 * A run on `$scenario` with `$turns` logged turns and a five-stat target.
 *
 * Every turn reads 600 on all five stats, so a target above 600 is a deficit and a target at or below it
 * is closed — which makes "every stat is already at its target" reachable without a second fixture.
 *
 * @param  array<string, int>  $targets
 */
function nextDecisionRun(string $scenario, int $turns, ?int $energy, array $targets): TrainingRun
{
    $run = TrainingRun::factory()->create([
        'scenario' => $scenario,
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->create()->id,
    ]);

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
            'fans' => 1000 * $turn,
        ]);
    }

    $run->update(['build_target' => BuildTargetPayload::fromArray([
        'purpose' => 'StoryClear',
        'distance' => 'Medium',
        'surface' => 'Turf',
        'style' => 'Pace Chaser',
        'targets' => $targets,
        'skill_priorities' => [],
    ])->toArray()]);

    return $run->fresh();
}

/**
 * Speed 300 under target, Wit 200 under, the other three closed. Largest deficit Speed, alternative Wit.
 *
 * @var array<string, int>
 */
const DECISION_SHORT_TARGETS = [
    'Speed' => 900,
    'Stamina' => 500,
    'Power' => 500,
    'Guts' => 500,
    'Wit' => 800,
];

/**
 * Nothing under target: the engine ranks, evaluates all five, and finds no deficit to close.
 *
 * @var array<string, int>
 */
const DECISION_MET_TARGETS = [
    'Speed' => 600,
    'Stamina' => 600,
    'Power' => 600,
    'Guts' => 600,
    'Wit' => 600,
];

/**
 * The FD-2 shape as FD-3 extends it. Seven keys, no eighth.
 */
function decisionKeys(): array
{
    // FD-4 grew the frozen contract by one key (`recorded_action`, plan §9a FD-4 §5.2 and ruling D-1a).
    // Every other key, and the order of all eight, is exactly what G1 froze, so a ninth key or a rename
    // still fails here.
    return ['priority', 'recommended', 'alternatives', 'why', 'switch_if', 'refusal', 'objectives', 'recorded_action'];
}

/**
 * A fifth scenario from one config entry, built only from keys the matrix already uses.
 */
function registerFifthScenario(): void
{
    config()->set('scenarios.scenarios.fifth_scenario', [
        'label' => 'A Fifth Scenario',
        'live_on_global' => '2027-01-01',
        'cap_bonus' => ['Speed' => 0, 'Stamina' => 0, 'Power' => 0, 'Guts' => 0, 'Wit' => 0],
        'widgets' => ['turn', 'energy', 'fans', 'grade_points'],
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
}

it('carries the frozen eight-key contract with a ranked recommendation and no score or confidence', function (): void {
    $run = nextDecisionRun('unity_cup', 12, 80, DECISION_SHORT_TARGETS);

    /** @var array{priority: string, recommended: array{action: string, action_url: string, benefit: list<string>}, alternatives: list<array{action: string, action_url: string, tradeoff: string}>, why: list<array{text: string, badge: string, title: string}>, switch_if: list<string>, refusal: mixed} $decision */
    $decision = $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->inertiaProps('advisor.decision');

    expect(array_keys($decision))->toBe(decisionKeys())
        // The tier that fired is build targets; the words come from screen-spec-2.0 §29.
        ->and($decision['priority'])->toBe('Close the largest deficit against the build target you entered.')
        ->and($decision['refusal'])->toBeNull()
        // The engine's own reason line, verbatim, is the benefit; Energy-after is the second.
        ->and($decision['recommended']['action'])->toBe('Speed')
        ->and($decision['recommended']['action_url'])->toBe(route('runs.training', $run))
        ->and($decision['recommended']['benefit'])->toBe([
            'Speed is 300 below target (the largest deficit).',
            'Energy after this action: 52 to 63.',
        ])
        // Wit is the alternative and the engine's own Wit line is its trade-off.
        ->and($decision['alternatives'])->toBe([[
            'action' => 'Wit',
            'action_url' => route('runs.training', $run),
            'tradeoff' => 'Wit costs 0 Energy and you are at 80.',
        ]])
        ->and($decision['switch_if'])->toHaveCount(2);

    // Every row is a three-key figure. The states a row may claim are the next test's subject.
    foreach ($decision['why'] as $row) {
        expect(array_keys($row))->toBe(['text', 'badge', 'title']);
    }
});

it('prints the constant a figure rests on with its source, its read date and its stale flag, visibly', function (): void {
    $run = nextDecisionRun('unity_cup', 12, 80, DECISION_SHORT_TARGETS);

    /** @var array{why: list<array{text: string, badge: string, title: string}> $decision */
    $decision = $this->get(route('runs.cockpit', $run))->assertOk()->inertiaProps('advisor.decision');

    $texts = array_column($decision['why'], 'text');
    $titles = array_column($decision['why'], 'title');
    $joined = implode("\n", $texts);

    // ADR-0001 §2 says the constants used are *visible* wherever the number appears and §7 says every
    // suggestion displays the date behind it. A title is mouse-only, so the date must be in the text
    // (owner ruling 5a). session_cost is the stale one, read 2023-02-25.
    expect($joined)->toContain('2023-02-25')
        // The source is named the way a Trainer can act on it — the publisher — and never as a
        // repository path they cannot open. This assertion used to REQUIRE `UMAMUSUME_REFERENCE.md`,
        // which pinned the identifier into shipped copy and was the reason the guard had to be told
        // about it rather than the other way round.
        ->and($joined)->toContain('GameWith')
        ->and($joined)->not->toContain('UMAMUSUME_REFERENCE')
        ->and($joined)->toContain('Marked stale')
        ->and($joined)->toContain('The advisory line is Energy 50, read as-is from a named source.')
        // The config key a figure came from is a developer handle, so it belongs in the title.
        ->and(implode("\n", $titles))->toContain('advisor.constants.session_cost');
});

it('marks an arithmetic row Calculated and a row taken as read Confirmed, and never the other two states', function (): void {
    $run = nextDecisionRun('unity_cup', 12, 80, DECISION_SHORT_TARGETS);

    /** @var array{why: list<array{text: string, badge: string}> $decision */
    $decision = $this->get(route('runs.cockpit', $run))->assertOk()->inertiaProps('advisor.decision');

    $badges = array_column($decision['why'], 'badge');

    expect($badges)->toBeArray()
        // Estimated would claim a model this build does not have, and Unknown beside an absence was
        // retired by owner ruling 2a. Neither is legal on the advisor's axis (ruling 1c).
        ->and(array_diff($badges, ['confirmed', 'calculated']))->toBe([])
        ->and($badges)->toContain('calculated')
        ->and($badges)->toContain('confirmed');
});

it('names the deficit arithmetic in the row that prints the deficit', function (): void {
    $run = nextDecisionRun('unity_cup', 12, 80, DECISION_SHORT_TARGETS);

    /** @var array{why: list<array{text: string, badge: string, title: string}> $decision */
    $decision = $this->get(route('runs.cockpit', $run))->assertOk()->inertiaProps('advisor.decision');

    $deficit = collect($decision['why'])
        ->first(fn (array $row): bool => str_contains($row['text'], 'the deficit is 300'));

    expect($deficit)->not->toBeNull()
        ->and($deficit['badge'])->toBe('calculated')
        // The row states the two operands, so the number is reproducible from what it prints.
        ->and($deficit['text'])->toContain('reads 600')
        ->and($deficit['text'])->toContain('a target of 900')
        // The title names both sources, so the Trainer can find where each operand was recorded.
        ->and($deficit['title'])->toContain('build_target')
        ->and($deficit['title'])->toContain('turn_entries');
});

it('refuses with the not-enough-information copy when Energy has not been recorded', function (): void {
    $run = nextDecisionRun('unity_cup', 3, null, DECISION_SHORT_TARGETS);

    /** @var array{priority: string, recommended: mixed, alternatives: list<array{action: string, action_url: string, tradeoff: string}>, switch_if: list<string>, refusal: array{reason: string, missing: list<string>}} $decision */
    $decision = $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->inertiaProps('advisor.decision');

    // No band, no ranking: the engine's own refusal state, carried across rather than papered over.
    expect(array_keys($decision))->toBe(decisionKeys())
        ->and($decision['recommended'])->toBeNull()
        ->and($decision['alternatives'])->toBe([])
        ->and($decision['switch_if'])->toBe([])
        // The refusal names Energy as the missing input, in the copy for incomplete data.
        ->and($decision['refusal']['missing'])->toBe(['Energy'])
        ->and($decision['refusal']['reason'])->toStartWith('Not enough information: ')
        // The priority line states the blocker rather than inventing a priority.
        ->and($decision['priority'])->toBe('Nothing can be prioritised until this turn\'s Energy is recorded.');
});

it('refuses with the not-enough-information copy when no build target was entered', function (): void {
    $run = nextDecisionRun('unity_cup', 12, 80, DECISION_SHORT_TARGETS);
    $run->update(['build_target' => null]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('advisor.decision.recommended', null)
            ->where('advisor.decision.refusal.missing', ['build target'])
            ->where('advisor.decision.refusal.reason', fn (string $reason): bool => str_starts_with(
                $reason, 'Not enough information:')));
});

it('refuses with the no-suitable-action copy when the engine ranked and found nothing to close', function (): void {
    $run = nextDecisionRun('unity_cup', 12, 80, DECISION_MET_TARGETS);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('advisor.decision.recommended', null)
            // Nothing is missing here: the ranking ran. An empty list is the honest difference between
            // this refusal and the two above, and the component prints a different copy for it.
            ->where('advisor.decision.refusal.missing', [])
            ->where('advisor.decision.refusal.reason', fn (string $reason): bool => str_starts_with(
                $reason, 'No suitable action found:')));
});

it('carries the finale as a provenance row and not as a second verdict', function (): void {
    // The finale block opens after the 72-turn grid, so a run that has logged 70 turns is four out.
    RaceCatalogSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'year' => RaceCatalogSlot::YEAR_FINALE,
        'month' => null,
        'turn' => null,
        'slot_label' => 'Finale block',
        'title' => 'Catalogue finale row',
        'is_mandatory' => true,
    ]);

    $run = nextDecisionRun('ura_finale', 70, 80, DECISION_SHORT_TARGETS);

    $page = $this->get(route('runs.cockpit', $run))->assertOk();

    /** @var array{label: string, state: string, turns_away: int|null}|null $finaleRow */
    $finaleRow = $page->inertiaProps('advisor.finale');

    /** @var array{why: list<array{text: string, badge: string, title: string}>} $decision */
    $decision = $page->inertiaProps('advisor.decision');

    expect($finaleRow)->not->toBeNull()
        ->and($finaleRow['turns_away'])->toBe(2);

    $finale = collect($decision['why'])
        ->first(fn (array $row): bool => str_contains($row['text'], 'finale')
            || str_contains($row['text'], 'Finale'));

    expect($finale)->not->toBeNull()
        ->and($finale['badge'])->toBe('calculated')
        ->and($finale['title'])->toContain('not a recommendation');
});

it('produces the same eight-key shape for all four Global scenarios', function (): void {
    /** @var list<string> $scenarios */
    $scenarios = array_keys(config('scenarios.scenarios'));

    expect($scenarios)->toHaveCount(4);

    foreach ($scenarios as $scenario) {
        $run = nextDecisionRun($scenario, 12, 80, DECISION_SHORT_TARGETS);

        /** @var array<string, mixed> $decision */
        $decision = $this->get(route('runs.cockpit', $run))->assertOk()->inertiaProps('advisor.decision');

        expect(array_keys($decision))
            ->toBe(decisionKeys(), "the decision contract differs on [{$scenario}]");
    }
});

it('gives a fifth scenario built from one config entry the same shape with no component edit', function (): void {
    registerFifthScenario();

    $run = nextDecisionRun('fifth_scenario', 12, 80, DECISION_SHORT_TARGETS);

    /** @var array<string, mixed> $decision */
    $decision = $this->get(route('runs.cockpit', $run))->assertOk()->inertiaProps('advisor.decision');

    expect(array_keys($decision))->toBe(decisionKeys());
});

it('ranks from a recorded Energy band and refuses only when the reading itself is absent', function (): void {
    // FD-2 retires `band` from the wire shape, so the boundary it carried has to be proven from the
    // outside: a coarse band the Trainer read IS a recorded reading and the engine ranks from it, while
    // a turn that records no figure at all is the refusal. Both arrive as a null `energy` column.
    $band = nextDecisionRun('unity_cup', 1, null, DECISION_SHORT_TARGETS);
    $band->turnEntries()->reorder('turn', 'desc')->first()->update([
        'energy_state' => EnergyState::Band,
        'energy_band' => 'low',
    ]);

    /** @var array{priority: string, recommended: array{action: string}, switch_if: list<string>, refusal: mixed} $fromBand */
    $fromBand = $this->get(route('runs.cockpit', $band))->assertOk()->inertiaProps('advisor.decision');

    expect($fromBand['refusal'])->toBeNull()
        ->and($fromBand['recommended']['action'])->toBe('Rest')
        ->and($fromBand['priority'])->toBe('Recover Energy before the next session.')
        // The reason names the band it came from rather than a figure the tool does not hold.
        ->and($fromBand['recommended']['benefit'][0])->toContain('low band')
        // Rest is a recovery, not a deficit: a successor row would read "if Rest reaches its target",
        // which states something false about an action that has no target to reach.
        ->and(implode(' ', $fromBand['switch_if']))->not->toContain('If Rest reaches its target')
        ->and($fromBand['switch_if'])->toContain('Once Energy reads 50 or above, the largest deficit against your target ranks first.');

    $unknown = nextDecisionRun('unity_cup', 1, null, DECISION_SHORT_TARGETS);
    $unknown->turnEntries()->reorder('turn', 'desc')->first()->update([
        'energy_state' => EnergyState::Unknown,
    ]);

    /** @var array{recommended: mixed, refusal: array{reason: string, missing: list<string>}} $fromUnknown */
    $fromUnknown = $this->get(route('runs.cockpit', $unknown))->assertOk()->inertiaProps('advisor.decision');

    expect($fromUnknown['recommended'])->toBeNull()
        ->and($fromUnknown['refusal']['missing'])->toBe(['Energy'])
        // The Trainer's own "I did not see a level" is a reading, and the sentence the engine writes for
        // it is the one printed: not the same claim as a turn that recorded nothing.
        ->and($fromUnknown['refusal']['reason'])->toContain('Energy is not recorded for this run');
});

it('carries no score and no confidence field anywhere in the payload, at any depth', function (): void {
    $run = nextDecisionRun('unity_cup', 12, 80, DECISION_SHORT_TARGETS);

    /** @var array<string, mixed> $decision */
    $decision = $this->get(route('runs.cockpit', $run))->assertOk()->inertiaProps('advisor.decision');

    $flat = json_encode($decision, JSON_THROW_ON_ERROR);

    expect($flat)->not->toMatch('/"score"/')
        ->and($flat)->not->toMatch('/"confidence"/')
        // `band` is retired from the wire shape: Energy is either recorded, and the engine ranks, or
        // absent, and it refuses. The engine keeps its own band internally.
        ->and(array_key_exists('band', $decision))->toBeFalse()
        ->and(array_key_exists('risk', $decision))->toBeFalse();
});

it('keeps recommended and refusal mutually exclusive in every case', function (): void {
    $cases = [
        ['energy' => 80, 'targets' => DECISION_SHORT_TARGETS],
        ['energy' => 20, 'targets' => DECISION_SHORT_TARGETS],
        ['energy' => null, 'targets' => DECISION_SHORT_TARGETS],
        ['energy' => 80, 'targets' => DECISION_MET_TARGETS],
    ];

    foreach ($cases as $case) {
        $run = nextDecisionRun('unity_cup', 12, $case['energy'], $case['targets']);

        /** @var array{recommended: mixed, refusal: mixed} $decision */
        $decision = $this->get(route('runs.cockpit', $run))->assertOk()->inertiaProps('advisor.decision');

        $hasRecommendation = $decision['recommended'] !== null;
        $hasRefusal = $decision['refusal'] !== null;

        // Exactly one of the two, in every case: a refusal that ships beside a recommendation would
        // tell the Trainer the tool both ranked and could not rank.
        expect($hasRecommendation xor $hasRefusal)->toBeTrue();
    }
});

it('carries no repository identifier anywhere in the shipped advice payload', function (): void {
    $run = nextDecisionRun('unity_cup', 12, 80, DECISION_SHORT_TARGETS);

    /** @var array<string, mixed> $decision */
    $decision = $this->get(route('runs.cockpit', $run))->assertOk()->inertiaProps('advisor.decision');

    // JSON_UNESCAPED_UNICODE, and that is load-bearing rather than tidy: plain `json_encode` escapes a
    // `§` to `\u00a7`, so the section-reference pattern below could never match and the case would pass
    // however many identifiers shipped. The first draft of this case had exactly that hole, and the
    // failability proof is what found it.
    $shipped = json_encode($decision, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

    // The pattern list `UiHygieneTest` owns, applied to the PAYLOAD rather than to the source file.
    // The two disagree in exactly the case this slice fixed: the guard reads a file, and a string can
    // be assembled from `config/advisor.php` and never appear as an identifier in the file that
    // composes it. An identifier that reaches a Trainer reaches them here, whatever file it came from.
    $patterns = [
        'an ADR citation' => '/\bADR-\d+/',
        'a known-issue identifier' => '/\bKI-\d+/',
        'a decision identifier' => '/\bD-\d{3}\b/',
        'a goal identifier' => '/\bG-\d+\b/',
        'an open-question identifier' => '/\bOQ-\d+/',
        'a section reference' => '/§\d/u',
        'a document path' => '/UMAMUSUME_REFERENCE|\.md\b/',
        'a source-file path' => '/\.php\b/',
    ];

    foreach ($patterns as $what => $pattern) {
        expect(preg_match($pattern, $shipped))->toBe(0, "the advisor payload carries {$what}");
    }
});

it('names the publisher and the read date, and never a document path', function (): void {
    $run = nextDecisionRun('unity_cup', 12, 80, DECISION_SHORT_TARGETS);

    /** @var array{why: list<array{text: string}>} $decision */
    $decision = $this->get(route('runs.cockpit', $run))->assertOk()->inertiaProps('advisor.decision');

    $joined = implode("\n", array_column($decision['why'], 'text'));

    // The ` Source: …` suffix still appends (the provenance requirement is unchanged), and what it
    // appends is the publisher plus the date, not a document a Trainer would have to go and find.
    expect($joined)->toContain(' Source: ')
        ->and($joined)->toContain('read ')
        ->and($joined)->not->toContain('UMAMUSUME_REFERENCE')
        ->and($joined)->not->toContain('docs/');
});
