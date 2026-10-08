<?php

declare(strict_types=1);

use App\Actions\ListVeterans;
use App\Actions\RecordVeteran;
use App\Actions\ShowVeteran;
use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Enums\SkillAcquisition;
use App\Models\RaceEntry;
use App\Models\Skill;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use App\Models\Veteran;
use Illuminate\Support\Facades\Schema;

/*
 * The Veteran library, record-only (PRD FR-G, ADR-0020 §3, under ADR-0010's recording allowance).
 *
 * The slice is domain-only: it stores a completed run plus the Trainer's tags and notes, and searches
 * that. It computes nothing — no Spark firing, no affinity payout, no offspring (FR-G-4) — and it touches
 * no engine-owned data. These cases prove the four behaviours the slice owns: recording, the G-2 filters,
 * the show read, and the completed-run requirement.
 */

it('creates the veterans table with the run as a cascading, unique foreign key', function (): void {
    expect(Schema::hasColumns('veterans', ['id', 'training_run_id', 'tags', 'notes', 'created_at', 'updated_at']))
        ->toBeTrue();

    $targets = collect(Schema::getForeignKeys('veterans'))
        ->map(fn (array $fk): string => ($fk['columns'][0] ?? '').'->'.$fk['foreign_table']);

    expect($targets)->toContain('training_run_id->training_runs');
});

it('records a completed run as a Veteran with the Trainer tags and notes', function (): void {
    $run = TrainingRun::factory()->create(['status' => RunStatus::Completed]);

    $veteran = (new RecordVeteran)->handle($run, ['Mile', 'Turf', 'Speed'], 'Solid medium parent.');

    expect($veteran->training_run_id)->toBe($run->id)
        ->and($veteran->tags)->toBe(['Mile', 'Turf', 'Speed'])
        ->and($veteran->notes)->toBe('Solid medium parent.')
        // The tags column round-trips as an array, which is what the library filters read.
        ->and($veteran->fresh()->tags)->toBe(['Mile', 'Turf', 'Speed']);
});

it('records a Veteran with no tags and no notes as a complete record', function (): void {
    // Nullability is the point: a Trainer who tags nothing still has a Veteran.
    $veteran = (new RecordVeteran)->handle(TrainingRun::factory()->create(['status' => RunStatus::Completed]));

    expect($veteran->tags)->toBe([])
        ->and($veteran->notes)->toBeNull();
});

it('refuses to record a run that is not Completed', function (RunStatus $status): void {
    // Decision: only a Completed run is recordable. The library is "a record built from a completed run"
    // (FR-G-1), so an Active or Retired run is refused rather than filed as a finished career.
    $run = TrainingRun::factory()->create(['status' => $status]);

    expect(fn () => (new RecordVeteran)->handle($run))
        ->toThrow(InvalidArgumentException::class);

    expect(Veteran::count())->toBe(0);
})->with([
    'Active' => RunStatus::Active,
    'Retired' => RunStatus::Retired,
]);

it('keeps one Veteran per run, rewriting its tags and notes on a second save', function (): void {
    $run = TrainingRun::factory()->create(['status' => RunStatus::Completed]);
    $action = new RecordVeteran;

    $first = $action->handle($run, ['Mile'], 'First pass.');
    $second = $action->handle($run, ['Long', 'Turf'], 'Revised.');

    expect(Veteran::count())->toBe(1)
        ->and($second->id)->toBe($first->id)
        ->and($second->fresh()->tags)->toBe(['Long', 'Turf'])
        ->and($second->fresh()->notes)->toBe('Revised.');
});

it('filters the library by trainee', function (): void {
    $specialWeek = Umamusume::factory()->create(['name' => 'Special Week']);
    $silenceSuzuka = Umamusume::factory()->create(['name' => 'Silence Suzuka']);

    $kept = Veteran::factory()->create([
        'training_run_id' => TrainingRun::factory()->create([
            'umamusume_id' => $specialWeek->id,
            'status' => RunStatus::Completed,
        ])->id,
    ]);
    Veteran::factory()->create([
        'training_run_id' => TrainingRun::factory()->create([
            'umamusume_id' => $silenceSuzuka->id,
            'status' => RunStatus::Completed,
        ])->id,
    ]);

    $ids = (new ListVeterans)->handle(['trainee' => $specialWeek->id])->getCollection()->pluck('id')->all();

    expect($ids)->toBe([$kept->id]);
});

it('filters the library by scenario', function (): void {
    $trackblazer = Veteran::factory()->create([
        'training_run_id' => TrainingRun::factory()->create([
            'scenario' => 'trackblazer',
            'status' => RunStatus::Completed,
        ])->id,
    ]);
    Veteran::factory()->create([
        'training_run_id' => TrainingRun::factory()->create([
            'scenario' => 'ura_finale',
            'status' => RunStatus::Completed,
        ])->id,
    ]);

    $ids = (new ListVeterans)->handle(['scenario' => 'trackblazer'])->getCollection()->pluck('id')->all();

    expect($ids)->toBe([$trackblazer->id]);
});

