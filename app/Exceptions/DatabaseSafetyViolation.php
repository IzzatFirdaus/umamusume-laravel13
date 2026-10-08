<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * A database safety guard refused an operation before it reached the database.
 *
 * Thrown only by `App\Services\DatabaseSafety\DatabaseGuard`. It is a
 * misconfiguration of the process that invoked it, never a defect in this
 * application, and the message is written to be read by the person or agent who
 * ran the command: it names the database that would have been written, and the
 * one environment variable that would have fixed it.
 *
 * It extends `RuntimeException` rather than an application exception because it
 * is a `php artisan`-level refusal, not an HTTP response. `UmaBackup` already
 * catches `RuntimeException` for its own file-level failures, so a guard thrown
 * before that check is not swallowed by a handler written for a different cause.
 */
class DatabaseSafetyViolation extends RuntimeException {}
