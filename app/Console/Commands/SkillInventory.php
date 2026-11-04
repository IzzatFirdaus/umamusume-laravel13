<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\ReleaseStatus;
use App\Models\Skill;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use JsonException;

/**
 * Inventory command for skill reconciliation (Slice A, Phase 3).
 *
 * Reads the export file (from config or --file=) and emits machine-readable counts.
 * With --join, performs a bidirectional join between file and table on export_id.
 * NULL export_id rows are tracked in a separate bucket to avoid keyBy collision.
 */
final class SkillInventory extends Command
{
    protected $signature = 'uma:skill-inventory
        {--file= : Path to the skills export JSON (defaults to config uma.sources.gametora-skills.seed_file)}
        {--join : Emit bidirectional join counts between file and table}
    ';

    protected $description = 'Emit skill inventory counts from export file and/or table';

    public function handle(): int
    {
        $filePath = $this->option('file');

        if ($filePath === null) {
            $source = config('uma.sources.gametora-skills');
            $seedFile = $source['seed_file'] ?? null;

            if ($seedFile === null) {
                $this->error('No seed_file in config and no --file provided');
                return 1;
            }

            $filePath = database_path("seeders/data/{$seedFile}");
        }

        if (! file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $fileRows = $this->parseFile($filePath);

        if ($this->option('join')) {
            return $this->runJoin($fileRows);
        }

        $this->runInventory($fileRows);

        return 0;
    }

    /**
     * @return list<array{export_id: int, name: string, name_ja: string|null, name_is_client: bool,
     *                   release_status: string, rarity: int|null, is_unique: bool, sp_cost: int|null, type: string|null,
     *                   condition_groups: list<array<string, mixed>>, description: string|null, description_match_key: string|null, distance_type: int|null}>
     */
    private function parseFile(string $path): array
    {
        $body = file_get_contents($path);

        if ($body === false) {
            $this->error("Failed to read file: {$path}");
            return [];
        }

        try {
            $skills = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $this->error('Invalid JSON in export file');
            return [];
        }

        if (! is_array($skills)) {
            $this->error('Export file is not an array');
            return [];
        }

        $rows = [];

        foreach ($skills as $skill) {
            $row = $this->row($skill);

            if ($row !== null) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * @param  mixed  $skill
     * @return array{export_id: int, name: string, name_ja: string|null, name_is_client: bool,
     *                   release_status: string, rarity: int|null, is_unique: bool, sp_cost: int|null, type: string|null,
     *                   condition_groups: list<array<string, mixed>>, description: string|null, description_match_key: string|null, distance_type: int|null}|null
     */
    private function row(mixed $skill): ?array
    {
        if (! is_array($skill) || ! isset($skill['id']) || ! is_int($skill['id'])) {
            return null;
        }

        $onGlobal = ! $this->unreleasedOnGlobal($skill['unreleased'] ?? null);
        $clientName = $this->textOrNull($skill['name_en'] ?? null);
        $rendering = $this->textOrNull($skill['enname'] ?? null);

        $name = $clientName ?? $rendering;

        if ($name === null) {
            return null;
        }

        $rarity = isset($skill['rarity']) && is_int($skill['rarity']) ? $skill['rarity'] : null;
        $description = $this->textOrNull($skill['desc_en'] ?? null);
        $distanceType = $this->parseDistanceType($skill['condition_groups'] ?? null);

        return [
            'export_id' => $skill['id'],
            'name' => $name,
            'name_ja' => $this->textOrNull($skill['jpname'] ?? null),
            'name_is_client' => $clientName !== null && $onGlobal,
            'release_status' => $onGlobal
                ? ReleaseStatus::GlobalReleased->value
                : ReleaseStatus::JapanOnly->value,
            'rarity' => $rarity,
            'is_unique' => $rarity !== null && in_array($rarity, [3, 4, 5], true),
            'sp_cost' => isset($skill['cost']) && is_int($skill['cost']) ? $skill['cost'] : null,
            'type' => $this->category($skill['condition_groups'] ?? null),
            'condition_groups' => $this->conditionGroups($skill['condition_groups'] ?? null),
            'description' => $description,
            'description_match_key' => $description !== null
                ? (new \App\Services\DataPipeline\NameNormalizer)->normalize($description)
                : null,
            'distance_type' => $distanceType,
        ];
    }

    private function unreleasedOnGlobal(mixed $unreleased): bool
    {
        return is_array($unreleased) && in_array('en', $unreleased, true);
    }

    private function category(mixed $groups): ?string
    {
        if (! is_array($groups)) {
            return null;
        }

        $categories = [];

        foreach ($groups as $group) {
            if (! is_array($group) || ! is_array($group['effects'] ?? null)) {
                continue;
            }

            foreach ($group['effects'] as $effect) {
                $code = is_array($effect) ? ($effect['type'] ?? null) : null;
                $value = is_array($effect) ? ($effect['value'] ?? null) : null;

                if (! is_int($code) || ! is_int($value) || $value <= 0
                    || ! isset(self::CATEGORY_BY_EFFECT_CODE[$code])) {
                    return null;
                }

                $categories[(string) self::CATEGORY_BY_EFFECT_CODE[$code]] = true;
            }
        }

        return count($categories) === 1 ? (string) array_key_first($categories) : null;
    }

    private const CATEGORY_BY_EFFECT_CODE = [
        27 => 'Speed',
        22 => 'Speed',
        9 => 'Recovery',
        28 => 'Recovery',
        1 => 'Passive',
        2 => 'Passive',
        3 => 'Passive',
    ];

    private function conditionGroups(mixed $groups): array
    {
        if (! is_array($groups)) {
            return [];
        }

        $projected = [];

        foreach ($groups as $group) {
            if (! is_array($group)) {
                continue;
            }

            $effects = [];
            $rawEffects = $group['effects'] ?? null;

            foreach (is_array($rawEffects) ? $rawEffects : [] as $effect) {
                if (! is_array($effect)
                    || ! is_int($effect['type'] ?? null)
                    || ! is_int($effect['value'] ?? null)) {
                    continue;
                }

                $effects[] = ['type' => $effect['type'], 'value' => $effect['value']];
            }

            $projected[] = [
                'base_time' => $this->baseTime($group['base_time'] ?? null),
                'condition' => $this->textOrNull($group['condition'] ?? null),
                'precondition' => $this->textOrNull($group['precondition'] ?? null),
                'effects' => $effects,
            ];
        }

        return $projected;
    }

    private function baseTime(mixed $value): ?int
    {
        return is_int($value) && $value >= 0 ? $value : null;
    }

    private function textOrNull(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * Parse distance_type from exact equality tokens in condition groups.
     * Only `distance_type==N` (N=1..4) sets the column; range operators (<=, >=) leave it NULL.
     */
    private function parseDistanceType(mixed $groups): ?int
    {
        if (! is_array($groups)) {
            return null;
        }

        foreach ($groups as $group) {
            if (! is_array($group)) {
                continue;
            }

            foreach ([$group['condition'] ?? null, $group['precondition'] ?? null] as $predicate) {
                if (! is_string($predicate)) {
                    continue;
                }

                foreach (preg_split('/[@&]/', $predicate) ?: [] as $atom) {
                    if (preg_match('/^distance_type==([1-4])$/', trim($atom), $m) === 1) {
                        return (int) $m[1];
                    }
                }
            }
        }

        return null;
    }

    private function runInventory(array $fileRows): void
    {
        $tableTotal = Skill::count();
        $tableDescriptionNotNull = Skill::whereNotNull('description')->count();
        $tableDistanceNotNull = Skill::whereNotNull('distance_type')->count();

        $releaseStatusCounts = Skill::query()
            ->selectRaw('release_status, count(*) as c')
            ->groupBy('release_status')
            ->pluck('c', 'release_status')
            ->all();

        // file.distance_exact: rows where distance_type parses to a SINGLE integer 1-4
        // (exactly one exact token, no range operators, no multi-token)
        $exactCount = 0;
        $multiCount = 0;
        $hasAnyTokenCount = 0;

        foreach ($fileRows as $row) {
            $groups = $row['condition_groups'] ?? null;
            if (! is_array($groups)) {
                continue;
            }

            $exactTokens = 0;
            $hasRange = false;

            foreach ($groups as $group) {
                if (! is_array($group)) {
                    continue;
                }

                foreach ([$group['condition'] ?? null, $group['precondition'] ?? null] as $predicate) {
                    if (! is_string($predicate)) {
                        continue;
                    }

                    foreach (preg_split('/[@&]/', $predicate) ?: [] as $atom) {
                        $atom = trim($atom);
                        if (preg_match('/^distance_type==([1-4])$/', $atom)) {
                            $exactTokens++;
                        } elseif (preg_match('/^distance_type[<>]=?[1-4]$/', $atom)) {
                            $hasRange = true;
                        }
                    }
                }
            }

            if ($exactTokens === 1 && ! $hasRange) {
                $exactCount++;
            } elseif ($exactTokens > 1 || $hasRange) {
                $multiCount++;
            }

            if ($exactTokens > 0 || $hasRange) {
                $hasAnyTokenCount++;
            }
        }

        $this->line("file.total=" . count($fileRows));
        $this->line("file.desc_en_nonblank=" . count(array_filter($fileRows, fn ($r) => $r['description'] !== null)));
        $this->line("file.distance_exact={$exactCount}");
        $this->line("file.distance_multi={$multiCount}");
        $this->line("file.release_status.Global=" . count(array_filter($fileRows, fn ($r) => $r['release_status'] === ReleaseStatus::GlobalReleased->value)));
        $this->line("file.release_status.JapanOnly=" . count(array_filter($fileRows, fn ($r) => $r['release_status'] === ReleaseStatus::JapanOnly->value)));
        $this->line("file.release_status.Other=0");
        $this->line("table.total={$tableTotal}");
        $this->line("table.description_notnull={$tableDescriptionNotNull}");
        $this->line("table.distance_notnull={$tableDistanceNotNull}");

        foreach (ReleaseStatus::cases() as $status) {
            $count = $releaseStatusCounts[$status->value] ?? 0;
            $this->line("table.release_status.{$status->value}={$count}");
        }

        // The D6 cross-check: the footer's number is checked against this independent count, not
        // against the controller method under test. Counted here from the same `condition_groups`
        // column the surface reads, but through this command's own parser. Two lines because the
        // catalogue footer counts only rows the Global scope admits: a JapanOnly range operator is
        // in `table.range_op_count` and not in `table.range_op_count_global`, and that difference is
        // the honest reason the two numbers can disagree.
        $tableRangeOpCount = 0;
        $tableRangeOpCountGlobal = 0;

        foreach (Skill::query()->get(['condition_groups', 'release_status', 'name_is_client']) as $skill) {
            if (! $this->hasRangeOperatorDistance($skill->condition_groups)) {
                continue;
            }

            $tableRangeOpCount++;

            if ($skill->release_status === ReleaseStatus::GlobalReleased && $skill->name_is_client) {
                $tableRangeOpCountGlobal++;
            }
        }

        $this->line("table.range_op_count={$tableRangeOpCount}");
        $this->line("table.range_op_count_global={$tableRangeOpCountGlobal}");
    }

    /** Whether any condition/precondition atom in the stored JSON is a distance range operator. */
    private function hasRangeOperatorDistance(mixed $groups): bool
    {
        if (! is_array($groups)) {
            return false;
        }

        foreach ($groups as $group) {
            if (! is_array($group)) {
                continue;
            }

            foreach ([$group['condition'] ?? null, $group['precondition'] ?? null] as $predicate) {
                if (! is_string($predicate)) {
                    continue;
                }

                foreach (preg_split('/[@&]/', $predicate) ?: [] as $atom) {
                    if (preg_match('/^distance_type[<>]=?[1-4]$/', trim($atom))) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function hasMultiTokenDistance(mixed $groups): bool
    {
        if (! is_array($groups)) {
            return false;
        }

        $exactCount = 0;

        foreach ($groups as $group) {
            if (! is_array($group)) {
                continue;
            }

            foreach ([$group['condition'] ?? null, $group['precondition'] ?? null] as $predicate) {
                if (! is_string($predicate)) {
                    continue;
                }

                foreach (preg_split('/[@&]/', $predicate) ?: [] as $atom) {
                    if (preg_match('/^distance_type==([1-4])$/', trim($atom))) {
                        $exactCount++;
                    }
                }
            }
        }

        return $exactCount > 1;
    }

    private function runJoin(array $fileRows): int
    {
        $fileById = [];

        foreach ($fileRows as $row) {
            $fileById[$row['export_id']] = $row;
        }

        // Separate table rows by export_id: those with a key, and those with NULL
        $tableRowsWithKey = Skill::query()
            ->select('id', 'export_id', 'name', 'description', 'distance_type', 'release_status')
            ->whereNotNull('export_id')
            ->get()
            ->keyBy('export_id');

        $tableRowsNullKey = Skill::query()
            ->select('id', 'export_id', 'name', 'description', 'distance_type', 'release_status')
            ->whereNull('export_id')
            ->get();

        $fileOnly = [];
        $tableOnly = [];
        $tableOnlyNullKey = [];
        $matched = 0;
        $colDiffs = [];

        foreach ($fileById as $id => $fileRow) {
            if (! $tableRowsWithKey->has($id)) {
                $fileOnly[] = $fileRow;
                continue;
            }

            $matched++;
            $tableRow = $tableRowsWithKey->get($id);

            $this->compareRow($id, $fileRow, $tableRow, $colDiffs);
        }

        foreach ($tableRowsWithKey as $id => $tableRow) {
            if (! isset($fileById[$id])) {
                $tableOnly[] = ['export_id' => $id, 'name' => $tableRow->name];
            }
        }

        foreach ($tableRowsNullKey as $tableRow) {
            $tableOnlyNullKey[] = ['export_id' => null, 'name' => $tableRow->name];
        }

        $this->line("file_only=" . count($fileOnly));
        $this->line("table_only=" . count($tableOnly));
        $this->line("table_only_null_key=" . count($tableOnlyNullKey));
        $this->line("matched={$matched}");

        foreach ($fileOnly as $row) {
            $this->line("missing_in_table key={$row['export_id']} name={$row['name']}");
        }

        foreach ($tableOnly as $row) {
            $this->line("missing_in_file key={$row['export_id']} name={$row['name']}");
        }

        foreach ($tableOnlyNullKey as $row) {
            $this->line("table_only_null_key key=<null> name={$row['name']}");
        }

        foreach ($colDiffs as $diff) {
            $this->line("col_diff key={$diff['key']} col={$diff['col']} file={$diff['file']} table={$diff['table']}");
        }

        if (! empty($fileOnly)) {
            $this->warn('file_only > 0: rows present in export but absent from skills table.');
            $this->warn('Likely causes: import filter (release_status), key mismatch on export_id, or parser returned null for these rows.');
        }

        if (! empty($tableOnly) || ! empty($tableOnlyNullKey)) {
            $this->warn('table_only + table_only_null_key > 0: rows present in skills table but absent from export.');
            $this->warn('Likely causes: legacy seeded rows, rows from a prior export version, or is_manual=true rows skipped by promotion.');
        }

        $descDiffs = array_filter($colDiffs, fn ($d) => $d['col'] === 'description');

        if (! empty($descDiffs)) {
            $this->warn('desc_en diverges on ' . count($descDiffs) . ' rows. Up to 5 examples:');

            $examples = array_slice($descDiffs, 0, 5);

            foreach ($examples as $ex) {
                $filePreview = $ex['file'] ?? '(null)';
                $tablePreview = $ex['table'] ?? '(null)';
                $this->warn("  key={$ex['key']} file=\"{$filePreview}\" table=\"{$tablePreview}\"");
            }
        }

        // Sum check
        $sum = $matched + count($tableOnly) + count($tableOnlyNullKey);
        $tableTotal = Skill::count();
        $sumCheck = $sum === $tableTotal ? 'OK' : 'FAIL';
        $this->line("sum_check matched={$matched} + table_only=" . count($tableOnly) . " + table_only_null_key=" . count($tableOnlyNullKey) . " = {$sum} vs table.total={$tableTotal} — {$sumCheck}");

        if ($sumCheck === 'FAIL') {
            return 2;
        }

        return (count($fileOnly) + count($tableOnly) + count($tableOnlyNullKey) > 0) ? 1 : 0;
    }

    /**
     * @param  array{export_id: int, name: string, name_ja: string|null, name_is_client: bool,
     *                   release_status: string, rarity: int|null, is_unique: bool, sp_cost: int|null, type: string|null,
     *                   condition_groups: list<array<string, mixed>>, description: string|null, description_match_key: string|null, distance_type: int|null}  $fileRow
     * @param  \App\Models\Skill  $tableRow
     * @param  list<array{key: int, col: string, file: string, table: string}>  $colDiffs
     */
    private function compareRow(int $id, array $fileRow, \App\Models\Skill $tableRow, array &$colDiffs): void
    {
        $fileDesc = $fileRow['description'] ?? null;
        $tableDesc = $tableRow->description;

        if ($fileDesc !== $tableDesc) {
            $colDiffs[] = [
                'key' => $id,
                'col' => 'description',
                'file' => $fileDesc ?? '(null)',
                'table' => $tableDesc ?? '(null)',
            ];
        }

        $fileDist = $fileRow['distance_type'] ?? null;
        $tableDist = $tableRow->distance_type;

        if ($fileDist !== $tableDist) {
            $colDiffs[] = [
                'key' => $id,
                'col' => 'distance_type',
                'file' => $fileDist !== null ? (string) $fileDist : '(null)',
                'table' => $tableDist !== null ? (string) $tableDist : '(null)',
            ];
        }

        $fileStatus = $fileRow['release_status'];
        $tableStatus = $tableRow->release_status?->value;

        if ($fileStatus !== $tableStatus) {
            $colDiffs[] = [
                'key' => $id,
                'col' => 'release_status',
                'file' => $fileStatus,
                'table' => $tableStatus ?? '(null)',
            ];
        }
    }
}