it('filters the library by tags, requiring every named tag', function (): void {
    $mediumSpeed = Veteran::factory()->create(['tags' => ['Mile', 'Turf', 'Speed']]);
    $longStamina = Veteran::factory()->create(['tags' => ['Long', 'Turf', 'Stamina']]);

    // One tag matches both; the all-of rule is what keeps a second tag from widening the set.
    $oneTag = (new ListVeterans)->handle(['tags' => ['Turf']])->getCollection()->pluck('id')->all();
    expect($oneTag)->toHaveCount(2);

    $bothTags = (new ListVeterans)->handle(['tags' => ['Mile', 'Speed']])->getCollection()->pluck('id')->all();
    expect($bothTags)->toBe([$mediumSpeed->id]);
});

it('excludes a non-matching Veteran when a tag it does not carry is filtered on', function (): void {
    // Negative control: the long-distance Veteran is in the library and is not returned, so the tag
    // filter is proven to filter rather than to return everything.
    $mediumSpeed = Veteran::factory()->create(['tags' => ['Mile', 'Speed']]);
    $longStamina = Veteran::factory()->create(['tags' => ['Long', 'Stamina']]);

    $ids = (new ListVeterans)->handle(['tags' => ['Mile']])->getCollection()->pluck('id')->all();

    expect($ids)->toBe([$mediumSpeed->id])
        ->and($ids)->not->toContain($longStamina->id);
});

it('answers a hand-typed tag through any casing via the folded twin, and keeps the typed spelling', function (): void {
    $run = TrainingRun::factory()->state(['status' => RunStatus::Completed])->create();

    $this->post(route('runs.veteran.store', $run), ['tags' => ['speed', 'Front Runner']])->assertSessionHasNoErrors();

    $veteran = Veteran::query()->where('training_run_id', $run->id)->sole();

    // The chips keep exactly what the Trainer typed (KI-72)...
    expect($veteran->tags)->toBe(['speed', 'Front Runner'])
        // ...while the twin is the folded list the filter matches against.
        ->and($veteran->tags_normalized)->toBe(['speed', 'front runner']);

    // The library's suggestion is capitalised and the lowercase tag still answers it, in both
    // directions. Before the twin, `Speed` found nothing and the row disagreed with its own chip.
    foreach (['Speed', 'speed', 'FRONT RUNNER', 'front runner'] as $filter) {
        $found = (new ListVeterans)->handle(['tags' => [$filter]])->getCollection()->pluck('id')->all();

        expect($found)->toBe([$veteran->id], "filter '{$filter}' did not find the row");
    }
});

it('returns the whole library when no filter is given', function (): void {
    Veteran::factory()->count(3)->create();

    expect((new ListVeterans)->handle()->total())->toBe(3);
});

it('reads a Veteran with the recorded facts it shows, without an N+1', function (): void {
    $umamusume = Umamusume::factory()->create(['name' => 'Special Week']);
    $run = TrainingRun::factory()->create([
        'umamusume_id' => $umamusume->id,
        'status' => RunStatus::Completed,
    ]);

    $run->turnEntries()->create(['turn' => 1, 'speed' => 600, 'stamina' => 300, 'power' => 400, 'guts' => 200, 'wit' => 250]);
    $run->setSkillStatus(Skill::factory()->create(), SkillAcquisition::Acquired);
    RaceEntry::factory()->create(['training_run_id' => $run->id, 'status' => RaceEntryStatus::Completed]);

    $veteran = (new ShowVeteran)->handle(Veteran::factory()->create(['training_run_id' => $run->id]));

    expect($veteran->relationLoaded('trainingRun'))->toBeTrue()
        ->and($veteran->trainingRun->relationLoaded('umamusume'))->toBeTrue()
        ->and($veteran->trainingRun->relationLoaded('turnEntries'))->toBeTrue()
        ->and($veteran->trainingRun->relationLoaded('skills'))->toBeTrue()
        ->and($veteran->trainingRun->relationLoaded('raceEntries'))->toBeTrue()
        ->and($veteran->trainingRun->umamusume->name)->toBe('Special Week')
        ->and($veteran->trainingRun->turnEntries)->toHaveCount(1)
        ->and($veteran->trainingRun->skills)->toHaveCount(1)
        ->and($veteran->trainingRun->raceEntries)->toHaveCount(1);
});

it('removes the Veteran when its run is deleted', function (): void {
    // The cascading choice, proven at the behaviour rather than the DDL: a Veteran is the run read back,
    // so with the run gone there is nothing left for the row to show.
    $veteran = Veteran::factory()->create();
    $run = $veteran->trainingRun;

    $run->delete();

    expect(Veteran::find($veteran->id))->toBeNull();
});
