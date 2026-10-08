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
use Database\Seeders\UraFinaleRunsSeeder;

/**
 * The seeder binds to concrete rows (umamusume, character cards, skills, race
 * slots), so the fixture materialises exactly those ids from RUNS before the
 * seeder runs. Nothing here is an assertion; the assertions are below.
 */
beforeEach(function (): void {
    $slotTitles = [
        1 => 'Junior Make Debut',
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

it('seeds the eight URA Finale tracker runs with one turn each', function (): void {
    $this->seed(UraFinaleRunsSeeder::class);

    $runs = TrainingRun::where('import_source', 'like', 'docs/Umamusume_Progress_Tracker_Runs.md (Run %')->get();

    expect($runs)->toHaveCount(8)
        ->and(TurnEntry::whereIn('training_run_id', $runs->pluck('id'))->count())->toBe(8)
        ->and($runs->where('imported_at', '!=', null))->toHaveCount(8);
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

it('is idempotent: running twice produces the same row count', function (): void {
    $this->seed(UraFinaleRunsSeeder::class);
    $firstCount = TrainingRun::count();

    $this->seed(UraFinaleRunsSeeder::class);

    expect(TrainingRun::count())->toBe($firstCount)
        ->and(TrainingRun::count())->toBe(8)
        ->and(RaceEntry::count())->toBe(15); // 1+1+4+4+2+1+1+1
});
