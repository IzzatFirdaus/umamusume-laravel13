<?php

declare(strict_types=1);

use App\Enums\ReleaseStatus;
use App\Models\Skill;
use App\Models\TrainingRun;

/*
 * R-5: every save that lands back on the run page carries a flash status, so a
 * Trainer can tell which of the page's forms landed. One assertion per form,
 * checking the redirect carries the `status` key the show view renders. The
 * rendering itself is verified on the live page; this pins the contract.
 */

function runForSaveConfirmation(): TrainingRun
{
    return TrainingRun::factory()->create(['scenario' => 'ura_finale']);
}

it('confirms a deck save', function (): void {
    $run = runForSaveConfirmation();

    test()->post(route('runs.deck.sync', $run), ['deck' => []])
        ->assertRedirect(route('runs.show', $run))
        ->assertSessionHas('status', 'Deck saved.');
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
        ->assertRedirect(route('runs.show', $run))
        ->assertSessionHas('status', 'Skill status saved.');
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
        ->assertRedirect(route('runs.show', $run))
        ->assertSessionHas('status', 'Turn 2 logged.');
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
        ->assertRedirect(route('runs.show', $run))
        ->assertSessionHas('status', 'Turn 3 logged.');
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
        ->assertRedirect(route('runs.show', $run))
        ->assertSessionHas('status', 'Race recorded.');
});

it('confirms a run update', function (): void {
    $run = runForSaveConfirmation();

    test()->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'status' => $run->status->value,
        'scenario' => $run->scenario,
    ])
        ->assertRedirect(route('runs.show', $run))
        ->assertSessionHas('status', 'Run updated.');
});
