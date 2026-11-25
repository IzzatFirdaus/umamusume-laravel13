import { expect, test } from './support/base-test';
import { spawn, type ChildProcess } from 'node:child_process';
import { createServer as createHttpServer } from 'node:http';
import { createServer } from 'node:net';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { getIdentityPreflightFailed, getLastVerifiedIdentity, refuseIdentityPreflightForTest, resetIdentityPreflightForTest, verifyIdentityPreflight } from './support/identity-preflight';
import { existsSync, rmSync, writeFileSync } from 'node:fs';
import {
    assertHarnessBaseUrl,
    assertIdentity,
    assertPortAvailable,
    browserRunToken,
    expectedDatabaseDigest,
    readHarnessManifest,
    verifyServerIdentity,
    type ExpectedIdentity,
} from './support/server-identity';

/*
 * Check #3: the browser isolation contract, measured rather than inferred.
 *
 * Every case here is read-only against this run's harness server. The negative
 * cases spin up disposable PHP servers whose DB_DATABASE points at a scratch
 * path under this run's scratch directory; none of them names or opens the
 * owner's canonical database, and none sends a data-mutating request.
 *
 * The first case proves the mandatory setupFiles hook really ran in this
 * worker, rather than merely existing in the config.
 */

const repoRoot = resolve(dirname(fileURLToPath(import.meta.url)), '..', '..');
const harness = (): ExpectedIdentity => readHarnessManifest(Number(process.env.PLAYWRIGHT_PORT ?? 8123));

async function freePort(): Promise<number> {
    return new Promise<number>((resolvePort, reject) => {
        const server = createServer();

        server.once('error', reject);
        server.listen(0, '127.0.0.1', () => {
            const address = server.address();
            const port = typeof address === 'object' && address !== null ? address.port : 0;

            server.close(() => resolvePort(port));
        });
    });
}

async function stop(child: ChildProcess): Promise<void> {
    if (child.exitCode !== null || child.signalCode !== null) {
        return;
    }

    child.kill('SIGTERM');

    await Promise.race([
        new Promise<void>((done) => child.once('exit', () => done())),
        new Promise<void>((done) => setTimeout(done, 2000)),
    ]);
}

interface DisposableApp {
    baseURL: string;
    port: number;
    stop: () => Promise<void>;
}

async function startDisposableApp(options: {
    database: string;
    expectedDatabase?: string;
    role?: string;
    token: string;
}): Promise<DisposableApp> {
    return startDisposableAppOnPort(await freePort(), options);
}

/**
 * Same, on a port the caller names. A replacement server has to take the port the previous one
 * released, or it is merely a different session rather than a swap of the process this run believes
 * it is talking to.
 */
async function startDisposableAppOnPort(port: number, options: {
    database: string;
    expectedDatabase?: string;
    role?: string;
    token: string;
}): Promise<DisposableApp> {
    const env: NodeJS.ProcessEnv = {
        ...process.env,
        DB_DATABASE: options.database,
        UMA_DATABASE_ROLE: options.role ?? 'browser',
        UMA_BROWSER_IDENTITY_TOKEN: options.token,
        INERTIA_DEVTOOLS_ENABLED: 'false',
    };

    if (options.expectedDatabase === undefined) {
        delete env.UMA_EXPECTED_DATABASE;
    } else {
        env.UMA_EXPECTED_DATABASE = options.expectedDatabase;
    }

    const child = spawn(
        'php',
        ['-S', `127.0.0.1:${port}`, '-t', 'public', 'tests/browser/server-router.php'],
        { cwd: repoRoot, env, stdio: 'ignore' },
    );
    const baseURL = `http://127.0.0.1:${port}`;

    try {
        for (let attempt = 0; attempt < 50; attempt += 1) {
            if (child.exitCode !== null) {
                throw new Error(`disposable server exited during startup (code ${child.exitCode})`);
            }

            try {
                await fetch(`${baseURL}/up`, { signal: AbortSignal.timeout(1000) });
                break;
            } catch {
                await new Promise((done) => setTimeout(done, 100));
            }
        }

        return { baseURL, port, stop: () => stop(child) };
    } catch (error) {
        await stop(child);
        throw error;
    }
}

test('the mandatory preflight ran in this worker and matches this run manifest', async ({ baseURL }) => {
    const expected = harness();

    expect(baseURL).toBe(expected.baseURL);
    expect(getLastVerifiedIdentity(), 'the auto-fixture in support/base-test.ts verified the server').toBeDefined();
    expect(getLastVerifiedIdentity()?.runToken).toBe(expected.runToken);
    expect(getLastVerifiedIdentity()?.dbDigest).toBe(expectedDatabaseDigest(expected.scratchDatabase));
    expect(getLastVerifiedIdentity()?.serverPid).toBeGreaterThan(0);
    expect(getLastVerifiedIdentity()?.targetsCanonical).toBe(false);
    expect(getLastVerifiedIdentity()?.expectedMatches).toBe(true);
    expect(getIdentityPreflightFailed()).toBe(false);
});

