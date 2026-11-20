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

it('does not let the completion scheduler overlap itself', function (): void {
    // `schedule:list` proves the cadence but not the overlap policy: its row carries only the cron
    // expression, command and next due. Pin the registration itself in bootstrap/app.php so a future
    // edit that drops the guard fails a test rather than only a review.
    $source = file_get_contents(base_path('bootstrap/app.php'));

    expect($source)->toMatch(
        "/independent-training:complete'\)->everyMinute\(\)->withoutOverlapping\(\)/"
    );
});

/*
 * Two invariants the class declared and did not enforce.
 *
 * `canStart()` was covered here and called by nothing in `app/`, so "you cannot start another session
 * while one is open" lived in a docblock. And neither transition ran in a transaction: `complete()` and
 * `abandon()` each wrote the overlay and then the parent run, so a failure between the two left the pair
 * disagreeing, a state `completeExpired()` never revisits because it only selects rows holding both
 * nulls.
 */
it('refuses a second session while another window is open', function (): void {
    $lifecycle = new Lifecycle;

    $first = itrRun();
    $lifecycle->start($first, ['policy' => 'stamina']);

    // A different run, one session already open: refused by the one-at-a-time rule.
    expect(fn () => $lifecycle->start(itrRun(), ['policy' => 'stamina']))
        ->toThrow(InvalidArgumentException::class);

    // The same run again, which the unique index also refuses.
    expect(fn () => $lifecycle->start($first, ['policy' => 'stamina']))
        ->toThrow(InvalidArgumentException::class);

    expect(IndependentTrainingRun::query()->count())->toBe(1);
});

it('allows a new session once the previous window is closed', function (): void {
    $lifecycle = new Lifecycle;

    $first = $lifecycle->start(itrRun(), ['policy' => 'stamina']);
    $lifecycle->abandon($first);

    $second = $lifecycle->start(itrRun(), ['policy' => 'stamina']);

    expect($second->abandoned_at)->toBeNull()
        ->and(IndependentTrainingRun::query()->count())->toBe(2);
});

it('does not re-stamp a session another caller already closed', function (): void {
    $lifecycle = new Lifecycle;
    $itr = $lifecycle->start(itrRun(), ['policy' => 'stamina']);

    // Two ticks racing the same row: the first claims it, the second must be a no-op rather than a
    // second stamp that would overwrite the recorded finish.
    $first = $lifecycle->complete($itr);
    $stampedAt = $itr->fresh()->completed_at;

    $second = $lifecycle->complete($itr->fresh());

    expect($itr->fresh()->completed_at->equalTo($stampedAt))->toBeTrue()
        ->and($first->id)->toBe($second->id)
        ->and($first->status)->toBe(RunStatus::Completed);
});

/*
 * S1.6's client half, pinned where it can be checked without a browser.
 *
 * "Give up" posted straight to `abandon_url`: no question, and on success the only visible change was
 * the card losing its in-progress block. A misclick ended a session, and a Trainer who meant it could
 * not tell an ended session from a re-render. The page now routes the click through a function that
 * asks first, and the server's flash names the outcome.
 */
it('asks before giving up, and says so when it did', function (): void {
    $source = (string) file_get_contents(base_path('resources/js/pages/Dashboard.vue'));

    // The click is wired to the guard, not straight to the post.
    expect($source)->toContain('@click="giveUpIndependentTraining"')
        ->and($source)->not->toContain('@click="router.post(props.activeCareer.indep.abandon_url)"')
        // And the guard asks. A function that posts without a question is the defect.
        ->and($source)->toContain('confirm(');

    // The server's answer names what happened rather than re-rendering silently.
    $run = itrRun();
    app(Lifecycle::class)->start($run, ['policy' => 'stamina']);

    $this->post(route('runs.indep.abandon', $run))
        ->assertRedirect(route('home'))
        ->assertSessionHas('status', 'Independent Training session abandoned.');

    // The run is retired, which is the consequence the question promised.
    expect($run->fresh()->status)->toBe(RunStatus::Retired);
});
