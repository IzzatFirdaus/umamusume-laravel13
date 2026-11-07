<?php

declare(strict_types=1);

use App\Enums\MoodTier;
use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Models\CharacterCard;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\Skill;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use App\Services\CareerCalendar;
use Database\Seeders\UraFinaleRunsSeeder;

/**
 * The seeder binds to concrete rows (umamusume, character cards, skills, race
 * slots), so the fixture materialises exactly those ids from RUNS before the
 * seeder runs. Nothing here is an assertion; the assertions are below.
 */
beforeEach(function (): void {
    $slotTitles = [
        1 => 'Junior Make Debut',
        180 => 'Kikuka Sho (Classic)',
        215 => 'Arima Kinen (Classic)',
        375 => 'JBC Sprint (Senior)',
        400 => 'Arima Kinen (Senior)',
        405 => 'URA Finals Qualifier',
        406 => 'URA Finals Semifinal',
        410 => 'URA Finals Final (URA)',
    ];

    foreach (UraFinaleRunsSeeder::RUNS as $run) {
        if (Umamusume::find($run['umamusume_id']) === null) {
            Umamusume::factory()->create(['id' => $run['umamusume_id']]);
        }
        if (CharacterCard::find($run['character_card_id']) === null) {
            CharacterCard::factory()->create([
                'id' => $run['character_card_id'],
                'umamusume_id' => $run['umamusume_id'],
            ]);
        }
        foreach ($run['skills'] as [$skillId]) {
            if (Skill::find($skillId) === null) {
                Skill::factory()->create(['id' => $skillId]);
            }
        }
        foreach ($run['races'] as $race) {
            if (RaceCatalogSlot::find($race['slot']) === null) {
                RaceCatalogSlot::factory()->create([
                    'id' => $race['slot'],
                    'title' => $slotTitles[$race['slot']],
                ]);
            }
        }
    }
});

it('seeds all seventeen URA Finale tracker runs with one turn each', function (): void {
    $this->seed(UraFinaleRunsSeeder::class);

    $runs = TrainingRun::where('import_source', 'like', 'docs/Umamusume_Progress_Tracker_Runs.md (Run %')->get();

    expect($runs)->toHaveCount(17)
        ->and(TurnEntry::whereIn('training_run_id', $runs->pluck('id'))->count())->toBe(17)
        ->and($runs->where('imported_at', '!=', null))->toHaveCount(17);
});

it('stores each run snapshot turn and mood from its roster line', function (): void {
    $this->seed(UraFinaleRunsSeeder::class);

    $run = TrainingRun::where('import_source', 'docs/Umamusume_Progress_Tracker_Runs.md (Run 1)')->firstOrFail();
    $turn = $run->turnEntries()->sole();

    expect($run->status)->toBe(RunStatus::Active)
        ->and($turn->turn)->toBe(48)
        ->and($turn->speed)->toBe(474)
        ->and($turn->sp)->toBe(267)
        ->and($turn->energy)->toBe(80)
        ->and($turn->mood)->toBe(MoodTier::Bad)
        ->and($turn->condition)->toBeNull();
});

it('attaches the resolved skill rosters and race entries per run', function (): void {
    $this->seed(UraFinaleRunsSeeder::class);

    $run = TrainingRun::where('import_source', 'docs/Umamusume_Progress_Tracker_Runs.md (Run 3)')->firstOrFail();

    expect($run->skills()->count())->toBe(19)
        ->and($run->raceEntries()->where('status', 'Completed')->count())->toBe(4)
        ->and($run->raceEntries()->where('race_catalog_slot_id', 410)->firstOrFail()->placement)->toBe(1);

    $dnf = TrainingRun::where('import_source', 'docs/Umamusume_Progress_Tracker_Runs.md (Run 4)')->firstOrFail();
    expect($dnf->raceEntries()->where('race_catalog_slot_id', 410)->firstOrFail()->status)
        ->toBe(RaceEntryStatus::Skipped)
        ->and($dnf->raceEntries()->where('race_catalog_slot_id', 410)->firstOrFail()->placement)->toBeNull();
});

it('maps tracker check marks to Acquired and blanks to Suggested', function (): void {
    $this->seed(UraFinaleRunsSeeder::class);

    $run = TrainingRun::where('import_source', 'docs/Umamusume_Progress_Tracker_Runs.md (Run 1)')->firstOrFail();

    expect($run->skills()->wherePivot('status', 'Acquired')->count())->toBe(4)
        ->and($run->skills()->wherePivot('status', 'Suggested')->count())->toBe(19)
        ->and($run->skills()->wherePivot('turn_acquired', '!=', null)->count())->toBe(0);
});

it('resolves the tracker tier-two glyph against the catalog ◎/× rows', function (): void {
    $this->seed(UraFinaleRunsSeeder::class);

    $run = TrainingRun::where('import_source', 'docs/Umamusume_Progress_Tracker_Runs.md (Run 1)')->firstOrFail();
    $ids = $run->skills()->pluck('skills.id');

    expect($ids)->toContain(138) // Long Shot ◎
        ->and($ids)->toContain(139) // Long Shot ○
        ->and($ids)->toContain(310) // Pace Chaser Straightaways ◎
        ->and($ids)->toContain(311) // Pace Chaser Straightaways ○
        ->and($ids)->toContain(355) // Late Surger Savvy ◎
        ->and($ids)->toContain(356); // Late Surger Savvy ○
});

