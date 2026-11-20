<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\RaceCatalogSlot;
use App\Services\DataPipeline\Parsers\GametoraRaceFansParser;
use App\Services\DataPipeline\Parsers\GametoraRaceParser;
use App\Services\DataPipeline\Parsers\GametoraRaceRewardParser;
use App\Services\DataPipeline\Parsers\GametoraRacetrackParser;
use App\Services\DataPipeline\SourceFetcher;
use Database\Seeders\ReadsCommittedSource;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EnrichRaces extends Command
{
    use ReadsCommittedSource;

    protected $signature = 'uma:enrich-races {--from-seed : Read committed seed bodies instead of snapshots or the network}';

    protected $description = 'Enrich race_catalog_slots from gametora race snapshots';

    public function handle(SourceFetcher $fetcher): int
    {
        // 1 & 2: read/fetch snapshots and parse. Each body is kept beside the path it came from,
        // because that path is the provenance stamped on every row the body enriches.
        // `--from-seed` is the offline path: the committed bodies are the same source documents a
        // fresh clone ships, so a machine with no snapshot history and no network still enriches.
        $fromSeed = (bool) $this->option('from-seed');
        $tracks = $fromSeed ? $this->committedBody('gametora-racetracks') : $this->snapshot('gametora-racetracks', $fetcher);
        $fans = $fromSeed ? $this->committedBody('gametora-race-fans') : $this->snapshot('gametora-race-fans', $fetcher);
        $races = $fromSeed ? $this->committedBody('gametora-races') : $this->snapshot('gametora-races', $fetcher);
        $rewards = $fromSeed ? $this->committedBody('gametora-race-rewards') : $this->snapshot('gametora-race-rewards', $fetcher);

        /** @var array<string, array{url: string}> $provenance */
        $provenance = [
            'gametora-racetracks' => ['url' => $tracks['url']],
            'gametora-race-fans' => ['url' => $fans['url']],
            'gametora-races' => ['url' => $races['url']],
            'gametora-race-rewards' => ['url' => $rewards['url']],
        ];

        $tracksParsed = (new GametoraRacetrackParser)->parse($tracks['body'] ?? '');
        $fansParsed = (new GametoraRaceFansParser)->parse($fans['body'] ?? '');
        $racesParsed = (new GametoraRaceParser)->parse($races['body'] ?? '');
        $rewardsParsed = (new GametoraRaceRewardParser)->parse($rewards['body'] ?? '');

        // 3: build lookup maps.
        $trackMap = [];
        foreach ($tracksParsed as $t) {
            if (! isset($t['id'])) {
                continue;
            }
            $trackMap[$t['id']] = ['name_en' => $t['name_en'] ?? null];
        }

        $fanMap = [];
        foreach ($fansParsed as $f) {
            if (! isset($f['id'])) {
                continue;
            }
            $fanMap[$f['id']] = [
                'first' => $f['first'] ?? null,
                'second' => $f['second'] ?? null,
                'third' => $f['third'] ?? null,
            ];
        }

        $raceMap = [];
        foreach ($racesParsed as $r) {
            if (! isset($r['id'])) {
                continue;
            }
            $raceMap[$r['id']] = [
                'course' => $r['course'] ?? null,
                'direction' => $r['direction'] ?? null,
                'season' => $r['season'] ?? null,
                'entries' => $r['entries'] ?? null,
                'factor_effect_1' => $r['factor_effect_1'] ?? null,
                'factor_effect_2' => $r['factor_effect_2'] ?? null,
            ];
        }

        $rewardMap = [];
        foreach ($rewardsParsed as $rw) {
            if (! isset($rw['id'])) {
                continue;
            }
            $rewardMap[$rw['id']] = $rw['rewards'];
        }

        $directionMap = [1 => 'Right-Handed', 2 => 'Left-Handed', 4 => null];
        $courseMap = [1 => 'Inner', 2 => 'Outer', 3 => null];
        $seasonMap = [0 => 'Spring', 1 => 'Summer', 2 => 'Early Autumn', 3 => 'Autumn', 4 => 'Winter (early)', 5 => 'Winter (late)'];

        $enriched = 0;
        $skipped = 0;
        $skippedManual = 0;
        $unchanged = 0;

        DB::transaction(function () use (
            $trackMap, $fanMap, $raceMap, $rewardMap, $provenance,
            $directionMap, $courseMap, $seasonMap,
            &$enriched, &$skipped, &$skippedManual, &$unchanged
        ): void {
            foreach (RaceCatalogSlot::all() as $slot) {
                // PRD B-4 / FR-B-4: `is_manual` rows are immutable to the engine, the same stop sign
                // every sibling writer honours (`StoreRaceCatalogSlots`, `StoreSkills`,
                // `StoreCharacterCards`, `PromoteMatchedRecord`). Counted apart from `$skipped`, which
                // means "no source matched this row": an operator reading the summary needs to know
                // whether a figure is low because the datasets stopped covering a race or because the
                // Trainer corrected it by hand.
                if ($slot->is_manual) {
                    $skippedManual++;

                    continue;
                }

                $matched = false;
                /** @var list<string> $contributors */
                $contributors = [];
                $new = [];

                if (array_key_exists($slot->track_id, $trackMap)) {
                    $new['track_name'] = $trackMap[$slot->track_id]['name_en'];
                    $contributors[] = 'gametora-racetracks';
                    $matched = true;
                }

                if (array_key_exists($slot->fans_gain_curve, $fanMap)) {
                    $f = $fanMap[$slot->fans_gain_curve];
                    $new['fans_first'] = $f['first'];
                    $new['fans_second'] = $f['second'];
                    $new['fans_third'] = $f['third'];
                    $contributors[] = 'gametora-race-fans';
                    $matched = true;
                }

                if (array_key_exists($slot->race_id, $raceMap)) {
                    $r = $raceMap[$slot->race_id];
                    $new['course'] = $r['course'];
                    $new['direction'] = $r['direction'];
                    $new['season'] = $r['season'];
                    $new['entries'] = $r['entries'];
                    $new['factor_effect_1'] = $r['factor_effect_1'];
                    $new['factor_effect_2'] = $r['factor_effect_2'];
                    $new['direction_label'] = array_key_exists($r['direction'], $directionMap) ? $directionMap[$r['direction']] : null;
                    $new['course_label'] = array_key_exists($r['course'], $courseMap) ? $courseMap[$r['course']] : null;
                    $new['season_label'] = array_key_exists($r['season'], $seasonMap) ? $seasonMap[$r['season']] : null;
                    $contributors[] = 'gametora-races';
                    $matched = true;
                }

                if (array_key_exists($slot->race_id, $rewardMap)) {
                    $new['reward_summary'] = json_encode($rewardMap[$slot->race_id]);
                    $contributors[] = 'gametora-race-rewards';
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

                // Provenance travels with the write, not beside it. Before this the row kept the
                // calendar document's `source_url` while carrying four other publishers' facts, so a
                // reader checking "where did this track name come from" was sent to a file that does
                // not contain it. Written only when a value changed, which is what `fetched_at` then
                // truthfully means: the last time this row's enrichment columns moved.
                $new = [...$new, ...$this->provenance($contributors, $provenance)];

                foreach ($new as $key => $value) {
                    $slot->{$key} = $value;
                }
                $slot->save();
                $enriched++;
            }
        });

        $this->info(sprintf(
            'Enrichment complete: %d enriched, %d skipped (no match), %d skipped (manual), %d unchanged.',
            $enriched,
            $skipped,
            $skippedManual,
            $unchanged
        ));

        return self::SUCCESS;
    }

    /**
     * The provenance this command stamps on a slot it changed.
     *
     * Four documents contribute to one calendar row, and `race_catalog_slots` carries one set of the
     * four `ADR-0003` Amendment R3 columns, so the row names every dataset that contributed rather
     * than whichever one happened to run last: a comma-joined list, which is the only shape a single
     * provenance column can hold for a fact with four publishers. Before this the row kept the
     * calendar document's `source_url` while carrying four other publishers' facts, so a reader
     * checking "where did this track name come from" was sent to a file that does not contain it.
     *
     * `snapshot_path` names each source's snapshot **directory**, not a file, for two reasons that
     * point the same way. Four full `<hash>.html` paths run to roughly 390 characters against a
     * column declared `string()` (255), and a path cut at the column boundary is a provenance record
     * pointing at bytes that are not there. The directory is also exactly what `snapshot()` resolves:
     * it reads the newest body inside it, so "the newest body in `snapshots/gametora-races`" stays
     * true after the next fetch adds a file, where a pinned filename would not.
     *
     * A `data_sources` row per source is not available to this writer: that table is
     * `umamusume_id`-scoped and the column is NOT NULL with a foreign key to `umamusume`
     * (`2026_09_26_162819`), while a calendar slot belongs to no trainee. `ADR-0008` states the same
     * reading from the other side: `data_sources` is the character-level table behind FR-A-4, not a
     * home for reference-row provenance, which is why `race_catalog_slots`, `scenario_races` and
     * `scenario_slots` each carry the four columns inline instead — the pattern
     * `StoreRaceCatalogSlots::provenance()` writes, and the one this mirrors.
     *
     * @param  list<string>  $contributors  the source keys whose values this slot now holds
     * @param  array<string, array{url: string}>  $provenance
     * @return array{source_url: string, snapshot_path: string, fetched_at: Carbon, source_timezone: string|null}
     */
    private function provenance(array $contributors, array $provenance): array
    {
        $urls = [];
        $directories = [];
        $timezone = null;

        foreach ($contributors as $key) {
            $urls[] = $provenance[$key]['url'];
            $directories[] = "snapshots/{$key}";
            $timezone ??= $this->sourceTimezone($key);
        }

        return [
            'source_url' => implode(', ', array_filter($urls)),
            'snapshot_path' => implode(', ', $directories),
            'fetched_at' => now(),
            'source_timezone' => $timezone,
        ];
    }

    private function sourceTimezone(string $sourceKey): ?string
    {
        /** @var array<string, mixed>|null $source */
        $source = config("uma.sources.{$sourceKey}");

        return is_array($source) && isset($source['timezone']) ? (string) $source['timezone'] : null;
    }

    /**
     * The committed body for `--from-seed`, with the declared URL as provenance.
     *
     * @return array{body: string|null, url: string}
     */
    private function committedBody(string $sourceKey): array
    {
        /** @var array<string, mixed>|null $source */
        $source = config("uma.sources.{$sourceKey}");
        $declared = is_array($source) && isset($source['url']) ? (string) $source['url'] : '';

        if (! is_array($source)) {
            return ['body' => null, 'url' => $declared];
        }

        $committed = $this->readCommittedSource($sourceKey, $source);

        return $committed === null
            ? ['body' => null, 'url' => $declared]
            : ['body' => $committed['body'], 'url' => $declared];
    }

    /**
     * The newest stored body for one source, with the address it came from.
     *
     * The address travels with the body because the caller stamps it as provenance. A body read back
     * from disk has no address of its own, so the source's declared URL stands in for it; a body
     * fetched now carries the address that actually answered, redirect hops included (`SourceFetcher`
     * reports it for exactly this reason).
     *
     * @return array{body: string|null, url: string}
     */
    private function snapshot(string $sourceKey, SourceFetcher $fetcher): array
    {
        /** @var array<string, mixed>|null $source */
        $source = config("uma.sources.{$sourceKey}");
        $declared = is_array($source) && isset($source['url']) ? (string) $source['url'] : '';

        $directory = "snapshots/{$sourceKey}";

        if (Storage::disk('local')->exists($directory)) {
            $files = collect(Storage::disk('local')->allFiles($directory))
                ->filter(fn (string $path): bool => str_ends_with($path, '.html'));

            $newest = $files
                ->sortBy(fn (string $path): int => Storage::disk('local')->lastModified($path))
                ->last();

            if ($newest !== null) {
                return [
                    'body' => (string) Storage::disk('local')->get($newest),
                    'url' => $declared,
                ];
            }
        }

        // Fetch via SourceFetcher if missing.
        /** @var array<string, array<string, mixed>> $sources */
        $sources = config('uma.sources', []);

        if (! isset($sources[$sourceKey])) {
            $this->warn("Source '{$sourceKey}' not declared in config; skipping fetch.");

            return ['body' => null, 'url' => $declared];
        }

        $fetched = $fetcher->fetch($sourceKey, $sources[$sourceKey]);

        if ($fetched === null) {
            $this->error("Fetch failed for '{$sourceKey}'.");

            return ['body' => null, 'url' => $declared];
        }

        return [
            'body' => $fetched['body'],
            'url' => $fetched['url'],
        ];
    }
}
