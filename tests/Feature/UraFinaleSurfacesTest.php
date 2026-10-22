<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\RaceCatalogSlot;
use App\Models\Skill;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * G3: the URA Finale decision surfaces (`docs/URA-Finale-plan.md` §8, §13).
 *
 * **The scope is the owner's ruling of 2026-10-11: config-placed gates, display-only duel.** Ruling
 * D-5 (`App\Enums\TurnChoice`) still says the finale's duels are display-only in this build, so no
 * case here asks for a stored duel, a column, or a write route. What the tests hold is the read path:
 * the three fan gates live in `config/scenarios.php` and nowhere in a component, and everything the
 * sources do not publish reaches the screen as an empty value with its reason.
 *
 * **A gate publishes two figures and no column names this trainee's track**, so the state is decided
 * on the bounds: at or above both, below both, and between them there is no state to claim. The
 * between case is a test, not a comment, because resolving it by picking a track is the one move that
 * would make the panel wrong rather than merely incomplete.
 *
 * The window the pre-final review opens in is `TrainerAdvisor::FINALE_TURNS_AHEAD`, the advisor's own
 * constant, so the fixtures set turns against turn 73, one past the 72-turn grid, rather than a
 * number this file invents.
 */

function g3Run(int $turn = 1, ?int $fans = null, int $speed = 900): TrainingRun
{
    $run = TrainingRun::factory()->create([
        'scenario' => 'ura_finale',
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->state([
            'name' => 'Rice Shower',
            'name_ja' => 'ライスシャワー',
        ])->create()->id,
    ]);

    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => $turn,
        'fans' => $fans,
        'energy' => 60,
        'speed' => $speed,
        'stamina' => 500,
    ]);

    return $run;
}

/**
 * The finale block, which `FinaleReader` needs to exist before any gate can be called missed.
 */
function g3Finale(): RaceCatalogSlot
{
    return RaceCatalogSlot::factory()->scenarioFinal('ura_finale')->create();
}

/**
 * The scenario section as plain arrays, so a structural assertion reads the shape rather than
 * fighting the `Collection`-at-each-depth rule `assertInertia` applies inside a closure.
 *
 * @return array<string, mixed>
 */
function g3Scenario(TestResponse $response): array
{
    /** @var array<string, mixed> $scenario */
    $scenario = [];

    $response->assertOk()->assertInertia(function (Assert $page) use (&$scenario): void {
        /** @var array<string, mixed> $props */
        $props = $page->toArray()['props'];
        $scenario = (array) $props['scenario'];
    });

    return $scenario;
}

it('places the three fan gates in the scenario entry and draws each with both published figures', function (): void {
    $run = g3Run();

    /** @var list<array<string, mixed>> $rows */
    $rows = (array) g3Scenario($this->get(route('runs.cockpit', $run)))['milestones'];

    expect($rows)->toHaveCount(3)
        ->and(array_column($rows, 'label'))->toBe(['Valentine\'s Day', 'Fan Fest', 'Holiday Season'])
        ->and($rows[0]['figures'])->toBe('60,000 Turf / 40,000 Dirt')
        ->and($rows[1]['figures'])->toBe('70,000 Turf / 60,000 Dirt')
        ->and($rows[2]['figures'])->toBe('120,000 Turf / 80,000 Dirt')
        // Both figures travel together because nothing says which one binds this trainee.
        ->and($rows[0]['figures_badge'])->toBe('confirmed')
        ->and($rows[1]['condition'])->toBe('a three-bar friendship with Director Akikawa')
        ->and($rows[0]['consequence'])->toBe('Unique Skill level up')
        // The guides date these by month and publish no turn, so the row states no turn.
        ->and($rows[0]['due_turn'])->toBeNull()
        ->and($rows[0]['due_label'])->toBe('Senior Year · early February')
        ->and($rows[0]['due_title'])->toContain('no source publishes the career turn')
        ->and($rows[0]['decision_url'])->toBe(route('runs.races.decision', $run));
});

it('draws no milestone rows and no duel section for a scenario that declares neither', function (): void {
    foreach (['unity_cup', 'trackblazer', 'our_grand_concert'] as $key) {
        $run = TrainingRun::factory()->create(['scenario' => $key, 'status' => RunStatus::Active]);

        $scenario = g3Scenario($this->get(route('runs.cockpit', $run)));

        expect($scenario['milestones'])->toBe([])
            ->and($scenario['duel'])->toBeNull()
            ->and($scenario['pre_final'])->toBeNull();
    }
});

it('marks a gate recorded only on the bound that clears both figures, without naming a track', function (): void {
    $run = g3Run(fans: 130000);

    /** @var list<array<string, mixed>> $rows */
    $rows = (array) g3Scenario($this->get(route('runs.cockpit', $run)))['milestones'];

    expect(array_column($rows, 'state'))->toBe(['completed', 'completed', 'completed'])
        ->and($rows[0]['title'])->toContain('at or above both published figures');
});

