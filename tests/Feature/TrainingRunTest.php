<?php

declare(strict_types=1);

use App\Enums\ReleaseStatus;
use App\Enums\SkillAcquisition;
use App\Models\CharacterCard;
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

/*
 * KI-33: a run started on a costume card pre-populates its `Suggested` skills from that card's own
 * innate and unique lists. Before this the lists were published by the source and stored nowhere, so
 * D-44's Suggested-before-the-run state had nothing to seed from and the Skills section read
 * "None." on a trainee who ships with four skills.
 *
 * Every row offered here resolves through `Skill::scopeAvailableOnGlobal()`, because a card's list is
 * not a Global statement: KI-33 names Fenomeno's card `112701` as holding skill data while
 * `release_en` is null. The pre-populate inherits the same read-path filter the picker and Screen D
 * use, so a Global Trainer is never offered a skill their client cannot show.
 */
function ki33GlobalSkill(int $exportId): Skill
{
    return Skill::create([
        'export_id' => $exportId,
        'name' => 'Ki33 Skill '.$exportId,
        'match_key' => 'ki33skill'.$exportId,
        'release_status' => ReleaseStatus::GlobalReleased->value,
        'name_is_client' => true,
    ]);
}

function ki33JapanOnlySkill(int $exportId): Skill
{
    return Skill::create([
        'export_id' => $exportId,
        'name' => 'Ki33 JP Skill '.$exportId,
        'match_key' => 'ki33jpskill'.$exportId,
        'release_status' => ReleaseStatus::JapanOnly->value,
        'name_is_client' => false,
    ]);
}

function ki33Card(?array $innate, ?array $unique): CharacterCard
{
    return CharacterCard::factory()->create([
        'umamusume_id' => Umamusume::factory()->create()->id,
        'skills_innate' => $innate,
        'skills_unique' => $unique,
    ]);
}

it('pre-populates the chosen card\'s innate and unique skills as Suggested', function (): void {
    $innate = [ki33GlobalSkill(200512), ki33GlobalSkill(201352), ki33GlobalSkill(200732)];
    $unique = ki33GlobalSkill(100011);
    $card = ki33Card([200512, 201352, 200732], [100011]);

    $this->post('/training-runs', [
        'umamusume_id' => $card->umamusume_id,
        'character_card_id' => $card->id,
        'status' => 'Active',
    ])->assertSessionHasNoErrors();

    $run = TrainingRun::firstWhere('character_card_id', $card->id);

    expect($run)->not->toBeNull()
        ->and($run->skills()->count())->toBe(4)
        // Every one of them suggested, none of them acquired: turn one has not happened.
        ->and($run->skills()->pluck('run_skills.status')->unique()->all())->toBe([SkillAcquisition::Suggested->value])
        ->and($run->skills()->pluck('run_skills.turn_acquired')->unique()->all())->toBe([null])
        ->and($run->skills()->pluck('skills.export_id')->sort()->values()->all())
        ->toBe([100011, 200512, 200732, 201352]);

    // The rows are the catalogue's own, not copies: the same skill id the picker would offer.
    expect($run->skills->pluck('id')->all())->toContain($innate[0]->id, $unique->id);
});

it('pre-populates nothing for a run that names no card', function (): void {
    $trainee = Umamusume::factory()->create();
    ki33GlobalSkill(200512);

    $this->post('/training-runs', ['umamusume_id' => $trainee->id, 'status' => 'Active'])
        ->assertSessionHasNoErrors();

    expect(TrainingRun::first()->skills()->count())->toBe(0);
});

it('drops a card skill the source has not released on Global', function (): void {
    ki33GlobalSkill(201591);
    ki33GlobalSkill(201212);
    ki33GlobalSkill(201472);
    // Gold Ship really carries two uniques; one of them is not on Global in this fixture.
    ki33JapanOnlySkill(100071);
    $card = ki33Card([201591, 201212, 201472], [10071, 100071]);
    ki33GlobalSkill(10071);

    $this->post('/training-runs', [
        'umamusume_id' => $card->umamusume_id,
        'character_card_id' => $card->id,
        'status' => 'Active',
    ])->assertSessionHasNoErrors();

    $run = TrainingRun::firstWhere('character_card_id', $card->id);

    // Five ids on the card, four of them Global: 100071 is the Japan-only unique and is filtered
    // rather than offered, while 10071 is Global and lands. The count is the proof the filter ran on
    // the list rather than dropping the card's skills wholesale.
    expect($run->skills()->count())->toBe(4)
        ->and($run->skills()->pluck('skills.export_id')->sort()->values()->all())
        ->toBe([10071, 201212, 201472, 201591]);
});

it('treats a card with no lists stored as a card with nothing to seed', function (): void {
    // The columns are nullable and the `array` cast does not coerce null to [], so this is the
    // shape a pre-existing card row has after the migration lands. A foreach over null here would
    // be a 500 on run creation, and the run is the thing the Trainer was trying to start.
    $card = ki33Card(null, null);

    $this->post('/training-runs', [
        'umamusume_id' => $card->umamusume_id,
        'character_card_id' => $card->id,
        'status' => 'Active',
    ])->assertSessionHasNoErrors();

    expect(TrainingRun::firstWhere('character_card_id', $card->id)->skills()->count())->toBe(0);
});

it('refuses to backfill an existing run when a later run is created from the same card', function (): void {
    // D-270: every figure on a run in progress is Trainer-entered. Deriving Suggested rows into a
    // run that already exists overwrites memory with plan, so the pre-populate belongs to creation
    // only — and the guard has to be tested against a second creation, not just asserted in a comment.
    ki33GlobalSkill(200512);
    $card = ki33Card([200512], []);

    $existing = TrainingRun::create([
        'umamusume_id' => $card->umamusume_id,
        'character_card_id' => $card->id,
        'status' => 'Active',
    ]);
    $existing->setSkillStatus(Skill::firstWhere('export_id', 200512), SkillAcquisition::Acquired, 7);

    $before = $existing->skills()->withPivot('status', 'turn_acquired')->get()->pluck(
        'skills.export_id',
        'run_skills.status'
    )->all();

    // A second run for the same trainee on the same card, through the real route.
    $this->post('/training-runs', [
        'umamusume_id' => $card->umamusume_id,
        'status' => 'Active',
    ])->assertSessionHasNoErrors();

    $existing->refresh();

    expect($existing->skills()->count())->toBe(1)
        ->and($existing->skills()->pluck('run_skills.status')->all())->toBe([SkillAcquisition::Acquired->value])
        ->and($existing->skills()->pluck('run_skills.turn_acquired')->all())->toBe([7])
        ->and($before)->toHaveCount(1)
        ->and(TrainingRun::query()->count())->toBe(2);
});
