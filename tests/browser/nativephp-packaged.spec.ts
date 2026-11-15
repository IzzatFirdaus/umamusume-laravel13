import { test, expect } from '@playwright/test';
import { spawn, type ChildProcess } from 'child_process';
import { createConnection, type NetConnectOpts } from 'net';

test('enforces request-secret gate', async () => {
    const exePath = process.env.NATIVEPHP_EXE_PATH;
    const secret = process.env.NATIVEPHP_SECRET;

    if (!exePath) {
        throw new Error('NATIVEPHP_EXE_PATH is not set');
    }
    if (!secret) {
        throw new Error('NATIVEPHP_SECRET is not set');
    }

    const app: ChildProcess = spawn(exePath, ['--no-sandbox', '--disable-gpu'], {
        env: { ...process.env, DISPLAY: ':0' },
        stdio: ['ignore', 'pipe', 'pipe'],
    });

    app.on('error', (err) => {
        throw new Error(
            `Failed to spawn Electron at ${exePath}: ${err.message}`,
        );
    });

    console.log(`[packaged-test] Electron spawned at ${exePath}, waiting for PHP port...`);

    let appOutput = '';
    app.stdout?.on('data', (d) => (appOutput += d.toString()));
    app.stderr?.on('data', (d) => (appOutput += d.toString()));
    app.on('exit', (code) => {
        if (code !== null && code !== 0) {
            throw new Error(
                `Electron process exited early with code ${code}. Output: ${appOutput}`,
            );
        }
    });

    // Detect PHP server port by probing 8100-9000 (NativePHP's getPhpPort range).
    // Parsing Electron stdout for 'PHP Server started on port:' is unreliable on
    // Windows packaged GUI apps, where console.log may not reach the spawn pipe.
    const phpPort = await new Promise<number>((resolve, reject) => {
        const deadline = Date.now() + 300000;

        const attempt = () => {
            if (Date.now() > deadline) {
                console.log(`[packaged-test] Electron output before timeout: ${appOutput}`);
                reject(
                    new Error(
                        `PHP server port not found within 300s. Electron output: ${appOutput}`,
                    ),
                );
                return;
            }

            const probePort = async (port: number): Promise<number | null> => {
                const opts: NetConnectOpts = { host: '127.0.0.1', port, timeout: 200 };
                return new Promise<number | null>((res) => {
                    const socket = createConnection(opts, () => {
                        socket.destroy();
                        res(port);
                    });
                    socket.on('error', () => res(null));
                    socket.on('timeout', () => {
                        socket.destroy();
                        res(null);
                    });
                });
            };

            (async () => {
                // Give Electron time to initialize and start PHP.
                await new Promise((r) => setTimeout(r, 5000));
                for (let batchStart = 8100; batchStart < 9000; batchStart += 20) {
                    const batchEnd = Math.min(batchStart + 20, 9000);
                    const results = await Promise.all(
                        Array.from(
                            { length: batchEnd - batchStart },
                            (_, i) => probePort(batchStart + i),
                        ),
                    );
                    const found = results.find((p) => p !== null);
                    if (found !== undefined) {
                        console.log(`[packaged-test] PHP port found: ${found}`);
                        resolve(found);
                        return;
                    }
                    if (Date.now() > deadline) break;
                }
                setTimeout(attempt, 10000);
            })().catch((err) => reject(err));
        };

        attempt();
    });

    const baseUrl = `http://127.0.0.1:${phpPort}`;

    try {
        const res1 = await fetch(baseUrl);
        expect(res1.status).toBe(403);

        const res2 = await fetch(baseUrl, {
            headers: { 'X-NativePHP-Secret': 'wrong-secret' },
        });
        expect(res2.status).toBe(403);

        const res3 = await fetch(baseUrl, {
            headers: { 'X-NativePHP-Secret': secret },
        });
        expect(res3.status).toBe(200);

        const res4 = await fetch(baseUrl, {
            headers: { Cookie: `_php_native=${secret}` },
        });
        expect(res4.status).toBe(200);
    } finally {
        app.kill();
    }
});
