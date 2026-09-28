<?php

declare(strict_types=1);

use App\Models\Skill;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;

/*
 * Run CRUD, turn recording, turn editing, export and delete.
 *
 * The Phase 3A mapping named this file's concerns as `CreateRunTest`, `TurnEditingTest`
 * and `ExportFormatTest` - three files that do not exist, and creating them would have
 * split one surface's coverage across four files that could then drift. They resolve
 * here instead:
 *
 *   CreateRunTest      -> "creates a run and renders logged turns in order" (below)
 *   TurnEditingTest    -> "rejects a duplicate turn number", "rejects a stat above the cap",
 *                         and the turn edit/remove cases at the foot of this file
 *   ExportFormatTest   -> "exports a run as csv and json" (below)
 *
 * The mapping pointed `TurnEditingTest` at the two rejection tests above, which are
 * *create* validations and cover no edit at all: `updateTurn` and `destroyTurn` were
 * reached by nothing in the suite. The cases at the foot of this file close that.
 */

it('creates a run and renders logged turns in order', function (): void {
    $umamusume = Umamusume::factory()->create();

    test()->post('/training-runs', [
        'umamusume_id' => $umamusume->id,
        'status' => 'Active',
    ])->assertRedirect();

    $run = TrainingRun::firstOrFail();

    test()->post("/training-runs/{$run->id}/turns", [
        'turn' => 1, 'speed' => 100, 'stamina' => 90, 'power' => 110, 'guts' => 80, 'wit' => 95,
    ])->assertRedirect();

    test()->post("/training-runs/{$run->id}/turns", [
        'turn' => 2, 'speed' => 150, 'stamina' => 90, 'power' => 110, 'guts' => 80, 'wit' => 95, 'sp' => 40,
    ])->assertRedirect();

    test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->assertSeeInOrder(['Turn', '150']);
});

it('rejects a duplicate turn number for the same run', function (): void {
    $run = TrainingRun::factory()->create();

    $payload = ['turn' => 1, 'speed' => 10, 'stamina' => 10, 'power' => 10, 'guts' => 10, 'wit' => 10];

    test()->post("/training-runs/{$run->id}/turns", $payload)->assertRedirect();

    test()->post("/training-runs/{$run->id}/turns", $payload)
        ->assertSessionHasErrors('turn');
});

it('rejects a stat above the 1200 cap', function (): void {
    $run = TrainingRun::factory()->create();

    test()->post("/training-runs/{$run->id}/turns", [
        'turn' => 1, 'speed' => 1500, 'stamina' => 10, 'power' => 10, 'guts' => 10, 'wit' => 10,
    ])->assertSessionHasErrors('speed');
});

it('records suggested acquired and skipped skills on a run', function (): void {
    $run = TrainingRun::factory()->create();
    $suggested = Skill::factory()->create(['name' => 'Certain Victory']);
    $taken = Skill::factory()->create(['name' => '1st Place Kiss☆']);
    $missed = Skill::factory()->create(['name' => 'Feel the Burn!']);

    test()->post("/training-runs/{$run->id}/skills", [
        'skills' => [
            ['skill_id' => $suggested->id, 'status' => 'Suggested'],
            ['skill_id' => $taken->id, 'status' => 'Acquired', 'turn_acquired' => 3],
            ['skill_id' => $missed->id, 'status' => 'Skipped'],
        ],
    ])->assertRedirect();

    $run->load('skills');

    expect($run->skills->count())->toBe(3);

    test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->assertSee('Certain Victory')
        ->assertSee('1st Place Kiss☆')
        ->assertSee('Feel the Burn!');
});

it('exports a run as csv and json', function (): void {
    $run = TrainingRun::factory()->create();
    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 1,
        'speed' => 100,
    ]);

    $csv = test()->get("/training-runs/{$run->id}/export/csv");
    $csv->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
        ->assertSee('turn,speed,stamina,power,guts,wit,sp,condition');

    $json = test()->get("/training-runs/{$run->id}/export/json");
    $json->assertOk();
    expect($json->json('data.turns.0.turn'))->toBe(1);
});

it('deletes a run with its turns', function (): void {
    $run = TrainingRun::factory()->create();
    TurnEntry::factory()->create(['training_run_id' => $run->id]);

    test()->delete("/training-runs/{$run->id}")->assertRedirect('/training-runs');

    expect(TrainingRun::count())->toBe(0)
        ->and(TurnEntry::count())->toBe(0);
});

