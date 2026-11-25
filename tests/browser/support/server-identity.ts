import { createHash } from 'node:crypto';
import { mkdirSync, readFileSync, realpathSync, writeFileSync } from 'node:fs';
import { createServer } from 'node:net';
import { tmpdir } from 'node:os';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { resolvedScratchDatabase } from '../global-setup';

/**
 * Browser harness identity contract.
 *
 * The config, global setup, web server and every worker are separate Node/PHP
 * processes. A value computed from `process.pid` in one of them is not the
 * value another one sees, so the contract below is derived only from inputs
 * that are stable for one invocation: the repository root, the session port
 * and the session scratch path. The path is published by `globalSetup` and is
 * forced by `PLAYWRIGHT_SCRATCH_DB` in the supported invocation.
 *
 * Nothing here reads or prints the canonical database path. The probe returns
 * a digest and three booleans; the assertions compare those against values
 * this runner computed itself. A server that answered from a different
 * database, with a different role, or without this run's token cannot pass.
 */

const repoRoot = resolve(dirname(fileURLToPath(import.meta.url)), '..', '..', '..');

export const IDENTITY_PATH = '/__trainerdesk_browser_identity';
export const IDENTITY_PROBE = 'trainerdesk-browser-identity-v1';
export const IDENTITY_ENV_TOKEN = 'UMA_BROWSER_IDENTITY_TOKEN';

export interface ExpectedIdentity {
    port: number;
    baseURL: string;
    scratchDatabase: string;
    runToken: string;
}

/**
 * Config, global setup, the web server and each worker are separate processes.
 * The manifest is written once when the config is evaluated and read by the
 * preflight, so a worker never has to guess which port/path the runner chose.
 */
function manifestPath(port: number): string {
    const key = createHash('sha1').update(repoRoot).digest('hex').slice(0, 12);

    return join(tmpdir(), `trainer-desk-browser-identity-${key}-${port}.json`);
}

export function publishHarnessManifest(identity: ExpectedIdentity): void {
    mkdirSync(dirname(manifestPath(identity.port)), { recursive: true });
    writeFileSync(manifestPath(identity.port), JSON.stringify(identity), 'utf8');
}

export function readHarnessManifest(port: number): ExpectedIdentity {
    return JSON.parse(readFileSync(manifestPath(port), 'utf8')) as ExpectedIdentity;
}

export interface IdentityResponse {
    probe?: string;
    available?: boolean;
    role?: string;
    runToken?: string;
    dbDigest?: string;
    expectedMatches?: boolean;
    targetsCanonical?: boolean;
    serverPid?: number;
}

/** The session scratch path used when the caller did not name one. Port-bound so it is stable across processes. */
export function defaultSessionDatabase(port: number): string {
    return join(repoRoot, '.scratch-uma', 'sqlite', `browser-${port}.sqlite`);
}

/** A per-invocation token shared by the harness server process and this runner. */
export function browserRunToken(env: NodeJS.ProcessEnv = process.env): string {
    const port = Number(env.PLAYWRIGHT_PORT ?? 8123);
    const seed = `${repoRoot}|${port}|${resolvedScratchDatabase(env)}`;

    return createHash('sha256').update(seed).digest('hex').slice(0, 24);
}

/**
 * Digest of the absolute, real-resolved database path. Must match PHP's
 * `DatabaseGuard::normalize()` followed by `hash('sha256', $resolved)`.
 */
export function expectedDatabaseDigest(databasePath: string): string {
    let normalized = resolve(databasePath);

    try {
        normalized = realpathSync(normalized);
    } catch {
        // A missing file is itself a failed identity; the digest still has a
        // deterministic value so the assertion reports the mismatch.
    }

    return createHash('sha256').update(normalized.replace(/\\/g, '/')).digest('hex');
}

/** A browser suite may only talk to its own loopback server on its own port. */
export function assertHarnessBaseUrl(baseURL: string, port: number): void {
    let url: URL;

    try {
        url = new URL(baseURL);
    } catch {
        throw new Error(`browser identity: PLAYWRIGHT_BASE_URL is not a URL: ${baseURL}`);
    }

    const host = url.hostname.replace(/^\[|\]$/g, '');
    const loopback = host === '127.0.0.1' || host === 'localhost' || host === '::1';
    const effectivePort = url.port === '' ? (url.protocol === 'https:' ? 443 : 80) : Number(url.port);

    if (url.protocol !== 'http:' || !loopback || effectivePort !== port) {
        throw new Error(
            `browser identity: base URL ${baseURL} is not the harness server on 127.0.0.1:${port}. `
            + 'Refusing to send browser tests to an application this run did not start.',
        );
    }
}

/** Fail before the harness starts a server if another process already owns the port. */
export async function assertPortAvailable(port: number): Promise<void> {
    await new Promise<void>((resolvePromise, rejectPromise) => {
        const server = createServer();

        server.once('error', (error: NodeJS.ErrnoException) => {
            if (error.code === 'EADDRINUSE') {
                rejectPromise(new Error(
                    `browser identity: port ${port} is already occupied. Refusing to reuse a server `
                    + 'whose database identity is not this run\'s scratch database.',
                ));

                return;
            }

            rejectPromise(error);
        });

        server.listen(port, '127.0.0.1', () => {
            server.close(() => resolvePromise());
        });
    });
}

/** Read-only probe. Never sends a request to an application path that can mutate data. */
export async function fetchIdentity(baseURL: string): Promise<IdentityResponse> {
    const response = await fetch(`${baseURL.replace(/\/$/, '')}${IDENTITY_PATH}`, {
        signal: AbortSignal.timeout(15_000),
        headers: { accept: 'application/json', 'cache-control': 'no-store' },
    });

    if (!response.ok) {
        throw new Error(
            `browser identity: probe answered HTTP ${response.status}. The server is not the harness `
            + 'server started with the browser role and this run\'s token.',
        );
    }

    return (await response.json()) as IdentityResponse;
}

/** Compare the probe answer against values computed by this runner. */
export function assertIdentity(actual: IdentityResponse, expected: ExpectedIdentity): void {
    if (actual.probe !== IDENTITY_PROBE || actual.available !== true) {
        throw new Error('browser identity: the server did not present the harness identity probe.');
    }

    if (actual.role !== 'browser') {
        throw new Error(`browser identity: expected role browser, probe reported ${String(actual.role)}.`);
    }

    if (actual.runToken !== expected.runToken) {
        throw new Error('browser identity: the server does not carry this run\'s identity token.');
    }

    if (actual.targetsCanonical !== false) {
        throw new Error('browser identity: the serving process resolves to the canonical development database.');
    }

    if (actual.expectedMatches !== true) {
        throw new Error('browser identity: the serving process does not match UMA_EXPECTED_DATABASE.');
    }

    const digest = expectedDatabaseDigest(expected.scratchDatabase);

    if (actual.dbDigest !== digest) {
        throw new Error('browser identity: the serving process opened a database other than this run\'s scratch database.');
    }
}

/** Full preflight used by the mandatory beforeEach hook. */
export async function verifyServerIdentity(
    baseURL: string,
    expected: ExpectedIdentity,
): Promise<IdentityResponse> {
    assertHarnessBaseUrl(baseURL, expected.port);
    const actual = await fetchIdentity(baseURL);
    assertIdentity(actual, expected);

    return actual;
}
