<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\SupportEffect;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Persist the parsed support-effect dictionary with its provenance attached (ADR-0014; PRD FR-B-5).
 *
 * **The grain is the export's own `id`, written into `effect_id`.** A re-import updates the 35 rows it
 * already owns instead of colliding with the unique index, the same way `StoreSupportCards` keys on
 * `support_id` and `StoreSkills` on `export_id`. Those ids are what the anchor vectors on
 * `support_cards.effects` point at, so a duplicated or renumbered dictionary row would mislabel a card.
 *
 * **This table cannot honour the `is_manual` stop sign.** `support_effects` has no `is_manual` column
 * (neither `2026_09_30_142618` nor `2026_09_30_151945` adds one), so PRD FR-B-4 has nothing to read
 * here and every row is source-owned. It is stated rather than fixed because inventing a guard for a
 * column that does not exist would be a stub, and adding the column is a schema change owned by the
 * Architect, not by an import path. The cards table does honour it.
 *
 * **`calc` nulls are written as nulls.** Only four of the 35 records declare a mode, and
 * UMAMUSUME_REFERENCE.md §1.4.8 reads the absence itself as the fact, so an update that finds no `calc`
 * on a record which used to carry one writes null rather than keeping the stale word: the whole point of
 * the column is that its emptiness means something. A value outside `mult` / `add` reaches the column
 * CHECK and fails the run loudly rather than being filtered into a null that would read as "no mode".
 *
 * **No timestamps.** `SupportEffect::$timestamps` is false; the dictionary is reference data whose only
 * time fact is `fetched_at`.
 */
final class StoreSupportEffects
{
    /**
     * @param  list<array{effect_id: int, name_en: string, name_ja: string|null, calc: string|null,
     *                   symbol: string|null, description_en: string|null}>  $records
     * @return array{created: int, updated: int, skipped: int}
     */
    public function handle(array $records, string $url): array
    {
        return DB::transaction(function () use ($records, $url): array {
            $counts = ['created' => 0, 'updated' => 0, 'skipped' => 0];

            foreach ($records as $record) {
                $existing = $this->find($record);

                $payload = [
                    'name_en' => $record['name_en'],
                    'name_ja' => $record['name_ja'],
                    'calc' => $record['calc'],
                    'symbol' => $record['symbol'],
                    'description_en' => $record['description_en'],
                    ...$this->provenance($url),
                ];

                if ($existing === null) {
                    SupportEffect::create([...$payload, 'effect_id' => $record['effect_id']]);
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
     * @param  array{effect_id: int}  $record
     */
    private function find(array $record): ?SupportEffect
    {
        return SupportEffect::query()->where('effect_id', $record['effect_id'])->first();
    }

    /**
     * @return array{source_url: string, fetched_at: Carbon}
     */
    private function provenance(string $url): array
    {
        return [
            'source_url' => $url,
            'fetched_at' => now(),
        ];
    }
}
