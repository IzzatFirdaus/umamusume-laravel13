import { execSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import { mkdirSync, readFileSync, readdirSync, rmSync, statSync, writeFileSync } from 'node:fs';
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
 * The path targets `.scratch-uma/sqlite/` (git-ignored scratch workspace) rather than `database/`,
 * keeping the project's `database/` directory clean for the canonical application database and migrations.
 * See AGENTS.md §6 ".scratch-uma/ = investigation scratch space" and `.scratch-uma/sqlite/INDEX.md`.
 */
export function scratchDatabasePath(env: NodeJS.ProcessEnv = process.env): string {
    const named = env.PLAYWRIGHT_SCRATCH_DB;

    if (named !== undefined && named !== '') {
        return isAbsolute(named) ? named : resolve(repoRoot, named);
    }

    return join(repoRoot, '.scratch-uma', 'sqlite', `browser-scratch-${process.pid}-${Date.now()}.sqlite`);
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
 * The build directory this run measures, and where its snapshot id is recorded.
 *
 * `PLAYWRIGHT_BUILD_DIR` and `PLAYWRIGHT_ARTIFACT_DIR` exist so the build-snapshot regression case
 * can point at a temp fixture instead of the real `public/build`; an ordinary run leaves them unset
 * and gets the checked-out bundle plus the OS temp directory.
 */
function buildDirectory(env: NodeJS.ProcessEnv): string {
    const named = env.PLAYWRIGHT_BUILD_DIR;

    return named !== undefined && named !== '' ? (isAbsolute(named) ? named : resolve(repoRoot, named)) : resolve(repoRoot, 'public/build');
}

function buildSnapshotPath(env: NodeJS.ProcessEnv): string {
    const named = env.PLAYWRIGHT_ARTIFACT_DIR;
    const dir = named !== undefined && named !== '' ? (isAbsolute(named) ? named : resolve(repoRoot, named)) : tmpdir();
    const runId = env.PLAYWRIGHT_RUN_ID ?? 'run';

    return join(dir, `trainer-desk-build-snapshot-${runId}.json`);
}

/**
 * Name the exact bytes `public/build` holds at setup time, and publish that id.
 *
 * A concurrent `npm run build` can replace the assets between setup and the last spec, so a green
 * report could describe a bundle nobody tested (KI-101). Global setup calls this once and teardown
 * calls `assertBuildSnapshot()` once; a swap in between fails the run instead of passing quietly.
 * The id is a hash of the manifest plus every asset it names, so an edit to any shipped file changes
 * it, and only 12 hex characters are kept because the value is an identity, not a signature.
 */
export function captureBuildSnapshot(env: NodeJS.ProcessEnv = process.env): { id: string } {
    const buildDir = buildDirectory(env);
    const manifestPath = join(buildDir, 'manifest.json');
    const hash = createHash('sha256').update(readFileSync(manifestPath));

    let manifest: Record<string, { file?: string }>;

    try {
        manifest = JSON.parse(readFileSync(manifestPath, 'utf8')) as Record<string, { file?: string }>;
    } catch {
        throw new Error(`assertBuildSnapshot: ${manifestPath} is not readable JSON.`);
    }

    // Asset order must not depend on JSON key order: sort the files so two runs of the same bundle
    // agree on the id regardless of how Vite happened to emit the manifest.
    const files = Object.values(manifest)
        .map((entry) => entry.file)
        .filter((file): file is string => typeof file === 'string' && file !== '')
        .sort();

    for (const file of files) {
        hash.update(file).update(readFileSync(join(buildDir, file)));
    }

    const id = hash.digest('hex').slice(0, 12);

    mkdirSync(dirname(buildSnapshotPath(env)), { recursive: true });
    writeFileSync(buildSnapshotPath(env), JSON.stringify({ id }), 'utf8');
    env.PLAYWRIGHT_BUILD_ID = id;

    return { id };
}

/**
 * Re-read the build and fail if the bytes changed during this run.
 *
 * A missing snapshot is not a failure: the setup step may not have run (a unit test importing this
 * module, or a spec run through another config), and a teardown that aborted on that would report a
 * harness fault for a condition no bundle caused.
 */
export function assertBuildSnapshot(env: NodeJS.ProcessEnv = process.env): void {
    const recorded = env.PLAYWRIGHT_BUILD_ID;

    if (recorded === undefined || recorded === '') {
        return;
    }

    const buildDir = buildDirectory(env);
    const hash = createHash('sha256').update(readFileSync(join(buildDir, 'manifest.json')));
    const manifest = JSON.parse(readFileSync(join(buildDir, 'manifest.json'), 'utf8')) as Record<string, { file?: string }>;
    const files = Object.values(manifest)
        .map((entry) => entry.file)
        .filter((file): file is string => typeof file === 'string' && file !== '')
        .sort();

    for (const file of files) {
        hash.update(file).update(readFileSync(join(buildDir, file)));
    }

    const actual = hash.digest('hex').slice(0, 12);

    if (actual !== recorded) {
        throw new Error(
            `assertBuildSnapshot: build snapshot changed during this Playwright run `
            + `(setup ${recorded}, now ${actual}). Re-run the suite against one bundle.`,
        );
    }
}

/**
 * Removes the scratch pointer file created by the browser test harness.
 */
export function removeScratchPointer(): void {
    // Remove only the pointer this run published. A peer session's pointer stays in place, and a
    // pointer that names a different database is never touched even if it uses this key.
    try {
        const path = pointerPath();
        const published = readFileSync(path, 'utf8').trim();
        const own = resolvedScratchDatabase();
        const comparable = (value: string): string => value.replace(/\\/g, '/').toLowerCase();

        if (published !== '' && comparable(published) === comparable(own)) {
            rmSync(path, { force: true });
        }
    } catch {
        // No pointer, or a transient lock: leaving it behind is safer than deleting a peer's.
    }
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

/**
 * The newest mtime under a source tree, for the staleness refusal in `globalSetup()` (KI-101).
 * Walks the tree once; directory mtimes are not read (a directory's own mtime moves on rename
 * operations that leave file content unchanged, which would refuse an honest bundle).
 */
 // Freshness tolerance for filesystem mtime resolution (KI-101 gate). A source
 // file edited in the same millisecond as the build, or a clock step between the
 // build process and this setup process, can place the newest source mtime a
 // fraction of a second ahead of a manifest that is genuinely fresh. Allow the
 // source to lead the manifest by up to 2s before refusing; a bundle that is
 // genuinely stale (source older than its own build output) still trips the gate.
 // ponytail: 2s absorbs ms-resolution noise on this host; raise only if a slower
 // filesystem shows systematic false positives — never to skip a real rebuild.
 const FRESHNESS_TOLERANCE_MS = 2_000;

function newestSourceMtime(root: string): number {
    let newest = -1;

    const walk = (dir: string): void => {
        for (const entry of readdirSync(dir, { withFileTypes: true })) {
            const full = join(dir, entry.name);

            // `._DS_Store`-class noise and Vite's own caches never ship; reading them would make
            // the check refuse on files no bundle carries.
            if (entry.isDirectory()) {
                if (entry.name === 'node_modules' || entry.name === '.vite') {
                    continue;
                }

                walk(full);

                continue;
            }

            newest = Math.max(newest, statSync(full).mtimeMs);
        }
    };

    walk(root);

    return newest;
}

export default async function globalSetup(): Promise<void> {
    // KI-101 (2026-10-16 half, S-RS-COMPLETE-1 slice B.1): refuse to run against a stale bundle.
    // The harness serves whatever `public/build` holds; a Vue change that lands after the last
    // `npm run build` is invisible to every spec, which then report the old bundle's behaviour as
    // green (measured 2026-11-10: a mutated pager line passed its own spec against a pre-mutation
    // bundle). The refusal costs only the session that needed the rebuild — an in-harness rebuild
    // would serialise every concurrent session behind one Vite build, which is the KI-70 hazard.
    // `PLAYWRIGHT_FRESH_BUILD=1` skips the check for a session that just rebuilt and knows it.
    if (process.env.PLAYWRIGHT_FRESH_BUILD !== '1') {
        const manifestPath = resolve(repoRoot, 'public/build/manifest.json');

        let manifestTime = -1;

        try {
            manifestTime = statSync(manifestPath).mtimeMs;
        } catch {
            throw new Error(
                'public/build is absent; run npm run build before the browser suite'
                + ' (or set PLAYWRIGHT_FRESH_BUILD=1 to override).',
            );
        }

        // The newest source file under resources/js decides staleness. A peer's in-flight edit is
        // exactly the case this check exists for: the bundle cannot contain it either.
        const newest = newestSourceMtime(resolve(repoRoot, 'resources/js'));

        if (newest > manifestTime + FRESHNESS_TOLERANCE_MS) {
            throw new Error(
                `public/build is older than resources/js (manifest ${Math.round(manifestTime)}, `
                + `newest source ${Math.round(newest)}); run npm run build `
                + '(or set PLAYWRIGHT_FRESH_BUILD=1 to override).',
            );
        }
    }

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
    // A zero exit alone is not enough: it also has to report the browser role, this run's path as
    // both the resolved and the expected database, and a non-canonical target.
    const guardOutput = execSync('php artisan uma:db-guard', {
        cwd: repoRoot,
        env: harnessEnv,
        encoding: 'utf8',
    });

    process.stdout.write(guardOutput);

    const guardLine = (label: string): string => guardOutput
        .split(/\r?\n/)
        .find((line) => line.trimStart().startsWith(label))?.trim() ?? '';
    const comparable = (value: string): string => value.replace(/\\/g, '/').toLowerCase();
    const guardChecks: Array<[string, boolean]> = [
        ['browser role', guardLine('role').includes('browser')],
        ['resolved scratch database', comparable(guardLine('resolved')).includes(comparable(SCRATCH_DATABASE))],
        ['expected scratch database', comparable(guardLine('expected')).includes(comparable(SCRATCH_DATABASE))],
        ['non-canonical target', guardLine('targets canonical').endsWith('no')],
        ['safe identity verdict', guardOutput.includes('Database identity is safe.')],
    ];
    const failedGuardChecks = guardChecks.filter(([, passed]) => !passed).map(([name]) => name);

    if (failedGuardChecks.length > 0) {
        throw new Error(
            `browser identity: uma:db-guard did not confirm ${failedGuardChecks.join(', ')} `
            + 'for the scratch database this setup just migrated.',
        );
    }

    // Name the bundle this run is about to measure. `global-teardown.ts` re-reads it and fails the
    // run if a concurrent build swapped the assets mid-way.
    process.env.PLAYWRIGHT_BUILD_ID = captureBuildSnapshot().id;
}
