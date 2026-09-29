<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Skill;
use App\Services\DataPipeline\NameNormalizer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Persist parsed skill rows with their provenance attached (ADR-0011; PRD FR-D-1, FR-B).
 *
 * `ADR-0003` Amendment R3 requires `source_url`, `snapshot_path`, `fetched_at` and `source_timezone` on
 * every reference row; the parser cannot supply them because it sees only the document body, so they are
 * stamped here, at the point where the fetch actually happened — the same split `StoreRaceCatalogSlots`
 * uses.
 *
 * **The grain is the source's id, not the name.** `export_id` is how a re-run finds its own row, and a
 * name is not a key: the same client string can be re-spelled by the publisher, and two skills can share
 * a rendering. Rows are adopted in one narrow case, described below, and in no other.
 *
 * **Unattributed rows are adopted once, not merged by name forever.** The table already holds ten
 * `SkillSeeder` rows carrying a name and a match key and nothing else — no `export_id`, because nothing
 * attributed them. Matching on name *only while `export_id` is null* gives those rows their source id on
 * the first run and never again, so the second run cannot silently repoint one at a different skill. A
 * Trainer who later adds a row by hand is protected by `is_manual` instead: the engine's stop sign
 * (PRD FR-B-4) is checked before any lookup result is used.
 *
 * **Source-owned columns are overwritten wholesale.** `PromoteMatchedRecord` deliberately keeps absent
 * aptitude letters from blanking stored ones because a second publisher may have written them. Nothing
 * else writes these columns: one document owns name, cost, class code, availability and the derived
 * category, so a null from that document is a statement, and a merge rule here would only hide a source
 * that stopped asserting something.
 *
 * All rows land in one transaction. NFR-4 asks that for a multi-write operation, and NFR-2's promise is
 * that a failed fetch leaves the catalog unblanked — a half-imported skill table would break both.
 */
final class StoreSkills
{
    public function __construct(private readonly NameNormalizer $normalizer) {}

    /**
     * @param  list<array{export_id: int, name: string, name_ja: string|null, name_is_client: bool,
     *                   release_status: string, rarity: int|null, is_unique: bool, sp_cost: int|null, type: string|null}>  $records
     * @return array{created: int, updated: int, skipped: int}
     */
    public function handle(array $records, string $url, ?string $snapshotPath, ?string $timezone): array
    {
        return DB::transaction(function () use ($records, $url, $snapshotPath, $timezone): array {
            $counts = ['created' => 0, 'updated' => 0, 'skipped' => 0];

            foreach ($records as $record) {
                $existing = $this->find($record);

                if ($existing !== null && $existing->is_manual) {
                    $counts['skipped']++;

                    continue;
                }

                $payload = [
                    ...$record,
                    // FR-D-2 searches on normalized keys, so every row needs one whether or not a screen
                    // renders it. Derived here rather than in the parser, because a parser takes a body
                    // and nothing else, and the normalizer is an application service.
                    'match_key' => $this->normalizer->normalize($record['name']),
                    ...$this->provenance($url, $snapshotPath, $timezone),
                ];

                if ($existing === null) {
                    Skill::create($payload);
                    $counts['created']++;

                    continue;
                }

                $existing->update($payload);
                $counts['updated']++;
            }

            return $counts;
        });
    }

    /**
     * @param  array{export_id: int, name: string}  $record
     */
    private function find(array $record): ?Skill
    {
        $byExportId = Skill::query()->where('export_id', $record['export_id'])->first();

        if ($byExportId !== null) {
            return $byExportId;
        }

        // The one adoption case: a seeded row no source has ever attributed, matched on the exact
        // display name it carries. `whereNull('export_id')` is what keeps this from becoming a name
        // join that runs forever and can silently repoint an attributed row at a different skill.
        return Skill::query()
            ->where('name', $record['name'])
            ->whereNull('export_id')
            ->first();
    }

    /**
     * @return array{source_url: string, snapshot_path: string|null, fetched_at: Carbon, source_timezone: string|null}
     */
    private function provenance(string $url, ?string $snapshotPath, ?string $timezone): array
    {
        return [
            'source_url' => $url,
            'snapshot_path' => $snapshotPath,
            'fetched_at' => now(),
            'source_timezone' => $timezone,
        ];
    }
}