it('leaves a gate between the two figures as no state at all, naming the missing track column', function (): void {
    // 50,000 clears Valentine's Dirt figure and not its Turf figure: the state depends on a track no
    // column records, so the honest answer is that there is none to print.
    $run = g3Run(fans: 50000);

    /** @var list<array<string, mixed>> $rows */
    $rows = (array) g3Scenario($this->get(route('runs.cockpit', $run)))['milestones'];

    expect($rows[0]['state'])->toBe('unknown')
        ->and($rows[0]['title'])->toContain('whether this trainee races on turf or dirt')
        // The other two sit below both of their figures, which is a state the bounds do settle.
        ->and($rows[1]['state'])->toBe('upcoming')
        ->and($rows[2]['state'])->toBe('upcoming');
});

it('marks a gate missed once the finale block has arrived and the reading is below both figures', function (): void {
    g3Finale();

    // Turn 72 fills the 24-turn Senior year, so the finale block is behind the run and a gate still
    // short of both figures cannot be cleared by the turns that remain.
    $run = g3Run(turn: 72, fans: 10000);

    /** @var list<array<string, mixed>> $rows */
    $rows = (array) g3Scenario($this->get(route('runs.cockpit', $run)))['milestones'];

    expect(array_column($rows, 'state'))->toBe(['missed', 'missed', 'missed'])
        ->and($rows[0]['title'])->toContain('The finale block has arrived');
});

it('states an unrecorded fan reading as upcoming with its reason, never as a zero', function (): void {
    $run = g3Run();

    /** @var list<array<string, mixed>> $rows */
    $rows = (array) g3Scenario($this->get(route('runs.cockpit', $run)))['milestones'];

    expect(array_column($rows, 'state'))->toBe(['upcoming', 'upcoming', 'upcoming'])
        ->and($rows[0]['title'])->toContain('No turn entry for this run records a fan count');
});

it('carries the published duel rules with their sources and prints no win chance anywhere', function (): void {
    $run = g3Run();

    /** @var array<string, mixed> $duel */
    $duel = (array) g3Scenario($this->get(route('runs.cockpit', $run)))['duel'];
    $rules = collect($duel['rules']);

    expect($rules->firstWhere('label', 'Win Skill Points')['value'])->toBe('30')
        ->and($rules->firstWhere('label', 'Lose Skill Points')['value'])->toBe('15')
        ->and($rules->firstWhere('label', 'Escalation')['value'])->toBe('after 6 wins')
        ->and($rules->firstWhere('label', 'Powered-up reward')['value'])
        ->toBe('Past My Limits, 200 or more Skill Points')
        // The three figures no source publishes stay empty, each with its own sentence, and none
        // carries a badge: a badge qualifies a displayed value (owner ruling 2a, 2026-10-09), so an
        // absence travels as `badge => null` with its reason, never as `unknown`.
        ->and($rules->firstWhere('label', 'Win chance per option')['value'])->toBeNull()
        ->and($rules->firstWhere('label', 'Win chance per option')['badge'])->toBeNull()
        ->and($rules->firstWhere('label', 'Enhanced spark rate')['value'])->toBeNull()
        ->and($rules->firstWhere('label', 'Enhanced spark rate')['badge'])->toBeNull()
        ->and($rules->firstWhere('label', 'Per-stat gain of a win')['value'])->toBeNull()
        ->and($rules->firstWhere('label', 'Per-stat gain of a win')['badge'])->toBeNull()
        // No percentage reaches the page on any branch of this panel.
        ->and((string) json_encode($duel))->not->toMatch('/%|chance of \d/');
});

it('reads the Racing Spirit hint names off the catalogue and marks the ones it cannot match', function (): void {
    Skill::factory()->create(['name' => 'Racing Spirit: Speed', 'name_is_client' => true, 'sp_cost' => 150]);
    Skill::factory()->create(['name' => 'Racing Spirit: Guts', 'name_is_client' => true, 'sp_cost' => 150]);

    $run = g3Run();

    /** @var list<array<string, mixed>> $hints */
    $hints = (array) g3Scenario($this->get(route('runs.cockpit', $run)))['duel']['hints'];

    expect($hints)->toHaveCount(5)
        ->and(array_column($hints, 'stat'))->toBe(['Speed', 'Stamina', 'Power', 'Guts', 'Wit'])
        // The mood skill is in the catalogue and belongs to no stat a Trainer dueled on, so it is not
        // one of the five rows.
        ->and(array_column($hints, 'name'))->not->toContain('Racing Spirit: Mood')
        ->and($hints[0]['sp_cost'])->toBe('150')
        ->and($hints[0]['badge'])->toBe('confirmed')
        ->and($hints[1]['sp_cost'])->toBeNull()
        // No catalogue row means no figure to qualify, so the row carries no badge (ruling 2a).
        ->and($hints[1]['badge'])->toBeNull()
        ->and($hints[1]['title'])->toContain('holds no client-named row');
});

