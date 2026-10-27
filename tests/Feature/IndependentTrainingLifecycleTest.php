<?php

declare(strict_types=1);

use App\Enums\IndependentTrainingPolicy;
use App\Enums\RunStatus;
use App\Models\IndependentTrainingRun;
use App\Models\TrainingRun;
use App\Services\IndependentTraining\Lifecycle;
use Illuminate\Support\Facades\Artisan;

/*
 * UMAMUSUME_REFERENCE.md §1.1.7: the idle mode's lifecycle. A session opens a 50-minute window, is
 * one-at-a-time, and ends by completion (scheduler) or abandonment (Trainer).
 */

function itrRun(): TrainingRun
{
    return TrainingRun::factory()->create(['status' => RunStatus::Active]);
}

it('starts a session with completes_at 50 minutes ahead and the policy cast', function (): void {
    $run = itrRun();
    $lifecycle = new Lifecycle;

    $itr = $lifecycle->start($run, ['policy' => 'stamina', 'priority_skills' => [1, 2, 3]]);

    expect($itr->policy)->toBe(IndependentTrainingPolicy::Stamina)
        ->and($itr->priority_skills)->toBe([1, 2, 3])
        ->and($itr->completes_at->isSameMinute(now()->addMinutes(50)))->toBeTrue()
        ->and($itr->completed_at)->toBeNull()
        ->and($itr->abandoned_at)->toBeNull();
});

it('refuses an unknown policy and more than ten priority skills', function (): void {
    $lifecycle = new Lifecycle;

    expect(fn () => $lifecycle->start(itrRun(), ['policy' => 'nonsense']))
        ->toThrow(InvalidArgumentException::class, 'Unknown Independent Training policy.');

    expect(fn () => $lifecycle->start(itrRun(), ['policy' => 'balanced', 'priority_skills' => range(1, 11)]))
        ->toThrow(InvalidArgumentException::class, 'At most 10 priority skills');
});

it('is one-at-a-time: canStart is false while any session is in progress', function (): void {
    $lifecycle = new Lifecycle;
    $first = itrRun();
    $lifecycle->start($first, ['policy' => 'balanced']);

    $second = itrRun();

    expect($lifecycle->canStart($second))->toBeFalse()
        ->and($lifecycle->canStart($first))->toBeFalse();
});

it('completes a session and marks the run Completed', function (): void {
    $lifecycle = new Lifecycle;
    $run = itrRun();
    $itr = $lifecycle->start($run, ['policy' => 'balanced']);

    $lifecycle->complete($itr);

    expect($itr->fresh()->completed_at)->not->toBeNull()
        ->and($run->fresh()->status)->toBe(RunStatus::Completed)
        ->and($run->fresh()->isInProgress())->toBeFalse();
});

it('abandons a session and marks the run Retired', function (): void {
    $lifecycle = new Lifecycle;
    $run = itrRun();
    $itr = $lifecycle->start($run, ['policy' => 'balanced']);

    $lifecycle->abandon($itr);

    expect($itr->fresh()->abandoned_at)->not->toBeNull()
        ->and($run->fresh()->status)->toBe(RunStatus::Retired);
});

it('is idempotent across both endings', function (): void {
    $lifecycle = new Lifecycle;
    $run = itrRun();
    $itr = $lifecycle->start($run, ['policy' => 'balanced']);

    $lifecycle->abandon($itr);
    $lifecycle->complete($itr); // must not overwrite the abandonment

    expect($itr->fresh()->abandoned_at)->not->toBeNull()
        ->and($itr->fresh()->completed_at)->toBeNull()
        ->and($run->fresh()->status)->toBe(RunStatus::Retired);
});

it('the scheduled command closes every expired session', function (): void {
    $lifecycle = new Lifecycle;
    $open = itrRun();
    $expired = itrRun();

    $lifecycle->start($open, ['policy' => 'balanced']);

    IndependentTrainingRun::factory()->for($expired, 'trainingRun')->expired()->create();

    Artisan::call('independent-training:complete');

    expect($open->fresh()->isInProgress())->toBeTrue()
        ->and($expired->fresh()->independentTrainingRun->completed_at)->not->toBeNull()
        ->and($expired->fresh()->status)->toBe(RunStatus::Completed);
});

it('registers the scheduler to complete sessions every minute', function (): void {
    // The authoritative surface is `schedule:list`, not the container's `Schedule::events()`: the
    // `withSchedule` callback in `bootstrap/app.php` is applied when the schedule boots, so a bare
    // `app(Schedule::class)` in a test resolves an empty instance and lies about the wiring.
    Artisan::call('schedule:list');

    $out = Artisan::output();

    expect($out)->toContain('independent-training:complete')
        ->and($out)->toContain('* * * * *');
});