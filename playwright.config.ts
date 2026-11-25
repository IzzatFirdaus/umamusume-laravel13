import { defineConfig, devices } from '@playwright/test';
import { DEVTOOLS_ENV, resolvedScratchDatabase } from './tests/browser/global-setup';
import {
    IDENTITY_ENV_TOKEN,
    assertHarnessBaseUrl,
    assertPortAvailable,
    browserRunToken,
    defaultSessionDatabase,
    publishHarnessManifest,
} from './tests/browser/support/server-identity';

// Rendered-copy evidence for the ported Vue screens (ADR-0020 §1): once a screen renders
// client-side, the server response no longer carries its markup, so the copy and
// accessibility the repo asserts on are checked in a real browser here. Runs against a scratch
// database the harness builds (`tests/browser/global-setup.ts`, KI-69) through a harness-owned
// PHP server with an identity probe; run with `npm run test:browser`.
//
// Fixed default port; a second invocation on the same port refuses at setup rather than
// sharing a server — that is the intended serial contract (workers: 1, retries: 0).
// `PLAYWRIGHT_PORT` still wins when a session names its own.
const port = Number(process.env.PLAYWRIGHT_PORT ?? 8123);
const baseURL = process.env.PLAYWRIGHT_BASE_URL ?? `http://127.0.0.1:${port}`;

// Playwright starts webServer before globalSetup, so the port must be proven free here, while
// the config is evaluated in the runner and before this run binds anything. Workers re-import
// the config after the server is listening, so they must not repeat the bind check.
// `TEST_WORKER_INDEX` is set by Playwright only in worker processes.
if (process.env.TEST_WORKER_INDEX === undefined) {
    await assertPortAvailable(port);
}

// An unqualified run used to compute a pid-stamped scratch path here and a different one in
// globalSetup, so the server could open an unmigrated file while setup migrated another. Bind
// the default to the session port; every process then reads the same absolute path.
if (process.env.PLAYWRIGHT_SCRATCH_DB === undefined || process.env.PLAYWRIGHT_SCRATCH_DB === '') {
    process.env.PLAYWRIGHT_SCRATCH_DB = defaultSessionDatabase(port);
}
const scratchDatabase = resolvedScratchDatabase();

// Refuse a base URL that points at an application this run did not start, before a server is
// launched or a test is collected. A harness session must never send a write to the owner's app.
assertHarnessBaseUrl(baseURL, port);

const runToken = browserRunToken();
publishHarnessManifest({ port, baseURL, scratchDatabase, runToken });

const webServerEnv = {
    ...DEVTOOLS_ENV,
    DB_DATABASE: scratchDatabase,
    UMA_DATABASE_ROLE: 'browser',
    UMA_EXPECTED_DATABASE: scratchDatabase,
    [IDENTITY_ENV_TOKEN]: runToken,
};

export default defineConfig({
    testDir: './tests/browser',
    // The packaged-app spec needs NATIVEPHP_EXE_PATH and a built Windows binary; the dedicated
    // config `playwright.nativephp.config.ts` runs it in the acceptance workflow. Keep it out of
    // the default sweep so `npm run test:browser` measures the app, not the packaging harness.
    testIgnore: '**/nativephp-packaged.spec.ts',
    // The suite owns its data: five cases assert empty-state surfaces and the rest read the seeded
    // reference data, so the database is built from nothing before the first test (KI-69).
    globalSetup: './tests/browser/global-setup.ts',
    // Every test body — including the destructive delete spec — first has to pass the read-only
    // server/database identity probe registered by this file.
    setupFiles: './tests/browser/support/identity-preflight.ts',
    // Serial: the dev server compiles modules on first request, so parallel workers
    // contend on a cold cache and hydrate slowly. One worker keeps the runs honest.
    fullyParallel: false,
    workers: 1,
    forbidOnly: !!process.env.CI,
    retries: 0,
    reporter: 'list',
    // Raised to 180s on measurement: a document on a quiet host costs 5-6s in Laravel's hot-file path
    // (`/preferences` 6.34s with `public/hot`, 0.56s on the built manifest, 0.21s for a 404), and this
    // worktree is not usually quiet. `php -S` is single-process, so a document queued behind another
    // session's page (or behind `php artisan test`, which is CPU-bound here) costs what that request
    // costs — 11-16s each, observed at `:8141` while a suite ran elsewhere on the box. A test that
    // creates a run and then reads four documents is therefore four requests deep before its first
    // assertion, and 60s stopped the run at `page.goto` rather than at a wrong value. The assertions
    // keep their own 15s, so a real defect still fails fast; only the host's queue gets the longer
    // budget.
    timeout: 180_000,
    expect: { timeout: 15_000 },
    use: {
        baseURL,
        trace: 'off',
    },
    projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
    webServer: {
        // Direct `php -S`, not `php artisan serve`. On Windows ServeCommand rebuilds the child
        // process environment from `$_ENV` through a whitelist that excludes DB_DATABASE and the
        // UMA_* identity variables; this host only escapes that because variables_order=GPCS.
        // A php.ini change would silently point the serving process at the canonical database.
        // The harness router adds a loopback-only identity probe without adding any route to the
        // application itself, and `php -S` does not silently retry another port.
        command: `php -S 127.0.0.1:${port} -t public tests/browser/server-router.php`,
        // The server the harness starts must serve the database the setup step just built.
        // `reuseExistingServer: false` is mandatory — a reused server would carry whatever database
        // it was started with (usually the shared dev file), which defeats the KI-69 contract that
        // the suite owns its scratch database. The health probe is `/up` (Laravel's built-in health
        // endpoint) rather than the root URL, so a slow cold cache doesn't time out the ready check.
        env: webServerEnv,
        url: `${baseURL}/up`,
        reuseExistingServer: false,
        timeout: 60_000,
    },
});