it('states this run\'s duel record as named absences rather than zeros', function (): void {
    $run = g3Run();

    /** @var list<array<string, mixed>> $runRows */
    $runRows = (array) g3Scenario($this->get(route('runs.cockpit', $run)))['duel']['run'];

    expect($runRows)->toHaveCount(4)
        ->and(array_column($runRows, 'value'))->toBe([null, null, null, null])
        // A named absence carries no badge (owner ruling 2a): the reason title is the whole statement.
        ->and(array_column($runRows, 'badge'))->toBe([null, null, null, null])
        ->and($runRows[0]['title'])->toContain('No table stores a duel')
        ->and($runRows[3]['title'])->toContain('nothing this run records names them');
});

it('opens the pre-final review only inside the window the advisor already owns', function (): void {
    g3Finale();

    // Far from the block: the section is absent, not empty.
    $far = g3Run(turn: 10);

    expect(g3Scenario($this->get(route('runs.cockpit', $far)))['pre_final'])->toBeNull();

    // Two turns out, inside the five the advisor looks ahead.
    $near = g3Run(turn: 70, fans: 50000);

    /** @var array<string, mixed> $preFinal */
    $preFinal = (array) g3Scenario($this->get(route('runs.cockpit', $near)))['pre_final'];

    expect($preFinal['window']['state'])->toBe('upcoming')
        ->and($preFinal['window']['turns_away'])->toBe(2)
        ->and($preFinal['window']['turns_ahead'])->toBe(5)
        ->and($preFinal['window']['title'])->toContain('TrainerAdvisor::FINALE_TURNS_AHEAD');

    /** @var list<array<string, mixed>> $recorded */
    $recorded = (array) $preFinal['recorded'];

    expect(array_column($recorded, 'label'))->toBe(['Speed', 'Stamina', 'Power', 'Guts', 'Wit'])
        ->and($recorded[0]['value'])->toBe('900')
        ->and($recorded[0]['cap'])->toBe('1,400')
        // A logged turn carries all five figures (`turn_entries.speed` and friends are NOT NULL), so
        // every row reads confirmed against the published cap. The empty branch in the component is
        // the type's, for a turn entry that does not exist, and no in-window run reaches it.
        ->and(array_column($recorded, 'badge'))->toBe(['confirmed', 'confirmed', 'confirmed', 'confirmed', 'confirmed'])
        ->and($recorded[2]['value'])->not->toBeNull()
        // The target keeps the source's hedge rather than becoming a requirement.
        ->and($preFinal['speed_target']['phrasing'])->toBe('around 800 Speed, or an A rank')
        ->and($preFinal['speed_target']['short_by'])->toBeNull()
        ->and($preFinal['stamina_floors']['rows'])->toBe([
            ['distance' => 'Sprint', 'floor' => '400'],
            ['distance' => 'Mile', 'floor' => '500'],
            ['distance' => 'Medium', 'floor' => '600'],
            ['distance' => 'Long', 'floor' => '700'],
        ])
        // Which floor applies is the finale distance, and nothing in this tool computes it. The row
        // is a pure absence, so it carries no badge key at all (owner ruling 2a).
        ->and($preFinal['distance']['value'])->toBeNull()
        ->and($preFinal['distance'])->not->toHaveKey('badge')
        ->and($preFinal['distance']['title'])->toContain('no column or reader in this tool computes it');
});

it('reports a short Speed reading against the approximation rather than against a rule', function (): void {
    g3Finale();
    $run = g3Run(turn: 70, speed: 620);

    /** @var array<string, mixed> $preFinal */
    $preFinal = (array) g3Scenario($this->get(route('runs.cockpit', $run)))['pre_final'];

    expect($preFinal['speed_target']['value'])->toBe('620')
        ->and($preFinal['speed_target']['short_by'])->toBe('180')
        ->and($preFinal['speed_target']['title'])->toContain('as an approximation');
});

it('stops claiming that no source publishes a Happy Meek reward table', function (): void {
    $run = g3Run();

    /** @var list<array<string, mixed>> $objectives */
    $objectives = (array) g3Scenario($this->get(route('runs.cockpit', $run)))['objectives'];

    $row = collect(collect($objectives)->keyBy('group')->get('Happy Meek')['rows'])
        ->firstWhere('label', 'Potential reward');

    expect($row)->not->toBeNull()
        ->and($row['absence'])->toStartWith('No column stores a reward for this run')
        ->and($row['absence'])->not->toContain('publishes a reward table');
});

