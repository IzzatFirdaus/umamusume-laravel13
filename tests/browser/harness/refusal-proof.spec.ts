import { expect, test } from '../support/base-test';
import { appendFileSync } from 'node:fs';
import { join } from 'node:path';

/*
 * An ORDINARY spec, deliberately run against a server that is not this run's assigned server.
 *
 * Nothing here imports a refusal helper or calls one. The file looks like any other mutating spec in
 * this suite: an `import` of the enforced `test`, a `beforeEach` that sets something up, and a body
 * that writes. It is only ever executed by `run-refusal-proof.mjs` through
 * `playwright.refusal.config.ts`, whose web server carries the wrong identity token.
 *
 * Both halves write a marker file, so the driver can check after the run whether the fixture really
 * stopped them. A suite that merely reported a failure would not distinguish "the preflight
 * refused" from "the body ran and then something else failed" — the markers do.
 */

const markerPath = (name: string): string => join(process.env.REFUSAL_PROOF_DIR ?? process.cwd(), name);

test.beforeEach(() => {
    // Mutating setup. Must NOT run: the preflight fixture is requested before any user hook.
    appendFileSync(markerPath('before-each-ran.txt'), 'setup executed\n');
});

test('an ordinary mutating test against a replacement server must not execute', async ({ page }) => {
    appendFileSync(markerPath('body-ran.txt'), 'body executed\n');

    await page.goto('/');

    expect(page).toBeTruthy();
});
