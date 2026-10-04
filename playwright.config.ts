import { defineConfig, devices } from '@playwright/test';

// Rendered-copy evidence for the ported Vue screens (ADR-0020 §1): once a screen renders
// client-side, the server response no longer carries its markup, so the copy and
// accessibility the repo asserts on are checked in a real browser here. Runs against the
// app's own scratch database via `php artisan serve`; run with `npm run test:browser`.
const port = 8127;
const baseURL = process.env.PLAYWRIGHT_BASE_URL ?? `http://127.0.0.1:${port}`;

export default defineConfig({
    testDir: './tests/browser',
    fullyParallel: true,
    forbidOnly: !!process.env.CI,
    retries: 0,
    reporter: 'list',
    use: {
        baseURL,
        trace: 'off',
    },
    projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
    webServer: {
        command: `php artisan serve --port=${port}`,
        url: baseURL,
        reuseExistingServer: true,
        timeout: 60_000,
    },
});