it('keeps the gate figures and the win-chance vocabulary out of the components that draw them', function (): void {
    $surfaces = [
        resource_path('js/components/scenario/UraMilestoneTracker.vue'),
        resource_path('js/components/scenario/UraDuelPanel.vue'),
        resource_path('js/components/scenario/UraPreFinalReview.vue'),
        // Phase 7A.1: UraPanel was archived; its extracted panels are checked individually above.
    ];

    foreach ($surfaces as $file) {
        $source = (string) file_get_contents($file);

        // The numbers live in config/scenarios.php, which is where the matrix says they belong, and a
        // scenario name never enters a component (§7).
        expect($source)->not->toMatch('/60,?000|70,000|120,?000/')
            ->and($source)->not->toMatch('/\d+\s*%/')
            ->and(strtolower($source))->not->toContain('recommended stat')
            ->and($source)->not->toMatch('/ura_finale|URA Finale/');
    }
});

it('holds the fan gates and the three unpublished duel figures in the matrix alone', function (): void {
    /** @var list<array<string, mixed>> $gates */
    $gates = (array) config('scenarios.scenarios.ura_finale.fan_gates');

    expect($gates)->toHaveCount(3)
        ->and($gates[2]['turf'])->toBe(120000)
        ->and($gates[2]['dirt'])->toBe(80000)
        ->and(config('scenarios.scenarios.unity_cup.fan_gates'))->toBeNull()
        ->and(config('scenarios.scenarios.ura_finale.duel.stat_gain_values'))->toBeNull()
        ->and(config('scenarios.scenarios.ura_finale.duel.win_chance'))->toBeNull()
        ->and(config('scenarios.scenarios.ura_finale.duel.spark_rate'))->toBeNull();
});

it('keeps the win-chance vocabulary and the unknown badge out of the scenario matrix', function (): void {
    /*
     * FQ-12's deletion of the two URA panels retires the component-source guards at
     * :301-319 and :345-364. This is the guard that remains, on the file the matrix says
     * owns the figures.
     *
     * Two of the retired patterns are deliberately not carried across, because carrying
     * them would assert the opposite of what the matrix is for:
     *
     *  - `/60,?000|70,000|120,?000/` -- those gate figures ARE the matrix's own values;
     *    the fan-gate test above asserts them here on purpose ("The numbers live in
     *    config/scenarios.php, which is where the matrix says they belong", :312-313).
     *  - `/ura_finale|URA Finale/` -- `ura_finale` is this file's own config key.
     *
     * What survives is what a config can still get wrong: the win-chance vocabulary
     * FQ-12 keeps out of the tree, and the `state="unknown"` badge the absence contract
     * retired (owner ruling 2a).
     */
    $source = (string) file_get_contents(config_path('scenarios.php'));
    $lower = strtolower($source);

    expect($lower)->not->toContain('win chance')
        ->and($lower)->not->toContain('win probability')
        ->and($lower)->not->toContain('likelihood')
        ->and($lower)->not->toContain('recommended stat')
        ->and($source)->not->toContain('state="unknown"');
});

it('hands the three surfaces to every cockpit as part of the section contract', function (): void {
    $run = g3Run();

    $this->get(route('runs.cockpit', $run))
        ->assertInertia(fn (Assert $page) => $page->where('scenario', function (Collection $section): bool {
            expect(array_keys($section->all()))->toContain('milestones', 'duel', 'pre_final');

            return true;
        }));
});

it('emits no unknown provenance badge on any absence payload or absence surface', function (): void {
    // The contract, at the two places a regression can enter: the controller that shapes the
    // payloads and the components that draw them. An absent value is `badge => null` with its
    // reason (owner ruling 2a); `unknown` survives only as the Database reference views'
    // citation-strength mark for an available row, which `config/reference.php` still carries.
    $controller = (string) file_get_contents(app_path('Http/Controllers/Career/CockpitController.php'));

    expect(substr_count($controller, "'badge' => 'unknown'"))->toBe(0)
        ->and((string) file_get_contents(resource_path('js/components/scenario/UraDuelPanel.vue')))
        ->not->toContain('state="unknown"')
        ->and((string) file_get_contents(resource_path('js/components/scenario/UraPreFinalReview.vue')))
        ->not->toContain('state="unknown"')
        ->and((string) file_get_contents(resource_path('js/components/scenario/UnityCupTournamentReadiness.vue')))
        ->not->toContain('state="unknown"')
        // The reference axis keeps its one unverified row: citation strength on an available result
        // is a different claim from a badge beside an absence, and only the latter was retired.
        ->and(array_column(config('reference.events.rows'), 'state'))->toContain('unknown')
        ->and(array_column(config('reference.shop_items.rows'), 'state'))->not->toContain('unknown')
        ->and(array_column(config('reference.sparks.categories'), 'state'))->not->toContain('unknown');
});
