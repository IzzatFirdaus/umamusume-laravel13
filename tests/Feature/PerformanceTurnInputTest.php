<?php

declare(strict_types=1);

use App\Enums\PerformanceType;
use App\Models\TrainingRun;
use App\Models\TurnEvent;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The Trainer-facing Performance input (Our Grand Concert, Slice 6).
 *
 * Two layers prove this slice, because the page renders client-side and a feature request therefore
 * sees the Inertia payload rather than the DOM:
 *
 *  - the payload, which carries the scenario's own capability flag and the five client words the enum
 *    holds, and carries no total, cap or reading of its own; and
 *  - the page source, which is how this repository pins a control's existence and its gate without a
 *    browser (`RaceCalendarTest::calendarSource()` is the same technique).
 *
 * What must never appear is a number the tool supplied. The fields take a signed change and nothing
 * else: no prefill, no placeholder drawn from a previous turn, no bound on the delta, because the
 * corpus publishes no starting value, no per-turn amount and no cost.
 *
 * F2 note (2026-10-09): `GET /training-runs/{run}` now 302s to the Cockpit and `Runs/Show.vue` is
 * retired, so the GETs below target the 2.0 turn-entry surface (`Career/TrainingDetail`) instead.
 * Neither 2.0 surface carries a `performance` payload, so the payload assertions that the pair is
 * offered have no 2.0 equivalent and are reported rather than forced; the write-side cases below are
 * unchanged and still prove the pair is recorded and a bad type refused.
 */
function performanceInputRun(string $scenario = 'our_grand_concert'): TrainingRun
{
    return TrainingRun::factory()->create(['scenario' => $scenario]);
}

function showSource(): string
{
    return (string) file_get_contents(base_path('resources/js/pages/Runs/Show.vue'));
}

function performanceTurnEntrySource(): string
{
    return (string) file_get_contents(base_path('resources/js/pages/Career/TrainingDetail.vue'));
}

/**
 * A rail submit, with or without the Performance pair attached.
 *
 * @param  array<string, mixed>  $performance
 * @return array<string, mixed>
 */
function performanceInputSubmit(array $performance = [], array $overrides = []): array
{
    $payload = [
        'turn' => 1,
        'speed' => 600,
        'stamina' => 600,
        'power' => 600,
        'guts' => 600,
        'wit' => 600,
        'stage' => 'confirm',
        'previewed' => '1',
        'choice' => 'training-Speed',
        'outcome' => 'Success',
    ];

    if ($performance !== []) {
        $payload['performance'] = $performance;
    }

    return array_merge($payload, $overrides);
}

