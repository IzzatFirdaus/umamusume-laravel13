<?php

declare(strict_types=1);

use App\Models\Skill;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;

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
