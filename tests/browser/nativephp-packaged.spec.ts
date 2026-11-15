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

    let phpPort: number | null = null;
    const portReady = new Promise<void>((resolve, reject) => {
        const timeout = setTimeout(
            () => reject(new Error('PHP server port not found within 90s')),
            90000,
        );
        const onData = (data: Buffer) => {
            const text = data.toString();
            const match =
                text.match(/PHP Server started on port:\s*(\d+)/) ??
                text.match(/Development Server \(.*:(\d+)\) started/);
            if (match) {
                clearTimeout(timeout);
                phpPort = parseInt(match[1], 10);
                resolve();
            }
        };
        app.stdout.on('data', onData);
        app.stderr.on('data', onData);
    });

    await portReady;

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
