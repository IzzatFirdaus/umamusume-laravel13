<?php

declare(strict_types=1);

use App\Enums\ReleaseStatus;
use App\Models\Skill;
use App\Models\TrainingRun;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * R-5: every save that lands back on the run carries a flash status, so a
 * Trainer can tell which of the run's forms landed. One assertion per form,
 * checking the redirect lands on the Cockpit (or the surviving legacy door the
 * write still pins) with the value already in the shared `flash` prop the shell
 * renders the banner from. What the layout draws from that prop is measured on
 * the live page; this pins the contract.
 */

function runForSaveConfirmation(): TrainingRun
{
    return TrainingRun::factory()->create(['scenario' => 'ura_finale']);
}

it('confirms a deck save', function (): void {
    $run = runForSaveConfirmation();

    // `runs.deck.sync` is the one surviving write that still pins the legacy `runs.show`
    // door rather than the Cockpit; `runs.show` then 302s on to `runs.cockpit`.
    test()->post(route('runs.deck.sync', $run), ['deck' => []])
        ->assertRedirect(route('runs.show', $run));

    test()->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Cockpit')
            ->where('flash.status', 'Deck saved.'));
});

it('confirms a skill status save', function (): void {
    $run = runForSaveConfirmation();
    $skill = Skill::factory()->create([
        'release_status' => ReleaseStatus::GlobalReleased->value,
        'name_is_client' => true,
    ]);

    test()->post(route('runs.skills.sync', $run), [
        'skills' => [
            ['skill_id' => $skill->id, 'status' => 'Acquired', 'turn_acquired' => null],
        ],
    ])
        ->assertRedirect(route('runs.cockpit', $run));

    test()->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Cockpit')
            ->where('flash.status', 'Skill status saved.'));
});

it('confirms a guided turn commit', function (): void {
    $run = runForSaveConfirmation();

    test()->post(route('runs.turns.store', $run), [
        'stage' => 'confirm',
        'previewed' => '1',
        'turn' => 2,
        'speed' => 100,
        'stamina' => 100,
        'power' => 100,
        'guts' => 100,
        'wit' => 100,
        'choice' => 'training-Speed',
        'outcome' => 'Success',
    ])
        ->assertRedirect(route('runs.cockpit', $run));

    test()->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Cockpit')
            ->where('flash.status', 'Turn 2 logged.'));
});

it('confirms a manual correction', function (): void {
    $run = runForSaveConfirmation();

    test()->post(route('runs.turns.store', $run), [
        'turn' => 3,
        'speed' => 100,
        'stamina' => 100,
        'power' => 100,
        'guts' => 100,
        'wit' => 100,
    ])
        ->assertRedirect(route('runs.cockpit', $run));

    test()->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Cockpit')
            ->where('flash.status', 'Turn 3 logged.'));
});

it('confirms a race entry', function (): void {
    $run = runForSaveConfirmation();

    test()->post(route('runs.races.store', $run), [
        'entry_mode' => 'manual',
        'status' => 'Entered',
        'title' => 'Kyoto Daishoten',
        'month' => 10,
        'half' => 'Early',
    ])
        ->assertRedirect(route('runs.cockpit', $run));

    test()->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Cockpit')
            ->where('flash.status', 'Race recorded.'));
});

it('confirms a run update', function (): void {
    $run = runForSaveConfirmation();

    test()->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'status' => $run->status->value,
        'scenario' => $run->scenario,
    ])
        ->assertRedirect(route('runs.cockpit', $run));

    test()->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Cockpit')
            ->where('flash.status', 'Run updated.'));
});
