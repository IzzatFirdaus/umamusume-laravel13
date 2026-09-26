<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ReleaseStatus;
use App\Models\DataSource;
use App\Models\Umamusume;
use App\Services\DataPipeline\NameNormalizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Upsert one cross-referenced catalog record inside a transaction and attach
 * its provenance row (PRD FR-B, AGENTS.md Data Engineer rules). Engine-owned
 * columns only (slug/name never rewritten); a row flagged is_manual is skipped
 * and reported, never overwritten.
 */
final class PromoteMatchedRecord
{
    public function __construct(private readonly NameNormalizer $normalizer) {}

    /**
     * Upsert one parsed catalog record with provenance. Never touches is_manual rows.
     *
     * @param  array{name: string, name_ja?: string|null, release_status?: string|null, jp_debut_date?: string|null, global_debut_date?: string|null}  $record
     * @return array{umamusume: Umamusume, created: bool, skipped: bool}
     */
    public function handle(
        array $record,
        ?Umamusume $existing,
        string $sourceKey,
        string $url,
        ?string $snapshotPath = null,
        ?float $confidence = null,
        ?string $sourceTimezone = null,
    ): array {
        if ($existing !== null && $existing->is_manual) {
            return ['umamusume' => $existing, 'created' => false, 'skipped' => true];
        }

        return DB::transaction(function () use ($record, $existing, $sourceKey, $url, $snapshotPath, $confidence, $sourceTimezone): array {
            $matchKey = $this->normalizer->normalize($record['name']);
            $releaseStatus = ReleaseStatus::tryFrom((string) ($record['release_status'] ?? ''));

            if ($existing === null) {
                $umamusume = new Umamusume([
                    'slug' => $this->uniqueSlug($record['name']),
                    'name' => $record['name'],
                    'name_ja' => $record['name_ja'] ?? null,
                    'match_key' => $matchKey,
                    'release_status' => $releaseStatus ?? ReleaseStatus::GlobalReleased,
                    'jp_debut_date' => $record['jp_debut_date'] ?? null,
                    'global_debut_date' => $record['global_debut_date'] ?? null,
                ]);
                $umamusume->save();
                $created = true;
            } else {
                $existing->fill([
                    'match_key' => $matchKey,
                    'name_ja' => $record['name_ja'] ?? $existing->name_ja,
                    'jp_debut_date' => $record['jp_debut_date'] ?? $existing->jp_debut_date,
                    'global_debut_date' => $record['global_debut_date'] ?? $existing->global_debut_date,
                ]);

                if ($releaseStatus !== null) {
                    $existing->release_status = $releaseStatus;
                }

                $existing->save();
                $umamusume = $existing;
                $created = false;
            }

            DataSource::create([
                'umamusume_id' => $umamusume->id,
                'url' => $url,
                'source_key' => $sourceKey,
                'fetched_at' => now(),
                'snapshot_path' => $snapshotPath,
                'confidence' => $confidence,
                'source_timezone' => $sourceTimezone,
            ]);

            return ['umamusume' => $umamusume, 'created' => $created, 'skipped' => false];
        });
    }

    private function uniqueSlug(string $name): string
    {
        $slug = Str::slug($name);

        if ($slug === '') {
            $slug = 'umamusume';
        }

        $candidate = $slug;
        $suffix = 2;

        while (Umamusume::where('slug', $candidate)->exists()) {
            $candidate = "{$slug}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }
}