test('refuses a base URL that is not this run loopback server on this run port', async () => {
    const expected = harness();
    const otherPort = expected.port + 1;

    expect(() => assertHarnessBaseUrl(`http://127.0.0.1:${otherPort}`, expected.port)).toThrow(/not the harness server/);
    expect(() => assertHarnessBaseUrl('http://example.test:8123', 8123)).toThrow(/not the harness server/);
    expect(() => assertHarnessBaseUrl('https://127.0.0.1:8123', 8123)).toThrow(/not the harness server/);
    expect(() => assertHarnessBaseUrl(expected.baseURL, expected.port)).not.toThrow();
});

test('refuses an unrelated HTTP server answering on another port', async () => {
    const expected = harness();
    const port = await freePort();
    const server = createHttpServer((_request, response) => {
        response.writeHead(404, { 'content-type': 'text/plain' });
        response.end('unrelated server');
    });

    await new Promise<void>((done) => server.listen(port, '127.0.0.1', done));

    try {
        await expect(verifyServerIdentity(`http://127.0.0.1:${port}`, { ...expected, port, baseURL: `http://127.0.0.1:${port}` }))
            .rejects.toThrow(/probe answered HTTP 404/);
    } finally {
        await new Promise<void>((done) => server.close(() => done()));
    }
});

test('refuses when the expected port is already occupied', async () => {
    const port = await freePort();
    const server = createServer();

    await new Promise<void>((done) => server.listen(port, '127.0.0.1', done));

    try {
        await expect(assertPortAvailable(port)).rejects.toThrow(/already occupied/);
    } finally {
        await new Promise<void>((done) => server.close(() => done()));
    }

    await expect(assertPortAvailable(await freePort())).resolves.toBeUndefined();
});

test('refuses a receiving application that uses a different scratch database', async () => {
    const expected = harness();
    const peerDatabase = join(dirname(expected.scratchDatabase), `peer-${process.pid}-${Date.now()}.sqlite`);

    // Same token and role as this run: only the database differs, so the refusal must come
    // from the digest/expected check rather than from the surface identity fields.
    const app = await startDisposableApp({
        database: peerDatabase,
        expectedDatabase: peerDatabase,
        token: expected.runToken,
    });

    try {
        await expect(verifyServerIdentity(app.baseURL, { ...expected, port: app.port, baseURL: app.baseURL }))
            .rejects.toThrow(/database other than/);
    } finally {
        await app.stop();
    }
});

test('refuses missing or inconsistent expected database identity', async () => {
    const expected = harness();
    const peerDatabase = join(dirname(expected.scratchDatabase), `expected-peer-${process.pid}-${Date.now()}.sqlite`);

    const missing = await startDisposableApp({ database: expected.scratchDatabase, token: expected.runToken });

    try {
        await expect(verifyServerIdentity(missing.baseURL, { ...expected, port: missing.port, baseURL: missing.baseURL }))
            .rejects.toThrow(/UMA_EXPECTED_DATABASE/);
    } finally {
        await missing.stop();
    }

    const inconsistent = await startDisposableApp({
        database: expected.scratchDatabase,
        expectedDatabase: peerDatabase,
        token: expected.runToken,
    });

    try {
        // With a mismatched expected identity the application's own boot guard refuses
        // before the probe can answer (HTTP 500); with a missing one the probe reports
        // expectedMatches=false. Both are safe refusals, and neither reaches app data.
        await expect(verifyServerIdentity(inconsistent.baseURL, { ...expected, port: inconsistent.port, baseURL: inconsistent.baseURL }))
            .rejects.toThrow(/UMA_EXPECTED_DATABASE|probe answered HTTP 500/);
    } finally {
        await inconsistent.stop();
    }
});

test('refuses when the isolated browser role is absent or incorrect', async () => {
    const expected = harness();

    for (const role of ['development', '']) {
        const app = await startDisposableApp({
            database: expected.scratchDatabase,
            expectedDatabase: expected.scratchDatabase,
            token: expected.runToken,
            role,
        });

        try {
            await expect(verifyServerIdentity(app.baseURL, { ...expected, port: app.port, baseURL: app.baseURL }))
                .rejects.toThrow(/probe answered HTTP 404/);
        } finally {
            await app.stop();
        }
    }
});

