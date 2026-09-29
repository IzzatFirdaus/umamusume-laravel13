<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Umamusume;
use App\Models\UmamusumeProfile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Persist parsed profile rows with their provenance attached.
 *
 * ADR-0003 Amendment R3 requires `source_url`, `snapshot_path`, `fetched_at` and `source_timezone`
 * on a reference row. The parser cannot supply those — it sees only the document body — so they are
 * stamped here, at the point where the fetch actually happened. This is the same split
 * `StoreCharacterCards` and `StoreSkills` use.
 *
 * **The join is `umamusume.external_ref`, never a name.** A profile row has a Japanese name, a
 * romanised one and two voice actors, and the document holds 163 rows against 135 trainees, so a
 * name match would be guessing across 28 rows this catalog does not track. The ref is exact, and
 * measured on 2026-09-30 it is unambiguous: 135 refs, 0 duplicates.
 *
 * **`is_manual` is read at both grains.** A trainee the Trainer wrote by hand receives no profile
 * at all, and a profile she corrected by hand is skipped while the other 134 update. This is
 * `StoreCharacterCards`' rule and PRD FR-B-4's: a fetch does not get to overwrite a person.
 *
 * **Source-owned columns are written wholesale, nulls included.** A null from this document is a
 * statement about the trainee, and merging around it would hide a source that stopped asserting
 * something. That is the opposite trade from `PromoteMatchedRecord`, which deliberately keeps
 * absent aptitude letters from blanking stored ones because a *second* publisher may have written
 * them; here one document owns the row.
 *
 * All rows land in one transaction. NFR-4 asks for that on a multi-write operation, and a
 * half-imported profile column would render a page that claims a height for some trainees and not
 * others for no visible reason.
 */
final class StoreCharacterProfiles
{
    /**
     * @param  list<array<string, mixed>>  $records
     * @return array{created: int, updated: int, skipped: int}
     */
    public function handle(array $records, string $url, ?string $snapshotPath, ?string $timezone): array
    {
        return DB::transaction(function () use ($records, $url, $snapshotPath, $timezone): array {
            $counts = ['created' => 0, 'updated' => 0, 'skipped' => 0];

            foreach ($records as $record) {
                $candidates = Umamusume::where('external_ref', $record['char_external_ref'])
                    ->get(['id', 'is_manual']);

                /*
                 * `external_ref` is indexed but not unique, so a source rename that leaves a
                 * stale ref behind can point two trainees at one char ref. That is a stop, not a
                 * tie-break, and `StoreCharacterCards` reaches the same conclusion for the same
                 * reason: an ambiguous ref means the source's own mapping is broken, and
                 * attaching to whichever row an unordered lookup returned would bury the
                 * breakage under a full run of confident-looking profiles.
                 */
                if ($candidates->count() > 1) {
                    $counts['skipped']++;

                    continue;
                }

                $trainee = $candidates->first();

                // An absent trainee is not an error: the document is wider than this catalog
                // (163 rows, 135 trainees), so 28 of its rows name characters no roster row
                // claims. A manual trainee is FR-B-4 at the character grain.
                if ($trainee === null || $trainee->is_manual) {
                    $counts['skipped']++;

                    continue;
                }

                $existing = UmamusumeProfile::where('umamusume_id', $trainee->id)->first();

                if ($existing !== null && $existing->is_manual) {
                    $counts['skipped']++;

                    continue;
                }

                /*
                 * Projected by name, for `StoreCharacterCards`' reason: a record also carries
                 * `char_external_ref`, which is a lookup key and not a column, and spreading the
                 * record would drop it silently today and throw the day the app turns on
                 * `Model::preventSilentlyDiscardingAttributes()`.
                 */
                $payload = [
                    'umamusume_id' => $trainee->id,
                    'name_ja' => $record['name_ja'],
                    'va_ja' => $record['va_ja'],
                    'va_en' => $record['va_en'],
                    'birth_year' => $record['birth_year'],
                    'birth_month' => $record['birth_month'],
                    'birth_day' => $record['birth_day'],
                    'height' => $record['height'],
                    'three_sizes_b' => $record['three_sizes_b'],
                    'three_sizes_h' => $record['three_sizes_h'],
                    'three_sizes_w' => $record['three_sizes_w'],
                    ...$this->provenance($url, $snapshotPath, $timezone),
                ];

                if ($existing === null) {
                    UmamusumeProfile::create($payload);
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
