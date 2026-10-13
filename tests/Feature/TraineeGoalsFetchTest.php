<?php

declare(strict_types=1);

use App\Models\MatchCandidate;
use App\Models\RaceCatalogSlot;
use App\Models\TraineeGoal;
use App\Models\Umamusume;
use App\Services\DataPipeline\PipelineRunner;
use Illuminate\Support\Facades\Cache;

const GOALS_BODY = 'ura-objectives.ead4bbfe.json';

function goalsBody(): string
{
    return (string) file_get_contents(database_path('seeders/data/'.GOALS_BODY));
}

/**
 * @return array{created: int, updated: int, skipped: int, review: int}
 */
function runGoalsSource(): array
{
    $key = 'gametora-ura-objectives';

    return app(PipelineRunner::class)->run(
        $key,
        config('uma.sources')[$key],
        goalsBody(),
        'snapshots/'.GOALS_BODY,
    );
}

/**
 * Rice Shower: 11 objectives on 2026-10-13, three of them the post-December rounds.
 */
function seedTrainee(string $ref = 'gametora:char:1030'): Umamusume
{
    return Umamusume::factory()->create(['external_ref' => $ref]);
}

it('takes the objectives branch instead of filing the ladder as unidentifiable characters', function (): void {
    seedTrainee();

    $counts = runGoalsSource();

    expect($counts['review'])->toBe(0)
        ->and($counts['created'])->toBe(11)
        // No character outside the roster becomes a row, and none becomes a review candidate either:
        // the 1,690 objectives belonging to the other 134 characters are simply not this tool's trainees.
        ->and(TraineeGoal::query()->count())->toBe(11)
        ->and(MatchCandidate::query()->count())->toBe(0);
});

it('stamps the provenance the parser cannot know on every row it writes', function (): void {
    seedTrainee();
    runGoalsSource();

    $goal = TraineeGoal::query()->firstOrFail();

    expect($goal->source_url)->toBe(config('uma.sources.gametora-ura-objectives.url'))
        ->and($goal->snapshot_path)->toBe('snapshots/'.GOALS_BODY)
        ->and($goal->source_timezone)->toBe('Asia/Tokyo')
        ->and($goal->fetched_at)->not->toBeNull()
        ->and($goal->is_manual)->toBeFalse();
});

it('resolves an objective onto the calendar row that carries the same race at the same deadline', function (): void {
    seedTrainee();

    // Rice Shower's fifth objective is Nikkei Sho, turn 54: Senior Late March.
    $slot = RaceCatalogSlot::factory()->create([
        'scenario_key' => null, 'year' => 3, 'month' => 3, 'half' => 'Late', 'title' => 'Nikkei Sho',
    ]);

    runGoalsSource();

    expect(TraineeGoal::query()->where('turn', 54)->value('race_catalog_slot_id'))->toBe($slot->id);
});

it('keeps the race the source printed when the calendar has no row for it', function (): void {
    seedTrainee();
    runGoalsSource();

    // No slot was seeded, so nothing is invented to fill the join: the row keeps the document's own
    // string and a null foreign key, which is the difference between "unsourced" and "unjoined".
    $goal = TraineeGoal::query()->where('turn', 54)->firstOrFail();

    expect($goal->race_catalog_slot_id)->toBeNull()
        ->and($goal->source_race_name)->toBe('Nikkei Sho');
});

it('is idempotent, updating the ladder rather than doubling it', function (): void {
    seedTrainee();

    $first = runGoalsSource();
    $second = runGoalsSource();

    expect($first['created'])->toBe(11)->and($first['updated'])->toBe(0)
        ->and($second['created'])->toBe(0)->and($second['updated'])->toBe(11)
        ->and(TraineeGoal::query()->count())->toBe(11);
});

it('never overwrites a row the Trainer flagged manual', function (): void {
    seedTrainee();
    runGoalsSource();

    $row = TraineeGoal::query()->where('turn', 54)->firstOrFail();
    $row->update(['is_manual' => true, 'required_placement' => 1]);

    $counts = runGoalsSource();

    expect($counts['skipped'])->toBe(1)
        ->and(TraineeGoal::query()->find($row->id)->required_placement)->toBe(1);
});

it('bumps the catalog version on a write and leaves it alone when nothing was written', function (): void {
    seedTrainee();

    // The first write seeds the counter rather than moving it, because the branch reuses the card
    // branch's `Cache::add`-then-`increment` idiom and that is what keeps one counter with one
    // seeding rule across every routed kind. The behaviour that matters is that a write eventually
    // moves it and a non-write never does, so a promotion cannot leave the catalog pages cached
    // against a ladder they no longer hold.
    Cache::forget('catalog:version');
    runGoalsSource();
    $after = (int) Cache::get('catalog:version');

    runGoalsSource();
    expect((int) Cache::get('catalog:version'))->toBe($after + 1);

    // An empty document writes nothing, so the counter must not move.
    app(PipelineRunner::class)->run(
        'gametora-ura-objectives',
        config('uma.sources.gametora-ura-objectives'),
        '[]',
        'snapshots/empty.json',
    );

    expect((int) Cache::get('catalog:version'))->toBe($after + 1);
});
