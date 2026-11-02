<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\RaceCatalogSlot;
use App\Services\DataPipeline\Parsers\GametoraRaceFansParser;
use App\Services\DataPipeline\Parsers\GametoraRaceParser;
use App\Services\DataPipeline\Parsers\GametoraRaceRewardParser;
use App\Services\DataPipeline\Parsers\GametoraRacetrackParser;
use App\Services\DataPipeline\SourceFetcher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EnrichRaces extends Command
{
    protected $signature = 'uma:enrich-races';

    protected $description = 'Enrich race_catalog_slots from gametora race snapshots';

    public function handle(SourceFetcher $fetcher): int
    {

        // 1 & 2: read/fetch snapshots and parse.
        $tracksBody = $this->snapshotBody('gametora-racetracks', $fetcher);
        $fansBody = $this->snapshotBody('gametora-race-fans', $fetcher);
        $racesBody = $this->snapshotBody('gametora-races', $fetcher);
        $rewardsBody = $this->snapshotBody('gametora-race-rewards', $fetcher);

        $tracksParsed = (new GametoraRacetrackParser)->parse($tracksBody ?? '');
        $fansParsed = (new GametoraRaceFansParser)->parse($fansBody ?? '');
        $racesParsed = (new GametoraRaceParser)->parse($racesBody ?? '');
        $rewardsParsed = (new GametoraRaceRewardParser)->parse($rewardsBody ?? '');

        // 3: build lookup maps.
        $tracks = [];
        foreach ($tracksParsed as $t) {
            if (! isset($t['id'])) {
                continue;
            }
            $tracks[$t['id']] = ['name_en' => $t['name_en'] ?? null];
        }

        $fans = [];
        foreach ($fansParsed as $f) {
            if (! isset($f['id'])) {
                continue;
            }
            $fans[$f['id']] = [
                'first' => $f['first'] ?? null,
                'second' => $f['second'] ?? null,
                'third' => $f['third'] ?? null,
            ];
        }

        $races = [];
        foreach ($racesParsed as $r) {
            if (! isset($r['id'])) {
                continue;
            }
            $races[$r['id']] = [
                'course' => $r['course'] ?? null,
                'direction' => $r['direction'] ?? null,
                'season' => $r['season'] ?? null,
                'entries' => $r['entries'] ?? null,
                'factor_effect_1' => $r['factor_effect_1'] ?? null,
                'factor_effect_2' => $r['factor_effect_2'] ?? null,
            ];
        }

        $rewards = [];
        foreach ($rewardsParsed as $rw) {
            if (! isset($rw['id'])) {
                continue;
            }
            $rewards[$rw['id']] = $rw['rewards'];
        }

        $directionMap = [1 => 'Right-Handed', 2 => 'Left-Handed', 4 => null];
        $courseMap = [1 => 'Inner', 2 => 'Outer', 3 => null];
        $seasonMap = [0 => 'Spring', 1 => 'Summer', 2 => 'Early Autumn', 3 => 'Autumn', 4 => 'Winter (early)', 5 => 'Winter (late)'];

        $enriched = 0;
        $skipped = 0;
        $unchanged = 0;

        DB::transaction(function () use (
            $tracks, $fans, $races, $rewards,
            $directionMap, $courseMap, $seasonMap,
            &$enriched, &$skipped, &$unchanged
        ): void {
            foreach (RaceCatalogSlot::all() as $slot) {
                $matched = false;
                $new = [];

                if (array_key_exists($slot->track_id, $tracks)) {
                    $new['track_name'] = $tracks[$slot->track_id]['name_en'];
                    $matched = true;
                }

                if (array_key_exists($slot->fans_gain_curve, $fans)) {
                    $f = $fans[$slot->fans_gain_curve];
                    $new['fans_first'] = $f['first'];
                    $new['fans_second'] = $f['second'];
                    $new['fans_third'] = $f['third'];
                    $matched = true;
                }

                if (array_key_exists($slot->race_id, $races)) {
                    $r = $races[$slot->race_id];
                    $new['course'] = $r['course'];
                    $new['direction'] = $r['direction'];
                    $new['season'] = $r['season'];
                    $new['entries'] = $r['entries'];
                    $new['factor_effect_1'] = $r['factor_effect_1'];
                    $new['factor_effect_2'] = $r['factor_effect_2'];
                    $new['direction_label'] = array_key_exists($r['direction'], $directionMap) ? $directionMap[$r['direction']] : null;
                    $new['course_label'] = array_key_exists($r['course'], $courseMap) ? $courseMap[$r['course']] : null;
                    $new['season_label'] = array_key_exists($r['season'], $seasonMap) ? $seasonMap[$r['season']] : null;
                    $matched = true;
                }

                if (array_key_exists($slot->race_id, $rewards)) {
                    $new['reward_summary'] = json_encode($rewards[$slot->race_id]);
                    $matched = true;
                }

                // The eight rows that stay unenriched are the special calendar races (the debut, the
                // maiden race, and the URA Finals rounds). They carry no track_id, race_id or fan
                // curve at all, so no Global source can match them: the skip is by design, not a
                // missing join key. Everything that names a real racecourse does match.
                if (! $matched) {
                    $skipped++;

                    continue;
                }

                // Only save if changed.
                $changed = false;
                foreach ($new as $key => $value) {
                    if ($slot->{$key} !== $value) {
                        $changed = true;

                        break;
                    }
                }

                if (! $changed) {
                    $unchanged++;

                    continue;
                }

                foreach ($new as $key => $value) {
                    $slot->{$key} = $value;
                }
                $slot->save();
                $enriched++;
            }
        });

        $this->info(sprintf(
            'Enrichment complete: %d enriched, %d skipped (no match), %d unchanged.',
            $enriched,
            $skipped,
            $unchanged
        ));

        return self::SUCCESS;
    }

    private function snapshotBody(string $sourceKey, SourceFetcher $fetcher): ?string
    {
        $directory = "snapshots/{$sourceKey}";

        if (Storage::disk('local')->exists($directory)) {
            $files = collect(Storage::disk('local')->allFiles($directory))
                ->filter(fn (string $path): bool => str_ends_with($path, '.html'));

            $newest = $files
                ->sortBy(fn (string $path): int => Storage::disk('local')->lastModified($path))
                ->last();

            if ($newest !== null) {
                return (string) Storage::disk('local')->get($newest);
            }
        }

        // Fetch via SourceFetcher if missing.
        /** @var array<string, array<string, mixed>> $sources */
        $sources = config('uma.sources', []);

        if (! isset($sources[$sourceKey])) {
            $this->warn("Source '{$sourceKey}' not declared in config; skipping fetch.");

            return null;
        }

        $fetched = $fetcher->fetch($sourceKey, $sources[$sourceKey]);

        if ($fetched === null) {
            $this->error("Fetch failed for '{$sourceKey}'.");

            return null;
        }

        return $fetched['body'];
    }
}
