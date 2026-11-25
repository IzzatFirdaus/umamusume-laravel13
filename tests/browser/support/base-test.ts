import { test as base, expect } from '@playwright/test';
import { readHarnessManifest, verifyServerIdentity, type IdentityResponse } from './server-identity';

/*
 * The mandatory browser-identity preflight, as a Playwright auto-fixture.
 *
 * Playwright 1.63 has no `setupFiles` option (verified against the installed
 * `@playwright/test` types), so a file that merely exports a `beforeEach` only protects the specs
 * that import it. That is how 136 of 137 specs came to run unprotected: the hook existed, the
 * import did not.
 *
 * `test.extend` with `{ auto: true }` is the supported mechanism that cannot be skipped by a spec
 * author: the fixture is requested for every test in a file that imports this `test`, before the
 * test body and before any user `beforeEach`, and a rejected fixture fails the test without running
 * its body. A spec that imports `test` from this module is protected by construction; the coverage
 * guard (`mandatory-preflight-coverage.spec.ts`) fails the run if any spec imports
 * `@playwright/test` directly instead.
 *
 * The probe runs once per test, not once per worker. A worker-lifetime cache would let a server
 * that passed at the start of the run keep that trust after being replaced by a different one
 * mid-run, which is precisely the case the cached-success regression pins: the first test sees
 * server A and passes, A is swapped for an incorrect B, and the next ordinary test must refuse
 * before it mutates anything. One loopback GET per test buys that; a wrong bundle is worth more
 * than the milliseconds.
 */

type ManifestReader = typeof readHarnessManifest;
type IdentityVerifier = typeof verifyServerIdentity;

/**
 * The session port the manifest is keyed by.
 *
 * The base URL is the authority: it is what Playwright is actually going to send requests to, and
 * `assertHarnessBaseUrl()` compares its port against the manifest. Reading `PLAYWRIGHT_PORT` instead
 * would let a run whose config bound one port probe another's manifest — which is how a refusal
 * proof at :8199 first reported a mismatch against :8123. `PLAYWRIGHT_PORT` stays as the fallback
 * for the case where no base URL is configured at all.
 */
function sessionPort(baseURL: string): number {
    try {
        const url = new URL(baseURL);
        const effective = url.port === '' ? (url.protocol === 'https:' ? 443 : 80) : Number(url.port);

        return Number.isFinite(effective) && effective > 0 ? effective : Number(process.env.PLAYWRIGHT_PORT ?? 8123);
    } catch {
        return Number(process.env.PLAYWRIGHT_PORT ?? 8123);
    }
}

/** The identity the most recent test verified against; the contract spec asserts the fixture ran. */
export let lastVerifiedIdentity: IdentityResponse | undefined;

/** A rejected identity is terminal for the worker; a test body must never run after it. */
export let identityPreflightFailed = false;

/** Getter form, for specs that cannot observe live reassignments through a re-export chain. */
export function getLastVerifiedIdentity(): IdentityResponse | undefined {
    return lastVerifiedIdentity;
}

export function getIdentityPreflightFailed(): boolean {
    return identityPreflightFailed;
}

/**
 * Ask the process actually serving this run's base URL to prove it is this run's harness server:
 * the loopback port in the manifest, the browser role, this run's token, and this run's scratch
 * database rather than the canonical development file.
 *
 * A rejection is terminal. The flag stops every later test in the worker before its body, so a
 * server that failed the probe is never followed by a write; it is deliberately not retried,
 * because a retry only re-asks the wrong process.
 */
export async function verifyIdentityPreflight(
    baseURL: string,
    readManifest: ManifestReader,
    verifyIdentity: IdentityVerifier,
): Promise<IdentityResponse> {
    if (identityPreflightFailed) {
        throw new Error('browser identity: this worker already refused the assigned server');
    }

    const expected = readManifest(sessionPort(baseURL));

    try {
        lastVerifiedIdentity = await verifyIdentity(baseURL, expected);

        return lastVerifiedIdentity;
    } catch (error) {
        identityPreflightFailed = true;
        lastVerifiedIdentity = undefined;

        throw error;
    }
}

/** Test-only reset, so the lifecycle cases can exercise a fresh worker identity. */
export function resetIdentityPreflightForTest(): void {
    lastVerifiedIdentity = undefined;
    identityPreflightFailed = false;
}

/** Test-only terminal state, matching a worker that already refused its assigned server. */
export function refuseIdentityPreflightForTest(): void {
    identityPreflightFailed = true;
}

export const test = base.extend<{ identityPreflight: void }>({
    // `auto: true` is what makes this mandatory rather than opt-in: Playwright requests the
    // fixture for every test in a file that imports this `test`, whether or not the spec mentions it.
    identityPreflight: [
        async ({ baseURL }, use) => {
            await verifyIdentityPreflight(baseURL ?? '', readHarnessManifest, verifyServerIdentity);
            await use();
        },
        { auto: true },
    ],
});

export { expect };
