<?php

declare(strict_types=1);

use App\Enums\IndependentTrainingPolicy;
use App\Enums\RunStatus;
use App\Models\IndependentTrainingRun;
use App\Models\TrainingRun;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * UMAMUSUME_REFERENCE.md §1.1.7: the dashboard surfaces the idle-mode overlay. While the 50-minute
 * window is open the active-career card carries the timer and the one-at-a-time rule, so a Trainer
 * who opens the app mid-session sees the run is still playing itself out.
 */

it('surfaces the in-progress idle window on the active career', function (): void {
    $run = TrainingRun::factory()->create(['status' => RunStatus::Active]);
    IndependentTrainingRun::factory()->for($run, 'trainingRun')->create([
        'policy' => IndependentTrainingPolicy::Balanced,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('activeCareer.indep.in_progress', true)
            ->where('activeCareer.indep.policy_label', 'Balanced')
            ->where('activeCareer.indep.completes_at', fn ($value): bool => is_string($value) && $value !== ''));
});

it('surfaces no idle window for a normal career', function (): void {
    TrainingRun::factory()->create(['status' => RunStatus::Active]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('activeCareer.indep', null));
});
