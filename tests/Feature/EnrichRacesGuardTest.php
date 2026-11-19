<?php

declare(strict_types=1);

use App\Models\RaceCatalogSlot;
use Illuminate\Support\Facades\Storage;

/*
 * `uma:enrich-races` was the one engine writer in the tree with no `is_manual` guard, and the one
 * that stamped no provenance of its own. Two measured consequences, and this file pins both.
 *
 * FR-B-4 / `PRD.md` B-4: `is_manual` rows are immutable to the engine. Every sibling writer honours
 * it — `StoreRaceCatalogSlots`, `StoreSkills`, `StoreCharacterCards`, `StoreCharacterProfiles`,
 * `StoreSupportCards`, `StoreSupportEffects`, `StoreTraineeGoals`, `PromoteMatchedRecord` — so a
 * Trainer's correction to a catalogue row has to survive this command too.
 *
 * `ADR-0003` Amendment R3 wants provenance on the reference row. Before this the command wrote
 * `track_name`, `fans_*`, `course`, `direction`, `season`, `reward_summary` and the three `*_label`
 * columns and touched none of `source_url`, `snapshot_path` or `fetched_at`, so the four enriching
 * publishers' facts stayed filed under the calendar document's URL.
 *
 * **Storage::fake, not the real snapshot tree.** The command reads the newest `.html` under
 * `snapshots/<sourceKey>/` on the `local` disk and only fetches when that directory is empty, so a
 * fake disk seeded with one body per source drives the whole join without a network call. The bodies
 * are the minimum shape each parser reads, so a fixture that drifts from its parser fails here
 * rather than passing on a document nobody re-read.
 */

/**
 * One body per enriching source, keyed by the source key whose snapshot directory holds it.
 *
 * The four are joined to a slot by `track_id` (racetracks), `fans_gain_curve` (race-fans), `race_id`
 * (races and race-rewards), which is the grain the command itself uses.
 *
 * @return array<string, string>
 */
function enrichRacesBodies(): array
{
    return [
        'gametora-racetracks' => json_encode([
            ['id' => 10008, 'name_en' => 'Tokyo', 'name_ja' => '東京', 'country' => 'Japan'],
        ], JSON_THROW_ON_ERROR),
        'gametora-race-fans' => json_encode([
            ['id' => 21, 'fans' => [
                ['order' => 1, 'fans' => 11000],
                ['order' => 2, 'fans' => 4400],
                ['order' => 3, 'fans' => 2750],
            ]],
        ], JSON_THROW_ON_ERROR),
        'gametora-races' => json_encode([
            ['id' => 100801, 'name_en' => 'Oka Sho', 'course' => 1, 'direction' => 2, 'season' => 0,
                'entries' => 18, 'factor' => ['effect_1' => 'speed', 'effect_2' => 'guts'], 'track' => 10008],
        ], JSON_THROW_ON_ERROR),
        'gametora-race-rewards' => json_encode([
            ['id' => 100801, 'rewards_combined' => [
                ['item' => '1001', 'num_min' => 1, 'num_max' => 1, 'chance' => 1.0],
            ]],
        ], JSON_THROW_ON_ERROR),
    ];
}

/**
 * A fake `local` disk holding one snapshot per enriching source, and no network reachable.
 *
 * `Http::preventStrayRequests()` is not used because nothing should reach the fetcher at all: a body
 * on disk is what makes `snapshot()` stop before `SourceFetcher::fetch()`, and this asserts that by
 * leaving the fake empty of anything else.
 */
function seedEnrichRacesSnapshots(): void
{
    Storage::fake('local');

    foreach (enrichRacesBodies() as $sourceKey => $body) {
        Storage::disk('local')->put("snapshots/{$sourceKey}/body.html", $body);
    }
}

