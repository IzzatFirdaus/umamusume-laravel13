<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\RaceCatalogSlot;
use Illuminate\Support\Carbon;

/**
 * Persist parsed race-catalogue rows with their provenance attached.
 *
 * ADR-0003 Amendment R3 requires every reference row to carry `source_url`,
 * `snapshot_path`, `fetched_at` and `source_timezone`. The parser cannot supply
 * those — it sees only the document body — so they are stamped here, at the point
 * where the fetch actually happened.
 *
 * Rows are matched on the same null-coalescing grain the unique index uses, so a
 * re-run updates in place instead of colliding. A row a Trainer wrote by hand is
 * never touched: `is_manual` is the engine's stop sign (PRD FR-B-4).
 */
final class StoreRaceCatalogSlots
{
    /**
     * @param  list<array<string, mixed>>  $records
     * @return array{created: int, updated: int, skipped: int}
     */
    public function handle(array $records, string $url, ?string $snapshotPath, ?string $timezone): array
    {
        $counts = ['created' => 0, 'updated' => 0, 'skipped' => 0];

        foreach ($records as $record) {
            $existing = $this->find($record);

            if ($existing !== null && $existing->is_manual) {
                $counts['skipped']++;

                continue;
            }

            $payload = [...$record, ...$this->provenance($url, $snapshotPath, $timezone)];

            if ($existing === null) {
                RaceCatalogSlot::create($payload);
                $counts['created']++;

                continue;
            }

            $existing->update($payload);
            $counts['updated']++;
        }

        return $counts;
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function find(array $record): ?RaceCatalogSlot
    {
        // The same null-coalescing expressions the unique index is built on, so a
        // lookup and the constraint can never disagree about what "the same row"
        // means for a shared slot, where scenario_key is genuinely NULL.
        return RaceCatalogSlot::query()
            ->whereRaw("IFNULL(scenario_key, '') = ?", [$record['scenario_key'] ?? ''])
            ->where('year', $record['year'])
            ->whereRaw('IFNULL(month, 0) = ?', [$record['month'] ?? 0])
            ->whereRaw("IFNULL(half, '') = ?", [$record['half'] ?? ''])
            ->where('title', $record['title'])
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
