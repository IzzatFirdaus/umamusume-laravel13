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

    // The packaged exe is a windowed GUI app: its stdout never carries the
    // "PHP Server started on port" line. The app picks a free port in
    // 8100-9000, so scan that range until something answers on HTTP.
    const findPort = async (): Promise<number | null> => {
        const results = await Promise.all(
            Array.from({ length: 901 }, (_, i) => 8100 + i).map(async (port) => {
                try {
                    const res = await fetch(`http://127.0.0.1:${port}`, {
                        signal: AbortSignal.timeout(100),
                    });
                    if (res.status === 403 || res.status === 200) {
                        return port;
                    }
                } catch {
                    // port closed or app not yet serving
                }
                return null;
            }),
        );
        return results.find((p) => p !== null) ?? null;
    };

    const deadline = Date.now() + 240000; // 4 min; cold CI start needs time
    while (Date.now() < deadline) {
        phpPort = await findPort();
        if (phpPort) {
            break;
        }
        await new Promise((resolve) => setTimeout(resolve, 2000));
    }

    if (!phpPort) {
        app.kill();
        throw new Error('PHP server port not found within 4 minutes');
    }

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