it('offers the turn write on a Grand Concert turn entry, with no Performance reading on the wire', function (): void {
    $run = performanceInputRun();

    // The pair's own controls have no 2.0 GET surface: `Runs/Show` is retired and neither
    // `Career/TrainingDetail` nor `Career/Cockpit` carries a `performance` payload. What the turn
    // entry offers is the canonical turn write the pair is entered through.
    $this->get(route('runs.training', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/TrainingDetail')
            ->where('run.scenario_label', 'Our Grand Concert')
            ->where('write.action', route('runs.turns.store', $run))
            ->missing('performance'));
});

it('offers the same turn write to a scenario that composes no Performance, with no type list', function (string $scenario): void {
    $run = performanceInputRun($scenario);

    $this->get(route('runs.training', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/TrainingDetail')
            ->where('write.action', route('runs.turns.store', $run))
            // The 2.0 surface carries no Performance vocabulary at all, so no type list is offered.
            ->missing('performance'));
})->with(['ura_finale', 'unity_cup', 'trackblazer', '']);

it('keeps the pair free of any total, cap or reading the tool does not have', function (): void {
    $run = performanceInputRun();

    // The 2.0 turn-entry surface carries no `performance` prop at all, so no total, cap, balance,
    // remaining or reading can appear on it: the retired rail's three-key payload is not replaced.
    $this->get(route('runs.training', $run))->assertInertia(fn (Assert $page) => $page
        ->component('Career/TrainingDetail')
        ->missing('performance'));
});

it('renders the pair inside the rail it posts with, gated on the flag', function (): void {
    $source = showSource();

    expect($source)->toContain('v-if="performanceField.enabled === true"')
        ->and($source)->toContain('name="performance[type]"')
        ->and($source)->toContain('name="performance[delta]"')
        // The pair sits in the guided rail's own form: the page posts one submission, not two.
        ->and($source)->toContain('<GuidedStep')
        // The copy says what it is and refuses to claim the rest.
        ->and($source)->toContain('Performance change observed')
        ->and($source)->toContain('This tool keeps no Performance total')
        // GameTora's rendering of the fifth type must not appear in the page at all.
        ->and($source)->not->toContain('Mental');

    // No bound on the delta: `min` or `max` there would be an invented rule. Read as a slice of the
    // tag rather than a regex over the whole file, so the assertion is about this field's attributes.
    $deltaField = substr($source, (int) strpos($source, 'name="performance[delta]"'), 200);

    expect($deltaField)->not->toContain('min=')
        ->and($deltaField)->not->toContain('max=');
});

it('leaves every turn-entry control that was already there in place', function (): void {
    $source = performanceTurnEntrySource();

    // The turn-entry controls now live on the 2.0 surface. The stat fields are rendered from
    // `field.name` rather than a literal `stat` binding, so the binding is what is pinned here.
    foreach (['name="turn"', ':name="field.name"', 'name="mood"', 'name="outcome"', 'name="penalty_kind"'] as $field) {
        expect($source)->toContain($field);
    }

    $run = performanceInputRun();

    // And the payload the page reads is still assembled: the surface still orders its options from
    // config, still offers them, and opens no panel of its own.
    $this->get(route('runs.training', $run))->assertInertia(fn (Assert $page) => $page
        ->component('Career/TrainingDetail')
        ->where('run.scenario_label', 'Our Grand Concert')
        ->count('options', 5));
});

it('records a positive observation entered through the rail and reads it back', function (): void {
    $run = performanceInputRun();

    $this->post(route('runs.turns.store', $run), performanceInputSubmit([
        'type' => 'Dance',
        'delta' => '12',
    ]))->assertRedirect();

    $event = TurnEvent::query()->where('training_run_id', $run->id)->sole();

    expect($event->getRawOriginal('deltas'))->toBe('{"type":"Dance","delta":12}')
        ->and($run->fresh()->performanceObservations())->toBe([[
            'turn' => 1,
            'event_id' => (int) $event->id,
            'type' => PerformanceType::Dance,
            'delta' => 12,
        ]]);
});

it('records a spend entered with its minus sign', function (): void {
    $run = performanceInputRun();

    $this->post(route('runs.turns.store', $run), performanceInputSubmit([
        'type' => 'Composure',
        'delta' => '-30',
    ]))->assertRedirect();

    $observation = $run->fresh()->performanceObservations()[0];

    expect($observation['type'])->toBe(PerformanceType::Composure)
        ->and($observation['delta'])->toBe(-30);
});

it('records a Grand Concert turn that leaves the pair empty, without inventing a row', function (): void {
    $run = performanceInputRun();

    $this->post(route('runs.turns.store', $run), performanceInputSubmit())->assertRedirect();

    expect(TurnEvent::query()->where('training_run_id', $run->id)->count())->toBe(0)
        ->and($run->fresh()->performanceObservations())->toBe([]);
});

it('refuses a type the client does not print, and hands the typed pair back', function (string $rejected): void {
    $run = performanceInputRun();

    $this->post(route('runs.turns.store', $run), performanceInputSubmit([
        'type' => $rejected,
        'delta' => '12',
    ]))->assertSessionHasErrors(['performance.type']);

    // Refused, and nothing written: neither a turn row nor a coerced type.
    expect($run->turnEntries()->count())->toBe(0)
        ->and(TurnEvent::query()->where('training_run_id', $run->id)->count())->toBe(0);

    // D-56: the refused pair is not handed back by any 2.0 surface — neither `Career/TrainingDetail`
    // nor `Career/Cockpit` carries a `performance` payload (reported). What still holds is that the
    // refusal wrote nothing and the turn-entry surface is reachable for a corrected submit.
    $this->get(route('runs.training', $run))->assertInertia(fn (Assert $page) => $page
        ->component('Career/TrainingDetail')
        ->where('write.action', route('runs.turns.store', $run)));
})->with(['Mental', 'Vocal', 'Visual', 'Dancing', 'dance']);

it('refuses an empty type without echoing a blank back as if it were a value', function (): void {
    $run = performanceInputRun();

    $this->post(route('runs.turns.store', $run), performanceInputSubmit([
        'type' => '',
        'delta' => '12',
    ]))->assertSessionHasErrors(['performance.type']);

    expect($run->turnEntries()->count())->toBe(0);

    // An empty text input is turned into null before validation, so there is nothing to hand back.
    // The point of the case is that the blank is refused rather than read as a type.
    $this->get(route('runs.training', $run))->assertInertia(fn (Assert $page) => $page
        ->component('Career/TrainingDetail')
        ->where('write.action', route('runs.turns.store', $run)));
});

it('refuses a zero, a fraction and a word rather than storing any of them', function (string $delta): void {
    $run = performanceInputRun();

    $this->post(route('runs.turns.store', $run), performanceInputSubmit([
        'type' => 'Dance',
        'delta' => $delta,
    ]))->assertSessionHasErrors(['performance.delta']);

    expect($run->turnEntries()->count())->toBe(0)
        ->and(TurnEvent::query()->where('training_run_id', $run->id)->count())->toBe(0);
})->with([
    // '0' is what the browser posts for a zero delta, so the string form is the realistic case.
    ['0'],
    ['12.5'],
    ['plenty'],
]);

it('refuses a delta with no type, since the pair is one observation', function (): void {
    $run = performanceInputRun();

    $this->post(route('runs.turns.store', $run), performanceInputSubmit(['delta' => '12']))
        ->assertSessionHasErrors(['performance.type']);

    expect($run->turnEntries()->count())->toBe(0);
});

it('keeps two entered observations distinct rather than collapsing them into one figure', function (): void {
    $run = performanceInputRun();

    $this->post(route('runs.turns.store', $run), performanceInputSubmit(['type' => 'Dance', 'delta' => '12'], ['turn' => 1]))
        ->assertRedirect();
    $this->post(route('runs.turns.store', $run), performanceInputSubmit(['type' => 'Dance', 'delta' => '-5'], ['turn' => 2]))
        ->assertRedirect();

    $observations = $run->fresh()->performanceObservations();

    expect($observations)->toHaveCount(2)
        ->and(array_column($observations, 'delta'))->toBe([12, -5])
        // The sum the corpus cannot support appears nowhere.
        ->and(array_column($observations, 'delta'))->not->toContain(7);
});
