<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

it('has no foreign key that references a table which does not exist', function (): void {
    $tables = collect(DB::select(
        "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'",
    ))->pluck('name');

    foreach ($tables as $table) {
        $foreignKeys = DB::select("PRAGMA foreign_key_list('".$table."')");

        foreach ($foreignKeys as $foreignKey) {
            expect($tables->all())->toContain($foreignKey->table);
        }
    }
});

it('deletes a run that carries roster members through the confirmation door', function (): void {
    /** @var TestCase $this */
    $run = TrainingRun::factory()->create();

    DB::table('roster_members')->insert([
        'training_run_id' => $run->id,
        'name' => 'Recorded teammate',
        'position' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->delete(route('runs.destroy', $run), ['confirmed' => '1'])
        ->assertRedirect(route('runs.index'));

    expect(DB::table('training_runs')->where('id', $run->id)->count())->toBe(0);
    expect(DB::table('roster_members')->where('training_run_id', $run->id)->count())->toBe(0);
});

it('refuses an unconfirmed delete and leaves the run and its roster intact', function (): void {
    /** @var TestCase $this */
    $run = TrainingRun::factory()->create();

    DB::table('roster_members')->insert([
        'training_run_id' => $run->id,
        'name' => 'Recorded teammate',
        'position' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->delete(route('runs.destroy', $run))
        ->assertRedirect(route('runs.confirm-delete', $run))
        ->assertSessionHasErrors('confirmed');

    expect(DB::table('training_runs')->where('id', $run->id)->count())->toBe(1);
    expect(DB::table('roster_members')->where('training_run_id', $run->id)->count())->toBe(1);
});
