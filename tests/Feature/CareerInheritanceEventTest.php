<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Enums\TurnEventType;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\TurnEvent;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * SCREEN-013, the Inheritance Event (plan §8 D12). The props are asserted here; the rendered copy,
 * the form's keyboard behaviour, the 44px sweep, the predicted/observed separation and the axe scan
 * live in `tests/browser/career-inheritance-event.spec.ts` (plan §2 convention 2).
 *
 * The load-bearing cases are the absences. The "expected inheritance" has no computation behind it
 * (ADR-0020 §3), so it arrives as `N/A` with the ADR citation in its `title`. The observed section
 * reuses the existing TurnEvent mechanism with `event_type = Inheritance`. No predicted total exists
 * in the payload.
 */

function inheritanceRun(array $runState = [], int $turns = 0): TrainingRun
{
    $run = TrainingRun::factory()->create($runState + [
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->state([
            'name' => 'Rice Shower',
            'name_ja' => 'ライスシャワー',
        ]),
    ]);

    for ($turn = 1; $turn <= $turns; $turn++) {
        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => $turn]);
    }

    return $run;
}

function inheritanceLegacyPayload(): array
{
    return [
        'legacies' => [
            [
                'rank' => 3,
                'is_guest' => false,
                // Names, not slot records: the wizard writes `ancestors` as a list of names
                // (ADR-0010 §2, `StoreLegacySelectionRequest::payload()`), and the position is the slot.
                'ancestors' => ['Symboli Rudolf', 'Mejiro McQueen'],
                'sparks' => [
                    ['kind' => 'blue', 'target' => 'Speed', 'stars' => 3],
                    ['kind' => 'pink', 'target' => 'Medium', 'stars' => 2],
                    ['kind' => 'white', 'target' => 'Arc Maestro', 'stars' => 1],
                ],
            ],
            [
                'rank' => 2,
                'is_guest' => true,
                'ancestors' => ['Special Week', 'Grass Wonder'],
                'sparks' => [
                    ['kind' => 'blue', 'target' => 'Stamina', 'stars' => 2],
                    ['kind' => 'green', 'target' => 'Endless Bloom', 'stars' => 3],
                    ['kind' => 'white', 'target' => 'Lightning Strike', 'stars' => 2],
                    ['kind' => 'scenario', 'target' => 'Trackblazer', 'stars' => 1],
                ],
            ],
        ],
        'affinity' => '◎',
    ];
}