it('records per-trainee counts matching the tracker', function (): void {
    $this->seed(UraFinaleRunsSeeder::class);

    expect(TrainingRun::where('import_source', 'like', 'docs/Umamusume_Progress_Tracker_Runs.md (Run %')
        ->where('umamusume_id', 4)->count())->toBe(2); // Maruzensky
    expect(TrainingRun::where('import_source', 'like', 'docs/Umamusume_Progress_Tracker_Runs.md (Run %')
        ->where('umamusume_id', 8)->count())->toBe(2); // Vodka
    expect(TrainingRun::where('import_source', 'like', 'docs/Umamusume_Progress_Tracker_Runs.md (Run %')
        ->where('umamusume_id', 20)->count())->toBe(2); // Agnes Tachyon
    expect(TrainingRun::where('import_source', 'like', 'docs/Umamusume_Progress_Tracker_Runs.md (Run %')
        ->where('umamusume_id', 14)->count())->toBe(2); // El Condor Pasa
    expect(TrainingRun::where('import_source', 'like', 'docs/Umamusume_Progress_Tracker_Runs.md (Run %')
        ->where('umamusume_id', 22)->count())->toBe(3); // Mayano Top Gun
    expect(TrainingRun::where('import_source', 'like', 'docs/Umamusume_Progress_Tracker_Runs.md (Run %')
        ->where('umamusume_id', 50)->count())->toBe(2); // Haru Urara
    expect(TrainingRun::where('import_source', 'like', 'docs/Umamusume_Progress_Tracker_Runs.md (Run %')
        ->where('umamusume_id', 2)->count())->toBe(2); // Tokai Teio
    expect(TrainingRun::where('import_source', 'like', 'docs/Umamusume_Progress_Tracker_Runs.md (Run %')
        ->where('umamusume_id', 9)->count())->toBe(1); // Daiwa Scarlet
    expect(TrainingRun::where('import_source', 'like', 'docs/Umamusume_Progress_Tracker_Runs.md (Run %')
        ->where('umamusume_id', 25)->count())->toBe(1); // Biwa Hayahide
});

it('records status per run: six Active, three Completed among the nine new runs', function (): void {
    $this->seed(UraFinaleRunsSeeder::class);

    $newRuns = [5, 6, 7, 8, 9, 10, 12, 13, 17];
    $active = 0;
    $completed = 0;

    foreach ($newRuns as $label) {
        $run = TrainingRun::where('import_source', 'docs/Umamusume_Progress_Tracker_Runs.md (Run '.$label.')')->firstOrFail();
        if ($run->status === RunStatus::Active) {
            $active++;
        } elseif ($run->status === RunStatus::Completed) {
            $completed++;
        }
    }

    expect($active)->toBe(6)
        ->and($completed)->toBe(3);
});

it('asserts result counts per run, covering rebuilt eight and new nine', function (): void {
    $this->seed(UraFinaleRunsSeeder::class);

    $expected = [
        1 => 1,   // Junior Make Debut
        2 => 1,   // Junior Make Debut
        3 => 4,   // Junior Make Debut, URA Q, URA SF, URA F
        4 => 4,   // Junior Make Debut, URA Q, URA SF, URA F
        5 => 1,   // Junior Make Debut
        6 => 2,   // Junior Make Debut, Kikuka Sho
        7 => 1,   // Junior Make Debut
        8 => 2,   // Junior Make Debut, Kikuka Sho
        9 => 3,   // Junior Make Debut, Kikuka Sho, Arima Kinen
        10 => 3,  // Junior Make Debut, Kikuka Sho, Arima Kinen (Skipped)
        11 => 3,  // Junior Make Debut, JBC Sprint, Arima Kinen
        12 => 3,  // Junior Make Debut, JBC Sprint, Arima Kinen (Skipped)
        13 => 3,  // Junior Make Debut, Kikuka Sho, Arima Kinen (Skipped)
        14 => 1,  // URA Final
        15 => 1,  // URA Final
        16 => 1,  // URA Final
        17 => 0,  // no race result
    ];

    foreach ($expected as $label => $count) {
        $run = TrainingRun::where('import_source', 'docs/Umamusume_Progress_Tracker_Runs.md (Run '.$label.')')->firstOrFail();
        expect($run->raceEntries()->count())->toBe($count, 'Run '.$label.' should have '.$count.' race entries');
    }
});

it('is idempotent: running twice produces the same row count', function (): void {
    $this->seed(UraFinaleRunsSeeder::class);
    $firstCount = TrainingRun::count();
    $firstRaceCount = RaceEntry::count();

    $this->seed(UraFinaleRunsSeeder::class);

    expect(TrainingRun::count())->toBe($firstCount)
        ->and(TrainingRun::count())->toBe(17)
        ->and(RaceEntry::count())->toBe($firstRaceCount)
        ->and(RaceEntry::count())->toBe(34); // 1+1+4+4+1+2+1+2+3+3+3+3+3+1+1+1+0
});

it('stores a named career_position for every imported run', function (): void {
    $this->seed(UraFinaleRunsSeeder::class);

    $runs = TrainingRun::where('import_source', 'like', 'docs/Umamusume_Progress_Tracker_Runs.md (Run %')->get();

    foreach ($runs as $run) {
        if ($run->turnEntries()->sole()->turn <= CareerCalendar::TURNS_PER_CAREER) {
            expect($run->career_position)->not->toBeNull()
                ->and($run->career_position_source)->toBe('imported');
        } else {
            expect($run->career_position_source)->toBe('imported');
        }
    }
});
