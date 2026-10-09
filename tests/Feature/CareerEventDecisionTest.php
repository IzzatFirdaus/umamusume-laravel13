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
 * SCREEN-012, the Event Decision (plan §8 D11). The props are asserted here; the rendered copy,
 * the keyboard behaviour, the 44px sweep and the axe scan live in
 * `tests/browser/career-event-decision.spec.ts` (plan §2 convention 2).
 *
 * The load-bearing absences are structural: the advisor holds no event advice, so its prop is a
 * refusal with a reason line and no recommendation; a choice whose row carries no recorded
 * outcome is flagged incomplete; and no score/confidence/total/probability key reaches the page.
 */

function eventDecisionRun(array $runState = [], int $turns = 0): TrainingRun
{
    $run = TrainingRun::factory()->create($runState + [
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->state([
            'name' => 'Rice Shower',
            'name_ja' => 'ライスシャワー',
        ])->create()->id,
    ]);

    for ($turn = 1; $turn <= $turns; $turn++) {
        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => $turn]);
    }

    return $run;
}

it('renders the event decision page with run, state, events, advisor and write sections', function (): void {
    $run = eventDecisionRun(['scenario' => 'trackblazer'], turns: 2);

    $this->get(route('runs.events.decision', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/EventDecision')
            ->where('run.id', $run->id)
            ->where('run.trainee', 'Rice Shower')
            ->where('run.trainee_ja', 'ライスシャワー')
            ->where('run.scenario_label', 'Trackblazer')
            ->where('run.status_label', 'Active')
            ->where('run.run_url', route('runs.cockpit', $run))
            ->where('run.cockpit_url', route('runs.cockpit', $run))
            ->has('state.stats', 5)
            ->where('state.stats.0.key', 'Speed')
            ->where('state.stats.0.cap', fn (int $cap): bool => $cap > 0)
            ->where('state.meta', fn ($meta): bool => count($meta) === 4)
            ->has('events', 0)
            ->has('known', 0)
            ->where('advisor.recommendation', null)
            ->where('advisor.band', null)
            ->where('advisor.reasons', fn ($reasons): bool => count($reasons) === 1
                && str_contains($reasons[0], 'no event advice'))
            ->where('advisor.alternative', null)
            ->where('advisor.risk', null)
            ->has('write.turns', 2)
            ->has('write.sources', 4)
            ->where('write.sources.0.value', 'Character')
            ->where('write.sources.1.value', 'SupportCard')
            ->where('write.sources.2.value', 'Group')
            ->where('write.sources.3.value', 'Scenario')
            ->where('write.action', route('runs.events.store', $run))
            ->where('empty', fn (?string $empty): bool => is_string($empty) && str_contains($empty, 'No event choices'))
        );
});

it('groups recorded choices by event name with per-choice outcome flags', function (): void {
    $run = eventDecisionRun(['scenario' => 'ura_finale'], turns: 4);

    TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => 1,
        'event_type' => TurnEventType::Character,
        'source_name' => 'Go Beyond',
        'choice_index' => 0,
        'choice_label' => 'Option A',
        'origin_note' => '+20 Speed, +10 SP',
    ]);

    TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => 2,
        'event_type' => TurnEventType::Character,
        'source_name' => 'Go Beyond',
        'choice_index' => 1,
        'choice_label' => 'Option B',
    ]);

    TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => 3,
        'event_type' => TurnEventType::SupportCard,
        'source_name' => 'Kitasan Black',
        'choice_label' => 'Encourage',
        'origin_note' => 'Bond up',
        'support_card_name' => 'Kitasan Black',
    ]);

    $this->get(route('runs.events.decision', $run))
        ->assertInertia(fn (Assert $page) => $page
            ->has('events', 3)
            ->where('events.0.event_type', 'SupportCard')
            ->where('events.0.source_label', 'Support Card')
            ->where('events.0.source_name', 'Kitasan Black')
            ->where('events.0.choice_label', 'Encourage')
            ->where('events.0.outcome_recorded', true)
            ->where('events.1.source_name', 'Go Beyond')
            ->where('events.1.outcome_recorded', false)
            ->where('events.2.outcome_recorded', true)
            ->has('known', 2)
            ->where('known.0.source_name', 'Go Beyond')
            ->has('known.0.choices', 2)
            ->where('known.0.choices.0.label', 'Option A')
            ->where('known.0.choices.0.outcome_recorded', true)
            ->where('known.0.choices.0.outcome', '+20 Speed, +10 SP')
            ->where('known.0.choices.1.label', 'Option B')
            ->where('known.0.choices.1.outcome_recorded', false)
            ->where('known.0.choices.1.outcome', null)
            ->where('known.1.source_name', 'Kitasan Black')
            ->has('known.1.choices', 1)
            ->where('empty', null)
        );
});

