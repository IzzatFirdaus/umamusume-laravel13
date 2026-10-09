<?php

declare(strict_types=1);

use App\Enums\MoodTier;
use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Models\CharacterCard;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\RunSkill;
use App\Models\Skill;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use App\Models\Veteran;
use Database\Seeders\TrackerVeteransSeeder;

/**
 * The seeder binds to concrete rows (umamusume, character cards, skills, race
 * slots), so the fixture materialises exactly those ids from the merged table
 * before the seeder runs. Nothing here is an assertion; the assertions are below.
 */
beforeEach(function (): void {
    $slots = [
        1 => ['title' => 'Junior Make Debut', 'year' => 1],
        180 => ['title' => 'Kikuka Sho', 'year' => 2],
        215 => ['title' => 'Arima Kinen', 'year' => 2, 'month' => 12, 'half' => 'Late'],
        375 => ['title' => 'JBC Sprint', 'year' => 3],
        400 => ['title' => 'Arima Kinen', 'year' => 3, 'month' => 12, 'half' => 'Late'],
        405 => ['title' => 'URA Finals Qualifier', 'year' => 4],
        406 => ['title' => 'URA Finals Semifinal', 'year' => 4],
        410 => ['title' => 'URA Finals Final (URA)', 'year' => 4],
    ];

    foreach (TrackerVeteransSeeder::veteranDefinitions() as $definition) {
        if (Umamusume::find($definition['umamusume_id']) === null) {
            Umamusume::factory()->create(['id' => $definition['umamusume_id']]);
        }
        if (CharacterCard::find($definition['character_card_id']) === null) {
            CharacterCard::factory()->create([
                'id' => $definition['character_card_id'],
                'umamusume_id' => $definition['umamusume_id'],
            ]);
        }
        foreach ($definition['skills'] as [$skillId]) {
            if (Skill::find($skillId) === null) {
                Skill::factory()->create(['id' => $skillId]);
            }
        }
        foreach ($definition['races'] as $race) {
            if (RaceCatalogSlot::find($race['slot']) === null) {
                RaceCatalogSlot::factory()->create($slots[$race['slot']] + ['id' => $race['slot']]);
            }
        }
    }
});

it('files the seven career-end tracker runs in the Veteran library', function (): void {
    $this->seed(TrackerVeteransSeeder::class);

    expect(Veteran::count())->toBe(7)
        ->and(TrainingRun::count())->toBe(7)
        ->and(TrainingRun::query()->where('status', '!=', RunStatus::Completed->value)->count())->toBe(0);

    $veteranRunIds = Veteran::pluck('training_run_id')->all();
    $importedRunIds = TrainingRun::query()
        ->where('import_source', 'like', 'docs/Umamusume_Progress_Tracker_Runs.md (Run %')
        ->pluck('id')
        ->all();

    expect($veteranRunIds)->toEqualCanonicalizing($importedRunIds);
});

it('leaves the in-progress and race-plan-only snapshots out of both buckets', function (): void {
    $this->seed(TrackerVeteransSeeder::class);

    $labels = TrainingRun::pluck('import_source')->all();

    foreach ([1, 2, 5, 6, 7, 8, 9, 11, 13, 17] as $skipped) {
        expect($labels)->not->toContain('docs/Umamusume_Progress_Tracker_Runs.md (Run '.$skipped.')');
    }
});

