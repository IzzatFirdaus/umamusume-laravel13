<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ScenarioSlot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeds scenario_slots from the committed client export (R55).
 *
 * Tier-1: URA Finale goal_race rows from ura-races + race_instances + races.
 * Tier-2: rows that carry a per-row server qualifier and date (none yet).
 * Trackblazer: zero rows (D-220 disclosure at render time).
 * Unity Cup: zero rows (no citable source yet).
 *
 * Provenance: every row carries source_url, snapshot_path, fetched_at.
 * Tier: read per race from the dated two-publisher extraction, never derived from the export's
 * numeric grade code (R72). Idempotent via updateOrCreate on (scenario_key, month, half, kind, source_key).
 */
class ScenarioSlotSeeder extends Seeder
{
    /**
     * R72: a tier is a per-race claim, so it is read per race from a dated two-publisher
     * extraction rather than derived here from the export's numeric grade code. The file records
     * for each row what uma.guide and Game8 said, each page's own date, and whether the label is
     * per-row evidence or the disclosed code-level pin that Open still rests on.
     *
     * There is deliberately no grade-code to label constant in this class. That is what G-16c
     * guards, and `TierLabelJoinTest` fails the file if one reappears.
     */
    private const string TIER_LABELS_FILE = 'race-tier-labels-2026-09-29.json';

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

    /**
     * @return array<string, array<string, mixed>> keyed by normalized race name
     */
    private function tierLabels(): array
    {
        $doc = $this->loadJson(self::TIER_LABELS_FILE);
        $map = [];

        foreach ($doc['rows'] ?? [] as $row) {
            $map[$this->normalizeName((string) ($row['name'] ?? ''))] = $row;
        }

        return $map;
    }

    private function normalizeName(string $name): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $name) ?? $name));
    }

    /**
     * Where a label came from, stated per row rather than assumed from the file's existence.
     *
     * An unlabelled row is still an export row, so the default is the export: the absence of a
     * tier says nothing was sourced about the tier, not that the row has no provenance.
     * `ScenarioSlotSeederTest`'s completeness check is what enforces that distinction.
     *
     * @param  array<string, mixed>  $row
     */
    private function tierSourceUrl(array $row): string
    {
        return match ($row['scope'] ?? null) {
            'two-publishers' => 'https://game8.co/games/Umamusume-Pretty-Derby/archives/536131'
                .' + https://uma.guide/agenda-planner/',
            default => self::SOURCE_URL,
        };
    }

    private function seedUraFinale(): void
    {
        $uraRaces = $this->loadJson('ura-races.json');
        $instances = $this->indexById($this->loadJson('race_instances.json'));
        $races = $this->indexByIntId($this->loadJson('races.json'));
        $labels = $this->tierLabels();

        $labelDoc = $this->loadJson(self::TIER_LABELS_FILE);
        $fetchedAt = now();
        $evidenceFetchedAt = isset($labelDoc['fetched_at'])
            ? Carbon::parse((string) $labelDoc['fetched_at'])
            : $fetchedAt;
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

            $title = $instance['details']['name_en']
                ?? $race['name_en']
                ?? "Race #{$instanceId}";
            $sourceKey = (string) ($uraRow['instance'] ?? $instanceId);

            // Joined on the race's own name, which is the identity both publishers publish under.
            // The export's numeric grade is never consulted for a label.
            $labelRow = $labels[$this->normalizeName($title)] ?? null;
            $tier = $labelRow['tier'] ?? null;
            $tierSourceUrl = $labelRow === null ? self::SOURCE_URL : $this->tierSourceUrl($labelRow);
            $tierFetchedAt = $tier === null ? $fetchedAt : $evidenceFetchedAt;

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
                    'source_url' => $tierSourceUrl,
                    'snapshot_path' => self::SNAPSHOT_PATH.'/'.($tier === null ? 'races.json' : self::TIER_LABELS_FILE),
                    'fetched_at' => $tierFetchedAt,
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
