import { expect, test } from '@playwright/test';
import { existsSync, mkdtempSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { removeScratchDatabase, scratchDatabasePath } from './global-setup';

/**
 * KI-80's two properties, checked without touching a database.
 *
 * The defect was a shared path: `SCRATCH_DATABASE` was one constant, so a second session's
 * `globalSetup` deleted the rows the first session had just seeded and its server still held open, and
 * neither run's numbers described anything real. These cases fail if the path becomes shared again
 * (a literal `database/browser-scratch.sqlite`), if the environment variable stops being honoured, or
 * if the sweep is widened to a pattern that can reach a file it does not own.
 *
 * Nothing here runs the harness's setup step: it is a unit case that happens to live in the browser
 * directory because that is where the module it tests is, and it needs no server and no database.
 */
test('the environment variable names the path, and a relative value lands inside the repo', () => {
    expect(scratchDatabasePath({ PLAYWRIGHT_SCRATCH_DB: 'D:/sessions/one/browser.sqlite' })).toBe(
        'D:/sessions/one/browser.sqlite',
    );

    const relative = scratchDatabasePath({ PLAYWRIGHT_SCRATCH_DB: 'database/peer-two.sqlite' });
    expect(relative.endsWith(join('database', 'peer-two.sqlite'))).toBe(true);
    expect(relative).not.toBe(scratchDatabasePath({ PLAYWRIGHT_SCRATCH_DB: 'database/peer-three.sqlite' }));
});

test('with no variable set, the default carries this process id so two runs differ', () => {
    const path = scratchDatabasePath({});

    expect(path).toMatch(new RegExp(`browser-scratch-${process.pid}-\\d+\\.sqlite$`));
    expect(path).not.toContain('database/browser-scratch.sqlite');
});

test('the sweep deletes the file it owns and leaves a peer run untouched', () => {
    const dir = mkdtempSync(join(tmpdir(), 'ki80-'));
    const mine = join(dir, 'browser-scratch-1.sqlite');
    const peer = join(dir, 'browser-scratch-2.sqlite');

    try {
        for (const path of [mine, peer]) {
            for (const suffix of ['', '-wal', '-shm']) {
                writeFileSync(`${path}${suffix}`, 'rows');
            }
        }

        removeScratchDatabase(mine);

        expect(existsSync(mine)).toBe(false);
        expect(existsSync(`${mine}-wal`)).toBe(false);
        expect(existsSync(`${mine}-shm`)).toBe(false);
        expect(existsSync(peer)).toBe(true);
        expect(existsSync(`${peer}-wal`)).toBe(true);
        expect(existsSync(`${peer}-shm`)).toBe(true);
    } finally {
        rmSync(dir, { recursive: true, force: true });
    }
});
