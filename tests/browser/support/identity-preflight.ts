import { test } from '@playwright/test';
import { readHarnessManifest, verifyServerIdentity, type IdentityResponse } from './server-identity';

/*
 * Mandatory gate for EVERY browser test, including the ones that write data.
 *
 * A config value alone only describes what the harness intended. This hook
 * asks the process that is actually serving the page to prove three things
 * before a test body runs: it is this run's loopback server (token + base URL),
 * it declared the browser role, and the database it resolved is this run's
 * scratch file rather than the canonical development database.
 *
 * The promise is cached per worker: one read-only probe per worker, not one
 * per test. If it rejects, every test in that worker refuses to start, which
 * is the intended fail-closed behaviour.
 */

let verified: Promise<IdentityResponse> | undefined;

/** Set only after a successful probe; the contract spec asserts the hook actually ran. */
export let lastVerifiedIdentity: IdentityResponse | undefined;

test.beforeEach(async ({ baseURL }) => {
    if (verified === undefined) {
        const port = Number(process.env.PLAYWRIGHT_PORT ?? 8123);
        const expected = readHarnessManifest(port);

        verified = verifyServerIdentity(baseURL ?? expected.baseURL, expected).then((identity) => {
            lastVerifiedIdentity = identity;

            return identity;
        });
    }

    await verified;
});
