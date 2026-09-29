<?php

declare(strict_types=1);

use App\Models\ScenarioSlot;
use Database\Seeders\ScenarioSlotSeeder;
use Illuminate\Support\Facades\Log;

/*
 * R76: the tier-label extraction is evidence, not a dependency.
 *
 * Slice 14 joined two publishers into `database/seeders/data/race-tier-labels-2026-09-29.json`, and
 * Slice 15 T1 (R75) made a row without per-race evidence seed a null tier. The next step in that
 * reading is the file itself: when it is gone, the seeder must still produce the schedule with every
 * tier null and say so, rather than throwing and taking the whole seed down with it — and rather
 * than succeeding silently, which is the same failure wearing a green exit code. A run with no tier
 * labels is honest and usable. A `db:seed` that dies is neither, and so is one that quietly drops
 * 155 labels.
 *
 * These tests move the committed file aside rather than deleting it, and put it back in a `finally`,
 * because that file is the corpus `TierLabelJoinTest` reads: a test that left it gone would make a
 * later suite fail for a reason that has nothing to do with what it is asserting.
 *
 * The seeder's `loadJson()` already returned an empty array for a missing file, so the "completes
 * with null tiers" half of R76 was true before this slice and is pinned here anyway. The warning is
 * the new behaviour, and the third test is what stops the warning from being unconditional.
 */

const TIER_LABELS_RELATIVE_PATH = 'database/seeders/data/race-tier-labels-2026-09-29.json';

it('completes with every tier null when the extraction file is gone, and warns about it', function (): void {
    $wasMoved = withTierLabelsFileAbsent();

    // Exactly one warning, and it names the file that is missing. `shouldReceive` is the seam here
    // because a log line is a side effect at a boundary, and this test is about whether that side
    // effect happens.
    Log::shouldReceive('warning')
        ->once()
        ->with(Mockery::on(fn (string $message): bool => str_contains($message, 'race-tier-labels-2026-09-29.json')));

    try {
        $this->seed(ScenarioSlotSeeder::class);
    } finally {
        restoreTierLabelsFile();
    }

    $rows = ScenarioSlot::where('scenario_key', 'ura_finale')->where('kind', 'goal_race')->get();

    expect($wasMoved)->toBeTrue('the file was never moved aside, so this test proves nothing')
        ->and($rows)->not->toBeEmpty()
        ->and($rows->every(fn (ScenarioSlot $s): bool => $s->tier === null))->toBeTrue();
});

it('keeps the rest of the schedule when the labels are missing, because the schedule is not the labels', function (): void {
    withTierLabelsFileAbsent();
    Log::shouldReceive('warning');

    try {
        $this->seed(ScenarioSlotSeeder::class);
    } finally {
        restoreTierLabelsFile();
    }

    // Rows, month and half, fan figures and slot labels all come from the client export, which is
    // still present. Losing the tier join must not cost a Trainer the calendar they already have.
    $row = ScenarioSlot::where('scenario_key', 'ura_finale')
        ->where('kind', 'goal_race')
        ->whereNotNull('fans_needed')
        ->first();

    expect(ScenarioSlot::where('scenario_key', 'ura_finale')->count())->toBe(296)
        ->and($row)->not->toBeNull()
        ->and($row->slot_label)->toBe('Race')
        ->and($row->source_url)->not->toBeNull()
        ->and($row->fetched_at)->not->toBeNull();
});

it('warns about nothing when the file is there, which is what makes the other two mean something', function (): void {
    // A warning that fires on every run is not a warning about a missing file, and the two tests
    // above would pass either way.
    Log::shouldReceive('warning')->never();

    $this->seed(ScenarioSlotSeeder::class);

    expect(file_exists(base_path(TIER_LABELS_RELATIVE_PATH)))->toBeTrue()
        ->and(ScenarioSlot::where('scenario_key', 'ura_finale')->where('tier', 'G1')->count())->toBe(34);
});

it('puts the extraction file back where the other tier tests read it', function (): void {
    // Checks the bookkeeping on its own merits: a `finally` that silently failed would leave the
    // repository without its committed corpus, and the next test to run would blame itself.
    $doc = json_decode(
        (string) file_get_contents(base_path(TIER_LABELS_RELATIVE_PATH)),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($doc['counts']['400']['OP'])->toBe(3)
        ->and(file_exists(base_path(TIER_LABELS_RELATIVE_PATH.'.held-aside')))->toBeFalse();
});

function withTierLabelsFileAbsent(): bool
{
    $file = base_path(TIER_LABELS_RELATIVE_PATH);
    rename($file, $file.'.held-aside');
    clearstatcache(true, $file);

    return ! file_exists($file);
}

function restoreTierLabelsFile(): void
{
    $file = base_path(TIER_LABELS_RELATIVE_PATH);

    if (file_exists($file.'.held-aside')) {
        rename($file.'.held-aside', $file);
    }

    clearstatcache(true, $file);
}
