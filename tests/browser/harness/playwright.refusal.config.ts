import { defineConfig, devices } from '@playwright/test';
import { mkdirSync, rmSync, writeFileSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { IDENTITY_ENV_TOKEN, assertPortAvailable, publishHarnessManifest } from '../support/server-identity';

/*
 * Config for the refusal-before-body proof, driven by `run-refusal-proof.mjs`.
 *
 * This config starts a disposable server that is deliberately NOT this run's assigned server: it
 * carries the wrong identity token, so `assertIdentity()` rejects it. The point is to run ordinary
 * Playwright — a real `test` call with a real `beforeEach` and a real test body — against that
 * server and observe that neither the mutating setup nor the body executes.
 *
 * It is a separate config because the real one refuses to start a server it does not own. Here the
 * refusal is the subject, so the harness itself creates the impostor and states what it is.
 */

const port = Number(process.env.REFUSAL_PROOF_PORT ?? 8199);
const baseURL = `http://127.0.0.1:${port}`;
const repoRoot = resolve(dirname(fileURLToPath(import.meta.url)), '..', '..', '..');
const scratchDir = process.env.REFUSAL_PROOF_DIR ?? join(repoRoot, '.scratch-uma', 'refusal-proof');

mkdirSync(scratchDir, { recursive: true });

const database = join(scratchDir, 'refusal-proof.sqlite');
rmSync(database, { force: true });
writeFileSync(database, '');

// Playwright starts the web server before it collects tests, and workers then re-import this config
// after the server is already listening. `TEST_WORKER_INDEX` is set only in worker processes, so the
// bind check has to be skipped there or every worker refuses the port its own server holds.
if (process.env.TEST_WORKER_INDEX === undefined) {
    await assertPortAvailable(port);
}

// The manifest states the token this run believes it issued. The server below is started with a
// different one, which is exactly the replacement-server case: the runner's belief and the serving
// process disagree, and the preflight has to notice.
publishHarnessManifest({ port, baseURL, scratchDatabase: database, runToken: 'refusal-proof-expected-token' });

export default defineConfig({
    testDir: import.meta.dirname,
    testMatch: 'refusal-proof.spec.ts',
    fullyParallel: false,
    workers: 1,
    retries: 0,
    reporter: [['list'], ['json', { outputFile: join(scratchDir, 'refusal-proof-report.json') }]],
    timeout: 60_000,
    expect: { timeout: 5_000 },
    use: { baseURL, trace: 'off' },
    projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
    webServer: {
        // Explicit cwd: Playwright resolves a relative `command` from the config file's directory,
        // and this config lives two levels under the repo root, so `-t public` and the router path
        // would both miss without it.
        command: `php -S 127.0.0.1:${port} -t public tests/browser/server-router.php`,
        cwd: repoRoot,
        env: {
            INERTIA_DEVTOOLS_ENABLED: 'false',
            DB_DATABASE: database,
            UMA_DATABASE_ROLE: 'browser',
            UMA_EXPECTED_DATABASE: database,
            // The impostor: a token this run did not issue.
            [IDENTITY_ENV_TOKEN]: 'refusal-proof-server-token',
        },
        url: `${baseURL}/up`,
        reuseExistingServer: false,
        timeout: 60_000,
    },
});
