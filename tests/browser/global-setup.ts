import { execSync } from 'node:child_process';
import { rmSync } from 'node:fs';
import { join } from 'node:path';

/**
 * The browser suite's data contract (KI-69).
 *
 * Five cases assert empty-state surfaces — `runs.spec.ts:41` and `career-legacy-deck-steps.spec.ts:268`
 * expect `No runs yet`, and three dashboard cases expect the no-active-career state — while other cases
 * need the seeded reference data the wizard's roster reads. Both halves point at one thing: the suite owns
 * a scratch database that it builds from nothing. Until now nothing did that. `playwright.config.ts` had
 * the server take its database from `.env`, which is the shared dev file, so `npm run test:browser`
 * followed the config and read whatever rows other sessions and earlier specs had left behind — the class
 * KI-69 records, and the reason a green suite proved nothing about the empty-state surfaces.
 *
 * Deleting a file is not weakening an assertion: the tests still require an empty runs table, and now
 * that requirement is established rather than assumed.
 *
 * The path is absolute and named here, so no `DB_DATABASE` in a cached config or a stray `.env` can
 * redirect it onto `database/database.sqlite`. Reference data arrives from the committed seeders, which
 * is what the wizard screens and the catalog tests read.
 */
export const SCRATCH_DATABASE = join(__dirname, '..', '..', 'database', 'browser-scratch.sqlite');

export default async function globalSetup(): Promise<void> {
    rmSync(SCRATCH_DATABASE, { force: true });

    execSync('php artisan migrate --seed', {
        cwd: join(__dirname, '..', '..'),
        env: { ...process.env, DB_DATABASE: SCRATCH_DATABASE },
        stdio: 'inherit',
    });
}
