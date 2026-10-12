<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use App\Services\Career\SetupDraft;
use App\Services\ScenarioCaps;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The ceilings a Trainer enters for one run (`ADR-0025`), on the two surfaces that rule them in: the
 * cockpit's identity form and the build target step (the run-scoped write and the setup wizard's step 3,
 * which is one rule set). The read path itself is pinned in `StatCeilingsTest`; these cases pin what may
 * be stored, what a stored figure does to the turn form's bound, and what the wizard carries.
 */

function enteredCapsRun(): TrainingRun
{
    return TrainingRun::factory()->create([
        'umamusume_id' => Umamusume::factory(),
        'scenario' => 'unity_cup',
    ]);
}

/**
 * The whole key set `StoreTrainingRunRequest` demands, so a ceiling write is the only thing under test.
 *
 * @return array<string, mixed>
 */
function identityPost(TrainingRun $run, array $caps = []): array
{
    return array_merge([
        'umamusume_id' => $run->umamusume_id,
        'status' => $run->status->value,
    ], $caps);
}

/**
 * A complete Unity Cup target, the same shape `CareerBuildTargetTest` posts.
 *
 * @return array<string, mixed>
 */
function enteredCapsTarget(array $overrides = []): array
{
    return array_merge([
        'purpose' => 'StoryClear',
        'distance' => 'Mile',
        'surface' => 'Turf',
        'style' => 'Front Runner',
        'targets' => ['Speed' => 1300, 'Stamina' => 800, 'Power' => 700, 'Guts' => 600, 'Wit' => 500],
        'skill_priorities' => [],
    ], $overrides);
}

it('stores the four ceilings entered on the identity form', function (): void {
    $run = enteredCapsRun();

    $this->put(route('runs.update', $run), identityPost($run, [
        'cap_speed' => 1325,
        'cap_stamina' => 1322,
        'cap_power' => 1368,
        'cap_guts' => 1308,
    ]))->assertSessionHasNoErrors();

    $run = $run->fresh();

    expect($run->cap_speed)->toBe(1325)
        ->and($run->cap_stamina)->toBe(1322)
        ->and($run->cap_power)->toBe(1368)
        ->and($run->cap_guts)->toBe(1308)
        ->and(ScenarioCaps::forRun($run)['Speed'])->toBe(1325);
});

it('raises the turn form\'s bound to the ceiling the Trainer entered', function (): void {
    $run = enteredCapsRun();
    $this->put(route('runs.update', $run), identityPost($run, ['cap_speed' => 1325]))->assertSessionHasNoErrors();

    // 1325 is the figure the source's client showed and the scenario bound of 1300 refuses: the whole
    // point of the ruling. The turn write reads it through the same ceiling owner.
    $this->post(route('runs.turns.store', $run), [
        'turn' => 1,
        'speed' => 1325,
        'stamina' => 500,
        'power' => 500,
        'guts' => 500,
        'wit' => 500,
    ])->assertSessionHasNoErrors();

    expect(TurnEntry::query()->where('training_run_id', $run->id)->where('speed', 1325)->exists())->toBeTrue();

    // One above the entered ceiling is refused, and the refusal cites the entered number rather than
    // the scenario's, so a Trainer sees the bound they set.
    $this->post(route('runs.turns.store', $run), [
        'turn' => 2,
        'speed' => 1326,
        'stamina' => 500,
        'power' => 500,
        'guts' => 500,
        'wit' => 500,
    ])->assertSessionHasErrors('speed');
});

it('refuses an entered ceiling above the engine hard cap', function (): void {
    $run = enteredCapsRun();

    $this->put(route('runs.update', $run), identityPost($run, ['cap_speed' => ScenarioCaps::hardCap() + 1]))
        ->assertSessionHasErrors('cap_speed');

    expect($run->fresh()->cap_speed)->toBeNull();
});

it('refuses an entered ceiling below the highest value the run already recorded', function (): void {
    $run = enteredCapsRun();
    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1, 'speed' => 1325]);

    $this->put(route('runs.update', $run), identityPost($run, ['cap_speed' => 1300]))
        ->assertSessionHasErrors(['cap_speed' => 'Speed has a turn recorded at 1325, so its ceiling cannot sit below that.']);

    expect($run->fresh()->cap_speed)->toBeNull();
});

