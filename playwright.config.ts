import { defineConfig, devices } from '@playwright/test';
import { SCRATCH_DATABASE } from './tests/browser/global-setup';

// Rendered-copy evidence for the ported Vue screens (ADR-0020 §1): once a screen renders
// client-side, the server response no longer carries its markup, so the copy and
// accessibility the repo asserts on are checked in a real browser here. Runs against a scratch
// database the harness builds (`tests/browser/global-setup.ts`, KI-69) via `php artisan serve`;
// run with `npm run test:browser`.
// The port is overridable via `PLAYWRIGHT_PORT`. `reuseExistingServer: false` is mandatory:
// a reused server would carry whatever database it was started with (usually the shared dev file),
// which defeats the KI-69 contract that the suite owns its scratch database.
const port = Number(process.env.PLAYWRIGHT_PORT ?? 8127);
const baseURL = process.env.PLAYWRIGHT_BASE_URL ?? `http://127.0.0.1:${port}`;

const webServerEnv = {
    DB_DATABASE: SCRATCH_DATABASE,
    UMA_DATABASE_ROLE: 'browser',
    UMA_EXPECTED_DATABASE: SCRATCH_DATABASE,
};

export default defineConfig({
    testDir: './tests/browser',
    // The suite owns its data: five cases assert empty-state surfaces and the rest read the seeded
    // reference data, so the database is built from nothing before the first test (KI-69).
    globalSetup: './tests/browser/global-setup.ts',
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
        command: `php artisan serve --port=${port}`,
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
