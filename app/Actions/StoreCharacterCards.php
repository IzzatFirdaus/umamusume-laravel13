<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\CharacterCard;
use App\Models\Umamusume;
use Illuminate\Support\Carbon;

/**
 * Persist parsed character-card rows with their provenance attached.
 *
 * ADR-0003 Amendment R3 requires every reference row to carry `source_url`,
 * `snapshot_path`, `fetched_at` and `source_timezone`. The parser cannot supply
 * those — it sees only the document body — so they are stamped here, at the point
 * where the fetch actually happened.
 *
 * Rows are matched on `card_id`, the source's own id, so a re-fetch updates the
 * row it already wrote instead of colliding with it (PRD FR-B-5). The trainee a
 * card belongs to is the one its char ref names through `umamusume.external_ref`,
 * never inferred from a string.
 *
 * `unconfirmed` is never written: Task 8 owns that cross-check verdict and a fetch
 * does not get to clear a flag it did not set. `is_manual` (PRD FR-B-4) is read at
 * both grains — a trainee the Trainer wrote by hand receives no cards at all, and a
 * single card she corrected by hand is skipped while her unlocked siblings update.
 */
final class StoreCharacterCards
{
    /**
     * @param  list<array<string, mixed>>  $records
     * @return array{created: int, updated: int, skipped: int}
     */
    public function handle(array $records, string $url, ?string $snapshotPath, ?string $timezone): array
    {
        $counts = ['created' => 0, 'updated' => 0, 'skipped' => 0];

        foreach ($records as $record) {
            $candidates = Umamusume::where('external_ref', $record['char_external_ref'])
                ->get(['id', 'is_manual']);

            /*
             * `external_ref` is indexed, not unique, so a source rename that leaves a
             * stale ref on the old row can put two trainees behind one char ref. That
             * is a stop, not a tie-break: an ambiguous ref means the source's own
             * mapping is broken, and attaching to whichever row an unordered lookup
             * returns would bury the breakage under a full run of confident-looking cards.
             */
            if ($candidates->count() > 1) {
                $counts['skipped']++;

                continue;
            }

            $trainee = $candidates->first();

            // An absent trainee means the roster has not cleared the review queue for
            // her yet; a manual one is FR-B-4 at the character grain, so nothing is
            // attached under her — not even a card she has no row for yet.
            if ($trainee === null || $trainee->is_manual) {
                $counts['skipped']++;

                continue;
            }

            $existing = $this->find($record);

            if ($existing !== null && $existing->is_manual) {
                $counts['skipped']++;

                continue;
            }

            /*
             * The columns this table owns, projected by name. A record also carries
             * `char_external_ref`, which is a lookup key and not a column: spreading
             * the record would drop it silently today and throw the day the app turns
             * on Model::preventSilentlyDiscardingAttributes().
             */
            $payload = [
                'umamusume_id' => $trainee->id,
                'title' => $record['title'],
                'rarity' => $record['rarity'],
                'global_release_date' => $record['global_release_date'],
                'is_debut_form' => $record['is_debut_form'],
                ...$this->provenance($url, $snapshotPath, $timezone),
            ];

            if ($existing === null) {
                CharacterCard::create([...$payload, 'card_id' => $record['card_id']]);
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
    private function find(array $record): ?CharacterCard
    {
        return CharacterCard::where('card_id', $record['card_id'])->first();
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
