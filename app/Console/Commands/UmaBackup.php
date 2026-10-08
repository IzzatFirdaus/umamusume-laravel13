<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\BackupDatabase;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * Produces a consistent single-file snapshot of the SQLite database (NFR-5).
 *
 * The copy lives in `App\Actions\BackupDatabase` so the Settings screen's Backup action and this
 * command cannot drift: one implementation, and the mechanism (`VACUUM INTO`) is the one that
 * holds a consistent snapshot of a live WAL database.
 */
class UmaBackup extends Command
{
    protected $signature = 'uma:backup {destination? : Target file path; defaults to storage/app/backups/uma-backup-<timestamp>.sqlite}';

    protected $description = 'Copy the SQLite database to a single consistent backup file (NFR-5)';

    public function handle(): int
    {
        try {
            $destination = BackupDatabase::to($this->argument('destination'));
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Backup written: {$destination}");

        return self::SUCCESS;
    }
}
