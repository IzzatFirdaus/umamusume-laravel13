import { execFileSync } from 'node:child_process';
import { DatabaseSync } from 'node:sqlite';
import { test } from '@playwright/test';
import { resolvedScratchDatabase } from '../browser/global-setup';
import { deleteRun } from './delete-run';

/*
 * The seeded runs this module left behind, read back as a footprint.
 *
 * `uraCompletedRun()` returns ONE id, but `UraFinaleRunsSeeder` writes a whole tracker set: the
 * scratch database holds seven `training_runs` after a single `containment.spec.ts` run. A teardown
 * that deleted only the returned id would leave six, which is why the first attempt changed nothing.
 * The footprint is the rows the seeder marks, and `import_source` is that mark - the seeder's own
 * idempotency guard reads the same column (`UraFinaleRunsSeeder:429`).
 *
 * `TrackerVeteransSeeder` shares these run definitions, so the value is not a private discriminator.
 * That does not matter here: the footprint is read per invocation from the suite's own scratch file,
 * and deleting it is safe because the seeder re-seeds on demand - the next file that needs a
 * completed run asks for one and gets it. The teardown is `afterAll`, not `afterEach`, so every case
 * inside one file shares the single seeded set.
 */
function seededRunIds(): number[] {
    const db = new DatabaseSync(resolvedScratchDatabase());

    try {
        return db
            .prepare('SELECT id FROM training_runs WHERE import_source IS NOT NULL ORDER BY id')
            .all()
            .map((row) => (row as { id: number }).id);
    } finally {
        db.close();
    }
}

test.afterAll(async ({ browser }) => {
    const ids = seededRunIds();

    if (ids.length === 0) {
        return;
    }

    const page = await browser.newPage();

    try {
        // `runs.destroy` needs the session's CSRF cookie, which a document request issues.
        await page.goto('/', { waitUntil: 'domcontentloaded' });

        for (const id of ids) {
            await deleteRun(page, `/training-runs/${id}`);
        }
    } finally {
        await page.close();
    }
});

/*
 * The suite's own scratch database, found the way `global-setup.ts` finds it.
 *
 * `DatabaseSeeder` registers only the reference seeders, so a migrated-and-seeded harness holds no
 * career runs at all: the run bodies live in `UraFinaleRunsSeeder`, which is deliberately unregistered.
 * The URA cases in `career-cockpit.spec.ts` need a *finished* career whose four mandatory races are
 * recorded, and rebuilding that through the wizard plus the race screen turn by turn is not a fixture,
 * it is a second suite. So this runs the repo's own seeder against the file the server is already
 * reading, once, and answers with the run it produced.
 */
const FIND = `
    SELECT r.id AS id
    FROM training_runs r
    WHERE r.scenario = 'ura_finale'
      AND r.status = 'Completed'
      AND (SELECT count(*) FROM race_entries e WHERE e.training_run_id = r.id) >= 4
    ORDER BY r.id
    LIMIT 1
`;

export function uraCompletedRun(): number {
    const path = resolvedScratchDatabase();

    const find = (): number | undefined => {
        const db = new DatabaseSync(path);
        try {
            const row = db.prepare(FIND).get() as { id: number } | undefined;

            return row?.id;
        } finally {
            db.close();
        }
    };

    const seeded = find();
    if (seeded !== undefined) {
        return seeded;
    }

    execFileSync('php', ['artisan', 'db:seed', '--class=Database\\Seeders\\UraFinaleRunsSeeder', '--no-interaction'], {
        env: { ...process.env, DB_DATABASE: path },
        stdio: 'ignore',
    });

    const created = find();
    if (created === undefined) {
        throw new Error(`UraFinaleRunsSeeder wrote no Completed ura_finale run with four race entries into ${path}`);
    }

    return created;
}