it('renders the inheritance event page for a run with a legacy configuration', function (): void {
    $run = inheritanceRun(['scenario' => 'trackblazer'], turns: 18);
    $run->update(['legacy_selection' => inheritanceLegacyPayload()]);

    $this->get(route('runs.inheritance', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/InheritanceEvent')
            ->where('run.id', $run->id)
            ->where('run.trainee', 'Rice Shower')
            ->where('run.trainee_ja', 'ライスシャワー')
            ->where('run.scenario_label', 'Trackblazer')
            ->where('run.status_label', 'Active')
            ->where('run.run_url', route('runs.show', $run))
            ->where('run.cockpit_url', route('runs.cockpit', $run))
            ->where('empty', null)
            ->has('legacy')
            ->where('legacy.affinity', '◎')
            ->has('legacy.parents', 2)
            ->where('legacy.parents.0.label', 'Parent A')
            ->where('legacy.parents.0.rank', 3)
            ->where('legacy.parents.0.is_guest', false)
            ->has('legacy.parents.0.ancestors', 2)
            ->where('legacy.parents.0.ancestors.0', 'Symboli Rudolf')
            ->where('legacy.parents.0.ancestors.1', 'Mejiro McQueen')
            ->where('legacy.parents.1.label', 'Parent B')
            ->where('legacy.parents.1.rank', 2)
            ->where('legacy.parents.1.is_guest', true)
            ->has('legacy.parents.1.ancestors', 2)
            ->where('legacy.parents.1.ancestors.0', 'Special Week')
            ->has('predicted')
            ->has('predicted.predicted_sparks')
            ->where('predicted.expected_inheritance.label', 'N/A')
            ->where('predicted.expected_inheritance.title', fn (string $title): bool => str_contains($title, 'ADR-0020')
                && str_contains($title, 'does not derive'))
            ->has('predicted.star_roll_table', 3)
            ->where('predicted.star_roll_table.0.stat_range', 'Below 600')
            ->where('predicted.star_roll_table.0.one_star', '~90%')
            ->where('predicted.star_roll_table.0.two_star', '~10%')
            ->where('predicted.star_roll_table.0.three_star', '0%')
            ->where('predicted.star_roll_table.1.stat_range', '600–1100')
            ->where('predicted.star_roll_table.2.stat_range', 'Above 1100')
            ->has('observed')
            ->where('observed.provenance', 'No inheritance events recorded yet.')
            ->has('observed.events', 0)
            ->has('milestones', 3)
            ->where('milestones.0.key', 'career_start')
            ->where('milestones.0.label', 'Career Start')
            ->where('milestones.0.glyph', '●')
            ->where('milestones.0.status', 'completed')
            ->where('milestones.1.key', 'classic_april')
            ->where('milestones.1.label', 'Classic Early April')
            ->where('milestones.2.key', 'senior_april')
            ->where('milestones.2.label', 'Senior Early April')
            ->has('write')
            ->where('write.action', route('runs.inheritance.store', $run))
            ->has('write.turns', 18)
            ->has('write.sources', 5));
});

it('renders a legacy selection whose ancestors are the name list the wizard writes', function (): void {
    // The wizard stores `ancestors` as names (`ADR-0010` §2). The page used to map each entry as an
    // array with `slot`/`name` keys and threw `array_map(): Argument #1 ($a) must be of type array`
    // on every run created through the UI. One parent names two ancestors, the other names none.
    $run = inheritanceRun(['scenario' => 'unity_cup'], turns: 18);
    $run->update(['legacy_selection' => [
        'legacies' => [
            ['rank' => null, 'is_guest' => false, 'ancestors' => ['Daiwa Scarlet', 'Seiun Sky'], 'sparks' => []],
            ['rank' => null, 'is_guest' => true, 'ancestors' => [], 'sparks' => []],
        ],
        'affinity' => null,
    ]]);

    $this->get(route('runs.inheritance', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('legacy.parents.0.ancestors', 2)
            ->where('legacy.parents.0.ancestors.0', 'Daiwa Scarlet')
            ->where('legacy.parents.0.ancestors.1', 'Seiun Sky')
            ->has('legacy.parents.1.ancestors', 0));
});

it('renders a legacy selection with a single ancestor and a null one', function (): void {
    $run = inheritanceRun(['scenario' => 'unity_cup'], turns: 18);
    $run->update(['legacy_selection' => [
        'legacies' => [
            ['rank' => null, 'is_guest' => false, 'ancestors' => ['Mejiro McQueen'], 'sparks' => []],
            ['rank' => null, 'is_guest' => false, 'ancestors' => [null, 'Taiki Shuttle'], 'sparks' => []],
        ],
        'affinity' => null,
    ]]);

    $this->get(route('runs.inheritance', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('legacy.parents.0.ancestors', 1)
            ->where('legacy.parents.0.ancestors.0', 'Mejiro McQueen')
            ->has('legacy.parents.1.ancestors', 2)
            ->where('legacy.parents.1.ancestors.0', null)
            ->where('legacy.parents.1.ancestors.1', 'Taiki Shuttle'));
});

it('renders the predicted sparks aggregated across both parents and grandparents', function (): void {
    $run = inheritanceRun(['scenario' => 'ura_finale'], turns: 18);
    $run->update(['legacy_selection' => inheritanceLegacyPayload()]);

    $this->get(route('runs.inheritance', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('predicted.predicted_sparks', 5)
            // Blue: 2 total (one from each parent)
            ->where('predicted.predicted_sparks.0.kind', 'blue')
            ->where('predicted.predicted_sparks.0.kind_label', 'Blue')
            ->where('predicted.predicted_sparks.0.total_count', 2)
            // Pink: 1 total
            ->where('predicted.predicted_sparks.1.kind', 'pink')
            ->where('predicted.predicted_sparks.1.total_count', 1)
            // Green: 1 total (only from Parent B)
            ->where('predicted.predicted_sparks.2.kind', 'green')
            ->where('predicted.predicted_sparks.2.total_count', 1)
            // White: 2 total
            ->where('predicted.predicted_sparks.3.kind', 'white')
            ->where('predicted.predicted_sparks.3.total_count', 2)
            // Scenario: 1 total
            ->where('predicted.predicted_sparks.4.kind', 'scenario')
            ->where('predicted.predicted_sparks.4.total_count', 1));
});

it('states the expected inheritance as N/A with the ADR-0020 §3 citation', function (): void {
    $run = inheritanceRun(['scenario' => 'unity_cup'], turns: 18);
    $run->update(['legacy_selection' => inheritanceLegacyPayload()]);

    $this->get(route('runs.inheritance', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('predicted.expected_inheritance.label', 'N/A')
            ->where('predicted.expected_inheritance.title', fn (string $title): bool => str_contains($title, 'ADR-0020') &&
                str_contains($title, '§3') &&
                str_contains($title, 'does not derive')
            ));
});

it('shows the sourced star-roll table as Estimated, never summed', function (): void {
    $run = inheritanceRun(['scenario' => 'trackblazer'], turns: 18);
    $run->update(['legacy_selection' => inheritanceLegacyPayload()]);

    $this->get(route('runs.inheritance', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('predicted.star_roll_table', 3)
            ->where('predicted.star_roll_table.0.stat_range', 'Below 600')
            ->where('predicted.star_roll_table.1.stat_range', '600–1100')
            ->where('predicted.star_roll_table.2.stat_range', 'Above 1100')
            // Probabilities, not guarantees — the table uses ~ and % but no totals column
            ->where('predicted.star_roll_table.0.one_star', '~90%')
            ->where('predicted.star_roll_table.0.three_star', '0%'));
});

it('renders the three milestones with correct glyphs based on turn position', function (): void {
    // Turn 18 = Junior Year, turn 18 is Late September (turn 13 = Early July, so turn 18 = Late September)
    // Career Start (year 1, half 1) = completed
    // Classic Early April (year 2, half 1) = upcoming (turn 25 would be Early April)
    // Senior Early April (year 3, half 1) = upcoming
    $run = inheritanceRun(['scenario' => 'ura_finale'], turns: 18);
    $run->update(['legacy_selection' => inheritanceLegacyPayload()]);

    $this->get(route('runs.inheritance', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('milestones.0.glyph', '●')
            ->where('milestones.0.status', 'completed')
            ->where('milestones.1.glyph', '○')
            ->where('milestones.1.status', 'upcoming')
            ->where('milestones.2.glyph', '○')
            ->where('milestones.2.status', 'upcoming'));
});

it('marks Classic Early April as current when the run has reached that turn', function (): void {
    // Turn 30 logged -> next turn is 31 = Classic Early April
    $run = inheritanceRun(['scenario' => 'ura_finale'], turns: 30);
    $run->update(['legacy_selection' => inheritanceLegacyPayload()]);

    $this->get(route('runs.inheritance', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('milestones.0.status', 'completed')
            ->where('milestones.1.glyph', '◉')
            ->where('milestones.1.status', 'current')
            ->where('milestones.2.glyph', '○')
            ->where('milestones.2.status', 'upcoming'));
});

it('marks Senior Early April as completed when the run has passed that turn', function (): void {
    // Turn 56 = Senior Year, Late April (past turn 55 = Early April)
    $run = inheritanceRun(['scenario' => 'ura_finale'], turns: 56);
    $run->update(['legacy_selection' => inheritanceLegacyPayload()]);

    $this->get(route('runs.inheritance', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('milestones.0.status', 'completed')
            ->where('milestones.1.status', 'completed')
            ->where('milestones.2.glyph', '●')
            ->where('milestones.2.status', 'completed'));
});

it('shows observed inheritance events recorded via TurnEvent', function (): void {
    $run = inheritanceRun(['scenario' => 'ura_finale'], turns: 30);
    $run->update(['legacy_selection' => inheritanceLegacyPayload()]);

    TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => 1,
        'event_type' => TurnEventType::Inheritance,
        'source_name' => 'Career Start',
        'choice_label' => 'Blue Speed ★★★, Pink Medium ★★',
        'deltas' => ['blue_speed' => 3, 'pink_medium' => 2],
        'origin_note' => 'First inheritance event',
    ]);

    TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => 25,
        'event_type' => TurnEventType::Inheritance,
        'source_name' => 'Classic Early April',
        'choice_label' => 'Green Endless Bloom ★★★',
        'deltas' => ['green_unique' => 3],
        'origin_note' => 'Golden event!',
    ]);

    $this->get(route('runs.inheritance', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('observed.events', 2)
            ->where('observed.events.0.turn', 1)
            ->where('observed.events.0.source_name', 'Career Start')
            ->where('observed.events.0.choice_label', 'Blue Speed ★★★, Pink Medium ★★')
            ->where('observed.events.0.deltas.blue_speed', 3)
            ->where('observed.events.0.origin_note', 'First inheritance event')
            ->where('observed.events.1.turn', 25)
            ->where('observed.events.1.source_name', 'Classic Early April')
            ->where('observed.events.1.choice_label', 'Green Endless Bloom ★★★')
            ->where('observed.provenance', 'Confirmed — entered by the Trainer'));
});

it('names the absence when no legacy configuration exists', function (): void {
    $run = inheritanceRun(['scenario' => 'trackblazer'], turns: 12);
    // No legacy_selection written

    $this->get(route('runs.inheritance', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('empty', fn (string $empty): bool => str_contains($empty, 'No Legacy configuration') &&
                str_contains($empty, 'Legacy Select') &&
                str_contains($empty, 'Legacy Lab')
            )
            ->where('legacy', null)
            ->where('predicted.predicted_sparks', [])
            ->where('observed.provenance', 'No inheritance events recorded yet.'));
});

it('posts a new observed inheritance event through the existing TurnEvent mechanism', function (): void {
    $run = inheritanceRun(['scenario' => 'ura_finale'], turns: 26);
    $run->update(['legacy_selection' => inheritanceLegacyPayload()]);

    $this->post(route('runs.inheritance.store', $run), [
        'turn' => 25,
        'source_name' => 'Classic Early April',
        'choice_label' => 'Blue Speed ★★★, Pink Mile ★★',
        'deltas' => ['blue_speed' => 3, 'pink_mile' => 2],
        'origin_note' => 'Rolled at 2:00 PM',
    ])
        ->assertRedirect(route('runs.inheritance', $run))
        ->assertSessionHas('status', 'Inheritance event recorded.');

    expect(TurnEvent::query()
        ->where('training_run_id', $run->id)
        ->where('event_type', TurnEventType::Inheritance)
        ->count())->toBe(1);

    $event = TurnEvent::query()
        ->where('training_run_id', $run->id)
        ->where('event_type', TurnEventType::Inheritance)
        ->firstOrFail();

    expect($event->turn)->toBe(25)
        ->and($event->source_name)->toBe('Classic Early April')
        ->and($event->choice_label)->toBe('Blue Speed ★★★, Pink Mile ★★')
        ->and($event->deltas)->toBe(['blue_speed' => 3, 'pink_mile' => 2])
        ->and($event->origin_note)->toBe('Rolled at 2:00 PM');
});

it('validates the observed event form', function (): void {
    $run = inheritanceRun(['scenario' => 'ura_finale'], turns: 26);
    $run->update(['legacy_selection' => inheritanceLegacyPayload()]);

    $this->post(route('runs.inheritance.store', $run), [
        'turn' => 'invalid',
        'source_name' => '',
    ])
        ->assertInvalid(['turn', 'source_name']);
});

it('carries no predicted total or numeric confidence anywhere in the payload', function (): void {
    $run = inheritanceRun(['scenario' => 'trackblazer'], turns: 18);
    $run->update(['legacy_selection' => inheritanceLegacyPayload()]);

    $this->get(route('runs.inheritance', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // The payload has no 'total', 'score', 'confidence', 'probability', or 'win' keys
            ->where('predicted', fn ($p): bool => ! isset($p['total']) &&
                ! isset($p['score']) &&
                ! isset($p['confidence']) &&
                ! isset($p['probability']) &&
                ! isset($p['win'])
            )
            ->where('observed', fn ($o): bool => ! isset($o['total']) &&
                ! isset($o['score']) &&
                ! isset($o['confidence'])
            ));
});
