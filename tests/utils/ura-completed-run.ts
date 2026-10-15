import { execFileSync } from 'node:child_process';
import { DatabaseSync } from 'node:sqlite';
import { scratchDatabasePath } from '../browser/global-setup';

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
    const path = scratchDatabasePath();

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
