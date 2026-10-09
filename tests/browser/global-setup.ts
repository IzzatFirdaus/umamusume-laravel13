import { execSync } from 'node:child_process';
import { rmSync, writeFileSync } from 'node:fs';
import { dirname, isAbsolute, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * The browser suite's data contract (KI-69).
 *
 * Five cases assert empty-state surfaces — `runs.spec.ts:41` and `career-legacy-deck-steps.spec.ts:268`
 * expect `No runs yet`, and three dashboard cases expect the no-active-career state — while other cases
 * need the seeded reference data the wizard's roster reads. Both halves point at one thing: the suite owns
 * a scratch database that it builds from nothing. Until now nothing did that. `playwright.config.ts` had
 * the server take its database from `.env`, which is the shared dev file, so `npm run test:browser`
 * followed the config and read whatever rows other sessions and earlier specs had left behind — the class
 * KI-69 records, and the reason a green suite proved nothing about the empty-state surfaces.
 *
 * Deleting a file is not weakening an assertion: the tests still require an empty runs table, and now
 * that requirement is established rather than assumed. Nothing here writes to `database/database.sqlite`;
 * the dev file is a different path and is never named.
 *
 * The path is absolute and reaches the server as `DB_DATABASE`. That holds while `bootstrap/cache/config.php`
 * is absent, which is the state on this tree; a cached config would win over the environment and silently
 * point the suite back at the dev file.
 *
 * Guardrail additions (2026-10-08 incident): the harness must prove the application will open the scratch
 * database, not the canonical one. This setup runs the `uma:db-guard` command with the same environment the
 * server receives and asserts the identity is safe and the expected database matches the scratch file.
 */
const repoRoot = join(dirname(fileURLToPath(import.meta.url)), '..', '..');

/**
 * One path per invocation (KI-80). With a fixed path, a second session's `globalSetup` deletes the data
 * the first one has just seeded and its server still holds open, so neither run's numbers are real.
 * `PLAYWRIGHT_SCRATCH_DB` names the path when set; the default carries the pid and the load-time
 * millisecond, because a recycled pid alone could match a peer's stale file.
 *
 * ponytail: per-invocation files are never swept, so `database/` keeps one file per run under the
 * existing `/database/*.sqlite*` ignore. Upgrade path: an `exit` hook removing only the resolved path.
 */
export function scratchDatabasePath(env: NodeJS.ProcessEnv = process.env): string {
    const named = env.PLAYWRIGHT_SCRATCH_DB;

    if (named !== undefined && named !== '') {
        return isAbsolute(named) ? named : resolve(repoRoot, named);
    }

    return join(repoRoot, 'database', `browser-scratch-${process.pid}-${Date.now()}.sqlite`);
}

/** Removes exactly this path and its write-ahead siblings. Never a glob: a pattern reaches a peer's file. */
export function removeScratchDatabase(path: string): void {
    // The write-ahead siblings go with the main file. WAL is on for this connection, so a leftover
    // -wal beside a deleted database reads as `file is not a database` rather than as an empty schema.
    // `maxRetries` because Windows answers EPERM for a file a dying process still holds a handle to, and
    // a gate that aborts on that reads as a broken harness rather than as a slow one.
    for (const suffix of ['', '-wal', '-shm']) {
        rmSync(`${path}${suffix}`, { force: true, maxRetries: 10, retryDelay: 500 });
    }
}

export const SCRATCH_DATABASE = scratchDatabasePath();

export default async function globalSetup(): Promise<void> {
    // Fail if a cached config would override the harness environment and point the server
    // at the canonical development database instead of the scratch file.
    try {
        execSync('php artisan config:clear', {
            cwd: repoRoot,
            stdio: 'ignore',
        });
    } catch {
        // Config cache might not exist; that's fine.
    }

    // Only this invocation's path is removed. The KI-80 defect was this step reaching a shared file.
    removeScratchDatabase(SCRATCH_DATABASE);

    // Laravel refuses a SQLite path that does not exist — `SQLiteDatabaseDoesNotExistException` from
    // `SQLiteConnector::parseDatabasePath()` — so the file is created empty and `migrate` fills it.
    writeFileSync(SCRATCH_DATABASE, '');

    const harnessEnv = {
        ...process.env,
        DB_DATABASE: SCRATCH_DATABASE,
        UMA_DATABASE_ROLE: 'browser',
        UMA_EXPECTED_DATABASE: SCRATCH_DATABASE,
    };

    execSync('php artisan migrate --seed --no-interaction', {
        cwd: repoRoot,
        env: harnessEnv,
        stdio: 'inherit',
    });

    // Prove the application will open the scratch database with the harness environment.
    // `uma:db-guard` returns non-zero if the resolved identity is unsafe for the declared role.
    execSync('php artisan uma:db-guard', {
        cwd: repoRoot,
        env: harnessEnv,
        stdio: 'inherit',
    });
}
