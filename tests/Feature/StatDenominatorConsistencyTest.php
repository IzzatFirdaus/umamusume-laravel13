<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\Advisor\BuildTargetPayload;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Services\ScenarioCaps;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * R2-09: the second number on the two stat strips carries its own denominator's name.
 *
 * The Cockpit's second number is the Trainer's own target and the event screen's is the run's scenario
 * ceiling, and neither screen said which one it was showing. The fix labels each in its own word rather
 * than unifying them: the Cockpit answers "how far to the Trainer's plan" and the event screen answers
 * "how close to the run's ceiling", so collapsing them would answer one question with the other's
 * number.
 *
 * The two data cases read the props each screen sends; the two copy cases read the components that
 * print them, because the word is a rendering concern no Inertia payload can carry; the last case pins
 * that an unset target stays null rather than borrowing the cap.
 */

/** A run with one logged turn, and a build target only when the case needs one. */
function denominatorRun(?array $targets = null): TrainingRun
{
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale', 'status' => RunStatus::Active]);

    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 1,
        'speed' => 607,
        'stamina' => 577,
        'power' => 549,
        'guts' => 736,
        'wit' => 397,
        'sp' => 173,
        'energy' => 70,
        'fans' => 209245,
    ]);

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

    return $run;
}

it('sends the Trainer target, not the cap, on the Cockpit stat strip', function (): void {
    $run = denominatorRun(['Speed' => 900, 'Stamina' => 500, 'Power' => 500, 'Guts' => 500, 'Wit' => 800]);
    $cap = ScenarioCaps::forRun($run)['Speed'];

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('state.stats.0.key', 'Speed')
            ->where('state.stats.0.target', 900)
            ->where('state.stats.0.cap', $cap));

    // The two numbers differ on this run, so a strip printing the cap under the target's name would be
    // visibly wrong rather than coincidentally right.
    expect(900)->not->toBe($cap);
});

it('sends the scenario cap on the event screen stat strip', function (): void {
    $run = denominatorRun(['Speed' => 900, 'Stamina' => 500, 'Power' => 500, 'Guts' => 500, 'Wit' => 800]);
    $cap = ScenarioCaps::forRun($run)['Speed'];

    $this->get(route('runs.events.decision', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('state.stats.0.key', 'Speed')
            ->where('state.stats.0.cap', $cap));
});

it('labels the Cockpit strip with the word target', function (): void {
    $panel = (string) file_get_contents(resource_path('js/components/career/CareerStatePanel.vue'));
    $page = (string) file_get_contents(resource_path('js/pages/Career/Cockpit.vue'));

    // The missing target is one fact about the run, so it now reads once above the five rows instead of as
    // a phrase repeated on each of them. This case pins both halves of that change: the caption is there,
    // and the per-row phrase is not.
    expect($panel)->toContain('denominatorLabel')
        ->and($panel)->toContain("'No build target was set for this run.'")
        ->and($panel)->not->toContain('no {{ props.denominatorLabel }} set')
        ->and($page)->toContain('denominator-label="target"');
});

it('labels the event strip with the word cap', function (): void {
    $page = (string) file_get_contents(resource_path('js/pages/Career/EventDecision.vue'));

    expect($page)->toContain('/{{ stat.cap }} cap');
});

it('leaves the target slot honest when no target is entered', function (): void {
    $run = denominatorRun();

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('state.stats.0.target', null));
});