it('stores the Mayano Top Gun career end from the tracker line', function (): void {
    $this->seed(TrackerVeteransSeeder::class);

    $run = TrainingRun::where('import_source', 'docs/Umamusume_Progress_Tracker_Runs.md (Run 10)')->sole();
    $turn = $run->turnEntries()->sole();

    expect($run->status)->toBe(RunStatus::Completed)
        ->and($run->umamusume_id)->toBe(24)
        ->and($run->character_card_id)->toBe(29)
        ->and($turn->turn)->toBe(48)
        ->and($turn->speed)->toBe(296)
        ->and($turn->stamina)->toBe(391)
        ->and($turn->power)->toBe(271)
        ->and($turn->guts)->toBe(243)
        ->and($turn->wit)->toBe(191)
        ->and($turn->sp)->toBe(83)
        ->and($turn->energy)->toBe(5)
        ->and($turn->mood)->toBe(MoodTier::Great)
        ->and($turn->condition)->toBeNull();

    expect($run->skills()->count())->toBe(17)
        ->and($run->skills()->wherePivot('status', 'Acquired')->count())->toBe(5)
        ->and($run->raceEntries()->where('race_catalog_slot_id', 215)->sole()->status)
        ->toBe(RaceEntryStatus::Skipped)
        ->and($run->raceEntries()->where('race_catalog_slot_id', 180)->sole()->placement)->toBe(3)
        ->and($run->raceEntries()->where('race_catalog_slot_id', 1)->sole()->placement)->toBe(1);
});

it('stores the Haru Urara career end from the tracker line', function (): void {
    $this->seed(TrackerVeteransSeeder::class);

    $run = TrainingRun::where('import_source', 'docs/Umamusume_Progress_Tracker_Runs.md (Run 12)')->sole();
    $turn = $run->turnEntries()->sole();

    expect($turn->turn)->toBe(69)
        ->and($turn->speed)->toBe(485)
        ->and($turn->stamina)->toBe(305)
        ->and($turn->power)->toBe(404)
        ->and($turn->guts)->toBe(314)
        ->and($turn->wit)->toBe(264)
        ->and($turn->sp)->toBe(174)
        ->and($turn->energy)->toBe(20)
        ->and($turn->mood)->toBe(MoodTier::Good)
        ->and($turn->condition)->toBe('CHARMING');

    expect($run->skills()->count())->toBe(15)
        ->and($run->skills()->wherePivot('status', 'Acquired')->count())->toBe(7)
        ->and($run->raceEntries()->where('race_catalog_slot_id', 400)->sole()->status)
        ->toBe(RaceEntryStatus::Skipped)
        ->and($run->raceEntries()->where('race_catalog_slot_id', 375)->sole()->placement)->toBe(4);
});

it('leaves every Legacy diagram empty and notes the gap instead of inventing ancestors', function (): void {
    $this->seed(TrackerVeteransSeeder::class);

    $runs = TrainingRun::all();

    expect($runs->whereNotNull('legacy_selection')->count())->toBe(0)
        ->and($runs->whereNotNull('inheritance_parent_a_id')->count())->toBe(0)
        ->and($runs->whereNotNull('inheritance_parent_b_id')->count())->toBe(0);

    foreach (Veteran::all() as $veteran) {
        expect($veteran->notes)->toContain('Legacy ancestry unrecorded');
    }
});

it('is idempotent: re-running the seeder adds and changes nothing', function (): void {
    $this->seed(TrackerVeteransSeeder::class);

    $before = [
        'runs' => TrainingRun::count(),
        'veterans' => Veteran::count(),
        'turns' => TurnEntry::count(),
        'races' => RaceEntry::count(),
        'run_skills' => RunSkill::count(),
        'imported_at' => TrainingRun::orderBy('id')->pluck('imported_at')->toJson(),
        'veteran_rows' => Veteran::orderBy('id')->get(['training_run_id', 'notes'])->toJson(),
    ];

    expect($before['runs'])->toBe(7)->and($before['veterans'])->toBe(7);

    $this->seed(TrackerVeteransSeeder::class);

    expect(TrainingRun::count())->toBe(7)
        ->and(Veteran::count())->toBe(7)
        ->and(TurnEntry::count())->toBe($before['turns'])
        ->and(RaceEntry::count())->toBe($before['races'])
        ->and(RunSkill::count())->toBe($before['run_skills'])
        ->and(TrainingRun::orderBy('id')->pluck('imported_at')->toJson())->toBe($before['imported_at'])
        ->and(Veteran::orderBy('id')->get(['training_run_id', 'notes'])->toJson())->toBe($before['veteran_rows']);
});
