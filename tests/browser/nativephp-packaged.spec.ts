import { test, expect } from '@playwright/test';
import { spawn, type ChildProcess } from 'child_process';
import { createConnection, type NetConnectOpts } from 'net';
import * as path from 'path';
import * as fs from 'fs';

test('enforces request-secret gate', async () => {
    const exePath = process.env.NATIVEPHP_EXE_PATH;
    const secret = process.env.NATIVEPHP_SECRET;

    if (!exePath) {
        throw new Error('NATIVEPHP_EXE_PATH is not set');
    }
    if (!secret) {
        throw new Error('NATIVEPHP_SECRET is not set');
    }

    const appDir = path.dirname(exePath);

    // Find the PHP binary bundled with the NativePHP app.
    const phpExe = findFileRecursive(appDir, 'php.exe', 4);
    if (!phpExe) {
        throw new Error(`php.exe not found in ${appDir}`);
    }

    // Find the NativePHP server entry point. In production builds this is
    // <appPath>/build/__nativephp_app_bundle.
    const serverScript =
        findFileRecursive(appDir, '__nativephp_app_bundle', 4) ??
        findFileRecursive(appDir, 'server.php', 4);
    if (!serverScript) {
        throw new Error(`NativePHP server script not found in ${appDir}`);
    }

    // Pick an available port in NativePHP's getPhpPort range (8100-9000).
    const phpPort = await findAvailablePort(8100, 9000);

    console.log(
        `[packaged-test] Starting PHP: ${phpExe} -S 127.0.0.1:${phpPort} ${serverScript}`,
    );

    const phpProcess: ChildProcess = spawn(
        phpExe,
        ['-S', `127.0.0.1:${phpPort}`, serverScript],
        {
            cwd: path.dirname(serverScript),
            env: {
                ...process.env,
                NATIVEPHP_SECRET: secret,
            },
            stdio: ['ignore', 'pipe', 'pipe'],
        },
    );

    let phpOutput = '';
    phpProcess.stdout?.on('data', (d) => (phpOutput += d.toString()));
    phpProcess.stderr?.on('data', (d) => (phpOutput += d.toString()));
    phpProcess.on('error', (err) => {
        throw new Error(`Failed to spawn PHP at ${phpExe}: ${err.message}`);
    });
    phpProcess.on('exit', (code) => {
        if (code !== null && code !== 0) {
            throw new Error(
                `PHP process exited early with code ${code}. Output: ${phpOutput}`,
            );
        }
    });

    // Wait for PHP to start listening on the chosen port (max 120s).
    const baseUrl = await waitForPort(phpPort, 120000, phpOutput);

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
        phpProcess.kill();
    }
});

/**
 * Recursively search for a filename up to maxDepth levels below root.
 * Returns the first match or null.
 */
function findFileRecursive(
    root: string,
    filename: string,
    maxDepth: number,
): string | null {
    if (maxDepth < 0) return null;
    let entries: fs.Dirent[];
    try {
        entries = fs.readdirSync(root, { withFileTypes: true });
    } catch {
        return null;
    }
    for (const entry of entries) {
        const full = path.join(root, entry.name);
        if (entry.isDirectory()) {
            const found = findFileRecursive(full, filename, maxDepth - 1);
            if (found) return found;
        } else if (entry.name === filename) {
            return full;
        }
    }
    return null;
}

/**
 * Find an available TCP port in [start, end) by trying to connect.
 * Returns the first port that refuses a connection (likely available).
 */
async function findAvailablePort(start: number, end: number): Promise<number> {
    for (let port = start; port < end; port++) {
        const available = await probePort(port, 100);
        if (available === null) {
            // Connection refused = port is available
            return port;
        }
    }
    throw new Error(`No available port in range ${start}-${end}`);
}

/**
 * Probe a TCP port. Returns the port if open, null if refused/unreachable.
 */
function probePort(port: number, timeoutMs: number): Promise<number | null> {
    return new Promise((res) => {
        const socket = createConnection({ host: '127.0.0.1', port, timeout: timeoutMs }, () => {
            socket.destroy();
            res(port);
        });
        socket.on('error', () => res(null));
        socket.on('timeout', () => {
            socket.destroy();
            res(null);
        });
    });
}

/**
 * Wait for a port to start accepting connections.
 * Returns the base URL once open, or throws after timeoutMs.
 */
async function waitForPort(
    port: number,
    timeoutMs: number,
    phpOutput: string,
): Promise<string> {
    const deadline = Date.now() + timeoutMs;
    while (Date.now() < deadline) {
        const open = await probePort(port, 200);
        if (open !== null) {
            return `http://127.0.0.1:${port}`;
        }
        await new Promise((r) => setTimeout(r, 500));
    }
    throw new Error(
        `Port ${port} not accepting connections within ${timeoutMs}ms. PHP output: ${phpOutput}`,
    );
}
