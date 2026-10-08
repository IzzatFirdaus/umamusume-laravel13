<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class MigrateToMariaDbAction
{
    private const TRAINER_DESK_TABLES = [
        'umamusume',
        'umamusume_aliases',
        'umamusume_profiles',
        'character_cards',
        'skills',
        'support_cards',
        'support_effects',
        'race_entries',
        'race_catalog_slots',
        'scenario_slots',
        'scenario_races',
        'scenarios',
        'training_runs',
        'turn_entries',
        'turn_events',
        'deck_slots',
        'run_skills',
        'data_sources',
        'match_candidates',
        'preferences',
        'veterans',
    ];

    /**
     * @return array<string, array{status: string, rows?: int, reason?: string}>
     */
    public function handle(): array
    {
        $this->verifySource();
        $this->verifyTarget();

        $results = [];
        $fkDisabled = false;

        try {
            foreach (self::TRAINER_DESK_TABLES as $table) {
                if (! Schema::hasTable($table)) {
                    $results[$table] = ['status' => 'skipped', 'reason' => 'table not found'];

                    continue;
                }

                $count = $this->migrateTable($table, $fkDisabled);
                $fkDisabled = true;
                $results[$table] = ['status' => 'success', 'rows' => $count];
            }

            return $results;
        } finally {
            if ($fkDisabled) {
                $this->enableForeignKeyChecks('mysql_backup');
            }
        }
    }

    private function verifySource(): void
    {
        $default = config('database.default');
        $driver = config('database.connections.sqlite.driver');

        if ($driver !== 'sqlite') {
            throw new \RuntimeException(
                "Source connection must be SQLite, but default is '{$default}' with driver '{$driver}'"
            );
        }

        $database = config('database.connections.sqlite.database');

        if ($database === ':memory:' || ! file_exists($database)) {
            throw new \RuntimeException("SQLite database file not found at: {$database}");
        }
    }

    private function verifyTarget(): void
    {
        $targetConfig = config('database.connections.mysql_backup');

        if (! is_array($targetConfig)) {
            throw new \RuntimeException(
                'Backup database connection (mysql_backup) is not configured. '.
                'Set TRAINER_DESK_MYSQL_HOST, TRAINER_DESK_MYSQL_PORT, TRAINER_DESK_MYSQL_DB, '.
                'TRAINER_DESK_MYSQL_USER, and TRAINER_DESK_MYSQL_PASSWORD environment variables.'
            );
        }

        $expectedDb = 'trainer_desk_backup';
        $configDb = $targetConfig['database'] ?? null;

        if ($configDb !== $expectedDb) {
            throw new \RuntimeException(
                "Backup database must be '{$expectedDb}', but mysql_backup.database is '{$configDb}'"
            );
        }

        $driver = $targetConfig['driver'] ?? null;

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            throw new \RuntimeException(
                "Target driver must be mysql or mariadb, but mysql_backup.driver is '{$driver}'"
            );
        }

        if (! isset($targetConfig['host']) || $targetConfig['host'] === '') {
            throw new \RuntimeException('Backup database host is not configured (TRAINER_DESK_MYSQL_HOST)');
        }

        if (! isset($targetConfig['username']) || $targetConfig['username'] === '') {
            throw new \RuntimeException('Backup database username is not configured (TRAINER_DESK_MYSQL_USER)');
        }

        try {
            $pdo = DB::connection('mysql_backup')->getPdo();
            $actualDb = $pdo->query('SELECT DATABASE()')->fetchColumn();

            if ($actualDb !== $expectedDb) {
                throw new \RuntimeException(
                    "Connected to database '{$actualDb}', expected '{$expectedDb}' - ".
                    'check your TRAINER_DESK_MYSQL_DB environment variable'
                );
            }

            $pdoStatement = $pdo->query('SELECT VERSION()');
            $version = $pdoStatement->fetchColumn();

            if (! str_contains(strtolower($version), 'mariadb') && ! str_contains(strtolower($version), 'mysql')) {
                throw new \RuntimeException(
                    "Target does not appear to be MySQL/MariaDB: {$version}"
                );
            }
        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \RuntimeException('Cannot connect to backup database: '.$e->getMessage());
        }
    }

    private function migrateTable(string $table, bool $fkDisabled): int
    {
        $sourceCount = DB::connection('sqlite')->table($table)->count();

        if ($sourceCount === 0) {
            return 0;
        }

        if (in_array($table, ['support_cards', 'deck_slots'], true)) {
            if (! $fkDisabled) {
                $this->disableForeignKeyChecks('mysql_backup');
            }

            try {
                $this->truncateAndInsert($table);
            } finally {
                if (! $fkDisabled) {
                    $this->enableForeignKeyChecks('mysql_backup');
                }
            }

            return $sourceCount;
        }

        if (! $fkDisabled) {
            $this->disableForeignKeyChecks('mysql_backup');
        }

        try {
            DB::connection('mysql_backup')->table($table)->truncate();

            $this->bulkInsert($table);
        } finally {
            if (! $fkDisabled) {
                $this->enableForeignKeyChecks('mysql_backup');
            }
        }

        return $sourceCount;
    }

    private function truncateAndInsert(string $table): void
    {
        $records = DB::connection('sqlite')->table($table)->get();

        if ($records->isEmpty()) {
            return;
        }

        $rows = [];
        $columns = null;

        foreach ($records as $record) {
            if ($columns === null) {
                $columns = array_keys((array) $record);
            }

            $row = [];
            foreach ($columns as $col) {
                $row[$col] = $record->{$col} ?? null;
            }
            $rows[] = $row;
        }

        DB::connection('mysql_backup')->table($table)->insert($rows);
    }

    private function bulkInsert(string $table): void
    {
        $records = DB::connection('sqlite')->table($table)->get();

        if ($records->isEmpty()) {
            return;
        }

        $batchSize = 1000;

        foreach ($records->chunk($batchSize) as $batch) {
            $rows = [];
            $columns = null;

            foreach ($batch as $record) {
                if ($columns === null) {
                    $columns = array_keys((array) $record);
                }

                $row = [];
                foreach ($columns as $col) {
                    $row[$col] = $record->{$col} ?? null;
                }
                $rows[] = $row;
            }

            DB::connection('mysql_backup')->table($table)->insert($rows);
        }
    }

    private function disableForeignKeyChecks(string $connection): void
    {
        DB::connection($connection)->statement('SET FOREIGN_KEY_CHECKS=0');
    }

    private function enableForeignKeyChecks(string $connection): void
    {
        DB::connection($connection)->statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * @return array<string, array{source: int, target: int, match: bool}>
     */
    public static function verifyIntegrity(): array
    {
        $results = [];

        foreach (self::TRAINER_DESK_TABLES as $table) {
            $sourceCount = DB::connection('sqlite')->table($table)->count();
            $targetCount = DB::connection('mysql_backup')->table($table)->count();

            $results[$table] = [
                'source' => $sourceCount,
                'target' => $targetCount,
                'match' => $sourceCount === $targetCount,
            ];
        }

        return $results;
    }
}
