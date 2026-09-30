<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\SupportCard;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Persist parsed support-card rows with their provenance attached (ADR-0014; PRD FR-B-4, FR-B-5).
 *
 * **The grain is the export's own `support_id`.** A re-run finds the row it wrote last time and updates
 * it, which is FR-B-5's requirement that a fetch be idempotent against its own source. There is no
 * second lookup by name, the way `StoreSkills` has one: `support_cards` holds no stored name to adopt
 * (the export publishes a character name and an epithet separately, and `SupportCard::displayName()`
 * composes them at read time), and nothing in this repository seeds a support-card row by hand before
 * the first import, so the case an adoption rule would serve does not exist yet.
 *
 * **Provenance here is two columns, not the four `ADR-0003` Amendment R3 names.** `support_cards`
 * carries `source_url` and `fetched_at` and has no `snapshot_path` or `source_timezone` column
 * (`2026_09_30_142618`, unchanged by `2026_09_30_151945`), so this Action takes the URL and stamps the
 * fetch time, and the caller's snapshot path is reported by the command rather than written into a
 * column that does not exist. That is a stated gap in R3's coverage, not a rule this class invented.
 *
 * **`is_manual` is checked before any lookup result is used** (PRD FR-B-4). A card the Trainer corrected
 * by hand keeps its correction across every re-import, and is counted as skipped rather than silently
 * absent.
 *
 * **`release_status` is never written.** It is a stored generated column over `release_jp` and
 * `release_global`; it is not in `SupportCard`'s `#[Fillable]`, and a payload naming it would be
 * discarded rather than persisted. The two source dates are the fact; the status is its reading.
 *
 * **The anchor vector is written as the parser produced it**: `list<list<int>>`, twelve wide, `-1`
 * meaning no anchor at that card level (UMAMUSUME_REFERENCE.md §1.4.7). Expanding it to fifty levels
 * here would materialise the ladder ADR-0014 correction 4 refuses, and would put a second copy of the
 * interpolation rule in the writer where the reader cannot see it.
 *
 * **Columns are projected by name rather than spread from the record**, the way `StoreCharacterCards`
 * does: a record also carries nothing that is not a column today, but a spread turns a future extra key
 * into a silent drop, and projecting makes it a decision.
 *
 * All rows land in one transaction, as `StoreSkills` does, so a failed import leaves the previous
 * catalogue whole rather than half-written (NFR-2, NFR-4).
 */
final class StoreSupportCards
{
    /**
     * @param  list<array{support_id: int, char_id: int, char_name: string|null, name_ja: string|null,
     *                   title_en: string|null, title_ja: string|null, rarity: int, type: string,
     *                   release_jp: string|null, release_global: string|null, effects: list<list<int>>}>  $records
     * @return array{created: int, updated: int, skipped: int}
     */
    public function handle(array $records, string $url): array
    {
        return DB::transaction(function () use ($records, $url): array {
            $counts = ['created' => 0, 'updated' => 0, 'skipped' => 0];

            foreach ($records as $record) {
                $existing = $this->find($record);

                if ($existing !== null && $existing->is_manual) {
                    $counts['skipped']++;

                    continue;
                }

                $payload = [
                    'char_id' => $record['char_id'],
                    'char_name' => $record['char_name'],
                    'name_ja' => $record['name_ja'],
                    'title_en' => $record['title_en'],
                    'title_ja' => $record['title_ja'],
                    'rarity' => $record['rarity'],
                    'type' => $record['type'],
                    'release_jp' => $record['release_jp'],
                    'release_global' => $record['release_global'],
                    'effects' => $record['effects'],
                    ...$this->provenance($url),
                ];

                if ($existing === null) {
                    SupportCard::create([...$payload, 'support_id' => $record['support_id']]);
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
     * @param  array{support_id: int}  $record
     */
    private function find(array $record): ?SupportCard
    {
        return SupportCard::query()->where('support_id', $record['support_id'])->first();
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
