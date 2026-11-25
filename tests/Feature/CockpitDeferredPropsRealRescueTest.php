<?php

declare(strict_types=1);

use App\Enums\PerformanceType;
use App\Models\TrainingRun;
use App\Models\TurnEvent;
use App\Models\Umamusume;
use Inertia\Support\Header;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Real rescue path for the deferred Cockpit Performance prop (UI/UX Phase 5 §5.3).
 *
 * The browser spec exercises the client's rescue slot by injecting Inertia's own
 * `rescuedProps` marker into a synthetic response. That verifies the UI; it does not
 * verify the server's half - that the resolver catches a thrown closure, reports it, and
 * marks the prop rescued rather than 500ing. This file does the server half.
 *
 * **No permanent fault injection.** The test subclasses nothing in production, patches
 * no method, and adds no switch to the app. It stores a turn event whose `deltas` row
 * the payload class rejects - the same shape a hand-edited row or a malformed import
 * already produces - and lets `CockpitController::performanceSection()` (the real
 * protected method the deferred prop closes over) hit it through the resolver's own
 * catch block.
 */

function realRescueRun(): TrainingRun
{
    return TrainingRun::factory()->create([
        'scenario' => 'our_grand_concert',
        'umamusume_id' => Umamusume::factory()->create()->id,
    ]);
}

/**
 * Store a turn event while bypassing the model's `saving` normalizer, which would
 * otherwise reject the malformed `deltas` row before the read under test can see it.
 */
function storeMalformedPerformanceEvent(TrainingRun $run): void
{
    TurnEvent::withoutEvents(static fn () => TurnEvent::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 1,
        'deltas' => ['type' => PerformanceType::Dance->value],
    ]));
}

it('marks the deferred performance prop rescued instead of throwing when the stored row is malformed', function (): void {
    $run = realRescueRun();
    storeMalformedPerformanceEvent($run);

    // First paint: the prop is excluded before its closure runs, so even a corrupt row
    // does not disturb the decision-bearing payload.
    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Cockpit')
            ->where('performance_input', true)
            ->missing('performance'));

    // Partial reload of the deferred group: the closure now runs and throws inside the
    // resolver, which must record the rescue rather than surface a 500.
    $response = $this->get(route('runs.cockpit', $run), [
        Header::VERSION => $this->get(route('runs.cockpit', $run))->viewData('page')['version'],
        Header::PARTIAL_COMPONENT => 'Career/Cockpit',
        Header::PARTIAL_ONLY => 'performance',
    ])->assertOk();

    $page = json_decode(json_encode($response->viewData('page')), true);

    expect($page)->toHaveKey('rescuedProps');
    expect($page['rescuedProps'])->toContain('performance');
    expect($page['props'])->not->toHaveKey('performance');
});

it('serves the deferred performance reading on its group reload when the row is well formed', function (): void {
    $run = realRescueRun();

    TurnEvent::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 1,
        'deltas' => ['type' => PerformanceType::Vocals->value, 'delta' => 12],
    ]);

    $first = $this->get(route('runs.cockpit', $run))->assertOk();
    $version = json_decode(json_encode($first->viewData('page')), true)['version'];

    $this->get(route('runs.cockpit', $run), [
        Header::VERSION => $version,
        Header::PARTIAL_COMPONENT => 'Career/Cockpit',
        Header::PARTIAL_ONLY => 'performance',
    ])->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('performance.enabled', true)
        ->where('performance.types', ['Dance', 'Passion', 'Vocals', 'Visuals', 'Composure'])
        ->where('performance.observations', [['type' => 'Vocals', 'turn' => 1, 'delta' => 12]]));
});
