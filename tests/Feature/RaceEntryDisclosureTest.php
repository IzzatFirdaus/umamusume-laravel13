<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\TrainingRun;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * R67: the two-path race form is server-driven disclosure, exactly as guided-step does it. Which
 * half is open is server state, resolved into `racePanel.entryMode` (the flash wins over the query,
 * which wins over 'calendar'), so the reachable branch is asserted from the page payload.
 *
 * R71: the assertion must name what a Trainer can reach, not what a template emitted. A `template
 * x-if` branch shipped as inert markup in the Blade source, so `assertSee('name="title"')` passed
 * against markup no Trainer could reach; the port moved that decision into the payload, and which
 * fields each branch renders is the component's.
 */

it('renders the calendar branch reachable, with no manual fields in the page', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    RaceCatalogSlot::factory()->create([
        'year' => 1, 'month' => 5, 'half' => 'Late', 'turn' => 11, 'title' => 'Japanese Oaks',
    ]);
    ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'free_race',
        'title' => 'Autumn Practice Stakes',
        'slot_label' => 'Autumn Practice Stakes',
        'month' => 9,
        'half' => 'Late',
        'is_manual' => true,
    ]);

    // Two lists, two controls: the calendar branch names a row of the career catalogue, and a race
    // the Trainer typed earlier keeps its own control; neither reaches the manual branch's fields.
    $this->get(route('runs.show', $run))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('racePanel.entryMode', 'calendar')
        ->has('racePanel.calendarSlots', 1)
        ->has('racePanel.manualSlots', 1)
        ->where('racePanel.manualSlots.0.title', 'Autumn Practice Stakes'));
});

it('carries a real entry_mode value in the calendar branch', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'goal_race',
        'source_key' => 'src-2',
        'title' => 'Tokyo Yushun (Japanese Derby)',
        'is_manual' => false,
    ]);

    // The form submits the branch it was served, so the mode is the one the page resolved, not a
    // literal a template could leave inert.
    $this->get(route('runs.show', $run))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('racePanel.entryMode', 'calendar'));
});

it('renders the manual branch reachable when the disclosure switches the mode', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    // The switch is a navigation: the mode arrives as the query parameter a Trainer's click sends,
    // and the manual branch's fields are what the payload names.
    $this->get(route('runs.show', ['run' => $run, 'entry_mode' => 'manual']))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('racePanel.entryMode', 'manual'));
});

it('carries a real entry_mode value in the manual branch', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    $this->get(route('runs.show', ['run' => $run, 'entry_mode' => 'manual']))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('racePanel.entryMode', 'manual'));
});

it('rejects a failed manual entry and stores nothing', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    $response = $this->post(route('runs.races.store', $run), [
        'entry_mode' => 'manual',
        'title' => 'Midsummer Practice Race',
        'month' => 13,
        'half' => 'Early',
        'status' => RaceEntryStatus::Completed->value,
    ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('month');

    expect(RaceEntry::where('training_run_id', $run->id)->count())->toBe(0)
        ->and(ScenarioSlot::where('kind', 'free_race')->count())->toBe(0);
});

/*
 * The redelivery half, driven through `withSession` rather than a second request.
 * `phpunit.xml:30` pins `SESSION_DRIVER=array`, which builds a fresh store per request, so a
 * flash put by the failed POST is gone by the time a following GET runs; a test that chained
 * the two would be asserting the session driver's amnesia, not the component's behaviour.
 * What the component actually promises is that a flashed `entry_mode` wins over the query
 * default, and that is what is supplied here.
 */
it('redelivers the manual branch with the mode and the entered title preserved', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    $this->withSession(['_old_input' => [
        'entry_mode' => 'manual',
        'title' => 'Midsummer Practice Race',
        'month' => 13,
        'half' => 'Early',
        'placement' => 4,
        'status' => RaceEntryStatus::Completed->value,
    ]])->get(route('runs.show', $run))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('racePanel.entryMode', 'manual')
        // R67 says the entered values come back, and the fields outside both branches are entered
        // values too: retyping a finish because a month was wrong is the friction being removed.
        ->where('racePanel.old.title', 'Midsummer Practice Race')
        ->where('racePanel.old.month', 13)
        ->where('racePanel.old.half', 'Early')
        ->where('racePanel.old.placement', 4)
        ->where('racePanel.old.status', RaceEntryStatus::Completed->value));
});

it('writes a free race through the rendered manual form, not a direct post', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    $page = $this->get(route('runs.show', ['run' => $run, 'entry_mode' => 'manual']))->assertOk();

    $page->assertInertia(fn (Assert $assert) => $assert
        ->component('Runs/Show')
        ->where('racePanel.entryMode', 'manual')
        // The action the form itself posts to, read out of the page payload rather than assumed.
        ->where('racePanel.racesUrl', route('runs.races.store', $run)));

    $response = $this->followingRedirects()->post(route('runs.races.store', $run), [
        'entry_mode' => $page->inertiaProps('racePanel.entryMode'),
        'title' => 'Autumn Practice Stakes',
        'month' => 9,
        'half' => 'Late',
        'status' => RaceEntryStatus::Completed->value,
        'placement' => 3,
    ]);

    $response->assertOk()->assertInertia(fn (Assert $assert) => $assert
        ->component('Runs/Show')
        // The row the form just wrote is readable back out of the same page, marker included.
        ->where('racePanel.entries', fn (Collection $entries) => $entries->contains(
            fn (array $entry): bool => $entry['title'] === 'Autumn Practice Stakes'
                && $entry['trainer_entered'] === true
        )));

    $slot = ScenarioSlot::where('kind', 'free_race')->where('title', 'Autumn Practice Stakes')->first();

    expect($slot)->not->toBeNull()
        ->and($slot->is_manual)->toBeTrue()
        ->and($slot->month)->toBe(9)
        ->and($slot->half)->toBe('Late')
        ->and($slot->tier)->toBeNull();
});

it('ships no Alpine at all in the race panel', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    // The disclosure is server state (`entryMode`), so nothing on this page needs Alpine, in the
    // shell or in the payload the panel reads.
    $response = $this->get(route('runs.show', $run));

    $response->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('racePanel.entryMode', 'calendar'));

    expect($response->content())->not->toContain('x-data')
        ->and($response->content())->not->toContain('x-if')
        ->and($response->content())->not->toContain('@click')
        ->and($response->content())->not->toContain('x-model');
});