test('two concurrent disposable sessions keep distinct servers, databases and tokens', async () => {
    const dir = dirname(harness().scratchDatabase);
    const stamp = `${process.pid}-${Date.now()}`;
    const firstDatabase = join(dir, `session-a-${stamp}.sqlite`);
    const secondDatabase = join(dir, `session-b-${stamp}.sqlite`);
    const firstToken = browserRunToken({ ...process.env, PLAYWRIGHT_PORT: '8601', PLAYWRIGHT_SCRATCH_DB: firstDatabase });
    const secondToken = browserRunToken({ ...process.env, PLAYWRIGHT_PORT: '8602', PLAYWRIGHT_SCRATCH_DB: secondDatabase });

    expect(firstToken).not.toBe(secondToken);
    expect(expectedDatabaseDigest(firstDatabase)).not.toBe(expectedDatabaseDigest(secondDatabase));

    const first = await startDisposableApp({
        database: firstDatabase,
        expectedDatabase: firstDatabase,
        token: firstToken,
    });
    const second = await startDisposableApp({
        database: secondDatabase,
        expectedDatabase: secondDatabase,
        token: secondToken,
    });

    try {
        expect(first.port).not.toBe(second.port);

        const firstIdentity = await verifyServerIdentity(first.baseURL, {
            port: first.port, baseURL: first.baseURL, scratchDatabase: firstDatabase, runToken: firstToken,
        });
        const secondIdentity = await verifyServerIdentity(second.baseURL, {
            port: second.port, baseURL: second.baseURL, scratchDatabase: secondDatabase, runToken: secondToken,
        });

        expect(firstIdentity.dbDigest).not.toBe(secondIdentity.dbDigest);
        expect(firstIdentity.serverPid).not.toBe(secondIdentity.serverPid);

        // Neither session can present the other's identity.
        expect(() => assertIdentity(firstIdentity, {
            port: second.port, baseURL: second.baseURL, scratchDatabase: secondDatabase, runToken: secondToken,
        })).toThrow();
        expect(() => assertIdentity(secondIdentity, {
            port: first.port, baseURL: first.baseURL, scratchDatabase: firstDatabase, runToken: firstToken,
        })).toThrow();
    } finally {
        await first.stop();
        await second.stop();
    }
});

test('a verified server that is replaced by an incorrect one refuses before the next test mutates', async () => {
    /*
     * The cached-success lifecycle, measured on ordinary Playwright rather than on a helper.
     *
     * The historical defect was a per-worker success cache: server A passed once, the worker kept
     * that verdict, A was then replaced by a server with a different identity, and every later test
     * in the worker ran against B on A's reputation. A cache that outlives the process it measured
     * is not a check.
     *
     * This case reproduces the sequence with the real fixture body. `preflightFixture()` is the same
     * generator `base-test.ts` hands to Playwright, so what runs here is the enforcement, not a
     * stand-in for it:
     *
     *   1. A correct disposable server passes the probe (the cached success).
     *   2. It is stopped, and an impostor with a different token takes the same port.
     *   3. The probe runs again, exactly as the next test's fixture would.
     *   4. It must reject, and a would-be mutation after it must never be reached.
     */
    const dir = dirname(harness().scratchDatabase);
    const stamp = `${process.pid}-${Date.now()}`;
    const database = join(dir, `lifecycle-${stamp}.sqlite`);
    const token = browserRunToken({ ...process.env, PLAYWRIGHT_PORT: '8611', PLAYWRIGHT_SCRATCH_DB: database });

    const correct = await startDisposableApp({ database, expectedDatabase: database, token });
    const port = correct.port;
    const baseURL = correct.baseURL;
    const expected: ExpectedIdentity = { port, baseURL, scratchDatabase: database, runToken: token };
    const mutationSentinel = join(dir, `lifecycle-mutation-${stamp}.txt`);
    let mutations = 0;

    try {
        // 1. The correct server passes, which is the success the old cache would have kept.
        const verified = await verifyServerIdentity(baseURL, expected);

        expect(verified.runToken).toBe(token);
        expect(verified.targetsCanonical).toBe(false);
        expect(verified.expectedMatches).toBe(true);

        await correct.stop();

        // 2. An impostor takes the same port with the same database but a token this run never
        //    issued. Same port is what makes it a replacement rather than a different session.
        const impostor = await startDisposableAppOnPort(port, {
            database,
            expectedDatabase: database,
            token: 'replacement-server-token',
        });

        try {
            // 3. The probe must notice. This is the request the next test's fixture would make.
            await expect(verifyServerIdentity(baseURL, expected)).rejects.toThrow(/identity token/);

            // 4. A mutation written after a refusal is unreachable: the fixture throws before the
            //    test body, so the body below cannot run. The counter stands in for the write.
            const runBodyAfterRefusal = async (): Promise<void> => {
                await verifyServerIdentity(baseURL, expected);
                mutations += 1;
                writeFileSync(mutationSentinel, 'mutated');
            };

            await expect(runBodyAfterRefusal()).rejects.toThrow(/identity token/);
            expect(mutations).toBe(0);
            expect(existsSync(mutationSentinel)).toBe(false);
        } finally {
            await impostor.stop();
        }
    } finally {
        await correct.stop();
        rmSync(mutationSentinel, { force: true });
    }

    // The same lifecycle through the fixture's own cached implementation: a worker that already
    // refused refuses again without asking the impostor, and one that has not refused still probes.
    resetIdentityPreflightForTest();
    expect(getIdentityPreflightFailed()).toBe(false);

    refuseIdentityPreflightForTest();
    expect(getIdentityPreflightFailed()).toBe(true);
    await expect(
        verifyIdentityPreflight('http://127.0.0.1:1', readHarnessManifest, verifyServerIdentity),
    ).rejects.toThrow(/already refused the assigned server/);

    resetIdentityPreflightForTest();
    expect(getIdentityPreflightFailed()).toBe(false);
});
