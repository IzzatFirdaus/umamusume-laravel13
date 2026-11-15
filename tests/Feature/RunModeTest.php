<?php

declare(strict_types=1);

use App\Enums\RunMode;
use App\Models\TrainingRun;
use App\Models\Umamusume;

/**
 * The mode a run came to exist in, and the one cross-field rule it carries.
 */
it('answers NewCareer for a run created without naming a mode', function (): void {
    $run = TrainingRun::factory()->create();

    expect($run->mode)->toBe(RunMode::NewCareer)
        ->and($run->isSnapshot())->toBeFalse();
});

it('backs existing rows to NewCareer in the migration, so isSnapshot is a plain read', function (): void {
    // The factory does not set `mode`, so this row arrives through the same
    // default a pre-snapshot writer's row would have been backfilled to.
    $run = TrainingRun::factory()->create();

    expect($run->fresh()->mode)->toBe(RunMode::NewCareer);
});

it('answers Snapshot for a run created with mode snapshot and a named position', function (): void {
    $run = TrainingRun::factory()->create([
        'mode' => 'snapshot',
        'career_position' => ['year' => 3, 'month' => 10, 'phase' => 'Early', 'turn_index' => 67],
    ]);

    expect($run->fresh()->mode)->toBe(RunMode::Snapshot)
        ->and($run->fresh()->isSnapshot())->toBeTrue()
        ->and($run->fresh()->careerPosition()->turnIndex)->toBe(67);
});

it('refuses a snapshot that names no position, with the reason it is not one', function (): void {
    $trainee = Umamusume::factory()->create();

    $response = test()->post('/training-runs', [
        'umamusume_id' => $trainee->id,
        'status' => 'Active',
        'mode' => 'snapshot',
    ]);

    $response->assertSessionHasErrors([
        'career_position' => 'A snapshot must name where the career stands, because a snapshot without a position is a new career that has not admitted to being one.',
    ]);
});

it('refuses a mode the enum does not name', function (): void {
    $trainee = Umamusume::factory()->create();

    test()->post('/training-runs', [
        'umamusume_id' => $trainee->id,
        'status' => 'Active',
        'mode' => 'imported-snapshot',
    ])->assertSessionHasErrors(['mode']);
});

it('defaults the create form to NewCareer when it sends no mode at all', function (): void {
    $trainee = Umamusume::factory()->create();

    $response = test()->post('/training-runs', [
        'umamusume_id' => $trainee->id,
        'status' => 'Active',
    ]);

    $response->assertRedirect();

    expect(TrainingRun::query()->latest('id')->first()->mode)->toBe(RunMode::NewCareer);
});

it('keeps a snapshot mode when an identity update omits it', function (): void {
    // `Career/RunSettings.vue` posts no `mode` (its forms carry status, scenario or identity), so
    // the request's unguarded default rewrote every edited snapshot to `new_career` on save
    // (measured 2026-11-15). The default belongs to `runs.store` alone.
    $run = TrainingRun::factory()->create([
        'mode' => 'snapshot',
        'career_position' => ['year' => 3, 'month' => 10, 'phase' => 'Early', 'turn_index' => 67],
    ]);

    test()->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'status' => $run->status->value,
    ])->assertRedirect();

    expect($run->fresh()->mode)->toBe(RunMode::Snapshot);
});
