/*
 * The mandatory browser-identity preflight — historical location, single implementation.
 *
 * This module used to register its own `test.beforeEach`, which only protected the specs that
 * imported it. One spec out of 137 did, so 136 ran unprotected while the docblock claimed a
 * mandatory gate. The enforcement now lives in `base-test.ts` as an `auto: true` fixture, because
 * Playwright requests a fixture for every test in a file that imports that `test` whether or not
 * the spec names it, and a rejected fixture fails the test before its body.
 *
 * The named re-exports stay so existing imports keep working, and so a reader arriving at the old
 * path is sent to the mechanism that actually holds.
 */
export {
    getIdentityPreflightFailed,
    getLastVerifiedIdentity,
    identityPreflightFailed,
    lastVerifiedIdentity,
    refuseIdentityPreflightForTest,
    resetIdentityPreflightForTest,
    test,
    verifyIdentityPreflight,
} from './base-test';
