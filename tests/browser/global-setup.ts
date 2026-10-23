import { execSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import { readFileSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
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

/**
 * Where this repository's setup publishes the path it resolved.
 *
 * `scratchDatabasePath()` is **per process**, not per run: it stamps `process.pid` and the load-time
 * millisecond. The config, the setup and every worker are separate processes, so three of them
 * compute three different paths — and `DatabaseSync` on a path that does not exist creates an empty
 * database rather than failing, so a fixture reading the wrong path reports "no such table" against a
 * file it just invented.
 *
 * This has now been paid for three times: `ura-completed-run.ts` (the `SCRATCH_DATABASE`-per-process
 * fix recorded in `CHANGELOG.md`), `b6a7d0f0` (the dashboard absence probe), and
 * `tests/utils/review-candidates.ts`, which called the function at use time and so opened a fresh empty
 * file on every call.
 *
 * The fix is one published value rather than three smarter consumers: setup writes the path it
 * migrated, and every helper reads `resolvedScratchDatabase()`. The pointer lives in the OS temp
 * directory, keyed by this repository's root, so two clones cannot collide and nothing is added to
 * the tree.
 */
function pointerPath(): string {
    const key = createHash('sha1').update(repoRoot).digest('hex').slice(0, 12);

    return join(tmpdir(), `trainer-desk-playwright-scratch-${key}.path`);
}

/**
 * The scratch path the current suite actually migrated.
 *
 * Resolution order: `PLAYWRIGHT_SCRATCH_DB` (a named path wins, as it always has) → the pointer the
 * setup wrote → the per-process default. The last fallback keeps a bare import working outside a
 * Playwright run (a unit test, an ad-hoc script); inside a run the pointer is always present.
 */
export function resolvedScratchDatabase(env: NodeJS.ProcessEnv = process.env): string {
    const named = env.PLAYWRIGHT_SCRATCH_DB;

    if (named !== undefined && named !== '') {
        return isAbsolute(named) ? named : resolve(repoRoot, named);
    }

    try {
        const published = readFileSync(pointerPath(), 'utf8').trim();

        if (published !== '') {
            return published;
        }
    } catch {
        // No setup has run in this checkout yet; fall through to the per-process default.
    }

    return scratchDatabasePath(env);
}

/**
 * The browser role runs with Inertia DevTools switched off (R3-32).
 *
 * `DevToolsServiceProvider::register()` flushes and then prunes its entry cache inside the handled
 * request, walking `storage/inertia-devtools` and unlinking expired entries synchronously. Past roughly
 * 28k files that loop outlives `max_execution_time`, `/up` never answers clean, and a browser run aborts
 * with zero tests — which does not fail loudly, it silently stops being evidence.
 *
 * The flag lives here rather than in `playwright.config.ts` so the assertion below and the server the
 * harness starts cannot drift apart: both read this one constant. `DevTools::enabled()` falls back to
 * `environment('local')` when the config value is null, and the harness server boots as local, so an
 * unset flag means recording is on.
 */
export const DEVTOOLS_ENV = { INERTIA_DEVTOOLS_ENABLED: 'false' } as const;

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

    // Prove the application honours the flag the harness is about to hand the server, rather than
    // trusting that a name typed into an env block reaches `config()`.
    //
    // Deliberately not an assertion that `storage/inertia-devtools` is absent: that directory belongs to
    // the dev role as well, and a peer's `composer dev` in this shared worktree populates it. Failing on
    // its existence would block every browser run for a condition the browser role did not cause.
    const devtoolsState = execSync('php artisan config:show inertia.devtools.enabled', {
        cwd: repoRoot,
        env: { ...process.env, ...DEVTOOLS_ENV },
        encoding: 'utf8',
    });

    if (!devtoolsState.includes('false')) {
        throw new Error(
            'R3-32: the browser role would boot with Inertia DevTools recording on. '
            + `Expected \`inertia.devtools.enabled\` to resolve to false under ${JSON.stringify(DEVTOOLS_ENV)}, `
            + `got: ${devtoolsState.trim()}`,
        );
    }

    // Publish the path before it is used, so every worker and fixture resolves this one file rather
    // than computing a pid-stamped path of its own (see `resolvedScratchDatabase`).
    writeFileSync(pointerPath(), SCRATCH_DATABASE);

    // Only this invocation's path is removed. The KI-80 defect was this step reaching a shared file.
    removeScratchDatabase(SCRATCH_DATABASE);

    // Laravel refuses a SQLite path that does not exist — `SQLiteDatabaseDoesNotExistException` from
    // `SQLiteConnector::parseDatabasePath()` — so the file is created empty and `migrate` fills it.
    writeFileSync(SCRATCH_DATABASE, '');

    const harnessEnv = {
        ...process.env,
        ...DEVTOOLS_ENV,
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
