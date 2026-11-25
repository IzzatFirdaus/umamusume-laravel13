import { test, expect } from './support/base-test';
import { spawn, type ChildProcess } from 'child_process';
import { createConnection } from 'net';

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

    app.stdout?.on('data', (data) => {
        console.log(`[app stdout] ${data.toString().trim()}`);
    });
    app.stderr?.on('data', (data) => {
        console.log(`[app stderr] ${data.toString().trim()}`);
    });
    app.on('exit', (code, signal) => {
        console.log(`[app exit] code=${code} signal=${signal}`);
    });

    let phpPort: number | null = null;

    // The packaged exe is a windowed GUI app: its stdout never carries the
    // "PHP Server started on port" line. The app picks a free port in
    // 8100-9000, so look for a listening socket in that range and then confirm
    // it answers HTTP.
    const isListening = (port: number): Promise<boolean> =>
        new Promise((resolve) => {
            const socket = createConnection({ host: '127.0.0.1', port });
            const done = (result: boolean): void => {
                socket.destroy();
                resolve(result);
            };
            socket.setTimeout(250);
            socket.once('connect', () => done(true));
            socket.once('timeout', () => done(false));
            socket.once('error', () => done(false));
        });

    // A TCP connect tells us whether anything is listening without making PHP
    // serve a request: the built-in server handles one request at a time, and a
    // burst of parallel HTTP probes starves the very port it is looking for.
    const findPort = async (): Promise<number | null> => {
        for (let port = 8100; port <= 9000; port++) {
            if (await isListening(port)) {
                console.log(`[port scan] ${port} -> listening`);
                return port;
            }
        }
        return null;
    };

    const deadline = Date.now() + 240000; // 4 min; cold CI start needs time
    while (Date.now() < deadline) {
        if (app.exitCode !== null && app.exitCode !== undefined) {
            throw new Error(`App exited early with code ${app.exitCode} before PHP port was found`);
        }
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
