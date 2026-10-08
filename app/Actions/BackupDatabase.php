<?php

declare(strict_types=1);

namespace App\Actions;

use PDO;
use RuntimeException;

/**
 * Produces a consistent single-file snapshot of the SQLite database (NFR-5).
 *
 * `VACUUM INTO` is the mechanism because it is the one SQLite gives for copying a database that
 * may be in use: it runs inside a read transaction of its own, so the target is a consistent
 * snapshot even while a request is writing, where a checkpoint-then-copy could read a main file
 * whose newest frames were still in the write-ahead log. The CLI command that used to checkpoint
 * and copy delegates here, so there is one backup implementation and it is the safer one.
 *
 * It opens **its own connection to the file** rather than borrowing the request's. A snapshot must
 * not run inside the caller's transaction — SQLite refuses a `VACUUM` there outright — and a backup
 * taken from the transaction a page happens to be writing would be that page's view of the data,
 * not the database's.
 *
 * The destination is never overwritten: `VACUUM INTO` refuses an existing target, and saying so
 * beats a generic SQLite error about a file the caller may not have meant to clobber.
 */
final class BackupDatabase
{
    /**
     * @param  string|null  $destination  an absolute path, or null for the default
     *                                    `storage/app/backups/uma-backup-<UTC-timestamp>.sqlite`
     * @return string the path the snapshot was written to
     *
     * @throws RuntimeException when there is no database file to back up, the destination already
     *                          exists, or the snapshot itself failed
     */
    public static function to(?string $destination = null): string
    {
        /** @var string $database */
        $database = config('database.connections.sqlite.database');

        if ($database === ':memory:' || ! file_exists($database)) {
            throw new RuntimeException('No SQLite database file to back up at the '.config('database.default').' connection path.');
        }

        $destination ??= storage_path('app/backups/uma-backup-'.now()->utc()->format('Ymd-His').'.sqlite');
        $directory = dirname($destination);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (file_exists($destination)) {
            throw new RuntimeException("A file already exists at {$destination}, and a snapshot never overwrites one.");
        }

        // The path is composed on this server, never read from the request; the one character SQL
        // cares about is still escaped rather than assumed absent.
        $escaped = str_replace("'", "''", $destination);

        (new PDO('sqlite:'.$database))->exec("VACUUM INTO '{$escaped}'");

        return $destination;
    }
}
