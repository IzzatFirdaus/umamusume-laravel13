<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ScenarioSlot;
use Illuminate\Database\Seeder;

/**
 * Seeds scenario_slots from the committed client export (R55).
 *
 * Tier-1: URA Finale goal_race rows from ura-races + race_instances + races.
 * Tier-2: rows that carry a per-row server qualifier and date (none yet).
 * Trackblazer: zero rows (D-220 disclosure at render time).
 * Unity Cup: zero rows (no citable source yet).
 *
 * Provenance: every row carries source_url, snapshot_path, fetched_at.
 * Idempotent via updateOrCreate on (scenario_key, month, half, kind, source_key).
 */
class ScenarioSlotSeeder extends Seeder
{
    private const array GRADE_MAP = [
        100 => 'G1',
        200 => 'G2',
        300 => 'G3',
        400 => 'OP',
        700 => 'Pre-OP',
    ];

    private const array HALF_MAP = [
        1 => 'Early',
        2 => 'Late',
    ];

    private const string SOURCE_URL = 'https://gametora.com/umamusume';

    private const string SNAPSHOT_PATH = 'database/seeders/data';

    private const string SOURCE_TIMEZONE = 'Asia/Tokyo';

    public function run(): void
    {
        $this->seedUraFinale();
    }

    private function seedUraFinale(): void
    {
        $uraRaces = $this->loadJson('ura-races.json');
        $instances = $this->indexById($this->loadJson('race_instances.json'));
        $races = $this->indexByIntId($this->loadJson('races.json'));

        $fetchedAt = now();
        $sortOrder = 0;

        foreach ($uraRaces as $uraRow) {
            if (($uraRow['month'] ?? 0) === 99999) {
                continue;
            }

            $instanceId = (string) ($uraRow['id'] ?? '');
            $instance = $instances[$instanceId] ?? null;
            if ($instance === null) {
                continue;
            }

            $raceId = $uraRow['instance'] ?? null;
            $race = $raceId !== null ? ($races[$raceId] ?? null) : null;
            if ($race === null) {
                continue;
            }

            if (in_array('en', $race['unreleased_servers'] ?? [], true)) {
                continue;
            }

            $half = self::HALF_MAP[$uraRow['half'] ?? 0] ?? null;
            if ($half === null) {
                continue;
            }

            $tier = self::GRADE_MAP[$race['grade'] ?? 0] ?? null;
            $title = $instance['details']['name_en']
                ?? $race['name_en']
                ?? "Race #{$instanceId}";
            $sourceKey = (string) ($uraRow['instance'] ?? $instanceId);

            $description = ! empty($instance['description'])
                ? implode(' ', $instance['description'])
                : null;

            ScenarioSlot::updateOrCreate(
                [
                    'scenario_key' => 'ura_finale',
                    'month' => $uraRow['month'],
                    'half' => $half,
                    'kind' => 'goal_race',
                    'source_key' => $sourceKey,
                ],
                [
                    'slot_label' => $tier ?? 'Race',
                    'title' => $title,
                    'description' => $description,
                    'tier' => $tier,
                    'fans_needed' => $uraRow['fans_needed'] ?? null,
                    'is_mandatory' => false,
                    'is_maiden_gated' => false,
                    'sort_order' => $sortOrder++,
                    'source_url' => self::SOURCE_URL,
                    'snapshot_path' => self::SNAPSHOT_PATH,
                    'fetched_at' => $fetchedAt,
                    'source_timezone' => self::SOURCE_TIMEZONE,
                    'is_manual' => false,
                ],
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loadJson(string $filename): array
    {
        $path = database_path('seeders/data/'.$filename);
        if (! file_exists($path)) {
            return [];
        }

        return json_decode(file_get_contents($path), true) ?? [];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, array<string, mixed>>
     */
    private function indexById(array $rows): array
    {
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[(string) ($row['id'] ?? '')] = $row;
        }

        return $indexed;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function indexByIntId(array $rows): array
    {
        $indexed = [];
        foreach ($rows as $row) {
            if (isset($row['id'])) {
                $indexed[(int) $row['id']] = $row;
            }
        }

        return $indexed;
    }
}
