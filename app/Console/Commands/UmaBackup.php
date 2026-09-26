<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Produces a consistent single-file copy of the SQLite database (NFR-5):
 * wal_checkpoint(TRUNCATE) folds the write-ahead log into the main file first,
 * then a plain copy is safe to restore by file replacement.
 */
class UmaBackup extends Command
{
    protected $signature = 'uma:backup {destination? : Target file path; defaults to storage/app/backups/uma-backup-<timestamp>.sqlite}';

    protected $description = 'Checkpoint the WAL and copy the SQLite database to a single backup file (NFR-5)';

    public function handle(): int
    {
        /** @var string $database */
        $database = config('database.connections.sqlite.database');

        if ($database === ':memory:' || ! file_exists($database)) {
            $this->error('SQLite database file not found at '.config('database.default').' connection path; nothing to back up.');

            return self::FAILURE;
        }

        DB::statement('PRAGMA wal_checkpoint(TRUNCATE);');

        $destination = $this->argument('destination')
            ?? storage_path('app/backups/uma-backup-'.now()->format('Ymd-His').'.sqlite');

        $destinationDir = dirname((string) $destination);

        if (! is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        if (! copy($database, (string) $destination)) {
            $this->error("Copy failed to {$destination}");

            return self::FAILURE;
        }

        $this->info("Backup written: {$destination}");

        return self::SUCCESS;
    }
}