it('flags a choice as outcome-incomplete when neither a note nor deltas were recorded', function (): void {
    $run = eventDecisionRun(['scenario' => 'unity_cup'], turns: 2);

    TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => 1,
        'event_type' => TurnEventType::Scenario,
        'source_name' => 'Team Meeting',
        'choice_label' => 'Drill',
    ]);

    $this->get(route('runs.events.decision', $run))
        ->assertInertia(fn (Assert $page) => $page
            ->where('events.0.choice_label', 'Drill')
            ->where('events.0.outcome_recorded', false)
            ->where('known.0.choices.0.outcome_recorded', false)
        );
});

it('excludes failure and inheritance rows from the event history', function (): void {
    $run = eventDecisionRun(['scenario' => 'trackblazer'], turns: 2);

    TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => 1,
        'event_type' => TurnEventType::Failure,
        'source_name' => 'Training failed',
        'deltas' => ['penalty_kind' => 'energy', 'recorded' => true],
    ]);

    TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => 1,
        'event_type' => TurnEventType::Inheritance,
        'source_name' => 'Classic April',
        'choice_label' => 'Blue Speed ★★★',
    ]);

    TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => 2,
        'event_type' => TurnEventType::Group,
        'source_name' => 'Group Training',
        'choice_label' => 'Join',
        'origin_note' => 'Fans +100',
    ]);

    $this->get(route('runs.events.decision', $run))
        ->assertInertia(fn (Assert $page) => $page
            ->has('events', 1)
            ->where('events.0.event_type', 'Group')
            ->has('known', 1)
        );
});

it('stores a recorded choice through the TurnEvent mechanism with the originating note', function (): void {
    $run = eventDecisionRun(['scenario' => 'ura_finale'], turns: 3);

    $this->post(route('runs.events.store', $run), [
        'turn' => 2,
        'event_type' => 'Character',
        'source_name' => 'Go Beyond',
        'choice_index' => 1,
        'choice_label' => 'Option B',
        'support_card_name' => null,
        'bond_delta' => null,
        'origin_note' => '+30 Energy, +10 Mood',
    ])
        ->assertRedirect(route('runs.events.decision', $run))
        ->assertSessionHas('status', 'Event choice recorded.');

    $event = TurnEvent::query()
        ->where('training_run_id', $run->id)
        ->firstOrFail();

    expect($event->event_type)->toBe(TurnEventType::Character)
        ->and($event->source_name)->toBe('Go Beyond')
        ->and($event->choice_index)->toBe(1)
        ->and($event->choice_label)->toBe('Option B')
        ->and($event->origin_note)->toBe('+30 Energy, +10 Mood')
        ->and($event->turn)->toBe(2);
});

it('rejects an unknown source type in the event write', function (): void {
    $run = eventDecisionRun(['scenario' => 'ura_finale'], turns: 1);

    $this->post(route('runs.events.store', $run), [
        'turn' => 1,
        'event_type' => 'Random',
        'source_name' => 'Go Beyond',
        'choice_label' => 'Option A',
    ])->assertInvalid(['event_type']);
});

it('requires a turn, an event name and a choice label', function (): void {
    $run = eventDecisionRun(['scenario' => 'ura_finale'], turns: 1);

    $this->post(route('runs.events.store', $run), [
        'event_type' => 'Character',
        'source_name' => '',
        'choice_label' => '',
    ])->assertInvalid(['turn', 'source_name', 'choice_label']);
});

it('carries no score, confidence, total or probability key anywhere in the props', function (): void {
    $run = eventDecisionRun(['scenario' => 'trackblazer'], turns: 1);

    $this->get(route('runs.events.decision', $run))
        ->assertInertia(fn (Assert $page) => $page
            ->where('advisor', fn ($a): bool => ! isset($a['score']) && ! isset($a['confidence']) && ! isset($a['total']))
            ->where('events', fn ($events): bool => collect($events)->every(fn ($e) => ! isset($e['score']) && ! isset($e['confidence']) && ! isset($e['total']) && ! isset($e['probability'])))
            ->where('known', fn ($known): bool => collect($known)->every(fn ($k) => ! isset($k['score']) && ! isset($k['confidence']) && ! isset($k['total']) && ! isset($k['probability'])))
        );
});

it('reports a no-turn run in the state section without a zero stat', function (): void {
    $run = eventDecisionRun(['scenario' => 'ura_finale'], turns: 0);

    $this->get(route('runs.events.decision', $run))
        ->assertInertia(fn (Assert $page) => $page
            ->where('state.stats.0.current', null)
            ->where('state.stats.0.target', null)
            ->where('state.meta.0.value', null)
            ->has('write.turns', 0)
        );
});
