<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * UI/UX Phase 5 §5.3 - deferred props for a data-heavy read.
 *
 * The Cockpit's Performance card is a read the first paint does not need: the Trainer's next decision
 * rides `advisor`, not the concert observations. The prop is therefore excluded from the first payload
 * and resolved by the partial reload `<Deferred>` asks for, while the matrix's cheap capability flag
 * stays un-deferred so the page knows whether the region has a home at all.
 */

function deferredPerformanceRun(): TrainingRun
{
    return TrainingRun::factory()->create([
        'scenario' => 'our_grand_concert',
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->create()->id,
    ]);
}

it('keeps the deferred performance reading out of the first cockpit payload', function (): void {
    $run = deferredPerformanceRun();

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Cockpit')
            ->where('performance_input', true)
            ->missing('performance'));
});

it('returns the deferred performance reading on its group partial reload', function (): void {
    $run = deferredPerformanceRun();

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->loadDeferredProps('cockpit-secondary', fn (Assert $deferred) => $deferred
                ->where('performance.enabled', true)
                ->where('performance.types', ['Dance', 'Passion', 'Vocals', 'Visuals', 'Composure'])
                ->where('performance.observations', [])));
});

it('carries the capability flag false without requesting a deferred region the page will not draw', function (): void {
    $run = TrainingRun::factory()->create([
        'scenario' => 'ura_finale',
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->create()->id,
    ]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('performance_input', false)
            ->missing('performance'));
});
