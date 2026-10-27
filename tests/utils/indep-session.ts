import { DatabaseSync } from 'node:sqlite';
import { resolvedScratchDatabase } from '../browser/global-setup';

/*
 * Independent Training rows written straight into the suite's scratch database
 * (`UMAMUSUME_REFERENCE.md` §1.1.7).
 *
 * No route creates an idle session outside the wizard's own preflight write, and a spec that walked
 * the whole wizard to reach one would spend four writes on a surface whose assertions are the timer and
 * the result banner. `tests/utils/review-candidates.ts` set the precedent for touching the scratch file
 * directly from a spec (the database is the harness's own, built and owned by `global-setup.ts`).
 *
 * The row shapes are the migrations' and the factories' own: `training_runs` needs only `umamusume_id`
 * (status defaults to `Active`), and `independent_training_runs` carries the three inputs plus the
 * 50-minute window. Times are written in SQLite's `YYYY-MM-DD HH:MM:SS` form.
 */

export interface SeededIndependentSession {
    /** The run's id, for navigating to `/training-runs/<id>/result`. */
    runId: number;
}

function sqlTime(date: Date): string {
    return date.toISOString().replace('T', ' ').slice(0, 19);
}

function firstTraineeId(db: DatabaseSync): number {
    const row = db.prepare('SELECT id FROM umamusume ORDER BY id LIMIT 1').get() as { id: number } | undefined;

    if (row === undefined) {
        throw new Error('The scratch database holds no trainee, so an idle session has no owner to seed.');
    }

    return row.id;
}

/**
 * A run whose idle window is still open: `completes_at` ten minutes from now, so `isInProgress()` is
 * true and the dashboard renders the timer card.
 */
export function seedInProgressSession(options: { policy?: string } = {}): SeededIndependentSession {
    const { policy = 'balanced' } = options;
    const db = new DatabaseSync(resolvedScratchDatabase());
    const now = new Date();
    const traineeId = firstTraineeId(db);

    try {
        const run = db
            .prepare("INSERT INTO training_runs (umamusume_id, status, created_at, updated_at) VALUES (?, 'Active', ?, ?)")
            .run(traineeId, sqlTime(now), sqlTime(now));

        const runId = Number(run.lastInsertRowid);

        db.prepare(
            `INSERT INTO independent_training_runs
                (training_run_id, policy, priority_skills, rotation, started_at, completes_at,
                 completed_at, abandoned_at, result, created_at, updated_at)
             VALUES (?, ?, NULL, NULL, ?, ?, NULL, NULL, NULL, ?, ?)`,
        ).run(
            runId,
            policy,
            sqlTime(new Date(now.getTime() - 5 * 60_000)),
            sqlTime(new Date(now.getTime() + 10 * 60_000)),
            sqlTime(now),
            sqlTime(now),
        );

        return { runId };
    } finally {
        db.close();
    }
}

/**
 * A run whose idle window closed with a completion payload, so the Result screen renders the banner
 * and the six-limits note.
 */
export function seedCompletedSession(options: { policy?: string; fans?: number; sashes?: number } = {}): SeededIndependentSession {
    const { policy = 'balanced', fans = 1_300_000, sashes = 1 } = options;
    const db = new DatabaseSync(resolvedScratchDatabase());
    const now = new Date();
    const traineeId = firstTraineeId(db);

    try {
        const run = db
            .prepare("INSERT INTO training_runs (umamusume_id, status, created_at, updated_at) VALUES (?, 'Completed', ?, ?)")
            .run(traineeId, sqlTime(now), sqlTime(now));

        const runId = Number(run.lastInsertRowid);

        const result = JSON.stringify({
            stat_gains: { Speed: 100, Stamina: 100, Power: 100, Guts: 100, Wit: 100 },
            fans,
            sashes,
            skill_hints: [],
        });

        db.prepare(
            `INSERT INTO independent_training_runs
                (training_run_id, policy, priority_skills, rotation, started_at, completes_at,
                 completed_at, abandoned_at, result, created_at, updated_at)
             VALUES (?, ?, NULL, NULL, ?, ?, ?, NULL, ?, ?, ?)`,
        ).run(
            runId,
            policy,
            sqlTime(new Date(now.getTime() - 60 * 60_000)),
            sqlTime(new Date(now.getTime() - 10 * 60_000)),
            sqlTime(now),
            result,
            sqlTime(now),
            sqlTime(now),
        );

        return { runId };
    } finally {
        db.close();
    }
}

/** Remove a seeded run and its overlay (the foreign key cascades the child row). */
export function deleteSeededSession(runId: number): void {
    const db = new DatabaseSync(resolvedScratchDatabase());

    try {
        db.prepare('DELETE FROM training_runs WHERE id = ?').run(runId);
    } finally {
        db.close();
    }
}
