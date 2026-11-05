<?php

declare(strict_types=1);

/**
 * Reconcile the GameTora skill export against the Trainer Desk `skills` table.
 *
 * Usage:
 *   php tools/skills-inventory.php [options]
 *
 * Options (all optional):
 *   --file=<path>   export JSON file (default: database/seeders/data/skills.609afe88.json)
 *   --db=<path>     SQLite database (default: database/database.sqlite if it exists)
 *   --limit=<n>     how many extra rows to print (default: 20)
 *
 * The file side mirrors `GametoraSkillsParser`: an exact token is the atom
 * `distance_type==N` (N in 1..4) after splitting the condition/precondition on
 * `[@&]`; a range token carries `<=` or `>=` against `distance_type`. Range
 * operators leave `distance_type` NULL in the parser, so they are counted here
 * as the parser counts them (zero on the current export).
 */
$file = 'database/seeders/data/skills.609afe88.json';
$dbPath = 'database/database.sqlite';
$limit = 20;

foreach (array_slice($argv, 1) as $arg) {
    if (str_starts_with($arg, '--file=')) {
        $file = substr($arg, 7);
    } elseif (str_starts_with($arg, '--db=')) {
        $dbPath = substr($arg, 5);
    } elseif (str_starts_with($arg, '--limit=')) {
        $limit = max(1, (int) substr($arg, 8));
    } elseif ($arg === '--no-db') {
        $dbPath = '';
    }
}

if (! is_file($file)) {
    fwrite(STDERR, "export file not found: {$file}\n");
    exit(2);
}

$decoded = json_decode((string) file_get_contents($file), true);

if (! is_array($decoded)) {
    fwrite(STDERR, "export file did not decode to an array\n");
    exit(2);
}

/** @var array<int, array<string, mixed>> $skills */
$skills = $decoded;

// --- File side ---------------------------------------------------------------

$total = count($skills);
$exportKeys = [];

foreach ($skills as $row) {
    if (is_array($row) && isset($row['id']) && is_int($row['id'])) {
        $exportKeys[] = $row['id'];
    }
}

$uniqueKeys = count(array_unique($exportKeys));

$descNonNull = 0;
$descNonBlank = 0;
$rowsWithExact = 0;
$exactOccurrences = 0;
$rowsWithTwoOrMore = 0;
$rangeOccurrences = 0;
$rowsWithRange = 0;
$rangeOpTokens = [];

foreach ($skills as $row) {
    if (! is_array($row)) {
        continue;
    }

    if (array_key_exists('desc_en', $row) && $row['desc_en'] !== null) {
        $descNonNull++;
    }

    if (isset($row['desc_en']) && is_string($row['desc_en']) && trim($row['desc_en']) !== '') {
        $descNonBlank++;
    }

    $exactHere = 0;

    foreach (['condition_groups'] as $placeholder) {
        // condition_groups is the carrier in the parser's input too, but the export stores
        // its predicates under `condition`/`precondition` inside each group.
    }

    $groups = $row['condition_groups'] ?? [];

    if (! is_array($groups)) {
        $groups = [];
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
                $atom = trim($atom);

                if (preg_match('/^distance_type==([1-4])$/', $atom) === 1) {
                    $exactOccurrences++;
                    $exactHere++;
                }

                if (preg_match('/distance_type\s*(<=|>=)/', $atom) === 1) {
                    $rangeOccurrences++;
                    $rangeOpTokens[] = $atom;
                }
            }
        }
    }

    if ($exactHere >= 1) {
        $rowsWithExact++;
    }

    if ($exactHere >= 2) {
        $rowsWithTwoOrMore++;
    }

    // row-level range flag: any range token anywhere in the row
    $rowHasRange = false;

    foreach ($groups as $group) {
        if (! is_array($group)) {
            continue;
        }

        foreach ([$group['condition'] ?? null, $group['precondition'] ?? null] as $predicate) {
            if (! is_string($predicate)) {
                continue;
            }

            foreach (preg_split('/[@&]/', $predicate) ?: [] as $atom) {
                if (preg_match('/distance_type\s*(<=|>=)/', trim($atom)) === 1) {
                    $rowHasRange = true;
                }
            }
        }
    }

    if ($rowHasRange) {
        $rowsWithRange++;
    }
}

// --- Table side --------------------------------------------------------------

$dbAvailable = $dbPath !== '' && is_file($dbPath);
$tableRows = 0;
$tableExportKeys = [];
$globalCount = 0;
$japanOnlyCount = 0;
$otherRelease = [];
$descTable = 0;
$descTableNonBlank = 0;
$distanceTable = 0;
$missingFromFile = [];   // table rows whose export_id is not in the file
$fileMissingFromTable = []; // file ids not in the table
$perRelease = [];

