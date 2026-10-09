<?php

declare(strict_types=1);

use App\Models\TrainingRun;

/*
 * C1's write boundary (FR-F-1, `ADR-0020` §2).
 *
 * What this file proves is that the validation has one owner and that its ceiling is the run's own:
 * the accepted number is the one `ScenarioCaps::forRun` publishes, and the refused one is refused
 * with the bound named. The payload's own shape rules live in `BuildTargetPayloadTest`; here the
 * question is whether a Trainer can get a bad number past the door.
 */

function targetPayload(array $overrides = []): array
{
    return array_merge([
        'purpose' => 'StoryClear',
        'distance' => 'Medium',
        'surface' => 'Turf',
        'style' => 'Pace Chaser',
        'targets' => ['Speed' => 900, 'Stamina' => 800, 'Power' => 700, 'Guts' => 600, 'Wit' => 500],
        'skill_priorities' => ['Corner Adept'],
    ], $overrides);
}

it('stores a target and reads it back through the payload', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    $this->put(route('runs.build-target.update', $run), targetPayload())
        ->assertRedirect(route('runs.cockpit', $run));

    expect($run->fresh()->buildTarget()?->toArray())->toBe(targetPayload());
});

it('accepts a target exactly on the scenario ceiling, and refuses one above it', function (): void {
    // URA Finale's Speed ceiling is 1,400 (1,200 base + 200). The boundary is asserted from both
    // sides so the rule cannot pass by refusing everything, or by accepting everything.
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    $this->put(route('runs.build-target.update', $run), targetPayload(['targets' => [
        'Speed' => 1400, 'Stamina' => 800, 'Power' => 700, 'Guts' => 600, 'Wit' => 500,
    ]]))->assertSessionHasNoErrors();

    $this->put(route('runs.build-target.update', $run), targetPayload(['targets' => [
        'Speed' => 1401, 'Stamina' => 800, 'Power' => 700, 'Guts' => 600, 'Wit' => 500,
    ]]))->assertSessionHasErrors('targets.Speed');
});

it('names the bound it refused on, rather than only that something was too high', function (): void {
    // D-56. A refusal a Trainer cannot act on is not a recoverable failure, and the bound is the
    // run's own ceiling rather than a number the form invented.
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    $this->put(route('runs.build-target.update', $run), targetPayload(['targets' => [
        'Speed' => 9999, 'Stamina' => 800, 'Power' => 700, 'Guts' => 600, 'Wit' => 500,
    ]]))->assertSessionHasErrors(['targets.Speed' => 'Speed must be between 0 and 1400.']);
});

it('holds a run with no scenario to the base cap, not to a bonus it never chose', function (): void {
    // The same refusal ScenarioCaps makes for a run that names no scenario: lending URA Finale's
    // +200 to a run that never picked it would accept a number the tool has no source for.
    $run = TrainingRun::factory()->create(['scenario' => null]);

    $this->put(route('runs.build-target.update', $run), targetPayload(['targets' => [
        'Speed' => 1200, 'Stamina' => 800, 'Power' => 700, 'Guts' => 600, 'Wit' => 500,
    ]]))->assertSessionHasNoErrors();

    $this->put(route('runs.build-target.update', $run), targetPayload(['targets' => [
        'Speed' => 1201, 'Stamina' => 800, 'Power' => 700, 'Guts' => 600, 'Wit' => 500,
    ]]))->assertSessionHasErrors('targets.Speed');
});

it('refuses a purpose that is not one of the four cases', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    $this->put(route('runs.build-target.update', $run), targetPayload(['purpose' => 'General Training']))
        ->assertSessionHasErrors('purpose');
});

it('refuses a target set missing one of the five stats', function (): void {
    // Named per stat, not against the whole map: a refusal that says "targets" when Wit is the one
    // missing leaves the Trainer to work out which of five fields to fill.
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    $this->put(route('runs.build-target.update', $run), targetPayload([
        'targets' => ['Speed' => 900, 'Stamina' => 800, 'Power' => 700, 'Guts' => 600],
    ]))->assertSessionHasErrors('targets.Wit');
});

it('refuses a sixth stat a hand-made POST tries to add', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    $this->put(route('runs.build-target.update', $run), targetPayload([
        'targets' => ['Speed' => 900, 'Stamina' => 800, 'Power' => 700, 'Guts' => 600, 'Wit' => 500, 'Extra' => 1],
    ]))->assertSessionHasErrors('targets');
});