it('edits a logged turn in place, keeping its number', function (): void {
    $run = TrainingRun::factory()->create();
    $turn = TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 2, 'speed' => 100]);

    test()->put("/training-runs/{$run->id}/turns/{$turn->id}", [
        'turn' => 2, 'speed' => 400, 'stamina' => 90, 'power' => 110, 'guts' => 80, 'wit' => 95,
    ])->assertRedirect(route('runs.show', $run));

    // An edit rewrites the row a Trainer misread off the client, so it is an update and
    // not a delete-and-reinsert: the id is the same row and the turn number is the same
    // number, because a renumber here would silently re-order the run.
    expect($turn->refresh()->speed)->toBe(400)
        ->and($turn->turn)->toBe(2)
        ->and(TurnEntry::query()->where('training_run_id', $run->id)->count())->toBe(1);
});

it('applies the same bounds to an edit as to a create', function (): void {
    $run = TrainingRun::factory()->create();
    $turn = TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1, 'speed' => 100]);

    test()->put("/training-runs/{$run->id}/turns/{$turn->id}", [
        'turn' => 1, 'speed' => 1500, 'stamina' => 90, 'power' => 110, 'guts' => 80, 'wit' => 95,
    ])->assertSessionHasErrors('speed');

    // Shared FormRequest, so the refusal has to be shared too: an edit path with laxer
    // bounds would be the way a 9000 Speed stat gets into a run.
    expect($turn->refresh()->speed)->toBe(100);
});

it('refuses to move a turn onto a number the run already uses', function (): void {
    $run = TrainingRun::factory()->create();
    $first = TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1, 'speed' => 100]);
    $second = TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 2, 'speed' => 200]);

    test()->put("/training-runs/{$run->id}/turns/{$second->id}", [
        'turn' => 1, 'speed' => 250, 'stamina' => 90, 'power' => 110, 'guts' => 80, 'wit' => 95,
    ])->assertSessionHasErrors('turn');

    expect($second->refresh()->turn)->toBe(2);
});

it('will not edit a turn belonging to another run', function (): void {
    $run = TrainingRun::factory()->create();
    $other = TrainingRun::factory()->create();
    $foreign = TurnEntry::factory()->create(['training_run_id' => $other->id, 'turn' => 1, 'speed' => 100]);

    // Route binding resolves the turn on its own key, so without the controller's
    // `training_run_id` check this would be a Trainer editing another run's data by
    // editing its URL.
    test()->put("/training-runs/{$run->id}/turns/{$foreign->id}", [
        'turn' => 1, 'speed' => 400, 'stamina' => 90, 'power' => 110, 'guts' => 80, 'wit' => 95,
    ])->assertNotFound();

    expect($foreign->refresh()->speed)->toBe(100);
});

it('removes one turn and leaves the rest of the run in order', function (): void {
    $run = TrainingRun::factory()->create();
    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1, 'speed' => 100]);
    $doomed = TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 2, 'speed' => 200]);
    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 3, 'speed' => 300]);

    test()->delete("/training-runs/{$run->id}/turns/{$doomed->id}")
        ->assertRedirect(route('runs.show', $run));

    expect(TurnEntry::query()->where('training_run_id', $run->id)->orderBy('turn')->pluck('speed')->all())
        ->toBe([100, 300]);
});

it('will not remove a turn belonging to another run', function (): void {
    $run = TrainingRun::factory()->create();
    $other = TrainingRun::factory()->create();
    $foreign = TurnEntry::factory()->create(['training_run_id' => $other->id, 'turn' => 1]);

    test()->delete("/training-runs/{$run->id}/turns/{$foreign->id}")->assertNotFound();

    expect(TurnEntry::query()->whereKey($foreign->id)->exists())->toBeTrue();
});

it('404s an export format this build does not have', function (): void {
    $run = TrainingRun::factory()->create();

    // Excel was cut in the Pre-Mortem (FR-C-5), so `xlsx` has to be a 404 rather than a
    // CSV under a spreadsheet name: a Trainer who exports xlsx and opens it in a
    // spreadsheet would be reading a file that claims to be one thing and is another.
    test()->get("/training-runs/{$run->id}/export/xlsx")->assertNotFound();
});

it('sends the export as a download named for the run', function (): void {
    $run = TrainingRun::factory()->create();
    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1, 'speed' => 100]);

    test()->get("/training-runs/{$run->id}/export/csv")
        ->assertOk()
        ->assertHeader('Content-Disposition', "attachment; filename=\"run-{$run->id}.csv\"");
});
