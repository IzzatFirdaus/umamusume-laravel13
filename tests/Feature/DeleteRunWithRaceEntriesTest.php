<?php

use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\Skill;
use App\Models\SkillLevel;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\TurnEvent;
use App\Models\Veteran;

it('deletes a run with race entries without a 500 (N3.3/N3.7 regression)', function (): void {
    $run = TrainingRun::factory()->create();
    $slot = RaceCatalogSlot::factory()->create();

    $turn = TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 1,
    ]);

    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $slot->id,
        'turn_entry_id' => $turn->id,
        'status' => 'Completed',
        'placement' => 1,
    ]);

    $response = $this->delete("/training-runs/{$run->id}", ['confirmed' => '1']);

    $response->assertRedirect(route('runs.index'));

    $this->assertDatabaseMissing('training_runs', ['id' => $run->id]);
    $this->assertDatabaseMissing('race_entries', ['training_run_id' => $run->id]);
    $this->assertDatabaseMissing('turn_entries', ['training_run_id' => $run->id]);
});

it('deletes a run whose race entries reference a cascade of dependent tables', function (): void {
    $run = TrainingRun::factory()->create();
    $slot = RaceCatalogSlot::factory()->create();
    $turn = TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1]);
    $veteran = Veteran::factory()->create(['training_run_id' => $run->id]);
    $skillLevel = SkillLevel::factory()->create(['training_run_id' => $run->id]);
    $skill = Skill::factory()->create();
    $run->skills()->sync([$skill->id => ['status' => 'acquired', 'turn_acquired' => 1]]);
    TurnEvent::factory()->create(['training_run_id' => $run->id, 'turn' => 1]);
    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $slot->id,
        'turn_entry_id' => $turn->id,
        'status' => 'Completed',
        'placement' => 1,
    ]);

    $response = $this->delete("/training-runs/{$run->id}", ['confirmed' => '1']);

    $response->assertRedirect(route('runs.index'));

    foreach (['training_runs', 'race_entries', 'turn_entries', 'turn_events', 'veterans', 'skill_levels', 'run_skills'] as $table) {
        $this->assertDatabaseMissing($table, ['training_run_id' => $run->id]);
    }
    $this->assertDatabaseMissing('veterans', ['id' => $veteran->id]);
    $this->assertDatabaseMissing('run_skills', ['training_run_id' => $run->id, 'skill_id' => $skill->id]);
});
