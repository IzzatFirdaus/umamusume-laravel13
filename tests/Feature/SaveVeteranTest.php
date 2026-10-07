<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use App\Models\Veteran;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * SCREEN-020, Save Veteran (plan §8 D16, the write half). The props and the write are asserted here;
 * the rendered copy, the keyboard tag toggle, the 44px sweep and the axe scan live in
 * `tests/browser/career-save-veteran.spec.ts`.
 *
 * **What this screen may not say.** No factor analysis, no legacy value and no best-use recommendation.
 * `screen-spec-2.0` §24's correction row asks for all three; `ADR-0020` §3 keeps them out of the record
 * screens and no column holds them either, so each arrives as a null with the reason beside it. Case (9)
 * asserts the two recommendation strings the brief itself writes are nowhere in the payload.
 *
 * **The run must be Completed**, which `RecordVeteran` already guards. The refusal is a validation error
 * rather than that guard escaping as a 500, because the Trainer who follows a stale link is the one who
 * has to be told what is wrong.
 */

function saveVeteranRun(string $name = 'Rice Shower', RunStatus $status = RunStatus::Completed, int $turns = 0): TrainingRun
{
    $run = TrainingRun::factory()->create([
        'status' => $status,
        'umamusume_id' => Umamusume::factory()->state(['name' => $name])->create()->id,
    ]);

    for ($turn = 1; $turn <= $turns; $turn++) {
        TurnEntry::factory()->create([
            'training_run_id' => $run->id,
            'turn' => $turn,
            'speed' => 300 + $turn,
            'stamina' => 200 + $turn,
            'power' => 150 + $turn,
            'guts' => 120 + $turn,
            'wit' => 100 + $turn,
        ]);
    }

    return $run->fresh();
}

