<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * The two readings the client prints before a turn resolves: the failure rate and the preview gains.
 *
 * Both are transcribed, never computed. `ADR-0016` and `ADR-0020` §3 keep outcome prediction out of the
 * product, and `PRD.md` §6.11 is the sharper rule: setting a reading must not move the advisor's answer.
 * The last case here is that guard, and it is the reason the two columns exist as stored facts rather
 * than as inputs to any ranking.
 */
function previewRun(): TrainingRun
{
    return TrainingRun::factory()->create([
        'status' => RunStatus::Active,
        'scenario' => 'unity_cup',
        'umamusume_id' => Umamusume::factory()->create()->id,
    ]);
}

it('persists a failure rate and a preview row through the turn route', function (): void {
    $run = previewRun();

    test()->post(route('runs.turns.store', $run), [
        'turn' => 9,
        'speed' => 600,
        'stamina' => 500,
        'power' => 500,
        'guts' => 500,
        'wit' => 500,
        'failure_rate' => 39,
        'preview_gains' => ['speed' => 18, 'power' => 8, 'sp' => 6],
    ])->assertRedirect();

    $entry = TurnEntry::query()
        ->where('training_run_id', $run->id)
        ->where('turn', 9)
        ->firstOrFail();

    expect($entry->failure_rate)->toBe(39)
        ->and($entry->preview_gains)->toBe(['speed' => 18, 'power' => 8, 'sp' => 6]);
});

it('stores only the preview members that were read, and null when none were', function (): void {
    $run = previewRun();

    // The form posts every member whether or not the Trainer filled it. A blank is not a reading, so
    // the untouched members must not be stored as nulls claiming the row was read.
    test()->post(route('runs.turns.store', $run), [
        'turn' => 3,
        'speed' => 600,
        'stamina' => 500,
        'power' => 500,
        'guts' => 500,
        'wit' => 500,
        'preview_gains' => [
            'speed' => 18,
            'stamina' => '',
            'power' => '',
            'guts' => '',
            'wit' => '',
            'sp' => '',
        ],
    ])->assertRedirect();

    $partial = TurnEntry::query()
        ->where('training_run_id', $run->id)
        ->where('turn', 3)
        ->firstOrFail();

    expect($partial->preview_gains)->toBe(['speed' => 18]);

    // A disclosure opened and left alone posts six blanks; that is no reading at all, not a row of nulls.
    test()->post(route('runs.turns.store', $run), [
        'turn' => 4,
        'speed' => 600,
        'stamina' => 500,
        'power' => 500,
        'guts' => 500,
        'wit' => 500,
        'preview_gains' => [
            'speed' => '',
            'stamina' => '',
            'power' => '',
            'guts' => '',
            'wit' => '',
            'sp' => '',
        ],
    ])->assertRedirect();

    $untouched = TurnEntry::query()
        ->where('training_run_id', $run->id)
        ->where('turn', 4)
        ->firstOrFail();

    expect($untouched->preview_gains)->toBeNull()
        ->and($untouched->failure_rate)->toBeNull();
});

it('refuses a failure rate outside the percentage scale and a preview key the row does not hold', function (): void {
    $run = previewRun();

    $base = [
        'speed' => 600,
        'stamina' => 500,
        'power' => 500,
        'guts' => 500,
        'wit' => 500,
    ];

    test()->post(route('runs.turns.store', $run), $base + ['turn' => 5, 'failure_rate' => 101])
        ->assertSessionHasErrors(['failure_rate']);

    // `array:` refuses the seventh key rather than dropping it, so a new reading is a schema change.
    test()->post(route('runs.turns.store', $run), $base + [
        'turn' => 6,
        'preview_gains' => ['speed' => 18, 'aptitude' => 3],
    ])->assertSessionHasErrors(['preview_gains']);
});

it('carries the labelled readings to the timeline, with one absence when a turn read none', function (): void {
    $run = previewRun();

    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 9,
        'failure_rate' => 39,
        'preview_gains' => ['speed' => 18, 'power' => 8, 'sp' => 6],
    ]);

    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 10]);

    test()->get(route('runs.timeline', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Timeline')
            ->where('entries.0.turn', 9)
            ->where('entries.0.failure_rate', 39)
            // Labelled and ordered the way the entry form asks for them, and only the members read.
            ->where('entries.0.preview_gains', [
                ['label' => 'Speed', 'value' => 18],
                ['label' => 'Power', 'value' => 8],
                ['label' => 'Skill Points', 'value' => 6],
            ])
            ->where('entries.1.turn', 10)
            ->where('entries.1.failure_rate', null)
            ->where('entries.1.preview_gains', null));
});

it('leaves the advisor unchanged when a turn carries a failure rate and preview gains', function (): void {
    $run = previewRun();

    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 1,
        'speed' => 600,
        'stamina' => 600,
        'power' => 600,
        'guts' => 600,
        'wit' => 600,
        'energy' => 80,
        'fans' => 1000,
    ]);

    $before = test()->get(route('runs.training', $run))->assertOk()->viewData('page')['props'];

    $turn = TurnEntry::query()->where('training_run_id', $run->id)->where('turn', 1)->firstOrFail();
    $turn->update([
        'failure_rate' => 39,
        'preview_gains' => ['speed' => 18, 'power' => 8, 'sp' => 6],
    ]);

    $after = test()->get(route('runs.training', $run))->assertOk()->viewData('page')['props'];

    // PRD §6.11: the advisor reads stored facts, and these two are readings it must not rank against.
    expect($after['advisor'])->toBe($before['advisor'])
        ->and($after['options'])->toBe($before['options']);

    // The guard is not vacuous: the same payload does move when a fact the advisor does read changes.
    $turn->update(['speed' => 900]);

    $moved = test()->get(route('runs.training', $run))->assertOk()->viewData('page')['props'];

    expect($moved['options'])->not->toBe($before['options']);
});