if ($dbAvailable) {
    $pdo = new PDO('sqlite:'.$dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    foreach ($pdo->query('SELECT id, name, export_id, release_status, description, distance_type FROM skills') as $row) {
        $tableRows++;
        $perRelease[$row['release_status']] = ($perRelease[$row['release_status']] ?? 0) + 1;

        if ($row['release_status'] === 'GlobalReleased') {
            $globalCount++;
        } elseif ($row['release_status'] === 'JapanOnly') {
            $japanOnlyCount++;
        } else {
            $otherRelease[] = $row['release_status'];
        }

        if ($row['description'] !== null) {
            $descTable++;
        }

        if (is_string($row['description']) && trim($row['description']) !== '') {
            $descTableNonBlank++;
        }

        if ($row['distance_type'] !== null) {
            $distanceTable++;
        }

        if ($row['export_id'] !== null) {
            $tableExportKeys[] = (int) $row['export_id'];
        }
    }

    $fileKeySet = array_flip($exportKeys);
    $tableKeySet = array_flip($tableExportKeys);

    foreach ($pdo->query('SELECT id, name, export_id, release_status FROM skills') as $row) {
        if ($row['export_id'] === null || ! isset($fileKeySet[(int) $row['export_id']])) {
            $missingFromFile[] = [
                'id' => (int) $row['id'],
                'name' => (string) $row['name'],
                'export_id' => $row['export_id'] === null ? null : (int) $row['export_id'],
                'release_status' => (string) $row['release_status'],
            ];
        }
    }

    foreach ($exportKeys as $key) {
        if (! isset($tableKeySet[$key])) {
            $fileMissingFromTable[] = $key;
        }
    }
}

// --- Output ------------------------------------------------------------------

$line = str_repeat('=', 78);

echo "skills-inventory reconciliation\n";
echo "{$line}\n";
echo "File: {$file}\n";
echo "  rows (total)                              : {$total}\n";
echo "  unique export keys (id)                   : {$uniqueKeys}\n";
echo "  desc_en non-null                          : {$descNonNull}\n";
echo "  desc_en non-blank (trimmed)               : {$descNonBlank}\n";
echo "  rows with >=1 exact distance_type==N      : {$rowsWithExact}\n";
echo "  exact-token occurrences                   : {$exactOccurrences}\n";
echo "  rows with >=2 exact tokens                : {$rowsWithTwoOrMore}\n";
echo "  range-form (<= or >=) occurrences         : {$rangeOccurrences}\n";
echo "  rows carrying a range token               : {$rowsWithRange}\n";

if ($dbAvailable) {
    echo "\nDatabase: {$dbPath}\n";
    echo "  skills rows (total)                       : {$tableRows}\n";

    foreach ($perRelease as $status => $count) {
        echo "  release_status = {$status}                : {$count}\n";
    }

    echo "  name_is_client Global (release+client)    : {$globalCount} (same as GlobalReleased here)\n";
    echo "  description non-null (table)              : {$descTable}\n";
    echo "  description non-blank (table)             : {$descTableNonBlank}\n";
    echo "  distance_type set (table)                 : {$distanceTable}\n";
    echo '  table rows not in file                    : '.count($missingFromFile)."\n";
    echo '  file rows not in table                    : '.count($fileMissingFromTable)."\n";

    if ($missingFromFile !== []) {
        echo "\n  Table rows not in file (first {$limit}):\n";

        foreach (array_slice($missingFromFile, 0, $limit) as $row) {
            echo sprintf(
                "    id=%s name=%s export_id=%s release=%s\n",
                $row['id'],
                $row['name'],
                $row['export_id'] === null ? 'null' : $row['export_id'],
                $row['release_status'],
            );
        }

        if (count($missingFromFile) > $limit) {
            echo '    ... '.(count($missingFromFile) - $limit)." more\n";
        }
    }

    if ($fileMissingFromTable !== []) {
        echo "\n  File rows not in table (first {$limit}): ".implode(', ', array_slice($fileMissingFromTable, 0, $limit))."\n";
    }
} else {
    echo "\nDatabase: (not found or --no-db) — join skipped\n";
}

echo "{$line}\n";

// Machine-readable tail, one line, for commit-message citation.
echo "SUMMARY rows={$total} unique_keys={$uniqueKeys} desc_nonnull={$descNonNull} desc_nonblank={$descNonBlank} "
    ."exact_rows={$rowsWithExact} exact_occurrences={$exactOccurrences} multi_token_rows={$rowsWithTwoOrMore} "
    ."range_occurrences={$rangeOccurrences} range_rows={$rowsWithRange} "
    ."table_rows={$tableRows} table_not_in_file=".count($missingFromFile)
    .' file_not_in_table='.count($fileMissingFromTable)."\n";
