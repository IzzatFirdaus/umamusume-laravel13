import { test, expect } from '@playwright/test';
import { spawn, type ChildProcess } from 'child_process';

test('enforces request-secret gate', async () => {
    const exePath = process.env.NATIVEPHP_EXE_PATH;
    const secret = process.env.NATIVEPHP_SECRET;

    if (!exePath) {
        throw new Error('NATIVEPHP_EXE_PATH is not set');
    }
    if (!secret) {
        throw new Error('NATIVEPHP_SECRET is not set');
    }

    const app: ChildProcess = spawn(exePath, [], {
        env: { ...process.env },
        stdio: ['ignore', 'pipe', 'pipe'],
    });

    // Detect PHP server port by probing 8100-9000 (NativePHP's getPhpPort range).
    // Parsing Electron stdout for 'PHP Server started on port:' is unreliable on
    // Windows packaged GUI apps, where console.log may not reach the spawn pipe.
    const phpPort = await new Promise<number>((resolve, reject) => {
        const deadline = Date.now() + 300000;

        const attempt = () => {
            if (Date.now() > deadline) {
                reject(new Error('PHP server port not found within 300s'));
                return;
            }

            (async () => {
                // Give Electron time to initialize and start PHP
                await new Promise((r) => setTimeout(r, 20000));
                for (let port = 8100; port < 9000; port++) {
                    try {
                        const controller = new AbortController();
                        const t = setTimeout(() => controller.abort(), 500);
                        await fetch(`http://127.0.0.1:${port}/`, { signal: controller.signal });
                        clearTimeout(t);
                        resolve(port);
                        return;
                    } catch {
                        // Port not open yet
                    }
                }
                setTimeout(attempt, 15000);
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