it('renders the save screen with the career it is filing', function (): void {
    $run = saveVeteranRun(turns: 2);

    $this->get(route('runs.veteran', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/SaveVeteran')
            ->where('run.id', $run->id)
            ->where('run.trainee', 'Rice Shower')
            ->where('run.status', 'Completed')
            ->where('run.recordable', true)
            ->where('blocked', null)
            // The latest logged turn, never a sum of deltas (ADR-0003). Turn 2 of the fixture.
            ->where('stats', ['Speed' => 302, 'Stamina' => 202, 'Power' => 152, 'Guts' => 122, 'Wit' => 102])
            ->has('suggested_tags', 5)
            ->where('suggested_tags.Stat', ['Speed', 'Stamina', 'Power', 'Guts', 'Wit'])
            ->where('saved', null)
            ->where('notice', fn (string $notice): bool => str_contains($notice, 'Record only'))
        );
});

it('shows the recorded sparks read-only so the Trainer reviews them before tagging', function (): void {
    $run = saveVeteranRun();
    $run->update([
        'legacy_selection' => [
            'affinity' => '◎',
            'legacies' => [
                ['rank' => 3, 'is_guest' => false, 'ancestors' => ['Grass Wonder', 'Mill Raptor'], 'sparks' => [['kind' => 'blue', 'target' => 'Speed', 'stars' => 2]]],
                ['rank' => null, 'is_guest' => true, 'ancestors' => ['Mayano Top Gun', ''], 'sparks' => []],
            ],
        ],
    ]);

    $this->get(route('runs.veteran', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('graph.parents', 2)
            ->where('graph.parents.0.sparks', fn ($sparks): bool => count($sparks) === 1
                && $sparks[0]['kind'] === 'blue'
                && $sparks[0]['target'] === 'Speed'
                && $sparks[0]['stars'] === 2)
            ->where('graph.parents.1.is_guest', true)
            ->where('graph.parents.1.rank', null)
            ->has('spark_kinds', 5));
});

it('states the three held figures as null with the ruling beside each, and carries no recommendation', function (): void {
    $run = saveVeteranRun();

    $response = $this->get(route('runs.veteran', $run));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('held.factor_analysis.value', null)
        ->where('held.legacy_value.value', null)
        ->where('held.best_use.value', null)
        ->where('held.factor_analysis.title', fn (string $title): bool => str_contains($title, 'ADR-0020'))
        ->where('absences', fn ($absences): bool => count($absences) >= 1));

    // The two strings `screen-spec-2.0` §24 asks the screen to print must not appear anywhere in it.
    $payload = json_decode($response->getContent(), true);
    expect(json_encode($payload))->not->toContain('Excellent Medium parent')
        ->and(json_encode($payload))->not->toContain('Best used for');
});

it('prefills the form from a Veteran row that already exists for the run', function (): void {
    $run = saveVeteranRun();
    Veteran::factory()->create([
        'training_run_id' => $run->id,
        'tags' => ['Mile', 'Turf'],
        'notes' => 'Ran the spring tour twice.',
    ]);

    $this->get(route('runs.veteran', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('saved.tags', ['Mile', 'Turf'])
            ->where('saved.notes', 'Ran the spring tour twice.'));
});

it('refuses a run that is not Completed instead of offering a form that cannot save', function (): void {
    $run = saveVeteranRun(status: RunStatus::Active);

    $this->get(route('runs.veteran', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('run.recordable', false)
            ->where('blocked', fn (?string $blocked): bool => is_string($blocked) && str_contains($blocked, 'Completed')));
});

it('files the run as a Veteran with the tags and notes the Trainer entered', function (): void {
    $run = saveVeteranRun();

    $response = $this->post(route('runs.veteran.store', $run), [
        'tags' => ['Medium', 'Pace Chaser', 'Custom: quiet early pace'],
        'notes' => 'Ran the autumn route.',
    ]);

    $veteran = Veteran::query()->where('training_run_id', $run->id)->sole();

    $response->assertRedirect(route('veterans.show', $veteran));

    expect($veteran->tags)->toBe(['Medium', 'Pace Chaser', 'Custom: quiet early pace'])
        ->and($veteran->notes)->toBe('Ran the autumn route.');
});

it('trims and de-duplicates tags and drops the blanks, so one tag is not stored three ways', function (): void {
    $run = saveVeteranRun();

    $this->post(route('runs.veteran.store', $run), [
        'tags' => ['  Turf  ', 'turf', '', '   ', 'Speed'],
    ])->assertSessionHasNoErrors();

    expect(Veteran::query()->where('training_run_id', $run->id)->sole()->tags)->toBe(['Turf', 'Speed']);
});

it('rewrites the same row on a second save rather than filing the career twice', function (): void {
    $run = saveVeteranRun();

    $this->post(route('runs.veteran.store', $run), ['tags' => ['Mile']])->assertSessionHasNoErrors();
    $first = Veteran::query()->where('training_run_id', $run->id)->sole()->id;
    $this->post(route('runs.veteran.store', $run), ['tags' => ['Long'], 'notes' => 'Rewritten.'])->assertSessionHasNoErrors();

    expect(Veteran::query()->where('training_run_id', $run->id)->count())->toBe(1)
        ->and(Veteran::find($first)->tags)->toBe(['Long'])
        ->and(Veteran::find($first)->notes)->toBe('Rewritten.');
});

it('prints no stat at all for a completed run that logged no turn, rather than five zeros', function (): void {
    $run = saveVeteranRun('Agnes Tachyon');

    $this->get(route('runs.veteran', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('stats', ['Speed' => null, 'Stamina' => null, 'Power' => null, 'Guts' => null, 'Wit' => null])
            ->where('counts.skills', 0)
            ->where('counts.races', 0));
});

it('refuses a run that is not Completed at the write boundary and files nothing', function (): void {
    $run = saveVeteranRun(status: RunStatus::Retired);

    $this->post(route('runs.veteran.store', $run), ['tags' => ['Mile']])
        ->assertSessionHasErrors('run');

    expect(Veteran::query()->where('training_run_id', $run->id)->count())->toBe(0);
});

it('refuses oversized, malformed and overflowing tag and note input', function (string $field, array $data, string $error): void {
    $run = saveVeteranRun("Refusal $field");

    $this->post(route('runs.veteran.store', $run), $data)->assertSessionHasErrors($error);

    expect(Veteran::query()->where('training_run_id', $run->id)->count())->toBe(0);
})->with([
    'a tag longer than 40 characters' => ['tags', ['tags' => [str_repeat('t', 41)]], 'tags.0'],
    'more than twenty tags' => ['tags', ['tags' => array_map(fn (int $i): string => "Tag $i", range(1, 21))], 'tags'],
    'a control character inside a tag' => ['tags', ['tags' => ["Bad\x07tag"]], 'tags.0'],
    'a note longer than 2000 characters' => ['notes', ['notes' => str_repeat('n', 2001)], 'notes'],
    'a non-string tag' => ['tags', ['tags' => [['nested']]], 'tags.0'],
]);

it('accepts a tag at the exact bound of every limit, so the bounds are the tested ones', function (): void {
    $run = saveVeteranRun();

    $this->post(route('runs.veteran.store', $run), [
        'tags' => array_map(fn (int $i): string => "Tag $i", range(1, 20)),
        'notes' => str_repeat('n', 2000),
    ])->assertSessionHasNoErrors();

    $veteran = Veteran::query()->where('training_run_id', $run->id)->sole();
    expect($veteran->tags)->toHaveCount(20)->and(strlen((string) $veteran->notes))->toBe(2000);
});