it('refuses clearing a ceiling while a recorded stat sits above the scenario bound', function (): void {
    $run = enteredCapsRun();
    $this->put(route('runs.update', $run), identityPost($run, ['cap_speed' => 1325]))->assertSessionHasNoErrors();
    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1, 'speed' => 1325]);

    // Null is a clear, not a no-op: reverting to 1300 would leave a stored row above its own ceiling,
    // which is the state the check above exists to refuse.
    $this->put(route('runs.update', $run), identityPost($run, ['cap_speed' => null]))
        ->assertSessionHasErrors('cap_speed');

    expect($run->fresh()->cap_speed)->toBe(1325);
});

it('applies a ceiling entered in the same save as the target that needs it', function (): void {
    $run = enteredCapsRun();

    // Unity Cup publishes 1300 for Speed, so a 1325 target is refused on its own. Entered together, the
    // posted ceiling is the bound the target is measured against, not the ceiling stored before the save.
    $this->put(route('runs.build-target.update', $run), array_merge(
        enteredCapsTarget(['targets' => [
            'Speed' => 1325,
            'Stamina' => 800,
            'Power' => 700,
            'Guts' => 600,
            'Wit' => 500,
        ]]),
        ['cap_speed' => 1325],
    ))->assertSessionHasNoErrors();

    $run = $run->fresh();

    expect($run->cap_speed)->toBe(1325)
        ->and($run->buildTarget()?->targets['Speed'])->toBe(1325);

    // The negative half: the posted ceiling raises the bound to itself and no further, so a target above
    // the ceiling the Trainer stated in the same breath is still refused.
    $this->put(route('runs.build-target.update', $run), array_merge(
        enteredCapsTarget(['targets' => [
            'Speed' => 1326,
            'Stamina' => 800,
            'Power' => 700,
            'Guts' => 600,
            'Wit' => 500,
        ]]),
        ['cap_speed' => 1325],
    ))->assertSessionHasErrors(['targets.Speed' => 'Speed must be between 0 and 1325.']);
});

it('carries the wizard\'s entered ceilings onto the run it starts', function (): void {
    $trainee = Umamusume::factory()->create();

    session([SetupDraft::SESSION_KEY => [
        'scenario' => 'unity_cup',
        'umamusume_id' => $trainee->id,
        'build_target' => enteredCapsTarget(),
        'caps' => ['cap_speed' => 1325],
    ]]);

    $this->put(route('career.preflight.store'))->assertRedirect();

    $run = TrainingRun::query()->where('umamusume_id', $trainee->id)->sole();

    expect($run->cap_speed)->toBe(1325)
        ->and($run->cap_power)->toBeNull()
        ->and(ScenarioCaps::forRun($run)['Speed'])->toBe(1325);
});

it('keeps Wit on its scenario bound whatever a client posts', function (): void {
    $run = enteredCapsRun();

    // No `cap_wit` column exists: the ruled surfaces cover four stats, and a fifth key is dropped by the
    // request rather than stored, so Wit cannot be moved by a hand-made POST.
    $this->put(route('runs.update', $run), identityPost($run, ['cap_speed' => 1325, 'cap_wit' => 1900]))
        ->assertSessionHasNoErrors();

    expect(ScenarioCaps::forRun($run->fresh())['Wit'])->toBe(1800);
});

it('sends the turn form the entered ceiling as the bound it draws', function (): void {
    $run = enteredCapsRun();
    $this->put(route('runs.update', $run), identityPost($run, ['cap_speed' => 1325]))->assertSessionHasNoErrors();

    // The Training Decision screen reads each stat's `max` off its option row, so this prop is the bound
    // the form shows: Speed follows the entered ceiling and Wit, which has no column, keeps the
    // scenario's own figure.
    $this->get(route('runs.training', $run))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/TrainingDetail')
            ->where('options.0.key', 'Speed')
            ->where('options.0.cap', 1325)
            ->where('options.4.key', 'Wit')
            ->where('options.4.cap', 1800)
        );
});

it('leaves the ceilings untouched on a run write that does not carry them', function (): void {
    $run = enteredCapsRun();
    $this->put(route('runs.update', $run), identityPost($run, ['cap_speed' => 1325]))->assertSessionHasNoErrors();

    // The status, scenario and period forms each post their own slice. A write that names no ceiling
    // must not blank one, because the ceiling the Trainer read does not stop being true because a
    // different form was saved.
    $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'status' => RunStatus::Completed->value,
    ])->assertSessionHasNoErrors();

    expect($run->fresh()->cap_speed)->toBe(1325)
        ->and($run->fresh()->status)->toBe(RunStatus::Completed);
});