it('leaves a hand-corrected slot alone and still enriches its sibling', function (): void {
    seedEnrichRacesSnapshots();

    // The Trainer's own correction: `is_manual` is the engine's stop sign, and this command used to
    // be the one writer that walked straight past it.
    $manual = RaceCatalogSlot::factory()->create([
        'title' => 'Manual Oka Sho',
        'track_id' => 10008,
        'race_id' => 100801,
        'fans_gain_curve' => 21,
        'track_name' => 'Hand-typed course',
        'is_manual' => true,
    ]);

    // The same join keys on a row nobody corrected, so the run has something to prove it still works
    // beside the skip: a guard that silently disabled the whole command would pass the first half.
    $auto = RaceCatalogSlot::factory()->create([
        'title' => 'Oka Sho',
        'track_id' => 10008,
        'race_id' => 100801,
        'fans_gain_curve' => 21,
        'track_name' => null,
        'is_manual' => false,
    ]);

    $this->artisan('uma:enrich-races')->assertExitCode(0);

    $manual->refresh();
    $auto->refresh();

    expect($manual->track_name)->toBe('Hand-typed course')
        ->and($manual->fans_first)->toBeNull()
        ->and($manual->direction_label)->toBeNull()
        ->and($manual->source_url)->toBeNull()
        ->and($manual->fetched_at)->toBeNull()
        // ...and the sibling in the same call took all four sources' facts.
        ->and($auto->track_name)->toBe('Tokyo')
        ->and($auto->fans_first)->toBe(11000)
        ->and($auto->direction_label)->toBe('Left-Handed')
        ->and($auto->entries)->toBe(18);
});

it('stamps every contributing source onto a slot it changed', function (): void {
    seedEnrichRacesSnapshots();

    $slot = RaceCatalogSlot::factory()->create([
        'track_id' => 10008,
        'race_id' => 100801,
        'fans_gain_curve' => 21,
        'track_name' => null,
        'source_url' => 'https://gametora.com/data/umamusume/race_instances.294424fc.json',
        'snapshot_path' => 'snapshots/gametora-race-catalog/existing.html',
        'fetched_at' => null,
    ]);

    $this->artisan('uma:enrich-races')->assertExitCode(0);

    $slot->refresh();

    // One row, one set of the four R3 columns: the row names every dataset whose values it now holds
    // rather than whichever happened to be written last. Four `data_sources` rows are not available
    // here — that table is `umamusume_id`-scoped and NOT NULL, and a calendar slot has no trainee.
    expect($slot->source_url)
        ->toContain('racetracks_extended')
        ->toContain('race-fans')
        ->toContain('races.')
        ->toContain('race-rewards')
        // The calendar document is not one of this row's sources any more.
        ->not->toContain('race_instances')
        ->and($slot->snapshot_path)
        // Directories, not the `<hash>.html` files: four full paths overflow the column's 255
        // characters, and the directory is what the command itself resolves (newest body inside it).
        ->toContain('snapshots/gametora-racetracks')
        ->toContain('snapshots/gametora-race-fans')
        ->toContain('snapshots/gametora-races')
        ->toContain('snapshots/gametora-race-rewards')
        ->and(mb_strlen((string) $slot->snapshot_path))->toBeLessThanOrEqual(255)
        ->and($slot->fetched_at)->not->toBeNull()
        ->and($slot->source_timezone)->toBe('Asia/Tokyo');
});

it('names both skip reasons in its summary, and counts them apart', function (): void {
    seedEnrichRacesSnapshots();

    RaceCatalogSlot::factory()->create([
        'title' => 'Corrected By Hand',
        'track_id' => 10008,
        'race_id' => 100801,
        'fans_gain_curve' => 21,
        'is_manual' => true,
    ]);

    // The eight special calendar rows: no track_id, race_id or fan curve, so no source can match
    // them. The skip is by design and must stay distinguishable from the manual one.
    RaceCatalogSlot::factory()->debut()->create();

    // An already-enriched row, so `unchanged` is exercised too rather than folded into `enriched`.
    RaceCatalogSlot::factory()->create([
        'title' => 'Already Done',
        'track_id' => 10008,
        'race_id' => 100801,
        'fans_gain_curve' => 21,
        'track_name' => 'Tokyo',
        'fans_first' => 11000,
        'fans_second' => 4400,
        'fans_third' => 2750,
        'course' => 1,
        'direction' => 2,
        'season' => 0,
        'entries' => 18,
        'factor_effect_1' => 'speed',
        'factor_effect_2' => 'guts',
        'reward_summary' => '[{"item":"1001","num_min":1,"num_max":1,"chance":1}]',
        'direction_label' => 'Left-Handed',
        'course_label' => 'Inner',
        'season_label' => 'Spring',
    ]);

    // One substring, because the summary is a single `info()` line and Laravel's console-output fake
    // can only attribute one `doWrite` call to one expected substring. Asserting the whole sentence
    // also pins the order and both counts, which is what the operator reads.
    $this->artisan('uma:enrich-races')
        ->expectsOutputToContain('Enrichment complete: 0 enriched, 1 skipped (no match), 1 skipped (manual), 1 unchanged.')
        ->assertExitCode(0);
});
