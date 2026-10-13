<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\RaceCatalogSlot;
use App\Models\TraineeGoal;
use App\Models\Umamusume;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Persist a trainee's parsed objective ladder with its provenance attached (PRD US-10, ADR-0026).
 *
 * `ADR-0003` Amendment R3 requires the four provenance columns on the reference row itself, and the
 * parser cannot supply them — it sees only a body — so they are stamped here, where the fetch
 * actually happened.
 *
 * Two resolutions happen here rather than in the parser, because both need the database and the parse
 * must stay testable without one: the source's `char_id` becomes an `umamusume_id` through
 * `umamusume.external_ref` (the same route `StoreCharacterCards` takes, so a card ref and a trainee ref
 * cannot drift apart), and a race becomes a `race_catalog_slot_id` through the calendar grain.
 *
 * A row a Trainer flagged `is_manual` is never touched: the engine's stop sign (PRD FR-B-4).
 */
final class StoreTraineeGoals
{
    /**
     * @param  list<array<string, mixed>>  $records
     * @return array{created: int, updated: int, skipped: int}
     */
    public function handle(array $records, string $url, ?string $snapshotPath, ?string $timezone): array
    {
        return DB::transaction(function () use ($records, $url, $snapshotPath, $timezone): array {
            $counts = ['created' => 0, 'updated' => 0, 'skipped' => 0];
            $calendar = $this->calendarGrain();
            $trainees = $this->traineeIds();
            $unknownChars = 0;

            foreach ($records as $record) {
                $umamusumeId = $trainees[$record['char_ref']] ?? null;

                if ($umamusumeId === null) {
                    // The character has no roster row: on today's document that is a Japan-only unit,
                    // and the rule is FR-A-6's — a trainee Global does not carry is not stored, and the
                    // count is reported rather than left as silence.
                    $unknownChars++;

                    continue;
                }

                $existing = $this->find($record, $umamusumeId);

                if ($existing !== null && $existing->is_manual) {
                    $counts['skipped']++;

                    continue;
                }

                $payload = [
                    'umamusume_id' => $umamusumeId,
                    'race_catalog_slot_id' => $this->slotId($record, $calendar),
                    'goal_type' => $record['goal_type'],
                    'required_placement' => $record['required_placement'],
                    'fan_threshold' => $record['fan_threshold'],
                    'career_year' => $record['career_year'],
                    'turn' => $record['turn'],
                    'source_order' => $record['source_order'],
                    'source_race_choice' => $record['source_race_choice'],
                    'source_cond_id' => $record['source_cond_id'],
                    'source_cond_type' => $record['source_cond_type'],
                    'source_cond_value' => $record['source_cond_value'],
                    'source_cond_value_2' => $record['source_cond_value_2'],
                    'source_target_type' => $record['source_target_type'],
                    'source_race_type' => $record['source_race_type'],
                    'source_race_name' => $record['source_race_name'],
                    'global_unreleased' => $record['global_unreleased'],
                    'source_url' => $url,
                    'snapshot_path' => $snapshotPath,
                    'fetched_at' => now(),
                    'source_timezone' => $timezone,
                ];

                if ($existing === null) {
                    TraineeGoal::create($payload);
                    $counts['created']++;

                    continue;
                }

                $existing->update($payload);
                $counts['updated']++;
            }

            if ($unknownChars > 0) {
                Log::warning("trainee_goals: {$unknownChars} objectives skipped for characters with no roster row");
            }

            return $counts;
        });
    }

    /**
     * The calendar keyed on (year, month, half, title), built once per batch.
     *
     * Scenario is deliberately not part of the key: 406 of the 410 catalogue rows carry a null
     * `scenario_key` because the race is shared by every scenario, and the four that are scenario-scoped
     * are the finals, which name no race here. Reading the shared grain is what let the join be measured
     * at 1,219 of 1,222 objectives.
     *
     * @return array<string, int>
     */
    private function calendarGrain(): array
    {
        $map = [];

        foreach (RaceCatalogSlot::query()->get(['id', 'year', 'month', 'half', 'title']) as $slot) {
            $key = $this->grainKey((int) $slot->year, $slot->month === null ? null : (int) $slot->month, $slot->half, $slot->title);
            $map[$key] ??= $slot->id;
        }

        return $map;
    }

    /**
     * @param  array<string, mixed>  $record
     * @param  array<string, int>  $calendar
     */
    private function slotId(array $record, array $calendar): ?int
    {
        $slot = $record['race_slot'] ?? null;

        if (! is_array($slot)) {
            return null;
        }

        return $calendar[$this->grainKey((int) $slot['year'], (int) $slot['month'], (string) $slot['half'], (string) $slot['title'])] ?? null;
    }

    /**
     * Case- and whitespace-insensitive, because the two documents capitalise the same race differently
     * and a title join that is exact on case silently drops rows.
     */
    private function grainKey(int $year, ?int $month, ?string $half, ?string $title): string
    {
        return $year.'|'.($month ?? 0).'|'.strtolower(trim((string) $half)).'|'.strtolower(trim((string) $title));
    }

    /**
     * `external_ref` to primary key, for the trainees this tool actually holds.
     *
     * @return array<string, int>
     */
    private function traineeIds(): array
    {
        $map = [];

        foreach (Umamusume::query()->get(['id', 'external_ref']) as $trainee) {
            if ($trainee->external_ref !== null && $trainee->external_ref !== '') {
                $map[$trainee->external_ref] = $trainee->id;
            }
        }

        return $map;
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function find(array $record, int $umamusumeId): ?TraineeGoal
    {
        return TraineeGoal::query()
            ->where('umamusume_id', $umamusumeId)
            ->where('source_cond_id', $record['source_cond_id'])
            ->where('turn', $record['turn'])
            ->where('source_race_choice', $record['source_race_choice'])
            ->first();
    }
}
