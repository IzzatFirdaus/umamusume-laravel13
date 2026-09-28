<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\TrainingRun;

/*
 * R67: the two-path race form is server-driven disclosure, exactly as guided-step does it.
 * R71: the assertion is on the rendered DOM, not the HTML source. A `template x-if` branch
 * is emitted by Blade and then made inert by the browser, so `assertSee('name="title"')`
 * passes against markup no Trainer can reach. Every check here resolves the field through
 * the DOM and refuses a field whose ancestor chain contains a `template` element.
 */

/**
 * The `name` attributes of every form field the browser can actually reach: not inside a
 * `<template>`, not inside a `<script>`.
 *
 * @return list<string>
 */
function reachableFieldNames(string $html): array
{
    $dom = new DOMDocument;
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    $names = [];

    foreach ($xpath->query('//input[@name] | //select[@name] | //textarea[@name]') as $field) {
        $insideTemplate = $xpath->query('ancestor::template', $field)->length > 0;
        $insideScript = $xpath->query('ancestor::script', $field)->length > 0;

        if (! $insideTemplate && ! $insideScript) {
            $names[] = (string) $field->getAttribute('name');
        }
    }

    return $names;
}

/**
 * The `value` of the hidden entry_mode field the record form submits, or null when absent.
 */
function submittedEntryMode(string $html): ?string
{
    $dom = new DOMDocument;
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    foreach ($xpath->query('//input[@name="entry_mode"]') as $input) {
        if ($xpath->query('ancestor::template', $input)->length > 0) {
            continue;
        }

        $form = $xpath->query('ancestor::form', $input)->item(0);

        // The disclosure control is a GET form that carries the mode as its own input; the
        // record form is the POST. Only the POST's value is what a validation redelivery
        // must preserve.
        if ($form instanceof DOMElement && strtolower($form->getAttribute('method')) === 'post') {
            return $input->getAttribute('value');
        }
    }

    return null;
}

it('renders the calendar branch reachable, with no manual fields in the page', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'goal_race',
        'source_key' => 'src-1',
        'title' => 'Japanese Oaks',
        'is_manual' => false,
    ]);

    $html = $this->get(route('runs.show', $run))->content();
    $names = reachableFieldNames($html);

    expect($names)->toContain('scenario_slot_id')
        ->and($names)->not->toContain('title')
        ->and($names)->not->toContain('month')
        ->and($names)->not->toContain('half');
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

    $html = $this->get(route('runs.show', $run))->content();

    expect(submittedEntryMode($html))->toBe('calendar');
});

it('renders the manual branch reachable when the disclosure switches the mode', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    $html = $this->get(route('runs.show', ['run' => $run, 'entry_mode' => 'manual']))->content();
    $names = reachableFieldNames($html);

    expect($names)->toContain('title')
        ->and($names)->toContain('month')
        ->and($names)->toContain('half')
        ->and($names)->not->toContain('scenario_slot_id');
});

it('carries a real entry_mode value in the manual branch', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    $html = $this->get(route('runs.show', ['run' => $run, 'entry_mode' => 'manual']))->content();

    expect(submittedEntryMode($html))->toBe('manual');
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
 * The redelivery half, driven through `withFlashInput` rather than a second request.
 * `phpunit.xml:30` pins `SESSION_DRIVER=array`, which builds a fresh store per request, so a
 * flash put by the failed POST is gone by the time a following GET runs; a test that chained
 * the two would be asserting the session driver's amnesia, not the component's behaviour.
 * What the component actually promises is that a flashed `entry_mode` wins over the query
 * default, and that is what is supplied here.
 */
it('redelivers the manual branch with the mode and the entered title preserved', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    $html = $this->withSession(['_old_input' => [
        'entry_mode' => 'manual',
        'title' => 'Midsummer Practice Race',
        'month' => 13,
        'half' => 'Early',
        'placement' => 4,
        'status' => RaceEntryStatus::Completed->value,
    ]])->get(route('runs.show', $run))->content();

    $names = reachableFieldNames($html);

    expect($names)->toContain('title')
        ->and($names)->toContain('month')
        ->and($names)->not->toContain('scenario_slot_id')
        ->and(submittedEntryMode($html))->toBe('manual')
        ->and($html)->toContain('Midsummer Practice Race');

    // R67 says the entered values come back, and the fields outside both branches are entered
    // values too: retyping a finish because a month was wrong is the friction being removed.
    $dom = new DOMDocument;
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    expect($xpath->query('//input[@name="placement"][@value="4"]')->length)->toBe(1)
        ->and($xpath->query('//select[@name="status"]/option[@selected][@value="'.RaceEntryStatus::Completed->value.'"]')->length)->toBe(1);
});

it('writes a free race through the rendered manual form, not a direct post', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    $manualHtml = $this->get(route('runs.show', ['run' => $run, 'entry_mode' => 'manual']))->content();

    // The form the Trainer is looking at, read out of the rendered page rather than assumed.
    // Attribute order is not part of the contract, so both orders are accepted.
    $storeRoute = preg_quote(route('runs.races.store', $run), '/');

    expect($manualHtml)->toMatch('/<form(?=[^>]*method="POST")(?=[^>]*action="'.$storeRoute.'")[^>]*>/i')
        ->and(reachableFieldNames($manualHtml))->toContain('title');

    $response = $this->followingRedirects()->post(route('runs.races.store', $run), [
        'entry_mode' => submittedEntryMode($manualHtml),
        'title' => 'Autumn Practice Stakes',
        'month' => 9,
        'half' => 'Late',
        'status' => RaceEntryStatus::Completed->value,
        'placement' => 3,
    ]);

    $response->assertOk();

    $slot = ScenarioSlot::where('kind', 'free_race')->where('title', 'Autumn Practice Stakes')->first();

    expect($slot)->not->toBeNull()
        ->and($slot->is_manual)->toBeTrue()
        ->and($slot->month)->toBe(9)
        ->and($slot->half)->toBe('Late')
        ->and($slot->tier)->toBeNull();

    // And the row the form just wrote is readable back out of the same page.
    expect($response->content())->toContain('Autumn Practice Stakes')
        ->and($response->content())->toContain('Trainer-entered');
});

it('ships no Alpine at all in the race panel', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    $html = $this->get(route('runs.show', $run))->content();

    expect($html)->not->toContain('x-data')
        ->and($html)->not->toContain('x-if')
        ->and($html)->not->toContain('@click')
        ->and($html)->not->toContain('x-model');
});
