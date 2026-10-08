<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Database identity guard
|--------------------------------------------------------------------------
|
| Declares the one file that is the Trainer's real data, and the rules an
| isolated workflow must satisfy before it may open a SQLite connection.
|
| The 2026-10-08 development-database incident destroyed the canonical file:
| `database/database.sqlite` was truncated to 0 bytes while its write-ahead log
| (1,849,912 bytes, 449 salt-valid frames) survived. The log is structurally
| intact, but the final committed database is 856 pages and the log holds only
| 96 of them, so 760 pages lived only in the main file and are gone. The record
| of the attempt is under `.scratch-uma/db-recovery-2026-10-08/`.
|
| Nothing about that was an accident at the database layer. Every layer had a
| permissive default:
|
|   .env names a *relative* path, so the caller's working directory decides
|   SQLiteConnector::parseDatabasePath() resolves a relative path against
|     realpath($path) first and only then base_path($path)
|   config/database.php falls back to database_path('database.sqlite')
|     when DB_DATABASE is simply absent
|
| so an omitted `DB_DATABASE=` prefix did not fail, it pointed at the real file
| and a destructive command then did what it said. `App\Services\DatabaseSafety\
| DatabaseGuard` turns those defaults into refusals. This file declares what the
| guard compares against; the guard owns the comparisons.
|
| The guard never changes a path. It only reports and refuses.
|
|--------------------------------------------------------------------------
| The canonical development database
|--------------------------------------------------------------------------
|
| The one file a Trainer's runs live in. Named here rather than re-derived at
| each call site so every check compares against a single declaration. `env()`
| wins for anyone who deliberately relocates it.
|
|--------------------------------------------------------------------------
| Isolated roles
|--------------------------------------------------------------------------
|
| A process that declares one of these in `UMA_DATABASE_ROLE` claims it is not
| the interactive application. That claim is what activates the guard: an
| isolated process must name an absolute database, must not resolve to the
| canonical file, and must match `UMA_EXPECTED_DATABASE` when one is set.
|
| A process that declares nothing is the interactive app and keeps the present
| behaviour, so a guard regression cannot strand a Trainer at their own desktop.
|
|--------------------------------------------------------------------------
| Destructive commands
|--------------------------------------------------------------------------
|
| Refused when they would open the canonical file and no explicit override is
| set. The refusal is unconditional for the list below regardless of role,
| because a destructive run is where the damage is done and the caller is
| usually a fresh shell with no role declared at all.
*/

return [
    'canonical' => env('UMA_CANONICAL_DATABASE', database_path('database.sqlite')),

    'isolated_roles' => [
        'isolated',
        'test',
        'browser',
        'scratch',
        'e2',
    ],

    'destructive_commands' => [
        'migrate:fresh',
        'migrate:refresh',
        'migrate:reset',
        'migrate:rollback',
        'db:wipe',
    ],

    /*
     * Requires both this value AND `UMA_OPERATOR_ACKNOWLEDGED=yes` AND an
     * interactive shell to accept a destructive operation against the
     * canonical development database. The second factor prevents automated
     * or LLM-driven workflows from enabling this by merely reading the error
     * message; the interactive check blocks non-TTY contexts where no human
     * can consciously confirm.
     */
    'allow_canonical_destructive' => (bool) env('UMA_ALLOW_CANONICAL_DESTRUCTIVE', false),
];
